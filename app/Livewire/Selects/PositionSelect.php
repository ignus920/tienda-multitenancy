<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Tenant\CfgPosition;

class PositionSelect extends Component
{
    public $positionId = '';
    public $name = 'positionId';
    public $placeholder = 'Seleccionar posición';
    public $label = 'Posición';
    public $required = false;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($positionId = '', $name = 'positionId', $placeholder = 'Seleccionar posición', $label = 'Posición', $required = false, $showLabel = true, $class = null)
    {
        $this->positionId = $positionId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedPositionId()
    {
        $this->dispatch('position-changed', $this->positionId);
    }

    public function getPositionsProperty()
    {
        return CfgPosition::active()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.selects.position-select', [
            'positions' => $this->positions
        ]);
    }
}
