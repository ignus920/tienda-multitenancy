<!-- Switch Premium de Modo Copia -->
<div class="flex items-center gap-3 bg-white dark:bg-gray-800 px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm transition-all hover:shadow-md">
    <div class="flex flex-col leading-none">
        <span class="text-[10px] uppercase font-bold tracking-wider {{ $isCopyMode ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-500' }}">
            {{ $isCopyMode ? 'Modo Copia' : 'Modo Cotización' }}
        </span>
        <span class="text-[8px] text-gray-500 dark:text-gray-400 hidden sm:block">
            {{ $isCopyMode ? 'Click para cotizar' : 'Click para copiar' }}
        </span>
    </div>
    
    <button 
        type="button"
        wire:click="toggleCopyMode"
        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $isCopyMode ? 'bg-emerald-500' : 'bg-red-500' }}">
        <span 
            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $isCopyMode ? 'translate-x-5' : 'translate-x-0' }}">
        </span>
    </button>
</div>
