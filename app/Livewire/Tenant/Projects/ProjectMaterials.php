<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectMaterial;
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

    public function addErpMaterial($itemId, $itemName, $itemPrice)
    {
        $this->ensureTenantConnection();
        $this->validate([
            'quantity' => 'required|numeric|min:0.01'
        ], [
            'quantity.required' => 'La cantidad es obligatoria.',
            'quantity.min' => 'La cantidad debe ser mayor a cero.'
        ]);

        ProjectMaterial::create([
            'project_id' => $this->projectId,
            'item_id' => $itemId,
            'origin' => 'erp',
            'description' => $itemName,
            'quantity' => $this->quantity,
            'unit_value' => $itemPrice,
            'line_cost' => $this->quantity * $itemPrice,
            'observations' => $this->observations,
            'created_by' => Auth::id()
        ]);

        $this->reset(['search', 'searchResults', 'quantity', 'observations']);
        $this->quantity = 1;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Material agregado']);
    }

    public function addExternalMaterial()
    {
        $this->ensureTenantConnection();
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

    public function deleteMaterial($materialId)
    {
        $this->ensureTenantConnection();
        ProjectMaterial::where('id', $materialId)->delete();
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Línea eliminada']);
    }

    // --- Exportación (trait WithExport: exportExcel(), exportCsv(), exportPdf()) ---

    protected function getDataForExport()
    {
        $this->ensureTenantConnection();
        return ProjectMaterial::where('project_id', $this->projectId)
            ->orderBy('created_at', 'asc')
            ->get();
    }

    protected function getExportHeadings(): array
    {
        return ['Origen', 'Descripción', 'Cantidad', 'Precio Unitario', 'Costo', 'Observaciones'];
    }

    protected function getExportMapping($item = null)
    {
        if ($item === null) {
            return null;
        }
        return [
            $item->origin === 'erp' ? 'ERP' : 'Externo',
            $item->description,
            $item->quantity,
            $item->unit_value,
            $item->line_cost,
            $item->observations,
        ];
    }

    protected function getExportFilename(): string
    {
        return 'materiales_proyecto_' . $this->projectId . '_' . now()->format('Y-m-d_His');
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $materials = ProjectMaterial::where('project_id', $this->projectId)
            ->orderBy('created_at', 'asc')
            ->get();

        $subtotalErp = $materials->where('origin', 'erp')->sum('line_cost');
        $subtotalExterno = $materials->where('origin', 'externo')->sum('line_cost');

        return view('livewire.tenant.projects.project-materials', [
            'materials' => $materials,
            'subtotalErp' => $subtotalErp,
            'subtotalExterno' => $subtotalExterno,
            'total' => $subtotalErp + $subtotalExterno
        ]);
    }
}
