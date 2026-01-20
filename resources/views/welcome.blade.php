<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Tienda Multitenancy</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

        <!-- Styles -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- SweetAlert2 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    </head>
    <body class="antialiased font-sans">
        <!-- Navbar Sticky -->
        <header id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 ease-in-out bg-white/80 backdrop-blur-lg border-b border-gray-200/20 dark:bg-gray-900/80 dark:border-gray-700/20 shadow-sm">
            <nav aria-label="Global" class="mx-auto flex max-w-7xl items-center justify-between px-6 py-4 lg:px-8">
                <!-- Logo -->
                <div class="flex lg:flex-1">
                    <a href="#inicio" class="-m-1.5 p-1.5">
                        <span class="sr-only">Tienda</span>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Tienda</h1>
                    </a>
                </div>

                <!-- Mobile menu button -->
                <div class="flex lg:hidden">
                    <button type="button" id="mobile-menu-button" class="-m-2.5 inline-flex items-center justify-center rounded-md p-2.5 text-gray-700 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800">
                        <span class="sr-only">Abrir menú principal</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                        </svg>
                    </button>
                </div>

                <!-- Desktop navigation -->
                <div class="hidden lg:flex lg:gap-x-8">
                    <a href="#inicio" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors px-3 py-2 rounded-md">Inicio</a>
                    <a href="#productos" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors px-3 py-2 rounded-md">Productos</a>
                    <a href="#servicios" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors px-3 py-2 rounded-md">Servicios</a>
                    <a href="#contacto" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors px-3 py-2 rounded-md">Contacto</a>
                </div>

                <!-- Auth navigation -->
                @if (Route::has('login'))
                    <div class="hidden lg:flex lg:flex-1 lg:justify-end lg:gap-x-4">
                        <livewire:welcome.navigation />
                    </div>
                @endif
            </nav>

        </header>

        <div class="bg-gray-50 text-black/50 dark:bg-black dark:text-white/50">
            <div class="relative min-h-screen pt-24">
                <div class="relative w-full max-w-7xl mx-auto px-6">
                    <!-- Hero Section -->
                    <section id="inicio" class="py-20">
                        <div class="text-center">
                            <h1 class="text-4xl md:text-6xl font-bold text-gray-900 dark:text-white mb-6">
                                Bienvenido a Tienda
                            </h1>
                            <p class="text-xl text-gray-600 dark:text-gray-300 mb-8">
                                Plataforma multitenancy para tu negocio
                            </p>
                        </div>
                    </section>

                    <!-- Productos Section -->
                    <section id="productos" class="py-20">
                        <div class="text-center mb-12">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Nuestros Productos</h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300">Descubre lo que tenemos para ti</p>
                        </div>
                        <div class="grid gap-6 lg:grid-cols-2 lg:gap-8">


                            <!-- Formulario de Registro -->
                            <div class="bg-white rounded-lg shadow-lg p-6 dark:bg-zinc-900">
                                <h3 class="text-2xl font-bold text-center mb-6 text-gray-900 dark:text-white">Registro</h3>
                                @livewire('auth.register-company')
                            </div>

                            

                            <!-- Información adicional -->
                            <div class="bg-white rounded-lg shadow-lg p-6 dark:bg-zinc-900">
                                <h3 class="text-2xl font-bold text-center mb-6 text-gray-900 dark:text-white">Características</h3>
                                <ul class="text-gray-600 dark:text-gray-300 space-y-3">
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Sistema multitenancy
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Gestión de inventarios
                                    </li>
                                    <li class="flex items-center">
                                        <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Reportes avanzados
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </section>

                    <!-- Servicios Section -->
                    <section id="servicios" class="py-20 bg-gray-100 dark:bg-gray-800">
                        <div class="text-center mb-12">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Nuestros Servicios</h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300">Soluciones completas para tu negocio</p>
                        </div>
                        <div class="grid gap-8 md:grid-cols-3">
                            <div class="bg-white rounded-lg shadow-lg p-6 dark:bg-zinc-900">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Gestión de Inventario</h3>
                                <p class="text-gray-600 dark:text-gray-300">Control total de tu inventario con seguimiento en tiempo real.</p>
                            </div>
                            <div class="bg-white rounded-lg shadow-lg p-6 dark:bg-zinc-900">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Ventas y Facturación</h3>
                                <p class="text-gray-600 dark:text-gray-300">Sistema completo de ventas y generación de facturas.</p>
                            </div>
                            <div class="bg-white rounded-lg shadow-lg p-6 dark:bg-zinc-900">
                                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Reportes</h3>
                                <p class="text-gray-600 dark:text-gray-300">Análisis detallado de tu negocio con reportes personalizados.</p>
                            </div>
                        </div>
                    </section>

                    <!-- Contacto Section -->
                    <section id="contacto" class="py-20">
                        <div class="text-center">
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Contáctanos</h2>
                            <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">¿Tienes preguntas? Estamos aquí para ayudarte</p>
                            <div class="max-w-md mx-auto">
                                <p class="text-gray-600 dark:text-gray-300">Email: contacto@tienda.com</p>
                                <p class="text-gray-600 dark:text-gray-300">Teléfono: +1 234 567 8900</p>
                            </div>
                        </div>
                    </section>

                    <footer class="py-16 text-center text-sm text-gray-600 dark:text-gray-400">
                        © {{ date('Y') }} Tienda Multitenancy. Todos los derechos reservados.
                    </footer>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const navbar = document.getElementById('navbar');
                const mobileMenuButton = document.getElementById('mobile-menu-button');
                const mobileMenu = document.getElementById('mobile-menu');
                const mobileMenuOverlay = document.getElementById('mobile-menu-overlay');
                const mobileMenuSidebar = document.getElementById('mobile-menu-sidebar');
                const mobileMenuClose = document.getElementById('mobile-menu-close');
                let lastScrollY = window.scrollY;

                function openMobileMenu() {
                    mobileMenu.classList.remove('invisible');
                    // Forzar reflow para que la transición de opacidad y transform funcione
                    mobileMenu.offsetHeight;
                    mobileMenuOverlay.classList.remove('opacity-0');
                    mobileMenuOverlay.classList.add('opacity-100');
                    mobileMenuSidebar.classList.remove('-translate-x-full');
                    mobileMenuSidebar.classList.add('translate-x-0');
                    document.body.classList.add('overflow-hidden');
                }

                function closeMobileMenu() {
                    mobileMenuOverlay.classList.remove('opacity-100');
                    mobileMenuOverlay.classList.add('opacity-0');
                    mobileMenuSidebar.classList.remove('translate-x-0');
                    mobileMenuSidebar.classList.add('-translate-x-full');
                    
                    // Esperar a que los estilos terminen su transición antes de ocultar el contenedor
                    setTimeout(() => {
                        mobileMenu.classList.add('invisible');
                        document.body.classList.remove('overflow-hidden');
                    }, 300);
                }

                // Event listeners
                if (mobileMenuButton) {
                    mobileMenuButton.addEventListener('click', openMobileMenu);
                }

                if (mobileMenuClose) {
                    mobileMenuClose.addEventListener('click', closeMobileMenu);
                }

                if (mobileMenuOverlay) {
                    mobileMenuOverlay.addEventListener('click', closeMobileMenu);
                }

                // Función para actualizar el navbar en scroll
                function updateNavbar() {
                    const currentScrollY = window.scrollY;

                    if (currentScrollY > 50) {
                        navbar.classList.add('bg-white/95', 'dark:bg-gray-900/95', 'shadow-md');
                        navbar.classList.remove('bg-white/80', 'dark:bg-gray-900/80', 'shadow-sm');
                    } else {
                        navbar.classList.add('bg-white/80', 'dark:bg-gray-900/80', 'shadow-sm');
                        navbar.classList.remove('bg-white/95', 'dark:bg-gray-900/95', 'shadow-md');
                    }

                    lastScrollY = currentScrollY;
                }

                // Smooth scrolling para los enlaces del nav
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function (e) {
                        const href = this.getAttribute('href');
                        if (href === '#') return;
                        
                        const target = document.querySelector(href);
                        if (target) {
                            e.preventDefault();
                            const offsetTop = target.offsetTop - 80; // Ajuste para el navbar
                            window.scrollTo({
                                top: offsetTop,
                                behavior: 'smooth'
                            });

                            // Cerrar menú móvil si está abierto
                            if (!mobileMenu.classList.contains('invisible')) {
                                closeMobileMenu();
                            }
                        }
                    });
                });

                // Listener para el scroll
                window.addEventListener('scroll', updateNavbar, { passive: true });

                // Cerrar menú móvil al redimensionar ventana
                window.addEventListener('resize', function() {
                    if (window.innerWidth >= 1024 && !mobileMenu.classList.contains('invisible')) {
                        closeMobileMenu();
                    }
                });

                // Ejecutar una vez al cargar
                updateNavbar();
            });
        </script>

        @stack('scripts')

        <!-- Mobile menu container moved here -->
        <div id="mobile-menu" class="fixed inset-0 z-[100] lg:hidden invisible" role="dialog" aria-modal="true">
            <!-- Overlay -->
            <div id="mobile-menu-overlay" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>
            
            <!-- Sidebar -->
            <div id="mobile-menu-sidebar" class="fixed inset-y-0 left-0 w-full max-w-xs bg-gray-800 shadow-2xl -translate-x-full transition-transform duration-300 ease-in-out flex flex-col">
                <!-- Sidebar Header -->
                <div class="flex items-center justify-between p-6 bg-gray-900/40 border-b border-gray-700/50">
                    <a href="#inicio" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-600 rounded-lg flex items-center justify-center shadow-lg shadow-blue-600/20">
                            <span class="text-white font-bold text-xl uppercase">T</span>
                        </div>
                        <span class="text-xl font-bold text-white tracking-tight">Tienda</span>
                    </a>
                    <button type="button" id="mobile-menu-close" class="p-2 -mr-2 text-gray-400 hover:text-white transition-colors focus:outline-none">
                        <span class="sr-only">Cerrar menú</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Sidebar Content -->
                <div class="flex-1 overflow-y-auto py-6 px-4 bg-gray-800">
                    <nav class="space-y-1">
                        <a href="#inicio" class="group mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-gray-700/50 transition-all">
                            <svg class="w-6 h-6 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            <span class="font-medium">Inicio</span>
                        </a>
                        <a href="#productos" class="group mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-gray-700/50 transition-all">
                            <svg class="w-6 h-6 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <span class="font-medium">Productos</span>
                        </a>
                        <a href="#servicios" class="group mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-gray-700/50 transition-all">
                            <svg class="w-6 h-6 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">Servicios</span>
                        </a>
                        <a href="#contacto" class="group mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-gray-700/50 transition-all">
                            <svg class="w-6 h-6 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <span class="font-medium">Contacto</span>
                        </a>
                        <div class="my-4 border-t border-gray-700/50 px-4"></div>
                        <a href="{{ route('dashboard') }}" class="group mobile-link flex items-center gap-4 px-4 py-3 rounded-xl text-gray-300 hover:text-white hover:bg-gray-700/50 transition-all">
                            <svg class="w-6 h-6 opacity-60 group-hover:opacity-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
                            </svg>
                            <span class="font-medium">Dashboard</span>
                        </a>
                    </nav>
                </div>

                <!-- Sidebar Footer -->
                @if (Route::has('login'))
                    <div class="p-6 border-t border-gray-700/50 bg-gray-900/20">
                         <div class="mb-4 text-xs font-semibold text-gray-500 uppercase tracking-wider px-4">Acceso</div>
                         <div class="flex flex-col gap-2 sidebar-auth-overrides">
                             <style>
                                 .sidebar-auth-overrides nav {
                                     flex-direction: column !important;
                                     align-items: stretch !important;
                                     justify-content: flex-start !important;
                                     display: flex !important;
                                     margin-left: 0 !important;
                                     margin-right: 0 !important;
                                 }
                                 .sidebar-auth-overrides a {
                                     color: #e5e7eb !important;
                                     background-color: transparent !important;
                                     padding: 0.75rem 1rem !important;
                                     border-radius: 0.75rem !important;
                                     width: 100% !important;
                                     text-align: center !important;
                                     border: 1px solid rgba(255,255,255,0.1) !important;
                                     margin-bottom: 0.5rem !important;
                                 }
                                 .sidebar-auth-overrides a:hover {
                                     background-color: rgba(255,255,255,0.1) !important;
                                     color: white !important;
                                 }
                             </style>
                             <livewire:welcome.navigation />
                         </div>
                    </div>
                @endif
            </div>
        </div>
    </body>
</html>
