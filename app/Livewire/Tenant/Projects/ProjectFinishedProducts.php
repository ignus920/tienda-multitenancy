<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectFinishedProduct;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Models\Tenant\Movements\InvReason;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Movements\MovementsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectFinishedProducts extends Component
{
    const ENTRY_REASON_NAME = 'Entrada por Producto Terminado';
    const IMPORTACIONES_PROFILE_ID = 21;

    public $projectId;

    // Formulario de alta — buscador de productos ERP (el producto terminado
    // debe existir ya como ítem en el ERP para poder generar la entrada)
    public $search = '';
    public $searchResults = [];
    public $selectedErpItem = null;
    public $price = null;
    public $quantity = 1;

    // Edición inline (solo para filas antiguas sin entrada de inventario)
    public $editingId = null;
    public $editDescription = '';
    public $editPrice = null;
    public $editQuantity = null;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) return;

        $tenant = Tenant::find($tenantId);
        if (!$tenant) return;

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        if (!tenancy()->initialized) {
            tenancy()->initialize($tenant);
        }

        config(['database.connections.tenant.database' => $tenant->tenancy_db_name]);
    }

    private function checkNotClosed()
    {
        $project = Project::find($this->projectId);
        if ($project && in_array($project->status, ['terminado', 'cerrado_entregado'])) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El proyecto está finalizado. No se permiten más modificaciones.']);
            return true;
        }
        return false;
    }

    /**
     * Solo Perfil "Importaciones" (Camilo hoy lo tiene) o Super
     * Administrador/Administrador — Laboratorio NO debe ver ni gestionar
     * esta pestaña.
     */
    private function canManage(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (in_array((int) $user->profile_id, Project::FULL_ACCESS_PROFILES, true)) {
            return true;
        }

        return (int) $user->profile_id === self::IMPORTACIONES_PROFILE_ID;
    }

    private function checkCanManage(): bool
    {
        if (!$this->canManage()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para gestionar producto terminado.']);
            return false;
        }
        return true;
    }

    public function updatedSearch()
    {
        $this->ensureTenantConnection();
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $words = array_filter(explode(' ', trim($this->search)));

        // Solo productos tipificados como "ENSAMBLADO" — los demás tipos no
        // aplican como producto terminado y causarían inconsistencias.
        $query = Items::with('invValues')->active()->byType('ENSAMBLADO');
        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->where('name', 'like', '%' . $word . '%')
                  ->orWhere('internal_code', 'like', '%' . $word . '%')
                  ->orWhere('description', 'like', '%' . $word . '%');
            });
        }

        $this->searchResults = $query->limit(10)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code,
                'price' => $item->price,
            ];
        })->toArray();
    }

    public function selectErpProduct($itemId, $itemName, $itemPrice, $itemCode = '')
    {
        $itemPrice = round((float) $itemPrice);

        $this->selectedErpItem = [
            'id' => $itemId,
            'name' => $itemName,
            'price' => $itemPrice,
            'code' => $itemCode,
        ];

        // El precio se toma directo del precio de lista del ERP — ya no se
        // digita a mano, Camilo ya lo dejó configurado en el ítem.
        $this->price = $itemPrice;

        $priceFormatted = '$' . number_format($itemPrice, 0);
        $this->search = $itemCode
            ? "{$itemCode} - {$itemName} ({$priceFormatted})"
            : "{$itemName} ({$priceFormatted})";

        $this->searchResults = [];
    }

    /**
     * Agrega un Producto Terminado a la lista, en borrador — todavía NO
     * genera entrada de inventario ni toca Alegra. Eso queda para
     * generateInventoryEntry(), un segundo paso explícito, para que el
     * usuario pueda revisar la lista completa antes de comprometer stock.
     */
    public function addFinishedProduct()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        if (!$this->selectedErpItem) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debes buscar y seleccionar el producto terminado del ERP primero.']);
            return;
        }

        $this->validate([
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
        ], [
            'price.required' => 'El precio es obligatorio.',
            'quantity.required' => 'La cantidad es obligatoria.',
        ]);

        $item = Items::find($this->selectedErpItem['id']);
        if (!$item) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'El producto seleccionado ya no existe en el ERP.']);
            return;
        }

        $fullDescription = !empty($this->selectedErpItem['code'])
            ? "{$this->selectedErpItem['code']} - {$this->selectedErpItem['name']}"
            : $this->selectedErpItem['name'];

        ProjectFinishedProduct::create([
            'project_id' => $this->projectId,
            'item_id' => $item->id,
            'description' => $fullDescription,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['search', 'price', 'searchResults', 'selectedErpItem']);
        $this->quantity = 1;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto terminado agregado a la lista']);
    }

    /**
     * Segundo paso, explícito: toma TODAS las filas pendientes (sin
     * entrada de inventario todavía) de este proyecto y genera UNA sola
     * entrada — sube el stock local y sincroniza con Alegra — con la
     * MISMA lógica ya probada de Movimientos (MovementForm::saveMovement(),
     * tipo entrada). Así el usuario revisa la lista completa antes de
     * comprometer inventario/Alegra, en vez de que cada "Agregar" dispare
     * un movimiento por su cuenta.
     */
    public function generateInventoryEntry()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        $pending = ProjectFinishedProduct::where('project_id', $this->projectId)
            ->whereNull('inventory_adjustment_id')
            ->get();

        if ($pending->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No hay productos terminados pendientes de generar entrada.']);
            return;
        }

        $store = InvStore::find(2) ?? InvStore::where('status', 1)->first();
        if (!$store) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se encontró una bodega activa para registrar la entrada.']);
            return;
        }

        $unidad = UnitMeasurements::where('description', 'UNIDAD')->first();

        // ── Paso 1: payload Alegra (mismo formato que MovementForm, tipo entrada) ──
        $itemsAlegra = [];
        foreach ($pending as $row) {
            if (!$row->item_id) continue;
            $item = Items::find($row->item_id);
            if ($item && $item->api_data_id) {
                $invValue = InvValues::where('itemId', $item->id)->first();
                $unitCost = $invValue ? floatval($invValue->values) : 0;

                $itemsAlegra[] = [
                    'type' => 'in',
                    'id' => (string) $item->api_data_id,
                    'unitCost' => $unitCost,
                    'quantity' => floatval($row->quantity),
                ];
            }
        }

        $reason = InvReason::firstOrCreate(
            ['name' => self::ENTRY_REASON_NAME, 'type' => 'e'],
            ['status' => 1]
        );

        $alegraData = !empty($itemsAlegra) ? [
            'date' => now()->format('Y-m-d'),
            'items' => $itemsAlegra,
            'warehouse' => ['id' => '1'],
            'observations' => 'Producto Terminado — Proyecto #' . $this->projectId,
        ] : [];

        $service = new MovementsService();
        $alegraResult = !empty($alegraData) ? $service->syncAdjustmentToApi($alegraData) : [
            'success' => true,
            'api_data_id' => null,
            'sync_skipped' => true,
        ];

        if (!$alegraResult['success']) {
            $userMessage = $alegraResult['message'] ?? 'Error desconocido al comunicarse con Alegra';
            Log::error('❌ [ProjectFinishedProducts] Alegra rechazó la entrada de producto terminado', ['alegra_result' => $alegraResult]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se pudo registrar la entrada en Alegra: ' . $userMessage]);
            return;
        }

        $apiDataId = $alegraResult['api_data_id'] ?? null;

        // ── Paso 2: crear localmente + sumar stock ──
        DB::connection('tenant')->beginTransaction();
        try {
            $lastMovement = InvInventoryAdjustment::byType('entrada')
                ->byStore($store->id)
                ->orderBy('consecutive', 'desc')
                ->first();
            $consecutive = $lastMovement ? $lastMovement->consecutive + 1 : 1;

            $movement = InvInventoryAdjustment::create([
                'date' => now()->format('Y-m-d'),
                'observations' => 'Producto Terminado — Proyecto #' . $this->projectId,
                'type' => 'entrada',
                'status' => 1,
                'storeId' => $store->id,
                'reasonId' => $reason->id,
                'consecutive' => $consecutive,
                'userId' => Auth::id(),
                'project_id' => $this->projectId,
                'api_data_id' => $apiDataId,
            ]);

            foreach ($pending as $row) {
                if (!$row->item_id) {
                    $row->update(['inventory_adjustment_id' => $movement->id]);
                    continue; // sin item_id no hay inventario físico que sumar
                }

                InvDetailInventoryAdjustment::create([
                    'inventoryAdjustmentId' => $movement->id,
                    'itemId' => $row->item_id,
                    'quantity' => $row->quantity,
                    'unitMeasurementId' => $unidad?->id,
                    'cost' => 0,
                ]);

                $itemStore = InvItemsStore::where('itemId', $row->item_id)
                    ->where('storeId', $store->id)
                    ->first();

                if ($itemStore) {
                    $itemStore->stock_items_store += $row->quantity;
                    $itemStore->save();
                } else {
                    InvItemsStore::create([
                        'itemId' => $row->item_id,
                        'storeId' => $store->id,
                        'stock_items_store' => $row->quantity,
                    ]);
                }

                $row->update(['inventory_adjustment_id' => $movement->id]);
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Entrada de inventario generada y sincronizada con Alegra']);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ [ProjectFinishedProducts] Error generando entrada de producto terminado', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al generar la entrada: ' . $e->getMessage()]);
        }
    }

    public function editFinishedProduct($id)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        $product = ProjectFinishedProduct::findOrFail($id);

        if ($product->inventory_adjustment_id) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Este producto ya generó una entrada de inventario — corrígelo desde Movimientos.']);
            return;
        }

        $this->editingId = $product->id;
        $this->editDescription = $product->description;
        $this->editPrice = $product->price;
        $this->editQuantity = $product->quantity;
    }

    public function cancelEdit()
    {
        $this->reset(['editingId', 'editDescription', 'editPrice', 'editQuantity']);
    }

    public function saveEdit()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        $product = ProjectFinishedProduct::findOrFail($this->editingId);
        if ($product->inventory_adjustment_id) {
            $this->cancelEdit();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Este producto ya generó una entrada de inventario — corrígelo desde Movimientos.']);
            return;
        }

        $this->validate([
            'editDescription' => 'required|string|max:255',
            'editPrice' => 'required|numeric|min:0',
            'editQuantity' => 'required|numeric|min:0.01',
        ]);

        $product->update([
            'description' => $this->editDescription,
            'price' => $this->editPrice,
            'quantity' => $this->editQuantity,
        ]);

        $this->cancelEdit();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto terminado actualizado']);
    }

    public function deleteFinishedProduct($id)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        $product = ProjectFinishedProduct::find($id);
        if (!$product) return;

        if ($product->inventory_adjustment_id) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Este producto ya generó una entrada de inventario — anúlala desde Movimientos si necesitas eliminarlo.']);
            return;
        }

        $product->delete();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto terminado eliminado']);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $canManage = $this->canManage();

        if (!$canManage) {
            return view('livewire.tenant.projects.project-finished-products', [
                'canManage' => false,
                'products' => collect(),
                'total' => 0,
                'isClosed' => false,
                'hasPendingEntry' => false,
            ]);
        }

        $products = ProjectFinishedProduct::where('project_id', $this->projectId)
            ->orderBy('created_at', 'asc')
            ->get();

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;

        return view('livewire.tenant.projects.project-finished-products', [
            'canManage' => true,
            'products' => $products,
            'total' => $products->sum(fn ($p) => $p->price * $p->quantity),
            'isClosed' => $isClosed,
            'hasPendingEntry' => $products->whereNull('inventory_adjustment_id')->isNotEmpty(),
        ]);
    }
}
