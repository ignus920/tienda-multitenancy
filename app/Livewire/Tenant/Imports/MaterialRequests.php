<?php

namespace App\Livewire\Tenant\Imports;

use Livewire\Component;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Movements\MovementsService;
use App\Models\Tenant\Projects\ProjectMaterialRequest;
use App\Models\Tenant\Tickets\TickDepartment;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Models\Tenant\Movements\InvReason;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MaterialRequests extends Component
{
    const IMPORTS_DEPARTMENT_NAME = 'Importaciones';
    const OUTBOUND_REASON_NAME = 'Salida a Proyecto';
    const ADMIN_PROFILE_IDS = [1, 2];

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

    /**
     * ¿Puede este usuario ver/gestionar las solicitudes de materiales?
     * Miembros del departamento "Importaciones" (Parámetros → Departamentos)
     * o Super Administrador/Administrador.
     */
    private function canManageRequests(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (in_array((int) $user->profile_id, self::ADMIN_PROFILE_IDS, true)) {
            return true;
        }

        $department = TickDepartment::where('name', self::IMPORTS_DEPARTMENT_NAME)->first();
        if (!$department) return false;

        return $department->users()->wherePivot('status', 1)->where('users.id', $user->id)->exists();
    }

    /**
     * Genera la Salida de Mercancía a partir de una solicitud ya revisada:
     * descuenta inventario y sincroniza con Alegra, con la MISMA lógica que
     * ya usa el módulo Movimientos (MovementForm::saveMovement()) — no se
     * duplica esa lógica desde cero, se replica el mismo flujo probado.
     */
    public function generateOutboundMovement($requestId)
    {
        $this->ensureTenantConnection();

        if (!$this->canManageRequests()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para gestionar solicitudes de materiales.']);
            return;
        }

        $request = ProjectMaterialRequest::with(['items' => function ($q) {
            $q->where('is_removed', false);
        }])->where('status', 'revisada')->find($requestId);

        if (!$request) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La solicitud no existe o ya no está disponible.']);
            return;
        }

        if ($request->items->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La solicitud no tiene productos para generar la salida.']);
            return;
        }

        $store = InvStore::find(2) ?? InvStore::where('status', 1)->first();
        if (!$store) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se encontró una bodega activa para generar la salida.']);
            return;
        }

        $unidad = UnitMeasurements::where('description', 'UNIDAD')->first();

        // ── Paso 1: payload Alegra (mismo formato que MovementForm) ──
        $itemsAlegra = [];
        foreach ($request->items as $reqItem) {
            $item = $reqItem->item_id ? Items::find($reqItem->item_id) : null;
            if ($item && $item->api_data_id) {
                $invValue = InvValues::where('itemId', $item->id)->first();
                $unitCost = $invValue ? floatval($invValue->values) : 0;

                $itemsAlegra[] = [
                    'type' => 'out',
                    'id' => (string) $item->api_data_id,
                    'unitCost' => $unitCost,
                    'quantity' => floatval($reqItem->quantity_requested),
                ];
            }
        }

        $reason = InvReason::firstOrCreate(
            ['name' => self::OUTBOUND_REASON_NAME, 'type' => 's'],
            ['status' => 1]
        );

        $alegraData = !empty($itemsAlegra) ? [
            'date' => now()->format('Y-m-d'),
            'items' => $itemsAlegra,
            'warehouse' => ['id' => '1'],
            'observations' => 'Salida a Proyecto #' . $request->project_id . ' — Solicitud de materiales',
        ] : [];

        $service = new MovementsService();
        $alegraResult = !empty($alegraData) ? $service->syncAdjustmentToApi($alegraData) : [
            'success' => true,
            'api_data_id' => null,
            'sync_skipped' => true,
        ];

        if (!$alegraResult['success']) {
            $userMessage = $alegraResult['message'] ?? 'Error desconocido al comunicarse con Alegra';
            Log::error('❌ [MaterialRequests] Alegra rechazó la salida de materiales', ['alegra_result' => $alegraResult]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No se pudo registrar la salida en Alegra: ' . $userMessage]);
            return;
        }

        $apiDataId = $alegraResult['api_data_id'] ?? null;

        // ── Paso 2: crear localmente + descontar stock ──
        DB::connection('tenant')->beginTransaction();
        try {
            $lastMovement = InvInventoryAdjustment::byType('salida')
                ->byStore($store->id)
                ->orderBy('consecutive', 'desc')
                ->first();
            $consecutive = $lastMovement ? $lastMovement->consecutive + 1 : 1;

            $movement = InvInventoryAdjustment::create([
                'date' => now()->format('Y-m-d'),
                'observations' => 'Salida a Proyecto #' . $request->project_id . ' — Solicitud de materiales',
                'type' => 'salida',
                'status' => 1,
                'storeId' => $store->id,
                'reasonId' => $reason->id,
                'consecutive' => $consecutive,
                'userId' => Auth::id(),
                'project_id' => $request->project_id,
                'api_data_id' => $apiDataId,
            ]);

            foreach ($request->items as $reqItem) {
                if (!$reqItem->item_id) {
                    continue; // sin item_id no hay inventario físico que descontar
                }

                InvDetailInventoryAdjustment::create([
                    'inventoryAdjustmentId' => $movement->id,
                    'itemId' => $reqItem->item_id,
                    'quantity' => $reqItem->quantity_requested,
                    'unitMeasurementId' => $unidad?->id,
                    'cost' => 0,
                ]);

                $itemStore = InvItemsStore::where('itemId', $reqItem->item_id)
                    ->where('storeId', $store->id)
                    ->first();

                if ($itemStore) {
                    $itemStore->stock_items_store -= $reqItem->quantity_requested;
                    $itemStore->save();
                } else {
                    InvItemsStore::create([
                        'itemId' => $reqItem->item_id,
                        'storeId' => $store->id,
                        'stock_items_store' => -$reqItem->quantity_requested,
                    ]);
                }
            }

            $request->update([
                'status' => 'salida_generada',
                'inventory_adjustment_id' => $movement->id,
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Salida de mercancía generada correctamente']);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ [MaterialRequests] Error generando salida de mercancía', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al generar la salida: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        if (!$this->canManageRequests()) {
            return view('livewire.tenant.imports.material-requests', [
                'requests' => collect(),
                'canManageRequests' => false,
            ]);
        }

        $requests = ProjectMaterialRequest::with(['project', 'requestedBy', 'items' => function ($q) {
            $q->where('is_removed', false);
        }])
            ->where('status', 'revisada')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('livewire.tenant.imports.material-requests', [
            'requests' => $requests,
            'canManageRequests' => true,
        ]);
    }
}
