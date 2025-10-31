@props([
    'wireModel' => null,
    'name' => 'fiscalResponsabilityId',
    'id' => null,
    'required' => true,
    'placeholder' => 'Seleccionar responsabilidad fiscal',
    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
    'label' => 'Responsabilidad Fiscal',
    'showLabel' => true,
    'error' => null
])

@php
    $computedId = $id ?? $name;
    $responsibilities = \App\Models\Central\CnfFiscalResponsability::orderBy('description')
        ->get(['id', 'description']);
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
        @foreach($responsibilities as $responsibility)
            <option value="{{ $responsibility->id }}">{{ $responsibility->description }}</option>
        @endforeach
    </select>

    @if($error)
        <span class="text-red-500 text-sm">{{ $error }}</span>
    @endif
</div>