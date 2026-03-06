<?php

namespace App\Livewire\Tenant\Production;

use Livewire\Component;
use App\Models\Tenant\Production\PrdProcess;
use App\Models\Tenant\Production\PrdProcessItem;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ProcessRegItem extends Component
{
    public $itemId;

    public $selectedProcessId = '';

    public $assignedProcesses = [];
    public $availableProcesses = [];

    protected $rules = [
        'selectedProcessId' => 'required|integer',
    ];

    protected $messages = [
        'selectedProcessId.required' => 'Debe seleccionar un proceso.',
    ];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadData();
    }

    private function loadData()
    {
        try {
            $this->availableProcesses = PrdProcess::where('status', 1)
                ->orderBy('name')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ProcessRegItem - Error cargando procesos: ' . $e->getMessage());
            $this->availableProcesses = [];
        }

        $this->loadAssignedProcesses();
    }

    private function loadAssignedProcesses()
    {
        try {
            $this->assignedProcesses = PrdProcessItem::with('process')
                ->where('itemId', $this->itemId)
                ->whereNull('deleted_at')
                ->orderBy('process_route_order')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ProcessRegItem - Error cargando procesos asignados: ' . $e->getMessage());
            $this->assignedProcesses = [];
        }
    }

    public function addProcess()
    {
        $this->ensureTenantConnection();
        $this->validate();

        $alreadyAssigned = PrdProcessItem::where('itemId', $this->itemId)
            ->where('processId', $this->selectedProcessId)
            ->whereNull('deleted_at')
            ->exists();

        if ($alreadyAssigned) {
            $this->addError('selectedProcessId', 'Este proceso ya está asignado a este item.');
            return;
        }

        // Calcular el siguiente orden automáticamente
        $nextOrder = PrdProcessItem::where('itemId', $this->itemId)
            ->whereNull('deleted_at')
            ->max('process_route_order') ?? 0;
        $nextOrder += 1;

        PrdProcessItem::create([
            'processId'           => $this->selectedProcessId,
            'itemId'              => $this->itemId,
            'process_route_order' => $nextOrder,
        ]);

        $this->reset(['selectedProcessId']);
        $this->loadAssignedProcesses();
        $this->dispatch('notify', type: 'success', message: 'Proceso agregado correctamente.');
    }

    public function removeProcess($processItemId)
    {
        $this->ensureTenantConnection();
        $record = PrdProcessItem::find($processItemId);
        if ($record) {
            $record->delete();
            $this->loadAssignedProcesses();
            $this->dispatch('notify', type: 'success', message: 'Proceso eliminado correctamente.');
        }
    }

    public function reorderProcesses(array $orderedIds): void
    {
        $this->ensureTenantConnection();

        foreach ($orderedIds as $index => $id) {
            PrdProcessItem::where('id', $id)
                ->where('itemId', $this->itemId)
                ->whereNull('deleted_at')
                ->update(['process_route_order' => $index + 1]);
        }

        $this->loadAssignedProcesses();
    }

    private function ensureTenantConnection()
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return redirect()->route('tenant.select');
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            session()->forget('tenant_id');
            return redirect()->route('tenant.select');
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        tenancy()->initialize($tenant);
    }

    public function render()
    {
        return view('livewire.tenant.production.process-reg-item');
    }
}
