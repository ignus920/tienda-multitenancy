<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;
// use Illuminate\Support\Facades\Auth;
// use App\Services\Tenant\TenantManager;

class ImportServices extends Component
{
    use WithPagination;

    public $showImportList = false;
    public $selectedService = '';
    public $showModalRegisItem = false;

    // Servicios de importación disponibles
    public $importServices = [
        'items' => [
            'name' => 'Importar Items',
            'description' => 'Importar productos desde archivo Excel o CSV',
            'icon' => 'package',
            'enabled' => true
        ],
        'customers' => [
            'name' => 'Importar Clientes',
            'description' => 'Importar clientes desde archivo Excel o CSV',
            'icon' => 'users',
            'enabled' => true
        ],
        'suppliers' => [
            'name' => 'Importar Proveedores',
            'description' => 'Importar proveedores desde archivo Excel o CSV',
            'icon' => 'truck',
            'enabled' => false
        ],
        'inventory' => [
            'name' => 'Importar Inventario',
            'description' => 'Importar cantidades de inventario inicial',
            'icon' => 'clipboard-list',
            'enabled' => false
        ]
    ];

    public function selectService($service)
    {
        $this->selectedService = $service;

        if ($service === 'items') {
            $this->showImportList = true;
        } else {
            $this->showImportList = false;
        }
    }

    public function hideImportList()
    {
        $this->showImportList = false;
        $this->selectedService = '';
    }

    public function showModalRegis()
    {
        $this->showModalRegisItem = true;
    }

    #[On('closeItemsImportModal')]
    public function cancel()
    {
        $this->showModalRegisItem = false;
    }

    public function render()
    {
        return view('livewire.tenant.imports.components.import-services');
    }
}
