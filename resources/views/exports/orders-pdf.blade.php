<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Órdenes de Importación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 15px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 8px;
        }
        .header h1 {
            font-size: 16px;
            margin: 0;
            color: #4f46e5;
            text-transform: uppercase;
        }
        .header p {
            margin: 4px 0 0 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #e5e7eb;
            padding: 6px 4px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 4px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            background-color: #e5e7eb;
            color: #374151;
        }
        .shipping-detail {
            font-size: 9px;
            line-height: 1.2;
        }
        .eliminated-box {
            color: #b91c1c;
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            padding: 4px;
            border-radius: 3px;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Órdenes de Importación</h1>
        <p>Fecha de Generación: {{ now()->format('d/m/Y H:i') }} | Total registros: {{ count($orders) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="4%" class="text-center">ID</th>
                <th width="28%">Item / Producto</th>
                <th width="10%">Factory Ref</th>
                <th width="8%" class="text-right">Last ($)</th>
                <th width="6%" class="text-center">Cant.</th>
                <th width="8%">Prio</th>
                <th width="8%" class="text-right">Cotizado</th>
                <th width="18%">Shipping Information</th>
                <th width="10%">Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $row)
                <tr>
                    <td class="text-center">{{ $row->id }}</td>
                    <td>{{ $row->item }}</td>
                    <td>{{ $row->factory_ref ?? 'N/A' }}</td>
                    <td class="text-right">${{ number_format($row->exw ?? 0, 2) }}</td>
                    <td class="text-center">{{ number_format($row->qty_requested ?? 0) }}</td>
                    <td>{{ $row->label ?? $row->priority ?? 'N/A' }}</td>
                    <td class="text-right">${{ number_format($row->price ?? 0, 2) }}</td>
                    <td>
                        <div class="shipping-detail">
                            @if ($row->status == 11)
                                <div class="eliminated-box">
                                    <strong>Eliminado por:</strong> {{ $row->deleted_by_user ?? 'N/A' }}<br>
                                    <strong>Justificación:</strong> "{{ $row->delete_justification ?? '' }}"
                                </div>
                            @elseif ($row->operation_number || $row->way || $row->etd)
                                <strong>O.N:</strong> {{ $row->operation_number ?? '—' }}<br>
                                <strong>ETD:</strong> {{ $row->etd ? \Carbon\Carbon::parse($row->etd)->format('d/m/Y') : '—' }}<br>
                                <strong>Vía:</strong> {{ $row->way ?? '—' }}<br>
                                <strong>Rec:</strong> {{ $row->received_at ? \Carbon\Carbon::parse($row->received_at)->format('d/m/Y') : '—' }}
                            @else
                                —
                            @endif
                        </div>
                    </td>
                    <td>
                        <span class="badge">
                            {{ $row->translated_name ?? 'N/A' }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
