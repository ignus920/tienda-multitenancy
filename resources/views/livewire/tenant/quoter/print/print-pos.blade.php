<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle }} {{ $quote->consecutive }}</title>
    <style>
        @page {
            size: 80mm 100%; /* Ancho fijo 80mm, alto automático */
            margin: 2mm; /* Márgenes mínimos */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 8pt;
            line-height: 1.1;
            color: #000;
            background: white;
            width: 76mm; /* 80mm - 4mm de margen */
            margin: 0 auto;
        }

        .center {
            text-align: center;
        }

        .left {
            text-align: left;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .large {
            font-size: 10pt;
        }

        .medium {
            font-size: 9pt;
        }

        .small {
            font-size: 7pt;
        }

        .separator {
            border-bottom: 1px dashed #000;
            margin: 3mm 0;
        }

        .double-separator {
            border-bottom: 2px solid #000;
            margin: 2mm 0;
        }

        .header {
            text-align: center;
            margin-bottom: 3mm;
        }

        .company-name {
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .company-info {
            font-size: 7pt;
            line-height: 1.2;
            margin-bottom: 2mm;
        }

        .quote-header {
            text-align: center;
            margin: 2mm 0;
        }

        .quote-number {
            font-size: 10pt;
            font-weight: bold;
        }

        .customer-section {
            margin: 2mm 0;
            font-size: 7pt;
        }

        .customer-line {
            margin-bottom: 1mm;
            word-wrap: break-word;
        }

        .products-section {
            margin: 2mm 0;
        }

        .product-item {
            margin-bottom: 2mm;
            border-bottom: 1px dotted #ccc;
            padding-bottom: 1mm;
        }

        .product-code {
            font-size: 14pt;
            color: #666;
        }

        .product-name {
            font-size: 11pt;
            font-weight: bold;
            margin: 1mm 0;
            word-wrap: break-word;
        }

        .product-details {
            display: flex;
            justify-content: space-between;
            font-size: 7pt;
        }

        .quantity-price {
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            margin-top: 1mm;
        }

        .totals-section {
            margin-top: 3mm;
            font-size: 8pt;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            padding: 0.5mm 0;
        }

        .final-total {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 9pt;
            margin-top: 2mm;
            padding: 1mm 0;
        }

        .observations-section {
            margin: 3mm 0;
            font-size: 7pt;
        }

        .observations-title {
            font-weight: bold;
            margin-bottom: 1mm;
        }

        .observations-text {
            word-wrap: break-word;
            line-height: 1.3;
        }

        .footer {
            margin-top: 5mm;
            text-align: center;
            font-size: 6pt;
            line-height: 1.2;
        }

        .contact-info {
            margin: 2mm 0;
        }

        .thank-you {
            margin-top: 3mm;
            font-weight: bold;
        }

        .qr-section {
            text-align: center;
            margin: 3mm 0;
        }

        .qr-placeholder {
            width: 30mm;
            height: 30mm;
            border: 1px solid #000;
            margin: 2mm auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 6pt;
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .no-print {
                display: none;
            }
        }

        /* Utility classes para espaciado */
        .mb-1 { margin-bottom: 1mm; }
        .mb-2 { margin-bottom: 2mm; }
        .mt-1 { margin-top: 1mm; }
        .mt-2 { margin-top: 2mm; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">{{ Str::limit($company->businessName ?? $company->firstName . ' ' . $company->lastName, 30) }}</div>
        <div class="company-info">
            @if($company->businessName)
                NIT: {{ $company->identification }}<br>
            @else
                CC: {{ $company->identification }}<br>
            @endif
            @if($company->phone)
                Tel: {{ $company->phone }}<br>
            @endif
            @if($company->billingEmail)
                {{ Str::limit($company->billingEmail, 25) }}
            @endif
        </div>
    </div>

    <div class="double-separator"></div>

    <!-- Quote Info -->
    <div class="quote-header">
        <div class="bold large">{{ $documentTitle }}</div>
        <div class="quote-number">No. {{ $quote->consecutive }}</div>
        <div class="small">FECHA: {{ $quote->created_at->format('Y-m-d H:i') }}</div>
    </div>

    <div class="separator"></div>

    <!-- Customer Info -->
    <div class="customer-section">
        @if($customer)
            <div class="customer-line bold">Cliente: {{ Str::limit($customer->businessName ?: $customer->firstName . ' ' . $customer->lastName, 35) }}</div>
            <div class="customer-line">{{ $customer->identification }}</div>
            @php $phoneVal = trim($customer->warehouse->phone ?? $customer->phone ?? $customer->personal_phone ?? $customer->business_phone ?? ''); @endphp
            @if($phoneVal && $phoneVal !== 'N/A' && $phoneVal !== 'na' && $phoneVal !== 'n/a')
                <div class="customer-line">Tel: {{ $phoneVal }}</div>
            @endif
            @if($customer->billingEmail)
                <div class="customer-line">{{ Str::limit($customer->billingEmail, 30) }}</div>
            @endif
        @else
            <div class="customer-line bold">Cliente: —</div>
        @endif
    </div>

    <div class="separator"></div>

    <!-- Products -->
    <div class="products-section">
        @php
            $subtotalGlobal = 0;
            $ivaGlobal = 0;
            $totalGeneral = 0;

            $sortedDetalles = $quote->detalles->sortBy([
                // 1. Tipo de item (1 = Producto físico, 2 = Cortes/Servicios, 3 = Fletes)
                function ($detalle) {
                    $sku = strtolower($detalle->item->sku ?? '');
                    $name = strtolower($detalle->item->name ?? $detalle->item->display_name ?? '');
                    $type = strtolower($detalle->item->type ?? '');

                    if (str_contains($sku, 'flete') || str_contains($name, 'flete')) {
                        return 3;
                    }
                    if (str_contains($sku, 'corte') || str_contains($name, 'corte') || str_contains($type, 'servicio')) {
                        return 2;
                    }
                    return 1;
                },
                // 2. Primera letra del picking (prioridad: P -> 1, A -> 2, M -> 3, otras -> 4_letra)
                function ($detalle) {
                    $picking = strtoupper(trim($detalle->item->picking ?? ''));
                    if (empty($picking) || $picking === 'N/A') {
                        return 'ZZZ';
                    }
                    preg_match('/^([A-Z])(\d{2})([A-Z])(\d{2})$/', $picking, $matches);
                    if ($matches) {
                        $char1 = $matches[1];
                        if ($char1 === 'P') return '1';
                        if ($char1 === 'A') return '2';
                        if ($char1 === 'M') return '3';
                        return '4_' . $char1;
                    }
                    return 'ZZZ';
                },
                // 3. Primeros dos dígitos numéricos del picking
                function ($detalle) {
                    $picking = strtoupper(trim($detalle->item->picking ?? ''));
                    preg_match('/^([A-Z])(\d{2})([A-Z])(\d{2})$/', $picking, $matches);
                    return $matches ? intval($matches[2]) : 999;
                },
                // 4. Letra del medio del picking
                function ($detalle) {
                    $picking = strtoupper(trim($detalle->item->picking ?? ''));
                    preg_match('/^([A-Z])(\d{2})([A-Z])(\d{2})$/', $picking, $matches);
                    return $matches ? $matches[3] : 'Z';
                },
                // 5. Últimos dos dígitos numéricos del picking
                function ($detalle) {
                    $picking = strtoupper(trim($detalle->item->picking ?? ''));
                    preg_match('/^([A-Z])(\d{2})([A-Z])(\d{2})$/', $picking, $matches);
                    return $matches ? intval($matches[4]) : 999;
                },
                // 6. Desempate por código interno
                function ($detalle) {
                    return $detalle->item->internal_code ?? $detalle->item->sku ?? '';
                }
            ])->values();
        @endphp

        @foreach($sortedDetalles as $index => $detalle)
            @php
                $valueWithoutTax = $detalle->tax > 0 
                    ? $detalle->value / (1 + $detalle->tax / 100) 
                    : $detalle->value;
                $subtotalItem = $valueWithoutTax * $detalle->quantity;
                $subtotalGlobal += $subtotalItem;
                
                $itemIva = ($detalle->value * $detalle->quantity) - $subtotalItem;
                $ivaGlobal += $itemIva;
                
                $totalGeneral += ($detalle->value * $detalle->quantity);
            @endphp

            <div class="product-item">
                @if($detalle->item?->sku || $detalle->item?->internal_code)
                    <div class="product-code">
                        {{ $detalle->item->internal_code ?? $detalle->item->sku }}
                        @if($detalle->item && $detalle->item->picking && $detalle->item->picking !== 'N/A')
                            <span style="color: #e74c3c; margin-left: 2px;">({{ $detalle->item->picking }})</span>
                        @endif
                    </div>
                @else
                    @if($detalle->item && $detalle->item->picking && $detalle->item->picking !== 'N/A')
                        <div class="product-code" style="color: #e74c3c;">{{ $detalle->item->picking }}</div>
                    @endif
                @endif

                <div class="product-name">
                    {{ Str::limit($detalle->description ?? $detalle->item?->name ?? $detalle->item?->display_name ?? 'Producto no encontrado', 35) }}
                </div>
                @if($documentTitle === 'REMISIÓN' && $detalle->item && $detalle->item->accessories && $detalle->item->accessories->count() > 0)
                    <div style="color: red; font-size: 7pt; margin-top: 1mm;">
                        @foreach($detalle->item->accessories as $accessory)
                            @php
                                $insumoName = $accessory->relationLoaded('insumo') 
                                    ? ($accessory->getRelation('insumo')->name ?? $accessory->getRelation('insumo')->display_name ?? 'Insumo #'.$accessory->insumo)
                                    : ($accessory->insumo()->first()->name ?? $accessory->insumo()->first()->display_name ?? 'Insumo #'.$accessory->insumo);
                            @endphp
                            <div>{{ $accessory->observacion ? $accessory->observacion . ' - ' : '' }}{{ Str::limit($insumoName, 20) }} - {{ $accessory->quantity * $detalle->quantity }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="quantity-price">
                    @if(!isset($showValues) || $showValues)
                        <span>{{ $detalle->quantity }} x ${{ number_format($valueWithoutTax, 0) }}</span>
                        <span class="bold">${{ number_format($subtotalItem, 0) }}</span>
                    @else
                        <span>Cantidad: {{ $detalle->quantity }}</span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <div class="separator"></div>

    <!-- Totals -->
    @if(!isset($showValues) || $showValues)
    <div class="totals-section">
        <div class="total-line">
            <span>Subtotal:</span>
            <span>${{ number_format($subtotalGlobal, 0) }}</span>
        </div>
        <div class="total-line">
            <span>IVA:</span>
            <span>${{ number_format($ivaGlobal, 0) }}</span>
        </div>
        @php
            $flete = $quote->flete ?? 0;
            $totalFinal = $totalGeneral + $flete;
        @endphp
        @if($flete > 0)
        <div class="total-line">
            <span>Flete:</span>
            <span>${{ number_format($flete, 0) }}</span>
        </div>
        @endif
        <div class="total-line final-total">
            <span>TOTAL:</span>
            <span>${{ number_format($totalFinal, 0) }}</span>
        </div>
    </div>
    @endif

    <!-- Observations -->
    @if($quote->observations || isset($giftObservation))
        <div class="separator"></div>
        <div class="observations-section">
            <div class="observations-title">Observaciones:</div>
            <div class="observations-text">
                @if($quote->observations)
                    {!! nl2br(e($quote->observations)) !!}
                @endif
                @if(isset($giftObservation))
                    @if($quote->observations) <br> @endif
                    <div style="font-weight: bold; margin-top: 1mm;">{!! nl2br(e($giftObservation)) !!}</div>
                @endif
            </div>
        </div>
    @endif

    <!-- QR Code Section (optional) -->
    @if($showQR ?? false)
        <div class="separator"></div>
        <div class="qr-section">
            <div class="small">Escanea para catálogo:</div>
            <img src="{{ asset('images/QR-fervicom.png') }}" style="width: 30mm;" alt="QR Fervicom">
        </div>
    @endif

    <div class="separator"></div>

    <!-- Footer -->
    <div class="footer">
        @if($company->billingEmail || $company->phone)
            <div class="contact-info">
                <div class="bold small">CONTACTO:</div>
                @if($company->billingEmail)
                    <div>{{ $company->billingEmail }}</div>
                @endif
                @if($company->phone)
                    <div>{{ $company->phone }}</div>
                @endif
            </div>
        @endif

        <div class="thank-you">
            ¡Gracias por su preferencia!
        </div>

        @if(!isset($showValues) || $showValues)
        <div class="mt-2 small">
            Cotización válida por 15 días
        </div>
        @endif
    </div>

    <div style="margin-top: 10mm;"></div> <!-- Espacio final para corte -->
</body>
</html>