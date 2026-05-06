<?php

namespace App\Livewire\Tenant\Returns;

use App\Models\Tenant\Sales\VntReturn;
use App\Models\Tenant\Sales\VntReturnEvidence;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class ReturnModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $returnId;
    protected $vntReturn;
    
    // Campos para Laboratorio
    public $labQty;
    public $labObs;
    public $labFiles = [];

    // Campos para Bodega
    public $warehouseObs;

    // Campos para Contabilidad
    public $ncNumber;
    public $ncObs;
    public $ncFile;

    protected $listeners = ['openReturnModal' => 'open'];

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
        tenancy()->initialize($tenant);
    }

    public function open($id)
    {
        $this->ensureTenantConnection();
        $this->returnId = $id;
        $this->vntReturn = VntReturn::with(['remission.quote.customer', 'item', 'evidences'])->find($id);
        
        \Log::info("🔍 Datos de Devolución cargados", [
            'id' => $id,
            'remission_id' => $this->vntReturn->remission_id ?? 'null',
            'has_remission' => isset($this->vntReturn->remission),
            'has_item' => isset($this->vntReturn->item),
        ]);

        if ($this->vntReturn) {
            $this->labQty = $this->vntReturn->lab_qty;
            $this->labObs = $this->vntReturn->obs_lab;
            $this->warehouseObs = $this->vntReturn->obs_warehouse;
            $this->ncNumber = $this->vntReturn->nc_number;
            $this->ncObs = $this->vntReturn->obs_accounting;
            $this->isOpen = true;
        } else {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Devolución no encontrada']);
        }
    }

    public function close()
    {
        $this->isOpen = false;
        $this->vntReturn = null;
        $this->reset(['labFiles', 'ncFile', 'returnId']);
    }

    public function processLab()
    {
        $this->validate([
            'labQty' => 'required|numeric|min:0',
            'labObs' => 'required|string',
        ]);

        $this->vntReturn->update([
            'lab_qty' => $this->labQty,
            'obs_lab' => $this->labObs,
            'status' => 2, // Siguiente estado (según lógica legacy era 2 para Lab, pero aquí Lab procesa para pasar a Bodega/3)
            'lab_processed_at' => now(),
        ]);

        // Guardar evidencias
        foreach ($this->labFiles as $file) {
            $path = $file->store('returns/lab', 'public');
            VntReturnEvidence::create([
                'return_id' => $this->returnId,
                'file_path' => $path,
            ]);
        }

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Procesado por Laboratorio correctamente']);
        $this->dispatch('refreshReturns');
        $this->close();
    }

    public function processWarehouse()
    {
        $this->validate([
            'warehouseObs' => 'required|string',
        ]);

        $this->vntReturn->update([
            'obs_warehouse' => $this->warehouseObs,
            'status' => 3, // Pasa a Contabilidad
        ]);

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Procesado por Bodega correctamente']);
        $this->dispatch('refreshReturns');
        $this->close();
    }

    public function processAccounting()
    {
        $this->validate([
            'ncNumber' => 'required',
            'ncObs' => 'required',
            'ncFile' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $ncFilePath = null;
        if ($this->ncFile) {
            $ncFilePath = $this->ncFile->store('returns/nc', 'public');
        }

        $this->vntReturn->update([
            'nc_number' => $this->ncNumber,
            'obs_accounting' => $this->ncObs,
            'nc_file' => $ncFilePath,
            'status' => 4, // Finalizado
            'accounting_processed_at' => now(),
        ]);

        $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Devolución finalizada correctamente']);
        $this->dispatch('refreshReturns');
        $this->close();
    }

    public function render()
    {
        if ($this->returnId && !$this->vntReturn) {
            $this->ensureTenantConnection();
            $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($this->returnId);
        }

        return view('livewire.tenant.returns.return-modal', [
            'vntReturn' => $this->vntReturn
        ]);
    }
}
