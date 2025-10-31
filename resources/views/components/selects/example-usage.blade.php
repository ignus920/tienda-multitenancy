{{--
    EJEMPLO: Cómo reemplazar los selects actuales con componentes reutilizables
    Este archivo muestra cómo convertir tu formulario actual
--}}

{{-- ============================================ --}}
{{-- ANTES (código actual en simple-setup.blade.php) --}}
{{-- ============================================ --}}

{{--
<div>
    <label for="typeIdentificationId" class="block text-sm font-medium text-gray-700">Tipo Identificación *</label>
    <select wire:model.live="typeIdentificationId" id="typeIdentificationId"
        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
        <option value="0">Seleccionar tipo</option>
        @foreach($typeIdentifications as $type)
            <option value="{{ $type->id }}">{{ $type->acronym }} - {{ $type->name }}</option>
        @endforeach
    </select>
    @error('typeIdentificationId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
</div>
--}}

{{-- ============================================ --}}
{{-- DESPUÉS (usando componente reutilizable) --}}
{{-- ============================================ --}}

<x-selects.type-identification
    wire:model.live="typeIdentificationId"
    :error="$errors->first('typeIdentificationId')"
/>

{{-- ============================================ --}}
{{-- EJEMPLO COMPLETO: Formulario convertido --}}
{{-- ============================================ --}}

<div class="space-y-6">
    {{-- Tipo de Identificación --}}
    <x-selects.type-identification
        wire:model.live="typeIdentificationId"
        :error="$errors->first('typeIdentificationId')"
    />

    {{-- Campos condicionales según tipo de persona --}}
    @if($typePerson == 'Natural')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label for="firstName" class="block text-sm font-medium text-gray-700">Nombre *</label>
                <input wire:model="firstName" type="text" id="firstName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('firstName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="lastName" class="block text-sm font-medium text-gray-700">Apellido *</label>
                <input wire:model="lastName" type="text" id="lastName" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                @error('lastName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>
    @endif

    {{-- Selects fiscales usando componentes --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-selects.regime
            wire:model="regimeId"
            :error="$errors->first('regimeId')"
        />

        <x-selects.fiscal-responsibility
            wire:model="fiscalResponsabilityId"
            :error="$errors->first('fiscalResponsabilityId')"
        />
    </div>

    {{-- Ciudad usando componente (filtrada por Colombia) --}}
    <x-selects.city
        wire:model="cityId"
        :country-id="48"
        :error="$errors->first('cityId')"
    />
</div>

{{-- ============================================ --}}
{{-- BENEFICIOS DE USAR COMPONENTES --}}
{{-- ============================================ --}}

{{--
✅ VENTAJAS:

1. **Menos código**: De 8 líneas a 3 líneas por select
2. **Reutilizable**: Úsalo en cualquier formulario
3. **Mantenible**: Cambios en un solo lugar
4. **Consistente**: Mismo estilo siempre
5. **Sin duplicación**: No repetir código
6. **Auto-actualizable**: Datos siempre frescos

📝 CÓMO MIGRAR TU FORMULARIO ACTUAL:

1. Reemplaza cada select manualmente
2. Elimina las variables del componente ($typeIdentifications, $regimes, etc.)
3. Elimina el método loadSelectData()
4. Listo! ✨

🎯 RESULTADO:
- Código más limpio
- Fácil mantenimiento
- Componentes reutilizables para todo el equipo
--}}