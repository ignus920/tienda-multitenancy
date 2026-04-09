<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\Tenant\Movements\MovementsService;
use ReflectionMethod;
use Mockery;

/**
 * Tests unitarios para la lógica de manejo de errores del MovementsService.
 *
 * IMPORTANTE: Estos tests son 100% independientes de la base de datos.
 * Prueban la lógica de clasificación de errores HTTP y excepciones PHP
 * usando una subclase de prueba que evita el constructor con BD.
 *
 * Ejecutar con: php artisan test tests/Unit/MovementsServiceErrorHandlingTest.php
 */
class MovementsServiceErrorHandlingTest extends TestCase
{
    private MovementsService $service;
    private ReflectionMethod $buildErrorResult;
    private ReflectionMethod $buildExceptionResult;

    protected function setUp(): void
    {
        parent::setUp();

        // Creamos una subclase anónima que sobreescribe el constructor
        // para evitar la llamada a initializeCompanyConfiguration() que requiere BD.
        $this->service = new class extends MovementsService {
            public function __construct()
            {
                // Constructor vacío: omitimos toda inicialización de BD/Auth
            }
        };

        // Exponer el método privado buildErrorResult via Reflection
        $this->buildErrorResult = new ReflectionMethod(MovementsService::class, 'buildErrorResult');
        $this->buildErrorResult->setAccessible(true);

        // Exponer el método privado buildExceptionResult via Reflection
        $this->buildExceptionResult = new ReflectionMethod(MovementsService::class, 'buildExceptionResult');
        $this->buildExceptionResult->setAccessible(true);
    }

    // =========================================================================
    // TESTS PARA buildErrorResult() — Clasificación de errores HTTP de Alegra
    // =========================================================================

    /** @test */
    public function error_401_es_clasificado_como_authentication_no_reintentable()
    {
        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            401,
            'Unauthorized',
            ['status' => 401]
        );

        $this->assertFalse($resultado['success'], 'success debe ser false para error 401');
        $this->assertEquals('authentication', $resultado['error_type'],
            'El tipo debe ser authentication'
        );
        $this->assertFalse($resultado['retryable'],
            'Un error 401 NO debe reintentarse — es problema de credenciales, no temporal'
        );
        $this->assertStringContainsStringIgnoringCase('credenciales', $resultado['message']);
    }

    /** @test */
    public function error_422_es_clasificado_como_validation_no_reintentable()
    {
        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            422,
            'El campo items es requerido',
            []
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('validation', $resultado['error_type'],
            'El tipo debe ser validation'
        );
        $this->assertFalse($resultado['retryable'],
            'Un 422 (datos inválidos) NO debe reintentarse — el payload es incorrecto'
        );
        $this->assertStringContainsString('El campo items es requerido', $resultado['message']);
    }

    /** @test */
    public function error_404_es_clasificado_como_not_found_no_reintentable()
    {
        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            404,
            'Not Found',
            []
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('not_found', $resultado['error_type']);
        $this->assertFalse($resultado['retryable'],
            'Un 404 NO debe reintentarse — el recurso simplemente no existe'
        );
    }

    /** @test */
    public function error_500_es_clasificado_como_server_error_y_es_reintentable()
    {
        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            500,
            'Internal Server Error',
            []
        );

        $this->assertFalse($resultado['success']);
        $this->assertEquals('server_error', $resultado['error_type'],
            'El tipo debe ser server_error'
        );
        $this->assertTrue($resultado['retryable'],
            'Un error 500 SÍ debe reintentarse — puede ser una falla temporal'
        );
    }

    /** @test */
    public function error_503_es_clasificado_como_server_error_y_es_reintentable()
    {
        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            503,
            'Service Unavailable',
            []
        );

        $this->assertTrue($resultado['retryable'],
            'Un 503 (servicio no disponible) SÍ debe reintentarse'
        );
        $this->assertEquals('server_error', $resultado['error_type']);
    }

    /** @test */
    public function error_generico_incluye_el_mensaje_original_de_la_api()
    {
        $mensajeApi = 'El warehouse no existe en el sistema';

        $resultado = $this->buildErrorResult->invoke(
            $this->service,
            400,
            $mensajeApi,
            ['raw' => 'response data']
        );

        $this->assertFalse($resultado['success']);
        $this->assertStringContainsString($mensajeApi, $resultado['message'],
            'El mensaje de error devuelto por Alegra debe aparecer en el resultado'
        );
        $this->assertArrayHasKey('api_response', $resultado);
        $this->assertNotEmpty($resultado['api_response']);
    }

    /** @test */
    public function todos_los_errores_http_tienen_las_claves_requeridas_por_el_contrato()
    {
        $codigosHttp   = [400, 401, 404, 422, 500, 503];
        $clavesRequeridas = ['success', 'message', 'error_type', 'retryable', 'api_response'];

        foreach ($codigosHttp as $codigo) {
            $resultado = $this->buildErrorResult->invoke(
                $this->service,
                $codigo,
                'Error de prueba',
                []
            );

            foreach ($clavesRequeridas as $clave) {
                $this->assertArrayHasKey(
                    $clave,
                    $resultado,
                    "HTTP {$codigo}: falta la clave obligatoria '{$clave}' — los componentes Livewire dependen de ella"
                );
            }
        }
    }

    // =========================================================================
    // TESTS PARA buildExceptionResult() — Clasificación de excepciones PHP
    // =========================================================================

    /** @test */
    public function excepcion_de_conexion_rechazada_es_reintentable()
    {
        $excepcion = new \Exception('Connection refused to api.alegra.com:443');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('connection', $resultado['error_type'],
            'Connection refused debe clasificarse como error de conexión'
        );
        $this->assertTrue($resultado['retryable'],
            'Una conexión rechazada SÍ debe reintentarse — puede ser transitoria'
        );
    }

    /** @test */
    public function excepcion_de_timeout_curl28_es_reintentable()
    {
        $excepcion = new \Exception('cURL error 28: Operation timed out after 30000 milliseconds');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('timeout', $resultado['error_type']);
        $this->assertTrue($resultado['retryable'],
            'Un timeout SÍ debe reintentarse'
        );
    }

    /** @test */
    public function excepcion_de_host_no_resolvible_es_clasificada_como_conexion()
    {
        $excepcion = new \Exception('Could not resolve host: api.alegra.com');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertEquals('connection', $resultado['error_type']);
        $this->assertTrue($resultado['retryable']);
    }

    /** @test */
    public function excepcion_curl7_failed_to_connect_es_conexion()
    {
        $excepcion = new \Exception('cURL error 7: Failed to connect to api.alegra.com port 443');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertEquals('connection', $resultado['error_type']);
        $this->assertTrue($resultado['retryable']);
    }

    /** @test */
    public function excepcion_inesperada_es_clasificada_como_exception_no_reintentable()
    {
        $excepcion = new \Exception('Undefined variable: alegraItems en MovementsService línea 42');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertFalse($resultado['success']);
        $this->assertEquals('exception', $resultado['error_type'],
            'Una excepción de código debe ser tipo exception genérica'
        );
        $this->assertFalse($resultado['retryable'],
            'Un bug de PHP NO debe reintentarse — necesita corrección en el código'
        );
        $this->assertStringContainsString('Undefined variable', $resultado['message']);
    }

    /** @test */
    public function resultado_de_excepcion_siempre_incluye_technical_details_para_debugging()
    {
        $mensajeOriginal = 'TypeError: Argument 1 must be of type int, string given';
        $excepcion       = new \Exception($mensajeOriginal);

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertArrayHasKey('technical_details', $resultado,
            'technical_details es obligatorio para facilitar el debugging en producción'
        );
        $this->assertEquals($mensajeOriginal, $resultado['technical_details']);
    }

    /** @test */
    public function resultado_de_excepcion_siempre_tiene_api_response_en_null()
    {
        $excepcion = new \Exception('Cualquier error de red o código');

        $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

        $this->assertNull($resultado['api_response'],
            'Cuando hay una excepción PHP nunca hubo respuesta HTTP — debe ser null'
        );
    }

    /** @test */
    public function todas_las_excepciones_tienen_las_claves_del_contrato_de_respuesta()
    {
        $excepciones = [
            new \Exception('Connection refused'),
            new \Exception('cURL error 28: timed out'),
            new \Exception('Error inesperado genérico'),
        ];

        $clavesRequeridas = ['success', 'message', 'error_type', 'retryable', 'api_response'];

        foreach ($excepciones as $excepcion) {
            $resultado = $this->buildExceptionResult->invoke($this->service, $excepcion);

            foreach ($clavesRequeridas as $clave) {
                $this->assertArrayHasKey($clave, $resultado,
                    "Excepción '{$excepcion->getMessage()}': falta la clave '{$clave}'"
                );
            }
        }
    }
}
