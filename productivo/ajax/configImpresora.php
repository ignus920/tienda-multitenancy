<?php 
require_once "../modelos/ConfigImpresora.php";

$config = new ConfigImpresora();

$contexto = isset($_POST["contexto"]) ? limpiarCadena($_POST["contexto"]) : "";
$idusuario = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : "";
$nombre_impresora = isset($_POST["nombre_impresora"]) ? limpiarCadena($_POST["nombre_impresora"]) : "";
$proxy_impresion = isset($_POST["proxy_impresion"]) ? limpiarCadena($_POST["proxy_impresion"]) : "";

switch ($_GET["op"]) {
    case 'guardar':
        if (empty($idusuario)) {
            if (strlen(session_id()) < 1) session_start();
            $idusuario = $_SESSION['id'];
        }
        $rspta = $config->guardar($contexto, $idusuario, $nombre_impresora, $proxy_impresion);
        echo $rspta ? "Configuración guardada correctamente" : "No se pudo guardar la configuración";
        break;

    case 'obtener':
        if (empty($idusuario)) {
            if (strlen(session_id()) < 1) session_start();
            $idusuario = $_SESSION['id'];
        }
        $rspta = $config->obtener($contexto, $idusuario);
        echo json_encode($rspta);
        break;
}
?>
