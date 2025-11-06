<div class="mb-4">
    @if($showLabel)
        <label for="brand_{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            {{ $label }}
        </label>
    @endif

    <select
        wire:model.live="brandId"
        name="{{ $name }}"
        id="brand_{{ $name }}"
        @if($required) required @endif
        class="{{ $class }}"
        wire:loading.attr="disabled">
        <option value="">{{ $placeholder }}</option>
        @foreach($this->brands as $brand)
            <option value="{{ $brand->id }}">
                {{ $brand->name }}
            </option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>