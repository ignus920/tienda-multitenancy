<?php

namespace App\Auth\Livewire;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Auth\Livewire\Logout;

class SelectTenant extends Component
{
    public $tenants = [];
    public $selectedTenantId = null;

    public function mount()
    {
        // Verificar que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Si es Super Administrador, redirigir al dashboard global
        if (Auth::user()->isSuperAdmin()) {
            return redirect()->route('super.admin.dashboard');
        }

        // Obtener tenants activos del usuario
        $this->tenants = Auth::user()->activeTenants()->get();

        // Si solo tiene un tenant, redirigir automáticamente
        if ($this->tenants->count() === 1) {
            return $this->selectTenant($this->tenants->first()->id);
        }

        // Si no tiene tenants, mostrar mensaje
        if ($this->tenants->count() === 0) {
            session()->flash('error', 'No tiene acceso a ninguna empresa. Contacte al administrador.');
        }
    }

    public function selectTenant($tenantId)
    {
        // Verificar que el usuario tenga acceso al tenant
        if (!Auth::user()->hasAccessToTenant($tenantId)) {
            session()->flash('error', 'No tiene acceso a esta empresa.');
            return;
        }

        // Guardar tenant en sesión
        Session::put('tenant_id', $tenantId);

        // Actualizar último acceso
        $userTenant = Auth::user()->tenants()->where('tenant_id', $tenantId)->first();
        if ($userTenant) {
            $userTenant->pivot->update(['last_accessed_at' => now()]);
        }

        // Si el usuario es de perfil 18 (Cliente), omitir selección de bodega y redirigir directamente
        if (Auth::user()->profile_id == 18) {
            session()->forget('needs_warehouse_selection');
            session()->put('warehouse_redirect_route', 'tenant.client.portal');
            return redirect()->route('tenant.client.portal');
        }

        // Establecer bandera para abrir el modal de selección de bodega
        session()->put('needs_warehouse_selection', true);
        $redirectRoute = Auth::user()->profile_id == 17 ? 'imports.imports-orders' : 'tenant.dashboard';
        session()->put('warehouse_redirect_route', $redirectRoute);

        // Redirigir al destino para que el selector de bodega se abra sobre el dashboard/pantalla correspondiente y no sobre "Selecciona tu empresa"
        return redirect()->route($redirectRoute);
    }

    public function logout(Logout $logout)
    {
        $logout();
        return redirect()->route('login');
    }

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.auth.select-tenant', [
            'tenants' => $this->tenants
        ]);
    }
}
