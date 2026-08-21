<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Favicon -->
        <link rel="icon" type="image/png" href="{{ asset('images/logofervi.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <!-- Quill.js -->
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>

        <!-- SortableJS -->
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.3/Sortable.min.js"></script>
    </head>
    <body class="font-sans antialiased"
          x-data="{
              sidebarOpen: false,
              sidebarCollapsed: localStorage.getItem('sidebarCollapsed') === 'true' || false,
              darkMode: localStorage.getItem('darkMode') === 'true' || false
          }"
          x-init="
              $watch('sidebarOpen', value => {
                  if (value) document.body.style.overflow = 'hidden';
                  else document.body.style.overflow = 'auto';
              });
              $watch('sidebarCollapsed', value => localStorage.setItem('sidebarCollapsed', value));
              $watch('darkMode', value => localStorage.setItem('darkMode', value));
          "
          :class="darkMode ? 'dark' : ''">

        <!-- Mobile sidebar overlay -->
        @if (Auth::user()?->profile_id != 18)
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-[60] lg:hidden">
            <div class="fixed inset-0 bg-gray-600 dark:bg-gray-900 bg-opacity-75 dark:bg-opacity-80" @click="sidebarOpen = false"></div>
        </div>

        <!-- Mobile sidebar -->
        <div x-show="sidebarOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-[70] w-64 bg-white dark:bg-gray-900 shadow-xl lg:hidden border-r border-gray-200 dark:border-gray-700">
            <div class="flex h-full flex-col">
                <livewire:layout.sidebar-navigation />
            </div>
        </div>
        @endif

        <!-- Desktop sidebar -->
        @if (Auth::user()?->profile_id != 18)
        <div class="hidden lg:fixed lg:inset-y-0 lg:flex lg:flex-col transition-all duration-300 z-50"
             :class="sidebarCollapsed ? 'lg:w-16' : 'lg:w-64'">
            <div class="flex min-h-0 flex-1 flex-col bg-white dark:bg-gray-900 shadow-xl border-r border-gray-200 dark:border-gray-700">
                <livewire:layout.sidebar-navigation />
            </div>
        </div>
        @endif

        <!-- Main content -->
        <div class="flex flex-1 flex-col min-h-screen transition-all duration-300 bg-gray-50 dark:bg-gray-900"
             :class="({{ Auth::user()?->profile_id == 18 ? 'true' : 'false' }}) ? '' : (sidebarCollapsed ? 'lg:pl-16' : 'lg:pl-64')">
            <!-- Top bar -->
            <div class="sticky top-0 z-40 bg-white dark:bg-gray-800 shadow-sm border-b border-gray-200 dark:border-gray-700">
                <div class="flex h-16 items-center gap-x-4 px-4 sm:gap-x-6 sm:px-6 lg:px-8">
                    <!-- Desktop sidebar toggle / Mobile menu button -->
                    @if (Auth::user()?->profile_id != 18)
                        <!-- Desktop sidebar toggle -->
                        <button type="button" class="hidden lg:block -m-2.5 p-2.5 text-gray-700 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400" @click="sidebarCollapsed = !sidebarCollapsed">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>

                        <!-- Mobile menu button -->
                        <button type="button" class="-m-2.5 p-2.5 text-gray-700 dark:text-gray-300 lg:hidden" @click="sidebarOpen = true">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>
                    @endif

                    <!-- Page title -->
                    <div class="flex flex-1 items-center gap-3">
                        @if (Auth::user()?->profile_id == 18)
                            <img src="{{ asset('images/logofervi.png') }}" alt="Fervicom" class="h-8 w-auto mr-1">
                        @endif
                        @if (isset($header))
                            <h1 class="text-xl font-semibold text-gray-900 dark:text-white shrink-0">{{ $header }}</h1>
                        @endif
                        <!-- Contenedor para Teleport del Buscador de Cliente (solo en el cotizador) -->
                        <div id="customer-header-container" class="ml-auto lg:mr-96"></div>
                        @if (Auth::user()?->profile_id == 17 && request()->routeIs('imports.imports-orders'))
                            <a href="{{ route('tenant.tickets') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors ml-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                                Requests
                             </a>
                        @endif
                        @if (Auth::user()?->profile_id == 17 && request()->routeIs('tenant.tickets'))
                            <a href="{{ route('imports.imports-orders') }}" 
                               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-lg shadow-sm transition-colors ml-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 002-2h2a2 2 0 012 2"></path>
                                </svg>
                                Orders
                            </a>
                        @endif
                    </div>

                    <!-- Contenedor para Teleport de Acciones de Cabecera (ej: Nuevas Solicitudes / Producto Nuevo) -->
                    <div id="header-actions-container" class="flex items-center gap-3 mr-2"></div>

                    <!-- Dark mode toggle -->
                    <button @click="darkMode = !darkMode" class="p-2 text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        <svg x-show="!darkMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg x-show="darkMode" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </button>

                    <!-- User menu -->
                    <livewire:layout.user-menu />
                </div>
            </div>

            <!-- Page content -->
            <main class="flex-1">
            {{ $slot }}
            </main>
        </div>
        <!-- Warehouse Selector Modal -->
        <livewire:warehouse-selector />
        <!-- Login Warehouse Selector Modal - Para selección de bodega después del login -->
        <livewire:auth.login-warehouse-selector />

        <!-- Modal Global de Soporte -->
        @if(!request()->routeIs('company.setup', 'company.*', 'register', 'login'))
        @livewire('tenant.components.ticket-request-modal')
        @endif
        <!-- Modal Global de Imágenes de Producto -->
        @livewire('tenant.components.product-image-modal')
        <!-- Modal Global de Observaciones de Producto -->
       @livewire('tenant.items.item-observation')
       <!-- Modal Global de Cálculo de Costo -->
       @livewire('tenant.components.import-cost-calculator')
       <!-- Modal Global de Cálculo de Potencia -->
       @livewire('tenant.components.product-bundle-power-calculator')
       <!-- Modal Global de Detalle de Corte de Items -->
       @livewire('tenant.components.inv-items-cut-details')

       <!-- Modal Global de Confirmación de Inventario -->
       @livewire('tenant.components.inventory-confirmation-modal')

       <!-- Modal Global de Accesorios de Item -->
       @livewire('tenant.components.item-accessories-modal')

     

        @livewireScripts
        @stack('scripts')

        <style>
            /* Animación suave para el toast */
            .swal2-show-slide-right {
                animation: swal2-slide-in-right 0.4s ease-out;
            }
            .swal2-hide-slide-right {
                animation: swal2-slide-out-right 0.3s ease-in;
            }
            @keyframes swal2-slide-in-right {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes swal2-slide-out-right {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
            .animate-pulse-subtle {
                animation: pulse-subtle 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
            }
            @keyframes pulse-subtle {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.85; shadow: 0 0 10px rgba(239, 68, 68, 0.1); }
            }
        </style>

        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('show-toast', (event) => {
                    const data = Array.isArray(event) ? event[0] : event;
                    
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        showClass: {
                            popup: 'swal2-show-slide-right'
                        },
                        hideClass: {
                            popup: 'swal2-hide-slide-right'
                        },
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: data.type || 'success',
                        title: data.message,
                        background: '#1f2937', // Gray-800
                        color: '#f9fafb', // Gray-50
                    });
                });

                // Alertas generales de Swal
                Livewire.on('swal', (event) => {
                    const data = Array.isArray(event) ? event[0] : event;
                    Swal.fire({
                        icon: data.icon || 'info',
                        title: data.title || '',
                        text: data.html ? undefined : (data.text || ''),
                        html: data.html || undefined,
                        timer: data.timer || null,
                        timerProgressBar: data.timer ? true : false,
                        confirmButtonColor: '#4f46e5',
                        background: document.documentElement.classList.contains('dark') ? '#1f2937' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#f9fafb' : '#111827',
                        customClass: {
                            container: 'swal-z-index-fix'
                        },
                        allowOutsideClick: data.icon !== 'error' && (data.allowOutsideClick !== undefined ? data.allowOutsideClick : true),
                        allowEscapeKey: data.icon !== 'error' && (data.allowEscapeKey !== undefined ? data.allowEscapeKey : true)
                    });
                });
            });

            async function copyImageToClipboard(imageUrl) {
                if (!imageUrl) return;
                
                const showToast = (type, message) => {
                    if (window.Livewire) {
                        Livewire.dispatch('show-toast', { type, message });
                    }
                };

                try {
                    // Intento de copiado avanzado (Blob/Archivo)
                    if (navigator.clipboard && window.isSecureContext) {
                        const response = await fetch(imageUrl);
                        const blob = await response.blob();
                        const item = new ClipboardItem({ [blob.type]: blob });
                        await navigator.clipboard.write([item]);
                        showToast('success', 'Se copió la imagen');
                    } else {
                        // Fallback: Copiar solo la URL si no hay HTTPS o falla el Blob
                        await navigator.clipboard.writeText(imageUrl);
                        showToast('info', 'Se copió el enlace de la imagen (Modo IP/No Seguro)');
                    }
                } catch (err) {
                    console.error('Error al copiar:', err);
                    try {
                        // Último intento: Copiar solo texto
                        await navigator.clipboard.writeText(imageUrl);
                        showToast('info', 'Se copió el enlace de la imagen');
                    } catch (lastErr) {
                        showToast('error', 'No se pudo copiar. Use clic derecho.');
                    }
                }
            }
        </script>

        <style>
            .swal-z-index-fix {
                z-index: 999999 !important;
            }
        </style>
    </body>
</html>
