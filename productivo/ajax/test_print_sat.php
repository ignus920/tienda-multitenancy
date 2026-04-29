<?php
require_once "../modelos/Orden_p.php";

// =========================================================================
// CONFIGURACIÓN DE MODO
// =========================================================================
// Esta sección usaba Mike42\Escpos, pero la impresora es ZPL.
// Se ha reescrito para generar código ZPL nativo.
// =========================================================================

if (isset($_POST["id_op"])) {
    $id_op = $_POST["id_op"];
    $cant_cajas = $_POST["cant_cajas"];
    $nombre_impresora = isset($_POST["impresora"]) ? $_POST["impresora"] : "SAT460"; 
    // Asegurarse de quitar espacios o caracteres extraños del nombre
    $nombre_impresora = trim($nombre_impresora);

    // Si el nombre es 'Zebra' (default del JS), forzamos SAT460 si es lo que se requiere
    if($nombre_impresora == 'Zebra') {
        $nombre_impresora = 'SAT460';
    }

    $orden = new Orden_p();
    $rspta = $orden->mostrarOrden($id_op);

    // Traer departamento
    $data_departamento = file_get_contents("../ticsia/scripts/colombia.min.json");
    $colombia = json_decode($data_departamento, true);
    $departamento = isset($colombia[$rspta['deptoe']]) ? $colombia[$rspta['deptoe']]['departamento'] : '';

    $cliente = substr($rspta['cliente'], 0, 30); // Truncar para que no rompa formato
    $contacto = $rspta['contacto'];
    $telefono = $rspta['telefonoe'];
    $nit = $rspta['num_idente'];
    $direccion = substr($rspta['direccione'], 0, 40);
    $ciudad_depto = $rspta['ciudade'] . " - " . $departamento;

    try {
        // Abrir conexión a la impresora
        $fp = false;

        // 1. Si es una IP válida, conectar por Socket (Puerto 9100)
        if (filter_var($nombre_impresora, FILTER_VALIDATE_IP)) {
            $fp = fsockopen($nombre_impresora, 9100, $errno, $errstr, 5);
            if (!$fp) {
                echo "Error de conexión IP: $errstr ($errno)<br>";
            }
        } 
        // 2. Si no es IP, intentar como recurso compartido de Windows (SMB)
        else {
            // Intenta abrir el recurso compartido localmente
            $fp = fopen("\\\\localhost\\$nombre_impresora", "w");
            if (!$fp) {
                // Fallback: intentar abrir directo por nombre
                $fp = fopen($nombre_impresora, "w");
            }
        }

        if ($fp) {
            for ($i = 1; $i <= $cant_cajas; $i++) {
                // =============================================================
                // CONFIGURACIÓN DE MARGEN IZQUIERDO (OFFSET)
                // Ajustar este valor si sale muy a la izquierda o derecha
                // =============================================================
                $mx = 50; // Margen X (50 dots aprox 6mm)
                // =============================================================

                // INICIO FORMATO ZPL
                $zpl = "^XA\n";
                $zpl .= "^CI28\n"; // Soporte UTF-8 (intentar)
                
                // --- ENCABEZADO ---
                // Fervicom
                $zpl .= "^FO" . (0 + $mx) . ",20^ACN,18,10^FDFERVICOM^FS\n"; 
                // www.fervicom.com
                $zpl .= "^FO" . (0 + $mx) . ",45^A0N,20,20^FDwww.fervicom.com^FS\n";
                // Nit y Ciudad
                $zpl .= "^FO" . (0 + $mx) . ",70^A0N,20,20^FDFervicom SAS Nit: 900.440.810-1^FS\n";
                $zpl .= "^FO" . (0 + $mx) . ",95^A0N,20,20^FDBogota-Colombia^FS\n";
                
                // Línea separadora
                $zpl .= "^FO" . (0 + $mx) . ",120^GB600,0,3^FS\n";

                // --- QR CODE Y DATOS OP ---
                // QR Code a la izquierda
                $qr_content = 'OPX' . $id_op;
                // QR posición X = 20 original + margen
                $zpl .= "^FO" . (20 + $mx) . ",140^BQN,2,5^FDQA,$qr_content^FS\n";

                // Datos OP a la derecha del QR
                // Posición X = 200 original + margen
                $zpl .= "^FO" . (200 + $mx) . ",160^A0N,50,50^FDOP#$id_op^FS\n";
                $zpl .= "^FO" . (200 + $mx) . ",220^A0N,40,40^FD$i / $cant_cajas^FS\n";

                // Línea separadora
                $zpl .= "^FO" . (0 + $mx) . ",300^GB600,0,3^FS\n";

                // --- DESTINATARIO ---
                $zpl .= "^FO" . (0 + $mx) . ",320^A0N,25,25^FDDESTINATARIO:^FS\n";
                $zpl .= "^FO" . (0 + $mx) . ",350^A0N,25,25^FD$cliente^FS\n";
                
                $y_pos = 380;
                if ($contacto != "" && $contacto != $cliente) {
                     $zpl .= "^FO" . (0 + $mx) . ",$y_pos^A0N,25,25^FD$contacto^FS\n";
                     $y_pos += 30;
                }
                
                $zpl .= "^FO" . (0 + $mx) . ",$y_pos^A0N,25,25^FDTEL: $telefono^FS\n";
                $y_pos += 30;
                $zpl .= "^FO" . (0 + $mx) . ",$y_pos^A0N,25,25^FDNIT: $nit^FS\n";
                $y_pos += 30;
                $zpl .= "^FO" . (0 + $mx) . ",$y_pos^A0N,25,25^FD$direccion^FS\n";
                $y_pos += 30;
                $zpl .= "^FO" . (0 + $mx) . ",$y_pos^A0N,25,25^FD$ciudad_depto^FS\n";

                $zpl .= "^XZ\n";
                
                fwrite($fp, $zpl);
            }
            
            fclose($fp);
            echo "Impresión exitosa";
        } else {
            throw new Exception("No se pudo conectar a la impresora: $nombre_impresora");
        }

    } catch (Exception $e) {
        echo "Error de impresión: " . $e->getMessage();
    }
} else {
    echo "No hay datos para imprimir";
}

