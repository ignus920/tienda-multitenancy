<?php

namespace App\Services\Facturacion;

use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class ApiClient
{
    protected $baseUrl;
    protected $token;
    protected $username;
    protected $timeout;

    public function __construct($baseUrl = null, $token = null, $username = null, $timeout = 30)
    {
        $this->baseUrl = $baseUrl ?: 'http://127.0.0.1:8000/api';
        $this->token = $token ?: 'dGljc2lhK2FsZWdyYUBhbGVncmEuY29tOmY3ODQyMTViNTgzYjk5NzU1MzBk';
        $this->username = $username ?: '';
        $this->timeout = $timeout;
    }

    /**
     * Crear una instancia del cliente con configuración de tenant
     */
    public static function forTenant(?Tenant $tenant = null): self
    {
        if ($tenant && isset($tenant->settings['facturacion'])) {
            $config = $tenant->settings['facturacion'];
            return new self(
                $config['base_url'] ?? null,
                $config['token'] ?? null,
                $config['username'] ?? null,
                $config['timeout'] ?? 30
            );
        }

        // Usar configuración global como fallback
        return new self();
    }

    /**
     * Realizar una petición HTTP
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): array
    {
        try {
            $url = rtrim($this->baseUrl, '/') . '/' . ltrim($endpoint, '/');

            $headers = [];
            if ($this->token) {
                $headers['token'] = $this->token;
            }
            if ($this->username) {
                $headers['username'] = $this->username;
            }

            Log::info('📡 Enviando petición a API de facturación', [
                'method' => strtoupper($method),
                'url' => $url,
                'headers' => array_keys($headers),
                'data_size' => count($data)
            ]);

            $response = Http::withHeaders($headers)
                ->timeout($this->timeout)
                ->$method($url, $data);

            $responseData = $response->json();
            $statusCode = $response->status();

            Log::info('📡 Respuesta de API de facturación', [
                'status_code' => $statusCode,
                'success' => $response->successful(),
                'response_id' => $responseData['id'] ?? null,
                'message' => $responseData['message'] ?? null
            ]);

            return [
                'success' => $response->successful(),
                'status' => $statusCode,
                'data' => $responseData,
                'message' => $responseData['message'] ?? null
            ];

        } catch (Exception $e) {
            Log::error('❌ Error en petición a API de facturación', [
                'method' => strtoupper($method),
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'status' => 500,
                'data' => null,
                'message' => 'Error de conexión: ' . $e->getMessage()
            ];
        }
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
}