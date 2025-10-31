@props([
    'wireModel' => null,
    'name' => 'cityId',
    'id' => null,
    'required' => true,
    'placeholder' => 'Seleccionar ciudad',
    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
    'label' => 'Ciudad',
    'showLabel' => true,
    'error' => null,
    'countryId' => 48, // Colombia por defecto
    'filterByCountry' => true
])

@php
    $computedId = $id ?? $name;
    $cities = collect();

    if ($filterByCountry && $countryId) {
        $cities = \App\Models\Central\CnfCity::where('country_id', $countryId)
            ->orderBy('name')
            ->get(['id', 'name', 'state_id']);
    } else {
        $cities = \App\Models\Central\CnfCity::orderBy('name')
            ->get(['id', 'name', 'state_id']);
    }
@endphp

<div>
    @if($showLabel)
        <label for="{{ $computedId }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        @if($wireModel) wire:model="{{ $wireModel }}" @endif
        name="{{ $name }}"
        id="{{ $computedId }}"
        @if($required) required @endif
        {{ $attributes->merge(['class' => $class]) }}>
        <option value="">{{ $placeholder }}</option>
        @foreach($cities as $city)
            <option value="{{ $city->id }}">{{ $city->name }}</option>
        @endforeach
    </select>

    @if($error)
        <span class="text-red-500 text-sm">{{ $error }}</span>
    @endif

    @if($filterByCountry)
        <p class="text-xs text-gray-500 mt-1">
            Mostrando ciudades {{ $countryId == 48 ? 'de Colombia' : 'del país seleccionado' }}
        </p>
    @endif
</div>