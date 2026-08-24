<?php

use App\Auth\Livewire\Logout;
use App\Helpers\PermissionHelper;
use Livewire\Volt\Component;

new class extends Component
{
    public bool $contactsOpen = false;

    public function mount(): void
    {
        $persisted = session('sidebar.contactsOpen');
        if ($persisted !== null) {
            $this->contactsOpen = (bool) $persisted;
        } else {
            $this->contactsOpen = request()->routeIs('customers.*') || request()->routeIs('users.*');
        }
    }

    public function toggleContacts(): void
    {
        $this->contactsOpen = !$this->contactsOpen;
        session(['sidebar.contactsOpen' => $this->contactsOpen]);
    }

    /**
     * Log the current user out of the application.
     */
    public function logout(Logout $logout): void
    {
        $logout();

        $this->redirect(route('login'), navigate: true);
    }
}; ?>

<div class="flex h-full flex-col" :class="sidebarCollapsed ? 'overflow-visible' : 'overflow-y-auto'">
    <!-- Logo -->
    <div class="flex shrink-0 items-center px-4 py-4 border-b border-gray-200 dark:border-gray-700"
        :class="sidebarCollapsed ? 'justify-center' : 'justify-start'">
        <div class="flex items-center">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg overflow-hidden">
                <img class="h-10 w-10 object-contain" src="{{ asset('images/logofervi.png') }}" alt="Logo Fervi">
            </div>
            <div x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 transform scale-95"
                x-transition:enter-end="opacity-100 transform scale-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 transform scale-100"
                x-transition:leave-end="opacity-0 transform scale-95">
                <span class="ml-3 text-lg font-semibold text-gray-900 dark:text-white">{{ config('app.name', 'Laravel')
                    }}</span>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex flex-1 flex-col p-4 space-y-1">
        @php
            $isOperario     = auth()->user()?->profile_id === 8;
            $isAlmacenista  = auth()->user()?->profile_id === 6;
            $isAdmin        = in_array(auth()->user()?->profile_id, [1, 2]);
        @endphp
        <!-- Dashboard
        <a href="{{ route('dashboard') }}" wire:navigate
            class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m4-6v6m4-6v6" />
            </svg>

            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Dashboard
            </span>

            Tooltip
            <div x-show="tooltip" x-transition
                class="absolute left-full ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded shadow-lg z-50 whitespace-nowrap">
                Dashboard
            </div>
        </a> -->



        <!-- Escritorio -->
        @if(!$isOperario && Auth::user()?->profile_id != 17 && Auth::user()?->profile_id != 18)
        <a href="{{ route('tenant.dashboard') }}" wire:navigate
            class="group relative flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.select') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>

            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Escritorio
            </span>

            <!-- Tooltip -->
            <div x-show="tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-gray-400 font-semibold uppercase tracking-wide text-xs px-3 py-2 rounded-lg shadow-xl z-[9999] whitespace-nowrap">
                Escritorio
            </div>
        </a>



        @endif

        <!-- Ventas (menú con subitems) && !$isAlmacenista  -->
        @if(!$isOperario && Auth::user()?->profile_id !== 11 && PermissionHelper::userCanAny(['Ventas'], 'show'))
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('tenant.quoter.products') || request()->routeIs('tenant.gestion') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.quoter.products') || request()->routeIs('tenant.gestion') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5v6m4-6v6m4-6v6" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                     Ventas
                </span>

                <!-- Icono desplegable -->
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>

            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
               
                <a href="{{ route('tenant.quoter.products') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.quoter.products') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Ventas
                </a>
                <a href="{{ route('tenant.quoter') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.quoter') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Cotizaciones
                </a>
              
                <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.remissions.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Pedidos
                </a>
           
                <a href="{{ route('tenant.gestion') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.gestion') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Gestión
                </a>
                
            </div>

            <!-- Submenú desplegable (para sidebar colapsado) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Ventas</div>
                <a href="{{ route('tenant.quoter.products') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Ventas</a>
                <a href="{{ route('tenant.quoter') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Cotizaciones</a>
                <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Pedidos</a>
                <a href="{{ route('tenant.gestion') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Gestión</a>
            </div>
        </div>
        @endif

        @if (Auth::user()?->profile_id != 17 && Auth::user()?->profile_id != 18)
        <!-- Devoluciones -->
        <a href="{{ route('tenant.returns') }}" wire:navigate
            class="group relative flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.returns') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15L12 19L8 15M12 19V5" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5C8.13401 5 5 8.13401 5 12C5 13.933 5.78358 15.683 7.05025 16.9497" />
            </svg>

            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Devoluciones
            </span>

            <!-- Tooltip -->
            <div x-show="tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-gray-400 font-semibold uppercase tracking-wide text-xs px-3 py-2 rounded-lg shadow-xl z-[9999] whitespace-nowrap">
                Devoluciones
            </div>
        </a>
        @endif



        @if($isAlmacenista || $isAdmin)
        <!-- Almacén (Menú agrupado) -->
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('tenant.bodega.*') || request()->routeIs('inventory.confirmations') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.bodega.*') || request()->routeIs('inventory.confirmations') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                     Almacén
                </span>

                <!-- Icono desplegable -->
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>

            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
               
                <a href="{{ route('tenant.bodega') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.bodega.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Bodega
                </a>
                <a href="{{ route('inventory.confirmations') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('inventory.confirmations') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Confirmación
                </a>
                <a href="{{ route('tenant.dispatches') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.dispatches') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Despachos
                </a>
                 <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.remissions.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Pedidos
                </a>
            </div>

            <!-- Submenú desplegable (para sidebar colapsado) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Almacén</div>
                <a href="{{ route('tenant.bodega') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Bodega</a>
                <a href="{{ route('inventory.confirmations') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Confirmación</a>
                <a href="{{ route('tenant.dispatches') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Despachos</a>
                <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Pedidos</a>
            </div>
        </div>
        @endif

        <!-- Facturación -->
        @if(!$isOperario && !$isAlmacenista && !in_array(Auth::user()?->profile_id, [4, 9, 16]) && PermissionHelper::userCanAny(['Cartera', 'Ventas'], 'show'))
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('tenant.remissions.*') || request()->routeIs('tenant.cartera.*') || request()->routeIs('tenant.invoices.*') || request()->routeIs('tenant.quoter.*') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.remissions.*') || request()->routeIs('tenant.cartera.*') || request()->routeIs('tenant.invoices.*') || request()->routeIs('tenant.quoter.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                    Facturación
                </span>

                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú expandido -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.remissions.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Pedidos
                </a>
                <a href="{{ route('tenant.cartera.index') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.cartera.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Cartera
                </a>
                <a href="{{ route('tenant.invoices') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.invoices.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Facturas
                </a>
                <a href="{{ route('tenant.quoter') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.quoter.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Cotizaciones
                </a>
            </div>

            <!-- Submenú colapsado (hover) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Facturación</div>
                <a href="{{ route('tenant.remissions') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Pedidos</a>
                <a href="{{ route('tenant.cartera.index') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Cartera</a>
                <a href="{{ route('tenant.invoices') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Facturas</a>
                <a href="{{ route('tenant.quoter') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Cotizaciones</a>
            </div>
        </div>
        @endif

        <!-- Informes (Menú Desplegable) -->
        @if(!$isOperario && PermissionHelper::userCan('Ventas', 'show') && Auth::user()?->profile_id != 17)
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('tenant.reports.*') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <!-- Botón Principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.reports.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'"
                @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)"
                @click="open = !open">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>Informes</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                     class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú expandido -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400 font-medium">
                <a href="{{ route('tenant.reports.list') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.reports.list') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    General
                </a>
                <a href="{{ route('tenant.reports.justifications') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.reports.justifications') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Justificaciones de Cantidad
                </a>
            </div>

            <!-- Tooltip colapsado -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Informes</div>
                <a href="{{ route('tenant.reports.list') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">General</a>
                <a href="{{ route('tenant.reports.justifications') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Justificaciones de Cantidad</a>
            </div>
        </div>
        @endif




        <!-- Clientes (menú con subitems: ruta por defecto + navegación AJAX) -->
        @if(!$isOperario && PermissionHelper::userCanAny(['Usuarios'], 'show'))
        <div x-data="{ tooltip: false, open: false, _t: null }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <!-- Icono de Parámetros (sliders/ajustes) -->
                <svg class="h-5 w-5 shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path fill="currentColor"
                        d="M5 19V5v4.475V9zm3-6h3.525q.425 0 .713-.288t.287-.712t-.288-.712t-.712-.288H8q-.425 0-.712.288T7 12t.288.713T8 13m0 4h3.525q.425 0 .713-.288t.287-.712t-.288-.712t-.712-.288H8q-.425 0-.712.288T7 16t.288.713T8 17m0-8h8q.425 0 .713-.288T17 8t-.288-.712T16 7H8q-.425 0-.712.288T7 8t.288.713T8 9M5 21q-.825 0-1.412-.587T3 19V5q0-.825.588-1.412T5 3h14q.825 0 1.413.588T21 5v4.45q0 .425-.288.713T20 10.45t-.712-.287T19 9.45V5H5v14h4q.425 0 .713.288T10 20t-.288.713T9 21zm12-5q-1.05 0-1.775-.725T14.5 13.5t.725-1.775T17 11t1.775.725t.725 1.775t-.725 1.775T17 16m0 1q.975 0 1.938.188t1.862.562q.575.225.888.738T22 19.6v.4q0 .425-.288.713T21 21h-8q-.425 0-.712-.288T12 20v-.4q0-.6.313-1.112t.887-.738q.9-.375 1.863-.562T17 17" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                     Gestión de contactos
                </span>

                <!-- Icono desplegable -->
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>

            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('customers.customers') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('customers.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Gestión Contactos
                </a>
                 <a href="{{ route('users.users') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('users.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Gestión Usuarios
                </a>
            </div>

            <!-- Submenú desplegable (para sidebar colapsado) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Gestión de contactos</div>
                <a href="{{ route('customers.customers') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Gestión Contactos</a>
                <a href="{{ route('users.users') }}" class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Gestión Usuarios</a>
            </div>
        </div>
        @endif


        <!-- Perfil
        <a href="{{ route('profile') }}" wire:navigate
            class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('profile') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
            </svg>

            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Perfil
            </span>

            Toolti
            <div x-show="tooltip" x-transition
                class="absolute left-full ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded shadow-lg z-50 whitespace-nowrap">
                Perfil
            </div>
        </a>-->

        <!-- Parámetros (menú con subitems) -->
        @if(!$isOperario && PermissionHelper::userCan('Parametros', 'show'))
        <div x-data="{ tooltip: false, open: false, _t: null }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <!-- Icono de Parámetros (sliders/ajustes) -->
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                    Parámetros
                </span>

                <!-- Icono desplegable -->
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>

            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{route('tenant.parameters.company-information')}}" class="block rounded-md px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">
                    Empresa
                </a>
                <a href="{{ route('tenant.parameters.pricelists') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.parameters.pricelists') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Listas de Precios
                </a>

                <a href="{{ route('tenant.parameters.zones') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.parameters.zones') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Zonas
                </a>

                <a href="{{ route('tenant.parameters.routes') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.parameters.routes') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Rutas
                </a>
                <a href="{{ route('tenant.parameters.buttons') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.parameters.buttons') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Botones
                </a>
                <a href="{{ route('tenant.parameters.access-control') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('tenant.parameters.access-control') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Control de Acceso
                </a>
            </div>

            <!-- Submenú desplegable (para sidebar colapsado) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Parámetros</div>
                <a href="{{ route('tenant.parameters.company-information') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Empresa</a>
                <a href="{{ route('tenant.parameters.pricelists') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Listas de Precios</a>
                <a href="{{ route('tenant.parameters.zones') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Zonas</a>
                <a href="{{ route('tenant.parameters.routes') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Rutas</a>
                <a href="{{ route('tenant.parameters.buttons') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Botones</a>
                <a href="{{ route('tenant.parameters.access-control') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Control de Acceso</a>
            </div>
        </div>
        @endif

        <!-- Mercadeo -->
        @if(!$isOperario && PermissionHelper::userCan('Mercadeo', 'show'))
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('tenant.campaigns.*') || request()->routeIs('tenant.wordpress.*') || request()->routeIs('tenant.catalogs') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.campaigns.*') || request()->routeIs('tenant.wordpress.*') || request()->routeIs('tenant.catalogs') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'"
                @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)"
                @click="open = !open">
                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>Mercadeo</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                     class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú expandido -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('tenant.campaigns.index') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.campaigns.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Campañas
                </a>
                <a href="{{ route('tenant.wordpress.stock-sync') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.wordpress.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    WordPress Stock
                </a>
                <a href="{{ route('tenant.catalogs') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.catalogs') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Catálogos
                </a>
                <a href="{{ route('tenant.sliders.index') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('tenant.sliders.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Sliders de Promoción
                </a>
            </div>

            <!-- Tooltip colapsado -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Mercadeo</div>
                <a href="{{ route('tenant.campaigns.index') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Campañas</a>
                <a href="{{ route('tenant.wordpress.stock-sync') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">WordPress Stock</a>
                <a href="{{ route('tenant.catalogs') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Catálogos</a>
                <a href="{{ route('tenant.sliders.index') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Sliders de Promoción</a>
            </div>
        </div>
        @endif

        <!-- Solicitudes -->
        @if (Auth::user()?->profile_id != 18)
        <a href="{{ route('tenant.tickets') }}" wire:navigate
            class="group relative flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('tenant.tickets') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'"
            x-data="{ tooltip: false }" @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
            </svg>

            <span x-show="!sidebarCollapsed" class="ml-3" x-transition>
                Solicitudes
            </span>

            <div x-show="tooltip" x-transition
                class="absolute left-full ml-2 px-2 py-1 bg-gray-900 dark:bg-gray-700 text-white text-xs rounded shadow-lg z-50 whitespace-nowrap">
                Solicitudes
            </div>
        </a>
        @endif




        <!-- Inventario (menú con subitems) -->
        @if(!$isAlmacenista && PermissionHelper::userCan('Inventario', 'show'))
        <div x-data="{ tooltip: false, open: false, _t: null }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400 cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">
                <svg class="h-5 w-5 shrink-0" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    width="24" height="24" fill="none" viewBox="0 0 24 24">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 7h6l2 4m-8-4v8m0-8V6a1 1 0 0 0-1-1H4a1 1 0 0 0-1 1v9h2m8 0H9m4 0h2m4 0h2v-4m0 0h-5m3.5 5.5a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Zm-10 0a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0Z" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                    Inventario
                </span>

                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{url('/items/items')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Ítems</a>
                <a href="{{url('/inventory/categories')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Categorías</a>
                @if (PermissionHelper::getMerchantType() == 5)
                <a href="{{url('/inventory/commands')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Comandas</a>
                @endif

                <a href="{{url('/inventory/brands')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Marcas</a>
                <a href="{{url('/inventory/houses')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Casas</a>
                <a href="{{url('/inventory/units')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Unidades de Medida</a>
                <a href="{{ route('movements.movements') }}" wire:navigate
                   class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('movements.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Gestión movimientos
                </a>
                <a href="{{ route('transfers.transfers') }}" wire:navigate
                   class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('transfers.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                   Transferencias
                </a>
                <a href="{{ route('tenant.transfer_requests') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('transfer_requests.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">Solicitud de stock</a>
                <a href="{{url('/inventory/warehouses')}}" wire:navigate
                    class="block px-2 py-1 hover:text-indigo-600 dark:hover:text-indigo-400">Bodegas</a>
            </div>

            <!-- Submenú desplegable (para sidebar colapsado) -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Inventario</div>
                <a href="{{url('/items/items')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Ítems</a>
                <a href="{{url('/inventory/categories')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Categorías</a>
                @if (PermissionHelper::getMerchantType() == 5)
                <a href="{{url('/inventory/commands')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Comandas</a>
                @endif
                <a href="{{url('/inventory/brands')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Marcas</a>
                <a href="{{url('/inventory/houses')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Casas</a>
                <a href="{{url('/inventory/units')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Unidades de Medida</a>
                <a href="{{ route('movements.movements') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Gestión movimientos</a>
                <a href="{{ route('transfers.transfers') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Transferencias</a>
                <a href="{{ route('tenant.transfer_requests') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Solicitud de stock</a>
                <a href="{{url('/inventory/warehouses')}}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Bodegas</a>
            </div>
        </div>
        @endif

        <!-- Producción (menú con subitems) -->
        {{--
        @if (PermissionHelper::userCan('Produccion', 'show'))
        <div x-data="{ tooltip: false, open: {{ request()->routeIs('production.*') ? 'true' : 'false' }}, _t: null }" class="w-full relative">
            <!-- Botón principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('production.*') ? 'text-amber-600 dark:text-amber-400 bg-amber-50/50 dark:bg-amber-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-amber-600 dark:hover:text-amber-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)" @click="open = !open">

                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>

                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>
                    Producción
                </span>

                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                    class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <a href="{{ route('production.orders') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('production.orders') ? 'bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : 'hover:text-amber-600 dark:hover:text-amber-400' }}">
                    Órdenes
                </a>
                <a href="{{ route('production.processes') }}" wire:navigate
                    class="block rounded-md px-2 py-1 text-sm transition-colors duration-150 {{ request()->routeIs('production.processes') ? 'bg-amber-50 dark:bg-amber-900/50 text-amber-700 dark:text-amber-300' : 'hover:text-amber-600 dark:hover:text-amber-400' }}">
                    Procesos
                </a>
            </div>

            <!-- Submenú colapsado -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Producción</div>
                <a href="{{ route('production.orders') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Órdenes</a>
                <a href="{{ route('production.processes') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Procesos</a>
            </div>
        </div>
        @endif
        --}}

        <!-- Importaciones (Menú Desplegable Exclusivo para Analistas / Admin) -->
        @if((Auth::user()?->profile_id === 2 || (!$isOperario && PermissionHelper::userCan('Importaciones', 'show'))) && Auth::user()?->profile_id != 17)
        <div x-data="{
            tooltip: false,
            open: {{ request()->routeIs('imports.*') ? 'true' : 'false' }},
            _t: null
        }" class="w-full relative">
            <!-- Botón Principal -->
            <div class="group flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('imports.*') ? 'text-indigo-600 dark:text-indigo-400 bg-indigo-50/50 dark:bg-indigo-900/20' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }} cursor-pointer"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'"
                @mouseenter="tooltip = sidebarCollapsed"
                @mouseleave="_t = setTimeout(() => tooltip = false, 200)"
                @click="open = !open">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="2" stroke="currentColor" class="h-5 w-5 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 9 12 4l9 5-9 5-9-5Zm0 6 9 5 9-5" />
                </svg>
                <span x-show="!sidebarCollapsed" class="ml-3 flex-1" x-transition>Importaciones</span>
                <svg x-show="!sidebarCollapsed" :class="open ? 'rotate-90' : ''"
                     class="w-4 h-4 ml-auto transition-transform duration-200" fill="none" stroke="currentColor"
                     stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"></path>
                </svg>
            </div>

            <!-- Submenú expandido -->
            <div x-show="open && !sidebarCollapsed" x-transition
                class="ml-8 mt-1 space-y-1 text-sm text-gray-600 dark:text-gray-400 font-medium">
                <a href="{{ route('imports.imports') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('imports.imports') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Movimiento
                </a>
                <a href="{{ route('imports.costing') }}" wire:navigate
                    class="block rounded-md px-2 py-1 transition-colors duration-150 {{ request()->routeIs('imports.costing') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300' : 'hover:text-indigo-600 dark:hover:text-indigo-400' }}">
                    Costeo de Importaciones
                </a>
            </div>

            <!-- Tooltip colapsado -->
            <div x-show="sidebarCollapsed && tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-white rounded-lg shadow-xl z-[9999] whitespace-nowrap overflow-hidden min-w-[160px]"
                @mouseenter="clearTimeout(_t); tooltip = true" @mouseleave="_t = setTimeout(() => tooltip = false, 200)">
                <div class="px-3 py-2 text-xs font-semibold text-gray-400 uppercase tracking-wide border-b border-gray-700">Importaciones</div>
                <a href="{{ route('imports.imports') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Movimiento</a>
                <a href="{{ route('imports.costing') }}" wire:navigate
                    class="block px-3 py-2 text-sm hover:bg-gray-700 transition-colors">Costeo</a>
            </div>
        </div>
        @endif

        <!-- Ordenes -->
        @if (!$isOperario && !$isAlmacenista && PermissionHelper::userCan('Compras', 'show'))
        <a href="{{ route('imports.imports-orders') }}" wire:navigate
            class="group relative flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('imports.imports-orders') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <x-heroicon-o-clipboard-document-list class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" />

            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Órdenes
            </span>

            <!-- Tooltip -->
            <div x-show="tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-gray-400 font-semibold uppercase tracking-wide text-xs px-3 py-2 rounded-lg shadow-xl z-[9999] whitespace-nowrap">
                Órdenes
            </div>
        </a>
        @endif


        <!-- Caja -->
        @if (!$isOperario && PermissionHelper::userCan('Caja', 'show'))
        <a href="{{ route('petty-cash.petty-cash') }}" wire:navigate
            class="group relative flex items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 {{ request()->routeIs('petty-cash.*') ? 'bg-indigo-50 dark:bg-indigo-900/50 text-indigo-700 dark:text-indigo-300 border-r-2 border-indigo-500' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:text-indigo-600 dark:hover:text-indigo-400' }}"
            :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
            @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
                stroke="currentColor" class="h-5 w-5 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
            </svg>


            <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4"
                class="ml-3">
                Caja
            </span>

            <!-- Tooltip -->
            <div x-show="tooltip" x-transition
                class="absolute top-0 left-full ml-2 bg-gray-800 text-gray-400 font-semibold uppercase tracking-wide text-xs px-3 py-2 rounded-lg shadow-xl z-[9999] whitespace-nowrap">
                Caja
            </div>
        </a>
        @endif

        <!-- Spacer -->
        <div class="flex-1"></div>

        <!-- Logout -->
        <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
            <button wire:click="logout"
                class="group relative flex w-full items-center rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-200 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400"
                :class="sidebarCollapsed ? 'justify-center' : 'justify-start'" x-data="{ tooltip: false }"
                @mouseenter="tooltip = sidebarCollapsed" @mouseleave="tooltip = false">

                <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>

                <span x-show="!sidebarCollapsed" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-x-4"
                    x-transition:enter-end="opacity-100 translate-x-0"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-x-0"
                    x-transition:leave-end="opacity-0 translate-x-4" class="ml-3">
                    Cerrar Sesión
                </span>

                <!-- Tooltip -->
                <div x-show="tooltip" x-transition
                    class="absolute top-0 left-full ml-2 bg-gray-800 text-gray-400 font-semibold uppercase tracking-wide text-xs px-3 py-2 rounded-lg shadow-xl z-[9999] whitespace-nowrap">
                    Cerrar Sesión
                </div>
            </button>
        </div>
    </nav>
</div>