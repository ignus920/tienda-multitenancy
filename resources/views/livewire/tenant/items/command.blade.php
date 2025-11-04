<div class="mb-4">
    @if($showLabel)
        <label for="command_{{ $name }}" class="block text-sm font-medium text-gray-700">
            {{ $label }}
        </label>
    @endif

    <select
        wire:model.live="commandId"
        name="{{ $name }}"
        id="command_{{ $name }}"
        @if($required) required @endif
        class="{{ $class }}"
        wire:loading.attr="disabled">
        <option value="">{{ $placeholder }}</option>
        @foreach($this->commands as $command)
            <option value="{{ $command->id }}">
                {{ $command->name }}
            </option>
        @endforeach
    </select>

    @error($name)
        <span class="text-red-500 text-sm">{{ $message }}</span>
    @enderror
</div>
