<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Category;
use App\Services\Tenant\TenantManager;
use App\Models\Auth\Tenant;

class ManageItems extends Component
{

    use WithPagination;

    protected $listeners = [
        'commandSelected' => 'onCommandSelected',
        'brandSelected' => 'onBrandSelected',
        'houseSelected' => 'onHouseSelected',
        'purchaseUnitSelected' => 'onPurchaseUnitSelected',
        'consumptionUnitSelected' => 'onConsumptionUnitSelected'
    ];

    // Propiedades para el formulario
    public $item_id;
    public $category_id;
    public $name;
    public $internal_code;
    public $sku;
    public $description;
    public $type;
    public $commandId;
    public $brandId;
    public $houseId;
    public $purchase_unit;
    public $consumption_unit;
    
    // Propiedades para la tabla
    public $search = '';
    public $sortField = 'name';
    public $sortDirection = 'asc';
    public $showModal = false;
    public $confirmingItemDeletion = false;
    public $itemIdToDelete;
    public $perPage = 10;

    // tipos disponibles (puedes externalizarlo si lo prefieres)
    public $types = [
        'COMBO' => 'Combo',
        'COMPRA_NACIONAL' => 'Compra nacional',
        'IMPORTADO' => 'Importado',
        'PRODUCIDO' => 'Producido',
    ];

    protected $rules =[
            'category_id' => 'required',
            'name' => 'required|min:3',
            'type' => 'required',
            'internal_code' => 'nullable|string',
            'brandId' => 'nullable|string',
            'houseId' => 'nullable|string',
            'purchase_unit' => 'nullable|string',
            'consumption_unit' => 'nullable|string',
        ];
    

    protected $queryString = [
        'search' => ['except' => ''],
        'perPage' => ['except' => 10],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortDirection = 'asc';
        }

        $this->sortField = $field;
        $this->resetPage();
        /*$this->sortDirection = $this->sortField === $field 
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;*/
    }

    public function mount()
    {
        $this->ensureTenantConnection();
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

        // Establecer conexión tenant
        $tenantManager = app(TenantManager::class);
        $tenantManager->setConnection($tenant);

        // Inicializar tenancy
        tenancy()->initialize($tenant);
    }

    public function render()
    {
        $this->ensureTenantConnection();

        $items = Items::query()
            ->when($this->search, function($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('sku', 'like', '%' . $this->search . '%')
                      ->orWhere('internal_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);

        return view('livewire.tenant.items.manage-items', [
            'items' => $items,
            'categories' => Category::where('status', 1)->get(),
            'types' => $this->types
        ]);
    }

    

    public function create()
    {
        $this->resetExcept(['categories', 'types']); // No reseteamos las listas de opciones
        $this->showModal = true;
        
        // Emitir eventos para inicializar los componentes hijos
        $this->dispatch('initializeCommand');
        $this->dispatch('initializeBrand');
        $this->dispatch('initializeHouse');
        $this->dispatch('initializePurchaseUnit');
        $this->dispatch('initializeConsumptionUnit');
    }

    public function edit(Items $item)
    {
        $this->ensureTenantConnection();

        $this->item_id = $item->id;
        $this->category_id = $item->category_id ?? $item->categoryId ?? null;
        $this->name = $item->name;
        $this->sku = $item->sku ?? null;
        $this->description = $item->description;
        $this->type = $item->type;
        $this->brandId = $item->brand;
        $this->purchase_unit = $item->purchase_unit;
        $this->consumption_unit = $item->consumption_unit;
        
        $this->showModal = true;
    }

    public function save()
    {
        $this->ensureTenantConnection();
        $this->validate();

        $itemData = [
            'categoryId' => $this->category_id,
            'name' => $this->name,
            'internal_code' => $this->internal_code,
            'sku' => $this->sku,
            'description' => $this->description,
            'type' => $this->type,
            'commandId' => $this->commandId,
            'brandId' => $this->brandId,
            'houseId' => $this->houseId,
            'inventoriable' => 1,
            'purchasing_unit' => $this->purchase_unit,
            'consumption_unit' => $this->consumption_unit,
            'status' => 1,
        ];

        if ($this->item_id) {
            $item = Items::findOrFail($this->item_id);
            $item->update($itemData);
            session()->flash('message', 'Item actualizado correctamente.');
        } else {
            Items::create($itemData);
            session()->flash('message', 'Item creado correctamente.');
        }

        // Mantener la paginación y filtros, limpiar solo el formulario
        $this->resetValidation();
        $this->reset([
            'item_id',
            'category_id',
            'name',
            'internal_code',
            'sku',
            'description',
            'type',
            'brandId',
            'houseId',
            'commandId',
            'purchase_unit',
            'consumption_unit'
        ]);
        $this->showModal = false;
    }

    public function confirmItemDeletion($id)
    {
        $this->confirmingItemDeletion = true;
        $this->itemIdToDelete = $id;
    }

    public function deleteItem()
    {
        $this->ensureTenantConnection();

        Items::find($this->itemIdToDelete)->delete();
        $this->confirmingItemDeletion = false;
        $this->reset(['itemIdToDelete']);
        $this->dispatchBrowserEvent('notify', ['message' => 'Item eliminado correctamente']);
    }

    public function cancel()
    {
        $this->resetValidation();
        $this->reset([
            'item_id',
            'category_id',
            'name',
            'internal_code',
            'sku',
            'description',
            'type',
            'brandId',
            'houseId',
            'commandId',
            'purchase_unit',
            'consumption_unit'
        ]);
        $this->showModal = false;
        $this->confirmingItemDeletion = false;
    }

    public function onCommandSelected($value)
    {
        $this->commandId = $value;
    }

    public function onBrandSelected($value)
    {
        $this->brandId = $value;
    }

    public function onHouseSelected($value)
    {
        $this->houseId = $value;
    }

    public function onPurchaseUnitSelected($value)
    {
        $this->purchase_unit = $value;
    }

    public function onConsumptionUnitSelected($value)
    {
        $this->consumption_unit = $value;
    }
}
