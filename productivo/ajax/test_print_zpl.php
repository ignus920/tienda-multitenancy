<?php
// Prueba de impresión ZPL directa
// La impresora tiene el driver "ZPL" por lo que probablemente no entienda ESC/POS

$nombre_impresora = "SAT460"; 
// Si falla, probar con: "\\\\localhost\\SAT460" o "\\\\NombreEquipo\\SAT460"

try {
    // Comando ZPL simple: Inicio (^XA), Posición (^FO), Datos (^FD), Fin (^XZ)
    $zpl = "^XA\n";
    $zpl .= "^FO50,50^ADN,36,20^FDPRUEBA DE IMPRESION ZPL^FS\n";
    $zpl .= "^FO50,100^ADN,18,10^FDSi puedes leer esto, ZPL funciona^FS\n";
    $zpl .= "^XZ";

    // Intentar abrir la impresora como archivo local
    // En Windows, escribir al recurso compartido funciona como escribir a un archivo
    $fp = fopen("\\\\localhost\\$nombre_impresora", "w");
    
    if (!$fp) {
        // Intentar sin localhost explícito si es local
        $fp = fopen($nombre_impresora, "w");
    }

    if ($fp) {
        fwrite($fp, $zpl);
        fclose($fp);
        echo "Exito! Enviado codigo ZPL a $nombre_impresora. <br>Revise si salio la etiqueta.";
    } else {
        echo "Error: No se pudo conectar a la impresora $nombre_impresora.";
        echo "<br>Asegurese de que esta compartida correctamente.";
    }

} catch (Exception $e) {
    echo "Excepcion: " . $e->getMessage();
}
?>
