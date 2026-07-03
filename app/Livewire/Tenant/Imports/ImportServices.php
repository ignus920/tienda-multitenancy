<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Imports\ImpLabels;
use App\Models\Tenant\Imports\ImpImports;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use App\Traits\Livewire\HasDynamicButtons;
// use Livewire\Attributes\On;
// use Illuminate\Support\Facades\Auth;
// use App\Services\Tenant\TenantManager;

class ImportServices extends Component
{
    use WithPagination, HasDynamicButtons;

    public $showImportList = false;
    public $selectedService = '';
    public $showModalRegisItem = false;
    public $moduleKey = 'imports';

    // Variables para el item seleccionado
    public $selectedItemId = null;
    public $selectedItemQuantity = 0;
    public $selectedItemData = [];
    public $selectedItemPriority = null;
    public $selectedItemPriorityDate = null;
    public $selectedItemPriorities = []; // Array de prioridades activas (ASAP, Second, Third) con sus cantidades y fechas
    public $selectedLabelId = null;
    public $selectedLabelName = 'Programación';

    // Array para almacenar las asignaciones del item seleccionado
    public $itemAssignments = [];

    // Array para almacenar las cantidades mensuales del item seleccionado
    public $monthlyQuantities = [];

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

    public function showModalRegis()
    {
        $this->showModalRegisItem = true;
    }
    public function cancel()
    {
        $this->showModalRegisItem = false;
    }

    #[Computed]
    public function labels()
    {
        try {
            $this->ensureTenantConnection();
            // Filtrar solo etiquetas con estado = 1 (activas)
            $labels = ImpLabels::where('status', 1)->get();
            return $labels;
        } catch (\Exception $e) {
            Log::error('Error al obtener labels: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect();
        }
    }

    /**
     * Obtener las asignaciones existentes para el item seleccionado
     */
    private function loadItemAssignments()
    {
        try {
            Log::info('=== INICIO loadItemAssignments ===');
            Log::info('Selected Item ID: ' . ($this->selectedItemId ?? 'NULL'));

            if (!$this->selectedItemId) {
                Log::info('No hay item seleccionado, limpiando asignaciones');
                $this->itemAssignments = [];
                return;
            }

            $this->ensureTenantConnection();
            Log::info('Conexión tenant establecida');

            // Consultar imp_imports para obtener las asignaciones del item
            Log::info('Consultando imp_imports para item_id: ' . $this->selectedItemId);
            $assignments = ImpImports::where('item_id', $this->selectedItemId)
                ->where('status', 1)
                ->whereNull('deleted_at')
                ->get(['label_id', 'qty_requested', 'qty_shipped'])
                ->keyBy('label_id')
                ->toArray();

            $this->itemAssignments = $assignments;

            Log::info('=== ASIGNACIONES CARGADAS ===');
            Log::info('Item ID: ' . $this->selectedItemId);
            Log::info('Total asignaciones: ' . count($this->itemAssignments));
            Log::info('Asignaciones: ' . json_encode($this->itemAssignments));
            Log::info('=== FIN ASIGNACIONES ===');
        } catch (\Exception $e) {
            Log::error('=== ERROR EN loadItemAssignments ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN ERROR ===');
            $this->itemAssignments = [];
        }
    }
    /**
     * Obtener las cantidades mensuales de remisiones para el item seleccionado
     * Retorna un array con los últimos 12 meses
     */
    private function loadMonthlyQuantities()
    {
        try {
            // Inicializar array con los últimos 12 meses móviles (del más antiguo al mes actual)
            $quantities = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $key = $date->format('Y-m');
                $quantities[$key] = [
                    'label' => $date->translatedFormat('M y'),
                    'qty' => 0
                ];
            }

            if (!$this->selectedItemId) {
                return $quantities;
            }

            $this->ensureTenantConnection();

            // Calcular el rango de fechas: últimos 12 meses
            $startDate = now()->startOfMonth()->subMonths(11)->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d 23:59:59');

            // Usar created_at como campo principal, con fallback a updated_at
            $monthlyData = DB::connection('tenant')
                ->table('inv_remissions as ir')
                ->join('inv_detail_remissions as idr', 'idr.remissionId', '=', 'ir.id')
                ->select(
                    DB::raw('YEAR(COALESCE(ir.created_at, ir.updated_at)) AS anio'),
                    DB::raw('MONTH(COALESCE(ir.created_at, ir.updated_at)) AS mes'),
                    DB::raw('SUM(idr.quantity) AS TotalQuantity')
                )
                ->where('idr.itemId', $this->selectedItemId)
                ->whereBetween(DB::raw('COALESCE(ir.created_at, ir.updated_at)'), [$startDate, $endDate])
                ->groupBy(DB::raw('YEAR(COALESCE(ir.created_at, ir.updated_at))'), DB::raw('MONTH(COALESCE(ir.created_at, ir.updated_at))'))
                ->get();

            // Llenar con los datos obtenidos
            foreach ($monthlyData as $data) {
                $key = $data->anio . '-' . str_pad($data->mes, 2, '0', STR_PAD_LEFT);
                if (isset($quantities[$key])) {
                    $quantities[$key]['qty'] = (int) $data->TotalQuantity;
                }
            }

            return $quantities;
        } catch (\Exception $e) {
            Log::error('Error en loadMonthlyQuantities: ' . $e->getMessage());
            
            // Generar fallback en caso de error
            $fallback = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $fallback[$date->format('Y-m')] = [
                    'label' => $date->translatedFormat('M y'),
                    'qty' => 0
                ];
            }
            return $fallback;
        }
    }


    /**
     * Verificar si un label está asignado al item seleccionado
     */
    public function isLabelAssigned($labelId)
    {
        return isset($this->itemAssignments[$labelId]);
    }

    /**
     * Obtener la cantidad solicitada para un label asignado
     */
    public function getAssignedQuantity($labelId)
    {
        return $this->itemAssignments[$labelId]['qty_requested'] ?? 0;
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
        Log::info('=== INICIO onItemSelected ===');
        Log::info('Data recibida: ' . json_encode($data));

        $this->selectedItemId = $data['itemId'];
        $this->selectedItemData = $data;
        $this->selectedItemPriority = $data['priority'] ?? null;
        $this->selectedItemPriorityDate = $data['priority_assigned_at'] ?? null;

        $this->ensureTenantConnection();
        if ($this->selectedLabelId) {
            $imp = ImpImports::where('item_id', $this->selectedItemId)
                ->where('label_id', $this->selectedLabelId)
                ->where('status', '<', 8)
                ->whereNull('deleted_at')
                ->first();
            $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
        } else {
            $imp = ImpImports::where('item_id', $this->selectedItemId)
                ->whereNull('label_id')
                ->where('status', '<', 8)
                ->whereNull('deleted_at')
                ->first();
            $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
        }

        Log::info('Selected Item ID establecido: ' . $this->selectedItemId);
        Log::info('Selected Item Quantity establecida desde imp_imports: ' . $this->selectedItemQuantity);

        // Cargar las asignaciones del item seleccionado
        Log::info('Cargando asignaciones del item...');
        $this->loadItemAssignments();

        // Cargar todas las prioridades activas del ítem para mostrarlas en el banner
        $this->selectedItemPriorities = ImpImports::where('item_id', $this->selectedItemId)
            ->where('status', '<', 8)
            ->whereNotNull('priority')
            ->whereNull('deleted_at')
            ->orderByRaw("FIELD(priority, 'ASAP', 'Second', 'Third')")
            ->get(['priority', 'qty_requested', 'priority_assigned_at'])
            ->toArray();

        // Cargar las cantidades mensuales del item seleccionado
        Log::info('Cargando cantidades mensuales del item...');
        $this->monthlyQuantities = $this->loadMonthlyQuantities();

        Log::info('=== ITEM SELECCIONADO EN IMPORT SERVICES ===');
        Log::info('Item ID: ' . $this->selectedItemId);
        Log::info('Cantidad: ' . $this->selectedItemQuantity);
        Log::info('Datos completos: ' . json_encode($this->selectedItemData));
        Log::info('Total asignaciones cargadas: ' . count($this->itemAssignments));
        Log::info('Cantidades mensuales: ' . json_encode($this->monthlyQuantities));
        Log::info('Prioridades activas encontradas: ' . json_encode($this->selectedItemPriorities));
        Log::info('=== FIN ITEM SELECCIONADO ===');
    }

    #[On('labelSelected')]
    #[On('label-selected')]
    public function onLabelSelected($labelId)
    {
        try {
            $this->ensureTenantConnection();
            
            $this->selectedLabelId = ($labelId == -1) ? null : (($labelId == 0) ? 0 : $labelId);
            
            // Buscar el nombre de la etiqueta
            if ($this->selectedLabelId === null) {
                $this->selectedLabelName = 'Programación';
            } else {
                $lbl = ImpLabels::find($this->selectedLabelId);
                $this->selectedLabelName = $lbl ? $lbl->name : 'Programación';
            }

            Log::info("Etiqueta sincronizada en el Padre - ID: " . ($this->selectedLabelId ?? 'NULL') . ", Name: " . $this->selectedLabelName);

            // Si hay un item seleccionado, actualizar su cantidad para esta etiqueta
            if ($this->selectedItemId) {
                if ($this->selectedLabelId) {
                    $imp = ImpImports::where('item_id', $this->selectedItemId)
                        ->where('label_id', $this->selectedLabelId)
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->first();
                    $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
                } else {
                    $imp = ImpImports::where('item_id', $this->selectedItemId)
                        ->whereNull('label_id')
                        ->where('status', '<', 8)
                        ->whereNull('deleted_at')
                        ->first();
                    $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
                }
                
                Log::info("Cantidad de item seleccionado actualizada para la nueva etiqueta: " . $this->selectedItemQuantity);
            }
        } catch (\Exception $e) {
            Log::error('Error en onLabelSelected del padre: ' . $e->getMessage());
        }
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
     * Refrescar la información y prioridades del item seleccionado actual
     */
    #[On('refresh-selected-item')]
    public function refreshSelectedItem()
    {
        if ($this->selectedItemId) {
            $this->ensureTenantConnection();
            
            // Recargar todas las prioridades activas para el banner
            $this->selectedItemPriorities = ImpImports::where('item_id', $this->selectedItemId)
                ->where('status', '<', 8)
                ->whereNotNull('priority')
                ->whereNull('deleted_at')
                ->orderByRaw("FIELD(priority, 'ASAP', 'Second', 'Third')")
                ->get(['priority', 'qty_requested', 'priority_assigned_at'])
                ->toArray();
                
            // Cargar asignaciones
            $this->loadItemAssignments();
            
            // Cargar cantidad del item seleccionado
            if ($this->selectedLabelId) {
                $imp = ImpImports::where('item_id', $this->selectedItemId)
                    ->where('label_id', $this->selectedLabelId)
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->first();
                $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
            } else {
                $imp = ImpImports::where('item_id', $this->selectedItemId)
                    ->whereNull('label_id')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->first();
                $this->selectedItemQuantity = $imp ? $imp->qty_requested : 0;
            }

            Log::info("Refrescado el item seleccionado {$this->selectedItemId}. Nuevas prioridades: " . json_encode($this->selectedItemPriorities));
        }
    }

    /**
     * Asignar etiqueta a un item por ID (sin necesidad de selección previa)
     */
    public function assignLabelToItemById($itemId, $labelId, $labelName)
    {
        try {
            Log::info('=== INICIO assignLabelToItemById ===');
            Log::info('Item ID recibido: ' . $itemId);
            Log::info('Label ID recibido: ' . $labelId);
            Log::info('Label Name recibido: ' . $labelName);

            $this->ensureTenantConnection();
            Log::info('Conexión tenant establecida');

            // Siempre permitir crear un nuevo registro de importación sin validar existencia previa
            Log::info('Procediendo con la inserción del nuevo pedido de importación...');

            $itemSetup = \App\Models\Tenant\Imports\ImpItemsSetup::where('item_id', $itemId)->first();
            Log::info('Item Setup encontrado: ' . ($itemSetup ? 'SI' : 'NO'));
            if ($itemSetup) {
                Log::info('Item Setup EXW: ' . ($itemSetup->exw ?? 'NULL'));
            }

            $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::withTrashed()->where('item_id', $itemId)->first();

            // Si no existe el registro, obtener la cantidad del item desde la query
            if (!$unconfirmedQty) {
                Log::warning('No se encontró registro en inv_unconfirmed_qty para item_id: ' . $itemId);

                // Obtener el item para ver su cantidad
                $item = \App\Models\Tenant\Items\Items::find($itemId);
                if (!$item) {
                    Log::error('Item no encontrado: ' . $itemId);
                    session()->flash('error', 'Item no encontrado');
                    return;
                }

                // Si es el item seleccionado, usar su cantidad
                $qtyToAssign = ($this->selectedItemId == $itemId) ? $this->selectedItemQuantity : 0;

                Log::info('Cantidad a usar: ' . $qtyToAssign);

                if ($qtyToAssign <= 0) {
                    Log::error('La cantidad a asignar es 0 o negativa');
                    session()->flash('error', 'La cantidad del item debe ser mayor a cero');
                    return;
                }

                // Crear el registro en inv_unconfirmed_qty
                Log::info('Creando registro en inv_unconfirmed_qty...');
                $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::create([
                    'item_id' => $itemId,
                    'qty' => $qtyToAssign,
                    'status' => true
                ]);
                Log::info('Registro inv_unconfirmed_qty creado con ID: ' . $unconfirmedQty->id);
            } else {
                if ($unconfirmedQty->trashed()) {
                    $unconfirmedQty->restore();
                }
                Log::info('Registro inv_unconfirmed_qty encontrado:');
                Log::info('  - ID: ' . $unconfirmedQty->id);
                Log::info('  - Item ID: ' . $unconfirmedQty->item_id);
                Log::info('  - Cantidad: ' . $unconfirmedQty->qty);

                $qtyToAssign = $unconfirmedQty->qty;
            }

            Log::info('Cantidad a asignar: ' . $qtyToAssign);

            $item = \App\Models\Tenant\Items\Items::find($itemId);
            $itemSku = $item ? $item->sku : 'N/A';
            Log::info('Item SKU: ' . $itemSku);

            // Crear el registro en imp_imports
            Log::info('Creando registro en imp_imports...');
            $newImport = ImpImports::create([
                'item_id' => $itemId,
                'user_id' => auth()->id(),
                'label_id' => $labelId,
                'qty_requested' => $qtyToAssign,
                'qty_shipped' => null,
                'price' => $itemSetup ? ($itemSetup->exw ?? 0) : 0,
                'status' => 1,
                'shipping_id' => null,
            ]);
            Log::info('Registro imp_imports creado con ID: ' . $newImport->id);
            Log::info('Datos del registro: ' . json_encode($newImport->toArray()));

            // Actualizar la cantidad a cero
            Log::info('Actualizando inv_unconfirmed_qty.qty a 0...');
            $unconfirmedQty->qty = 0;
            $unconfirmedQty->save();
            Log::info('Cantidad actualizada a 0 exitosamente');

            // Verificar la actualización
            $unconfirmedQtyCheck = \App\Models\Tenant\Imports\InvUnconfirmedQty::where('item_id', $itemId)->first();
            Log::info('Verificación - Cantidad DESPUÉS: ' . ($unconfirmedQtyCheck ? $unconfirmedQtyCheck->qty : 'REGISTRO NO ENCONTRADO'));

            // Recargar asignaciones si es el item seleccionado
            if ($this->selectedItemId == $itemId) {
                Log::info('Recargando asignaciones del item seleccionado...');
                $this->loadItemAssignments();
                Log::info('Asignaciones recargadas. Total: ' . count($this->itemAssignments));
            }

            Log::info('=== ETIQUETA ASIGNADA EXITOSAMENTE (BY ID) ===');
            Log::info('Item ID: ' . $itemId);
            Log::info('Item SKU: ' . $itemSku);
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            Log::info('Cantidad asignada: ' . $qtyToAssign);
            Log::info('inv_unconfirmed_qty actualizado a 0');
            Log::info('=== FIN ASIGNACIÓN EXITOSA (BY ID) ===');

            // Emitir evento para mostrar notificación de éxito
            $this->dispatch('label-assigned', [
                'itemId' => $itemId,
                'itemSku' => $itemSku,
                'labelId' => $labelId,
                'labelName' => $labelName
            ]);

            // Emitir evento para actualizar la cantidad a 0 en el input
            $this->dispatch('update-item-quantity', [
                'itemId' => $itemId,
                'quantity' => 0
            ]);

            // Forzar refresco de la tabla del listado
            $this->dispatch('refresh-import-list');

            // Si es el item seleccionado, limpiar la selección para cerrar el panel
            if ($this->selectedItemId == $itemId) {
                $this->selectedItemId = null;
                $this->selectedItemQuantity = 0;
                $this->selectedItemData = [];
                $this->itemAssignments = [];
                $this->monthlyQuantities = [];
            }

            session()->flash('success', "Programación '{$labelName}' asignada correctamente al item {$itemSku}");
            Log::info('=== FIN assignLabelToItemById (ÉXITO) ===');
        } catch (\Exception $e) {
            Log::error('=== ERROR EN assignLabelToItemById ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN ERROR ===');

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
            Log::info('=== INICIO assignLabelToItem ===');
            Log::info('Label ID recibido: ' . $labelId);
            Log::info('Label Name recibido: ' . $labelName);
            Log::info('Selected Item ID: ' . $this->selectedItemId);
            Log::info('Selected Item Data: ' . json_encode($this->selectedItemData));

            $this->ensureTenantConnection();
            Log::info('Conexión tenant establecida');

            // Validar que hay un item seleccionado
            if (!$this->selectedItemId) {
                Log::warning('No hay item seleccionado');
                session()->flash('error', 'No hay un item seleccionado');
                return;
            }

            // Verificar si ya existe una asignación
            // Siempre permitir crear un nuevo registro de importación sin validar existencia previa
            Log::info('Procediendo con la inserción del nuevo pedido de importación...');

            $itemSetup = \App\Models\Tenant\Imports\ImpItemsSetup::where('item_id', $this->selectedItemId)->first();
            Log::info('Item Setup encontrado: ' . ($itemSetup ? 'SI' : 'NO'));
            if ($itemSetup) {
                Log::info('Item Setup EXW: ' . ($itemSetup->exw ?? 'NULL'));
            }

            // Buscar el registro en inv_unconfirmed_qty para obtener la cantidad
            $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::withTrashed()->where('item_id', $this->selectedItemId)->first();

            // Si no existe el registro, usar la cantidad del selectedItemData
            if (!$unconfirmedQty) {
                Log::warning('No se encontró registro en inv_unconfirmed_qty para item_id: ' . $this->selectedItemId);
                Log::info('Usando cantidad de selectedItemData: ' . ($this->selectedItemQuantity ?? 0));

                $qtyToAssign = $this->selectedItemQuantity ?? 0;

                if ($qtyToAssign <= 0) {
                    Log::error('La cantidad a asignar es 0 o negativa');
                    session()->flash('error', 'La cantidad del item debe ser mayor a cero');
                    return;
                }

                // Crear el registro en inv_unconfirmed_qty para futuras asignaciones
                Log::info('Creando registro en inv_unconfirmed_qty...');
                $unconfirmedQty = \App\Models\Tenant\Imports\InvUnconfirmedQty::create([
                    'item_id' => $this->selectedItemId,
                    'qty' => $qtyToAssign,
                    'status' => true
                ]);
                Log::info('Registro inv_unconfirmed_qty creado con ID: ' . $unconfirmedQty->id);
            } else {
                if ($unconfirmedQty->trashed()) {
                    $unconfirmedQty->restore();
                }
                Log::info('Registro inv_unconfirmed_qty encontrado:');
                Log::info('  - ID: ' . $unconfirmedQty->id);
                Log::info('  - Item ID: ' . $unconfirmedQty->item_id);
                Log::info('  - Cantidad: ' . $unconfirmedQty->qty);

                $qtyToAssign = $unconfirmedQty->qty;
            }

            Log::info('Cantidad a asignar: ' . $qtyToAssign);

            // Crear el registro en imp_imports
            Log::info('Creando registro en imp_imports...');
            $newImport = ImpImports::create([
                'item_id' => $this->selectedItemId,
                'user_id' => auth()->id(),
                'label_id' => $labelId,
                'qty_requested' => $qtyToAssign,
                'qty_shipped' => null,
                'price' => $itemSetup ? ($itemSetup->exw ?? 0) : 0,
                'status' => 1,
                'shipping_id' => null,
            ]);
            Log::info('Registro imp_imports creado con ID: ' . $newImport->id);
            Log::info('Datos del registro: ' . json_encode($newImport->toArray()));

            // Actualizar la cantidad a cero en inv_unconfirmed_qty
            Log::info('Actualizando inv_unconfirmed_qty.qty a 0...');
            $unconfirmedQty->qty = 0;
            $unconfirmedQty->save();
            Log::info('Cantidad actualizada a 0 exitosamente');

            // Verificar la actualización
            $unconfirmedQtyCheck = \App\Models\Tenant\Imports\InvUnconfirmedQty::where('item_id', $this->selectedItemId)->first();
            Log::info('Verificación - Cantidad DESPUÉS: ' . ($unconfirmedQtyCheck ? $unconfirmedQtyCheck->qty : 'REGISTRO NO ENCONTRADO'));

            // Recargar asignaciones
            Log::info('Recargando asignaciones del item...');
            $this->loadItemAssignments();
            Log::info('Asignaciones recargadas. Total: ' . count($this->itemAssignments));

            Log::info('=== ETIQUETA ASIGNADA EXITOSAMENTE ===');
            Log::info('Item ID: ' . $this->selectedItemId);
            Log::info('Item SKU: ' . ($this->selectedItemData['sku'] ?? 'N/A'));
            Log::info('Label ID: ' . $labelId);
            Log::info('Label Name: ' . $labelName);
            Log::info('Cantidad asignada: ' . $qtyToAssign);
            Log::info('inv_unconfirmed_qty actualizado a 0');
            Log::info('=== FIN ASIGNACIÓN EXITOSA ===');

            // Guardar el SKU antes de limpiar selectedItemData
            $itemSku = $this->selectedItemData['sku'] ?? 'N/A';

            // Emitir evento para mostrar notificación de éxito
            $this->dispatch('label-assigned', [
                'itemId' => $this->selectedItemId,
                'itemSku' => $itemSku,
                'labelId' => $labelId,
                'labelName' => $labelName
            ]);

            // Emitir evento para actualizar la cantidad a 0 en el input
            $this->dispatch('update-item-quantity', [
                'itemId' => $this->selectedItemId,
                'quantity' => 0
            ]);

            // Limpiar el item seleccionado para cerrar el panel
            $this->selectedItemId = null;
            $this->selectedItemQuantity = 0;
            $this->selectedItemData = [];
            $this->itemAssignments = [];
            $this->monthlyQuantities = [];

            session()->flash('success', "Programación '{$labelName}' asignada correctamente al item {$itemSku}");
            Log::info('=== FIN assignLabelToItem (ÉXITO) ===');
        } catch (\Exception $e) {
            Log::error('=== ERROR EN assignLabelToItem ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Línea: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error('=== FIN ERROR ===');

            session()->flash('error', 'Error al asignar la programación: ' . $e->getMessage());
        }
    }

    /**
     * Actualizar la prioridad de un ítem individual
     */
    public function updateItemPriority($itemId, $newPriority)
    {
        try {
            $this->ensureTenantConnection();
            
             // Validar prioridad
             $validPriorities = ['ASAP', 'Second', 'Third', null];
             if (!in_array($newPriority, $validPriorities)) {
                 $newPriority = null;
             }

             // Validar que el ítem tenga cantidad mayor a 0 antes de asignar prioridad (solo si no es para quitar prioridad)
             if ($newPriority !== null) {
                 $qty = $this->selectedItemId == $itemId ? $this->selectedItemQuantity : 0;
                 if ($qty <= 0) {
                     $unconfirmed = DB::connection('tenant')
                         ->table('imp_unconfirmed_qty')
                         ->where('item_id', $itemId)
                         ->whereNull('deleted_at')
                         ->first(['qty']);
                     $qty = $unconfirmed ? $unconfirmed->qty : 0;
                 }

                 if ($qty <= 0) {
                     $this->dispatch('show-toast', [
                         'type' => 'warning',
                         'message' => 'No se puede definir prioridad a un ítem con cantidad igual a 0'
                     ]);
                     return;
                 }
             }

            // Buscar la importación activa en imp_imports
            $import = ImpImports::where('item_id', $itemId)
                ->where('status', '<', 8)
                ->whereNull('deleted_at')
                ->first();

            // COMENTADO: Anteriormente se requería una etiqueta obligatoria para definir prioridad.
            // Ahora se permite crear el registro en imp_imports directamente con la prioridad si no existe.
            /*
            if ($import) {
            */
                // Obtener la cantidad acumulada preliminar de imp_unconfirmed_qty
                $unconfirmed = DB::connection('tenant')
                    ->table('imp_unconfirmed_qty')
                    ->where('item_id', $itemId)
                    ->whereNull('deleted_at')
                    ->first();

                $unconfirmedQty = $unconfirmed ? $unconfirmed->qty : 0;

                // Si hay una cantidad preliminar mayor a 0, siempre insertamos un nuevo pedido en movimiento
                if ($unconfirmedQty > 0 && $newPriority !== null) {
                    ImpImports::create([
                        'item_id' => $itemId,
                        'priority' => $newPriority,
                        'priority_assigned_at' => now(),
                        'qty_requested' => $unconfirmedQty,
                        'user_id' => \Illuminate\Support\Facades\Auth::id(),
                        'status' => 1
                    ]);

                    DB::connection('tenant')
                        ->table('imp_unconfirmed_qty')
                        ->where('item_id', $itemId)
                        ->update([
                            'qty' => 0,
                            'updated_at' => now()
                        ]);
                } else {
                    // Si no hay cantidad nueva, o es para quitar la prioridad, actualizamos el registro existente
                    if ($import) {
                        $import->update([
                            'priority' => $newPriority,
                            'priority_assigned_at' => $newPriority ? now() : null,
                            'user_id' => \Illuminate\Support\Facades\Auth::id()
                        ]);
                    }
                }



                // Si es el item seleccionado, actualizar variables locales y reiniciar cantidad a 0
                if ($this->selectedItemId == $itemId) {
                    $this->selectedItemPriority = $newPriority;
                    $this->selectedItemPriorityDate = $newPriority ? now()->format('Y-m-d H:i:s') : null;
                    $this->selectedItemQuantity = 0;
                    
                    // Recargar prioridades activas para el banner
                    $this->selectedItemPriorities = ImpImports::where('item_id', $this->selectedItemId)
                        ->where('status', '<', 8)
                        ->whereNotNull('priority')
                        ->whereNull('deleted_at')
                        ->orderByRaw("FIELD(priority, 'ASAP', 'Second', 'Third')")
                        ->get(['priority', 'qty_requested', 'priority_assigned_at'])
                        ->toArray();
                }

                // Despachar evento para limpiar visualmente el input en el listado
                $this->dispatch('update-item-quantity', [
                    'itemId' => $itemId,
                    'quantity' => 0
                ]);

                $this->dispatch('show-toast', [
                    'type' => 'success',
                    'message' => 'Prioridad y cantidad asignadas exitosamente'
                ]);

                $this->dispatch('refresh-import-list');
            /*
            } else {
                $this->dispatch('show-toast', [
                    'type' => 'warning',
                    'message' => 'El ítem debe tener una etiqueta asignada antes de definir su prioridad'
                ]);
            }
            */
        } catch (\Exception $e) {
            Log::error('Error al actualizar prioridad: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al actualizar la prioridad: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Rotar las prioridades en bloque (desplazamiento)
     */
    public function rotatePriorities()
    {
        try {
            $this->ensureTenantConnection();

            DB::connection('tenant')->transaction(function () {
                // 1. ASAP pasa a null (ya recibidos)
                ImpImports::where('priority', 'ASAP')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => null,
                        'priority_assigned_at' => null
                    ]);

                // 2. Second pasa a ASAP
                ImpImports::where('priority', 'Second')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => 'ASAP',
                        'priority_assigned_at' => now()
                    ]);

                // 3. Third pasa a Second
                ImpImports::where('priority', 'Third')
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->update([
                        'priority' => 'Second',
                        'priority_assigned_at' => now()
                    ]);
            });

            // Refrescar el seleccionado si aplica
            if ($this->selectedItemId) {
                $import = ImpImports::where('item_id', $this->selectedItemId)
                    ->where('status', '<', 8)
                    ->whereNull('deleted_at')
                    ->first(['priority', 'priority_assigned_at']);

                if ($import) {
                    $this->selectedItemPriority = $import->priority;
                    $this->selectedItemPriorityDate = $import->priority_assigned_at;
                } else {
                    $this->selectedItemPriority = null;
                    $this->selectedItemPriorityDate = null;
                }
            }

            $this->dispatch('show-toast', [
                'type' => 'success',
                'message' => 'Prioridades rotadas en bloque exitosamente'
            ]);

            $this->dispatch('refresh-import-list');
        } catch (\Exception $e) {
            Log::error('Error al rotar prioridades: ' . $e->getMessage());
            $this->dispatch('show-toast', [
                'type' => 'error',
                'message' => 'Error al rotar las prioridades: ' . $e->getMessage()
            ]);
        }
    }

    public function render()
    {
        $labels = $this->labels;
        $debugInfo = [
            'labels_count' => $labels->count(),
            'has_labels' => $labels->isNotEmpty(),
            'selected_item_id' => $this->selectedItemId,
            'assignments_count' => count($this->itemAssignments),
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
