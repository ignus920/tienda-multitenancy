<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Central\CnfDistrict;
use Illuminate\Support\Facades\Log;
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
    public $city_id;

    public $showDistrictForm = false;
    public $newDistrictName = '';

    protected $listeners = ['refreshDistricts' => '$refresh'];

    public function mount($districtId = '', $name = 'districtId', $placeholder = 'Seleccionar barrio', $label = 'Barrio', $showLabel = true, $class = null, $index = null, $city_id)
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
        if (is_array($city_id)) {
            $this->city_id = !empty($city_id) ? (int)$city_id[0] : null;
        } else {
            $this->city_id = $city_id ? (int)$city_id : null;
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

    #[On('city-changed')]
    public function updateCity($cityId)
    {
        \Illuminate\Support\Facades\Log::info('DistrictSelect: city-changed received', [
            'cityId' => $cityId,
        ]);

        // Actualiza el city_id
        $this->city_id = $cityId ? (int)$cityId : null;

        // Limpia la selección de district
        $this->districtId = null;
        $this->search = '';

        // Notifica al padre
        $this->updatedDistrictId();
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


    #[Computed]
    public function selectedDistrictName()
    {
        if (!$this->districtId) return null;
        return CnfDistrict::find($this->districtId)?->district;
    }

    public function toggleDistrictForm()
    {
        $this->showDistrictForm = !$this->showDistrictForm;
        if ($this->showDistrictForm) {
            $this->newDistrictName = '';
            $this->resetErrorBag();
        }
    }

    public function getDistrictsProperty()
    {
        if (!$this->city_id) {
            return collect(); // Retorna colección vacía si no hay city_id
        }

        $query = CnfDistrict::where('status', 1);

        if (!empty($this->search)) {
            $query->where('city_id', (int)$this->city_id)->where('district', 'like', '%' . $this->search . '%');
        } else {
            $query->where('city_id', (int)$this->city_id);
        }

        return $query->select('id', 'district', 'city_id')
            ->orderBy('district')
            ->limit(50)
            ->get();
    }

    public function createDistrict()
    {
        try {
            $district = CnfDistrict::create([
                'city_id' => $this->city_id,
                'district' => $this->newDistrictName,
                'status' => 1,
            ]);

            // Resetear el formulario
            $this->showDistrictForm = false;
            $this->newDistrictName = '';

            // Emitir eventos
            $this->dispatch('district-created', districtId: $district->id);
            $this->dispatch('refreshDistricts'); // Refrescar este componente

            // Opcional: Seleccionar automáticamente
            $this->districtId = $district->id;
            $this->updatedDistrictId();
        } catch (\Exception $e) {
            $this->addError('newDistrictName', 'Error al crear el barrio: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.selects.district-select', [
            'districts' => $this->districts
        ]);
    }
}
