<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $calc->name }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { margin-bottom: 12px; border-bottom: 2px solid #4f46e5; padding-bottom: 8px; }
        .header h1 { font-size: 16px; margin: 0; color: #4f46e5; }
        .header p { margin: 3px 0 0 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 5px 6px; text-align: left; vertical-align: top; }
        th { background-color: #f9fafb; color: #374151; font-weight: bold; font-size: 9px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .badge { display: inline-block; padding: 1px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-erp { background-color: #eef2ff; color: #4338ca; }
        .badge-ext { background-color: #fff1e6; color: #b5560c; }
        .total-row td { font-weight: bold; background-color: #f3f4f6; }
        .venta-box { width: 260px; margin-left: auto; margin-top: 10px; }
        .venta-box td { border: none; padding: 4px 6px; }
        .venta-good { color: #1d4ed8; font-weight: bold; }
        .venta-bad { color: #b91c1c; font-weight: bold; }
        .note { color: #9ca3af; font-size: 8px; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $calc->name }}</h1>
        <p>Lista de precio: {{ $calc->price_list_label }} &nbsp;·&nbsp; Creado por {{ $calc->creator->name ?? 'Usuario' }} &nbsp;·&nbsp; Fecha: {{ now()->format('d/m/Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Origen</th>
                <th>Descripción</th>
                <th class="text-right">Cantidad</th>
                <th class="text-right">Precio Unit.</th>
                <th class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lines as $line)
            <tr>
                <td><span class="badge {{ $line['origin'] === 'erp' ? 'badge-erp' : 'badge-ext' }}">{{ $line['origin'] === 'erp' ? 'ERP' : 'EXT' }}</span></td>
                <td>{{ $line['description'] }}</td>
                <td class="text-right">{{ rtrim(rtrim(number_format($line['qty_display'], 2), '0'), '.') }}{{ $line['mode'] === 'cm' ? ' cm' : '' }}</td>
                <td class="text-right">${{ number_format($line['unit_display'], 0) }}{{ $line['mode'] === 'cm' ? '/cm' : '' }}</td>
                <td class="text-right">${{ number_format($line['subtotal'], 0) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="text-right">Total costo</td>
                <td class="text-right">${{ number_format($totals['total'], 0) }}</td>
            </tr>
        </tbody>
    </table>

    @if($calc->sale_price)
    <table class="venta-box">
        <tr>
            <td>Precio de venta</td>
            <td class="text-right {{ $calc->sale_price >= $totals['total'] ? 'venta-good' : 'venta-bad' }}">${{ number_format($calc->sale_price, 0) }}</td>
        </tr>
        @if($calc->max_discount_percent)
        @php $withDisc = $calc->sale_price * (1 - $calc->max_discount_percent / 100); @endphp
        <tr>
            <td>Con descuento máximo ({{ $calc->max_discount_percent }}%)</td>
            <td class="text-right {{ $withDisc >= $totals['total'] ? 'venta-good' : 'venta-bad' }}">${{ number_format($withDisc, 0) }}</td>
        </tr>
        @endif
    </table>
    @endif

    <p class="note">Los precios de este documento corresponden al ERP en el momento de imprimir — pueden variar respecto a versiones anteriores impresas de este mismo cálculo.</p>
</body>
</html>
