<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMaterial;
use App\Models\Tenant\Projects\ProjectMaterialRequest;
use App\Models\Tenant\Projects\ProjectMaterialRequestItem;
use App\Models\Tenant\Projects\ProjectParticipant;
use App\Models\Tenant\Projects\ProjectMessage;
use App\Models\Tenant\Projects\ProjectNotification;
use App\Events\Tenant\Projects\NewProjectMessage;
use App\Events\Tenant\Projects\NewProjectNotification;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Traits\Livewire\WithExport;
use Illuminate\Support\Facades\Auth;

class ProjectMaterials extends Component
{
    use WithExport;

    const LABORATORIO_PROFILE_ID = 20;

    public $projectId;

    // Buscador de productos ERP
    public $search = '';
    public $searchResults = [];
    public $quantity = 1;
    public $observations = '';

    // Formulario de producto externo
    public $showExternalForm = false;
    public $externalDescription = '';
    public $externalUnitValue = null;
    public $externalQuantity = 1;
    public $externalObservations = '';

    // Edición inline de una línea existente
    public $editingMaterialId = null;
    public $editQuantity = null;
    public $editDescription = '';
    public $editUnitValue = null;

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

    /**
     * Mientras la Solicitud de Materiales siga "pendiente" (Laboratorio
     * todavía no la envía a Importaciones), se mantiene reflejando la
     * lista real de Materiales: si un material se desactiva/reactiva,
     * cambia de cantidad, o se agrega uno nuevo, la Solicitud pendiente
     * se actualiza igual — evita pedirle a Bodega algo que ya no aplica.
     * Una vez enviada ('revisada'/'salida_generada') queda congelada.
     */
    private function syncMaterialToPendingRequest(ProjectMaterial $material): void
    {
        if ($material->origin !== 'erp' || !$material->item_id) {
            return;
        }

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if (!$request) return;

        $requestItem = ProjectMaterialRequestItem::where('request_id', $request->id)
            ->where('project_material_id', $material->id)
            ->first();

        if (!$material->is_active) {
            if ($requestItem) {
                $requestItem->update(['is_removed' => true]);
                broadcast(new \App\Events\Tenant\Projects\MaterialsListSynced($this->projectId));
            }
            return;
        }

        $quantity = (int) ceil($material->quantity);

        if ($requestItem) {
            $requestItem->update([
                'quantity_requested' => $quantity,
                'is_removed' => false,
            ]);
        } else {
            ProjectMaterialRequestItem::create([
                'request_id' => $request->id,
                'project_material_id' => $material->id,
                'item_id' => $material->item_id,
                'description' => $material->description,
                'quantity_requested' => $quantity,
                'is_removed' => false,
            ]);
        }

        broadcast(new \App\Events\Tenant\Projects\MaterialsListSynced($this->projectId));
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

    public function updatedSearch()
    {
        $this->ensureTenantConnection();
        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $words = array_filter(explode(' ', trim($this->search)));

        $query = Items::with('invValues')->active();
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

    public $selectedErpItem = null;

    public function selectErpMaterial($itemId, $itemName, $itemPrice, $itemCode = '')
    {
        $this->selectedErpItem = [
            'id' => $itemId,
            'name' => $itemName,
            'price' => $itemPrice,
            'code' => $itemCode
        ];
        
        $priceFormatted = '$' . number_format($itemPrice, 2);
        if ($itemCode) {
            $this->search = "{$itemCode} - {$itemName} ({$priceFormatted})";
        } else {
            $this->search = "{$itemName} ({$priceFormatted})";
        }
        
        $this->searchResults = [];
    }

    public function addErpMaterial()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;
        if (!$this->selectedErpItem) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Debes seleccionar un producto primero.']);
            return;
        }

        $this->ensureTenantConnection();
        $this->validate([
            'quantity' => 'required|numeric|min:0.01'
        ], [
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad debe ser mayor a cero.'
        ]);

        $fullDescription = !empty($this->selectedErpItem['code']) 
            ? "{$this->selectedErpItem['code']} - {$this->selectedErpItem['name']}" 
            : $this->selectedErpItem['name'];

        $material = ProjectMaterial::create([
            'project_id' => $this->projectId,
            'item_id' => $this->selectedErpItem['id'],
            'origin' => 'erp',
            'description' => $fullDescription,
            'quantity' => $this->quantity,
            'unit_value' => $this->selectedErpItem['price'],
            'line_cost' => $this->quantity * $this->selectedErpItem['price'],
            'observations' => $this->observations,
            'created_by' => Auth::id()
        ]);

        $this->syncMaterialToPendingRequest($material);

        $this->reset(['search', 'quantity', 'observations', 'searchResults', 'selectedErpItem']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Material agregado']);
    }

    public function addExternalMaterial()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;
        $this->validate([
            'externalDescription' => 'required|string|max:255',
            'externalUnitValue' => 'required|numeric|min:0',
            'externalQuantity' => 'required|numeric|min:0.01'
        ], [
            'externalDescription.required' => 'La descripción es obligatoria.',
            'externalUnitValue.required' => 'El valor unitario es obligatorio.',
            'externalQuantity.required' => 'La cantidad es obligatoria.'
        ]);

        ProjectMaterial::create([
            'project_id' => $this->projectId,
            'item_id' => null,
            'origin' => 'externo',
            'description' => $this->externalDescription,
            'quantity' => $this->externalQuantity,
            'unit_value' => $this->externalUnitValue,
            'line_cost' => $this->externalQuantity * $this->externalUnitValue,
            'observations' => $this->externalObservations,
            'created_by' => Auth::id()
        ]);

        $this->reset(['externalDescription', 'externalUnitValue', 'externalQuantity', 'externalObservations']);
        $this->externalQuantity = 1;
        $this->showExternalForm = false;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto externo agregado']);
    }

    public function editMaterial($materialId)
    {
        $this->ensureTenantConnection();
        $material = ProjectMaterial::findOrFail($materialId);
        $this->editingMaterialId = $material->id;
        $this->editQuantity = $material->quantity;
        $this->editDescription = $material->description;
        $this->editUnitValue = $material->unit_value;
    }

    public function cancelEdit()
    {
        $this->reset(['editingMaterialId', 'editQuantity', 'editDescription', 'editUnitValue']);
    }

    public function saveEdit()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;
        $this->validate([
            'editQuantity' => 'required|numeric|min:0.01',
            'editDescription' => 'required|string|max:255',
            'editUnitValue' => 'required|numeric|min:0'
        ]);

        $material = ProjectMaterial::findOrFail($this->editingMaterialId);

        $data = ['quantity' => $this->editQuantity];
        if ($material->origin === 'externo') {
            $data['description'] = $this->editDescription;
            $data['unit_value'] = $this->editUnitValue;
        }
        $data['line_cost'] = $data['quantity'] * ($data['unit_value'] ?? $material->unit_value);

        $material->update($data);
        $this->syncMaterialToPendingRequest($material);

        $this->cancelEdit();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Línea actualizada']);
    }

    #[On('deactivateMaterial')]
    public function deactivateMaterial($materialId, $reason)
    {
        $this->ensureTenantConnection();
        $material = ProjectMaterial::find($materialId);
        if ($material) {
            $material->is_active = false;
            $material->deactivation_reason = $reason;
            $material->save();
            $this->syncMaterialToPendingRequest($material);
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Línea desactivada']);
        }
    }

    public function reactivateMaterial($materialId)
    {
        $this->ensureTenantConnection();
        $material = ProjectMaterial::find($materialId);
        if ($material) {
            $material->is_active = true;
            $material->deactivation_reason = null;
            $material->save();
            $this->syncMaterialToPendingRequest($material);
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Línea reactivada']);
        }
    }

    #[On('clearMaterialList')]
    public function clearMaterialList($reason)
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;
        $materials = ProjectMaterial::where('project_id', $this->projectId)->get();

        foreach ($materials as $material) {
            $material->clear_reason = $reason;
            $material->save();
            $material->delete(); // Soft Delete
        }

        // Se eliminó toda la lista — si había una Solicitud pendiente, ya no
        // tiene sentido: no queda nada que pedirle a Bodega.
        $hadPendingRequest = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->exists();

        ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->get()
            ->each(function (ProjectMaterialRequest $request) {
                $request->items()->delete();
                $request->delete();
            });

        if ($hadPendingRequest) {
            broadcast(new \App\Events\Tenant\Projects\MaterialsListSynced($this->projectId));
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Lista de materiales eliminada y archivada']);
    }

    /**
     * Solo Laboratorio puede cerrar/abrir la Lista de Materiales — ni
     * siquiera Super Administrador/Administrador, así lo pidió Edwin
     * explícitamente para que quede claro quién da el visto bueno.
     */
    private function isLaboratorio(): bool
    {
        $user = Auth::user();
        return $user && (int) $user->profile_id === self::LABORATORIO_PROFILE_ID;
    }

    /**
     * Laboratorio cierra la lista: ya no se puede agregar/editar/desactivar
     * materiales hasta que la vuelvan a abrir, y esto es lo que habilita
     * la pestaña "Solicitud de Materiales" para el resto del equipo.
     */
    public function closeMaterialsList()
    {
        $this->ensureTenantConnection();
        if (!$this->isLaboratorio()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Solo Laboratorio puede cerrar la lista de materiales.']);
            return;
        }
        if ($this->checkNotClosed()) return;

        $project = Project::find($this->projectId);
        if (!$project || $project->materials_locked) return;

        if (!ProjectMaterial::where('project_id', $this->projectId)->where('is_active', true)->exists()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'No hay materiales activos para cerrar la lista.']);
            return;
        }

        $project->update([
            'materials_locked' => true,
            'materials_locked_by' => Auth::id(),
            'materials_locked_at' => now(),
        ]);

        $this->notifyParticipants(
            'Laboratorio cerró la Lista de Materiales — ya está lista para generar la Solicitud de Materiales.',
            'materiales_cerrada',
            'Lista de Materiales cerrada'
        );

        // Lógica de inicio automático de producción/desarrollo al cerrar materiales
        $wasAutoStarted = false;
        $oldStatus = $project->status;

        if ($project->type === 'external' && $project->status === 'orden_creada') {
            $project->update(['status' => 'en_produccion']);
            $wasAutoStarted = true;
        } elseif ($project->type === 'internal' && $project->status === 'cotizacion') {
            $project->update(['status' => 'en_produccion']);
            $wasAutoStarted = true;
        }

        if ($wasAutoStarted) {
            \App\Models\Tenant\Projects\ProjectStatusHistory::create([
                'project_id' => $project->id,
                'from_status' => $oldStatus,
                'to_status' => 'en_produccion',
                'changed_by' => Auth::id()
            ]);

            $autoMessage = ProjectMessage::create([
                'project_id' => $project->id,
                'user_id' => Auth::id(),
                'message' => "**AVANCE AUTOMÁTICO**\n\nAl cerrar la Lista de Materiales, el proyecto ha iniciado su Producción/Desarrollo automáticamente."
            ]);
            broadcast(new NewProjectMessage($autoMessage));
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Lista de materiales cerrada']);
    }

    /**
     * Laboratorio la vuelve a abrir si hace falta agregar o corregir algo
     * — revierte el cierre y vuelve a ocultar "Solicitud de Materiales".
     */
    public function openMaterialsList()
    {
        $this->ensureTenantConnection();
        if (!$this->isLaboratorio()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Solo Laboratorio puede abrir la lista de materiales.']);
            return;
        }
        if ($this->checkNotClosed()) return;

        $project = Project::find($this->projectId);
        if (!$project || !$project->materials_locked) return;

        $project->update([
            'materials_locked' => false,
            'materials_locked_by' => null,
            'materials_locked_at' => null,
        ]);

        $this->notifyParticipants(
            'Laboratorio volvió a abrir la Lista de Materiales para agregar o corregir productos.',
            'materiales_abierta',
            'Lista de Materiales reabierta'
        );

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Lista de materiales abierta de nuevo']);
    }

    /**
     * Avisa a todos los participantes del proyecto (menos a quien ejecutó
     * la acción) con un mensaje en el Chat + notificación en la campana,
     * igual que ya se hace para la Solicitud de Materiales.
     */
    private function notifyParticipants(string $chatMessage, string $type, string $notificationTitle): void
    {
        $recipientIds = ProjectParticipant::where('project_id', $this->projectId)->pluck('user_id')->toArray();
        if (empty($recipientIds)) return;

        $project = Project::find($this->projectId);
        $projectTitle = $project->title ?? 'Proyecto';
        $senderName = Auth::user()->name ?? 'Usuario';

        $message = ProjectMessage::create([
            'project_id' => $this->projectId,
            'user_id' => Auth::id(),
            'message' => $chatMessage,
        ]);

        broadcast(new NewProjectMessage($message));

        foreach ($recipientIds as $userId) {
            if ((int) $userId === (int) Auth::id()) {
                continue;
            }

            $notification = ProjectNotification::create([
                'user_id' => $userId,
                'project_id' => $this->projectId,
                'message_id' => $message->id,
                'sender_id' => Auth::id(),
                'type' => $type,
            ]);

            try {
                broadcast(new NewProjectNotification(
                    $userId,
                    $this->projectId,
                    $projectTitle,
                    $senderName,
                    $notificationTitle,
                    $type,
                    $notification->id
                ));
            } catch (\Throwable $e) {
                // Si Reverb está caído, la notificación queda igual en BD y
                // aparece al recargar — no debe romper la acción principal.
            }
        }
    }

    // --- Exportación (trait WithExport: exportCsv(), exportPdf()) ---
    // exportExcel está sobreescrito aquí para aplicar formato personalizado

    public function exportExcel()
    {
        $this->ensureTenantConnection();
        $materials = $this->getDataForExport();
        $project = \App\Models\Tenant\Projects\Project::with('customer')->find($this->projectId);
        
        $projectName = 'Proyecto';
        $clientName = 'Cliente';

        if ($project) {
            $projectName = $project->title ?: 'Proyecto';
            if ($project->customer) {
                $clientName = $project->customer->businessName ?? trim(($project->customer->firstName ?? '') . ' ' . ($project->customer->lastName ?? ''));
            }
        }

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\ProjectMaterialsExport($materials, $projectName, $clientName),
            $this->getExportFilename() . '.xlsx'
        );
    }

    public function getDataForExport()
    {
        $this->ensureTenantConnection();
        return ProjectMaterial::where('project_id', $this->projectId)
            ->with('item.locations')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getExportHeadings(): array
    {
        return ['Origen', 'Picking', 'Descripción', 'Cantidad', 'Precio Unitario', 'Costo', 'Observaciones'];
    }

    public function getExportMapping($item = null)
    {
        if ($item === null) {
            return null;
        }

        $picking = $item->item?->picking;

        return [
            $item->origin === 'erp' ? 'ERP' : 'Externo',
            ($picking && $picking !== 'N/A') ? $picking : '',
            $item->description,
            $item->quantity,
            $item->unit_value,
            $item->line_cost,
            $item->observations,
        ];
    }

    public function getExportFilename(): string
    {
        $project = \App\Models\Tenant\Projects\Project::with('customer')->find($this->projectId);
        $projectName = 'proyecto';
        $clientName = 'cliente';

        if ($project) {
            if (!empty($project->title)) {
                $wordsProject = array_filter(explode(' ', trim($project->title)));
                $projectName = implode('_', array_slice($wordsProject, 0, 2));
            }
            if ($project->customer) {
                $customerName = $project->customer->businessName ?? trim(($project->customer->firstName ?? '') . ' ' . ($project->customer->lastName ?? ''));
                if (!empty($customerName)) {
                    $wordsClient = array_filter(explode(' ', trim($customerName)));
                    $wordsClient = array_values($wordsClient);
                    $clientName = $wordsClient[0] ?? 'cliente';
                }
            }
        }

        return $projectName . '_' . $clientName;
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $materials = ProjectMaterial::where('project_id', $this->projectId)
            ->with('item.locations')
            ->orderBy('created_at', 'asc')
            ->get();

        // Stock disponible en tiempo real (suma de todas las bodegas) — se
        // recalcula en cada render para nunca marcar con datos viejos. Solo
        // aplica a materiales activos del ERP (externos no tienen stock físico).
        foreach ($materials as $material) {
            $material->available_stock = null;
            $material->insufficient_stock = false;
            if ($material->origin === 'erp' && $material->item_id && $material->is_active) {
                $stock = (int) ($material->item?->invItemsStore()->sum('stock_items_store') ?? 0);
                $material->available_stock = $stock;
                $material->insufficient_stock = $material->quantity > $stock;
            }
        }

        $subtotalErp = $materials->where('origin', 'erp')->where('is_active', true)->sum('line_cost');
        $subtotalExterno = $materials->where('origin', 'externo')->where('is_active', true)->sum('line_cost');

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;
        $materialsLocked = $project ? (bool) $project->materials_locked : false;

        $hasSalidaGenerada = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'salida_generada')
            ->exists();

        return view('livewire.tenant.projects.project-materials', [
            'materials' => $materials,
            'subtotalErp' => $subtotalErp,
            'subtotalExterno' => $subtotalExterno,
            'total' => $subtotalErp + $subtotalExterno,
            'isClosed' => $isClosed,
            'materialsLocked' => $materialsLocked,
            'hasSalidaGenerada' => $hasSalidaGenerada,
            'isLaboratorio' => $this->isLaboratorio(),
            'materialsLockedByName' => $materialsLocked ? optional($project->materialsLockedBy)->name : null,
            'materialsLockedAt' => $materialsLocked ? $project->materials_locked_at : null,
        ]);
    }
}
