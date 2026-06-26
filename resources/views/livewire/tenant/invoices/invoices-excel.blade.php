<table>
    <thead>
        <tr>
            <th colspan="{{ 14 + count($methodPayments) }}" style="font-weight: bold; text-align: center; font-size: 13pt; height: 30px;">
                INFORME CIERRE DE CAJA FACTURAS REALIZADAS {{ $dateTitle }}
            </th>
        </tr>
        <tr>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">#</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Cliente</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Cotizacion</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">OP</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"># factura</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Fecha factura</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Recaudo</th>
            @foreach($methodPayments as $method)
                <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">{{ strtoupper($method->name) }}</th>
            @endforeach
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Subtotal</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Iva</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Rtefte</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Rteica</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Total a pagar</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Total pagado</th>
            <th style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;">Obs Facturación</th>
        </tr>
    </thead>
    <tbody>
        @php $counter = 0; @endphp
        @foreach($invoices as $invoice)
            @php
                $counter++;
                $invoicePayments = $invoice->payments ?? collect();
                
                // Retenciones
                $rtefte = (float)($invoice->retentionFuente ?? 0);
                $rteica = (float)($invoice->retentionIca ?? 0);

                // Totales
                $totalAPagar = (float)($invoice->total_con_impuestos - $rtefte - $rteica - ($invoice->retentionIva ?? 0));

                // Si no hay pagos locales registrados en vnt_invoice_payments, buscar el método de pago original en la remisión
                $remissionId = DB::connection('tenant')->table('vnt_invoicesXsales')->where('invoiceId', $invoice->id)->value('remissionId');
                if ($invoicePayments->isEmpty()) {
                    if ($remissionId) {
                        $remission = DB::connection('tenant')->table('inv_remissions')->where('id', $remissionId)->first();
                        if ($remission) {
                            $paymentDetails = $remission->payment_details;
                            if (is_string($paymentDetails)) {
                                $paymentDetails = json_decode($paymentDetails, true);
                            }
                            if (is_array($paymentDetails) && count($paymentDetails) > 0) {
                                $mappedPayments = [];
                                foreach ($paymentDetails as $pDetail) {
                                    $mappedPayments[] = (object)[
                                        'methodPaymentId' => $pDetail['method_payment_id'],
                                        'value' => (float)($pDetail['value'] ?? 0)
                                    ];
                                }
                                $invoicePayments = collect($mappedPayments);
                            } elseif ($remission->methodPaymentId) {
                                $invoicePayments = collect([
                                    (object)[
                                        'methodPaymentId' => $remission->methodPaymentId,
                                        'value' => $totalAPagar
                                    ]
                                ]);
                            }
                        }
                    }
                }

                // Obtener observación de facturación de vnt_observations (buscando primero por la Factura, luego Remisión, luego Cotización)
                $obsFacturacion = DB::connection('tenant')->table('vnt_observations')
                    ->where('reference_id', $invoice->id)
                    ->where('reference_type', 'invoice')
                    ->where('observation_type', 'invoice_observation')
                    ->value('observation') ?? '';

                if (empty($obsFacturacion) && $remissionId) {
                    $obsFacturacion = DB::connection('tenant')->table('vnt_observations')
                        ->where('reference_id', $remissionId)
                        ->where('reference_type', 'remission')
                        ->where('observation_type', 'invoice_observation')
                        ->value('observation') ?? '';
                }
                if (empty($obsFacturacion) && $invoice->quoteId) {
                    $obsFacturacion = DB::connection('tenant')->table('vnt_observations')
                        ->where('reference_id', $invoice->quoteId)
                        ->where('reference_type', 'quote')
                        ->where('observation_type', 'invoice_observation')
                        ->value('observation') ?? '';
                }

                // Obtener primer pago para la fecha de recaudo
                $firstPayment = $invoicePayments->filter(function($p) {
                    if (!isset($p->methodPayment) && !isset($p->methodPaymentId)) return true;
                    $name = isset($p->methodPayment) ? strtolower($p->methodPayment->name) : '';
                    return !str_contains($name, 'retencion') && !str_contains($name, 'rte') && !str_contains($name, 'ret.');
                })->first();

                $fechaRecaudo = '';
                if ($firstPayment) {
                    $fechaRecaudo = isset($firstPayment->created_at) ? \Carbon\Carbon::parse($firstPayment->created_at)->format('d/m/Y') : ($invoice->updated_at ? $invoice->updated_at->format('d/m/Y') : '');
                } elseif ($invoice->status_payment === 'PAGADO') {
                    $fechaRecaudo = $invoice->updated_at ? $invoice->updated_at->format('d/m/Y') : '';
                }

                // Subtotal e IVA
                $subtotal = (float)$invoice->total_sin_impuestos;
                $iva = (float)($invoice->total_con_impuestos - $invoice->total_sin_impuestos);

                $totalPagado = (float)$invoicePayments->sum('value');
            @endphp
            <tr>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $counter }}</td>
                <td style="border: 1px solid #d3d3d3;">{{ $invoice->client_name }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $invoice->quote_consecutive }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $invoice->remission_consecutive }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $invoice->invoiceNumber }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $invoice->created_at ? $invoice->created_at->format('d/m/Y') : '' }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: center;">{{ $fechaRecaudo }}</td>
                
                @foreach($methodPayments as $method)
                    @php
                        $val = $invoicePayments->where('methodPaymentId', $method->id)->sum('value');
                    @endphp
                    <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($val, 0, ',', '.') }}</td>
                @endforeach

                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($subtotal, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($iva, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($rtefte, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($rteica, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($totalAPagar, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3; text-align: right;">${{ number_format($totalPagado, 0, ',', '.') }}</td>
                <td style="border: 1px solid #d3d3d3;">{{ $obsFacturacion }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <!-- Fila de Totales (Primero) -->
        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: center;">TOTALES</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
            
            <!-- Totales de cada Método de Pago -->
            @foreach($methodPayments as $method)
                @php
                    $totalMethod = 0;
                    foreach($invoices as $invoice) {
                        $rtefte = (float)($invoice->retentionFuente ?? 0);
                        $rteica = (float)($invoice->retentionIca ?? 0);
                        $totalAPagar = (float)($invoice->total_con_impuestos - $rtefte - $rteica - ($invoice->retentionIva ?? 0));
                        $invoicePayments = $invoice->payments ?? collect();
                        $remissionId = DB::connection('tenant')->table('vnt_invoicesXsales')->where('invoiceId', $invoice->id)->value('remissionId');
                        if ($invoicePayments->isEmpty() && $remissionId) {
                            $remission = DB::connection('tenant')->table('inv_remissions')->where('id', $remissionId)->first();
                            if ($remission) {
                                $paymentDetails = $remission->payment_details;
                                if (is_string($paymentDetails)) {
                                    $paymentDetails = json_decode($paymentDetails, true);
                                }
                                if (is_array($paymentDetails) && count($paymentDetails) > 0) {
                                    $mappedPayments = [];
                                    foreach ($paymentDetails as $pDetail) {
                                        $mappedPayments[] = (object)[
                                            'methodPaymentId' => $pDetail['method_payment_id'],
                                            'value' => (float)($pDetail['value'] ?? 0)
                                        ];
                                    }
                                    $invoicePayments = collect($mappedPayments);
                                } elseif ($remission->methodPaymentId) {
                                    $invoicePayments = collect([
                                        (object)[
                                            'methodPaymentId' => $remission->methodPaymentId,
                                            'value' => $totalAPagar
                                        ]
                                    ]);
                                }
                            }
                        }
                        $totalMethod += $invoicePayments->where('methodPaymentId', $method->id)->sum('value');
                    }
                @endphp
                <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalMethod, 0, ',', '.') }}</td>
            @endforeach

            <!-- Totales de Subtotal, Iva, Rtefte, Rteica, Total a Pagar, Total Pagado -->
            @php
                $totalSubtotal = 0;
                $totalIva = 0;
                $totalRtefte = 0;
                $totalRteica = 0;
                $totalPagarSum = 0;
                $totalPagadoSum = 0;

                foreach($invoices as $invoice) {
                    $rtefte = (float)($invoice->retentionFuente ?? 0);
                    $rteica = (float)($invoice->retentionIca ?? 0);
                    $totalSubtotal += (float)$invoice->total_sin_impuestos;
                    $totalIva += (float)($invoice->total_con_impuestos - $invoice->total_sin_impuestos);
                    $totalRtefte += $rtefte;
                    $totalRteica += $rteica;
                    
                    $totalAPagar = (float)($invoice->total_con_impuestos - $rtefte - $rteica - ($invoice->retentionIva ?? 0));
                    $totalPagarSum += $totalAPagar;
                    
                    $invoicePayments = $invoice->payments ?? collect();
                    $remissionId = DB::connection('tenant')->table('vnt_invoicesXsales')->where('invoiceId', $invoice->id)->value('remissionId');
                    if ($invoicePayments->isEmpty() && $remissionId) {
                         $remission = DB::connection('tenant')->table('inv_remissions')->where('id', $remissionId)->first();
                         if ($remission) {
                             $paymentDetails = $remission->payment_details;
                             if (is_string($paymentDetails)) {
                                 $paymentDetails = json_decode($paymentDetails, true);
                             }
                             if (is_array($paymentDetails) && count($paymentDetails) > 0) {
                                 $mappedPayments = [];
                                 foreach ($paymentDetails as $pDetail) {
                                     $mappedPayments[] = (object)[
                                         'methodPaymentId' => $pDetail['method_payment_id'],
                                         'value' => (float)($pDetail['value'] ?? 0)
                                     ];
                                 }
                                 $invoicePayments = collect($mappedPayments);
                             } elseif ($remission->methodPaymentId) {
                                 $invoicePayments = collect([
                                     (object)[
                                         'methodPaymentId' => $remission->methodPaymentId,
                                         'value' => $totalAPagar
                                     ]
                                 ]);
                             }
                         }
                     }
                    $totalPagadoSum += (float)$invoicePayments->sum('value');
                }
            @endphp

            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalSubtotal, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalIva, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalRtefte, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalRteica, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalPagarSum, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2; text-align: right;">${{ number_format($totalPagadoSum, 0, ',', '.') }}</td>
            <td style="font-weight: bold; border: 1px solid #000000; background-color: #f2f2f2;"></td>
        </tr>

        <!-- Cabecera repetida en el Footer (Segundo) -->
        <tr style="font-weight: bold; background-color: #f2f2f2;">
            <th style="border: 1px solid #000000;">#</th>
            <th style="border: 1px solid #000000;">Cliente</th>
            <th style="border: 1px solid #000000;">Cotizacion</th>
            <th style="border: 1px solid #000000;">OP</th>
            <th style="border: 1px solid #000000;"># factura</th>
            <th style="border: 1px solid #000000;">Fecha factura</th>
            <th style="border: 1px solid #000000;">Recaudo</th>
            @foreach($methodPayments as $method)
                <th style="border: 1px solid #000000;">{{ strtoupper($method->name) }}</th>
            @endforeach
            <th style="border: 1px solid #000000;">Subtotal</th>
            <th style="border: 1px solid #000000;">Iva</th>
            <th style="border: 1px solid #000000;">Rtefte</th>
            <th style="border: 1px solid #000000;">Rteica</th>
            <th style="border: 1px solid #000000;">Total a pagar</th>
            <th style="border: 1px solid #000000;">Total pagado</th>
            <th style="border: 1px solid #000000;">Obs Facturación</th>
        </tr>
    </tfoot>
</table>
