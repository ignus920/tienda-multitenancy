<?php

namespace App\Http\Livewire\Tenant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Auth\Tenant;
use App\Traits\HasCompanyConfiguration;

class Dashboard extends Component
{
    use HasCompanyConfiguration;

    public $tenant;
    public $user;
    public $stats = [];

    public function mount()
    {
        $this->user = Auth::user();

        // Obtener tenant actual de la sesión
        $tenantId = Session::get('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $this->tenant = Tenant::find($tenantId);

        if (!$this->tenant) {
            Session::forget('tenant_id');
            return redirect()->route('tenant.select')->withErrors(['tenant' => 'Tenant no encontrado']);
        }

        // IMPORTANTE: Inicializar configuración de empresa
        $this->initializeCompanyConfiguration();

        // Cargar estadísticas básicas
        $this->loadStats();
    }

    protected function loadStats()
    {
        // Aquí puedes cargar estadísticas de la base del tenant
        // Por ahora dejamos datos de ejemplo
        $this->stats = [
            'total_ventas_hoy' => 0,
            'total_clientes' => 0,
            'total_productos' => 0,
            'ventas_mes' => 0,
        ];
    }

    public function switchTenant()
    {
        Session::forget('tenant_id');
        return redirect()->route('tenant.select');
    }

    public function logout()
    {
        Session::forget('tenant_id');
        Auth::logout();
        return redirect()->route('login');
    }

    /**
     * Verifica si debe mostrarse una funcionalidad específica según configuración
     */
    public function canShowFeature(int $optionId): bool
    {
        // Usar el trait para verificar si la opción está habilitada
        return $this->isOptionEnabled($optionId);
    }

    /**
     * Obtener las funcionalidades habilitadas para mostrar accesos rápidos
     * Completamente dinámico desde la base de datos - sin hardcodeo
     */
    public function getEnabledFeatures(): array
    {
        // Primero obtenemos todas las opciones habilitadas para esta empresa
        $enabledOptionIds = $this->getEnabledOptions();

        if (empty($enabledOptionIds)) {
            return [];
        }

        // Consultamos directamente a una tabla de catálogo de funcionalidades
        // Si no existe esta tabla, podemos obtener la info de forma básica
        $features = $this->getFeaturesFromDatabase($enabledOptionIds);

        return $features;
    }

    /**
     * Obtiene las funcionalidades desde la base de datos RAP
     * Consulta la información completa desde la BD de administración
     */
    private function getFeaturesFromDatabase(array $enabledOptionIds): array
    {
        if (empty($enabledOptionIds)) {
            return [];
        }

        try {
            // Consultar la información completa desde la base de datos RAP
            $features = DB::connection('mysql')->table('cnf_company_options as co')
                ->join('companies as c', 'co.company_id', '=', 'c.id')
                ->whereIn('co.option_id', $enabledOptionIds)
                ->where('co.value', 1)
                ->whereNull('co.deleted_at')
                ->select(
                    'co.option_id',
                    'co.opcion as name',
                    'co.modul as module',
                    'co.plain as plan',
                    'c.businessName'
                )
                ->get()
                ->keyBy('option_id')
                ->toArray();

            // Transformar a formato esperado por la vista
            return $this->transformFeaturesToViewFormat($features);

        } catch (\Exception $e) {
            Log::error("Error obteniendo features desde RAP: " . $e->getMessage());
            // Fallback: generar información básica desde los option_id
            return $this->generateBasicFeatures($enabledOptionIds);
        }
    }

    /**
     * Transforma la data de RAP al formato esperado por la vista
     */
    private function transformFeaturesToViewFormat(array $features): array
    {
        $transformed = [];

        foreach ($features as $feature) {
            $key = 'option_' . $feature->option_id;
            $transformed[$key] = [
                'option_id' => $feature->option_id,
                'name' => $feature->name ?? "Opción {$feature->option_id}",
                'module' => $feature->module ?? 'general',
                'route' => $this->getRouteForOption($feature->option_id, $feature->module ?? 'general'),
                'icon' => $this->getIconForModule($feature->module ?? 'general'),
                'color' => $this->getColorForModule($feature->module ?? 'general'),
            ];
        }

        return $transformed;
    }

    /**
     * Mapea rutas según el módulo y option_id
     */
    private function getRouteForOption(int $optionId, string $module): string
    {
        // Mapeo básico de rutas según módulos conocidos
        $moduleRoutes = [
            'ventas' => '#', // Ruta para ventas
            'clientes' => 'tenant.customers',
            'inventario' => '#',
            'caja' => '#',
            'informes' => '#',
            'compras' => '#',
            'parametros' => '#',
            'facturacion electronica' => '#',
            'notas credito' => '#',
        ];

        return $moduleRoutes[strtolower($module)] ?? '#';
    }

    /**
     * Obtiene icono según el módulo
     */
    private function getIconForModule(string $module): string
    {
        $moduleIcons = [
            'ventas' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z',
            'clientes' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z',
            'inventario' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
            'caja' => 'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
            'informes' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        ];

        return $moduleIcons[strtolower($module)] ?? 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z';
    }

    /**
     * Obtiene color según el módulo
     */
    private function getColorForModule(string $module): string
    {
        $moduleColors = [
            'ventas' => 'indigo',
            'clientes' => 'green',
            'inventario' => 'blue',
            'caja' => 'purple',
            'informes' => 'red',
            'compras' => 'yellow',
        ];

        return $moduleColors[strtolower($module)] ?? 'gray';
    }

    /**
     * Genera información básica de funcionalidades cuando no hay tabla catálogo
     */
    private function generateBasicFeatures(array $enabledOptionIds): array
    {
        $features = [];

        foreach ($enabledOptionIds as $optionId) {
            $features["option_$optionId"] = [
                'option_id' => $optionId,
                'name' => "Funcionalidad $optionId", // Nombre genérico
                'route' => '#', // Ruta por defecto
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z', // Icono por defecto
                'color' => 'gray', // Color por defecto
                'description' => "Opción del sistema ID: $optionId"
            ];
        }

        return $features;
    }

    /**
     * Ejemplos de verificaciones específicas de funcionalidades
     */
    public function canManageMultipleUsers(): bool
    {
        return $this->isOptionEnabled(1);
    }

    public function hasAdvancedReports(): bool
    {
        return $this->isOptionEnabled(2);
    }

    public function canAccessInventory(): bool
    {
        return $this->isOptionEnabled(16);
    }

    public function canManageEmployees(): bool
    {
        return $this->isOptionEnabled(33);
    }

    public function hasApiAccess(): bool
    {
        return $this->isOptionEnabled(51);
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tenant.dashboard', [
            'tenant' => $this->tenant,
            'stats' => $this->stats,
            'enabledFeatures' => $this->getEnabledFeatures(),
        ]);
    }
}
