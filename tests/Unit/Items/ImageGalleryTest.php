<?php

namespace Tests\Unit\Items;

use Tests\TestCase;
use App\Models\Tenant\Items\ImageGallery;
use ReflectionMethod;

/**
 * Tests unitarios para la lógica de ImageGallery.
 *
 * Métodos testeados sin BD:
 *  - getThumbnailPath() → lógica de rutas de archivos
 *  - cleanMalformedUrl() → corrección de URLs duplicadas (http:https://)
 *  - isPrincipal() → verificación de tipo de imagen
 *  - isDeleted() → verificación de soft delete
 *  - getOriginalName() → nombre base del archivo
 *
 * Ejecutar con: php artisan test tests/Unit/Items/ImageGalleryTest.php
 */
class ImageGalleryTest extends TestCase
{
    /**
     * Crea un ImageGallery en memoria con los atributos dados.
     */
    private function makeImage(array $attributes = []): ImageGallery
    {
        $image = new ImageGallery();
        $image->forceFill(array_merge([
            'itemId'     => 1,
            'img_path'   => null,
            'type'       => 'GALERIA',
            'deleted_at' => null,
        ], $attributes));
        return $image;
    }

    /**
     * Expone el método privado cleanMalformedUrl para poder testearlo directamente.
     */
    private function callCleanUrl(ImageGallery $image, string $url): string
    {
        $method = new ReflectionMethod(ImageGallery::class, 'cleanMalformedUrl');
        $method->setAccessible(true);
        return $method->invoke($image, $url);
    }

    // =========================================================================
    // TESTS PARA getThumbnailPath() — Construcción de rutas de thumbnails
    // =========================================================================

    /** @test */
    public function genera_ruta_thumbnail_correctamente_para_imagen_local()
    {
        $image = $this->makeImage(['img_path' => 'items/12/foto-producto.jpg']);

        $thumb = $image->getThumbnailPath();

        $this->assertEquals(
            'items/12/thumbnails/foto-producto.jpg',
            $thumb,
            'El thumbnail debe estar en la subcarpeta /thumbnails/ manteniendo el nombre de archivo'
        );
    }

    /** @test */
    public function genera_ruta_thumbnail_para_imagen_en_directorio_raiz()
    {
        $image = $this->makeImage(['img_path' => 'imagen.png']);

        $thumb = $image->getThumbnailPath();

        // pathinfo de 'imagen.png' → dirname = '.'
        $this->assertStringContainsString('thumbnails', $thumb);
        $this->assertStringContainsString('imagen.png', $thumb);
    }

    /** @test */
    public function retorna_null_si_no_hay_img_path()
    {
        $image = $this->makeImage(['img_path' => null]);

        $thumb = $image->getThumbnailPath();

        $this->assertNull($thumb,
            'getThumbnailPath() debe retornar null si no hay ruta de imagen'
        );
    }

    /** @test */
    public function preserva_el_nombre_de_archivo_original_en_el_thumbnail()
    {
        $image = $this->makeImage(['img_path' => 'uploads/items/foto-2024.webp']);

        $thumb = $image->getThumbnailPath();

        $this->assertStringEndsWith('foto-2024.webp', $thumb,
            'El nombre de archivo del thumbnail debe ser igual al original'
        );
    }

    // =========================================================================
    // TESTS PARA cleanMalformedUrl() — Corrección de URLs malformadas
    // Esta fue un bug real de producción: URLs generadas como "http:https://..."
    // =========================================================================

    /** @test */
    public function corrige_url_con_doble_protocolo_http_https()
    {
        $image  = $this->makeImage();
        $urlMal = 'http:https://erp.dosil.com.co/storage/item.jpg';

        $limpia = $this->callCleanUrl($image, $urlMal);

        $this->assertEquals(
            'https://erp.dosil.com.co/storage/item.jpg',
            $limpia,
            'Debe corregir "http:https://" a "https://"'
        );
    }

    /** @test */
    public function corrige_url_con_doble_protocolo_https_https()
    {
        $image  = $this->makeImage();
        $urlMal = 'https:https://cloud.ticsia.com/storage/item.jpg';

        $limpia = $this->callCleanUrl($image, $urlMal);

        $this->assertStringStartsWith('https://', $limpia,
            'Debe corregir "https:https://" a una sola instancia de "https://"'
        );
        $this->assertStringNotContainsString('https:https', $limpia);
    }

    /** @test */
    public function no_modifica_url_correctamente_formada()
    {
        $image     = $this->makeImage();
        $urlCorrecta = 'https://erp.dosil.com.co/storage/items/12/foto.jpg';

        $resultado = $this->callCleanUrl($image, $urlCorrecta);

        $this->assertEquals($urlCorrecta, $resultado,
            'Una URL correcta no debe modificarse'
        );
    }

    /** @test */
    public function fuerza_https_en_dominio_de_produccion_erp_dosil()
    {
        $image   = $this->makeImage();
        $urlHttp = 'http://erp.dosil.com.co/storage/items/foto.jpg';

        $limpia = $this->callCleanUrl($image, $urlHttp);

        $this->assertStringStartsWith('https://', $limpia,
            'Las URLs de erp.dosil.com.co deben ser forzadas a HTTPS'
        );
    }

    /** @test */
    public function fuerza_https_en_dominio_de_produccion_ticsia()
    {
        $image   = $this->makeImage();
        $urlHttp = 'http://cloud.ticsia.com/storage/items/foto.jpg';

        $limpia = $this->callCleanUrl($image, $urlHttp);

        $this->assertStringStartsWith('https://', $limpia,
            'Las URLs de cloud.ticsia.com deben ser forzadas a HTTPS'
        );
    }

    /** @test */
    public function no_modifica_urls_de_otros_dominios()
    {
        $image   = $this->makeImage();
        $urlHttp = 'http://localhost/storage/items/foto.jpg';

        $resultado = $this->callCleanUrl($image, $urlHttp);

        // localhost no es un dominio de producción → no se fuerza HTTPS
        $this->assertStringStartsWith('http://', $resultado,
            'Las URLs de localhost (desarrollo) no deben convertirse a HTTPS'
        );
    }

    /** @test */
    public function retorna_null_si_la_url_es_null()
    {
        $image     = $this->makeImage();
        $resultado = $this->callCleanUrl($image, null);

        $this->assertNull($resultado);
    }

    // =========================================================================
    // TESTS PARA isPrincipal() e isDeleted() — Lógica de estado
    // =========================================================================

    /** @test */
    public function es_principal_cuando_type_es_PRINCIPAL()
    {
        $image = $this->makeImage(['type' => 'PRINCIPAL']);

        $this->assertTrue($image->isPrincipal(),
            'Una imagen con type=PRINCIPAL debe ser identificada como principal'
        );
    }

    /** @test */
    public function no_es_principal_cuando_type_es_GALERIA()
    {
        $image = $this->makeImage(['type' => 'GALERIA']);

        $this->assertFalse($image->isPrincipal(),
            'Una imagen de galería NO debe ser principal'
        );
    }

    /** @test */
    public function no_es_principal_cuando_type_es_PDF()
    {
        $image = $this->makeImage(['type' => 'PDF']);

        $this->assertFalse($image->isPrincipal());
    }

    /** @test */
    public function is_deleted_retorna_true_cuando_tiene_deleted_at()
    {
        $image = $this->makeImage(['deleted_at' => now()]);

        $this->assertTrue($image->isDeleted(),
            'Una imagen con deleted_at debe considerarse eliminada'
        );
    }

    /** @test */
    public function is_deleted_retorna_false_cuando_deleted_at_es_null()
    {
        $image = $this->makeImage(['deleted_at' => null]);

        $this->assertFalse($image->isDeleted(),
            'Una imagen activa (sin deleted_at) no debe considerarse eliminada'
        );
    }

    // =========================================================================
    // TESTS PARA getOriginalName() — Nombre del archivo
    // =========================================================================

    /** @test */
    public function retorna_el_nombre_de_archivo_sin_la_ruta()
    {
        $image = $this->makeImage(['img_path' => 'uploads/items/99/foto-producto-nuevo.jpg']);

        $nombre = $image->getOriginalName();

        $this->assertEquals('foto-producto-nuevo.jpg', $nombre,
            'getOriginalName() debe retornar solo el nombre del archivo, sin la ruta'
        );
    }

    /** @test */
    public function retorna_el_nombre_correcto_para_archivos_tipo_pdf()
    {
        $image = $this->makeImage([
            'img_path' => 'uploads/items/5/ficha-tecnica.pdf',
            'type'     => 'PDF',
        ]);

        $nombre = $image->getOriginalName();

        $this->assertEquals('ficha-tecnica.pdf', $nombre);
    }

    /** @test */
    public function retorna_nombre_cuando_la_imagen_esta_en_el_directorio_raiz()
    {
        $image = $this->makeImage(['img_path' => 'imagen.png']);

        $nombre = $image->getOriginalName();

        $this->assertEquals('imagen.png', $nombre);
    }
}
