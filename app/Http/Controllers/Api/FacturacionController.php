<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FacturacionController extends Controller
{
    /**
     * Configurar facturación para el tenant actual
     */
    public function configure(Request $request): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el tenant'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'base_url' => 'required|url',
                'token' => 'required|string|min:10',
                'username' => 'nullable|string|max:255',
                'timeout' => 'nullable|integer|min:1|max:300',
                'enabled' => 'nullable|boolean'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $config = $validator->validated();
            $success = TenantConfigManager::setFacturacionConfig($tenant, $config);

            if ($success) {
                // Validar la configuración con una prueba de conexión
                $facturacionService = new FacturacionService($tenant);
                $validation = $facturacionService->validateConfiguration();

                return response()->json([
                    'success' => true,
                    'message' => 'Configuración guardada exitosamente',
                    'connection_test' => $validation
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar la configuración'
            ], 500);

        } catch (\Exception $e) {
            Log::error('❌ Error configurando facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Obtener configuración actual de facturación
     */
    public function getConfiguration(): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el tenant'
                ], 400);
            }

            $config = TenantConfigManager::getFacturacionConfig($tenant);
            $hasConfig = TenantConfigManager::hasFacturacionConfig($tenant);

            // No devolver el token completo por seguridad
            if ($config && isset($config['token'])) {
                $config['token_preview'] = substr($config['token'], 0, 10) . '***';
                unset($config['token']);
            }

            return response()->json([
                'success' => true,
                'config' => $config,
                'enabled' => $hasConfig,
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error obteniendo configuración de facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor'
            ], 500);
        }
    }

    /**
     * Probar conexión con la API de facturación
     */
    public function testConnection(): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo identificar el tenant'
                ], 400);
            }

            if (!TenantConfigManager::hasFacturacionConfig($tenant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facturación no está configurada para este tenant'
                ], 400);
            }

            $facturacionService = new FacturacionService($tenant);
            $validation = $facturacionService->validateConfiguration();

            return response()->json([
                'success' => $validation['valid'],
                'message' => $validation['message'],
                'details' => $validation
            ]);

        } catch (\Exception $e) {
            Log::error('❌ Error probando conexión de facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error probando conexión: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Crear una categoría vía API de facturación
     */
    public function createCategory(Request $request): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant || !TenantConfigManager::hasFacturacionConfig($tenant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facturación no configurada'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facturacionService = new FacturacionService($tenant);
            $result = $facturacionService->syncCategory($validator->validated());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('❌ Error creando categoría via facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando categoría'
            ], 500);
        }
    }

    /**
     * Crear un producto vía API de facturación
     */
    public function createProduct(Request $request): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant || !TenantConfigManager::hasFacturacionConfig($tenant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facturación no configurada'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'category' => 'nullable|integer',
                'reference' => 'nullable|string|max:100',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facturacionService = new FacturacionService($tenant);
            $result = $facturacionService->syncProduct($validator->validated());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('❌ Error creando producto via facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando producto'
            ], 500);
        }
    }

    /**
     * Crear un cliente vía API de facturación
     */
    public function createCustomer(Request $request): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant || !TenantConfigManager::hasFacturacionConfig($tenant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facturación no configurada'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'identification' => 'required|string|max:50',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facturacionService = new FacturacionService($tenant);
            $result = $facturacionService->syncCustomer($validator->validated());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('❌ Error creando cliente via facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando cliente'
            ], 500);
        }
    }

    /**
     * Crear una factura vía API de facturación
     */
    public function createInvoice(Request $request): JsonResponse
    {
        try {
            $tenant = tenancy()->tenant;
            if (!$tenant || !TenantConfigManager::hasFacturacionConfig($tenant)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Facturación no configurada'
                ], 400);
            }

            $validator = Validator::make($request->all(), [
                'contact' => 'required|integer',
                'date' => 'required|date',
                'dueDate' => 'required|date|after:date',
                'items' => 'required|array|min:1',
                'items.*.id' => 'required|integer',
                'items.*.price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|numeric|min:0.01',
                'observations' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Datos inválidos',
                    'errors' => $validator->errors()
                ], 422);
            }

            $facturacionService = new FacturacionService($tenant);
            $result = $facturacionService->createInvoice($validator->validated());

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('❌ Error creando factura via facturación', [
                'tenant_id' => tenancy()->tenant?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error creando factura'
            ], 500);
        }
    }
}