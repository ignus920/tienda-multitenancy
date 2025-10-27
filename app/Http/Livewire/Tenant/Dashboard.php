<?php

namespace App\Http\Livewire\Tenant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\Auth\Tenant;

class Dashboard extends Component
{
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

    #[Layout('layouts.app')]
    public function render()
    {
        return view('livewire.tenant.dashboard', [
            'tenant' => $this->tenant,
            'stats' => $this->stats,
        ]);
    }
}
