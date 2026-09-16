<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Alistamiento #{{ $orderNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #000; background: #fff; padding: 16px; }
        .header { border: 2px solid #000; margin-bottom: 12px; padding: 10px 14px; display: table; width: 100%; }
        .header-left { display: table-cell; width: 60%; vertical-align: middle; }
        .header-right { display: table-cell; width: 40%; vertical-align: middle; text-align: right; }
        .company-name { font-size: 15px; font-weight: bold; text-transform: uppercase; }
        .doc-title { font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; }
        .doc-number { font-size: 20px; font-weight: bold; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .info-table td { padding: 4px 8px; border: 1px solid #ccc; font-size: 10px; }
        .info-table td.label { font-weight: bold; background: #f5f5f5; width: 140px; }
        table.items { width: 100%; border-collapse: collapse; }
        table.items th { background: #000; color: #fff; padding: 6px 8px; text-align: left; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 6px 8px; border-bottom: 1px solid #ddd; font-size: 11px; }
        table.items td.qty { text-align: center; font-weight: bold; font-size: 13px; width: 80px; }
        .checkbox { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; }
        .footer-note { margin-top: 24px; font-size: 9px; color: #555; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-left">
            <div class="company-name">{{ $companyName }}</div>
            <div class="doc-title">Orden de Alistamiento</div>
        </div>
        <div class="header-right">
            <div class="doc-number">#{{ $orderNumber }}</div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td class="label">Fecha</td>
            <td>{{ \Carbon\Carbon::parse($movement->date)->format('d/m/Y') }}</td>
            <td class="label">Bodega</td>
            <td>{{ $movement->store->name ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Motivo</td>
            <td>{{ $movement->reason->name ?? '—' }}</td>
            <td class="label">Proyecto</td>
            <td>{{ $movement->project->title ?? ('Proyecto #' . $movement->project_id) }}</td>
        </tr>
        @if($movement->observations)
        <tr>
            <td class="label">Observaciones</td>
            <td colspan="3">{{ $movement->observations }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th style="width: 30px;">✓</th>
                <th>Código</th>
                <th>Descripción</th>
                <th style="text-align:center;">Cantidad</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movement->details as $detail)
                <tr>
                    <td><span class="checkbox"></span></td>
                    <td>{{ $detail->item->internal_code ?? '—' }}</td>
                    <td>{{ $detail->item->name ?? '—' }}</td>
                    <td class="qty">{{ $detail->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="footer-note">Documento generado automáticamente a partir de una Solicitud de Materiales de Proyectos. Alistado por: ______________________  Fecha: ______________</p>
</body>
</html>
