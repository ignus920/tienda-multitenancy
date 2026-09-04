@php
$statusColors = [
    'sin_programar' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
    'programada' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    'pendiente' => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
    'disponible' => 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300',
    'en_proceso' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
    'pausada' => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300',
    'bloqueada' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
    'terminada' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
    'vencida' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
    'reprogramada' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/40 dark:text-orange-300',
    'cancelada' => 'bg-gray-100 text-gray-400 dark:bg-gray-700 dark:text-gray-500 line-through',
];
@endphp
<span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold {{ $statusColors[$task->status] ?? $statusColors['sin_programar'] }}">
    {{ $task->status_label }}
</span>
