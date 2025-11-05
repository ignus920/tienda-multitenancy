<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Command  as CommandModel;
use Livewire\Attributes\On;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class Command extends Component
{
    public $commandId = '';
    public $name = 'commandId';
    public $placeholder = 'Seleccione una comanda';
    public $label = 'Comanda';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($commandId = '', $name = 'commandId', $placeholder = 'Seleccione una comanda', $label = 'Comanda', $required = true, $showLabel = true, $class = null)
    {
        $this->commandId = $commandId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedCommandId(){
        $this->dispatch('command-changed', $this->commandId);
    }

    public function getCommandsProperty()
    {
        $this->ensureTenantConnection();
        // Cargar todas las comandas desde la base de datos
        return CommandModel::where('status', 1)->get(['id', 'name']);
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

    public function render()
    {
        return view('livewire.tenant.items.command', [
            'commands' => $this->commands,
            'showLabel' => $this->showLabel
        ]);
    }
}
