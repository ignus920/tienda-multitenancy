<?php
// Script de prueba de impresión - DISEÑO IMAGEN "NUEVO" (Versión Ultra-Compatible)
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/escpos/autoload.php'; 
use Mike42\Escpos\Printer;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

$impresora = $_GET['printer'] ?? 'Kyocera'; 
$printer = null;
$temp_png = tempnam(sys_get_temp_dir(), 'label') . '.png';

echo "<h1>Generando Rótulo GRÁFICO (v4.0)</h1>";

try {
    // 1. CREAR LA IMAGEN
    echo "<li>Paso 1: Dibujando etiqueta... ";
    $ancho = 400; 
    $alto = 450;
    $img = imagecreatetruecolor($ancho, $alto);
    $blanco = imagecolorallocate($img, 255, 255, 255);
    $negro = imagecolorallocate($img, 0, 0, 0);
    $gris = imagecolorallocate($img, 240, 240, 240);
    
    imagefill($img, 0, 0, $blanco);
    imagerectangle($img, 0, 0, $ancho-1, $alto-1, $negro);

    // Encabezado
    imagestring($img, 5, 150, 20, "FERVICOM SAS", $negro);
    imagestring($img, 3, 130, 45, "NIT: 900.440.810-1", $negro);
    imageline($img, 20, 70, $ancho-20, 70, $negro);

    // QR y OP
    imagefilledrectangle($img, 40, 90, 160, 210, $gris);
    imagestring($img, 3, 85, 145, "QR", $negro);
    
    imagestring($img, 5, 200, 110, "OP #48269", $negro);
    imagestring($img, 5, 230, 150, "1 / 3", $negro);
    
    imageline($img, 20, 230, $ancho-20, 230, $negro);

    // Destinatario
    imagestring($img, 4, 130, 250, "DESTINATARIO:", $negro);
    imagestring($img, 4, 50, 290, "JHON JAIRO GUERRERO ABELLA", $negro);
    imagestring($img, 2, 50, 320, "Cel: 3017410082", $negro);
    imagestring($img, 2, 50, 340, "Direccion: Carrera 14B #118-58", $negro);

    // Guardar imagen temporalmente para evitar problemas de tipos en PHP 8
    imagepng($img, $temp_png);
    imagedestroy($img);
    echo "OK</li>";

    // 2. CONECTAR
    echo "<li>Paso 2: Conectando a <b>$impresora</b>... ";
    if (filter_var($impresora, FILTER_VALIDATE_IP)) {
        $connector = new NetworkPrintConnector($impresora, 9100);
    } else {
        $connector = new WindowsPrintConnector($impresora);
    }
    $printer = new Printer($connector);
    echo "OK</li>";

    // 3. IMPRIMIR
    echo "<li>Paso 3: Procesando imagen e imprimiendo... ";
    $escpos_img = EscposImage::load($temp_png);
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $printer->bitImage($escpos_img);
    $printer->feed(2);
    echo "OK</li>";
    
    // 4. CERRAR
    echo "<li>Paso 4: Finalizando... ";
    $printer->close();
    $printer = null;
    if (file_exists($temp_png)) unlink($temp_png);
    echo "OK</li>";

    echo "<h2 style='color:green'>¡DISEÑO ENVIADO CON ÉXITO!</h2>";
    echo "<p>Si la Kyocera no imprime de inmediato, revisa que no tenga trabajos pausados.</p>";

} catch (\Throwable $e) {
    echo "<h2 style='color:red'>ERROR: " . $e->getMessage() . "</h2>";
    echo "Archivo: " . $e->getFile() . " (Línea " . $e->getLine() . ")";
    if ($printer) { try { $printer->close(); } catch(\Throwable $ex) {} }
    if (file_exists($temp_png)) @unlink($temp_png);
}
?>
