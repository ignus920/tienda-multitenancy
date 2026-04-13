<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemAccesorios;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ItemAccesorios extends Component
{
    public $itemId;

    public $selectedInsumoId = '';
    public $observacion = '';
    public $assignedAccesorios = [];
    public $availableInsumos = [];

    protected $rules = [
        'selectedInsumoId' => 'required|integer',
        'observacion'      => 'nullable|string|max:255',
    ];

    protected $messages = [
        'selectedInsumoId.required' => 'Debe seleccionar un insumo.',
    ];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadData();
    }

    private function loadData(): void
    {
        try {
            $this->availableInsumos = Items::on('tenant')
                ->where('type', 'INSUMO')
                ->where('status', 1)
                ->orderBy('name')
                ->get(['id', 'name', 'internal_code'])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ItemAccesorios - Error cargando insumos: ' . $e->getMessage());
            $this->availableInsumos = [];
        }

        $this->loadAssigned();
    }

    private function loadAssigned(): void
    {
        try {
            $this->assignedAccesorios = InvItemAccesorios::with('insumo')
                ->where('item', $this->itemId)
                ->orderBy('id')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ItemAccesorios - Error cargando accesorios: ' . $e->getMessage());
            $this->assignedAccesorios = [];
        }
    }

    public function addAccesorio(): void
    {
        $this->ensureTenantConnection();
        $this->validate();

        $alreadyAssigned = InvItemAccesorios::where('item', $this->itemId)
            ->where('insumo', $this->selectedInsumoId)
            ->exists();

        if ($alreadyAssigned) {
            $this->addError('selectedInsumoId', 'Este insumo ya está asignado como accesorio.');
            return;
        }

        InvItemAccesorios::create([
            'item'        => $this->itemId,
            'insumo'      => $this->selectedInsumoId,
            'observacion' => $this->observacion ?: null,
        ]);

        $this->reset('selectedInsumoId', 'observacion');
        $this->loadAssigned();
        $this->dispatch('notify', type: 'success', message: 'Accesorio agregado correctamente.');
    }

    public function removeAccesorio(int $id): void
    {
        $this->ensureTenantConnection();
        $record = InvItemAccesorios::find($id);

        if ($record) {
            $record->delete();
            $this->loadAssigned();
            $this->dispatch('notify', type: 'success', message: 'Accesorio eliminado correctamente.');
        }
    }

    private function ensureTenantConnection(): void
    {
        $tenantId = session('tenant_id');

        if (!$tenantId) {
            return;
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return;
        }

        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        return view('livewire.tenant.items.item-accesorios');
    }
}
