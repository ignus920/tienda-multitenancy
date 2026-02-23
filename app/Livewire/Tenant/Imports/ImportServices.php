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

    /**
     * Asignar etiqueta a un item por ID (sin necesidad de selección previa)
     */
    public function assignLabelToItemById($itemId, $labelId, $labelName)
    {
        try {
            $this->ensureTenantConnection();

            // Buscar el registro en imp_items_setup para obtener el precio
            $itemSetup = \App\Models\Tenant\Imports\ImpItemsSetup::where('item_id', $itemId)->first();

            // Buscar el registro en inv_unconfirmed_qty para obtener la cantidad
            $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::where('item_id', $itemId)->first();
            
            if (!$unconfirmedQty) {
                session()->flash('error', 'No se encontró la cantidad no confirmada para este item');
                return;
            }

            // Obtener información del item
            $item = \App\Models\Tenant\Items\Items::find($itemId);
            $itemSku = $item ? $item->sku : 'N/A';

            // Crear registro en imp_imports
            \App\Models\Tenant\Imports\ImpImports::create([
                'item_id' => $itemId,
                'user_id' => auth()->id(),
                'label_id' => $labelId,
                'qty_requested' => 0,
                'qty_shipped' => $unconfirmedQty->qty,
                'price' => $itemSetup ? ($itemSetup->exw ?? 0) : 0,
                'status' => 1,
                'shipping_id' => null,
            ]);

            Log::info('=== ETIQUETA ASIGNADA POR ID ===');
            Log::info('Item ID: ' . $itemId);
            Log::info('Item SKU: ' . $itemSku);
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            Log::info('qty_shipped: ' . $unconfirmedQty->qty);
            Log::info('price: ' . ($itemSetup ? ($itemSetup->exw ?? 0) : 0));
            Log::info('=== FIN REGISTRO ===');

            // Emitir evento para mostrar notificación de éxito
            $this->dispatch('label-assigned', [
                'itemId' => $itemId,
                'itemSku' => $itemSku,
                'labelId' => $labelId,
                'labelName' => $labelName
            ]);

            session()->flash('success', "Programación '{$labelName}' asignada correctamente al item {$itemSku}");

        } catch (\Exception $e) {
            Log::error('Error al asignar etiqueta por ID: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            session()->flash('error', 'Error al asignar la programación: ' . $e->getMessage());
        }
    }

    /**
     * Listener para cuando se hace clic en el input de cantidad con una etiqueta seleccionada
     */
    #[On('select-item-for-label')]
    public function selectItemForLabel($itemId, $labelId, $labelName)
    {
        try {
            Log::info('=== SELECT ITEM FOR LABEL ===');
            Log::info('Item ID: ' . $itemId);
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            
            // Emitir evento para que ImportList seleccione el item
            $this->dispatch('trigger-item-selection', ['itemId' => $itemId]);
            
            // Asignar la etiqueta
            $this->assignLabelToItem($labelId, $labelName);
            
        } catch (\Exception $e) {
            Log::error('Error en selectItemForLabel: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar la selección: ' . $e->getMessage());
        }
    }

    /**
     * Listener para cuando se cambia el valor del input con una etiqueta seleccionada
     */
    #[On('select-item-and-assign-label')]
    public function selectItemAndAssignLabel($itemId, $labelId, $labelName)
    {
        try {
            Log::info('=== SELECT ITEM AND ASSIGN LABEL ===');
            Log::info('Item ID: ' . $itemId);
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            
            // Emitir evento para que ImportList seleccione el item
            $this->dispatch('trigger-item-selection', ['itemId' => $itemId]);
            
            // Esperar un momento para que se seleccione el item
            // Luego asignar la etiqueta
            $this->assignLabelToItem($labelId, $labelName);
            
        } catch (\Exception $e) {
            Log::error('Error en selectItemAndAssignLabel: ' . $e->getMessage());
            session()->flash('error', 'Error al procesar la asignación: ' . $e->getMessage());
        }
    }

    /**
     * Asignar etiqueta a un item
     * Los datos del item y la etiqueta se pasan desde el componente hijo
     */
    /**
     * Asignar etiqueta a un item
     * Los datos del item y la etiqueta se pasan desde el componente hijo
     */
    public function assignLabelToItem($labelId, $labelName)
    {
        try {
            $this->ensureTenantConnection();

            // Validar que hay un item seleccionado
            if (!$this->selectedItemId) {
                session()->flash('error', 'No hay un item seleccionado');
                return;
            }

            // Buscar el registro en imp_items_setup para obtener el precio
            $itemSetup = \App\Models\Tenant\Imports\ImpItemsSetup::where('item_id', $this->selectedItemId)->first();

            // Buscar el registro en inv_unconfirmed_qty para obtener la cantidad
            $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::where('item_id', $this->selectedItemId)->first();
            
            if (!$unconfirmedQty) {
                session()->flash('error', 'No se encontró la cantidad no confirmada para este item');
                return;
            }

            // Crear registro en imp_imports
            \App\Models\Tenant\Imports\ImpImports::create([
                'item_id' => $this->selectedItemId,
                'user_id' => auth()->id(),
                'label_id' => $labelId,
                'qty_requested' => 0,
                'qty_shipped' => $unconfirmedQty->qty,
                'price' => $itemSetup ? ($itemSetup->exw ?? 0) : 0,
                'status' => 1,
                'shipping_id' => null,
            ]);

            Log::info('=== ETIQUETA ASIGNADA Y REGISTRO CREADO ===');
            Log::info('Item ID: ' . $this->selectedItemId);
            Log::info('Item SKU: ' . ($this->selectedItemData['sku'] ?? 'N/A'));
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            Log::info('qty_shipped: ' . $unconfirmedQty->qty);
            Log::info('price: ' . ($itemSetup ? ($itemSetup->exw ?? 0) : 0));
            Log::info('=== FIN REGISTRO ===');

            // Emitir evento para mostrar notificación de éxito
            $this->dispatch('label-assigned', [
                'itemId' => $this->selectedItemId,
                'itemSku' => $this->selectedItemData['sku'] ?? 'N/A',
                'labelId' => $labelId,
                'labelName' => $labelName
            ]);

            session()->flash('success', "Programación '{$labelName}' asignada correctamente al item {$this->selectedItemData['sku']}");

        } catch (\Exception $e) {
            Log::error('Error al asignar etiqueta: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            session()->flash('error', 'Error al asignar la programación: ' . $e->getMessage());
        }
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
