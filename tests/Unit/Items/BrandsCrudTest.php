<?php

namespace Tests\Unit\Items;

use Tests\TenantTestCase;
use App\Models\Tenant\Items\Brand;
use App\Services\Tenant\Inventory\BrandsService;

/**
 * Tests CRUD para el módulo de Marcas (Brands).
 * Usa TenantTestCase que configura SQLite :memory: para la conexión 'tenant'.
 *
 * Ejecutar: php artisan test tests/Unit/Items/BrandsCrudTest.php
 */
class BrandsCrudTest extends TenantTestCase
{
    private BrandsService $service;

    protected function setUp(): void
    {
        parent::setUp();

        // Subclass that bypasses ensureTenantConnection (already done in TenantTestCase)
        $this->service = new class extends BrandsService {
            protected function ensureTenantConnection(): void
            {
                // No-op: connection already configured in TenantTestCase::setUp()
            }
        };
    }

    // =========================================================================
    // CREATE
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_una_marca_con_datos_validos(): void
    {
        $brand = $this->service->createBrand([
            'name'   => 'Nike',
            'status' => 1,
        ]);

        $this->assertNotNull($brand->id, 'La marca debe tener un ID asignado');
        $this->assertEquals('Nike', $brand->name);
        $this->assertEquals(1, $brand->status);
        $this->assertDatabaseHas('inv_item_brand', ['name' => 'Nike', 'status' => 1], 'tenant');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function crear_marca_usa_status_1_por_defecto(): void
    {
        $brand = $this->service->createBrand(['name' => 'Adidas']);

        $this->assertEquals(1, $brand->status, 'El status por defecto debe ser 1 (activo)');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_crear_marca_con_status_inactivo(): void
    {
        $brand = $this->service->createBrand(['name' => 'Puma', 'status' => 0]);

        $this->assertEquals(0, $brand->status);
        $this->assertDatabaseHas('inv_item_brand', ['name' => 'Puma', 'status' => 0], 'tenant');
    }

    // =========================================================================
    // READ
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_active_brands_retorna_solo_marcas_activas(): void
    {
        Brand::on('tenant')->create(['name' => 'Activa', 'status' => 1]);
        Brand::on('tenant')->create(['name' => 'Inactiva', 'status' => 0]);

        $activas = $this->service->getActiveBrands();

        $this->assertCount(1, $activas);
        $this->assertEquals('Activa', $activas->first()->name);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_active_brands_retorna_lista_vacia_si_no_hay_marcas(): void
    {
        $activas = $this->service->getActiveBrands();

        $this->assertCount(0, $activas);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function get_active_brands_ordena_por_nombre_ascendente(): void
    {
        Brand::on('tenant')->create(['name' => 'Zara', 'status' => 1]);
        Brand::on('tenant')->create(['name' => 'Adidas', 'status' => 1]);
        Brand::on('tenant')->create(['name' => 'Nike', 'status' => 1]);

        $marcas = $this->service->getActiveBrands();

        $this->assertEquals('Adidas', $marcas[0]->name);
        $this->assertEquals('Nike', $marcas[1]->name);
        $this->assertEquals('Zara', $marcas[2]->name);
    }

    // =========================================================================
    // UPDATE (via model directo)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_actualizar_el_nombre_de_una_marca(): void
    {
        $brand = Brand::on('tenant')->create(['name' => 'Original', 'status' => 1]);

        $brand->update(['name' => 'Actualizado']);

        $this->assertDatabaseHas('inv_item_brand', ['id' => $brand->id, 'name' => 'Actualizado'], 'tenant');
        $this->assertDatabaseMissing('inv_item_brand', ['name' => 'Original'], 'tenant');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_desactivar_una_marca(): void
    {
        $brand = Brand::on('tenant')->create(['name' => 'Activa', 'status' => 1]);

        $brand->update(['status' => 0]);

        $this->assertDatabaseHas('inv_item_brand', ['id' => $brand->id, 'status' => 0], 'tenant');
    }

    // =========================================================================
    // DELETE (soft delete)
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function puede_eliminar_una_marca_con_soft_delete(): void
    {
        $brand = Brand::on('tenant')->create(['name' => 'Para Borrar', 'status' => 1]);

        $brand->delete();

        $this->assertSoftDeleted('inv_item_brand', ['id' => $brand->id], 'tenant');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function marca_eliminada_no_aparece_en_get_active_brands(): void
    {
        $brand = Brand::on('tenant')->create(['name' => 'Borrada', 'status' => 1]);
        $brand->delete();

        $activas = $this->service->getActiveBrands();

        $this->assertCount(0, $activas, 'Marcas eliminadas no deben aparecer en getActiveBrands');
    }

    // =========================================================================
    // VALIDACIONES
    // =========================================================================

    #[\PHPUnit\Framework\Attributes\Test]
    public function brand_exists_retorna_true_si_la_marca_existe(): void
    {
        Brand::on('tenant')->create(['name' => 'Existente', 'status' => 1]);

        $this->assertTrue($this->service->BrandExists('Existente'));
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function brand_exists_retorna_false_si_la_marca_no_existe(): void
    {
        $this->assertFalse($this->service->BrandExists('NoExiste'));
    }
}
