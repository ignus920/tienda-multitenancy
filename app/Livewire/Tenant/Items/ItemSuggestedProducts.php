<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\InvItemSuggested;
use App\Models\Auth\Tenant;
use App\Services\Tenant\TenantManager;
use Illuminate\Support\Facades\Log;

class ItemSuggestedProducts extends Component
{
    const MAX_SUGGESTIONS = 6;

    public $itemId;

    public $selectedSuggestedItemId = '';
    public $assignedSuggestions = [];
    public $availableItems = [];

    protected $rules = [
        'selectedSuggestedItemId' => 'required|integer',
    ];

    protected $messages = [
        'selectedSuggestedItemId.required' => 'Debe seleccionar un producto.',
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
            $this->availableItems = Items::on('tenant')
                ->where('status', 1)
                ->where('id', '!=', $this->itemId)
                ->orderBy('name')
                ->get(['id', 'name', 'internal_code'])
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ItemSuggestedProducts - Error cargando productos: ' . $e->getMessage());
            $this->availableItems = [];
        }

        $this->loadAssigned();
    }

    private function loadAssigned(): void
    {
        try {
            $this->assignedSuggestions = InvItemSuggested::with('suggestedItem')
                ->where('item', $this->itemId)
                ->orderBy('id')
                ->get()
                ->toArray();
        } catch (\Exception $e) {
            Log::error('ItemSuggestedProducts - Error cargando sugeridos: ' . $e->getMessage());
            $this->assignedSuggestions = [];
        }
    }

    public function addSuggestion(): void
    {
        $this->ensureTenantConnection();
        $this->validate();

        if ((int) $this->selectedSuggestedItemId === (int) $this->itemId) {
            $this->addError('selectedSuggestedItemId', 'Un producto no puede sugerirse a sí mismo.');
            return;
        }

        if (count($this->assignedSuggestions) >= self::MAX_SUGGESTIONS) {
            $this->addError('selectedSuggestedItemId', 'Máximo ' . self::MAX_SUGGESTIONS . ' productos sugeridos por producto.');
            return;
        }

        $alreadyAssigned = InvItemSuggested::where('item', $this->itemId)
            ->where('suggested_item', $this->selectedSuggestedItemId)
            ->exists();

        if ($alreadyAssigned) {
            $this->addError('selectedSuggestedItemId', 'Este producto ya está asignado como sugerido.');
            return;
        }

        InvItemSuggested::create([
            'item'           => $this->itemId,
            'suggested_item' => $this->selectedSuggestedItemId,
        ]);

        $this->reset('selectedSuggestedItemId');
        $this->loadAssigned();
        $this->dispatch('notify', type: 'success', message: 'Producto sugerido agregado correctamente.');
    }

    public function removeSuggestion(int $id): void
    {
        $this->ensureTenantConnection();
        $record = InvItemSuggested::find($id);

        if ($record) {
            $record->delete();
            $this->loadAssigned();
            $this->dispatch('notify', type: 'success', message: 'Producto sugerido eliminado correctamente.');
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
        return view('livewire.tenant.items.item-suggested-products');
    }
}
