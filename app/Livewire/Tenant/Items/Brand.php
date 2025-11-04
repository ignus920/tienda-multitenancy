<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Brand as BrandModel;

class Brand extends Component
{
    public $brandId = '';
    public $name = 'brandId';
    public $placeholder = 'Seleccione una marca';
    public $label = 'Marca';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($brandId = '', $name = 'brandId', $placeholder = 'Seleccione una marca', $label = 'Marca', $required = true, $showLabel = true, $class = null)
    {
        $this->brandId = $brandId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedBrandId(){
        $this->dispatch('brand-changed', $this->brandId);
    }

    public function getBrandsProperty()
    {
        // Cargar todas las marcas desde la base de datos
        return BrandModel::where('status', 1)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.tenant.items.brand',[
            'brands' => $this->brands,
            'showLabel' => $this->showLabel
        ]);
    }
}
