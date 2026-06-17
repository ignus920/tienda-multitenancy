<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Corte #{{ $cutId }}</title>
    <style>
        @page { size: letter portrait; margin: 1cm; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 10pt; color: #333; line-height: 1.4; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .company-info h1 { margin: 0; font-size: 18pt; text-transform: uppercase; color: #1e3a8a; }
        .company-info p { margin: 2px 0; font-size: 9pt; color: #666; }
        .report-title { text-align: right; }
        .report-title h2 { margin: 0; font-size: 14pt; color: #1e3a8a; }
        .details-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; background: #f9fafb; padding: 10px; border-radius: 8px; }
        .detail-item b { color: #4b5563; text-transform: uppercase; font-size: 8pt; display: block; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e3a8a; color: white; text-align: left; padding: 8px; text-transform: uppercase; font-size: 9pt; }
        td { padding: 8px; border-bottom: 1px solid #e5e7eb; font-size: 9pt; }
        .cut-plan { display: flex; flex-wrap: wrap; gap: 5px; }
        .cut-pill { border: 1px solid #374151; padding: 2px 8px; border-radius: 4px; font-weight: bold; font-size: 8pt; background: #fff; }
        .unit-label { font-size: 7pt; color: #6b7280; margin-right: 4px; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 8pt; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="background: #fff9c4; padding: 10px; text-align: center; margin-bottom: 20px; border: 1px solid #fbc02d; border-radius: 8px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #1e3a8a; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold;">🖨️ CONFIRMAR IMPRESIÓN</button>
    </div>

    <div class="header" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px;">
        <!-- Logo y eslogan de Fervicom -->
        <div style="width: 35%; text-align: left;">
            <h1 style="margin: 0; font-size: 16pt; font-weight: 800; color: #1e3a8a; text-transform: uppercase; letter-spacing: 0.5px;">Fervicom</h1>
            <p style="margin: 2px 0 0 0; font-size: 7.5pt; font-weight: bold; color: #4b5563; text-transform: uppercase; letter-spacing: 1px;">Iluminación LED</p>
            <p style="margin: 4px 0 2px 0; font-size: 8pt; color: #1e3a8a; font-weight: 600;">www.fervicom.com</p>
            <p style="margin: 0; font-size: 8pt; color: #4b5563; font-style: italic;">Moderna iluminación a su alcance</p>
        </div>
        
        <!-- Título Central con Usuario y Fecha -->
        <div style="width: 35%; text-align: center;">
            <h2 style="margin: 0 0 6px 0; font-size: 13pt; font-weight: 800; color: #111827; letter-spacing: 0.5px; text-transform: uppercase;">Cortes de Perfil</h2>
            <p style="margin: 2px 0; font-size: 8.5pt; color: #374151;"><b>Usuario:</b> {{ $cutDetails->first()->created_by ?? 'N/A' }}</p>
            <p style="margin: 2px 0; font-size: 8.5pt; color: #374151;"><b>Fecha:</b> {{ $date->format('Y-m-d H:i:s') }}</p>
        </div>

        <!-- Información del Corte y Cliente a la Derecha -->
        <div style="width: 30%; text-align: right;">
            <p style="margin: 0 0 4px 0; font-size: 11pt; color: #111827;"><b>Corte: #</b> {{ $cutId }}</p>
            <p style="margin: 2px 0; font-size: 8.5pt; color: #374151;"><b>Cliente:</b> {{ $customer ? strtoupper($customer->firstName . ' ' . $customer->lastName) : 'CONSUMIDOR FINAL' }}</p>
        </div>
    </div>



    <table style="border: 2px solid #333;">
        <thead>
            <tr style="background: #1e293b; color: white; text-transform: uppercase; font-size: 8pt;">
                <th style="padding: 6px; text-align: left; border-right: 1px solid #475569; width: 50%;">Referencia</th>
                <th style="padding: 6px; text-align: left; border-right: 1px solid #475569; width: 15%;">#op</th>
                <th style="padding: 6px; text-align: left; width: 35%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cutDetails as $detail)
            <!-- Fila Principal: Datos Básicos -->
            <tr style="background: #f8fafc;">
                <td style="padding: 10px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <b style="font-size: 11pt; color: #1e3a8a;">{{ $detail->item->name }}</b><br>
                    <span style="font-size: 9pt; color: #64748b;">{{ $detail->item->internal_code }}</span>
                </td>
                <td style="padding: 10px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 10pt;">{{ $detail->production_order_id ? '# '.$detail->production_order_id : 'N/A' }}</span>
                </td>
                <td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">
                    <span style="font-size: 9pt; color: #475569;">{{ $detail->notes ?: 'Sin observaciones' }}</span>
                </td>
            </tr>

            <!-- Fila Secundaria: Largos, Acumulado, Sobrante -->
            <tr style="background: #ffffff;">
                <td style="padding: 6px 10px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <b style="font-size: 8pt; text-transform: uppercase; color: #64748b; display: block;">Largo perfil</b>
                    <span style="font-size: 10pt;">
                        {{ number_format($detail->length_cm, 2) }} cm 
                        {{ number_format($detail->length_mm, 0) }} mm
                    </span>
                </td>
                <td style="padding: 6px 10px; border-right: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0;">
                    <b style="font-size: 8pt; text-transform: uppercase; color: #64748b; display: block;">Acumulado</b>
                    <span style="font-size: 10pt;">
                        {{ number_format($detail->accumulated_cm, 2) }} cm 
                        {{ number_format($detail->accumulated_cm * 10, 0) }} mm
                    </span>
                </td>
                <td style="padding: 6px 10px; border-bottom: 1px solid #e2e8f0;">
                    <b style="font-size: 8pt; text-transform: uppercase; color: #64748b; display: block;">Sobrante</b>
                    <span style="font-size: 10pt;">
                        {{ number_format($detail->remaining_cm, 2) }} cm 
                        {{ number_format($detail->remaining_cm * 10, 0) }} mm
                    </span>
                </td>
            </tr>

            <!-- Fila de Plan de Corte y Cantidad de Perfiles -->
            <tr style="background: #f1f5f9;">
                <td colspan="2" style="padding: 6px 10px; border-right: 1px solid #e2e8f0;">
                    <b style="font-size: 9pt; text-transform: uppercase; color: #1e3a8a;">Plano de corte</b>
                </td>
                <td style="padding: 6px 10px; text-align: center;">
                    <b style="font-size: 8pt; text-transform: uppercase; color: #64748b; display: block;"># de Perfiles</b>
                    <b style="font-size: 11pt; color: #1e3a8a;">{{ $detail->repeat_in ?: 1 }}</b>
                </td>
            </tr>

            <!-- Fila de Segmentos (Píldoras) -->
            <tr style="background: #e2e8f0;">
                <td colspan="3" style="padding: 10px;">
                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                        <span style="width: 40px; font-weight: bold; font-size: 9pt;">cm</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach(explode(', ', $detail->plan_centimeters) as $cm)
                                @if(trim($cm)) <span style="border: 1.5px solid #000; padding: 3px 12px; border-radius: 8px; font-weight: bold; font-size: 10pt; background: #fff;">{{ trim($cm) }}</span> @endif
                            @endforeach
                        </div>
                    </div>
                    <div style="display: flex; align-items: center;">
                        <span style="width: 40px; font-weight: bold; font-size: 9pt;">mm</span>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            @foreach(explode(', ', $detail->plan_millimeters) as $mm)
                                @if(trim($mm)) <span style="border: 1.5px solid #000; padding: 3px 12px; border-radius: 8px; font-weight: bold; font-size: 10pt; background: #fff;">{{ trim($mm) }}</span> @endif
                            @endforeach
                        </div>
                    </div>
                </td>
            </tr>

            <!-- Espacio entre ítems -->
            <tr><td colspan="3" style="height: 15px; border: none;"></td></tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px; display: flex; justify-content: space-between; align-items: center; border: 1.5px solid #333; padding: 15px; border-radius: 8px; background: #fff; page-break-inside: avoid;">
        <!-- Firmas / Sellos -->
        <div style="display: flex; gap: 40px;">
            <div style="text-align: center; width: 150px;">
                <div style="height: 50px; border-bottom: 1px solid #666; margin-bottom: 5px;"></div>
                <span style="font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #4b5563;">Firma Autorizada</span>
            </div>
            <div style="text-align: center; width: 150px;">
                <div style="height: 50px; border-bottom: 1px solid #666; margin-bottom: 5px;"></div>
                <span style="font-size: 8pt; font-weight: bold; text-transform: uppercase; color: #4b5563;">Control de Calidad</span>
            </div>
        </div>

        <!-- Caja de Verificación (Checklist) -->
        <div style="border: 1.5px solid #1e3a8a; padding: 12px; border-radius: 6px; background: #f8fafc; min-width: 250px;">
            <div style="display: flex; align-items: center; margin-bottom: 10px;">
                <div style="width: 16px; height: 16px; border: 2px solid #1e3a8a; border-radius: 3px; margin-right: 10px; background: #fff;"></div>
                <span style="font-size: 9.5pt; font-weight: bold; color: #1e3a8a;">Revisión de Perfil a cortar</span>
            </div>
            <div style="display: flex; align-items: center;">
                <div style="width: 16px; height: 16px; border: 2px solid #1e3a8a; border-radius: 3px; margin-right: 10px; background: #fff;"></div>
                <span style="font-size: 9.5pt; font-weight: bold; color: #1e3a8a;">Revisión de Perfiles cortados</span>
            </div>
        </div>
    </div>

    <div class="footer">
        Generado automáticamente por {{ config('app.name') }} - {{ date('d/m/Y H:i:s') }}
    </div>

    <script>
        window.onload = function() {
            // setTimeout(function() { window.print(); }, 1000);
        };
    </script>
</body>
</html>
