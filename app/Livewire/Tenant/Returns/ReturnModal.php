<?php

namespace App\Livewire\Tenant\Returns;

use App\Models\Tenant\Sales\VntReturn;
use App\Models\Tenant\Sales\VntReturnEvidence;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReturnModal extends Component
{
    use WithFileUploads;

    public $isOpen = false;
    public $returnId;
    public $mode = 'info'; // 'info' o 'process'
    protected $vntReturn;
    
    // Campos para Laboratorio
    public $labQty;
    public $labObs;
    public $labFiles = [];
    public $tempImages = []; // Para previsualización temporal

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

    public function open($id, $mode = 'info')
    {
        $this->ensureTenantConnection();
        $this->returnId = $id;
        $this->mode = $mode;
        $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($id);
        
        Log::info("🔍 Modal Abierto - Depuración", [
            'id' => $id,
            'evidences_count' => $this->vntReturn ? $this->vntReturn->evidences->count() : 0,
            'db_name' => DB::connection('tenant')->getDatabaseName()
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
        $this->reset(['labFiles', 'tempImages', 'ncFile', 'returnId']);
    }

    public function updatedLabFiles()
    {
        $this->validate([
            'labFiles.*' => 'image|max:2048'
        ]);

        foreach ($this->labFiles as $file) {
            $this->tempImages[] = [
                'file' => $file,
                'url' => $file->temporaryUrl(),
            ];
        }
    }

    public function removeTempImage($index)
    {
        unset($this->tempImages[$index]);
        $this->tempImages = array_values($this->tempImages);
    }

    public function processLab()
    {
        $this->validate([
            'labQty' => 'required|numeric|min:0',
            'labObs' => 'required|string',
            'labFiles.*' => 'image|max:2048'
        ]);

        try {
            $this->ensureTenantConnection();
            DB::connection('tenant')->beginTransaction();

            $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($this->returnId);

            if (!$this->vntReturn) {
                $this->dispatch('show-toast', type: 'error', message: 'Devolución no encontrada');
                return;
            }

            $this->vntReturn->update([
                'lab_qty' => $this->labQty,
                'obs_lab' => $this->labObs,
                'status' => 3, // Pasa a Bodega
                'lab_processed_at' => now(),
            ]);

            // Guardar evidencias
            if (!empty($this->tempImages)) {
                foreach ($this->tempImages as $img) {
                    $path = $img['file']->store('returns/lab', 'public');
                    VntReturnEvidence::create([
                        'return_id' => $this->returnId,
                        'file_path' => $path,
                    ]);
                }
            }

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', type: 'success', message: 'Procesado por Laboratorio. Enviado a Bodega.');
            $this->dispatch('refreshReturns');
            $this->close();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', type: 'error', message: 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function processWarehouse()
    {
        $this->validate([
            'warehouseObs' => 'required|string',
        ]);

        $this->ensureTenantConnection();
        $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($this->returnId);

        if (!$this->vntReturn) {
            $this->dispatch('show-toast', type: 'error', message: 'Devolución no encontrada');
            return;
        }

        $this->vntReturn->update([
            'obs_warehouse' => $this->warehouseObs,
            'status' => 4, // Pasa a Contabilidad
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Procesado por Bodega correctamente');
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

        $this->ensureTenantConnection();
        $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($this->returnId);

        if (!$this->vntReturn) {
            $this->dispatch('show-toast', type: 'error', message: 'Devolución no encontrada');
            return;
        }

        $ncFilePath = null;
        if ($this->ncFile) {
            $ncFilePath = $this->ncFile->store('returns/nc', 'public');
        }

        $this->vntReturn->update([
            'nc_number' => $this->ncNumber,
            'obs_accounting' => $this->ncObs,
            'nc_file' => $ncFilePath,
            'status' => 6, // Finalizado (Total)
            'accounting_processed_at' => now(),
        ]);

        $this->dispatch('show-toast', type: 'success', message: 'Devolución finalizada correctamente');
        $this->dispatch('refreshReturns');
        $this->close();
    }

    public function render()
    {
        if ($this->returnId && !$this->vntReturn) {
            $this->ensureTenantConnection();
            $this->vntReturn = VntReturn::with(['remission', 'item', 'evidences'])->find($this->returnId);
        }

        \Log::info("🖼️ Render Modal - Estado Final", [
            'return_id' => $this->returnId,
            'vntReturn_exists' => $this->vntReturn ? true : false,
            'evidences_count' => $this->vntReturn ? $this->vntReturn->evidences->count() : 0,
            'db_name' => \DB::connection('tenant')->getDatabaseName()
        ]);

        return view('livewire.tenant.returns.return-modal', [
            'vntReturn' => $this->vntReturn
        ]);
    }
}
