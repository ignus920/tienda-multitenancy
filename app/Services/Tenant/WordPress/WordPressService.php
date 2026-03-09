<?php

namespace App\Services\Tenant\WordPress;

use App\Models\Tenant\WordPress\WordPressConfig;
use App\Models\Tenant\Items\ImageGallery;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

class WordPressService
{
    protected $config;
    protected $baseUrl;
    protected $auth;

    public function __construct()
    {
        $this->loadConfig();
    }

    /**
     * Carga la configuración activa de la base de datos del tenant.
     */
    protected function loadConfig()
    {
        try {
            $this->config = WordPressConfig::where('is_active', true)->first();

            if ($this->config) {
                $this->baseUrl = rtrim($this->config->wp_url, '/') . '/wp-json/wc/v3/';
                $this->auth = [
                    $this->config->wp_user,
                    $this->config->wp_password
                ];
            }
        } catch (Exception $e) {
            Log::error("Error cargando configuración de WordPress: " . $e->getMessage());
        }
    }

    /**
     * Verifica si la configuración está disponible.
     */
    public function isConfigured()
    {
        return !empty($this->config);
    }

    /**
     * Busca un producto en WooCommerce por su SKU.
     * Soporta productos simples y variaciones.
     */
    public function findProductBySku($sku)
    {
        if (!$this->isConfigured() || empty($sku)) return null;

        try {
            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->get($this->baseUrl . 'products', [
                    'sku' => $sku
                ]);

            if ($response->successful()) {
                $products = $response->json();
                if (count($products) > 0) {
                    $product = $products[0];
                    return [
                        'id' => $product['id'],
                        'type' => $product['type'],
                        'sku' => $product['sku'],
                        'name' => $product['name'],
                        'images' => $product['images'],
                        'is_variation' => ($product['type'] === 'variation')
                    ];
                }
            }

            // Si no se encuentra como producto simple, buscar en variaciones (opcional según versión de API)
            // WooCommerce v3 suele devolver variaciones en la búsqueda si el SKU coincide exactamente.
            
        } catch (Exception $e) {
            Log::error("Error buscando producto por SKU ($sku): " . $e->getMessage());
        }

        return null;
    }

    /**
     * Sube una imagen a la librería de medios de WordPress.
     */
    public function uploadMedia($localPath, $filename = null)
    {
        if (!$this->isConfigured()) return null;

        try {
            if (!Storage::disk('public')->exists($localPath)) {
                throw new Exception("Archivo local no encontrado: $localPath");
            }

            $absolutePath = Storage::disk('public')->path($localPath);
            $finalPath = $this->optimizeImage($absolutePath);
            
            $fileContents = file_get_contents($finalPath);
            $name = $filename ?: basename($localPath);
            $mimeType = mime_content_type($finalPath);

            $wpMediaUrl = rtrim($this->config->wp_url, '/') . '/wp-json/wp/v2/media';

            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->withHeaders([
                    'Content-Disposition' => 'attachment; filename="' . $name . '"',
                ])
                ->withBody($fileContents, $mimeType)
                ->post($wpMediaUrl);

            // Eliminar temporal si se optimizó
            if ($finalPath !== $absolutePath && file_exists($finalPath)) {
                unlink($finalPath);
            }

            if ($response->successful()) {
                return $response->json()['id'];
            }

            Log::error("Error subiendo media a WP: " . $response->body());
        } catch (Exception $e) {
            Log::error("Excepción subiendo media a WP: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Asigna una imagen como principal de un producto.
     */
    public function setFeaturedImage($productId, $mediaId)
    {
        if (!$this->isConfigured()) return false;

        try {
            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/$productId", [
                    'images' => [
                        ['id' => (int)$mediaId]
                    ]
                ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error("Error asignando imagen principal en WP: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Agrega una imagen a la galería de un producto sin borrar las existentes.
     */
    public function addToGallery($productId, $mediaId)
    {
        if (!$this->isConfigured()) return false;

        try {
            // 1. Obtener imágenes actuales
            $currentProduct = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->get($this->baseUrl . "products/$productId")
                ->json();

            $images = $currentProduct['images'] ?? [];
            
            // 2. Verificar si ya existe el ID
            foreach ($images as $img) {
                if ($img['id'] == $mediaId) return true;
            }

            // 3. Agregar nueva
            $images[] = ['id' => (int)$mediaId];

            // 4. Actualizar
            $response = Http::withBasicAuth($this->auth[0], $this->auth[1])
                ->put($this->baseUrl . "products/$productId", [
                    'images' => $images
                ]);

            return $response->successful();
        } catch (Exception $e) {
            Log::error("Error agregando a galería en WP: " . $e->getMessage());
        }

        return false;
    }

    /**
     * Sincroniza una imagen específica del ERP con WordPress.
     */
    public function syncImage(ImageGallery $image, $productSku)
    {
        if (!$this->isConfigured()) return false;

        // 1. Buscar producto en WP
        $wpProduct = $this->findProductBySku($productSku);
        if (!$wpProduct) {
            Log::warning("Sincronización cancelada: Producto no encontrado en WP con SKU: $productSku");
            return false;
        }

        // 2. Subir imagen si no tiene ID de WP o forzar resubida
        $mediaId = $this->uploadMedia($image->img_path);
        if (!$mediaId) return false;

        // 3. Guardar ID en base de datos local
        $image->wp_media_id = $mediaId;
        $image->save();

        // 4. Asignar según el tipo
        if ($image->type === 'PRINCIPAL') {
            return $this->setFeaturedImage($wpProduct['id'], $mediaId);
        } else {
            return $this->addToGallery($wpProduct['id'], $mediaId);
        }
    }

    /**
     * Optimiza una imagen usando GD si es muy pesada (>512KB).
     * Retorna la ruta del archivo a subir.
     */
    protected function optimizeImage($path)
    {
        if (!file_exists($path) || filesize($path) < 512000) {
            return $path;
        }

        try {
            $info = getimagesize($path);
            if (!$info) return $path;

            $type = $info[2];
            $source = null;

            switch ($type) {
                case IMAGETYPE_JPEG: $source = imagecreatefromjpeg($path); break;
                case IMAGETYPE_PNG:  $source = imagecreatefrompng($path); break;
                case IMAGETYPE_WEBP: $source = @imagecreatefromwebp($path); break;
            }

            if (!$source) return $path;

            $width = imagesx($source);
            $height = imagesy($source);
            $maxDimension = 1200;

            if ($width > $maxDimension || $height > $maxDimension) {
                $ratio = $width / $height;
                if ($ratio > 1) {
                    $newWidth = $maxDimension;
                    $newHeight = (int)($maxDimension / $ratio);
                } else {
                    $newHeight = $maxDimension;
                    $newWidth = (int)($maxDimension * $ratio);
                }

                $thumb = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $thumb;
            }

            $tempPath = tempnam(sys_get_temp_dir(), 'wp_opt_');
            imagejpeg($source, $tempPath, 80);
            imagedestroy($source);

            return $tempPath;
        } catch (Exception $e) {
            Log::error("Error optimizando imagen: " . $e->getMessage());
            return $path;
        }
    }
}
