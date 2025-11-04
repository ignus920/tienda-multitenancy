<div>
    @if($showLabel)
        <label for="type_identification_{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        wire:model.live="typeIdentificationId"
        name="{{ $name }}"
        id="type_identification_{{ $name }}"
        @if($required) required @endif
        class="{{ $class }}"
        wire:loading.attr="disabled">
        <option value="">{{ $placeholder }}</option>
        @foreach($typeIdentifications as $typeIdentification)
            <option value="{{ $typeIdentification->id }}">
                {{ $typeIdentification->name }} ({{ $typeIdentification->acronym }})
            </option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
