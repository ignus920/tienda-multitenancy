<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Producción #{{ $orderNumber }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #000;
            background: #fff;
        }

        /* ── Header ── */
        .header {
            border: 2px solid #000;
            margin-bottom: 4px;
        }
        .header-top {
            display: table;
            width: 100%;
            border-bottom: 1px solid #000;
        }
        .header-top-left {
            display: table-cell;
            width: 50%;
            padding: 6px 8px;
            vertical-align: middle;
            border-right: 1px solid #000;
        }
        .header-top-right {
            display: table-cell;
            width: 50%;
            padding: 6px 8px;
            vertical-align: middle;
            text-align: center;
        }
        .company-name {
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .order-title {
            font-size: 13px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #000;
        }

        /* ── Info grid ── */
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 6px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            font-size: 8px;
            text-transform: uppercase;
            color: #333;
            display: block;
            margin-bottom: 1px;
        }
        .value {
            font-size: 10px;
            font-weight: normal;
        }

        /* ── Sección proceso ── */
        .section {
            border: 1px solid #000;
            margin-bottom: 4px;
        }
        .section-header {
            background: #222;
            color: #fff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 3px 7px;
            letter-spacing: 0.5px;
        }
        .section-body {
            padding: 5px 7px;
        }

        /* ── Campos de proceso ── */
        .fields-grid {
            display: table;
            width: 100%;
        }
        .field-row {
            display: table-row;
        }
        .field-cell {
            display: table-cell;
            padding: 2px 6px 4px 0;
            vertical-align: top;
            width: 33%;
        }
        .field-underline {
            border-bottom: 1px solid #555;
            min-height: 16px;
            padding-bottom: 1px;
            font-size: 10px;
        }
        .field-empty {
            min-height: 20px;
            border-bottom: 1px dashed #aaa;
        }

        /* ── Tabla materiales ── */
        .mat-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 3px;
        }
        .mat-table th {
            background: #eee;
            border: 1px solid #999;
            padding: 3px 5px;
            font-size: 8px;
            text-transform: uppercase;
            text-align: left;
        }
        .mat-table td {
            border: 1px solid #bbb;
            padding: 3px 5px;
            font-size: 9px;
        }

        /* ── Firmas ── */
        .signatures {
            display: table;
            width: 100%;
            margin-top: 8px;
        }
        .sig-cell {
            display: table-cell;
            width: 33%;
            text-align: center;
            padding: 0 8px;
            vertical-align: bottom;
        }
        .sig-line {
            border-top: 1px solid #000;
            margin-top: 28px;
            padding-top: 3px;
            font-size: 8px;
            text-transform: uppercase;
        }

        /* ── Utilidades ── */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .mt-4 { margin-top: 4px; }
        .no-data { color: #888; font-style: italic; font-size: 8px; }

        @page { size: letter portrait; margin: 0.5in; }
        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    {{-- ── ENCABEZADO ── --}}
    <div class="header">
        <div class="header-top">
            <div class="header-top-left">
                <div class="company-name">{{ $companyName }}</div>
                <div style="font-size:8px; color:#555; margin-top:2px;">
                    Fecha recibido: <strong>{{ $order->date->format('d/m/Y') }}</strong>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    Fecha entrega: <strong>{{ $order->delivery_date->format('d/m/Y') }}</strong>
                </div>
            </div>
            <div class="header-top-right">
                <div class="order-title">Orden de Producción</div>
                <div class="order-number"># {{ $orderNumber }}</div>
            </div>
        </div>

        {{-- Datos generales --}}
        <table class="info-table">
            <tr>
                <td style="width:40%">
                    <span class="label">Cliente</span>
                    <span class="value bold">
                        {{ $order->contact ? trim($order->contact->firstName . ' ' . $order->contact->lastName) : '—' }}
                    </span>
                </td>
                <td style="width:35%">
                    <span class="label">Producto</span>
                    <span class="value bold">{{ $order->item?->name ?? '—' }}</span>
                </td>
                <td style="width:15%">
                    <span class="label">Cantidad</span>
                    <span class="value bold">{{ number_format($order->requested_qty) }}</span>
                </td>
                <td style="width:10%">
                    <span class="label">Estado</span>
                    <span class="value">
                        @php
                            $statusLabels = [3=>'Registrado',4=>'En proceso',5=>'Finalizado',6=>'Anulado',7=>'Pausado'];
                        @endphp
                        {{ $statusLabels[$order->status] ?? '—' }}
                    </span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Ref. Cliente</span>
                    <span class="value">{{ $order->customer_order ?: '—' }}</span>
                </td>
                <td>
                    <span class="label">Orden de Venta</span>
                    <span class="value">{{ $order->sales_order ?: '—' }}</span>
                </td>
                <td>
                    <span class="label">Etapa actual</span>
                    <span class="value">{{ $order->productiveProcess?->name ?? '—' }}</span>
                </td>
                <td>
                    <span class="label">Código</span>
                    <span class="value">{{ $order->item?->internal_code ?? '—' }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ── PROCESOS ── --}}
    @foreach($processRoute as $pi)
    @php
        $process      = $pi->process;
        $processId    = $pi->processId;
        $fields       = \App\Models\Tenant\Production\PrdFieldsProcess::where('processId', $processId)
                            ->where('status', 1)
                            ->orderBy('id')
                            ->get();
        $savedValues  = $processData->get($processId, collect())->keyBy(fn($d) => $d->field_processId);
        $hasValues    = $savedValues->isNotEmpty();
    @endphp
    <div class="section">
        <div class="section-header" style="{{ $hasValues ? 'background:#1a1a2e;' : '' }}">
            {{ $pi->process_route_order }}. {{ $process?->name ?? 'Proceso' }}
            @if($hasValues)
                &nbsp;✓
            @endif
        </div>
        <div class="section-body">
            @if($fields->isEmpty())
                <span class="no-data">Sin campos configurados para este proceso.</span>
            @else
            <div class="fields-grid">
                @foreach($fields->chunk(3) as $chunk)
                <div class="field-row">
                    @foreach($chunk as $field)
                    @php $savedField = $savedValues->get($field->id); @endphp
                    <div class="field-cell">
                        <span class="label">{{ $field->label }}</span>
                        @if($savedField)
                            <div class="field-underline bold">{{ $savedField->field_value }}</div>
                        @else
                            <div class="field-empty"></div>
                        @endif
                    </div>
                    @endforeach
                    {{-- Rellenar celdas vacías si el chunk no tiene 3 --}}
                    @for($i = $chunk->count(); $i < 3; $i++)
                    <div class="field-cell"></div>
                    @endfor
                </div>
                @endforeach
            </div>
            @endif

            {{-- Firma del operario --}}
            <table style="width:100%; margin-top:6px;">
                <tr>
                    <td style="width:70%"></td>
                    <td style="width:30%; border-top:1px solid #000; text-align:center; padding-top:2px;">
                        <span style="font-size:8px; text-transform:uppercase;">V.B. Operario</span>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @endforeach

    {{-- ── MATERIALES CONSUMIDOS ── --}}
    @if($materials->isNotEmpty())
    <div class="section">
        <div class="section-header" style="background:#4a4a4a;">
            Consumo de Materiales
        </div>
        <div class="section-body">
            <table class="mat-table">
                <thead>
                    <tr>
                        <th>Material / Insumo</th>
                        <th style="width:80px; text-align:center;">Cantidad</th>
                        <th style="width:80px; text-align:center;">Unidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $mat)
                    <tr>
                        <td>{{ $mat['name'] }}</td>
                        <td style="text-align:center;">{{ number_format($mat['qty'], 2) }}</td>
                        <td style="text-align:center;">{{ $mat['unit'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ── FIRMAS FINALES ── --}}
    <div class="signatures">
        <div class="sig-cell">
            <div class="sig-line">Elaborado por</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">Revisado por</div>
        </div>
        <div class="sig-cell">
            <div class="sig-line">Aprobado por</div>
        </div>
    </div>

<script>
    window.onload = function() {
        setTimeout(function() { window.print(); }, 500);
    };
</script>
</body>
</html>
