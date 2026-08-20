<?php

namespace App\Livewire\Tenant\Warranties;

use App\Models\Tenant\Sales\VntWarranty;
use App\Models\Tenant\Sales\VntWarrantyItem;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;

class WarrantyDetailModal extends Component
{
    public $isOpen = false;
    public $warrantyId;
    protected $warranty;

    // Campos de interacción
    public $adminConcept = '';
    public $adminSolution = '';
    
    // Arrays para editar conceptos individuales por ítem de laboratorio e importaciones
    public $labConcepts = [];
    public $importsConcepts = [];

    protected $listeners = ['openWarrantyDetail' => 'open'];

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
        $this->warrantyId = $id;
        $this->loadWarranty();
        $this->isOpen = true;
    }

    public function loadWarranty()
    {
        $this->warranty = VntWarranty::with(['remission.quote.customer', 'items.item', 'items.evidences', 'user'])->find($this->warrantyId);
        
        if ($this->warranty) {
            $this->adminConcept = $this->warranty->admin_concept ?? '';
            $this->adminSolution = $this->warranty->admin_solution ?? '';
            
            $this->labConcepts = [];
            $this->importsConcepts = [];
            foreach ($this->warranty->items as $item) {
                $this->labConcepts[$item->id] = $item->lab_concept ?? '';
                $this->importsConcepts[$item->id] = $item->imports_concept ?? '';
            }
        }
    }

    public function close()
    {
        $this->isOpen = false;
        $this->warranty = null;
        $this->reset(['warrantyId', 'adminConcept', 'adminSolution', 'labConcepts', 'importsConcepts']);
    }

    // Acción A: Dar solución definitiva directo
    public function resolveDefinitively()
    {
        $this->validate([
            'adminSolution' => 'required|string|min:5'
        ]);

        try {
            $this->ensureTenantConnection();
            $this->warranty->update([
                'admin_concept' => $this->adminConcept ?: 'Caso resuelto directamente.',
                'admin_solution' => $this->adminSolution,
                'status' => 4, // Resuelto
                'resolved_at' => now()
            ]);

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Garantía resuelta y cerrada con éxito.']);
            $this->dispatch('refreshWarranties');
            $this->close();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Acción B: Derivar a Laboratorio
    public function sendToLab()
    {
        try {
            $this->ensureTenantConnection();
            $this->warranty->update([
                'admin_concept' => $this->adminConcept ?: 'Remitido a revisión de Laboratorio.',
                'status' => 2 // En Laboratorio
            ]);

            $this->dispatch('show-toast', ['type' => 'info', 'message' => 'Garantía remitida a Laboratorio con éxito.']);
            $this->dispatch('refreshWarranties');
            $this->close();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Acción C: Derivar a Importaciones
    public function sendToImports()
    {
        try {
            $this->ensureTenantConnection();
            $this->warranty->update([
                'admin_concept' => $this->adminConcept ?: 'Remitido a revisión de Importaciones.',
                'status' => 3 // En Importaciones
            ]);

            $this->dispatch('show-toast', ['type' => 'info', 'message' => 'Garantía remitida a Importaciones con éxito.']);
            $this->dispatch('refreshWarranties');
            $this->close();
        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // Responder desde Laboratorio
    public function saveLabConcept()
    {
        try {
            $this->ensureTenantConnection();
            DB::connection('tenant')->beginTransaction();

            foreach ($this->labConcepts as $itemId => $concept) {
                VntWarrantyItem::where('id', $itemId)->update([
                    'lab_concept' => $concept
                ]);
            }

            // Retornar a revisión de Admin para solución final
            $this->warranty->update([
                'status' => 1 // Regresa a Pendiente Admin
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Concepto técnico de Laboratorio registrado. Devuelto al administrador.']);
            $this->dispatch('refreshWarranties');
            $this->close();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al guardar concepto: ' . $e->getMessage()]);
        }
    }

    // Responder desde Importaciones
    public function saveImportsConcept()
    {
        try {
            $this->ensureTenantConnection();
            DB::connection('tenant')->beginTransaction();

            foreach ($this->importsConcepts as $itemId => $concept) {
                VntWarrantyItem::where('id', $itemId)->update([
                    'imports_concept' => $concept
                ]);
            }

            // Retornar a revisión de Admin para solución final
            $this->warranty->update([
                'status' => 1 // Regresa a Pendiente Admin
            ]);

            DB::connection('tenant')->commit();

            $this->dispatch('show-toast', ['type' => 'success', 'message' => 'Concepto de Importaciones registrado. Devuelto al administrador.']);
            $this->dispatch('refreshWarranties');
            $this->close();
        } catch (\Exception $e) {
            DB::connection('tenant')->rollBack();
            $this->dispatch('show-toast', ['type' => 'error', 'message' => 'Error al guardar concepto: ' . $e->getMessage()]);
        }
    }

    public function render()
    {
        if ($this->warrantyId && !$this->warranty) {
            $this->ensureTenantConnection();
            $this->warranty = VntWarranty::with(['remission.quote.customer', 'items.item', 'items.evidences', 'user'])->find($this->warrantyId);
        }

        return view('livewire.tenant.warranties.warranty-detail-modal');
    }
}
