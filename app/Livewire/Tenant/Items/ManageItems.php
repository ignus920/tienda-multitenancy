<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Tenant\Items\Items;
use App\Models\Tenant\Items\Category;

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

    // tipos disponibles (puedes externalizarlo si lo prefieres)
    public $types = [
        'COMBO' => 'Combo',
        'COMPRA_NACIONAL' => 'Compra nacional',
        'IMPORTADO' => 'Importado',
        'PRODUCIDO' => 'Producido',
    ];

    protected function rules()
    {
        $table = (new Items)->getTable();

        return [
            'category_id' => 'required',
            'name' => 'required|min:3',
            'sku' => [
                'nullable',
                Rule::unique($table, 'sku')->ignore($this->item_id),
            ],
            'type' => ['required', Rule::in(array_keys($this->types))],
            'internal_code' => 'nullable|string',
            'brandId' => 'nullable|string',
            'houseId' => 'nullable|string',
            'purchase_unit' => 'nullable|string',
            'consumption_unit' => 'nullable|string',
        ];
    }

    public function render()
    {
    $items = Items::query()
            ->when($this->search, function($query) {
                $query->where(function($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                      ->orWhere('sku', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.tenant.items.manage-items', [
            'items' => $items,
            'categories' => Category::where('status', 1)->get(),
            'types' => $this->types
        ]);
    }

    public function sortBy($field)
    {
        $this->sortDirection = $this->sortField === $field 
            ? $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc'
            : 'asc';

        $this->sortField = $field;
    }

    public function create()
    {
        $this->resetExcept(['categories', 'types']); // No reseteamos las listas de opciones
        $this->showModal = true;
        
        // Emitir eventos para inicializar los componentes hijos
        $this->emit('initializeCommand');
        $this->emit('initializeBrand');
        $this->emit('initializeHouse');
        $this->emit('initializePurchaseUnit');
        $this->emit('initializeConsumptionUnit');
    }

    public function edit(Items $item)
    {
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
        $this->validate();

        $itemData = [
            'categoryId' => $this->category_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'internal_code' => $this->internal_code,
            'description' => $this->description,
            'type' => $this->type,
            'brandId' => $this->brandId,
            'houseId' => $this->houseId,
            'inventoriable' => 1,
            'purchasing_unit' => $this->purchase_unit,
            'consumption_unit' => $this->consumption_unit,
            'status' => 1,
        ];

        if ($this->item_id) {
            $item = Items::find($this->item_id);
            $item->update($itemData);
            $this->dispatchBrowserEvent('notify', ['message' => 'Item actualizado correctamente']);
        } else {
            Items::create($itemData);
            $this->dispatchBrowserEvent('notify', ['message' => 'Item creado correctamente']);
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
