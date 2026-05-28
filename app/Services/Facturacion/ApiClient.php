<?php

namespace App\Services\Facturacion;

use App\Models\Auth\Tenant;
use App\Services\Facturacion\TenantConfigManager;
use App\Services\Facturacion\DatabaseConfigService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;

class ApiClient
{
    protected $baseUrl;
    protected $token;
    protected $username;
    protected $timeout;
    /** @var bool Indica si la URL apunta al proxy intermediario (no a Alegra directamente) */
    protected bool $isProxy = false;
    /** @var string|null URL de Alegra (sandbox o producción) que el proxy debe usar */
    protected ?string $alegraBaseUrl = null;

    public function __construct($baseUrl = null, $token = null, $username = null, $timeout = 15, bool $isProxy = false, ?string $alegraBaseUrl = null)
    {
        $this->baseUrl      = $baseUrl;
        $this->token        = $token;
        $this->username     = $username ?: '';
        $this->timeout      = $timeout;
        $this->isProxy      = $isProxy;
        $this->alegraBaseUrl = $alegraBaseUrl;
    }

    /**
     * Crear una instancia del cliente con configuración de tenant
     */
    public static function forTenant(?Tenant $tenant = null): self
    {
        if ($tenant) {
            // Usar TenantConfigManager para obtener configuración desde BD
            $config = TenantConfigManager::getFacturacionConfig($tenant);

            if ($config) {
                $token    = $config['token'] ?? null;
                $baseUrl  = $config['base_url'] ?? null;
                $username = $config['username'] ?? null;

                // Detectar si se usa proxy intermediario (facturador configurado)
                // Proxy → token en header "token" + header "base-url" apuntando a Alegra
                // Directo → Authorization: Basic <token>
                $isProxy      = !empty($config['facturador']);
                $alegraBaseUrl = $isProxy
                    ? DatabaseConfigService::resolveBaseUrl($config['base'] ?? 'Produccion')
                    : null;

                Log::info('🔑 [ApiClient] Configuración cargada para tenant', [
                    'tenant_id'       => $tenant->id,
                    'tenant_name'     => $tenant->name,
                    'base_url'        => $baseUrl,
                    'is_proxy'        => $isProxy,
                    'alegra_base_url' => $alegraBaseUrl,
                    'base_env'        => $config['base'] ?? 'Produccion',
                    'warehouse_id'    => $config['warehouse_id'] ?? null,
                    'facturador'      => $config['facturador'] ?? null,
                    'numeracion'      => $config['numeracion'] ?? null,
                    'source'          => $config['source'] ?? 'unknown',
                    'token_present'   => !empty($token),
                    'token_length'    => $token ? strlen($token) : 0,
                    'token_preview'   => $token ? substr($token, 0, 12) . '...' . substr($token, -6) : null,
                    'username'        => $username ?: '(vacío)',
                    'timeout'         => $config['timeout'] ?? 90,
                ]);

                return new self(
                    $baseUrl,
                    $token,
                    $username,
                    $config['timeout'] ?? 90,
                    $isProxy,
                    $alegraBaseUrl
                );
            }
        }

        // Si no hay configuración, lanzar excepción en lugar de usar valores por defecto
        throw new \Exception('No se encontró configuración de facturación para el tenant. Configure la tabla cnf_invoices.');
    }

    /**
     * Crear una instancia del cliente a partir de un array de configuración
     * (resultado de DatabaseConfigService::getFacturacionConfigByUser / getFacturacionConfigFromDatabase)
     *
     * Detecta automáticamente si la URL apunta al proxy propio ('facturador' configurado)
     * y ajusta los headers de autenticación en consecuencia.
     */
    public static function forConfig(array $config): self
    {
        $isProxy      = !empty($config['facturador']);
        $alegraBaseUrl = $isProxy
            ? DatabaseConfigService::resolveBaseUrl($config['base'] ?? 'Produccion')
            : null;

        Log::info('🔑 [ApiClient] forConfig() - configuración cargada', [
            'base_url'        => $config['base_url'] ?? null,
            'is_proxy'        => $isProxy,
            'alegra_base_url' => $alegraBaseUrl,
            'base_env'        => $config['base'] ?? 'Produccion',
            'facturador'      => $config['facturador'] ?? null,
            'token_present'   => !empty($config['token']),
            'source'          => $config['source'] ?? 'unknown',
        ]);

        return new self(
            $config['base_url']  ?? null,
            $config['token']     ?? null,
            $config['username']  ?? null,
            $config['timeout']   ?? 30,
            $isProxy,
            $alegraBaseUrl
        );
    }

    /**
     * Realizar una petición HTTP
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        $startTime = microtime(true);

        try {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

            $headers = [];
            if ($this->isProxy) {
                // Proxy intermediario: espera el token en header "token" y la URL de Alegra en "base-url"
                if ($this->token) {
                    $headers['token'] = $this->token;
                }
                if ($this->alegraBaseUrl) {
                    $headers['base-url'] = $this->alegraBaseUrl;
                }
            } else {
                // Alegra directo: HTTP Basic Auth → Authorization: Basic base64(email:token)
                // El campo 'token' en cnf_invoices ya contiene el valor base64 completo
                if ($this->token) {
                    $headers['Authorization'] = 'Basic ' . $this->token;
                }
                if ($this->username) {
                    $headers['username'] = $this->username;
                }
            }

            Log::info('📡 [ApiClient] Enviando petición a API', [
                'method'          => strtoupper($method),
                'url'             => $url,
                'base_url'        => $this->baseUrl,
                'is_proxy'        => $this->isProxy,
                'alegra_base_url' => $this->alegraBaseUrl,
                'token_present'   => !empty($this->token),
                'token_length'    => $this->token ? strlen($this->token) : 0,
                'token_preview'   => $this->token
                    ? substr($this->token, 0, 12) . '...' . substr($this->token, -6)
                    : '❌ SIN TOKEN',
                'username'        => $this->username ?: '(vacío)',
                'auth_header'     => $this->isProxy
                    ? 'token: ***'
                    : (isset($headers['Authorization']) ? 'Basic ***' : '❌ SIN Authorization'),
                'header_keys'     => array_keys($headers),
                'data_keys'       => array_keys($data),
                'data_payload'    => $data,
                'timeout'         => $this->timeout,
            ]);

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->$method($url, $data);

            $responseData = $response->json();
            $statusCode = $response->status();
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::info('📡 [ApiClient] Respuesta de API', [
                'status_code'     => $statusCode,
                'success'         => $response->successful(),
                'response_time_ms'=> $responseTime,
                'response_id'     => $responseData['id'] ?? null,
                'message'         => $responseData['message'] ?? null,
                'response_size'   => strlen(json_encode($responseData)),
            ]);

            // Log detallado para cualquier error (401, 403, 422, 500, etc.)
            if (!$response->successful()) {
                Log::error('❌ [ApiClient] Error en respuesta', [
                    'status_code'   => $statusCode,
                    'endpoint'      => $endpoint,
                    'url'           => $url,
                    'token_preview' => $this->token
                        ? substr($this->token, 0, 12) . '...' . substr($this->token, -6)
                        : '❌ SIN TOKEN',
                    'username'      => $this->username ?: '(vacío)',
                    'full_response' => $responseData,
                    'raw_body'      => $response->body(),
                    'request_data'  => $data,
                ]);
            }

            $result = [
                'success' => $response->successful(),
                'status' => $statusCode,
                'data' => $responseData,
                'message' => $responseData['message'] ?? null,
                'response_time' => $responseTime,
                'request_info' => [
                    'method' => strtoupper($method),
                    'url' => $url,
                    'data_sent' => count($data) > 0
                ]
            ];

            // Añadir información adicional para errores
            if (!$response->successful()) {
                $result['error_details'] = [
                    'status_code' => $statusCode,
                    'status_text' => $this->getHttpStatusText($statusCode),
                    'response_body' => $responseData,
                    'request_url' => $url,
                    'request_method' => strtoupper($method)
                ];

                // Agregar información de validación si está disponible
                if ($statusCode === 422 && isset($responseData['errors'])) {
                    $result['validation_errors'] = $responseData['errors'];
                }
            }

            return $result;

        } catch (Exception $e) {
            $responseTime = round((microtime(true) - $startTime) * 1000, 2);

            // Categorizar el error
            $errorType = $this->categorizeError($e);

            Log::error('❌ Error en petición a API de facturación', [
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'url' => rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
                'error_type' => $errorType['type'],
                'error' => $e->getMessage(),
                'response_time_ms' => $responseTime,
                'timeout_configured' => $this->timeout,
                'data_size' => count($data),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'status' => $errorType['http_code'],
                'data' => null,
                'message' => $errorType['user_message'],
                'response_time' => $responseTime,
                'error_details' => [
                    'error_type' => $errorType['type'],
                    'original_message' => $e->getMessage(),
                    'request_url' => rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/'),
                    'request_method' => strtoupper($method),
                    'timeout_used' => $this->timeout
                ]
            ];
        }
    }

    /**
     * Categorizar el tipo de error
     */
    private function categorizeError(Exception $e): array
    {
        $message = $e->getMessage();

        // Errores de conexión cURL
        if (str_contains($message, 'Connection refused') ||
            str_contains($message, 'Could not resolve host') ||
            str_contains($message, 'Failed to connect') ||
            str_contains($message, 'Couldn\'t connect to server') ||
            str_contains($message, 'cURL error 7')) {
            return [
                'type' => 'connection',
                'http_code' => 503,
                'user_message' => 'No se puede conectar al servidor de facturación. Verifique que el servicio esté disponible'
            ];
        }

        // Errores de timeout
        if (str_contains($message, 'timeout') ||
            str_contains($message, 'timed out') ||
            str_contains($message, 'Operation timed out') ||
            str_contains($message, 'cURL error 28')) {
            return [
                'type' => 'timeout',
                'http_code' => 408,
                'user_message' => 'La petición al servidor de facturación ha expirado. Intente nuevamente'
            ];
        }

        // Errores SSL/TLS
        if (str_contains($message, 'SSL') ||
            str_contains($message, 'certificate') ||
            str_contains($message, 'cURL error 35') ||
            str_contains($message, 'cURL error 60')) {
            return [
                'type' => 'ssl',
                'http_code' => 495,
                'user_message' => 'Error de certificado SSL en la conexión'
            ];
        }

        return [
            'type' => 'general',
            'http_code' => 500,
            'user_message' => 'Error de conexión: ' . $message
        ];
    }

    /**
     * Obtener texto descriptivo del código de estado HTTP
     */
    private function getHttpStatusText(int $code): string
    {
        $statusTexts = [
            200 => 'OK',
            201 => 'Created',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            408 => 'Request Timeout',
            422 => 'Unprocessable Entity',
            500 => 'Internal Server Error',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout'
        ];

        return $statusTexts[$code] ?? 'Unknown Status';
    }

    // =================== CATEGORÍAS ===================

    /**
     * Crear una nueva categoría
     */
    public function createCategory(array $data): array
    {
        return $this->makeRequest('post', 'categories', $data);
    }

    /**
     * Actualizar una categoría
     */
    public function updateCategory(int $id, array $data): array
    {
        return $this->makeRequest('put', "categories/{$id}", $data);
    }

    /**
     * Obtener categorías
     */
    public function getCategories(array $filters = []): array
    {
        return $this->makeRequest('get', 'categories', $filters);
    }

    /**
     * Obtener una categoría específica
     */
    public function getCategory(int $id): array
    {
        return $this->makeRequest('get', "categories/{$id}");
    }

    /**
     * Eliminar una categoría
     */
    public function deleteCategory(int $id): array
    {
        return $this->makeRequest('delete', "categories/{$id}");
    }

    // =================== PRODUCTOS/ITEMS ===================

    /**
     * Crear un nuevo producto/item
     */
    public function createItem(array $data): array
    {
        return $this->makeRequest('post', 'items', $data);
    }

    /**
     * Actualizar un producto/item
     */
    public function updateItem(int $id, array $data): array
    {
        return $this->makeRequest('put', "items/{$id}", $data);
    }

    /**
     * Obtener productos/items
     */
    public function getItems(array $filters = []): array
    {
        return $this->makeRequest('get', 'items', $filters);
    }

    /**
     * Obtener un producto/item específico
     */
    public function getItem(int $id): array
    {
        return $this->makeRequest('get', "items/{$id}");
    }

    /**
     * Eliminar un producto/item
     */
    public function deleteItem(int $id): array
    {
        return $this->makeRequest('delete', "items/{$id}");
    }

    // =================== CONTACTOS/CLIENTES ===================

    /**
     * Crear un nuevo contacto/cliente
     */
    public function createContact(array $data): array
    {
        return $this->makeRequest('post', 'contacts', $data);
    }

    /**
     * Actualizar un contacto/cliente
     */
    public function updateContact(int $id, array $data): array
    {
        return $this->makeRequest('put', "contacts/{$id}", $data);
    }

    /**
     * Obtener contactos/clientes
     */
    public function getContacts(array $filters = []): array
    {
        return $this->makeRequest('get', 'contacts', $filters);
    }

    /**
     * Obtener un contacto/cliente específico
     */
    public function getContact(int $id): array
    {
        return $this->makeRequest('get', "contacts/{$id}");
    }

    /**
     * Eliminar un contacto/cliente
     */
    public function deleteContact(int $id): array
    {
        return $this->makeRequest('delete', "contacts/{$id}");
    }

    // =================== FACTURAS ===================

    /**
     * Crear una nueva factura
     */
    public function createInvoice(array $data): array
    {
        return $this->makeRequest('post', 'invoices', $data);
    }

    /**
     * Actualizar una factura
     */
    public function updateInvoice(int $id, array $data): array
    {
        return $this->makeRequest('put', "invoices/{$id}", $data);
    }

    /**
     * Obtener facturas
     */
    public function getInvoices(array $filters = []): array
    {
        return $this->makeRequest('get', 'invoices', $filters);
    }

    /**
     * Obtener una factura específica
     */
    public function getInvoice(int $id): array
    {
        return $this->makeRequest('get', "invoices/{$id}");
    }

    /**
     * Eliminar una factura
     */
    public function deleteInvoice(int $id): array
    {
        return $this->makeRequest('delete', "invoices/{$id}");
    }

    /**
     * Emitir (stamp) facturas
     */
    public function stampInvoice(int $id): array
    {
        return $this->makeRequest('post', "invoices/stamp", ['ids' => [(string)$id]]);
    }

    // =================== AJUSTES DE INVENTARIO ===================

    /**
     * Crear un ajuste de inventario
     */
    public function createInventoryAdjustment(array $data): array
    {
        return $this->makeRequest('post', 'inventory-adjustments', $data);
    }

    /**
     * Eliminar un ajuste de inventario
     */
    public function deleteInventoryAdjustment(string $id): array
    {
        return $this->makeRequest('delete', "inventory-adjustments/{$id}");
    }

    // =================== NOTAS CRÉDITO ===================

    /**
     * Crear una nota crédito
     * El endpoint requiere el ID de la factura en Alegra como parámetro de ruta
     */
    public function createCreditNote(int|string $invoiceApiId, array $data): array
    {
        return $this->makeRequest('post', "credit-notes/{$invoiceApiId}", $data);
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl ?? '';
    }

    // =================== MÉTODOS GENÉRICOS ===================

    /**
     * Realizar una petición GET genérica
     */
    public function get(string $endpoint, array $params = []): array
    {
        return $this->makeRequest('get', $endpoint, $params);
    }

    /**
     * Realizar una petición POST genérica
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->makeRequest('post', $endpoint, $data);
    }

    /**
     * Realizar una petición PUT genérica
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->makeRequest('put', $endpoint, $data);
    }

    /**
     * Realizar una petición DELETE genérica
     */
    public function delete(string $endpoint): array
    {
        return $this->makeRequest('delete', $endpoint);
    }

    /**
     * Crear vendedor en la API
     */
    public function createSeller(array $sellerData): array
    {
        return $this->post('sellers', $sellerData);
    }

    /**
     * Actualizar vendedor en la API
     */
    public function updateSeller(string $sellerId, array $sellerData): array
    {
        return $this->put("sellers/{$sellerId}", $sellerData);
    }

    /**
     * Obtener vendedor por ID
     */
    public function getSeller(string $sellerId): array
    {
        return $this->get("sellers/{$sellerId}");
    }

    /**
     * Listar vendedores
     */
    public function getSellers(array $params = []): array
    {
        $queryString = !empty($params) ? '?' . http_build_query($params) : '';
        return $this->get("sellers{$queryString}");
    }


    /**
     * Eliminar vendedor
     */
    public function deleteSeller(string $sellerId): array
    {
        return $this->delete("sellers/{$sellerId}");
    }
}