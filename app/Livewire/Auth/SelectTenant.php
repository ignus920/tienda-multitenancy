<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

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

        // Redirigir al dashboard del tenant
        return redirect()->route('tenant.dashboard');
    }

    #[Layout('layouts.guest')]
    public function render()
    {
        return view('livewire.auth.select-tenant', [
            'tenants' => $this->tenants
        ]);
    }
}
