<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Sobrantes de Laboratorio</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #111827;
        }
        .header p {
            margin: 5px 0 0;
            color: #6b7280;
            font-size: 10px;
        }
        .filters {
            margin-bottom: 15px;
            font-size: 10px;
            color: #4b5563;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #FCE4D6;
            color: #333;
            font-weight: bold;
            text-align: left;
            padding: 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
        }
        td {
            padding: 7px 8px;
            border: 1px solid #e5e7eb;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center {
            text-align: center;
        }
        .footer {
            margin-top: 20px;
            text-align: right;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Sobrantes de Laboratorio</h1>
        <p>Control de inventario en centímetros de recortes de materiales</p>
    </div>

    <div class="filters">
        <strong>Período:</strong> {{ $dateFrom ?? 'Inicio' }} al {{ $dateTo ?? 'Hoy' }}
        @if($search)
            | <strong>Búsqueda:</strong> "{{ $search }}"
        @endif
        | <strong>Fecha de Emisión:</strong> {{ now()->format('d/m/Y H:i') }}
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Código / SKU</th>
                <th style="width: 45%;">Nombre del Insumo</th>
                <th style="width: 15%; text-align: center;">Sobrante Disp.</th>
                <th style="width: 15%; text-align: center;">Largo Original</th>
                <th style="width: 10%; text-align: center;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leftovers as $left)
                <tr>
                    <td>{{ $left->item->internal_code ?? $left->item->sku ?? 'N/A' }}</td>
                    <td>{{ $left->item->name ?? $left->item->display_name ?? 'Insumo' }}</td>
                    <td class="text-center"><strong>{{ number_format($left->available_cm, 0) }} cm</strong></td>
                    <td class="text-center">
                        @if($left->item && $left->item->dimensions && $left->item->dimensions->long > 0)
                            {{ number_format($left->item->dimensions->long, 0) }} cm
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">{{ $left->created_at ? $left->created_at->format('d/m/Y') : '' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center" style="padding: 20px; color: #9ca3af;">
                        No se encontraron sobrantes registrados con los filtros seleccionados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generado automáticamente por el ERP el {{ now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
