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
<span x-data="{
        open: false,
        x: 0, y: 0,
        place(el) {
            const r = el.getBoundingClientRect();
            this.x = r.right + 8;
            this.y = r.top + r.height / 2;
            this.open = true;
        }
     }"
      class="inline-flex align-middle">
    <button type="button"
            @click.stop.prevent="open ? open = false : place($el)"
            @mouseenter="place($el)" @mouseleave="open = false"
            @blur="open = false"
            class="text-gray-400 hover:text-indigo-500 dark:hover:text-indigo-400 focus:outline-none"
            aria-label="¿A qué menú corresponde?">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
    </button>
    <template x-teleport="body">
        <span x-show="open" x-transition.opacity
              :style="`position:fixed;left:${x}px;top:${y}px;transform:translateY(-50%);z-index:9999`"
              class="pointer-events-none whitespace-nowrap rounded-md bg-gray-900 dark:bg-black px-2.5 py-1.5 text-xs font-medium text-white shadow-xl">
            {{ $hint }}
        </span>
    </template>
</span>
