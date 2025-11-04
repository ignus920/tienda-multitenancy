<?php

namespace App\Livewire\Selects;

use Livewire\Component;
use App\Models\Central\CnfCity;
use Livewire\Attributes\On;

class CitySelect extends Component
{
    public $cityId = '';
    public $countryId = 48; // Colombia por defecto
    public $name = 'cityId';
    public $placeholder = 'Seleccionar ciudad';
    public $label = 'Ciudad';
    public $required = true;
    public $showLabel = true;
    public $class = 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500';
    public function mount($cityId = '', $countryId = 48, $name = 'cityId', $placeholder = 'Seleccionar ciudad', $label = 'Ciudad', $required = true, $showLabel = true, $class = null)
    {
        $this->cityId = $cityId;
        $this->countryId = $countryId;
        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->label = $label;
        $this->required = $required;
        $this->showLabel = $showLabel;
        if ($class) {
            $this->class = $class;
        }
    }

    #[On('country-changed')]
    public function updateCountry($countryId)
    {
        $this->countryId = $countryId;
        $this->cityId = ''; // Reset city when country changes
    }

    public function updatedCityId()
    {
        $this->dispatch('city-changed', $this->cityId);
    }

    public function getCitiesProperty()
    {
        return CnfCity::where('country_id', $this->countryId)
            ->orderBy('name')
            ->get(['id', 'name', 'state_id']);
    }

    public function render()
    {
        return view('livewire.selects.city-select', [
            'cities' => $this->cities
        ]);
    }
}
