<?php

namespace App\Services\Facturacion;

use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;

class FacturacionService
{
    protected $apiClient;

    public function __construct(?Tenant $tenant = null)
    {
        $this->apiClient = ApiClient::forTenant($tenant);
    }

    /**
     * Crear una instancia del servicio para un tenant específico
     */
    public static function forTenant(Tenant $tenant): self
    {
        return new self($tenant);
    }

    /**
     * Sincronizar una categoría con la API de facturación
     */
    public function syncCategory(array $categoryData, ?int $remoteId = null): array
    {
        try {
            if ($remoteId) {
                Log::info('📝 Actualizando categoría en API de facturación', ['remote_id' => $remoteId]);
                return $this->apiClient->updateCategory($remoteId, $categoryData);
            } else {
                Log::info('📝 Creando categoría en API de facturación', ['category_name' => $categoryData['name'] ?? 'N/A']);
                return $this->apiClient->createCategory($categoryData);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error sincronizando categoría', [
                'error' => $e->getMessage(),
                'data' => $categoryData
            ]);
            return [
                'success' => false,
                'message' => 'Error sincronizando categoría: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sincronizar un producto con la API de facturación
     */
    public function syncProduct(array $productData, ?int $remoteId = null): array
    {
        try {
            if ($remoteId) {
                Log::info('📝 Actualizando producto en API de facturación', ['remote_id' => $remoteId]);
                return $this->apiClient->updateItem($remoteId, $productData);
            } else {
                Log::info('📝 Creando producto en API de facturación', ['product_name' => $productData['name'] ?? 'N/A']);
                return $this->apiClient->createItem($productData);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error sincronizando producto', [
                'error' => $e->getMessage(),
                'data' => $productData
            ]);
            return [
                'success' => false,
                'message' => 'Error sincronizando producto: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Sincronizar un cliente con la API de facturación
     */
    public function syncCustomer(array $customerData, ?int $remoteId = null): array
    {
        try {
            if ($remoteId) {
                Log::info('📝 Actualizando cliente en API de facturación', ['remote_id' => $remoteId]);
                return $this->apiClient->updateContact($remoteId, $customerData);
            } else {
                Log::info('📝 Creando cliente en API de facturación', ['customer_name' => $customerData['name'] ?? 'N/A']);
                return $this->apiClient->createContact($customerData);
            }
        } catch (\Exception $e) {
            Log::error('❌ Error sincronizando cliente', [
                'error' => $e->getMessage(),
                'data' => $customerData
            ]);
            return [
                'success' => false,
                'message' => 'Error sincronizando cliente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear una factura en la API de facturación
     */
    public function createInvoice(array $invoiceData): array
    {
        try {
            Log::info('📄 Creando factura en API de facturación', [
                'customer_id' => $invoiceData['contact'] ?? null,
                'items_count' => count($invoiceData['items'] ?? [])
            ]);

            return $this->apiClient->createInvoice($invoiceData);
        } catch (\Exception $e) {
            Log::error('❌ Error creando factura', [
                'error' => $e->getMessage(),
                'data' => $invoiceData
            ]);
            return [
                'success' => false,
                'message' => 'Error creando factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener el estado de una factura
     */
    public function getInvoiceStatus(int $invoiceId): array
    {
        try {
            return $this->apiClient->getInvoice($invoiceId);
        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo estado de factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error obteniendo estado de factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Validar configuración de facturación del tenant
     */
    public function validateConfiguration(): array
    {
        // Verificar que el tenant tenga configuración de facturación
        $config = $this->getFacturacionConfig();

        $errors = [];

        if (!isset($config['base_url']) || empty($config['base_url'])) {
            $errors[] = 'URL base de la API no configurada';
        }

        if (!isset($config['token']) || empty($config['token'])) {
            $errors[] = 'Token de autenticación no configurado';
        }

        if (!empty($errors)) {
            return [
                'valid' => false,
                'errors' => $errors,
                'message' => 'Configuración de facturación incompleta: ' . implode(', ', $errors)
            ];
        }

        // Probar conexión con la API
        try {
            $response = $this->apiClient->get('health-check');
            return [
                'valid' => $response['success'],
                'message' => $response['success'] ? 'Configuración válida' : 'Error de conexión con API'
            ];
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'message' => 'Error validando conexión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener la configuración de facturación actual
     */
    protected function getFacturacionConfig(): array
    {
        // Si se instanció sin tenant, usar configuración global
        if (!$this->apiClient) {
            return [
                'base_url' => config('app.facturacion_api_url', 'http://127.0.0.1:8000/api'),
                'token' => config('app.facturacion_token'),
                'username' => config('app.facturacion_username')
            ];
        }

        return [];
    }

    /**
     * Obtener el cliente API para uso directo si es necesario
     */
    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }
}