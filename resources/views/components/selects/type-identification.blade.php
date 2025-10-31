@props([
    'wireModel' => null,
    'name' => 'typeIdentificationId',
    'id' => null,
    'required' => true,
    'placeholder' => 'Seleccionar tipo de identificación',
    'class' => 'mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500',
    'label' => 'Tipo de Identificación',
    'showLabel' => true,
    'error' => null
])

@php
    $computedId = $id ?? $name;
    $types = \App\Models\Central\CnfTypeIdentification::where('status', 1)
        ->orderBy('name')
        ->get(['id', 'name', 'acronym']);
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
        @foreach($types as $type)
            <option value="{{ $type->id }}">{{ $type->acronym }} - {{ $type->name }}</option>
        @endforeach
    </select>

    @if($error)
        <span class="text-red-500 text-sm">{{ $error }}</span>
    @endif
</div>