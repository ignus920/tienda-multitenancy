<?php

namespace App\Livewire\Tenant\Inventory;

use Livewire\Component;
use App\Models\Tenant\Items\Command as CommandModel;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Carbon\Carbon;


class Command extends Component
{
    public $command_id, $name, $print_path, $status, $created_at;

    //Propiedades para la tabla
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $confirmingItemDeletion = false;
    public $categorieIdToDelete;
    public $perPage = 10;

    protected $rules =[
        'name' => 'required|min:3',
        'print_path' => 'required|min:3',
    ];

    public function resetForm()
    {
        $this->name = '';
        $this->print_path = '';
        $this->status = '';
        $this->created_at = null;
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

    public function create()
    {
        $this->resetExcept(['categories', 'types']);
        $this->showModal = true;
    }

    public function edit($id)
    {
        $this->ensureTenantConnection();
        $command = CommandModel::findOrFail($id);
        $this->name = $command->name;
        $this->command_id=$command->id;
        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.tenant.inventory.command');
    }
}
