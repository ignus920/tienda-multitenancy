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

    public $search = '';
    public $searchResults = [];
    public $selectedSuggestedItemId = '';
    public $assignedSuggestions = [];

    protected $rules = [
        'selectedSuggestedItemId' => 'required|integer',
    ];

    protected $messages = [
        'selectedSuggestedItemId.required' => 'Debe buscar y seleccionar un producto.',
    ];

    public function mount($itemId)
    {
        $this->itemId = $itemId;
        $this->ensureTenantConnection();
        $this->loadAssigned();
    }

    private function loadAssigned(): void
    {
        try {
            $suggestions = InvItemSuggested::with('suggestedItem.principalImage')
                ->where('item', $this->itemId)
                ->orderBy('id')
                ->get();

            // Se aplana a un array simple (id, nombre, código, url de miniatura) en vez de
            // dejar la relación anidada: 'suggested_item' como key de array choca con la
            // columna FK del mismo nombre (toArray() la pisa), y de paso ya resolvemos acá
            // la miniatura para que la vista no tenga que llamar métodos del modelo.
            $this->assignedSuggestions = $suggestions->map(function ($suggestion) {
                $item = $suggestion->suggestedItem;
                return [
                    'id'                => $suggestion->id,
                    'suggested_item_id' => $suggestion->suggested_item,
                    'name'              => $item->name ?? '—',
                    'internal_code'     => $item->internal_code ?? null,
                    'thumbnail_url'     => ($item && $item->principalImage) ? $item->getPrincipalThumbnailUrl() : null,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('ItemSuggestedProducts - Error cargando sugeridos: ' . $e->getMessage());
            $this->assignedSuggestions = [];
        }
    }

    public function updatedSearch(): void
    {
        $this->ensureTenantConnection();
        $this->selectedSuggestedItemId = '';

        if (strlen($this->search) < 2) {
            $this->searchResults = [];
            return;
        }

        $assignedIds = collect($this->assignedSuggestions)->pluck('suggested_item_id')->filter()->all();

        $words = array_filter(explode(' ', trim($this->search)));

        $query = Items::active()
            ->where('id', '!=', $this->itemId)
            ->whereNotIn('id', $assignedIds);

        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->where('name', 'like', '%' . $word . '%')
                  ->orWhere('internal_code', 'like', '%' . $word . '%')
                  ->orWhere('description', 'like', '%' . $word . '%');
            });
        }

        $this->searchResults = $query->limit(10)->get(['id', 'name', 'internal_code'])->map(function ($item) {
            return [
                'id'   => $item->id,
                'name' => $item->name,
                'code' => $item->internal_code,
            ];
        })->toArray();
    }

    public function selectSuggestedItem(int $itemId, string $itemName, string $itemCode = ''): void
    {
        $this->selectedSuggestedItemId = $itemId;
        $this->search = $itemCode ? "{$itemCode} - {$itemName}" : $itemName;
        $this->searchResults = [];
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

        $this->reset('selectedSuggestedItemId', 'search', 'searchResults');
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
