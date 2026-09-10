@props(['name'])
@php
    // Mapa: nombre del permiso (catálogo usr_permissions) -> a qué parte del menú corresponde.
    $map = [
        'Ventas'               => 'Menú: Ventas · acceso rápido "Nueva Venta"',
        'Caja'                 => 'Menú: Caja · acceso rápido "Cajas"',
        'Inventario'           => 'Menú: Inventario · acceso rápido "Productos"',
        'Reportes'             => 'Menú: Informes · acceso rápido "Informes"',
        'Compras'              => 'Menús: Compras y Órdenes',
        'Cartera'              => 'Menú: Facturación (Pedidos, Cartera, Facturas, Cotizaciones)',
        'Mercadeo'             => 'Menú: Mercadeo',
        'Gestión de Videos'    => 'Submenú de Mercadeo (Videos)',
        'Usuarios'             => 'Menú: Gestión de contactos (Contactos y Usuarios) · acceso rápido "Clientes"',
        'Parametros'           => 'Menú: Parámetros',
        'Produccion'           => 'Menú: Producción (deshabilitado por ahora)',
        'Almacen'              => 'Menú: Almacén',
        'Importaciones'        => 'Menú: Importaciones',
        'Garantias'            => 'Menú: Devoluciones y Garantías',
        'Proyectos'            => 'Menú: Proyectos',
        'Planeacion de Tareas' => 'Menú: Planeación de Tareas',
        'Solicitudes'          => 'Menú: Solicitudes',
        'Despachos'            => 'Sin sección de menú asignada todavía',
    ];
    $hint = $map[$name] ?? 'Sin sección de menú asignada todavía';
@endphp
<span class="inline-flex items-center gap-1.5">
    <span>{{ $name }}</span>
    <span x-data="{ show: false }" class="relative inline-flex">
        <button type="button"
                @mouseenter="show = true" @mouseleave="show = false" @click="show = !show"
                @focus="show = true" @blur="show = false"
                class="text-gray-400 hover:text-indigo-500 dark:hover:text-indigo-400 focus:outline-none"
                aria-label="¿A qué menú corresponde?">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </button>
        <span x-show="show" x-transition x-cloak
              class="absolute left-4 top-1/2 -translate-y-1/2 z-[60] whitespace-nowrap rounded-md
                     bg-gray-800 dark:bg-gray-700 px-2.5 py-1.5 text-xs font-medium text-white shadow-xl">
            {{ $hint }}
        </span>
    </span>
</span>
