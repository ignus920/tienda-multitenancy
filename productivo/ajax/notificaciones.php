<?php

require_once "../modelos/Notificaciones.php";

if (strlen(session_id()) < 1) 
	session_start();

$notificaciones = new Notificaciones();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";





switch ($_GET["op"]) {


    case 'obtener':
    $usuario_id = $_SESSION["id"];

    // Lista de módulos válidos relacionados con solicitudes
    $modulos_solicitud = ['SuperUsuario', 'Laboratorio', 'Mercadeo', 'Importaciones', 'Secretaria'];

    // Recorremos los permisos activos en $_SESSION
    $permisos_activos = [];
    foreach ($modulos_solicitud as $modulo) {
        if (isset($_SESSION[$modulo]) && $_SESSION[$modulo] == 1) {
            $permisos_activos[] = strtolower($modulo); // ej: 'mercadeo'
        }
    }

    // Llamamos a la función que filtra por módulo y usuario
    $rspta = $notificaciones->obtenerNotificacionesPorPermisos($usuario_id, $permisos_activos);
    echo json_encode($rspta);
    break;


    // case 'obtener':
    //     $usuario_id = $_SESSION["id"]; // o el nombre de tu variable de sesión
    //     $rol = $_SESSION["txroll"]; // asegúrate de tener este dato en sesión
    //     $rspta = $notificaciones->obtenerNotificaciones($usuario_id, $rol);
    //     echo json_encode($rspta);
    // break;






    case 'marcar_mostrada':
        $id = isset($_GET["id"]) ? limpiarCadena($_GET["id"]) : 0;
        $rspta = $notificaciones->marcarNotificacionComoVista($id);
        echo json_encode(["success" => true]);
    break;
}

?>