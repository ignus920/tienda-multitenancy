<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Central\CnfDistrict;
use Livewire\Attributes\On;
use Livewire\Attributes\Computed;

class DistrictSelect extends Component
{
    public $districtId = '';
    public $name = 'districtId';
    public $placeholder = 'Seleccionar barrio';
    public $label = 'Barrio';
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 text-left bg-white cursor-default sm:text-sm py-2 pl-3 pr-10 relative';
    public $index = null;
    public $search = '';

    public function mount($districtId = '', $name = 'districtId', $placeholder = 'Seleccionar barrio', $label = 'Barrio', $showLabel = true, $class = null, $index = null)
    {
        $this->districtId = $districtId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->showLabel = $showLabel;
        $this->index = $index;
        if ($class) {
            $this->class = $class;
        }
    }

    #[On('district-changed')]
    public function updateDistrict()
    {
        $this->districtId = '';
        $this->search = '';
        $this->dispatch('validate-district');
    }

    #[On('validate-district')]
    public function validateDistrict()
    {
        $this->validate([
            'districtId' => 'nullable',
        ]);
        // Notificar al padre que el hijo pasó la validación
        $this->dispatch('district-valid', index: $this->index, districtId: $this->districtId);
    }

    public function selectDistrict($id)
    {
        \Illuminate\Support\Facades\Log::info('DisctrictSelect: selectDistrict called', [
            'id' => $id,
            'index' => $this->index,
            'name' => $this->name
        ]);

        $this->districtId = $id;
        $this->search = '';

        if ($this->index !== null) {
            $this->dispatch('district-changed', districtId: $this->districtId, index: $this->index);
        } else {
            $this->dispatch('district-changed', $this->districtId);
        }
    }

    public function updatedDistrictId()
    {
        \Illuminate\Support\Facades\Log::info('DistrictSelect: updatedDistrictId hook triggered', [
            'districtId' => $this->districtId,
            'index' => $this->index,
            'name' => $this->name
        ]);

        if ($this->index !== null) {
            $this->dispatch('district-changed', districtId: $this->districtId, index: $this->index);
        } else {
            $this->dispatch('district-changed', $this->districtId);
        }

        \Illuminate\Support\Facades\Log::info('DistrictSelect: district-changed event dispatched', [
            'districtId' => $this->districtId,
            'index' => $this->index
        ]);
    }

    #[Computed]
    public function selectedDistrictName()
    {
        if (!$this->districtId) return null;
        return CnfDistrict::find($this->districtId)?->name;
    }

    public function getDistrictsProperty()
    {
        $query = CnfDistrict::where('status', 1);

        if (!empty($this->search)) {
            $query->where('district', 'like', '%' . $this->search . '%');
        }

        return $query->select('id', 'district', 'city_id')
            ->orderBy('district')
            ->limit(50)
            ->get();
    }

    public function render()
    {
        return view('livewire.selects.district-select', [
            'districts' => $this->districts
        ]);
    }
}
