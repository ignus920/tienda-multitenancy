<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8 bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl">
        
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Solicitud de Garantía
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Llene el siguiente formulario para radicar su solicitud por novedad en productos.
            </p>
        </div>

        @if($isSubmitted)
            <div class="rounded-md bg-green-50 dark:bg-green-900/30 p-4 border border-green-200 dark:border-green-800 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 dark:bg-green-800 mb-4">
                    <svg class="h-6 w-6 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-sm font-medium text-green-800 dark:text-green-300">¡Solicitud recibida exitosamente!</h3>
                <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                    <p>Su número de radicado es: <strong class="text-lg block mt-1">{{ $requestFolio }}</strong></p>
                    <p class="mt-3">Un asesor comercial se pondrá en contacto pronto para gestionar su caso.</p>
                </div>
            </div>
        @else
            <form wire:submit.prevent="submit" class="mt-8 space-y-6">
                
                <div class="space-y-4">
                    <!-- Nombre empresa -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre de la Empresa o Cliente <span class="text-red-500">*</span></label>
                        <input wire:model="company_name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('company_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Número de Factura -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Factura <span class="text-red-500">*</span></label>
                        <input wire:model="reference_number" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('reference_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Asesor -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asesor Comercial (Opcional)</label>
                        <input wire:model="advisor_name" type="text" placeholder="¿Quién lo atiende usualmente?" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('advisor_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Producto y Cantidad -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Producto y Cantidad <span class="text-red-500">*</span></label>
                        <input wire:model="product_details" type="text" required placeholder="Ej: 5 x Luminarias LED 50W" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('product_details') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Descripción -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción detallada de la solicitud <span class="text-red-500">*</span></label>
                        <textarea wire:model="description" required rows="4" placeholder="Describa la falla o motivo de la garantía..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border"></textarea>
                        @error('description') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <!-- Fotos / Videos -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Evidencia (Fotos o Videos)</label>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Máximo 10 MB por archivo. Formatos permitidos: JPG, PNG, MP4, MOV.</p>
                        
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md dark:border-gray-600 relative">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                    <label class="relative cursor-pointer bg-white dark:bg-gray-700 rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none px-2 py-1">
                                        <span>Seleccionar archivos</span>
                                        <input wire:model="media_files" type="file" multiple accept="image/*,video/mp4,video/quicktime" class="sr-only">
                                    </label>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    o arrastre y suelte aquí
                                </p>
                            </div>
                        </div>
                        
                        <!-- Progreso / Vista previa -->
                        <div wire:loading wire:target="media_files" class="text-xs text-indigo-600 mt-2">Cargando archivos...</div>
                        
                        @if($media_files)
                        <ul class="mt-3 space-y-1">
                            @foreach($media_files as $file)
                                <li class="text-xs text-gray-600 dark:text-gray-300 flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    {{ $file->getClientOriginalName() }}
                                </li>
                            @endforeach
                        </ul>
                        @endif

                        @error('media_files.*') <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <button type="submit" class="group relative w-full flex justify-center py-2.5 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors" wire:loading.attr="disabled">
                        <span wire:loading.remove wire:target="submit">Enviar Solicitud de Garantía</span>
                        <span wire:loading wire:target="submit">Enviando...</span>
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
