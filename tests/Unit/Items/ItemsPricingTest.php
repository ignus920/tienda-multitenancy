<?php

namespace Tests\Unit\Items;

use Tests\TestCase;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvValues;
use Illuminate\Database\Eloquent\Collection;
use ReflectionMethod;

/**
 * Tests unitarios para la lógica de precios del modelo Items.
 *
 * TÉCNICA CLAVE: Los accessors de precio (getPriceAttribute, getAllPricesAttribute)
 * usan $this->invValues como una colección ya cargada (Eloquent eager-loaded).
 * Esto significa que podemos INYECTAR datos falsos directamente en la relación
 * sin necesitar base de datos.
 *
 * Ejecutar con: php artisan test tests/Unit/Items/ItemsPricingTest.php
 */
class ItemsPricingTest extends TestCase
{
    /**
     * Crea un modelo Items con su colección invValues inyectada en memoria.
     * Este es el patrón central de este test: evitar la BD simulando las relaciones.
     *
     * @param array $valuesData  Array de arrays con los datos de cada InvValues a simular
     * @return Items
     */
    private function makeItemWithValues(array $valuesData): Items
    {
        // Crear el Item como modelo puro, sin guardarlo en BD
        $item = new class extends Items {
            public function __construct()
            {
                // Omitir constructor del padre para no inicializar configuración de BD
                $this->connection = null;
            }

            // Sobreescribir getOptionValue para que devuelva 0 por defecto
            // (modo precios fijos, sin lista de precios)
            protected function getOptionValue(int $optionId): ?int
            {
                return 0;
            }
        };

        // Construir colección de InvValues en memoria
        $invValuesCollection = new Collection(
            array_map(function ($data) {
                $invValue = new InvValues();
                $invValue->forceFill($data + [
                    'date'       => now()->toDateTimeString(),
                    'created_at' => now()->toDateTimeString(),
                ]);
                return $invValue;
            }, $valuesData)
        );

        // Inyectar la relación directamente en el objeto (eager loading simulado)
        $item->setRelation('invValues', $invValuesCollection);
        $item->setRelation('tax', null); // Sin impuesto por defecto

        return $item;
    }

    /**
     * Crea un mock de CnfTaxes con un porcentaje dado.
     */
    private function makeTax(float $percentage): object
    {
        return new class($percentage) {
            public float $percentage;
            public function __construct(float $p) { $this->percentage = $p; }
        };
    }

    // =========================================================================
    // TESTS PARA getPriceAttribute() — Modo PRECIOS FIJOS (sin lista de precios)
    // =========================================================================

    /** @test */
    public function retorna_cero_si_no_hay_valores_de_precio()
    {
        $item = $this->makeItemWithValues([]);

        $this->assertEquals(0, $item->price,
            'Sin valores de inv_values el precio debe ser 0'
        );
    }

    /** @test */
    public function retorna_el_precio_mas_reciente_por_fecha()
    {
        // Hay dos precios: el más reciente debe ganar
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 80000, 'date' => '2024-01-01'],
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 95000, 'date' => '2024-06-01'],
        ]);

        $this->assertEquals(95000, $item->price,
            'Debe retornar el precio con la fecha más reciente'
        );
    }

    /** @test */
    public function ignora_valores_que_no_son_de_tipo_precio()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'costo',  'label' => 'Costo',       'values' => 50000, 'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 120000, 'date' => '2024-06-01'],
        ]);

        $this->assertEquals(120000, $item->price,
            'Solo debe considerar registros de type=precio, no costos'
        );
    }

    /** @test */
    public function retorna_cero_si_solo_hay_costos_y_no_precios()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'costo', 'label' => 'Costo Base', 'values' => 45000, 'date' => '2024-06-01'],
        ]);

        $this->assertEquals(0, $item->price,
            'Si no hay registros de tipo precio, debe retornar 0'
        );
    }

    // =========================================================================
    // TESTS PARA getAllPricesAttribute() — Modo PRECIOS FIJOS
    // =========================================================================

    /** @test */
    public function retorna_todos_los_precios_agrupados_por_label()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Base',    'values' => 100000, 'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Regular', 'values' => 90000,  'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Crédito', 'values' => 95000,  'date' => '2024-06-01'],
        ]);

        $precios = $item->all_prices;

        $this->assertArrayHasKey('Precio Base',    $precios, 'Debe incluir Precio Base');
        $this->assertArrayHasKey('Precio Regular', $precios, 'Debe incluir Precio Regular');
        $this->assertArrayHasKey('Precio Crédito', $precios, 'Debe incluir Precio Crédito');
    }

    /** @test */
    public function excluye_precios_con_valor_cero_o_negativo()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Base',     'values' => 0,      'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Regular',  'values' => 90000,  'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Especial', 'values' => -1000,  'date' => '2024-06-01'],
        ]);

        $precios = $item->all_prices;

        $this->assertArrayNotHasKey('Precio Base', $precios,
            'Precios con valor 0 deben excluirse'
        );
        $this->assertArrayNotHasKey('Precio Especial', $precios,
            'Precios negativos deben excluirse'
        );
        $this->assertArrayHasKey('Precio Regular', $precios,
            'Precios válidos deben mantenerse'
        );
    }

    /** @test */
    public function excluye_precios_menores_al_precio_regular()
    {
        // Defensa de negocio: no mostrar precio menor al regular (protege margen)
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Regular',  'values' => 80000, 'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Especial', 'values' => 70000, 'date' => '2024-06-01'],
            ['type' => 'precio', 'label' => 'Precio Premium',  'values' => 95000, 'date' => '2024-06-01'],
        ]);

        $precios = $item->all_prices;

        $this->assertArrayNotHasKey('Precio Especial', $precios,
            '$70.000 < $80.000 (precio regular) → debe excluirse para proteger el margen'
        );
        $this->assertArrayHasKey('Precio Premium', $precios,
            '$95.000 > $80.000 (precio regular) → debe incluirse'
        );
    }

    /** @test */
    public function toma_el_precio_mas_reciente_cuando_hay_duplicados_por_label()
    {
        // El mismo label con dos fechas distintas → gana la más reciente
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 50000,  'date' => '2024-01-01', 'created_at' => '2024-01-01'],
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 120000, 'date' => '2024-12-01', 'created_at' => '2024-12-01'],
        ]);

        $precios = $item->all_prices;

        $this->assertEquals(120000, $precios['Precio Base'],
            'De dos registros con el mismo label, debe usarse el más reciente'
        );
    }

    /** @test */
    public function retorna_array_vacio_si_no_hay_precios_validos()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'costo', 'label' => 'Costo', 'values' => 50000, 'date' => '2024-06-01'],
        ]);

        $precios = $item->all_prices;

        $this->assertIsArray($precios);
        $this->assertEmpty($precios,
            'Si no hay precios válidos, debe retornar un array vacío (no null)'
        );
    }

    // =========================================================================
    // TESTS PARA getFormattedPriceAttribute()
    // =========================================================================

    /** @test */
    public function retorna_precio_formateado_con_signo_pesos_y_miles()
    {
        $item = $this->makeItemWithValues([
            ['type' => 'precio', 'label' => 'Precio Base', 'values' => 1250000, 'date' => '2024-06-01'],
        ]);

        $precioFormateado = $item->formatted_price;

        $this->assertStringStartsWith('$ ', $precioFormateado,
            'El precio formateado debe empezar con "$ "'
        );
        $this->assertStringContainsString('1,250,000', $precioFormateado,
            'El precio debe formatearse con separadores de miles'
        );
    }

    // =========================================================================
    // TESTS PARA getDisplayNameAttribute()
    // =========================================================================

    /** @test */
    public function display_name_retorna_el_nombre_en_mayusculas()
    {
        $item = new class extends Items {
            public function __construct() { $this->connection = null; }
        };
        $item->forceFill(['name' => 'Camisa polo clásica azul']);

        $this->assertEquals('CAMISA POLO CLÁSICA AZUL', $item->display_name,
            'getDisplayNameAttribute() debe retornar el nombre completamente en mayúsculas'
        );
    }

    /** @test */
    public function display_name_maneja_nombres_ya_en_mayusculas()
    {
        $item = new class extends Items {
            public function __construct() { $this->connection = null; }
        };
        $item->forceFill(['name' => 'PRODUCTO XYZ']);

        $this->assertEquals('PRODUCTO XYZ', $item->display_name);
    }

    // =========================================================================
    // TESTS PARA LOS SCOPES — Verifican que configuran el query correctamente
    // =========================================================================

    /** @test */
    public function scope_active_filtra_por_status_igual_a_1()
    {
        // Verificamos que el scope modifica la query correctamente sin ejecutarla
        $query = Items::query()->active();
        $sql   = $query->toRawSql();

        $this->assertStringContainsString('status', $sql,
            'El scope active() debe filtrar por la columna status'
        );
        $this->assertStringContainsString('1', $sql,
            'El scope active() debe filtrar por status = 1'
        );
    }

    /** @test */
    public function scope_inactive_filtra_por_status_igual_a_0()
    {
        $query = Items::query()->inactive();
        $sql   = $query->toRawSql();

        $this->assertStringContainsString('status', $sql);
        $this->assertStringContainsString('0', $sql);
    }

    /** @test */
    public function scope_inventoriable_filtra_por_inventoriable_igual_a_1()
    {
        $query = Items::query()->inventoriable();
        $sql   = $query->toRawSql();

        $this->assertStringContainsString('inventoriable', $sql);
    }

    /** @test */
    public function scope_by_type_filtra_por_tipo_de_item()
    {
        $query = Items::query()->byType('PRODUCTO');
        $sql   = $query->toRawSql();

        $this->assertStringContainsString('type', $sql);
        $this->assertStringContainsString('PRODUCTO', $sql);
    }

    /** @test */
    public function scope_by_category_filtra_por_categoria()
    {
        $query = Items::query()->byCategory(5);
        $sql   = $query->toRawSql();

        $this->assertStringContainsString('categoryId', $sql);
        $this->assertStringContainsString('5', $sql);
    }
}
