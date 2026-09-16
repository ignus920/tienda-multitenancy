<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use Livewire\Attributes\On;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMaterial;
use App\Models\Tenant\Projects\ProjectMaterialRequest;
use App\Models\Tenant\Projects\ProjectMaterialRequestItem;
use App\Models\Tenant\Items\Items;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use App\Traits\Livewire\WithExport;
use Illuminate\Support\Facades\Auth;

class ProjectMaterials extends Component
{
    use WithExport;

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

        ProjectMaterial::create([
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
        
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Lista de materiales eliminada y archivada']);
    }

    // --- Solicitud de Materiales (a partir de los materiales origen ERP) ---
    // Genera una lista aparte, redondeada a unidades enteras hacia arriba,
    // que Laboratorio puede ajustar (sobrantes en bodega de laboratorio)
    // SIN tocar la lista original de materiales del proyecto (arriba).

    /**
     * Crea una nueva solicitud a partir de los materiales activos de
     * origen ERP. Si ya existe una solicitud sin convertir en salida
     * (pendiente/revisada), no crea otra — se sigue editando esa misma.
     */
    public function generateMaterialRequest()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;

        $existing = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->whereIn('status', ['pendiente', 'revisada'])
            ->exists();

        if ($existing) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Ya hay una solicitud de materiales en curso para este proyecto.']);
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

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if (!$request) return;

        if ($request->items()->where('is_removed', false)->doesntExist()) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'La solicitud no puede quedar vacía — debe tener al menos un producto sin quitar.']);
            return;
        }

        $request->update(['status' => 'revisada']);
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud enviada a Importaciones']);
    }

    public function cancelMaterialRequest()
    {
        $this->ensureTenantConnection();

        $request = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->where('status', 'pendiente')
            ->first();

        if ($request) {
            $request->items()->delete();
            $request->delete();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Solicitud cancelada']);
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

        $subtotalErp = $materials->where('origin', 'erp')->where('is_active', true)->sum('line_cost');
        $subtotalExterno = $materials->where('origin', 'externo')->where('is_active', true)->sum('line_cost');

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;

        $materialRequest = ProjectMaterialRequest::where('project_id', $this->projectId)
            ->whereIn('status', ['pendiente', 'revisada'])
            ->with('items')
            ->latest('id')
            ->first();

        $hasActiveErpMaterials = $materials->where('origin', 'erp')->where('is_active', true)->isNotEmpty();

        return view('livewire.tenant.projects.project-materials', [
            'materials' => $materials,
            'subtotalErp' => $subtotalErp,
            'subtotalExterno' => $subtotalExterno,
            'total' => $subtotalErp + $subtotalExterno,
            'isClosed' => $isClosed,
            'materialRequest' => $materialRequest,
            'hasActiveErpMaterials' => $hasActiveErpMaterials,
        ]);
    }
}
