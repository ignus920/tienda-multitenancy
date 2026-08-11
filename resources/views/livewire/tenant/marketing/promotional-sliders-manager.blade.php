<div class="py-6">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-xl sm:rounded-lg p-6">
            <!-- Header de la sección -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between pb-6 border-b border-gray-200 dark:border-slate-700 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <span>📊 Gestión de Sliders Promocionales</span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Crea, edita y administra los banners promocionales que se muestran en el Portal de Clientes.</p>
                </div>
                <button wire:click="openModal"
                        style="background-color: #4f46e5 !important; color: #ffffff !important;"
                        class="mt-4 sm:mt-0 px-5 py-2.5 rounded-lg font-medium flex items-center shadow-lg transition-all transform hover:scale-105 active:scale-95">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="stroke: #ffffff !important;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Nuevo Slider
                </button>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
                <div class="w-full md:w-1/3 relative">
                    <input type="text" wire:model.live="search" placeholder="Buscar por título..." 
                           class="w-full pl-10 pr-4 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                    <div class="absolute left-3 top-2.5 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
            </div>

            <!-- Tabla de Sliders -->
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-slate-700">
                    <thead class="bg-gray-50 dark:bg-slate-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Imagen</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Botón de Acción / URL</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Orden</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Estado</th>
                            <th class="px-6 py-3 text-center text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-slate-700 bg-white dark:bg-slate-900">
                        @forelse($sliders as $slider)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="w-20 h-10 rounded overflow-hidden bg-gray-100 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 flex items-center justify-center">
                                        <img src="{{ $slider->image_path }}" alt="Preview" class="object-cover w-full h-full">
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                                    {{ $slider->title }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                    @if($slider->action_button_text)
                                        <span class="px-2 py-0.5 rounded bg-indigo-100 text-indigo-800 dark:bg-indigo-900/30 dark:text-indigo-300 text-xs font-semibold">{{ $slider->action_button_text }}</span>
                                        <div class="text-xs text-gray-400 mt-1 truncate max-w-[200px]" title="{{ $slider->action_url }}">{{ $slider->action_url }}</div>
                                    @else
                                        <span class="text-xs text-gray-400">Sin acción</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-gray-700 dark:text-slate-300">
                                    {{ $slider->order }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <div class="flex justify-center items-center">
                                        <button wire:click="toggleStatus({{ $slider->id }})" 
                                                type="button"
                                                class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $slider->status ? 'bg-indigo-600' : 'bg-gray-300 dark:bg-slate-700' }}">
                                            <span class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out {{ $slider->status ? 'translate-x-5' : 'translate-x-0' }}"></span>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                    <button wire:click="openModal({{ $slider->id }})" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300 font-medium inline-flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        Editar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-sm text-gray-500 dark:text-slate-400">
                                    No se encontraron sliders creados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $sliders->links() }}
            </div>
        </div>
    </div>

    <!-- Modal de Formulario (Crear / Editar) con Preview -->
    @if($isOpen)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 flex items-center justify-center p-4 z-50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-2xl w-full max-w-5xl overflow-hidden transform transition-all">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                    {{ $sliderId ? 'Editar Slider' : 'Nuevo Slider' }}
                </h3>
                <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-slate-200 p-1 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form wire:submit.prevent="save">
                <div class="flex flex-col lg:flex-row">
                    <!-- Columna Izquierda: Formulario -->
                    <div class="lg:w-1/2 p-6 space-y-4 border-r border-gray-200 dark:border-slate-700 max-h-[70vh] overflow-y-auto">
                        <!-- Título -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Título del Slider *</label>
                            <input type="text" wire:model.live="title" 
                                   class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                            @error('title') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <!-- Subtítulo -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subtítulo</label>
                            <input type="text" wire:model.live="subtitle" placeholder="Descripción corta del slide"
                                   class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                        </div>

                        <!-- Badge y Color del Overlay -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Texto del Badge</label>
                                <input type="text" wire:model.live="badge_text" placeholder="Ej: 🔥 Oferta"
                                       class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                            </div>
                            <div>
                                <div class="flex justify-between items-center mb-1">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color del Overlay</label>
                                    <button type="button" wire:click="$set('overlay_color', 'transparent')" class="text-[10px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                        Hacer transparente
                                    </button>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="color" wire:model.live="overlay_color" 
                                           class="w-10 h-10 rounded-lg border border-gray-300 dark:border-slate-700 cursor-pointer p-0.5">
                                    <input type="text" wire:model.live="overlay_color" placeholder="transparent o #hex"
                                           class="flex-1 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                                </div>
                            </div>
                        </div>

                        <!-- Posición del Texto -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Posición del Texto</label>
                            <select wire:model.live="text_position" 
                                    class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                                <option value="left">⬅️ Izquierda</option>
                                <option value="center">⬆️ Centro</option>
                                <option value="right">➡️ Derecha</option>
                            </select>
                        </div>

                        <!-- Colores personalizados (Texto Slider, Fondo Botón, Texto Botón) -->
                        <div class="border-t border-gray-150 dark:border-slate-700 pt-4 mt-4 space-y-4">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wider block">🎨 Colores de Textos y Botón</span>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <!-- Color Texto Slider -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Color de Textos</label>
                                        <button type="button" wire:click="$set('text_color', null)" class="text-[9px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            Por defecto
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="text_color" 
                                               class="w-10 h-10 rounded-lg border border-gray-300 dark:border-slate-700 cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="text_color" placeholder="#ffffff"
                                               class="flex-1 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                                    </div>
                                </div>

                                <!-- Color Fondo Botón -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fondo del Botón</label>
                                        <button type="button" wire:click="$set('button_color', null)" class="text-[9px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            Por defecto
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="button_color" 
                                               class="w-10 h-10 rounded-lg border border-gray-300 dark:border-slate-700 cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="button_color" placeholder="transparent o #hex"
                                               class="flex-1 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <!-- Color Texto Botón -->
                                <div>
                                    <div class="flex justify-between items-center mb-1">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Texto del Botón</label>
                                        <button type="button" wire:click="$set('button_text_color', null)" class="text-[9px] text-indigo-650 dark:text-indigo-400 hover:underline">
                                            Por defecto
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="color" wire:model.live="button_text_color" 
                                               class="w-10 h-10 rounded-lg border border-gray-300 dark:border-slate-700 cursor-pointer p-0.5">
                                        <input type="text" wire:model.live="button_text_color" placeholder="#000000"
                                               class="flex-1 px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 outline-none font-mono">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Imagen -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Imagen * (Recomendado 1200x400)</label>
                            @if($image)
                                <div class="w-full h-24 rounded bg-gray-100 dark:bg-slate-900 overflow-hidden mb-2 border border-gray-200 dark:border-slate-700">
                                    <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                                </div>
                            @elseif($existingImage)
                                <div class="w-full h-24 rounded bg-gray-100 dark:bg-slate-900 overflow-hidden mb-2 border border-gray-200 dark:border-slate-700">
                                    <img src="{{ $existingImage }}" class="w-full h-full object-cover">
                                </div>
                            @endif
                            <input type="file" wire:model="image" 
                                   wire:key="image-upload-{{ $sliderId ?? 'new' }}"
                                   class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 dark:file:bg-slate-900 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-slate-700 transition-all">
                            @error('image') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <!-- Texto del Botón -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Texto del Botón</label>
                                <input type="text" wire:model.live="action_button_text" placeholder="Ej. Comprar ahora"
                                       class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                            </div>
                            <!-- URL Redirección -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Enlace (URL)</label>
                                <input type="text" wire:model="action_url" placeholder="http://..."
                                       class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                                @error('action_url') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        <!-- Orden -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Orden *</label>
                            <input type="number" wire:model="order" min="0"
                                   class="w-full px-3 py-2 text-sm text-gray-700 dark:text-gray-200 bg-white dark:bg-slate-900 border border-gray-300 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none transition-all">
                            @error('order') <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- Columna Derecha: Previsualización en Vivo -->
                    <div class="lg:w-1/2 p-6 bg-gray-50 dark:bg-slate-900/50 flex flex-col">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <span class="text-sm font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Previsualización</span>
                        </div>

                        <!-- Preview del Slider -->
                        <div class="relative w-full rounded-xl overflow-hidden shadow-lg flex-1 min-h-[220px]"
                             style="background-color: {{ $overlay_color && $overlay_color !== 'transparent' ? $overlay_color : '#1e1b4b' }};">
                            
                            <!-- Imagen de fondo -->
                            @if($image)
                                <img src="{{ $image->temporaryUrl() }}" class="absolute inset-0 w-full h-full object-cover">
                            @elseif($existingImage)
                                <img src="{{ $existingImage }}" class="absolute inset-0 w-full h-full object-cover">
                            @else
                                <div class="absolute inset-0 bg-gradient-to-br from-indigo-900 to-slate-900 flex items-center justify-center">
                                    <svg class="w-16 h-16 text-white/20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            <!-- Overlay con color dinámico condicionado -->
                            @php
                                $hex = $overlay_color ?: '#1e1b4b';
                                $showOverlay = ($hex !== 'transparent');
                                if ($showOverlay) {
                                    $r = hexdec(substr($hex, 1, 2) ?: '1e');
                                    $g = hexdec(substr($hex, 3, 2) ?: '1b');
                                    $b = hexdec(substr($hex, 5, 2) ?: '4b');
                                }
                            @endphp
                            @if($showOverlay)
                                <div class="absolute inset-0" style="background: linear-gradient(135deg, rgba({{ $r }},{{ $g }},{{ $b }},0.88) 0%, rgba({{ $r }},{{ $g }},{{ $b }},0.50) 45%, rgba(0,0,0,0.10) 100%);"></div>
                            @endif

                            <!-- Contenido del preview -->
                            <div class="relative z-10 flex flex-col h-full p-6 sm:p-8
                                {{ $text_position === 'center' ? 'items-center text-center' : ($text_position === 'right' ? 'items-end text-right' : 'items-start text-left') }}
                                justify-center">
                                
                                <!-- Badge -->
                                @if($badge_text)
                                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-widest mb-3"
                                         style="background: rgba(255,255,255,0.15); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.20); color: #fff;">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                        {{ $badge_text }}
                                    </div>
                                @endif

                                <!-- Título -->
                                <h3 class="text-xl sm:text-2xl font-extrabold leading-tight mb-2"
                                    style="text-shadow: 0 2px 15px rgba(0,0,0,0.3); {{ $text_color ? 'color: ' . $text_color . ' !important;' : 'color: #ffffff;' }}">
                                    {{ $title ?: 'Título del Slider' }}
                                </h3>

                                <!-- Subtítulo -->
                                @if($subtitle)
                                    <p class="text-sm mb-4 max-w-md font-medium" 
                                       style="text-shadow: 0 1px 8px rgba(0,0,0,0.2); {{ $text_color ? 'color: ' . $text_color . ' !important; opacity: 0.85;' : 'color: rgba(255,255,255,0.8);' }}">
                                        {{ $subtitle }}
                                    </p>
                                @endif

                                <!-- Botón CTA -->
                                @if($action_button_text)
                                    @php
                                        $btnStyle = "";
                                        if ($button_color) {
                                            $btnStyle .= "background-color: " . $button_color . " !important; ";
                                        } else {
                                            $btnStyle .= "background: rgba(255,255,255,0.18); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.25); ";
                                        }
                                        if ($button_text_color) {
                                            $btnStyle .= "color: " . $button_text_color . " !important; ";
                                        } else {
                                            $btnStyle .= "color: #fff !important; ";
                                        }
                                    @endphp
                                    <div class="inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold rounded-xl"
                                         style="{{ $btnStyle }}">
                                        {{ $action_button_text }}
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <p class="text-[10px] text-gray-400 dark:text-slate-500 mt-2 text-center italic">
                            * La vista previa es aproximada. El resultado final puede variar según el tamaño de pantalla.
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div class="px-6 py-4 bg-gray-50 dark:bg-slate-700/30 border-t border-gray-200 dark:border-slate-700 flex justify-end space-x-3">
                    <button type="button" wire:click="closeModal"
                            class="px-4 py-2 border border-gray-300 dark:border-slate-600 rounded-lg text-gray-700 dark:text-slate-300 bg-white dark:bg-slate-700 hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors font-medium">
                        Cancelar
                    </button>
                    <button type="submit"
                            wire:loading.attr="disabled"
                            style="background-color: #4f46e5 !important; color: #ffffff !important;"
                            class="px-4 py-2 rounded-lg font-medium flex items-center shadow-lg transition-all transform hover:scale-105 active:scale-95 disabled:opacity-50">
                        <span wire:loading.remove>Guardar</span>
                        <span wire:loading>Subiendo imagen...</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
