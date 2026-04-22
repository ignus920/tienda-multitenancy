<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $documentTitle }} {{ $quote->consecutive }}</title>
    <style>
        @page {
            size: letter;
            margin: 0.5in;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.2;
            color: #000;
            background: white;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .company-info {
            flex: 1;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            margin-bottom: 5px;
            color: #000;
        }

        .company-details {
            font-size: 9pt;
            line-height: 1.3;
        }

        .quote-info {
            text-align: right;
            font-size: 11pt;
        }

        .quote-title {
            font-size: 16pt;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .quote-details {
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
        }

        .customer-info, .quote-meta {
            flex: 1;
            width: 48%;
            margin-right: 20px;
        }

        .quote-meta {
            margin-right: 0;
        }

        .section-title {
            font-weight: bold;
            margin-bottom: 8px;
            color: #333;
            border-bottom: 1px solid #ccc;
            padding-bottom: 2px;
        }

        .info-line {
            margin-bottom: 3px;
        }

        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9pt;
        }

        .products-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: center;
            font-weight: bold;
            font-size: 9pt;
        }

        .products-table td {
            border: 1px solid #000;
            padding: 4px;
            text-align: center;
        }

        .products-table td.description {
            text-align: left;
            max-width: 200px;
            word-wrap: break-word;
        }

        .products-table td.code {
            text-align: center;
            width: 80px;
        }

        .products-table td.unit {
            width: 60px;
        }

        .products-table td.quantity {
            width: 50px;
        }

        .products-table td.price {
            text-align: right;
            width: 70px;
        }

        .products-table td.iva {
            width: 40px;
        }

        .products-table td.subtotal {
            text-align: right;
            width: 80px;
            font-weight: bold;
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
        }

        .image-card img {
            width: 130px;
            height: 130px;
            object-fit: contain;
            display: block;
            margin: 0 auto 6px auto;
        }

        .image-card .product-code {
            font-size: 7pt;
            color: #666;
            margin-bottom: 4px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .image-card .product-name {
            font-size: 8pt;
            font-weight: bold;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            height: calc(8pt * 1.3 * 2);
        }

        .image-card .no-image-placeholder {
            width: 130px;
            height: 130px;
            background-color: #f5f5f5;
            border: 1px dashed #ccc;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 6px auto;
            font-size: 8pt;
            color: #999;
        }

        .totals-section {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }

        .observations {
            flex: 1;
            margin-right: 20px;
        }

        .observations-content {
            border: 1px solid #ccc;
            padding: 8px;
            min-height: 80px;
            font-size: 9pt;
            background-color: #fafafa;
        }

        .totals {
            width: 250px;
        }

        .total-line {
            display: flex;
            justify-content: space-between;
            padding: 4px 0;
            border-bottom: 1px solid #eee;
        }

        .total-line.final {
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            font-weight: bold;
            font-size: 12pt;
            margin-top: 5px;
            padding: 6px 0;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }

        .footer-contact {
            margin-bottom: 5px;
        }

        .qr-section {
            text-align: center;
            margin: 15px 0;
        }

        .qr-code {
            margin: 10px 0;
        }

        .page-break {
            page-break-after: always;
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

        .amount {
            font-weight: bold;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <div class="company-name">{{ $company->businessName ?? $company->firstName . ' ' . $company->lastName }}</div>
            <div class="company-details">
                @if($company->businessName)
                    <div>NIT: {{ $company->identification }}</div>
                @else
                    <div>Cédula: {{ $company->identification }}</div>
                @endif
                @if($company->billingAddress)
                    <div>Dirección: {{ $company->billingAddress }}</div>
                @endif
                @if($company->phone)
                    <div>Teléfono: {{ $company->phone }}</div>
                @endif
                @if($company->billingEmail)
                    <div>Email: {{ $company->billingEmail }}</div>
                @endif
            </div>
        </div>
        <div class="quote-info">
            <div class="quote-title">{{ $documentTitle }}</div>
            <div><strong>No. {{ $quote->consecutive }}</strong></div>
            <div>Página 1 de 1</div>
        </div>
    </div>

    <!-- Quote Details -->
    <div class="quote-details">
        <div class="customer-info">
            <div class="section-title">Señores:</div>
            @if($customer)
                @php
                    $customerBusinessName = trim($customer->company->businessName ?? '');
                    $customerDisplayName = !empty($customerBusinessName) ? $customerBusinessName : ($customer->firstName . ' ' . $customer->lastName);
                    $customerNIT = $customer->company->identification ?? $customer->identification ?? 'N/A';
                @endphp
                <div class="info-line"><strong>{{ $customerDisplayName }}</strong></div>
                <div class="info-line">Atención: {{ $customer->firstName }} {{ $customer->lastName }}</div>
                <div class="info-line">NIT: {{ $customerNIT }}</div>
                @if($customer->warehouse && ($customer->warehouse->address || $customer->warehouse->city))
                    <div class="info-line">
                        Dirección: {{ $customer->warehouse->address ?? '' }}
                        {{ ($customer->warehouse->address && $customer->warehouse->city) ? ' - ' : '' }}
                        {{ $customer->warehouse->city->name ?? '' }}
                    </div>
                @elseif($customer->billingAddress || ($customer->company && $customer->company->billingAddress))
                    <div class="info-line">Dirección: {{ $customer->company->billingAddress ?? $customer->billingAddress }}</div>
                @endif
                @if($customer->phone)
                    <div class="info-line">Teléfono: {{ $customer->phone }}</div>
                @endif
            @else
                <div class="info-line"><strong>Cliente no encontrado</strong></div>
            @endif
        </div>

        <div class="quote-meta">
            <div class="info-line"><strong>Fecha:</strong> {{ $quote->created_at->format('Y-m-d') }}</div>
            <div class="info-line"><strong>Entrega:</strong> {{ $quote->created_at->addDays(3)->format('Y-m-d') }}</div>
            <div class="info-line"><strong>Vendedor:</strong> {{ $quote->seller_name }}</div>
            <div class="info-line"><strong>Forma de Pago:</strong> Contado</div>
        </div>
    </div>

    <!-- Products Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Código</th>
                <th>Unidad</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Valor Unitario</th>
                <th>Descuento</th>
                <th>IVA %</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($quote->detalles as $index => $detalle)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td class="code">{{ $detalle->item ? ($detalle->item->sku ?? 'N/A') : 'N/A' }}</td>
                    <td class="unit">Unidad</td>
                    <td class="description">{{ $detalle->item ? ($detalle->item->name ?? $detalle->item->display_name) : 'Producto no encontrado' }}</td>
                    <td class="quantity">{{ $detalle->quantity }}</td>
                    <td class="price">${{ number_format($detalle->value, 0) }}</td>
                    <td>
                        @if(isset($detalle->price_label) && preg_match('/^\d+%$/', trim($detalle->price_label)))
                            {{ $detalle->price_label }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="iva">0</td>
                    <td class="subtotal">${{ number_format($detalle->value * $detalle->quantity, 0) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Totals Section -->
    <div class="totals-section">
        <div class="observations">
            <div class="section-title">Observaciones:</div>
            <div class="observations-content">
                @if($quote->observations)
                    {!! nl2br(e($quote->observations)) !!}
                    @if(isset($giftObservation))
                        <br><strong style="color: #000;">{!! nl2br(e($giftObservation)) !!}</strong>
                    @endif
                @else
                    <p>{{ $defaultObservations ?? 'Sin observaciones especiales.' }}</p>
                @endif
            </div>
        </div>

        <div class="totals">
            @php
                $subtotal = $quote->detalles->sum(function($detalle) {
                    return $detalle->value * $detalle->quantity;
                });
                $iva = 0;
                $flete = $quote->flete ?? 0;
                $total = $subtotal + $iva + $flete;
            @endphp

            <div class="total-line">
                <span>Vr. Bruto:</span>
                <span class="amount">${{ number_format($subtotal, 0) }}</span>
            </div>
            <div class="total-line">
                <span>Subtotal:</span>
                <span class="amount">${{ number_format($subtotal, 0) }}</span>
            </div>
            <div class="total-line">
                <span>IVA $:</span>
                <span class="amount">${{ number_format($iva, 0) }}</span>
            </div>
            @if($flete > 0)
            <div class="total-line">
                <span>Flete:</span>
                <span class="amount">${{ number_format($flete, 0) }}</span>
            </div>
            @endif
            <!-- Retenciones (solo si existen y superan el tope) -->
            @php
                // Lógica igual a ProductQuoter.php
                $regimeDescription = 'COMMON_REGIME';
                if (isset($customer) && isset($customer->company) && isset($customer->company->regimen)) {
                    $regimeDescription = $customer->company->regimen === 'COMUN' ? 'COMMON_REGIME' : 'SPECIAL_REGIME';
                }
                $fiscalResponsability = 0;
                if (isset($customer) && isset($customer->company) && isset($customer->company->fiscal_responsibility_id)) {
                    $fiscalResponsability = (int)$customer->company->fiscal_responsibility_id;
                } elseif (isset($customer) && isset($customer->fiscal_responsibility_id)) {
                    $fiscalResponsability = (int)$customer->fiscal_responsibility_id;
                }
                $city = '';
                if (isset($customer) && isset($customer->company) && isset($customer->company->city)) {
                    $city = $customer->company->city;
                } elseif (isset($customer) && isset($customer->city)) {
                    $city = $customer->city;
                }
                $subTotal = $subtotal;
            
                // Topes y porcentajes desde config
                $baseFuente = config('facturacion.retentions.base_amounts.fuente', 524000);
                $porcFuente = config('facturacion.retentions.percentages.fuente', 0.025);
                $baseIca = config('facturacion.retentions.base_amounts.ica', 1418800);
                $porcIca = config('facturacion.retentions.percentages.ica', 0.001104);
                $icaCities = config('facturacion.retentions.ica_cities', ['Bogotá, D.C.']);
                $baseIva = config('facturacion.retentions.base_amounts.iva', 300000);
                $porcIva = config('facturacion.retentions.percentages.iva', 0.15);
                $ivaResponsibilities = config('facturacion.retentions.iva_fiscal_responsibilities', [5]);
            
                // Calculo fuente
                $ret_fuente = 0;
                if ($subTotal >= $baseFuente && $regimeDescription && (
                    $regimeDescription === 'COMMON_REGIME' ||
                    ($regimeDescription === 'SPECIAL_REGIME' && $fiscalResponsability === 5)
                )) {
                    $ret_fuente = round($subTotal * $porcFuente, 2);
                }
            
                // Calculo ICA
                $ret_ica = 0;
                if ($subTotal >= $baseIca && in_array($city, $icaCities) && $regimeDescription && (
                    $regimeDescription === 'COMMON_REGIME' || $regimeDescription === 'SPECIAL_REGIME'
                )) {
                    $ret_ica = round($subTotal * $porcIca, 2);
                }
            
                // Calculo IVA
                $ret_iva = 0;
                if ($subTotal >= $baseIva && in_array($fiscalResponsability, $ivaResponsibilities)) {
                    $ret_iva = round($subTotal * $porcIva, 2);
                }
            
                $showRetentions = ($ret_fuente > 0 || $ret_ica > 0 || $ret_iva > 0);
            @endphp
            {{-- @if($showRetentions)
                <div class="total-line">RETENCIONES</div>
                @if($ret_fuente > 0)
                    <span>Ret. Fuente (2.5%):</span>
                    <span class="amount">-${{ number_format($ret_fuente, 0) }}</span>
                @endif
                @if($ret_ica > 0)
                    <span>Ret. ICA (11.04‰):</span>
                    <span class="amount">-${{ number_format($ret_ica, 0) }}</span>
                @endif
                @if($ret_iva > 0)
                    <span>Ret. IVA (15%):</span>
                    <span class="amount">-${{ number_format($ret_iva, 0) }}</span>
                @endif
                <div style="display: flex; justify-content: space-between; font-weight: bold; border-top: 1px solid black; margin-top: 6px; padding-top: 4px;">
                    <span>Total con Retenciones:</span>
                    <span style="color: black;">${{ number_format($total - $ret_fuente - $ret_ica - $ret_iva, 0) }}</span>
                </div>
            @endif --}}
            <div class="total-line final">
                <span>Total:</span>
                <span class="amount">${{ number_format($total, 0) }}</span>
            </div>
        </div>
    </div>


    <!-- Footer -->
    <div class="footer">
        <div class="footer-contact">
            <strong>PARA PEDIDOS:</strong>
            @if($company->billingEmail)
                {{ $company->billingEmail }}
            @endif
            @if($company->phone)
                - {{ $company->phone }}
            @endif
        </div>
        <div>
            <strong>Apreciado cliente, favor confirmar la recepción total de los productos despachados.</strong>
        </div>
    </div>

    @if($showQR ?? false)
    <!-- QR Section -->
    <div class="qr-section">
        <div>Escanea el QR para ver el catálogo</div>
        <div class="qr-code">
            <!-- Aquí iría el QR code si está disponible -->
            <div style="width: 100px; height: 100px; border: 1px solid #000; margin: 0 auto; display: flex; align-items: center; justify-content: center;">
                QR CODE
            </div>
        </div>
    </div>
    @endif

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