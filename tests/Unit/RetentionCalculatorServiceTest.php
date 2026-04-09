<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Tenant\RetentionCalculatorService;

/**
 * Tests unitarios para RetentionCalculatorService.
 *
 * Verifica que los cálculos de retenciones fiscales colombianas (2026)
 * sean correctos en todos los escenarios posibles.
 *
 * IMPORTANTE: Estos tests NO requieren base de datos.
 * Los valores de config() se sobreescriben en setUp().
 *
 * Ejecutar con: php artisan test tests/Unit/RetentionCalculatorServiceTest.php
 */
class RetentionCalculatorServiceTest extends TestCase
{
    private RetentionCalculatorService $service;

    // Valores base oficiales Colombia 2026
    private const BASE_FUENTE  = 524000;
    private const BASE_ICA     = 1418800;
    private const BASE_IVA     = 300000;

    protected function setUp(): void
    {
        parent::setUp();

        // Configurar valores fijos para que los tests sean deterministas
        // (independientes del archivo config/facturacion.php del entorno)
        config([
            'facturacion.retentions.base_amounts.fuente' => self::BASE_FUENTE,
            'facturacion.retentions.base_amounts.ica'    => self::BASE_ICA,
            'facturacion.retentions.base_amounts.iva'    => self::BASE_IVA,
            'facturacion.retentions.percentages.fuente'  => 0.025,
            'facturacion.retentions.percentages.ica'     => 0.001104,
            'facturacion.retentions.percentages.iva'     => 0.15,
            'facturacion.retentions.ica_cities'          => ['Bogotá, D.C.'],
            'facturacion.retentions.iva_fiscal_responsibilities' => [5],
        ]);

        $this->service = new RetentionCalculatorService();
    }

    // =========================================================================
    // TESTS RETENCIÓN EN LA FUENTE
    // =========================================================================

    /** @test */
    public function no_aplica_fuente_si_subtotal_es_menor_a_la_base_minima()
    {
        // $523,999 < $524,000 → NO se retiene
        $resultado = $this->service->calculateRetentionSource('COMMON_REGIME', 0, 523999.00);

        $this->assertEquals(0.00, $resultado,
            'No debe retenerse en la fuente si el subtotal es menor a $524.000'
        );
    }

    /** @test */
    public function no_aplica_fuente_si_subtotal_es_exactamente_igual_a_la_base_minima()
    {
        // Exactamente $524,000 → SÍ debe retenerse (condición >=)
        $resultado = $this->service->calculateRetentionSource('COMMON_REGIME', 0, 524000.00);

        $esperado = round(524000.00 * 0.025, 2);
        $this->assertEquals($esperado, $resultado,
            'Debe aplicar retención cuando el subtotal es exactamente igual a la base mínima'
        );
    }

    /** @test */
    public function aplica_fuente_correctamente_a_regimen_comun()
    {
        // $1,000,000 * 2.5% = $25,000
        $resultado = $this->service->calculateRetentionSource('COMMON_REGIME', 0, 1000000.00);

        $this->assertEquals(25000.00, $resultado,
            'Para régimen común con $1.000.000 la retención debe ser $25.000'
        );
    }

    /** @test */
    public function aplica_fuente_a_regimen_especial_con_responsabilidad_fiscal_5()
    {
        // Régimen especial + responsabilidad 5 (Gran contribuyente) = SÍ retiene
        $resultado = $this->service->calculateRetentionSource('SPECIAL_REGIME', 5, 1000000.00);

        $this->assertEquals(25000.00, $resultado,
            'Para régimen especial con responsabilidad fiscal 5 sí debe retenerse'
        );
    }

    /** @test */
    public function no_aplica_fuente_a_regimen_especial_sin_responsabilidad_5()
    {
        // Régimen especial + responsabilidad 7 = NO retiene
        $resultado = $this->service->calculateRetentionSource('SPECIAL_REGIME', 7, 1000000.00);

        $this->assertEquals(0.00, $resultado,
            'Para régimen especial sin responsabilidad fiscal 5 NO debe retenerse'
        );
    }

    /** @test */
    public function no_aplica_fuente_a_regimen_simplificado()
    {
        $resultado = $this->service->calculateRetentionSource('SIMPLIFIED_REGIME', 0, 1000000.00);
        $this->assertEquals(0.00, $resultado, 'El régimen simplificado NO causaría retención en la fuente');
    }

    /** @test */
    public function no_aplica_fuente_con_regimen_vacio()
    {
        $resultado = $this->service->calculateRetentionSource('', 0, 2000000.00);
        $this->assertEquals(0.00, $resultado, 'Con régimen vacío no debe calcularse retención');
    }

    /** @test */
    public function fuente_se_redondea_correctamente_a_dos_decimales()
    {
        // $1,111,111 * 2.5% = $27,777.775 → redondeado: $27,777.78
        $resultado = $this->service->calculateRetentionSource('COMMON_REGIME', 0, 1111111.00);

        $this->assertEquals(27777.78, $resultado,
            'La retención debe redondearse con PHP_ROUND_HALF_UP a 2 decimales'
        );
    }

    // =========================================================================
    // TESTS RETENCIÓN ICA
    // =========================================================================

    /** @test */
    public function no_aplica_ica_si_ciudad_no_es_bogota()
    {
        $resultado = $this->service->calculateRetentionIca('Medellín', 'COMMON_REGIME', 2000000.00);

        $this->assertEquals(0.00, $resultado,
            'ICA solo aplica para Bogotá, D.C. — Medellín no debe causar ICA'
        );
    }

    /** @test */
    public function no_aplica_ica_si_subtotal_es_menor_a_la_base()
    {
        // $1,418,799 < $1,418,800 → NO se retiene ICA
        $resultado = $this->service->calculateRetentionIca('Bogotá, D.C.', 'COMMON_REGIME', 1418799.00);

        $this->assertEquals(0.00, $resultado,
            'No debe calcularse ICA si el subtotal no supera la base mínima'
        );
    }

    /** @test */
    public function aplica_ica_correctamente_para_bogota_regimen_comun()
    {
        // $2,000,000 * 0.1104% = $2,208
        $resultado = $this->service->calculateRetentionIca('Bogotá, D.C.', 'COMMON_REGIME', 2000000.00);

        $esperado = round(2000000.00 * 0.001104, 2);
        $this->assertEquals($esperado, $resultado);
        $this->assertEquals(2208.00, $resultado);
    }

    /** @test */
    public function aplica_ica_para_regimen_especial_en_bogota()
    {
        $resultado = $this->service->calculateRetentionIca('Bogotá, D.C.', 'SPECIAL_REGIME', 2000000.00);

        $this->assertEquals(2208.00, $resultado,
            'ICA también aplica para régimen especial en Bogotá'
        );
    }

    /** @test */
    public function no_aplica_ica_para_regimen_simplificado()
    {
        $resultado = $this->service->calculateRetentionIca('Bogotá, D.C.', 'SIMPLIFIED_REGIME', 5000000.00);

        $this->assertEquals(0.00, $resultado);
    }

    // =========================================================================
    // TESTS RETENCIÓN IVA
    // =========================================================================

    /** @test */
    public function no_aplica_reteiva_si_responsabilidad_fiscal_no_es_5()
    {
        // Solo gran contribuyente (responsabilidad 5) causa ReteIVA
        $sinReteIva = $this->service->calculateRetentionIva(7, 1000000.00);

        $this->assertEquals(0.00, $sinReteIva,
            'ReteIVA solo aplica para responsabilidad fiscal 5 (Gran contribuyente)'
        );
    }

    /** @test */
    public function aplica_reteiva_correctamente_para_gran_contribuyente()
    {
        // $1,000,000 * 15% = $150,000
        $resultado = $this->service->calculateRetentionIva(5, 1000000.00);

        $this->assertEquals(150000.00, $resultado,
            'Para gran contribuyente (fiscal 5) la ReteIVA es 15% del subtotal'
        );
    }

    /** @test */
    public function no_aplica_reteiva_si_subtotal_menor_a_base()
    {
        // $299,999 < $300,000 → NO se retiene IVA
        $resultado = $this->service->calculateRetentionIva(5, 299999.00);

        $this->assertEquals(0.00, $resultado);
    }

    // =========================================================================
    // TESTS PARA calculateAllRetentions() — Integración de los 3 cálculos
    // =========================================================================

    /** @test */
    public function calcula_todas_las_retenciones_para_gran_contribuyente_bogota()
    {
        $datos = [
            'regime_description'   => 'COMMON_REGIME',
            'fiscal_responsability' => 5,                // Gran contribuyente
            'city'                  => 'Bogotá, D.C.',
            'sub_total'             => 2000000.00,
        ];

        $resultado = $this->service->calculateAllRetentions($datos);

        $this->assertArrayHasKey('retention_fuente', $resultado);
        $this->assertArrayHasKey('retention_ica', $resultado);
        $this->assertArrayHasKey('retention_iva', $resultado);

        $this->assertEquals(50000.00,  $resultado['retention_fuente']); // 2M * 2.5%
        $this->assertEquals(2208.00,   $resultado['retention_ica']);    // 2M * 0.1104%
        $this->assertEquals(300000.00, $resultado['retention_iva']);    // 2M * 15%
    }

    /** @test */
    public function retorna_cero_en_todas_las_retenciones_para_datos_vacios()
    {
        $resultado = $this->service->calculateAllRetentions([]);

        $this->assertEquals(0.00, $resultado['retention_fuente']);
        $this->assertEquals(0.00, $resultado['retention_ica']);
        $this->assertEquals(0.00, $resultado['retention_iva']);
    }

    // =========================================================================
    // TESTS PARA calculateFinalTotal()
    // =========================================================================

    /** @test */
    public function calcula_total_final_restando_todas_las_retenciones()
    {
        $totalConImpuestos = 2380000.00; // Total con IVA
        $retenciones = [
            'retention_fuente' => 50000.00,
            'retention_ica'    => 2208.00,
            'retention_iva'    => 300000.00,
        ];

        $totalFinal = $this->service->calculateFinalTotal($totalConImpuestos, $retenciones);

        // 2,380,000 - 50,000 - 2,208 - 300,000 = 2,027,792
        $this->assertEquals(2027792.00, $totalFinal,
            'El total final debe restar todas las retenciones al total con impuestos'
        );
    }

    /** @test */
    public function calcula_total_final_sin_retenciones_retorna_el_mismo_valor()
    {
        $total = 1500000.00;
        $sinRetenciones = [
            'retention_fuente' => 0.00,
            'retention_ica'    => 0.00,
            'retention_iva'    => 0.00,
        ];

        $resultado = $this->service->calculateFinalTotal($total, $sinRetenciones);
        $this->assertEquals(1500000.00, $resultado);
    }
}
