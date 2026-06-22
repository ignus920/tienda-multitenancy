<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Guía de Envío - OP#{{ $consecutive }}</title>
    <style>
        @page {
            size: 100mm 150mm;
            margin: 0;
        }
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 4mm;
            width: 92mm;
            height: 142mm;
            box-sizing: border-box;
            background-color: #ffffff;
            color: #000000;
        }
        .container {
            width: 100%;
            height: 100%;
            border: 1px solid #000000;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
        }
        .divider {
            border-top: 1px solid #000000;
            width: 100%;
            margin: 0;
        }
        /* Header */
        .header {
            text-align: center;
            padding: 3mm 0;
        }
        .header-logo {
            font-size: 24px;
            font-weight: 900;
            color: #0a3d62;
            letter-spacing: 1px;
            margin: 0;
            font-family: 'Arial Black', Gadget, sans-serif;
        }
        .header-logo span {
            color: #38ada9;
        }
        .header-web {
            font-size: 12px;
            color: #0a3d62;
            margin: 1mm 0 0 0;
            font-weight: bold;
        }
        
        /* Middle Section: QR and Sender */
        .middle-section {
            display: flex;
            padding: 3mm;
            box-sizing: border-box;
        }
        .qr-column {
            width: 40%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        .qr-image {
            width: 28mm;
            height: 28mm;
        }
        .qr-label {
            font-size: 8px;
            font-weight: bold;
            margin-top: 1mm;
            text-align: center;
        }
        .sender-column {
            width: 60%;
            padding-left: 3mm;
            font-size: 9px;
            line-height: 1.3;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .sender-title {
            font-weight: bold;
            font-size: 11px;
            margin-bottom: 0.5mm;
        }

        /* Destinatario Section */
        .receiver-section {
            flex-grow: 1;
            padding: 4mm;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .receiver-header {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 2mm;
            letter-spacing: 0.5px;
        }
        .receiver-name {
            font-size: 14px;
            font-weight: 900;
            text-align: center;
            margin-bottom: 1.5mm;
            text-transform: uppercase;
        }
        .receiver-contact {
            font-size: 11px;
            text-align: center;
            margin-bottom: 2mm;
            color: #333333;
        }
        .receiver-info-grid {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 2mm;
            text-align: center;
        }
        .receiver-address {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            margin-top: 1.5mm;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        .receiver-location {
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            text-transform: uppercase;
        }

        /* Footer Section */
        .footer-section {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 3mm;
            box-sizing: border-box;
            background-color: #fafafa;
        }
        .footer-left {
            width: 70%;
            font-size: 8px;
            line-height: 1.3;
            font-weight: bold;
            color: #333333;
        }
        .footer-right {
            width: 30%;
            display: flex;
            justify-content: flex-end;
        }
        .logo-meanwell {
            background-color: #d11b22;
            color: #ffffff;
            padding: 3px 6px;
            font-family: 'Arial Black', sans-serif;
            text-align: center;
            border-radius: 2px;
            display: inline-block;
            box-sizing: border-box;
            border: 1px solid #b0151a;
        }
        .logo-meanwell-mw {
            font-size: 16px;
            font-weight: 900;
            line-height: 1;
        }
        .logo-meanwell-text {
            font-size: 6px;
            font-weight: bold;
            border-top: 1px solid #ffffff;
            margin-top: 1px;
            padding-top: 1px;
            letter-spacing: 0.2px;
        }

        /* Print Button / Auto-Print */
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/logofervi.png') }}" alt="Logo Fervicom" style="height: 10mm; max-width: 90%; object-fit: contain; display: block; margin: 0 auto;">
            <div class="header-web">www.fervicom.com</div>
        </div>

        <div class="divider"></div>

        <!-- Middle Section -->
        <div class="middle-section">
            <div class="qr-column">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $remission->id }}" alt="QR Code" class="qr-image">
                <div class="qr-label">OP#{{ $consecutive }} - ERP{{ $remission->id }}</div>
            </div>
            <div class="sender-column">
                <div class="sender-title">{{ $sender->businessName }}</div>
                <div>Nit: {{ $sender->identification }}</div>
                <div>{{ $sender->billingAddress }}</div>
                <div>{{ $sender->city }}</div>
                <div>Tel: {{ $sender->phone }}</div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Destinatario Section -->
        <div class="receiver-section">
            <div class="receiver-header">DESTINATARIO:</div>
            <div class="receiver-name">{{ $receiver['name'] }}</div>
            @if(!empty($receiver['contact']))
                <div class="receiver-contact">{{ $receiver['contact'] }}</div>
            @endif
            
            <div class="receiver-info-grid">
                @if(!empty($receiver['phone']) && $receiver['phone'] !== 'N/A')
                    <div><strong>Cel:</strong> {{ $receiver['phone'] }}</div>
                @endif
                @if(!empty($receiver['nit']) && $receiver['nit'] !== 'N/A')
                    <div><strong>Nit:</strong> {{ $receiver['nit'] }}</div>
                @endif
                @if(!empty($receiver['email']) && $receiver['email'] !== 'N/A')
                    <div><strong>Email:</strong> {{ $receiver['email'] }}</div>
                @endif
            </div>

            <div class="receiver-address">{{ $receiver['address'] }}</div>
            <div class="receiver-location">{{ $receiver['city'] }} - {{ $receiver['state'] }}</div>
        </div>

        <div class="divider"></div>

        <!-- Footer -->
        <div class="footer-section">
            <div class="footer-left">
                Cintas Led - Módulos Led - Regletas Led<br>
                Fuentes MeanWell<br>
                Perfiles de Aluminio
            </div>
            <div class="footer-right">
                <img src="{{ asset('images/MeanWell.png') }}" alt="MeanWell" style="height: 10mm; max-width: 100%; object-fit: contain;">
            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
            // Cerrar pestaña después de imprimir (opcional, pero útil para flujos rápidos)
            setTimeout(function() {
                window.close();
            }, 1000);
        }
    </script>
</body>
</html>
