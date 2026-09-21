<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-xl">
        
        <div>
            <h2 class="mt-2 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                Solicitud de Garantía
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                Siga los pasos para radicar su solicitud por novedad en productos.
            </p>
        </div>

        @if($isSubmitted)
            <div class="mt-8 rounded-md bg-green-50 dark:bg-green-900/30 p-6 border border-green-200 dark:border-green-800 text-center">
                <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 dark:bg-green-800 mb-4">
                    <svg class="h-8 w-8 text-green-600 dark:text-green-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-green-800 dark:text-green-300">¡Solicitud recibida exitosamente!</h3>
                <div class="mt-2 text-sm text-green-700 dark:text-green-400">
                    <p>Su número de radicado es: <strong class="text-2xl block mt-2">{{ $requestFolio }}</strong></p>
                    <p class="mt-4">Un asesor comercial verificará la información y se pondrá en contacto pronto para gestionar su caso.</p>
                </div>
            </div>
        @else
            <!-- Stepper UI -->
            <div class="mt-8 mb-8">
                <div class="flex items-center justify-center">
                    <div class="flex items-center w-full max-w-sm">
                        <!-- Step 1 -->
                        <div class="flex-1">
                            <div class="relative flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ $currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                    1
                                </div>
                                <div class="absolute top-10 text-xs font-medium {{ $currentStep >= 1 ? 'text-indigo-600' : 'text-gray-500' }}">Validación</div>
                            </div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $currentStep >= 2 ? 'border-indigo-600' : 'border-gray-200' }}"></div>
                        
                        <!-- Step 2 -->
                        <div class="flex-1">
                            <div class="relative flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ $currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700' }}">
                                    2
                                </div>
                                <div class="absolute top-10 text-xs font-medium {{ $currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500' }}">Productos</div>
                            </div>
                        </div>
                        <div class="flex-auto border-t-2 transition duration-500 ease-in-out {{ $currentStep >= 3 ? 'border-indigo-600' : 'border-gray-200' }}"></div>
                        
                        <!-- Step 3 -->
                        <div class="flex-1">
                            <div class="relative flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ $currentStep >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700' }}">
                                    3
                                </div>
                                <div class="absolute top-10 text-xs font-medium {{ $currentStep >= 3 ? 'text-indigo-600' : 'text-gray-500' }}">Detalles</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-12">
                <!-- PASO 1: Validación -->
                @if($currentStep === 1)
                <form wire:submit.prevent="validateStep1" class="space-y-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-4 mb-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-blue-700 dark:text-blue-300">
                                    Por favor ingrese el NIT de su empresa y el número de la factura para cargar los productos.
                                </p>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">NIT o Identificación de la Empresa <span class="text-red-500">*</span></label>
                        <input wire:model="nit" type="text" placeholder="Ej: 900123456" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('nit') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Número de Factura <span class="text-red-500">*</span></label>
                        <input wire:model="invoice_number" type="text" placeholder="Ej: FE-1234" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('invoice_number') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Siguiente Paso
                            <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </form>
                @endif

                <!-- PASO 2: Selección de Productos -->
                @if($currentStep === 2)
                <form wire:submit.prevent="validateStep2" class="space-y-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase">Datos Encontrados</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><span class="font-medium">Cliente:</span> {{ $company_name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">Factura:</span> {{ $invoice_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Seleccione los productos que presentan fallas:</label>
                        @error('general_products') <div class="text-sm text-red-500 mb-3 bg-red-50 p-2 rounded">{{ $message }}</div> @enderror
                        
                        <div class="space-y-3 max-h-80 overflow-y-auto pr-2">
                            @foreach($foundProducts as $prod)
                                @php $id = $prod['id']; @endphp
                                <div class="flex items-start p-3 border {{ isset($selectedProducts[$id]['selected']) && $selectedProducts[$id]['selected'] ? 'border-indigo-500 bg-indigo-50 dark:bg-indigo-900/20' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800' }} rounded-lg transition-colors cursor-pointer" 
                                     wire:click="$toggle('selectedProducts.{{ $id }}.selected')">
                                    <div class="flex items-center h-5 mt-1">
                                        <input type="checkbox" wire:model="selectedProducts.{{ $id }}.selected" class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded cursor-pointer" id="prod-{{ $id }}">
                                    </div>
                                    <div class="ml-3 flex-1">
                                        <label for="prod-{{ $id }}" class="text-sm font-medium text-gray-700 dark:text-gray-200 cursor-pointer w-full block">
                                            {{ $prod['name'] }} <span class="text-gray-400 font-normal">({{ $prod['reference'] }})</span>
                                        </label>
                                        
                                        @if(isset($selectedProducts[$id]['selected']) && $selectedProducts[$id]['selected'])
                                            <div class="mt-2 flex items-center gap-2" wire:click.stop>
                                                <label class="text-xs text-gray-500 dark:text-gray-400">Cant. a reclamar:</label>
                                                <input type="number" wire:model="selectedProducts.{{ $id }}.qty" min="1" max="{{ $prod['max_qty'] }}" class="block w-20 text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-2 py-1">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">de {{ $prod['max_qty'] }} comprados</span>
                                            </div>
                                            @error("selectedProducts.{$id}.qty") <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 -ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Volver
                        </button>
                        <button type="submit" class="inline-flex items-center px-6 py-2.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Siguiente
                            <svg class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>
                </form>
                @endif

                <!-- PASO 3: Detalles y Fotos -->
                @if($currentStep === 3)
                <form wire:submit.prevent="submit" class="space-y-6">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Asesor Comercial (Opcional)</label>
                        <input wire:model="advisor_name" type="text" placeholder="¿Qué comercial lo atiende?" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                        @error('advisor_name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción detallada de la solicitud <span class="text-red-500">*</span></label>
                        <textarea wire:model="description" required rows="4" placeholder="Describa la falla o motivo de la garantía de los productos seleccionados..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border"></textarea>
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

                    <div class="pt-4 flex justify-between">
                        <button type="button" wire:click="previousStep" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 -ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Volver
                        </button>
                        <button type="submit" class="group relative inline-flex items-center justify-center py-2.5 px-6 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="submit">Enviar Solicitud de Garantía</span>
                            <span wire:loading wire:target="submit">Enviando...</span>
                        </button>
                    </div>
                </form>
                @endif

            </div>
        @endif
    </div>
</div>
