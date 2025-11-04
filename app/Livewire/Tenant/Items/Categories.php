<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\Category;

class Categories extends Component
{
    public $categoryId = '';
    public $name = 'categoryId';
    public $placeholder = 'Seleccione una categoría';
    public $label = 'Categoría';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($categoryId = '', $name = 'categoryId', $placeholder = 'Seleccione una categoría', $label = 'Categoría', $required = true, $showLabel = true, $class = null)
    {
        $this->categoryId = $categoryId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedCategoryId(){
        $this->dispatch('category-changed', $this->categoryId);
    }

    public function getCategoriesProperty()
    {
        // Cargar todas las categorías desde la base de datos
        return Category::where('status', 1)->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.tenant.items.categories',[
            'categories' => $this->categories,
            'showLabel' => $this->showLabel
        ]);
    }
}
