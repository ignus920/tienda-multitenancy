<div>
    @if($showLabel)
        <label for="fiscal_responsibility_{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        wire:model.live="fiscalResponsibilityId"
        name="{{ $name }}"
        id="fiscal_responsibility_{{ $name }}"
        @if($required) required @endif
        class="{{ $class }}"
        wire:loading.attr="disabled">
        <option value="">{{ $placeholder }}</option>
        @foreach($fiscalResponsibilities as $fiscalResponsibility)
            <option value="{{ $fiscalResponsibility->id }}">{{ $fiscalResponsibility->description }}</option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
