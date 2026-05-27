<?php

namespace App\Services\Tenant\Inventory;

use App\Models\Tenant\Items\Category;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use App\Services\Facturacion\DatabaseConfigService;
use App\Services\Facturacion\ApiClient;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Traits\HasCompanyConfiguration;

class CategoriesService
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
    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    public function createCategory(array $data){
        Log::info('🚀 INICIO createCategory', ['data' => $data]);

        $this->ensureTenantConnection();
        Log::info('🔗 Conexión tenant establecida');

        try {
            // Crear la categoría localmente
            $category = Category::create([
                'name' => $data['name'],
                'status' => $data['status'] ?? 1
            ]);

            Log::info('✅ Categoría creada localmente', [
                'category_id' => $category->id,
                'name' => $category->name
            ]);

            // Intentar sincronizar con API de facturación
            Log::info('🔄 Iniciando sincronización con API');
            $syncResult = $this->syncCategoryToApi($category);

            Log::info('📊 Resultado sincronización', $syncResult);

            // Mostrar mensaje al usuario según el resultado
            if ($syncResult['success']) {
                if (isset($syncResult['sync_skipped']) && $syncResult['sync_skipped']) {
                    // Sincronización omitida silenciosamente - mensaje simple
                    session()->flash('success', 'Categoría creada exitosamente');
                    Log::info('💾 Categoría creada solo localmente', [
                        'category_id' => $category->id,
                        'sync_reason' => $syncResult['sync_reason'] ?? 'N/A'
                    ]);
                } else {
                    
                    // Sincronización exitosa
                    session()->flash('success', 'Categoría creada y sincronizada exitosamente');
                }
            } else {
                // Solo mostrar errores técnicos reales
                $userMessage = $syncResult['message'];

                // Añadir detalles técnicos si existen (para desarrolladores)
                if (config('app.debug') && isset($syncResult['api_response']['message'])) {
                    $userMessage .= ' (Detalle técnico: ' . $syncResult['api_response']['message'] . ')';
                } elseif (config('app.debug') && isset($syncResult['technical_details'])) {
                    $userMessage .= ' (Detalle técnico: ' . $syncResult['technical_details'] . ')';
                }

                if ($syncResult['retryable']) {
                    session()->flash('warning', 'Categoría creada localmente. ' . $userMessage . '. La sincronización se reintentará automáticamente');
                } else {
                    session()->flash('error', 'Categoría creada localmente pero falló la sincronización: ' . $userMessage);
                }
            }

            return $category;
        } catch (\Exception $e) {
            Log::error('❌ Error en createCategory', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function updateCategory(Category $category, array $data)
    {
        Log::info('🔄 INICIO updateCategory', [
            'category_id' => $category->id,
            'original_name' => $category->name,
            'new_data' => $data
        ]);

        $this->ensureTenantConnection();

        // Guardar datos originales para comparar
        $originalName = $category->name;

        try {
            // Actualizar localmente
            $category->update([
                'name' => $data['name'] ?? $category->name,
                'status' => $data['status'] ?? $category->status,
            ]);

            Log::info('✅ Categoría actualizada localmente', [
                'category_id' => $category->id,
                'old_name' => $originalName,
                'new_name' => $category->name
            ]);

            // Sincronizar si cambió el nombre
            if (isset($data['name']) && $data['name'] !== $originalName) {
                Log::info('🔄 Nombre cambió, iniciando sincronización');
                $syncResult = $this->syncCategoryToApi($category);

                // Mostrar mensaje al usuario según el resultado
                if ($syncResult['success']) {
                    if (isset($syncResult['sync_skipped']) && $syncResult['sync_skipped']) {
                        // Sincronización omitida silenciosamente - mensaje simple
                        session()->flash('success', 'Categoría actualizada exitosamente');
                        Log::info('💾 Categoría actualizada solo localmente', [
                            'category_id' => $category->id,
                            'sync_reason' => $syncResult['sync_reason'] ?? 'N/A'
                        ]);
                    } else {
                        // Sincronización exitosa
                        session()->flash('success', 'Categoría actualizada y sincronizada exitosamente');
                    }
                } else {
                    // Solo mostrar errores técnicos reales
                    $userMessage = $syncResult['message'];

                    // Añadir detalles técnicos si existen (para desarrolladores)
                    if (config('app.debug') && isset($syncResult['api_response']['message'])) {
                        $userMessage .= ' (Detalle técnico: ' . $syncResult['api_response']['message'] . ')';
                    } elseif (config('app.debug') && isset($syncResult['technical_details'])) {
                        $userMessage .= ' (Detalle técnico: ' . $syncResult['technical_details'] . ')';
                    }

                    if ($syncResult['retryable']) {
                        session()->flash('warning', 'Categoría actualizada localmente. ' . $userMessage . '. La sincronización se reintentará automáticamente');
                    } else {
                        session()->flash('error', 'Categoría actualizada localmente pero falló la sincronización: ' . $userMessage);
                    }
                }
            } else {
                Log::info('📝 Sin cambios que requieran sincronización');
                session()->flash('success', 'Categoría actualizada exitosamente');
            }

            return $category;
        } catch (\Exception $e) {
            Log::error('❌ Error en updateCategory', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Verifica si el tenant tiene facturación electrónica habilitada
     */
    private function canUseElectronicInvoicing(): array
    {
        $result = [
            'allowed' => false,
            'reason' => 'Plan no verificado',
            'message' => 'No se pudo verificar el plan del tenant'
        ];

        try {
            if (!$this->currentCompanyId) {
                $result['reason'] = 'No company';
                $result['message'] = 'No se encontró información de la empresa';
                return $result;
            }

            // Obtener el valor de la opción de facturación electrónica
            $optionValue = $this->getOptionValue(self::FACTURACION_ELECTRONICA_OPTION_ID);

            Log::info('🔍 Verificando facturación electrónica', [
                'company_id' => $this->currentCompanyId,
                'option_id' => self::FACTURACION_ELECTRONICA_OPTION_ID,
                'option_value' => $optionValue,
                'enabled' => $optionValue > 0
            ]);

            // Si la opción no existe en la tabla, el tenant no tiene acceso
            if ($optionValue === null) {
                $result['reason'] = 'Option not configured';
                $result['message'] = 'La facturación electrónica no está configurada para esta empresa. Contacte al administrador';
                return $result;
            }

            // Si existe pero el value es 0, el tenant no tiene acceso
            if ($optionValue == 0) {
                $result['reason'] = 'Plan insufficient';
                $result['message'] = 'Su plan actual no incluye facturación electrónica. Contacte al administrador para actualizar su plan';
                return $result;
            }

            // Si value = 1 o cualquier valor > 0, tiene acceso
            if ($optionValue > 0) {
                $result = [
                    'allowed' => true,
                    'reason' => 'Plan valid',
                    'message' => 'Facturación electrónica habilitada',
                    'option_value' => $optionValue
                ];

                Log::info('✅ Facturación electrónica HABILITADA', [
                    'company_id' => $this->currentCompanyId,
                    'option_value' => $optionValue
                ]);
            } else {
                $result['reason'] = 'Invalid value';
                $result['message'] = 'Configuración de facturación electrónica inválida';

                Log::warning('⚠️ Valor de facturación inválido', [
                    'company_id' => $this->currentCompanyId,
                    'option_value' => $optionValue
                ]);
            }

        } catch (\Exception $e) {
            Log::error('❌ Error validando plan para facturación', [
                'error' => $e->getMessage(),
                'company_id' => $this->currentCompanyId ?? 'NULL',
                'trace' => $e->getTraceAsString()
            ]);

            $result['reason'] = 'Validation error';
            $result['message'] = 'Error interno verificando permisos de facturación';
        }

        return $result;
    }

    /**
     * Sincronizar categoría con API de facturación
     */
    public function syncCategoryToApi(Category $category): array
    {
        $result = [
            'success' => false,
            'message' => 'Error desconocido',
            'error_type' => 'unknown',
            'retryable' => false,
            'api_response' => null
        ];

        try {
            Log::info('🔄 syncCategoryToApi INICIO', ['category_id' => $category->id]);

            // PRIMERO: Verificar si el tenant tiene permisos para facturación electrónica
            $planValidation = $this->canUseElectronicInvoicing();

            if (!$planValidation['allowed']) {
                Log::info('💼 Facturación electrónica no habilitada - guardando solo local', [
                    'category_id' => $category->id,
                    'reason' => $planValidation['reason'],
                    'company_id' => $this->currentCompanyId
                ]);

                // Retorno silencioso - no hay error, simplemente no sincroniza
                return [
                    'success' => true,
                    'message' => 'Categoría guardada localmente',
                    'error_type' => null,
                    'retryable' => false,
                    'api_response' => null,
                    'sync_skipped' => true,
                    'sync_reason' => 'Facturación electrónica no habilitada'
                ];
            }

            Log::info('✅ Plan válido para facturación - iniciando sincronización', [
                'category_id' => $category->id,
                'company_id' => $this->currentCompanyId
            ]);

            // MÉTODO OPTIMIZADO: Usar user_id directamente para obtener configuración
            $user = Auth::user();
            if (!$user) {
                Log::error('❌ No hay usuario autenticado', [
                    'category_id' => $category->id
                ]);
                throw new \Exception("No hay usuario autenticado");
            }

            // Usar método optimizado que va directo: user → contact → warehouse → config
            $optimizedConfig = DatabaseConfigService::getFacturacionConfigByUser($user->id);

            if (!$optimizedConfig) {
                Log::error('❌ No se encontró configuración de facturación para usuario', [
                    'user_id' => $user->id,
                    'user_email' => $user->email,
                    'category_id' => $category->id
                ]);
                throw new \Exception("No se encontró configuración de facturación para el usuario: {$user->email}");
            }

            // Crear ApiClient con detección automática de proxy
            $apiClient = ApiClient::forConfig($optimizedConfig);

            Log::info('🚀 Usando configuración OPTIMIZADA (user → contact → warehouse)', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'contact_id' => $optimizedConfig['contact_id'],
                'warehouse_id' => $optimizedConfig['warehouse_id'],
                'source' => $optimizedConfig['source']
            ]);

            // Preparar datos para la API
            // La API requiere: name, description (no puede ser vacío) y status ("active"/"inactive")
            $apiData = [
                'name'        => $category->name,
                'description' => $category->description ?: $category->name, // fallback: usa el nombre si no hay descripción
                'status'      => $category->status ? 'active' : 'inactive',
            ];

            // Sincronizar (crear o actualizar) usando ApiClient directo
            if ($category->api_data_id) {
                Log::info('📝 Actualizando categoría en API', [
                    'category_id' => $category->id,
                    'api_data_id' => $category->api_data_id
                ]);
                $apiResult = $apiClient->updateCategory($category->api_data_id, $apiData);
            } else {
                Log::info('📝 Creando categoría en API', [
                    'category_id' => $category->id,
                    'name' => $category->name
                ]);
                $apiResult = $apiClient->createCategory($apiData);
            }

            $result['api_response'] = $apiResult;

            if ($apiResult['success']) {
                // Extraer el ID de la respuesta de la API
                // La API puede devolver el id como string ("93"), se castea a int
                $apiDataId = isset($apiResult['data']['id']) ? (int) $apiResult['data']['id'] : null;

                if ($apiDataId) {
                    $category->setApiId($apiDataId);

                    $result = [
                        'success' => true,
                        'message' => 'Categoría sincronizada exitosamente con el servidor de facturación',
                        'error_type' => null,
                        'retryable' => false,
                        'api_response' => $apiResult,
                        'api_data_id' => $apiDataId
                    ];

                    Log::info('✅ Categoría sincronizada exitosamente', [
                        'category_id' => $category->id,
                        'api_data_id' => $apiDataId,
                        'response' => $apiResult['data']
                    ]);
                } else {
                    $result = [
                        'success' => false,
                        'message' => 'El servidor de facturación no retornó un identificador válido',
                        'error_type' => 'invalid_response',
                        'retryable' => true,
                        'api_response' => $apiResult
                    ];

                    Log::error('❌ API no retornó ID válido', [
                        'category_id' => $category->id,
                        'response' => $apiResult['data']
                    ]);
                }
            } else {
                // Analizar el tipo de error según el código de estado
                $statusCode = $apiResult['status'] ?? 500;
                $errorMessage = $apiResult['message'] ?? 'Error desconocido';

                if ($statusCode === 422) {
                    $result = [
                        'success' => false,
                        'message' => 'Error de validación: ' . $errorMessage,
                        'error_type' => 'validation',
                        'retryable' => false,
                        'api_response' => $apiResult
                    ];
                } elseif ($statusCode === 401) {
                    $result = [
                        'success' => false,
                        'message' => 'Error de autenticación. Verifique las credenciales de la API',
                        'error_type' => 'authentication',
                        'retryable' => false,
                        'api_response' => $apiResult
                    ];
                } elseif ($statusCode === 404) {
                    $result = [
                        'success' => false,
                        'message' => 'La categoría no fue encontrada en el servidor de facturación',
                        'error_type' => 'not_found',
                        'retryable' => false,
                        'api_response' => $apiResult
                    ];
                } elseif ($statusCode >= 500) {
                    $result = [
                        'success' => false,
                        'message' => 'Error interno del servidor de facturación. Intente nuevamente',
                        'error_type' => 'server_error',
                        'retryable' => true,
                        'api_response' => $apiResult
                    ];
                } else {
                    $result = [
                        'success' => false,
                        'message' => 'Error del servidor: ' . $errorMessage,
                        'error_type' => 'api_error',
                        'retryable' => $statusCode >= 500 || $statusCode === 408,
                        'api_response' => $apiResult
                    ];
                }

                Log::error('❌ Error sincronizando categoría', [
                    'category_id' => $category->id,
                    'status_code' => $statusCode,
                    'error' => $errorMessage,
                    'error_type' => $result['error_type'],
                    'retryable' => $result['retryable'],
                    'response' => $apiResult
                ]);
            }

        } catch (\Exception $e) {
            // Analizar el tipo de excepción
            $errorMessage = $e->getMessage();

            // Crear un resultado base con información del error
            if (str_contains($errorMessage, 'Connection refused') ||
                str_contains($errorMessage, 'Could not resolve host') ||
                str_contains($errorMessage, 'Failed to connect') ||
                str_contains($errorMessage, 'Couldn\'t connect to server') ||
                str_contains($errorMessage, 'cURL error 7')) {
                $result = [
                    'success' => false,
                    'message' => 'No se puede conectar al servidor de facturación. Verifique que el servicio esté disponible',
                    'error_type' => 'connection',
                    'retryable' => true,
                    'api_response' => null,
                    'technical_details' => $errorMessage
                ];
            } elseif (str_contains($errorMessage, 'timeout') ||
                      str_contains($errorMessage, 'timed out') ||
                      str_contains($errorMessage, 'cURL error 28')) {
                $result = [
                    'success' => false,
                    'message' => 'La conexión al servidor de facturación ha expirado. Intente nuevamente',
                    'error_type' => 'timeout',
                    'retryable' => true,
                    'api_response' => null,
                    'technical_details' => $errorMessage
                ];
            } else {
                $result = [
                    'success' => false,
                    'message' => 'Error inesperado de conexión',
                    'error_type' => 'exception',
                    'retryable' => false,
                    'api_response' => null,
                    'technical_details' => $errorMessage
                ];
            }

            Log::error('❌ Excepción sincronizando categoría', [
                'category_id' => $category->id,
                'error' => $errorMessage,
                'error_type' => $result['error_type'],
                'retryable' => $result['retryable'],
                'technical_details' => $result['technical_details'],
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $result;
    }

    /**
     * Sincronizar todas las categorías pendientes
     */
    // public function syncPendingCategories(): array
    // {
    //     $this->ensureTenantConnection();

    //     $pendingCategories = Category::pendingSync()->get();
    //     $results = [
    //         'synced' => 0,
    //         'errors' => 0,
    //         'total' => $pendingCategories->count()
    //     ];

    //     foreach ($pendingCategories as $category) {
    //         if ($this->syncCategoryToApi($category)) {
    //             $results['synced']++;
    //         } else {
    //             $results['errors']++;
    //         }
    //     }

    //     Log::info('📊 Sincronización masiva de categorías completada', $results);
    //     return $results;
    // }

    /**
     * Obtener categorías con información de sincronización
     */
    // public function getCategoriesWithSyncInfo()
    // {
    //     $this->ensureTenantConnection();

    //     return Category::select([
    //         'id',
    //         'name',
    //         'status',
    //         'api_data_id',
    //         'sync_status',
    //         'last_synced_at',
    //         'sync_error',
    //         'created_at'
    //     ])->orderBy('name')->get();
    // }

    /**
     * Verificar si una categoría necesita sincronización
     */
    // public function needsSync(Category $category): bool
    // {
    //     return empty($category->api_data_id) ||
    //            $category->sync_status !== 'synced' ||
    //            $category->updated_at > $category->last_synced_at;
    // }

    /**
     * Obtener estadísticas de sincronización
     */
    // public function getSyncStats(): array
    // {
    //     $this->ensureTenantConnection();

    //     $total = Category::count();
    //     $synced = Category::synced()->count();
    //     $pending = Category::pendingSync()->count();
    //     $errors = Category::where('sync_status', 'error')->count();

    //     return [
    //         'total' => $total,
    //         'synced' => $synced,
    //         'pending' => $pending,
    //         'errors' => $errors,
    //         'sync_percentage' => $total > 0 ? round(($synced / $total) * 100, 1) : 0
    //     ];
    // }

    public function getActiveCategories()
    {
        return Category::where('status', 1)
            ->orderBy('name')
            ->get();
    }

    public function categoryExists($name)
    {
        return Category::where('name', $name)->exists();
    }


    public function deleteCategory($id)
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return $category;
    }

    public function getCategory($id)
    {
        return Category::findOrFail($id);
    }
}
