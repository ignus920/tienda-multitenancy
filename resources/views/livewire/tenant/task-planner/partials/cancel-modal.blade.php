@if($showCancelModal)
<div class="fixed inset-0 bg-gray-500/75 dark:bg-gray-900/80 backdrop-blur-xs flex items-center justify-center p-4 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-sm">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cancelar tarea</h3>
        </div>
        <div class="p-6 space-y-3">
            <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300 mb-1">Motivo (opcional)</label>
            <textarea wire:model="cancelReason" rows="3"
                class="block w-full border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-900 dark:text-white rounded-lg px-3 py-2 text-sm"></textarea>
        </div>
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            <button wire:click="$set('showCancelModal', false)" class="px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 rounded-lg hover:bg-gray-200">Volver</button>
            <button wire:click="confirmCancel" class="px-4 py-2 text-sm font-semibold text-white bg-red-600 hover:bg-red-700 rounded-lg">Cancelar tarea</button>
        </div>
    </div>
</div>
@endif
