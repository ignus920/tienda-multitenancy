@props(['text'])

{{-- Ícono de ayuda con tooltip. Uso: <x-help-tip text="Explicación del campo" /> --}}
<span x-data="{ o: false }" class="relative inline-flex items-center align-middle ml-1 shrink-0">
    <button type="button" tabindex="-1"
        @mouseenter="o = true" @mouseleave="o = false" @click.prevent="o = !o"
        @click.away="o = false"
        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 focus:outline-none">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>
    <span x-show="o" x-cloak style="display:none" x-transition.opacity
        class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1.5 w-52 p-2 rounded-lg bg-gray-800 text-white text-[10px] font-normal normal-case leading-snug tracking-normal shadow-lg z-[70] pointer-events-none">
        {{ $text }}
    </span>
</span>
