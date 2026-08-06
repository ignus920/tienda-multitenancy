<?php

namespace App\Livewire\Tenant\Production;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Tenant\Production\PrdProductionOrder;
use App\Models\Tenant\Production\PrdProcessItem;
use App\Models\Tenant\Production\PrdFieldsProcess;
use App\Models\Tenant\Production\PrdDataProcessOrder;
use App\Models\Tenant\Production\PrdMaterialItems;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Customer\VntContacts;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ManageProductionOrders extends Component
{
    use WithPagination;

    // Búsqueda / paginación
    public string $search  = '';
    public int    $perPage = 10;
    public string $filterStatus = '';

    // Modal
    public bool  $showModal  = false;
    public ?int  $editingId  = null;

    // Campos del formulario
    public string $date                  = '';
    public ?int   $item_id               = null;
    public        $requested_qty         = '';
    public ?int   $warehouse_customer_id = null;
    public string $customer_order        = '';
    public string $delivery_date         = '';
    public ?int   $productive_stage      = null;
    public string $sales_order           = '';

    // Listas para selects
    public array $productItems  = [];
    public array $contacts      = [];

    // Procesos del producto seleccionado
    public array $itemProcesses = [];

    public array $stages = [
        'pendiente'  => 'Pendiente',
        'disenio'    => 'Diseño',
        'preprensa'  => 'Preprensa',
        'impresion'  => 'Impresión',
        'acabados'   => 'Acabados',
        'despacho'   => 'Listo para despacho',
    ];

    // Estados de la orden (IDs de la BD)
    public array $statuses = [
        3 => 'Registrado',
        4 => 'En proceso',
        5 => 'Finalizado',
        6 => 'Anulado',
        7 => 'Pausado',
    ];

    // ── Modal de avance de etapa ──────────────────────────
    public bool   $showProcessModal         = false;
    public bool   $processModalIsLast       = false;
    public bool   $processModalHasInventory = false;
    public ?int   $processModalOrderId      = null;
    public string $processModalName         = '';
    public int    $processModalId           = 0;
    public array  $processModalFields       = [];
    public array  $processFieldValues       = [];
    public array  $materialItems            = [];
    public array  $unitMeasurements         = [];
    public string $processModalError        = '';
    public array  $materialConsumptions     = [];

    protected $queryString = [
        'search'       => ['except' => ''],
        'filterStatus' => ['except' => ''],
        'perPage'      => ['except' => 10],
    ];

    protected function rules(): array
    {
        return [
            'date'                  => 'required|date',
            'item_id'               => 'required|integer',
            'requested_qty'         => 'required|integer|min:1',
            'warehouse_customer_id' => 'required|integer',
            'customer_order'        => 'nullable|string|max:100',
            'delivery_date'         => 'required|date|after_or_equal:date',
            'sales_order'           => 'nullable|string|max:100',
        ];
    }

    protected $messages = [
        'date.required'                  => 'La fecha de la orden es obligatoria.',
        'item_id.required'               => 'Debe seleccionar un producto.',
        'requested_qty.required'         => 'La cantidad es obligatoria.',
        'requested_qty.min'              => 'La cantidad mínima es 1.',
        'warehouse_customer_id.required' => 'Debe seleccionar un cliente.',
        'delivery_date.required'         => 'La fecha de entrega es obligatoria.',
        'delivery_date.after_or_equal'   => 'La fecha de entrega debe ser igual o posterior a la fecha de orden.',
    ];

    public function mount(): void
    {
        $this->ensureTenantConnection();
        $this->date          = now()->format('Y-m-d');
        $this->delivery_date = now()->addDays(7)->format('Y-m-d');
        $this->loadSelectData();
    }

    private function loadSelectData(): void
    {
        $this->productItems = Items::on('tenant')
            ->where('type', 'PRODUCIDO')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'internal_code'])
            ->toArray();

        $this->contacts = VntContacts::on('tenant')
            ->where('status', 1)
            ->orderBy('firstName')
            ->get(['id', 'firstName', 'secondName', 'lastName', 'secondLastName'])
            ->map(fn($c) => [
                'id'       => $c->id,
                'fullName' => trim("{$c->firstName} {$c->secondName} {$c->lastName} {$c->secondLastName}"),
            ])
            ->toArray();
    }

    // Se ejecuta cada vez que cambia el item_id en el formulario
    public function updatedItemId(): void
    {
        $this->ensureTenantConnection();
        $this->loadItemProcesses();
    }

    private function loadItemProcesses(): void
    {
        if (!$this->item_id) {
            $this->itemProcesses = [];
            return;
        }

        $processItems = PrdProcessItem::with('process')
            ->where('itemId', $this->item_id)
            ->whereNull('deleted_at')
            ->orderBy('process_route_order')
            ->get();

        $this->itemProcesses = $processItems->map(function ($pi) {
            $fields = PrdFieldsProcess::where('processId', $pi->processId)
                ->where('status', 1)
                ->orderBy('id')
                ->get(['id', 'name', 'label', 'type', 'options'])
                ->map(function ($f) {
                    $opts = [];
                    if ($f->options) {
                        $decoded = is_array($f->options) ? $f->options : json_decode($f->options, true);
                        $opts = $decoded ?? [];
                    }
                    return [
                        'id'      => $f->id,
                        'name'    => $f->name,
                        'label'   => $f->label,
                        'type'    => $f->type,
                        'options' => $opts,
                    ];
                })
                ->toArray();

            return [
                'route_order'  => $pi->process_route_order,
                'process_id'   => $pi->processId,
                'process_name' => $pi->process?->name ?? '—',
                'fields'       => $fields,
            ];
        })->toArray();

        // Al crear, auto-asignar la etapa al ID del primer proceso del ítem
        if (!$this->editingId && !empty($this->itemProcesses)) {
            $this->productive_stage = $this->itemProcesses[0]['process_id'];
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    // ── CRUD ─────────────────────────────────────────────

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $this->ensureTenantConnection();
        $order = PrdProductionOrder::findOrFail($id);

        $this->editingId              = $order->id;
        $this->date                   = $order->date->format('Y-m-d');
        $this->item_id                = $order->item_id;
        $this->requested_qty          = $order->requested_qty;
        $this->warehouse_customer_id  = $order->warehouse_customer_id;
        $this->customer_order         = $order->customer_order ?? '';
        $this->delivery_date          = $order->delivery_date->format('Y-m-d');
        $this->productive_stage       = $order->productive_stage;
        $this->sales_order            = $order->sales_order ?? '';

        $this->loadItemProcesses();

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->ensureTenantConnection();
        $this->validate();

        $data = [
            'date'                  => $this->date,
            'item_id'               => $this->item_id,
            'requested_qty'         => (int) $this->requested_qty,
            'warehouse_customer_id' => $this->warehouse_customer_id,
            'customer_order'        => $this->customer_order ?: null,
            'delivery_date'         => $this->delivery_date,
            'productive_stage'      => $this->productive_stage,
            'sales_order'           => $this->sales_order ?: null,
        ];

        if ($this->editingId) {
            PrdProductionOrder::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Orden actualizada correctamente.');
        } else {
            // Al crear, el estado se asigna automáticamente como REGISTRADO (3)
            $data['status'] = 3;
            PrdProductionOrder::create($data);
            session()->flash('success', 'Orden de producción creada correctamente.');
        }

        $this->resetForm();
        $this->showModal = false;
    }

    public function delete(int $id): void
    {
        $this->ensureTenantConnection();
        PrdProductionOrder::findOrFail($id)->delete();
        session()->flash('success', 'Orden eliminada correctamente.');
    }

    public function changeStatus(int $id, int $newStatus): void
    {
        $this->ensureTenantConnection();
        PrdProductionOrder::findOrFail($id)->update(['status' => $newStatus]);
    }

    // ── Modal avance de etapa ─────────────────────────────

    public function openProcessModal(int $orderId): void
    {
        $this->ensureTenantConnection();

        $order = PrdProductionOrder::findOrFail($orderId);

        // Ruta de procesos del item ordenada
        $processRoute = PrdProcessItem::with('process')
            ->where('itemId', $order->item_id)
            ->whereNull('deleted_at')
            ->orderBy('process_route_order')
            ->get();

        if ($processRoute->isEmpty()) {
            session()->flash('error', 'Este producto no tiene procesos de producción asignados.');
            return;
        }

        $currentProcessId = $order->productive_stage;
        $targetProcess    = null;

        if (!$currentProcessId) {
            // Sin etapa asignada → primer proceso
            $targetProcess = $processRoute->first();
        } else {
            $foundCurrent = false;
            foreach ($processRoute as $pi) {
                if ($foundCurrent) {
                    $targetProcess = $pi;
                    break;
                }
                if ($pi->processId == $currentProcessId) {
                    $foundCurrent = true;
                }
            }
            // Si la etapa actual no se encontró en la ruta → usar el primero
            if (!$foundCurrent) {
                $targetProcess = $processRoute->first();
            }
        }

        if (!$targetProcess) {
            // Estamos en el último proceso → modal de finalización
            $lastProcess = $order->productiveProcess;

            $this->processModalIsLast       = true;
            $this->processModalOrderId      = $orderId;
            $this->processModalName         = $lastProcess?->name ?? '—';
            $this->processModalId           = $currentProcessId ?? 0;
            $this->processModalFields       = [];
            $this->processFieldValues       = [];
            $this->processModalHasInventory = (bool) ($lastProcess?->inventory_consumption ?? false);

            if ($this->processModalHasInventory) {
                $this->loadMaterialItems();
                $this->materialConsumptions = [['material_itemId' => '', 'qty' => '', 'unit_measurement' => '']];
            }

            $this->showProcessModal = true;
            return;
        }

        $fields = PrdFieldsProcess::where('processId', $targetProcess->processId)
            ->where('status', 1)
            ->orderBy('id')
            ->get(['id', 'name', 'label', 'type', 'options', 'class', 'parent_field', 'parent_value'])
            ->map(function ($f) {
                return [
                    'id'           => $f->id,
                    'name'         => $f->name,
                    'label'        => $f->label,
                    'type'         => $f->type,
                    'class'        => $f->class ?? '',
                    'options'      => $this->parseFieldOptions($f->options),
                    'parent_field' => $f->parent_field,
                    'parent_value' => $f->parent_value,
                ];
            })
            ->toArray();

        $this->processModalOrderId      = $orderId;
        $this->processModalName         = $targetProcess->process?->name ?? '—';
        $this->processModalId           = $targetProcess->processId;
        $this->processModalFields       = $fields;
        $this->processFieldValues       = [];
        $this->processModalHasInventory = (bool) ($targetProcess->process?->inventory_consumption ?? false);

        foreach ($fields as $field) {
            $this->processFieldValues[$field['name']] = '';
        }

        if ($this->processModalHasInventory) {
            $this->loadMaterialItems();
            $this->materialConsumptions = [['material_itemId' => '', 'qty' => '', 'unit_measurement' => '']];
        }

        $this->showProcessModal = true;
    }

    public function saveProcessStage(): void
    {
        $this->ensureTenantConnection();
        $this->processModalError = '';

        // Validar stock antes de proceder
        if ($this->processModalHasInventory && !$this->validateMaterialStock()) {
            return;
        }

        try {
        if ($this->processModalIsLast) {
            // Guardar consumo de materiales si aplica (descuenta stock)
            $this->saveMaterialConsumptions();

            // Último proceso → finalizar la orden
            PrdProductionOrder::findOrFail($this->processModalOrderId)->update([
                'status' => 5,
            ]);

            $this->resetProcessModal();
            session()->flash('success', 'Orden de producción finalizada correctamente.');
            return;
        }

        // Guardar los valores de cada campo en prd_data_process_order
        foreach ($this->processModalFields as $field) {
            // Omitir campos cuya condición de visibilidad no se cumple
            if (!empty($field['parent_field'])) {
                $parentValue = $this->processFieldValues[$field['parent_field']] ?? '';
                if ($parentValue !== $field['parent_value']) {
                    continue;
                }
            }

            $value = $this->processFieldValues[$field['name']] ?? null;

            if ($value === null || $value === '') {
                continue;
            }

            PrdDataProcessOrder::create([
                'production_order_id' => $this->processModalOrderId,
                'field_processId'     => $field['id'],
                'field_value'         => is_array($value) ? implode(',', $value) : (string) $value,
            ]);
        }

        // Guardar consumo de materiales si aplica (descuenta stock)
        $this->saveMaterialConsumptions();

        // Avanzar la etapa y marcar "En proceso"
        PrdProductionOrder::findOrFail($this->processModalOrderId)->update([
            'productive_stage' => $this->processModalId,
            'status'           => 4,
        ]);

        $this->resetProcessModal();
        session()->flash('success', 'Etapa de producción avanzada correctamente.');

        } catch (\Exception $e) {
            Log::error('❌ [Producción] Error al guardar etapa de proceso', [
                'error' => $e->getMessage(),
            ]);
            session()->flash('error', 'Error al guardar el proceso: ' . $e->getMessage());
        }
    }

    /**
     * Valida que haya stock suficiente para cada material antes de descontar.
     * Retorna true si todo está bien, false si algún item no tiene stock.
     */
    private function validateMaterialStock(): bool
    {
        $storeId = $this->getUserStoreId();

        if (!$storeId) {
            $this->processModalError = 'No se encontró una bodega asignada al usuario. No se puede verificar el stock.';
            return false;
        }

        foreach ($this->materialConsumptions as $row) {
            if (empty($row['material_itemId']) || empty($row['qty'])) {
                continue;
            }

            // Calcular cantidad en unidad de consumo base
            $unit      = collect($this->unitMeasurements)->firstWhere('id', (int) ($row['unit_measurement'] ?? 0));
            $factor    = $unit ? floatval($unit['quantity']) : 1;
            $requested = floatval($row['qty']) * $factor;

            $itemStore = InvItemsStore::where('itemId', $row['material_itemId'])
                ->where('storeId', $storeId)
                ->first();

            $quarantineQty = (float) DB::table('inv_quarantine_movements')
                ->where('item_id', $row['material_itemId'])
                ->where('store_id', $storeId)
                ->whereNull('deleted_at')
                ->sum('quantity');

            $showroomQty = (float) DB::table('inv_showroom_movements')
                ->where('item_id', $row['material_itemId'])
                ->where('store_id', $storeId)
                ->whereNull('deleted_at')
                ->sum('quantity');

            $available = $itemStore ? floatval($itemStore->stock_items_store) : 0;
            $realAvailable = max(0, $available - $quarantineQty - $showroomQty);

            if ($realAvailable < $requested) {
                $item     = Items::find($row['material_itemId']);
                $itemName = $item?->name ?? "Item #{$row['material_itemId']}";
                $unitLabel = $unit ? $unit['description'] : 'unidades base';
                
                $details = [];
                if ($quarantineQty > 0) $details[] = "en cuarentena: {$quarantineQty}";
                if ($showroomQty > 0) $details[] = "en vitrina: {$showroomQty}";
                $detailsStr = !empty($details) ? " (" . implode(', ', $details) . ")" : "";
                
                $this->processModalError = "Stock insuficiente para \"{$itemName}\"{$detailsStr}. "
                    . "Disponible libre: {$realAvailable} unidades base, Requerido: {$requested} ({$row['qty']} {$unitLabel}).";
                return false;
            }
        }

        return true;
    }

    private function saveMaterialConsumptions(): void
    {
        if (!$this->processModalHasInventory) {
            return;
        }

        // Obtener la bodega del usuario autenticado para descontar stock
        $storeId = $this->getUserStoreId();

        DB::connection('tenant')->beginTransaction();
        try {
            foreach ($this->materialConsumptions as $row) {
                if (empty($row['material_itemId']) || empty($row['qty'])) {
                    continue;
                }

                $unitId = !empty($row['unit_measurement']) ? (int) $row['unit_measurement'] : null;
                $unit   = $unitId ? collect($this->unitMeasurements)->firstWhere('id', $unitId) : null;
                $factor = $unit ? floatval($unit['quantity']) : 1;

                PrdMaterialItems::create([
                    'production_order_id' => $this->processModalOrderId,
                    'process_id'          => $this->processModalId,
                    'material_itemId'     => $row['material_itemId'],
                    'qty'                 => $row['qty'],
                    'unit_measurement' => $unitId,
                ]);

                // Descontar stock (qty × factor de unidad) en la bodega del usuario
                if ($storeId) {
                    $itemStore = InvItemsStore::where('itemId', $row['material_itemId'])
                        ->where('storeId', $storeId)
                        ->first();

                    if ($itemStore) {
                        $newStock = $itemStore->stock_items_store - (floatval($row['qty']) * $factor);
                        $itemStore->stock_items_store = max(0, $newStock);
                        $itemStore->save();
                    }

                    Log::info('📦 [Producción] Stock descontado por consumo de material', [
                        'material_itemId'     => $row['material_itemId'],
                        'qty'                 => $row['qty'],
                        'storeId'             => $storeId,
                        'production_order_id' => $this->processModalOrderId,
                        'process_id'          => $this->processModalId,
                    ]);
                }
            }

            DB::connection('tenant')->commit();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ [Producción] Error al descontar stock por consumo de materiales', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Obtiene el ID de la bodega (store) asignada al usuario autenticado.
     * Si el almacén tiene más de una bodega activa, retorna la primera.
     */
    private function getUserStoreId(): ?int
    {
        $user = Auth::user();

        if (!$user || !$user->contact_id) {
            return null;
        }

        $contact = \App\Models\Central\VntContact::with('warehouse')->find($user->contact_id);

        if (!$contact || !$contact->warehouseId) {
            return null;
        }

        $store = InvStore::where('warehouseId', $contact->warehouseId)
            ->where('status', 1)
            ->orderBy('id')
            ->first();

        return $store?->id;
    }

    private function loadMaterialItems(): void
    {
        $this->materialItems = Items::on('tenant')
            ->where('type', 'INSUMO')
            ->where('status', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'internal_code'])
            ->toArray();

        $this->unitMeasurements = UnitMeasurements::where('status', 1)
            ->orderBy('description')
            ->get(['id', 'description', 'quantity'])
            ->toArray();
    }

    public function addMaterialRow(): void
    {
        $this->materialConsumptions[] = ['material_itemId' => '', 'qty' => '', 'unit_measurement' => ''];
    }

    public function removeMaterialRow(int $index): void
    {
        unset($this->materialConsumptions[$index]);
        $this->materialConsumptions = array_values($this->materialConsumptions);
    }

    private function parseFieldOptions($options): array
    {
        if (!$options) return [];
        if (is_array($options)) return $options;
        $decoded = json_decode($options, true);
        if ($decoded !== null) return $decoded;
        return array_map('trim', explode(',', $options));
    }

    private function resetProcessModal(): void
    {
        $this->showProcessModal         = false;
        $this->processModalIsLast       = false;
        $this->processModalHasInventory = false;
        $this->processModalOrderId      = null;
        $this->processModalName         = '';
        $this->processModalId           = 0;
        $this->processModalFields       = [];
        $this->processFieldValues       = [];
        $this->materialItems            = [];
        $this->materialConsumptions     = [];
        $this->processModalError        = '';
    }

    private function resetForm(): void
    {
        $this->editingId             = null;
        $this->date                  = now()->format('Y-m-d');
        $this->item_id               = null;
        $this->requested_qty         = '';
        $this->warehouse_customer_id = null;
        $this->customer_order        = '';
        $this->delivery_date         = now()->addDays(7)->format('Y-m-d');
        $this->productive_stage      = null;
        $this->sales_order           = '';
        $this->itemProcesses         = [];
        $this->resetErrorBag();
    }

    // ── Tenant connection ─────────────────────────────────

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            redirect()->route('tenant.select');
            return;
        }
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            session()->forget('tenant_id');
            redirect()->route('tenant.select');
            return;
        }
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $orders = PrdProductionOrder::query()
            ->with(['item', 'contact', 'productiveProcess'])
            ->when($this->search, function ($q) {
                $q->where(function ($inner) {
                    $inner->whereHas('item', fn($i) => $i->where('name', 'like', "%{$this->search}%"))
                          ->orWhere('customer_order', 'like', "%{$this->search}%")
                          ->orWhere('sales_order', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.tenant.production.manage-production-orders', [
            'orders' => $orders,
        ])->layout('layouts.app');
    }
}
