<?php

namespace App\Services\Tenant\Movements;

use App\Services\Facturacion\ApiClient;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Traits\HasCompanyConfiguration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovementsService
{
    use HasCompanyConfiguration;

    /**
     * ID de opción para facturación electrónica
     */
    const FACTURACION_ELECTRONICA_OPTION_ID = 8;

    public function __construct()
    {
        $this->initializeCompanyConfiguration();
    }

    /**
     * Verifica si el tenant tiene facturación electrónica habilitada
     */
    public function canUseElectronicInvoicing(): array
    {
        $result = [
            'allowed' => false,
            'reason'  => 'Plan no verificado',
            'message' => 'No se pudo verificar el plan del tenant'
        ];

        try {
            if (!$this->currentCompanyId) {
                $result['reason']  = 'No company';
                $result['message'] = 'No se encontró información de la empresa';
                return $result;
            }

            $optionValue = $this->getOptionValue(self::FACTURACION_ELECTRONICA_OPTION_ID);

            Log::info('🔍 [MovementsService] Verificando facturación electrónica', [
                'company_id'   => $this->currentCompanyId,
                'option_id'    => self::FACTURACION_ELECTRONICA_OPTION_ID,
                'option_value' => $optionValue,
                'enabled'      => $optionValue > 0
            ]);

            if ($optionValue === null) {
                $result['reason']  = 'Option not configured';
                $result['message'] = 'La facturación electrónica no está configurada para esta empresa';
                return $result;
            }

            if ($optionValue == 0) {
                $result['reason']  = 'Plan insufficient';
                $result['message'] = 'Su plan actual no incluye facturación electrónica';
                return $result;
            }

            $result = [
                'allowed'      => true,
                'reason'       => 'Plan valid',
                'message'      => 'Facturación electrónica habilitada',
                'option_value' => $optionValue
            ];

        } catch (\Exception $e) {
            Log::error('❌ [MovementsService] Error validando plan para facturación', [
                'error'      => $e->getMessage(),
                'company_id' => $this->currentCompanyId ?? 'NULL',
            ]);
            $result['reason']  = 'Validation error';
            $result['message'] = 'Error interno verificando permisos de facturación';
        }

        return $result;
    }

    /**
     * Obtener instancia del ApiClient configurado para el usuario actual
     */
    private function getApiClient(): ApiClient
    {
        $user = Auth::user();
        if (!$user) {
            throw new \Exception('No hay usuario autenticado');
        }

        $config = DatabaseConfigService::getFacturacionConfigByUser($user->id);

        if (!$config) {
            throw new \Exception("No se encontró configuración de facturación para el usuario: {$user->email}");
        }

        Log::info('🚀 [MovementsService] Configuración API obtenida', [
            'user_id'      => $user->id,
            'warehouse_id' => $config['warehouse_id'],
            'source'       => $config['source'],
        ]);

        return ApiClient::forConfig($config);
    }

    /**
     * Enviar ajuste de inventario a Alegra (POST inventory-adjustments)
     *
     * @param array $alegraData  { date, items: [{type, id, unitCost, quantity}], warehouse: {id} }
     * @return array { success, message, retryable, sync_skipped, api_data_id, api_response }
     */
    public function syncAdjustmentToApi(array $alegraData): array
    {
        $result = [
            'success'      => false,
            'message'      => 'Error desconocido',
            'error_type'   => 'unknown',
            'retryable'    => false,
            'api_response' => null,
        ];

        try {
            Log::info('🔄 [MovementsService] syncAdjustmentToApi INICIO', [
                'items_count' => count($alegraData['items'] ?? []),
                'date'        => $alegraData['date'] ?? null,
            ]);

            // Verificar si el módulo de facturación está habilitado
            $planValidation = $this->canUseElectronicInvoicing();

            if (!$planValidation['allowed']) {
                Log::info('💼 [MovementsService] Facturación no habilitada - solo local', [
                    'reason' => $planValidation['reason'],
                ]);
                return [
                    'success'      => true,
                    'message'      => 'Movimiento guardado localmente',
                    'error_type'   => null,
                    'retryable'    => false,
                    'api_response' => null,
                    'sync_skipped' => true,
                    'sync_reason'  => 'Facturación electrónica no habilitada',
                ];
            }

            // Si no hay items con alegraId, omitir sincronización silenciosamente
            if (empty($alegraData['items'])) {
                Log::info('💼 [MovementsService] Sin items con alegraId - solo local');
                return [
                    'success'      => true,
                    'message'      => 'Movimiento guardado localmente (ningún item tiene ID en Alegra)',
                    'error_type'   => null,
                    'retryable'    => false,
                    'api_response' => null,
                    'sync_skipped' => true,
                    'sync_reason'  => 'Sin items con alegraId',
                ];
            }

            $apiClient = $this->getApiClient();

            // Log completo del payload enviado a Alegra para diagnóstico
            Log::info('📤 [MovementsService] Payload COMPLETO enviado a Alegra', [
                'endpoint'       => 'POST inventory-adjustments',
                'alegra_data'    => $alegraData,
                'alegra_data_json' => json_encode($alegraData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                'items_detail'   => array_map(function ($item) {
                    return [
                        'type'     => $item['type'],
                        'id'       => $item['id'],
                        'id_type'  => gettype($item['id']),
                        'unitCost' => $item['unitCost'] ?? null,
                        'quantity' => $item['quantity'],
                    ];
                }, $alegraData['items'] ?? []),
                'warehouse'      => $alegraData['warehouse'] ?? null,
                'date'           => $alegraData['date'] ?? null,
            ]);

            $apiResult = $apiClient->createInventoryAdjustment($alegraData);


            $result['api_response'] = $apiResult;

            if ($apiResult['success']) {
                $apiDataId = $apiResult['data']['id'] ?? null;

                if ($apiDataId) {
                    $result = [
                        'success'      => true,
                        'message'      => 'Ajuste de inventario sincronizado exitosamente con Alegra',
                        'error_type'   => null,
                        'retryable'    => false,
                        'api_response' => $apiResult,
                        'api_data_id'  => $apiDataId,
                    ];

                    Log::info('✅ [MovementsService] Ajuste sincronizado con Alegra', [
                        'api_data_id' => $apiDataId,
                    ]);
                } else {
                    $result = [
                        'success'      => false,
                        'message'      => 'Alegra no retornó un identificador válido para el ajuste',
                        'error_type'   => 'invalid_response',
                        'retryable'    => true,
                        'api_response' => $apiResult,
                    ];
                }
            } else {
                $statusCode   = $apiResult['status'] ?? 500;
                $errorMessage = $apiResult['message'] ?? 'Error desconocido';

                $result = $this->buildErrorResult($statusCode, $errorMessage, $apiResult);

                Log::error('❌ [MovementsService] Error al crear ajuste en Alegra', [
                    'status_code' => $statusCode,
                    'error'       => $errorMessage,
                    'error_type'  => $result['error_type'],
                ]);
            }

        } catch (\Exception $e) {
            $result = $this->buildExceptionResult($e);
            Log::error('❌ [MovementsService] Excepción al sincronizar ajuste', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $result;
    }

    /**
     * Eliminar ajuste de inventario en Alegra (DELETE inventory-adjustments/{id})
     *
     * @param int $alegraId  ID del ajuste en Alegra
     * @return array { success, message, retryable, sync_skipped, api_response }
     */
    public function deleteAdjustmentFromApi(string $alegraId): array
    {
        $result = [
            'success'      => false,
            'message'      => 'Error desconocido',
            'error_type'   => 'unknown',
            'retryable'    => false,
            'api_response' => null,
        ];

        try {
            Log::info('🗑️ [MovementsService] deleteAdjustmentFromApi INICIO', [
                'alegra_id' => $alegraId,
            ]);

            // Verificar si el módulo de facturación está habilitado
            $planValidation = $this->canUseElectronicInvoicing();

            if (!$planValidation['allowed']) {
                Log::info('💼 [MovementsService] Facturación no habilitada - anulando solo local');
                return [
                    'success'      => true,
                    'message'      => 'Movimiento anulado localmente',
                    'error_type'   => null,
                    'retryable'    => false,
                    'api_response' => null,
                    'sync_skipped' => true,
                    'sync_reason'  => 'Facturación electrónica no habilitada',
                ];
            }

            $apiClient = $this->getApiClient();
            $apiResult = $apiClient->deleteInventoryAdjustment($alegraId);

            $result['api_response'] = $apiResult;

            if ($apiResult['success']) {
                $result = [
                    'success'      => true,
                    'message'      => 'Ajuste de inventario eliminado exitosamente en Alegra',
                    'error_type'   => null,
                    'retryable'    => false,
                    'api_response' => $apiResult,
                ];

                Log::info('✅ [MovementsService] Ajuste eliminado en Alegra', [
                    'alegra_id' => $alegraId,
                ]);
            } else {
                $statusCode   = $apiResult['status'] ?? 500;
                $errorMessage = $apiResult['message'] ?? 'Error desconocido';

                $result = $this->buildErrorResult($statusCode, $errorMessage, $apiResult);

                Log::error('❌ [MovementsService] Error al eliminar ajuste en Alegra', [
                    'alegra_id'   => $alegraId,
                    'status_code' => $statusCode,
                    'error'       => $errorMessage,
                ]);
            }

        } catch (\Exception $e) {
            $result = $this->buildExceptionResult($e);
            Log::error('❌ [MovementsService] Excepción al eliminar ajuste', [
                'alegra_id' => $alegraId,
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
        }

        return $result;
    }

    /**
     * Construir resultado de error según código HTTP
     */
    private function buildErrorResult(int $statusCode, string $errorMessage, array $apiResult): array
    {
        if ($statusCode === 422) {
            return [
                'success'      => false,
                'message'      => 'Error de validación en Alegra: ' . $errorMessage,
                'error_type'   => 'validation',
                'retryable'    => false,
                'api_response' => $apiResult,
            ];
        }

        if ($statusCode === 401) {
            return [
                'success'      => false,
                'message'      => 'Error de autenticación con Alegra. Verifique las credenciales de la API',
                'error_type'   => 'authentication',
                'retryable'    => false,
                'api_response' => $apiResult,
            ];
        }

        if ($statusCode === 404) {
            return [
                'success'      => false,
                'message'      => 'El ajuste de inventario no fue encontrado en Alegra',
                'error_type'   => 'not_found',
                'retryable'    => false,
                'api_response' => $apiResult,
            ];
        }

        if ($statusCode >= 500) {
            return [
                'success'      => false,
                'message'      => 'Error interno del servidor de Alegra. Intente nuevamente',
                'error_type'   => 'server_error',
                'retryable'    => true,
                'api_response' => $apiResult,
            ];
        }

        return [
            'success'      => false,
            'message'      => 'Error en Alegra: ' . $errorMessage,
            'error_type'   => 'api_error',
            'retryable'    => $statusCode >= 500 || $statusCode === 408,
            'api_response' => $apiResult,
        ];
    }

    /**
     * Construir resultado de error desde excepción
     */
    private function buildExceptionResult(\Exception $e): array
    {
        $message = $e->getMessage();

        if (str_contains($message, 'Connection refused') ||
            str_contains($message, 'Could not resolve host') ||
            str_contains($message, 'Failed to connect') ||
            str_contains($message, 'cURL error 7')) {
            return [
                'success'          => false,
                'message'          => 'No se puede conectar al servidor de Alegra. Verifique que el servicio esté disponible',
                'error_type'       => 'connection',
                'retryable'        => true,
                'api_response'     => null,
                'technical_details'=> $message,
            ];
        }

        if (str_contains($message, 'timeout') ||
            str_contains($message, 'timed out') ||
            str_contains($message, 'cURL error 28')) {
            return [
                'success'          => false,
                'message'          => 'La conexión con Alegra ha expirado. Intente nuevamente',
                'error_type'       => 'timeout',
                'retryable'        => true,
                'api_response'     => null,
                'technical_details'=> $message,
            ];
        }

        return [
            'success'          => false,
            'message'          => 'Error inesperado al conectar con Alegra: ' . $message,
            'error_type'       => 'exception',
            'retryable'        => false,
            'api_response'     => null,
            'technical_details'=> $message,
        ];
    }
}
