<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Category;
use App\Models\Tenant\Items\Command;
use App\Models\Tenant\Inventory\Item;

class Items extends Component
{
    public $categories = [];
    public $commands = [];

    public $selectedCategory;
    public $selectedCommand;

    public $name;
    public $internal_code;
    public $sku;
    public $description;
    public $type;
    public $brand;
    public $house;
    public $purchasing_unit;
    public $consumption_unit;
    public $inventoriable = 1;

    public function mount()
    {
        // Cargar todas las categorías desde la base de datos
        $this->categories = Category::where('status', 1)->get();
        $this->commands = Command::where('status', 1)->get();

    }

    public function save()
    {
        $this->validate([
            'selectedCategory' => 'required|exists:company_1_b2c3a9df_44bf_4f62_8ff7_7fbfdbc5464e.inv_categories,id',
            'selectedCommand' => 'required|exists:company_1_b2c3a9df_44bf_4f62_8ff7_7fbfdbc5464e.inv_command,id',
            'name' => 'required|string|max:255',
        ]);

        Items::create([
            'categoryId' => $this->selectedCategory,
            'commandId' => $this->selectedCommand,
            'name' => $this->name,
            'internal_code' => $this->internal_code,
            'sku' => $this->sku,
            'description' => $this->description,
            'type' => $this->type,
            'brandId' => $this->brand,
            'houseId' => $this->house,
            'purchasing_unit' => $this->purchasing_unit,
            'consumption_unit' => $this->consumption_unit,
            'inventoriable' => $this->inventoriable,
            'status' => 1,
        ]);

        session()->flash('message', 'Item creado correctamente ✅');
        $this->resetInput();
    }

    public function resetInput()
    {
        $this->selectedCategory = null;
        $this->selectedCommand = null;
        $this->name = '';
        $this->internal_code = '';
        $this->sku = '';
        $this->description = '';
        $this->type = '';
        $this->brand = '';
        $this->house = '';
        $this->purchasing_unit = '';
        $this->consumption_unit = '';
    }

    public function render()
    {
        return view('items.items',[
            'categories' => $this->categories
        ]);
    }
}
