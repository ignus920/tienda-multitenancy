<div>
    @if($showLabel)
        <label for="regime_{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
            @if($required) <span class="text-red-500">*</span> @endif
        </label>
    @endif

    <select
        wire:model.live="regimeId"
        name="{{ $name }}"
        id="regime_{{ $name }}"
        @if($required) required @endif
        class="{{ $class }}"
        wire:loading.attr="disabled">
        <option value="">{{ $placeholder }}</option>
        @foreach($regimes as $regime)
            <option value="{{ $regime->id }}">{{ $regime->name }}</option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
