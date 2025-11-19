<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Tenant\Customer\CnfPosition as TenantPosition;
use App\Models\Central\CnfPosition as CentralCnfPosition;

class PositionSelect extends Component
{
    public $positionId = '';
    public $name = 'positionId';
    public $placeholder = 'Seleccionar posición';
    public $label = 'Posición';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($positionId = '', $name = 'positionId', $placeholder = 'Seleccionar posición', $label = 'Posición', $required = true, $showLabel = true, $class = null)
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

    public function updatedPositionId($value)
    {
        // Emit event to parent component with the new position ID
        $this->dispatch('position-changed', positionId: $value);
        
        // Also dispatch a generic event that can be caught by parent
        $this->dispatch('positionUpdated', positionId: $value);
    }

    public function getPositionsProperty()
    {
        if (tenant()) {
            return TenantPosition::active()->orderBy('name')->get(['id', 'name']);

        }
        return CentralCnfPosition::active()->orderBy('name')->get(['id', 'name']);
    }

    public function render()
    {
        return view('livewire.selects.position-select', [
            'positions' => $this->positions
        ]);
    }
}
