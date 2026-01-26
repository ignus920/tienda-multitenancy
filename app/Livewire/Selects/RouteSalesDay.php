<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Auth\User;
use Livewire\Attributes\Computed;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class RouteSalesDay extends Component
{
    public $routeId = '';
    public $name = 'routeId';
    public $placeholder = 'Seleccionar ruta';
    public $label = 'Ruta';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-left bg-white cursor-default sm:text-sm py-2 pl-3 pr-10 relative';
    public $search = '';

    public function mount($routeId = '', $name = 'routeId', $placeholder = 'Seleccionar ruta', $label = 'Ruta', $required = true, $showLabel = true, $class = null)
    {
        $this->routeId = $routeId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function selectRoute($id)
    {
        $this->routeId = $id;
        $this->search = '';
        $this->dispatch('route-changed', routeId: $this->routeId);
    }

    public function updatedRouteId($value)
    {
        $this->dispatch('route-changed', routeId: $value);
    }

    #[Computed]
    public function selectedRouteName()
    {
        $this->ensureTenantConnection();
        if (!$this->routeId) return null;

        $route = \App\Models\Tenant\Parameters\VntRoutes::with(['salesman'])->find($this->routeId);

        if (!$route) return null;

        return ucfirst($route->sale_day) . ' - ' . ($route->salesman?->name ?? 'Sin vendedor') . ' - ' . $route->name;
    }

    #[Computed]
    public function routes()
    {
        $sessionTenant = $this->getTenantId();

        // Obtener los IDs de los vendedores (usuarios con profile_id = 4) desde la conexión central.
        $salesmanIds = \App\Models\Auth\User::on('central')
            ->where('profile_id', 4)
            ->whereHas('tenants', fn($q) => $q->where('tenants.id', $sessionTenant))
            ->pluck('id');

        // Construir la consulta principal en la conexión del tenant
        $query = \App\Models\Tenant\Parameters\VntRoutes::query()
            ->with([
                'zones', // Relación con zonas
                'salesman' => fn($query) => $query->select('id', 'name', 'email', 'profile_id'),
            ])
            ->whereIn('salesman_id', $salesmanIds);

        // Aplicar búsqueda si existe
        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sale_day', 'like', '%' . $this->search . '%')
                    ->orWhereHas('salesman', function ($salesmanQuery) {
                        // Este whereHas ahora debería funcionar correctamente al buscar por nombre de vendedor
                        $salesmanQuery->on('central')->where('name', 'like', '%' . $this->search . '%');
                    });
            });
        }

        return $query->orderBy('sale_day')
            ->orderBy('name')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.selects.route-sales-day');
    }

    private function getTenantId()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }
        return $tenantId;
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            throw new \Exception('No tenant selected');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            throw new \Exception('Invalid tenant');
        }

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
