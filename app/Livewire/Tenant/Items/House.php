<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\House as HouseModel;

class House extends Component
{
    public $houseId = '';
    public $name = 'houseId';
    public $placeholder = 'Seleccione una casa';
    public $label = 'Casa';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($houseId = '', $name = 'houseId', $placeholder = 'Seleccione una casa', $label = 'Casa', $required = true, $showLabel = true, $class = null)
    {
        $this->houseId = $houseId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedHouseId(){
        $this->dispatch('house-changed', $this->houseId);
    }

    public function getHousesProperty()
    {
        // Cargar todas las casas desde la base de datos
        return HouseModel::where('status', 1)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.tenant.items.house', [
            'houses' => $this->houses,
            'showLabel' => $this->showLabel
        ]);
    }
}
