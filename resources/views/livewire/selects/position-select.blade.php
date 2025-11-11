<div>
    @if($showLabel)
        <label for="position_{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <div class="relative">
        <select
            wire:model="positionId"
            name="{{ $name }}"
            id="position_{{ $name }}"
            @if($required) required @endif
            class="{{ $class }}">
            <option value="">{{ $placeholder }}</option>
            @foreach($positions as $position)
                <option value="{{ $position->id }}">{{ $position->name }}</option>
            @endforeach
        </select>
    </div>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
