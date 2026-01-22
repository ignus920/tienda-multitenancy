<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\Facturacion\ApiClient;
use App\Services\Facturacion\FacturacionService;

class TestApiErrors extends Command
{
    protected $signature = 'test:api-errors {type?}';
    protected $description = 'Test different API error scenarios';

    public function handle()
    {
        $type = $this->argument('type') ?? 'all';

        $this->info('🔥 Probando manejo de errores del API de Facturación');
        $this->line('');

        switch ($type) {
            case 'connection':
                $this->testConnectionError();
                break;
            case 'timeout':
                $this->testTimeoutError();
                break;
            case 'validation':
                $this->testValidationError();
                break;
            case 'auth':
                $this->testAuthenticationError();
                break;
            case 'all':
            default:
                $this->testConnectionError();
                $this->testTimeoutError();
                $this->testValidationError();
                $this->testAuthenticationError();
                break;
        }

        $this->line('');
        $this->info('✅ Pruebas completadas');
    }

    private function testConnectionError()
    {
        $this->line('📡 Probando error de conexión...');

        // Usar una URL que no existe para simular error de conexión
        $client = new ApiClient('http://servidor-inexistente.local:9999/api', 'test-token');

        $result = $client->createCategory([
            'name' => 'Categoría Test',
            'description' => 'Prueba de error de conexión'
        ]);

        $this->displayResult('Connection Error', $result);
    }

    private function testTimeoutError()
    {
        $this->line('⏰ Probando error de timeout...');

        // Usar un timeout muy corto para forzar timeout
        $client = new ApiClient('http://127.0.0.1:8010/api', 'test-token', '', 1);

        $result = $client->createCategory([
            'name' => 'Categoría Test Timeout',
            'description' => 'Prueba de error de timeout'
        ]);

        $this->displayResult('Timeout Error', $result);
    }

    private function testValidationError()
    {
        $this->line('✏️ Probando error de validación...');

        $client = new ApiClient();

        // Enviar datos vacíos para provocar error de validación
        $result = $client->createCategory([]);

        $this->displayResult('Validation Error', $result);
    }

    private function testAuthenticationError()
    {
        $this->line('🔐 Probando error de autenticación...');

        // Usar un token inválido
        $client = new ApiClient('http://127.0.0.1:8010/api', 'token-invalido');

        $result = $client->createCategory([
            'name' => 'Categoría Test Auth',
            'description' => 'Prueba de error de autenticación'
        ]);

        $this->displayResult('Authentication Error', $result);
    }

    private function displayResult($testName, $result)
    {
        $this->line("  🔍 {$testName}:");
        $this->line("    Success: " . ($result['success'] ? '✅ true' : '❌ false'));
        $this->line("    Status: {$result['status']}");
        $this->line("    Message: {$result['message']}");

        if (isset($result['error_details'])) {
            $this->line("    Error Type: " . ($result['error_details']['error_type'] ?? 'N/A'));
        }

        $this->line('');
    }
}