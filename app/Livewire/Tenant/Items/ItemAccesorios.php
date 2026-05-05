<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemAccesorios;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ItemAccesorios extends Component
{
    use WithFileUploads;

    public $itemId;

    public $selectedInsumoId = '';
    public $observacion = '';
    public $quantity = 1;
    public $photo; // Nueva propiedad para la imagen
    public $assignedAccesorios = [];
    public $availableInsumos = [];

    protected $rules = [
        'selectedInsumoId' => 'required|integer',
        'quantity'         => 'required|integer|min:1',
        'observacion'      => 'nullable|string|max:255',
        'photo'            => 'nullable|image|max:2048', // 2MB max
    ];

    protected $messages = [
        'selectedInsumoId.required' => 'Debe seleccionar un insumo.',
        'quantity.required'         => 'La cantidad es obligatoria.',
        'quantity.min'              => 'La cantidad debe ser al menos 1.',
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

        $imagePath = null;
        if ($this->photo) {
            $tenantId = session('tenant_id', 'default');
            $imagePath = $this->photo->store("items/{$tenantId}/accessories", 'public');
            Log::info('📸 Imagen de accesorio guardada', ['path' => $imagePath]);
        }

        InvItemAccesorios::create([
            'item'        => $this->itemId,
            'insumo'      => $this->selectedInsumoId,
            'quantity'    => $this->quantity,
            'image'       => $imagePath,
            'observacion' => $this->observacion ?: null,
        ]);

        Log::info('✅ Accesorio registrado en DB', [
            'item' => $this->itemId,
            'insumo' => $this->selectedInsumoId,
            'image' => $imagePath
        ]);

        $this->reset('selectedInsumoId', 'observacion', 'quantity', 'photo');
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
