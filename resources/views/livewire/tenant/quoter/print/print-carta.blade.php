<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle }} {{ $quote->consecutive }}</title>
    <style>
        @page {
            size: letter;
            margin: 0.4in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Arial', 'Helvetica', sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #2c3e50;
            background: white;
            padding: 0;
        }

        /* Header Layout */
        .header-container {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            padding-bottom: 12px;
        }

        .header-left {
            display: table-cell;
            width: 18%;
            vertical-align: middle;
            text-align: center;
        }

        .header-center {
            display: table-cell;
            width: 58%;
            vertical-align: middle;
            padding: 0 12px;
        }

        .header-right {
            display: table-cell;
            width: 18%;
            text-align: center;
            vertical-align: middle;
        }

        .logo {
            max-width: 90px;
            height: auto;
            margin-bottom: 5px;
        }

        .company-name {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .company-info-text {
            font-size: 8.5pt;
            color: #34495e;
            line-height: 1.4;
        }

        .document-box {
            padding: 10px;
            text-align: center;
            /*background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
            border-radius: 8px;
            color: #ccc;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .document-title {
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
            display: block;
        }

        .document-number {
            font-size: 15pt;
            font-weight: bold;
            display: block;
        }

        /* Customer and Meta Info */
        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
            border: 1px solid #e1e8ee;
            padding: 12px;
            background-color: white;
            border-radius: 6px;
        }

        .section-header {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 10px;
            padding-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #0a3d62;
        }

        .info-row {
            margin-bottom: 6px;
            display: block;
            font-size: 9.5pt;
            line-height: 1.4;
        }

        .label {
            font-weight: bold;
            color: #2c3e50;
            display: inline;
        }

        /* Products Table */
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .products-table th {
            background-color: #ffffff;
            color: #0a3d62;
            border: 1px solid #0a3d62;
            padding: 10px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .products-table td {
            border: 1px solid #e1e8ee;
            padding: 8px 6px;
            vertical-align: middle;
            font-size: 13pt;
        }

        .col-idx { width: 30px; text-align: center; }
        .col-code { width: 105px; text-align: center; }
        .products-table td.col-code { font-family: 'Courier New', monospace; font-size: 20pt; font-weight: 600; }
        .col-desc { text-align: left; min-width: 200px; }
        .col-qty { width: 50px; text-align: center; }
        .col-delivered { width: 60px; text-align: center; }
        .col-pending { width: 60px; text-align: center; }
        .col-price { width: 75px; text-align: right; }
        .col-total { width: 75px; text-align: right; font-weight: bold; }

        .row-even { background-color: #f8f9fa; }
        
        .products-table tr:hover {
            background-color: #f0f4f8;
            transition: background-color 0.2s;
        }

        /* Totals and Footer */
        .footer-section {
            display: table;
            width: 100%;
            margin-top: 15px;
            gap: 20px;
        }

        .notes-container {
            display: table-cell;
            width: 55%;
            vertical-align: top;
            padding-right: 20px;
        }

        .totals-container {
            display: table-cell;
            width: 45%;
            vertical-align: top;
        }

        .notes-box {
            padding: 12px;
            min-height: 80px;
            font-size: 9.5pt;
            background-color: #f8f9fa;
            border-radius: 6px;
            line-height: 1.4;
            border: 1px solid #e1e8ee;
        }

        .notes-section {
            margin-bottom: 8px;
            padding: 4px 0;
            border-bottom: 1px solid #e1e8ee;
        }

        .notes-label {
            font-weight: bold;
            font-size: 9.5pt;
            color: #2c3e50;
            min-width: 80px;
            display: inline-block;
        }

        .total-row {
            display: table;
            width: 100%;
            padding: 8px 0;
            border-bottom: 1px solid #e1e8ee;
            font-size: 10pt;
        }

        .total-label {
            display: table-cell;
            text-align: left;
            font-weight: bold;
            width: 65%;
            color: #2c3e50;
        }

        .total-value {
            display: table-cell;
            text-align: right;
            font-weight: 500;
        }

        .grand-total {
            /*background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);*/
            color: #2c3e50;
            padding: 10px;
            margin-top: 8px;
            border-radius: 6px;
            font-size: 13pt;
            font-weight: bold;
            display: table;
            width: 100%;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .grand-total .total-label,
        .grand-total .total-value {
            display: table-cell;
            color: #2c3e50;
        }

        .grand-total .total-value {
            font-size: 14pt;
            font-weight: bold;
        }

        .signatures {
            margin-top: 30px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 48%;
            text-align: center;
            padding-top: 25px;
        }

        .signature-line {
            border-top: 2px solid #2c3e50;
            margin: 0 15px;
            padding-top: 8px;
            font-weight: bold;
            font-size: 9pt;
            color: #2c3e50;
        }

        .qr-footer {
            text-align: center;
            margin-top: 25px;
            border-top: 2px solid #e1e8ee;
            padding-top: 15px;
            font-size: 9pt;
            background-color: #fafbfc;
            border-radius: 6px;
        }

        .qr-image {
            width: 80px;
            height: 80px;
            margin-bottom: 8px;
            border: 2px solid #e1e8ee;
            border-radius: 8px;
            padding: 5px;
        }

        .provider-logos {
            text-align: center;
            margin-top: 12px;
            font-size: 9pt;
            color: #7f8c8d;
            padding: 8px;
            background-color: white;
            border-radius: 6px;
        }

        .provider-logo {
            max-width: 70px;
            height: auto;
            margin: 0 12px;
            display: inline-block;
            vertical-align: middle;
        }

        /* Images page */
        .images-page {
            page-break-before: always;
        }

        .images-page-title {
            font-size: 14pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 8px;
            border-bottom: 2px solid #000;
        }

        .images-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            justify-content: flex-start;
        }

        .image-card {
            width: 150px;
            text-align: center;
            border: 1px solid #ccc;
            padding: 8px;
            border-radius: 4px;
            height: 190px;
            display: inline-block;
            vertical-align: top;
            margin: 0 10px 15px 0;
        }

        .image-card img {
            max-width: 130px;
            max-height: 120px;
            object-fit: contain;
            display: block;
            margin: 0 auto 6px auto;
        }

        .image-card .product-code {
            font-size: 7.5pt;
            font-weight: bold;
            color: #666;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-card .product-name {
            font-size: 8pt;
            color: #2c3e50;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-top: 4px;
        }

        .image-card .no-image-placeholder {
            width: 130px;
            height: 120px;
            background-color: #f5f5f5;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px auto;
            font-size: 8pt;
            color: #999;
        }

        @media print {
            .no-print { display: none; }
            body { 
                padding: 0;
                margin: 0;
            }
            .document-box {
                background: #2c3e50;
                /*-webkit-print-color-adjust: exact;*/
                /*print-color-adjust: exact;*/
            }
            .grand-total {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .products-table th {
                background-color: #ffffff;
                color: #0a3d62;
                border: 1px solid #0a3d62;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header-container">
        <div class="header-left">
            <img src="{{ asset('images/logofervi.png') }}" class="logo" alt="Logo Fervicom">
            <img src="{{ asset('images/QR-Fervicom.png') }}" alt="QR Code" style="display: block; width: 100px; height: 100px; margin: 6px auto 0;">
        </div>
        <div class="header-center">
            @if($documentTitle === 'REMISIÓN')
                <div class="company-name">{{ $company->businessName ?? 'FERVICOM S.A.S.' }}</div>
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed;">
                    <tr>
                        @if($customer)
                        <td style="text-align: left; font-size: 8.5pt; vertical-align: top; width: 50%;">
                            <div style="font-weight: bold; font-size: 9pt; margin-bottom: 6px; color: #3498db;">DATOS DE ENVÍO</div>
                            <div><strong>{{ $customer->display_name }}</strong></div>
                            <div><strong>NIT/CC:</strong> {{ $customer->company->identification ?? $customer->identification ?? 'N/A' }}</div>
                            <div><strong>Dirección:</strong> {{ ($deliveryBranch ?? null)?->address ?? $customer->warehouse->address ?? 'N/A' }}</div>
                            <div><strong>Ciudad:</strong> {{ ($deliveryBranchCityName ?? null) ?? $customer->warehouse->city->name ?? 'N/A' }}</div>
                            @php $emailVal = trim($customer->email ?? ''); @endphp
                            @if($emailVal && filter_var($emailVal, FILTER_VALIDATE_EMAIL))
                            <div><strong>Email:</strong> {{ $emailVal }}</div>
                            @endif
                            @php $phoneVal = trim(($deliveryBranch ?? null)?->phone ?? $customer->warehouse->phone ?? $customer->phone ?? $customer->personal_phone ?? $customer->business_phone ?? ''); @endphp
                            @if($phoneVal && $phoneVal !== 'N/A' && $phoneVal !== 'na' && $phoneVal !== 'n/a')
                            <div><strong>Teléfono:</strong> {{ $phoneVal }}</div>
                            @endif
                        </td>
                        <td style="text-align: left; font-size: 8.5pt; vertical-align: top; width: 50%; padding-left: 12px;">
                            <div style="font-weight: bold; font-size: 9pt; color: #3498db;">ASESOR COMERCIAL</div>
                            <div>{{ $quote->seller_name }}</div>
                            <div><small>Fecha Registro: {{ $quote->created_at->format('Y-m-d H:i') }}</small></div>
                        </td>
                        @endif
                    </tr>
                </table>
            @else
                <table style="width: 100%; border-collapse: collapse; table-layout: fixed; border: 1.5px solid #3498db;">
                    <tr>
                        <td style="text-align: left; font-size: 8pt; vertical-align: top; width: 50%; border-bottom: 1.5px solid #3498db; padding: 8px; line-height: 1.35;">
                            <div style="font-size: 11pt; font-weight: bold; color: #0a3d62; margin-bottom: 4px;">{{ $company->businessName ?? 'FERVICOM S.A.S.' }}</div>
                            <div><strong>NIT:</strong> {{ $company->identification ?? '900.440.810' }}-{{ $company->checkDigit ?? '' }}</div>
                            <div><strong>Dirección:</strong> {{ $company->billingAddress ?? 'Cra 70B #3A-18 Nueva Marsella' }}</div>
                        </td>
                        <td style="text-align: left; font-size: 8pt; vertical-align: top; width: 50%; border-bottom: 1.5px solid #3498db; padding: 8px; line-height: 1.35;">
                            <div style="font-weight: bold; font-size: 8.5pt; color: #3498db; margin-bottom: 4px;">ASESOR COMERCIAL</div>
                            <div>{{ $quote->seller_name }}</div>
                            @php $sellerPhone = trim($quote->seller_phone ?? ''); @endphp
                            @if($sellerPhone && $sellerPhone !== 'N/A' && $sellerPhone !== 'na' && $sellerPhone !== 'n/a')
                                <div><strong>Tel:</strong> {{ $sellerPhone }}</div>
                            @endif
                        </td>
                    </tr>
                    @if($customer)
                    <tr>
                        <td style="text-align: left; font-size: 8pt; vertical-align: top; width: 50%; padding: 8px; line-height: 1.35;">
                            <div style="font-weight: bold; font-size: 8.5pt; color: #3498db; margin-bottom: 4px;">DATOS DEL CLIENTE</div>
                            <div><strong>{{ $customer->display_name }}</strong></div>
                            <div><strong>NIT/CC:</strong> {{ $customer->company->identification ?? $customer->identification ?? 'N/A' }}</div>
                            <div style="font-size: 7.5pt; color: #7f8c8d; margin-top: 2px;">Fecha Registro: {{ $quote->created_at->format('Y-m-d H:i') }}</div>
                        </td>
                        <td style="text-align: left; font-size: 8pt; vertical-align: top; width: 50%; padding: 8px; line-height: 1.35;">
                            <div style="font-weight: bold; font-size: 8.5pt; color: #3498db; margin-bottom: 4px;">DATOS DE ENVÍO</div>
                            <div><strong>{{ $customer->display_name }}</strong></div>
                            <div><strong>NIT/CC:</strong> {{ $customer->company->identification ?? $customer->identification ?? 'N/A' }}</div>
                            <div><strong>Dirección:</strong> {{ ($deliveryBranch ?? null)->address ?? $customer->warehouse->address ?? 'N/A' }}</div>
                            <div><strong>Ciudad:</strong> {{ ($deliveryBranchCityName ?? null) ?? $customer->warehouse->city->name ?? 'N/A' }}</div>
                            @php $phoneVal = trim(($deliveryBranch ?? null)->phone ?? $customer->warehouse->phone ?? $customer->phone ?? $customer->personal_phone ?? $customer->business_phone ?? ''); @endphp
                            @if($phoneVal && $phoneVal !== 'N/A' && $phoneVal !== 'na' && $phoneVal !== 'n/a')
                            <div><strong>Teléfono:</strong> {{ $phoneVal }}</div>
                            @endif
                        </td>
                    </tr>
                    @endif
                </table>
            @endif
        </div>
        <div class="header-right">
            <div class="document-box">
                <span class="document-title">{{ $documentTitle }}</span>
                <span class="document-number">{{ $quote->consecutive }}</span>
            </div>
        </div>
    </div>

    <!-- Products Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th class="col-idx">#</th>
                <th class="col-code">CÓDIGO</th>
                <th class="col-qty">CANT.</th>
                <th class="col-desc">DESCRIPCIÓN</th>
                {{-- <th class="col-delivered">ENTREGADO</th>
                <th class="col-pending">PENDIENTES</th> --}}
                @if(!isset($showValues) || $showValues)
                <th class="col-price">V.UNIT.</th>
                <th class="col-discount" style="font-size: 8pt; line-height: 1.1;">DESCUENTO<br>APLICADO</th>
                <th class="col-total">SUBTOTAL</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @php
                $sortedDetalles = $quote->detalles->sortBy(function ($detalle) {
                    $sku = strtolower($detalle->item->sku ?? '');
                    $name = strtolower($detalle->item->name ?? $detalle->item->display_name ?? '');
                    $type = strtolower($detalle->item->type ?? '');

                    // Prioridades: 1 = Productos, 2 = Cortes/Servicios, 3 = Fletes
                    if (str_contains($sku, 'flete') || str_contains($name, 'flete')) {
                        return 3;
                    }
                    if (str_contains($sku, 'corte') || str_contains($name, 'corte') || str_contains($type, 'servicio')) {
                        return 2;
                    }
                    return 1;
                })->values();
            @endphp
            @foreach($sortedDetalles as $index => $detalle)
                <tr class="{{ $index % 2 == 0 ? '' : 'row-even' }}">
                    <td class="col-idx">{{ $index + 1 }}</td>
                    <td class="col-code">
                        @if((!isset($showValues) || !$showValues) && $detalle->item && $detalle->item->picking && $detalle->item->picking !== 'N/A')
                            <div style="font-size: 20pt; color: #e74c3c; font-weight: bold; font-family: 'Courier New', monospace; margin-bottom: 3px;">{{ $detalle->item->picking }}</div>
                        @endif
                        <div style="font-size: 20pt; font-weight: bold; font-family: 'Courier New', monospace;">{{ $detalle->item->internal_code ?? $detalle->item->sku ?? 'N/A' }}</div>
                    </td>
                    <td class="col-qty">
                        @php
                            $consumptionUnit = $detalle->item->consumptionUnit ?? null;
                            $unitQty = ($consumptionUnit && $consumptionUnit->quantity > 1) ? $consumptionUnit->quantity : 0;
                        @endphp
                        @if($unitQty > 0 && $documentTitle === 'REMISIÓN')
                            {{ number_format($detalle->quantity, 0) }}({{ number_format($detalle->quantity / $unitQty, 1) }}{{ strtolower(substr($consumptionUnit->description, 0, 2)) }})
                        @else
                            {{ number_format($detalle->quantity, 0) }}
                        @endif
                    <td class="col-desc">
                        {{ $detalle->item->name ?? $detalle->item->display_name }}
                        @if($documentTitle === 'REMISIÓN' && $detalle->item && $detalle->item->accessories && $detalle->item->accessories->count() > 0)
                            <div style="color: red; font-size: 10.5pt; margin-top: 3px;">
                                @foreach($detalle->item->accessories as $accessory)
                                    @php
                                        $insumoName = $accessory->relationLoaded('insumo') 
                                            ? ($accessory->getRelation('insumo')->name ?? $accessory->getRelation('insumo')->display_name ?? 'Insumo #'.$accessory->insumo)
                                            : ($accessory->insumo()->first()->name ?? $accessory->insumo()->first()->display_name ?? 'Insumo #'.$accessory->insumo);
                                    @endphp
                                    <div>
                                        {{ $accessory->observacion ? $accessory->observacion . ' - ' : '' }}{{ $insumoName }} - {{ $accessory->quantity * $detalle->quantity }}
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    {{-- <td class="col-delivered">{{ $detalle->delivered ?? 0 }}</td>
                    <td class="col-pending">{{ ($detalle->quantity) - ($detalle->delivered ?? 0) }}</td> --}}
                    @if(!isset($showValues) || $showValues)
                    @php
                        $valueWithoutTax = $detalle->tax > 0 
                            ? $detalle->value / (1 + $detalle->tax / 100) 
                            : $detalle->value;
                    @endphp
                    <td class="col-price">${{ number_format($valueWithoutTax, 2) }}</td>
                    <td class="col-discount">
                        @if(isset($detalle->price_label) && preg_match('/^\d+%$/', trim($detalle->price_label)))
                            {{ $detalle->price_label }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="col-total">${{ number_format($valueWithoutTax * $detalle->quantity, 2) }}</td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals and Footer -->
    <div class="footer-section">
        <div class="notes-container">
            <div class="section-header">OBSERVACIONES</div>
            <div class="notes-box">
                @if($quote->observations)
                    <div class="notes-section">
                        {!! nl2br(e($quote->observations)) !!}
                    </div>
                @endif
                
                <div class="notes-section">
                    <span class="notes-label">Peso:</span> {{ $totalWeight ? number_format($totalWeight / 1000, 2) . ' kg' : 'N/A' }}
                </div>
                
                {{-- <div class="notes-section">
                    <span class="notes-label">Entrega:</span> Mensajero - Edificio LUX
                </div> --}}
                
                {{-- @if ($quote->status === 'REMISIÓN')
                    <div class="notes-section">
                        <span class="notes-label">Forma de pago:</span> 
                        {{ $quote->method_payment_name }}
                    </div> 
                @endif --}}
                <div class="notes-section">
                    <span class="notes-label">Forma de pago:</span> 
                    @if(isset($quote->methodPayment))
                        {{ $quote->methodPayment->name ?? 'primer if'}}
                    @elseif($quote->status === 'REMISIÓN' && isset($quote->method_payment_name))
                        {{ $quote->method_payment_name ?? 'segundo if' }}
                    @endif
                </div>
                <div class="notes-section">
                    <span class="notes-label">Entrega:</span>
                    {{ $delivery_type ?? '' }}
                </div>
                <div class="notes-section">
                    <span class="notes-label">Obs. Pedido:</span> {{ $quote->obs ?? 'na' }}
                </div>

            </div>

            @if($documentTitle === 'REMISIÓN')
                @php
                    $paymentName = strtolower($quote->methodPayment->name ?? $quote->method_payment_name ?? '');
                @endphp
                @if(str_contains($paymentName, 'contra entrega'))
                <div style="margin-top: 14px; text-align: center; border: 3px solid #e74c3c; border-radius: 6px; padding: 10px 16px; background-color: #fff5f5;">
                    <span style="font-size: 18pt; font-weight: bold; color: #e74c3c; letter-spacing: 1px; text-transform: uppercase;">
                        &#9888; PAGAN CONTRA ENTREGA
                    </span>
                </div>
                @endif
            @endif

            {{-- <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line">RECIBIDO POR EL CLIENTE</div>
                    <div style="font-size: 7.5pt; margin-top: 5px;">Firma y sello</div>
                </div>
                <div class="signature-box">
                    <div class="signature-line">AUTORIZADO POR FERVICOM</div>
                    <div style="font-size: 7.5pt; margin-top: 5px;">Firma autorizada</div>
                </div>
            </div> --}}
        </div>

        @if(isset($assignedCampaign) && $assignedCampaign)
        <div style="margin-top: 12px; border: 2px solid #2c3e50; border-radius: 6px; padding: 10px 14px; background-color: #f8f9fa;">
            <div style="font-weight: bold; font-size: 10pt; color: #2c3e50; margin-bottom: 6px; border-bottom: 1px solid #dee2e6; padding-bottom: 4px;">
                🎁 Entrega de regalos:
            </div>
            <div style="font-size: 9pt; line-height: 1.6;">
                <div><strong>Nombre:</strong> {{ $assignedCampaign->name }}</div>
                @if($assignedCampaign->description)
                <div><strong>Descripción:</strong> {{ $assignedCampaign->description }}</div>
                @endif
                <div><strong>Cant:</strong> {{ $assignedCampaign->max_per_order ?? 1 }}</div>
            </div>
        </div>
        @endif

        @if(!isset($showValues) || $showValues)
        <div class="totals-container">
            @php
                // $d->value ya incluye IVA — extraemos la base sin IVA para mostrar el desglose correcto
                $totalConIva    = $quote->detalles->sum(fn($d) => $d->value * $d->quantity);
                $subtotalGlobal = $quote->detalles->sum(fn($d) =>
                    $d->tax > 0
                        ? round(($d->value / (1 + $d->tax / 100)) * $d->quantity, 2)
                        : $d->value * $d->quantity
                );
                $ivaGlobal  = $totalConIva - $subtotalGlobal;
                $flete      = $quote->flete ?? 0;
                $totalFinal = $totalConIva + $flete;
            @endphp

            <div class="total-row">
                <div class="total-label">SUBTOTAL:</div>
                <div class="total-value">${{ number_format($subtotalGlobal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">+ IVA:</div>
                <div class="total-value">${{ number_format($ivaGlobal, 2) }}</div>
            </div>
            <div class="total-row">
                <div class="total-label">+ FLETE:</div>
                <div class="total-value">${{ number_format($flete, 2) }}</div>
            </div>
            {{-- <div class="total-row">
                <div class="total-label">- RETEFUENTE:</div>
                <div class="total-value">${{ number_format($retencion, 2) }}</div>
            </div> --}}
            {{-- <div class="total-row">
                <div class="total-label">- ICA:</div>
                <div class="total-value">${{ number_format($ica, 2) }}</div>
            </div> --}}
            
            <div class="grand-total">
                <div class="total-label">TOTAL A PAGAR:</div>
                <div class="total-value">${{ number_format($totalFinal, 2) }}</div>
            </div>
        </div>
        @endif
    </div>

    <div style="margin-top: 15px; text-align: left; width: 100%;">
        @php
            // Consulta directa proporcionada por el usuario adaptada al ID de la cotización actual
            $discountResult = \Illuminate\Support\Facades\DB::connection('tenant')->selectOne("
                SELECT SUM( (iv.values * REPLACE(vd.price_label, '%', '') / 100) * vd.quantity ) AS total 
                FROM vnt_detail_quotes vd 
                INNER JOIN inv_values iv ON iv.itemId = vd.itemId 
                WHERE vd.quoteId = ? AND iv.label = 'Precio Base'
            ", [$quote->id]);
            
            $totalDiscountAmount = $discountResult->total ?? 0;
            
            // Obtener etiquetas de descuento para mostrar los nombres de las listas/etiquetas aplicadas
            $discountLabels = $quote->detalles
                ->pluck('price_label')
                ->filter(function ($label) {
                    if (empty($label)) return false;
                    $l = trim(mb_strtolower($label, 'UTF-8'));
                    return !in_array($l, ['precio regular', 'regular', 'precio base', 'precio', 'lista', 'crédito', 'precio crédito', 'precio remisión', 'precio seleccionado']);
                })
                ->unique()
                ->values();
        @endphp

        @if($totalDiscountAmount > 0 || $discountLabels->count() > 0)
            <div style="padding: 10px; border: 1px dashed #3498db; border-radius: 6px; background-color: #f8f9fa; display: inline-block; min-width: 250px;">
                <div style="font-weight: bold; color: #3498db; font-size: 9pt; margin-bottom: 3px;">DESCUENTO APLICADO:</div>
                @if($totalDiscountAmount > 0)
                    <div style="font-size: 10pt; font-weight: bold; color: #2c3e50;">
                        ${{ number_format($totalDiscountAmount) }}
                    </div>
                @endif
            </div>
        @endif
    </div>

    <!-- QR Footer -->
    <div class="qr-footer" style="border-top: 2px solid #e1e8ee; padding-top: 15px; margin-top: 25px;">
        <div class="provider-logos">
            <span>🔌 Fuentes de poder | 💡 Cintas LED | 📐 Perfiles para cintas | 🎯 Perfiles para pasamanajes | ⚡ Fuentes dimerizables | 🧵 Materiales Pasamanajes</span>
        </div>
    </div>

    <!-- Images Page -->
    @php
        $detallesConImagen = $quote->detalles->filter(fn($d) => $d->item);
    @endphp
    @if($detallesConImagen->count() > 0)
    <div class="images-page">
        <div class="images-page-title">Imágenes de Productos</div>
        <div class="images-grid">
            @foreach($detallesConImagen as $detalle)
                <div class="image-card">
                    @if($detalle->item->internal_code)
                        <div class="product-code">{{ $detalle->item->internal_code }}</div>
                    @endif
                    @if($detalle->item->principalImage)
                        <img src="{{ $detalle->item->getPrincipalThumbnailUrl() }}"
                             alt="{{ $detalle->item->name ?? $detalle->item->display_name }}">
                    @else
                        <div class="no-image-placeholder">Sin imagen</div>
                    @endif
                    <div class="product-name">{{ $detalle->item->name ?? $detalle->item->display_name }}</div>
                </div>
            @endforeach
        </div>
    </div>
    @endif
</body>
</html>