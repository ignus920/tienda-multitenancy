<?php

namespace App\Livewire\Tenant\Invoices;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Invoices\VntInvoices;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class Invoices extends Component
{
    use WithPagination;

    public $search = '';
    public $perPage = 12;
    public $sortField = 'invoiceNumber';
    public $sortDirection = 'desc';

    public function boot()
    {
        // Establecer conexión tenant lo más pronto posible (antes de la hidratación de modelos)
        $this->ensureTenantConnection();
    }

    public function updatingSearch()
    {
        $this->resetPage();
        Log::info('🔄 Reseteando página por cambio en búsqueda', ['search' => $this->search]);
    }

    public function updatingPerPage()
    {
        $this->resetPage();
        Log::info('🔄 Reseteando página por cambio en perPage', ['perPage' => $this->perPage]);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
    }

    public function mount()
    {
        $this->ensureTenantConnection();
    }

    public function render()
    {
        try {
            $this->ensureTenantConnection();
            $invoices = VntInvoices::query()
                ->with(['quote.detalles', 'warehouse'])
                ->when($this->search, function ($query) {
                    $query->where('consecutive', 'like', '%' . $this->search . '%');
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate($this->perPage);
            return view('livewire.tenant.invoices.invoices', [
                'invoices' => $invoices
            ])->layout('layouts.app', ['header' => 'Remisiones']);
        } catch (\Exception $e) {
            Log::error('❌ Error consultando con la consulta: ' . $e->getMessage());
        }
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
}
