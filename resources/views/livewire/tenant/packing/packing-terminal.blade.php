<div class="py-6" x-data="packingTerminal()">
    <!-- Warning de Foco -->
    <div id="focus-warning" x-show="!hasFocus" x-cloak
         class="fixed inset-0 bg-slate-900/95 z-[100] flex flex-col items-center justify-center p-6 text-center border-8 border-yellow-500">
        <div class="animate-bounce mb-6">
            <svg class="w-24 h-24 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <h2 class="text-4xl font-black text-white mb-4">¡VENTANA FUERA DE FOCO!</h2>
        <p class="text-xl text-gray-300 max-w-lg mb-8">
            Haz clic en cualquier parte de la pantalla antes de usar el lector QR para asegurar que los datos se capturen correctamente.
        </p>
        <button @click="gainFocus()" class="px-8 py-4 bg-yellow-500 hover:bg-yellow-600 text-slate-900 font-bold rounded-xl text-xl transition-all">
            ENTENDIDO / VOLVER
        </button>
    </div>

    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-700 overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 bg-indigo-600 flex justify-between items-center">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    Estación de Empaque
                </h3>
                <button @click="$dispatch('openPrinterConfig')" class="p-2 text-indigo-100 hover:text-white hover:bg-white/10 rounded-lg transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                </button>
            </div>

            <div class="p-8">
                <!-- SECCIÓN 1: OPERADOR -->
                <div class="mb-10 text-center">
                    <template x-if="isOperadorValid">
                        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl p-4 flex items-center justify-between animate-fadeIn">
                            <div class="flex items-center text-green-700 dark:text-green-400 font-bold">
                                <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Operador: <span class="ml-2 text-green-800 dark:text-green-300" x-text="nombreOperador"></span>
                            </div>
                            <button wire:click="resetOperador" class="text-xs font-bold text-green-600 hover:text-green-700 uppercase underline tracking-widest">
                                Cambiar
                            </button>
                        </div>
                    </template>

                    <template x-if="!isOperadorValid">
                        <div>
                            <label class="block text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">1. Identificación del Operador</label>
                            <input type="text" 
                                   id="qr_usuario"
                                   wire:model.live="userqr"
                                   readonly
                                   placeholder="Escanea tu código QR"
                                   class="w-full text-center py-5 bg-gray-50 dark:bg-slate-900 border-2 border-dashed border-indigo-300 dark:border-slate-700 rounded-2xl text-2xl font-bold text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-0 outline-none transition-all cursor-default"
                                   @keydown="scannerHack($event)">
                            <p class="mt-2 text-xs text-gray-400">Usa el lector láser para identificarte</p>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-100 dark:border-slate-700 my-8"></div>

                <!-- SECCIÓN 2: PEDIDO -->
                <div :class="!isOperadorValid ? 'opacity-20 pointer-events-none' : ''" class="transition-all duration-500">
                    <div class="mb-8 text-center">
                        <label class="block text-sm font-bold text-gray-500 uppercase tracking-widest mb-3">2. Escaneo de Orden (OP)</label>
                        <input type="text" 
                               id="qr_op"
                               wire:model.live="opqr"
                               readonly
                               placeholder="Escanea QR de la OP"
                               class="w-full text-center py-5 bg-gray-50 dark:bg-slate-900 border-2 border-dashed border-indigo-300 dark:border-slate-700 rounded-2xl text-2xl font-bold text-gray-800 dark:text-white focus:border-indigo-500 focus:ring-0 outline-none transition-all cursor-default"
                               @keydown="scannerHack($event)">
                        <div class="mt-3 min-h-[1.5rem]">
                            <span :class="msgType === 'success' ? 'text-green-600' : 'text-red-500'" 
                                  class="text-sm font-bold animate-pulse" 
                                  x-text="msgOp"></span>
                        </div>
                    </div>

                    <div class="mb-10">
                        <label class="block text-center text-sm font-bold text-gray-500 uppercase tracking-widest mb-4">3. Cantidad de Bultos / Cajas</label>
                        <div class="flex items-center justify-center space-x-6">
                            <button @click="decrementCajas" class="p-3 bg-gray-100 dark:bg-slate-700 rounded-xl hover:bg-gray-200 transition-colors">
                                <svg class="w-8 h-8 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            </button>
                            <input type="number" 
                                   id="cant_cajas"
                                   wire:model="cant_cajas"
                                   class="w-24 text-center text-4xl font-black bg-transparent border-none focus:ring-0 dark:text-white"
                                   min="1">
                            <button @click="incrementCajas" class="p-3 bg-indigo-100 dark:bg-indigo-900/30 rounded-xl hover:bg-indigo-200 transition-colors">
                                <svg class="w-8 h-8 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            </button>
                        </div>
                    </div>

                    <button @click="procesarRegistro" 
                            id="btn_registrar"
                            :disabled="!remissionValid"
                            class="w-full py-5 bg-green-600 hover:bg-green-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-xl font-black rounded-2xl shadow-xl shadow-green-500/20 transition-all flex items-center justify-center">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                        REGISTRAR Y GENERAR RÓTULOS
                    </button>
                </div>

                <!-- Historial -->
                <div class="mt-12 border-t border-gray-100 dark:border-slate-700 pt-6">
                    <h4 class="text-sm font-bold text-gray-400 uppercase tracking-widest mb-4">Últimos procesados:</h4>
                    <div class="space-y-3">
                        <template x-for="item in historial" :key="item.op">
                            <div class="flex justify-between items-center bg-gray-50 dark:bg-slate-900/50 p-3 rounded-lg border border-gray-100 dark:border-slate-800">
                                <span class="font-bold text-gray-700 dark:text-gray-300">OP #<span x-text="item.op"></span> - <span x-text="item.cajas"></span> bultos</span>
                                <span class="text-xs bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-400 px-2 py-1 rounded font-bold" x-text="item.hora"></span>
                            </div>
                        </template>
                        <template x-if="historial.length === 0">
                            <p class="text-center text-sm text-gray-400 italic py-4">Esperando primer escaneo...</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modales Adicionales -->
    @livewire('tenant.configuration.printer-config-modal')

    <script>
        function packingTerminal() {
            return {
                hasFocus: true,
                isOperadorValid: @entangle('is_operador_valid'),
                nombreOperador: @entangle('nombre_operador'),
                msgOp: @entangle('msg_op'),
                msgType: @entangle('msg_type'),
                remissionValid: false,
                historial: @entangle('historial'),

                init() {
                    window.addEventListener('blur', () => this.hasFocus = false);
                    window.addEventListener('focus', () => {
                        this.hasFocus = true;
                        this.reenfocar();
                    });
                    
                    this.reenfocar();

                    this.$watch('msgOp', value => {
                        this.remissionValid = value.includes('detectada');
                    });

                    // Listener para impresión
                    window.addEventListener('imprimir-rotulo', event => {
                        this.enviarAlProxy(event.detail[0].url, event.detail[0].data);
                    });
                },

                gainFocus() {
                    this.hasFocus = true;
                    this.reenfocar();
                },

                reenfocar() {
                    setTimeout(() => {
                        if (!this.isOperadorValid) {
                            document.getElementById('qr_usuario')?.focus();
                        } else {
                            document.getElementById('qr_op')?.focus();
                        }
                    }, 300);
                },

                scannerHack(e) {
                    // Permitir solo si es readonly (lector láser)
                    if (e.target.readOnly) {
                        e.target.readOnly = false;
                        setTimeout(() => {
                            e.target.readOnly = true;
                        }, 20);
                    }
                },

                incrementCajas() {
                    this.$wire.set('cant_cajas', parseInt(this.$wire.get('cant_cajas')) + 1);
                },

                decrementCajas() {
                    const current = parseInt(this.$wire.get('cant_cajas'));
                    if (current > 1) {
                        this.$wire.set('cant_cajas', current - 1);
                    }
                },

                procesarRegistro() {
                    this.$wire.registrarEmpaque();
                },

                async enviarAlProxy(url, data) {
                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify(data)
                        });
                        const result = await response.json();
                        if (result.status === 'ok') {
                            toastr.success('Rótulo enviado correctamente');
                        } else {
                            toastr.error('Error en impresora: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error enviando al proxy:', error);
                        toastr.error('No se pudo conectar con el PC de impresión local');
                    }
                }
            }
        }
    </script>
</div>
