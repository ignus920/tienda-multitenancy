<?php

namespace App\Services\Tenant\Inventory;

use App\Models\Tenant\Items\Category;
use App\Services\Facturacion\FacturacionService;
use App\Services\Facturacion\TenantConfigManager;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CategoriesService
{
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

            Log::info('📊 Resultado sincronización', ['success' => $syncResult]);

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
                $this->syncCategoryToApi($category);
            } else {
                Log::info('📝 Sin cambios que requieran sincronización');
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
     * Sincronizar categoría con API de facturación
     */
    public function syncCategoryToApi(Category $category): bool
    {
        try {
            Log::info('🔄 syncCategoryToApi INICIO', ['category_id' => $category->id]);

            // En desarrollo, usar configuración global en lugar de tenant
            $facturacionService = new FacturacionService();
            Log::info('🔧 Usando configuración global de facturación');

            // Preparar datos para la API
            $apiData = [
                'name' => $category->name,
                'description' => $category->description ?? ''
            ];

            // Sincronizar (crear o actualizar)
            if ($category->api_data_id) {
                Log::info('📝 Actualizando categoría en API', [
                    'category_id' => $category->id,
                    'api_data_id' => $category->api_data_id
                ]);
                $result = $facturacionService->syncCategory($apiData, $category->api_data_id);
            } else {
                Log::info('📝 Creando categoría en API', [
                    'category_id' => $category->id,
                    'name' => $category->name
                ]);
                $result = $facturacionService->syncCategory($apiData);
            }

            if ($result['success']) {
                // Extraer el ID de la respuesta de la API
                $apiDataId = $result['data']['id'] ?? null;

                if ($apiDataId) {
                    $category->setApiId($apiDataId);

                    Log::info('✅ Categoría sincronizada exitosamente', [
                        'category_id' => $category->id,
                        'api_data_id' => $apiDataId,
                        'response' => $result['data']
                    ]);
                    return true;
                } else {
                    Log::error('❌ API no retornó ID válido', [
                        'category_id' => $category->id,
                        'response' => $result['data']
                    ]);
                    return false;
                }
            } else {
                $error = $result['message'] ?? 'Error desconocido';

                Log::error('❌ Error sincronizando categoría', [
                    'category_id' => $category->id,
                    'error' => $error,
                    'response' => $result
                ]);
                return false;
            }

        } catch (\Exception $e) {
            Log::error('❌ Excepción sincronizando categoría', [
                'category_id' => $category->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
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
