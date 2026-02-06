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
     * Emitir (stamp) una factura en la API
     */
    public function stampInvoice(int $invoiceId): array
    {
        try {
            Log::info('📄 Emitiendo (stamp) factura en API de facturación', ['invoice_id' => $invoiceId]);
            return $this->apiClient->stampInvoice($invoiceId);
        } catch (\Exception $e) {
            Log::error('❌ Error emitiendo factura', [
                'invoice_id' => $invoiceId,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error emitiendo factura: ' . $e->getMessage()
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
     * Consultar factura en Alegra para obtener saldo pendiente
     */
    public function getInvoiceBalance(string $invoiceId): array
    {
        try {
            Log::info('🔍 Consultando saldo de factura en Alegra', [
                'invoice_id' => $invoiceId
            ]);

            $response = $this->apiClient->get("invoices/{$invoiceId}");

            if (isset($response['success']) && $response['success']) {
                $invoiceData = $response['data'] ?? [];
                $total = $invoiceData['total'] ?? 0;
                $balance = $invoiceData['balance'] ?? $total; // Saldo pendiente

                Log::info('💰 Saldo de factura obtenido', [
                    'invoice_id' => $invoiceId,
                    'total' => $total,
                    'balance' => $balance,
                    'paid_amount' => $total - $balance
                ]);

                return [
                    'success' => true,
                    'total' => $total,
                    'balance' => $balance,
                    'paid_amount' => $total - $balance
                ];
            } else {
                Log::error('❌ Error consultando factura en Alegra', [
                    'response' => $response
                ]);

                return [
                    'success' => false,
                    'error' => 'No se pudo consultar la factura en Alegra'
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción consultando factura en Alegra', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Error al consultar factura: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Registrar pago en Alegra
     */
    public function registerPayment(array $paymentData): array
    {
        try {
            Log::info('📤 Enviando pago a API de Alegra', [
                'payment_data' => $paymentData
            ]);

            $response = $this->apiClient->post('payments', $paymentData);

            Log::info('🔄 Respuesta raw de API de pagos', [
                'response' => $response
            ]);

            // Verificar estructura de respuesta según endpoints mostrados
            if (isset($response['success']) && $response['success']) {
                // Si la respuesta tiene estructura {success: true, data: {...}}
                $paymentId = $response['data']['id'] ?? $response['data'] ?? null;

                Log::info('✅ Pago registrado exitosamente en Alegra', [
                    'payment_id' => $paymentId,
                    'amount' => $paymentData['invoices'][0]['amount'] ?? null
                ]);

                return [
                    'success' => true,
                    'data' => $response['data'] ?? ['id' => $paymentId],
                    'message' => 'Pago registrado exitosamente en Alegra'
                ];
            }
            // Si la respuesta es solo el ID (según endpoint: return response()->json($result['data']['id']))
            else if (isset($response['data']) && is_numeric($response['data']) && $response['data'] > 0) {
                $paymentId = $response['data'];

                Log::info('✅ Pago registrado exitosamente en Alegra (ID directo)', [
                    'payment_id' => $paymentId,
                    'amount' => $paymentData['invoices'][0]['amount'] ?? null
                ]);

                return [
                    'success' => true,
                    'data' => ['id' => $paymentId],
                    'message' => 'Pago registrado exitosamente en Alegra'
                ];
            }
            // Si la respuesta es 0 o error
            else {
                Log::error('❌ Error en respuesta de API de pagos Alegra', [
                    'response' => $response
                ]);

                return [
                    'success' => false,
                    'error' => 'Error al registrar pago en Alegra (respuesta: 0 o inválida)',
                    'response' => $response
                ];
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción registrando pago en Alegra', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'error' => 'Error al conectar con API de pagos: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener el cliente API para uso directo si es necesario
     */
    public function getApiClient(): ApiClient
    {
        return $this->apiClient;
    }
}