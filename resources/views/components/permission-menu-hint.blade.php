@props(['name'])
@php
    $map = [
        'Ventas'               => 'Menú: Ventas · acceso rápido "Nueva Venta"',
        'Caja'                 => 'Menú: Caja · acceso rápido "Cajas"',
        'Inventario'           => 'Menú: Inventario · acceso rápido "Productos"',
        'Reportes'             => 'Menú: Informes',
        'Compras'              => 'Menús: Compras y Órdenes',
        'Cartera'              => 'Menú: Facturación',
        'Mercadeo'             => 'Menú: Mercadeo',
        'Gestión de Videos'    => 'Submenú de Mercadeo (Videos)',
        'Usuarios'              => 'Menú: Gestión de contactos · acceso rápido "Clientes"',
        'Gestión de contactos'  => 'Menú: Gestión de contactos (Contactos + Usuarios del sistema)',
        'Parametros'           => 'Menú: Parámetros',
        'Produccion'           => 'Menú: Producción (deshabilitado)',
        'Almacen'              => 'Menú: Almacén',
        'Importaciones'        => 'Menú: Importaciones',
        'Garantias'            => 'Menú: Devoluciones y Garantías',
        'Proyectos'            => 'Menú: Proyectos',
        'Planeacion de Tareas' => 'Menú: Planeación de Tareas',
        'Solicitudes'          => 'Menú: Solicitudes',
        'Despachos'            => 'Sin sección de menú asignada',
    ];
    $hint = $map[$name] ?? 'Sin sección de menú asignada';
@endphp
<span class="pmh">
    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <span class="pmh-tip">{{ $hint }}</span>
</span>
@once
<style>
    .pmh{position:relative;display:inline-flex;align-items:center;cursor:help}
    .pmh-tip{
        position:absolute;left:calc(100% + 6px);top:50%;transform:translateY(-50%);
        white-space:nowrap;background:#111827;color:#fff;font-size:11px;font-weight:500;
        padding:4px 8px;border-radius:6px;box-shadow:0 4px 14px rgba(0,0,0,.25);
        opacity:0;pointer-events:none;transition:opacity .12s ease;z-index:60;
    }
    .pmh:hover .pmh-tip{opacity:1}
</style>
@endonce
