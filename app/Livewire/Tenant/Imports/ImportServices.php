<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpLabels;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
// use Livewire\Attributes\On;
// use Illuminate\Support\Facades\Auth;
// use App\Services\Tenant\TenantManager;

class ImportServices extends Component
{
    use WithPagination;

    public $showImportList = false;
    public $selectedService = '';
    
    // Variables para el item seleccionado
    public $selectedItemId = null;
    public $selectedItemQuantity = 0;
    public $selectedItemData = [];
    
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


        #[Computed]
    public function labels()
    {
        try {
            $this->ensureTenantConnection();
            $labels = ImpLabels::all();
            return $labels;
            
        } catch (\Exception $e) {
            Log::error('Error al obtener labels: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect(); // Retornar colección vacía en caso de error
        }
    }


    public function hideImportList()
    {
        $this->showImportList = false;
        $this->selectedService = '';
    }

    /**
     * Listener para cuando se selecciona un item de la lista
     */
    #[On('item-selected')]
    public function onItemSelected($data)
    {
        $this->selectedItemId = $data['itemId'];
        $this->selectedItemQuantity = $data['quantity'];
        $this->selectedItemData = $data;
        
        // Log para debug
        Log::info('=== ITEM SELECCIONADO EN IMPORT SERVICES ===');
        Log::info('Item ID: ' . $this->selectedItemId);
        Log::info('Cantidad: ' . $this->selectedItemQuantity);
        Log::info('Datos completos: ' . json_encode($this->selectedItemData));
        Log::info('=== FIN ITEM SELECCIONADO ===');
        
        // Aquí puedes agregar la lógica que necesites con el item seleccionado
        // Por ejemplo: mostrar un modal, actualizar otra vista, etc.
    }

    /**
     * Listener para cuando se actualiza la cantidad de un item
     */
    #[On('quantity-updated')]
    public function onQuantityUpdated($itemId, $quantity)
    {
        // Actualizar la cantidad si este item está seleccionado
        if ($this->selectedItemId == $itemId) {
            $this->selectedItemQuantity = $quantity;
        }
        
        // Log para debug
        Log::info('=== CANTIDAD ACTUALIZADA EN IMPORT SERVICES ===');
        Log::info('Item ID: ' . $itemId);
        Log::info('Nueva Cantidad: ' . $quantity);
        Log::info('=== FIN CANTIDAD ACTUALIZADA ===');
        
        // Aquí puedes agregar la lógica adicional que necesites
        // Por ejemplo: actualizar cálculos, mostrar notificaciones, etc.
    }

    public function render()
    {
          $labels = $this->labels; 
            $debugInfo = [
             'labels_count' => $labels->count(), // Agregar contador de labels
             'has_labels' => $labels->isNotEmpty(),
        ];
        return view('livewire.tenant.imports.components.import-services', [
            'labels' => $labels,
            'debugInfo' => $debugInfo
        ]);
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
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }
}
