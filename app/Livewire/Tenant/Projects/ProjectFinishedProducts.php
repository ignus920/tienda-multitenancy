<?php

namespace App\Livewire\Tenant\Projects;

use Livewire\Component;
use App\Models\Tenant\Projects\Project;
use App\Models\Tenant\Projects\ProjectFinishedProduct;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Auth;

class ProjectFinishedProducts extends Component
{
    public $projectId;

    // Formulario de alta
    public $description = '';
    public $price = null;
    public $quantity = 1;

    // Edición inline
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

    public function addFinishedProduct()
    {
        $this->ensureTenantConnection();
        if ($this->checkNotClosed()) return;

        $this->validate([
            'description' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|numeric|min:0.01',
        ], [
            'description.required' => 'La descripción es obligatoria.',
            'price.required' => 'El precio es obligatorio.',
            'quantity.required' => 'La cantidad es obligatoria.',
        ]);

        ProjectFinishedProduct::create([
            'project_id' => $this->projectId,
            'description' => $this->description,
            'price' => $this->price,
            'quantity' => $this->quantity,
            'created_by' => Auth::id(),
        ]);

        $this->reset(['description', 'price']);
        $this->quantity = 1;
        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto terminado agregado']);
    }

    public function editFinishedProduct($id)
    {
        $this->ensureTenantConnection();
        $product = ProjectFinishedProduct::findOrFail($id);
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
        if ($this->checkNotClosed()) return;

        $this->validate([
            'editDescription' => 'required|string|max:255',
            'editPrice' => 'required|numeric|min:0',
            'editQuantity' => 'required|numeric|min:0.01',
        ]);

        $product = ProjectFinishedProduct::findOrFail($this->editingId);
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
        if ($this->checkNotClosed()) return;

        $product = ProjectFinishedProduct::find($id);
        if ($product) {
            $product->delete();
            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Producto terminado eliminado']);
        }
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $products = ProjectFinishedProduct::where('project_id', $this->projectId)
            ->orderBy('created_at', 'asc')
            ->get();

        $project = Project::find($this->projectId);
        $isClosed = $project ? in_array($project->status, ['terminado', 'cerrado_entregado']) : false;

        return view('livewire.tenant.projects.project-finished-products', [
            'products' => $products,
            'total' => $products->sum(fn ($p) => $p->price * $p->quantity),
            'isClosed' => $isClosed,
        ]);
    }
}
