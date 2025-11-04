<?php

namespace App\Livewire\Tenant\Items;

use Livewire\Component;
use App\Models\Tenant\Items\UnitMeasurements as UnitMeasurementsModel;

class ConsumptionUnit extends Component
{
    public $consumptionUnitId = '';
    public $name = 'consumptionUnitId';
    public $placeholder = 'Seleccione una unidad de medida';
    public $label = 'Unidad de consumo';
    public $required = true;
    public $showLabel = false;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';

    public function mount($consumptionUnitId = '', $name = 'consumptionUnitId', $placeholder = 'Seleccione una unidad de medida', $label = 'Unidad de consumo', $required = true, $showLabel = true, $class = null)
    {
        $this->consumptionUnitId = $consumptionUnitId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    public function updatedUnitMeasurementId(){
        $this->dispatch('unit-measurement-changed', $this->unitMeasurementId);
    }

    public function getUnitMeasurementsProperty()
    {
        // Cargar todas las unidades de medida desde la base de datos
        return UnitMeasurementsModel::where('status', 1)->get(['id', 'description']);
    }

    public function render()
    {
        return view('livewire.tenant.items.consumption-unit', [
            'consumptionUnits' => $this->unitMeasurements,
            'showLabel' => $this->showLabel
        ]);
    }
}
