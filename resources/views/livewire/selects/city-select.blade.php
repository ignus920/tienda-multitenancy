<div>
    @if($showLabel)
        <label for="city_{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <select
            wire:model.live="cityId"
            name="{{ $name }}"
            id="city_{{ $name }}"
            @if($required) required @endif
            class="{{ $class }}"
            wire:loading.attr="disabled">
            <option value="">{{ $placeholder }}</option>
            @foreach($cities as $city)
                <option value="{{ $city->id }}">{{ $city->name }}</option>
            @endforeach
        </select>

        <!-- Loading indicator solo cuando cambia el país -->
        <div wire:loading.flex wire:target="countryId"
             class="absolute inset-y-0 right-0 pr-8 flex items-center pointer-events-none">
            <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>
    </div>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
