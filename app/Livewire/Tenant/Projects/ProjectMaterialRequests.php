<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMaterial;
use App\Models\Tenant\Projects\ProjectMaterialRequest;
use App\Models\Tenant\Projects\ProjectMaterialRequestItem;
use App\Models\Tenant\Projects\ProjectMessage;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Events\Tenant\Projects\NewProjectNotification;
use App\Models\Auth\Tenant;
use App\Models\Auth\User;
use App\Services\Tenant\TenantManager;
use App\Services\Tenant\Movements\MovementsService;
use App\Models\Tenant\Movements\InvInventoryAdjustment;
use App\Models\Tenant\Movements\InvDetailInventoryAdjustment;
use App\Models\Tenant\Movements\InvReason;
use App\Models\Tenant\Movements\InvStore;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvValues;
use App\Models\Tenant\Items\UnitMeasurements;
use App\Models\Tenant\Items\InvItemsStore;
use App\Models\Tenant\Remissions\InvRemissions;
use App\Models\Tenant\Remissions\InvDetailRemissions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMaterialRequests extends Component
{
    const IMPORTACIONES_PROFILE_ID = 21;
    const LABORATORIO_PROFILE_ID = 20;
    const OUTBOUND_REASON_NAME = 'Salida a Proyecto';

    public $projectId;

    // true cuando Materiales cambió mientras esta pantalla ya estaba abierta
    // — mientras esté en true, se oculta "Enviar a Importaciones" y se
    // obliga a actualizar primero, para que nadie envíe una lista vieja.
    public $hasPendingUpdates = false;

    public function mount($projectId)
    {
        $this->projectId = $projectId;
    }

    public function boot()
    {
        $this->ensureTenantConnection();
    }

    /**
     * Se dispara por WebSocket cuando alguien cambia algo en Materiales
     * mientras esta pantalla ya está abierta (por ejemplo, en otra sesión).
     * No refresca los datos solo, para no ocultarle nada a quien está a
     * punto de darle "Enviar" — solo prende la advertencia y cambia el
     * botón por "Actualizar Lista".
     */
    #[On('echo-private:project.{projectId},.MaterialsListSynced')]
    public function markListOutdated()
    {
        $this->hasPendingUpdates = true;
    }

    /**
     * El propio render() de Livewire ya vuelve a consultar la solicitud
     * desde la base de datos en cada acción — solo hace falta apagar la
     * advertencia para que reaparezca "Enviar a Importaciones" con los
     * datos ya frescos.
     */
    public function refreshMaterialsList()
    {
        $this->hasPendingUpdates = false;
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
     * Solo Laboratorio puede ajustar/enviar la Solicitud de Materiales —
     * ni siquiera Super Administrador/Administrador, porque ellos no son
     * quienes piden materiales a Bodega, mismo criterio que Cerrar/Abrir
     * Lista de Materiales. El resto de perfiles con acceso (canManageOutbound)
     * igual pueden VER la lista, pero sin botones de acción.
     */
    private function canManage(): bool
    {
        $user = Auth::user();
        return $user && (int) $user->profile_id === self::LABORATORIO_PROFILE_ID;
    }

    private function checkCanManage(): bool
    {
        if (!$this->canManage()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para gestionar solicitudes de materiales.']);
            return false;
        }
        return true;
    }

    /**
     * ¿Puede este usuario revisar la solicitud ya enviada y generar la
     * Salida de Mercancía? Perfil "Importaciones" (Camilo hoy lo tiene) o
     * Super Administrador/Administrador.
     */
    private function canManageOutbound(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        if (in_array((int) $user->profile_id, Project::FULL_ACCESS_PROFILES, true)) {
            return true;
        }

        return (int) $user->profile_id === self::IMPORTACIONES_PROFILE_ID;
    }

    /**
     * Crea una nueva solicitud a partir de los materiales activos de
     * origen ERP, redondeados hacia arriba. Si ya existe una solicitud sin
     * convertir en salida (pendiente/revisada), no crea otra.
     */
    public function generateMaterialRequest()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;
        if ($this->checkNotClosed()) return;

        // Bloquea una solicitud nueva si ya hay una en curso (pendiente/revisada)
        // o si ya se ejecutó la salida — esa acción no se puede repetir.
        $existing = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->whereIn('status', ['pendiente', 'revisada', 'salida_generada'])
            ->exists();

        if ($existing) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ya hay una solicitud de materiales para este proyecto — no se puede generar otra.']);
            return;
        }

        $erpMaterials = ProjectMaterial::where('project_id', $this->projectId)
            ->where('origin', 'erp')
            ->where('is_active', true)
            ->get();

        if ($erpMaterials->isEmpty()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No hay materiales del ERP activos para generar la solicitud.']);
            return;
        }

        $request = ProjectMaterialRequest::create([
            'project_id' => $this->projectId,
            'status' => 'pendiente',
            'requested_by' => Auth::id(),
        ]);

        foreach ($erpMaterials as $material) {
            ProjectMaterialRequestItem::create([
                'request_id' => $request->id,
                'project_material_id' => $material->id,
                'item_id' => $material->item_id,
                'description' => $material->description,
                'quantity_requested' => (int) ceil($material->quantity),
                'is_removed' => false,
            ]);
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud de materiales generada']);
    }

    public function updateRequestItemQuantity($itemId, $quantity)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $item = ProjectMaterialRequestItem::whereHas('request', function ($q) {
            $q->where('project_id', $this->projectId)->where('status', 'pendiente');
        })->find($itemId);

        if (!$item) return;

        $quantity = max(1, (int) $quantity);
        $item->update(['quantity_requested' => $quantity]);
    }

    public function toggleRequestItemRemoved($itemId)
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $item = ProjectMaterialRequestItem::whereHas('request', function ($q) {
            $q->where('project_id', $this->projectId)->where('status', 'pendiente');
        })->find($itemId);

        if (!$item) return;

        $item->update(['is_removed' => !$item->is_removed]);
    }

    /**
     * Laboratorio ya terminó de ajustar la solicitud — queda visible para
     * que Importaciones la revise y genere la Salida de Mercancía.
     */
    public function markRequestReviewed()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if (!$request) return;

        if ($request->items()->where('is_removed', false)->doesntExist()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La solicitud no puede quedar vacía — debe tener al menos un producto sin quitar.']);
            return;
        }

        $request->update(['status' => 'revisada']);

        $this->notifyImportsDepartment($request);

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud enviada a Importaciones']);
    }

    /**
     * Avisa a todos los usuarios con Perfil "Importaciones" (Camilo hoy lo
     * tiene) usando la misma campana de notificaciones de Proyectos que ya
     * existe y funciona, en vez de depender del sistema de notificaciones
     * genérico que todavía no está disponible en esta rama.
     */
    private function notifyImportsDepartment(ProjectMaterialRequest $request): void
    {
        $recipientIds = User::where('profile_id', self::IMPORTACIONES_PROFILE_ID)
            ->whereHas('tenants', function ($q) {
                $q->where('tenants.id', session('tenant_id'));
            })
            ->pluck('id')->toArray();
        if (empty($recipientIds)) return;

        $project = Project::find($this->projectId);
        $projectTitle = $project->title ?? 'Proyecto';
        $senderName = Auth::user()->name ?? 'Usuario';

        $message = ProjectMessage::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'message' => 'Laboratorio envió una Solicitud de Materiales — pendiente de generar la Salida de Mercancía.',
        ]);

        foreach ($recipientIds as $userId) {
            if ((int) $userId === (int) Auth::id()) {
                continue; // no notificar a quien la envió, si él mismo es de Importaciones
            }

            $notification = ProjectNotification::create([
                'user_id' => $userId,
                'project_id' => $this->projectId,
                'message_id' => $message->id,
                'sender_id' => Auth::id(),
                'type' => 'solicitud_materiales',
            ]);

            try {
                broadcast(new NewProjectNotification(
                    $userId,
                    $this->projectId,
                    $projectTitle,
                    $senderName,
                    'Solicitud de Materiales pendiente de revisar',
                    'solicitud_materiales',
                    $notification->id
                ));
            } catch (\Throwable $e) {
                // Si Reverb está caído, la notificación queda igual en BD y
                // aparece al recargar — no debe romper el envío de la solicitud.
            }
        }
    }

    /**
     * Importaciones (Camilo) revisa la solicitud ya enviada y genera la
     * Salida de Mercancía: descuenta inventario y sincroniza con Alegra,
     * con la MISMA lógica que ya usa el módulo Movimientos
     * (MovementForm::saveMovement()) — se replica el flujo probado en vez
     * de duplicarlo desde cero.
     */
    public function generateOutboundMovement($requestId)
    {
        $this->ensureTenantConnection();

        if (!$this->canManageOutbound()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No tienes permiso para generar la salida de mercancía.']);
            return;
        }

        $request = ProjectMaterialRequest::with(['items' => function ($q) {
            $q->where('is_removed', false);
        }])->where('project_id', $this->projectId)->where('status', 'revisada')->find($requestId);

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

        $project = Project::find($request->project_id);
        $outboundObservations = 'Salida a Proyecto #' . $request->project_id
            . ($project ? ' - ' . $project->name : '')
            . ' — Solicitud de materiales';

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
            'observations' => $outboundObservations,
        ] : [];

        $service = new MovementsService();
        $alegraResult = !empty($alegraData) ? $service->syncAdjustmentToApi($alegraData) : [
            'success' => true,
            'api_data_id' => null,
            'sync_skipped' => true,
        ];

        if (!$alegraResult['success']) {
            $userMessage = $alegraResult['message'] ?? 'Error desconocido al comunicarse con Alegra';
            Log::error('❌ [ProjectMaterialRequests] Alegra rechazó la salida de materiales', ['alegra_result' => $alegraResult]);
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
                'observations' => $outboundObservations,
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

            // ── Crea también un "Pedido" interno en Remisiones para que Bodega
            // lo vea en la pantalla de Pedidos y lo alistone/entregue. Es un
            // ticket 100% interno: sin cotización/cliente, nunca se factura
            // y no aparece en el Portal de Clientes.
            $lastRemission = InvRemissions::lockForUpdate()->orderBy('consecutive', 'desc')->first();
            $remissionConsecutive = $lastRemission ? $lastRemission->consecutive + 1 : 1;

            $remission = InvRemissions::create([
                'consecutive' => $remissionConsecutive,
                'status' => 'ALISTAMIENTO',
                'quoteId' => null,
                'project_id' => $request->project_id,
                'warehouseId' => $store->id,
                'userId' => Auth::id(),
                'created_by' => Auth::id(),
                'deliveryDate' => $project?->delivery_date,
                'expiration' => 0,
                'modify' => 0,
                'obs' => $outboundObservations,
                'flete' => 0,
            ]);

            foreach ($request->items as $reqItem) {
                if (!$reqItem->item_id) {
                    continue;
                }

                $invValue = InvValues::where('itemId', $reqItem->item_id)->first();

                InvDetailRemissions::create([
                    'remissionId' => $remission->id,
                    'itemId' => $reqItem->item_id,
                    'quantity' => $reqItem->quantity_requested,
                    'value' => $invValue ? floatval($invValue->values) : 0,
                    'tax' => 0,
                ]);
            }

            $request->update([
                'status' => 'salida_generada',
                'inventory_adjustment_id' => $movement->id,
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Salida de mercancía generada correctamente']);
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            Log::error('❌ [ProjectMaterialRequests] Error generando salida de mercancía', ['error' => $e->getMessage()]);
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al generar la salida: ' . $e->getMessage()]);
        }
    }

    public function cancelMaterialRequest()
    {
        $this->ensureTenantConnection();
        if (!$this->checkCanManage()) return;

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if ($request) {
            $request->items()->delete();
            $request->delete();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud cancelada']);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $canManage = $this->canManage();
        $canManageOutbound = $this->canManageOutbound();

        if (!$canManage && !$canManageOutbound) {
            return view('livewire.tenant.projects.project-material-requests', [
                'canManage' => false,
                'canManageOutbound' => false,
                'materialRequest' => null,
                'hasActiveErpMaterials' => false,
                'isClosed' => false,
            ]);
        }

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;

        // Se trae la última solicitud SIN importar el estado (incluye 'salida_generada')
        // para que, una vez ya se ejecutó la salida, la pantalla la muestre como
        // historial de solo lectura en vez de "olvidarla" y dejar abrir una nueva.
        $materialRequest = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->with(['items', 'requestedBy'])
            ->latest('id')
            ->first();

        $hasActiveErpMaterials = ProjectMaterial::where('project_id', $this->projectId)
            ->where('origin', 'erp')
            ->where('is_active', true)
            ->exists();

        return view('livewire.tenant.projects.project-material-requests', [
            'canManage' => $canManage,
            'canManageOutbound' => $canManageOutbound,
            'materialRequest' => $materialRequest,
            'hasActiveErpMaterials' => $hasActiveErpMaterials,
            'isClosed' => $isClosed,
        ]);
    }
}
