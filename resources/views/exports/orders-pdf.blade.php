<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Órdenes de Importación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #4f46e5;
            padding-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
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
            padding: 8px 6px;
            text-align: left;
        }
        th {
            background-color: #f9fafb;
            color: #374151;
            font-weight: bold;
            font-size: 10px;
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
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
        }
        .status-quoted {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-approved {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-production {
            background-color: #e0e7ff;
            color: #3730a3;
        }
        .status-transit {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-received {
            background-color: #e5e7eb;
            color: #374151;
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
                <th width="5%" class="text-center">ID</th>
                <th width="35%">Item / Producto</th>
                <th width="12%">Factory Ref</th>
                <th width="8%" class="text-right">Last ($)</th>
                <th width="8%" class="text-center">Cant.</th>
                <th width="10%">Etiqueta / Prio</th>
                <th width="10%" class="text-right">Cotizado</th>
                <th width="12%">Estado</th>
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
