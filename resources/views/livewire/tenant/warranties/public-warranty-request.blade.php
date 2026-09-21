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
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ $currentStep >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                    1
                                </div>
                                <div class="mt-2 text-xs font-medium {{ $currentStep >= 1 ? 'text-indigo-600' : 'text-gray-500' }}">Validación</div>
                            </div>
                        </div>
                        <div class="flex-auto border-t-4 border-dashed transition duration-500 ease-in-out {{ $currentStep >= 2 ? 'border-indigo-600' : 'border-gray-200 dark:border-gray-700' }} mx-2"></div>
                        
                        <!-- Step 2 -->
                        <div class="flex-1">
                            <div class="flex flex-col items-center">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold shadow-sm {{ $currentStep >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500 dark:bg-gray-700' }}">
                                    2
                                </div>
                                <div class="mt-2 text-xs font-medium {{ $currentStep >= 2 ? 'text-indigo-600' : 'text-gray-500' }}">Productos & Detalles</div>
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

                <!-- PASO 2: Selección de Productos y Detalles -->
                @if($currentStep === 2)
                <form wire:submit.prevent="submit" class="space-y-6">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-lg border border-gray-200 dark:border-gray-600 mb-6">
                        <h4 class="text-sm font-semibold text-gray-800 dark:text-gray-200 uppercase">Datos Encontrados</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1"><span class="font-medium">Cliente:</span> {{ $company_name }}</p>
                        <p class="text-sm text-gray-600 dark:text-gray-400"><span class="font-medium">Factura:</span> {{ $invoice_number }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-900 dark:text-gray-100 mb-3">Seleccione los productos que presentan fallas:</label>
                        @error('general_products') <div class="text-sm text-red-500 mb-3 bg-red-50 p-2 rounded">{{ $message }}</div> @enderror
                        
                        <div class="space-y-4">
                            @foreach($foundProducts as $prod)
                                @php $id = $prod['id']; @endphp
                                <div class="flex flex-col p-4 border {{ isset($selectedProducts[$id]['selected']) && $selectedProducts[$id]['selected'] ? 'border-indigo-500 bg-indigo-50/50 dark:bg-indigo-900/10' : 'border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-800' }} rounded-xl transition-colors">
                                    <div class="flex items-start cursor-pointer" wire:click="$toggle('selectedProducts.{{ $id }}.selected')">
                                        <div class="flex items-center h-5 mt-1">
                                            <input type="checkbox" wire:model="selectedProducts.{{ $id }}.selected" class="focus:ring-indigo-500 h-5 w-5 text-indigo-600 border-gray-300 rounded cursor-pointer" id="prod-{{ $id }}">
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <label for="prod-{{ $id }}" class="text-base font-semibold text-gray-800 dark:text-gray-200 cursor-pointer block">
                                                {{ $prod['name'] }} <span class="text-gray-400 font-normal text-sm">({{ $prod['reference'] }})</span>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    @if(isset($selectedProducts[$id]['selected']) && $selectedProducts[$id]['selected'])
                                        <div class="mt-4 pl-8 border-t border-indigo-100 dark:border-gray-700 pt-4 space-y-4">
                                            
                                            <!-- Cantidad -->
                                            <div class="flex items-center gap-2">
                                                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Cantidad a reclamar:</label>
                                                <input type="number" wire:model="selectedProducts.{{ $id }}.qty" min="1" max="{{ $prod['max_qty'] }}" class="block w-24 text-sm rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 px-3 py-1.5 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                                <span class="text-xs text-gray-500 dark:text-gray-400">de {{ $prod['max_qty'] }} disponibles</span>
                                            </div>
                                            @error("selectedProducts.{$id}.qty") <span class="text-xs text-red-500 block">{{ $message }}</span> @enderror

                                            <!-- Observación -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Detalle de la falla (Opcional)</label>
                                                <textarea wire:model="productDescriptions.{{ $id }}" rows="2" placeholder="Describa el motivo de la garantía para este producto..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border"></textarea>
                                            </div>

                                            <!-- Evidencias -->
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Evidencia / Fotos (Opcional)</label>
                                                <div class="flex items-center gap-3">
                                                    <label for="file-{{ $id }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition cursor-pointer">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                                        Cargar Archivos
                                                    </label>
                                                    <input id="file-{{ $id }}" wire:model="productMedia.{{ $id }}" type="file" multiple accept="image/*,video/mp4,video/quicktime" class="sr-only">
                                                    
                                                    <div wire:loading wire:target="productMedia.{{ $id }}" class="text-xs text-indigo-600">Cargando...</div>
                                                </div>
                                                
                                                @if(isset($productMedia[$id]) && count($productMedia[$id]) > 0)
                                                <ul class="mt-2 space-y-1">
                                                    @foreach($productMedia[$id] as $file)
                                                        <li class="text-xs text-green-600 dark:text-green-400 flex items-center">
                                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                            {{ $file->getClientOriginalName() }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                                @endif
                                                @error("productMedia.{$id}.*") <span class="text-xs text-red-500 block mt-1">{{ $message }}</span> @enderror
                                            </div>

                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <hr class="border-gray-200 dark:border-gray-700">

                    <!-- Asesor Comercial (Mover del Paso 3 al 2) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Asesor Comercial (Opcional)</label>
                        <input wire:model="advisor_name" type="text" placeholder="¿Qué comercial lo atiende?" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white sm:text-sm px-3 py-2 border">
                    </div>

                    <div class="pt-4 flex justify-between items-center bg-gray-50 dark:bg-gray-800/50 -mx-8 -mb-8 p-6 rounded-b-2xl border-t border-gray-100 dark:border-gray-750">
                        <button type="button" wire:click="previousStep" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="mr-2 -ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                            Volver
                        </button>
                        <button type="submit" class="group relative inline-flex items-center justify-center py-2.5 px-6 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 shadow-lg shadow-indigo-200 dark:shadow-none transition-all" wire:loading.attr="disabled" wire:target="submit, productMedia">
                            <span wire:loading.remove wire:target="submit">Enviar Solicitud de Garantía</span>
                            <span wire:loading wire:target="submit">Enviando...</span>
                            <svg wire:loading.remove wire:target="submit" class="ml-2 -mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        </button>
                    </div>
                </form>
                @endif

            </div>
        @endif
    </div>
</div>
