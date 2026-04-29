<?php
session_start();
require_once "../modelos/ConsultasDepartamentos.php";

$consultas = new ConsultasDepartamentos();

switch ($_GET["op"]) {
    case 'obtenerConsultas':
        $departamento = isset($_POST["departamento"]) ? limpiarCadena($_POST["departamento"]) : '';
        $estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : '';
        $fecha_desde = isset($_POST["fecha_desde"]) ? limpiarCadena($_POST["fecha_desde"]) : '';
        $fecha_hasta = isset($_POST["fecha_hasta"]) ? limpiarCadena($_POST["fecha_hasta"]) : '';

        // Verificar si el usuario es administrador
        $idUsuario = $_SESSION['id'];
        $esAdmin = ($idUsuario == 81 || $idUsuario == 162);
        
        // Si no es admin, filtrar por sus departamentos
        $departamentos_usuario = [];
        if (!$esAdmin && isset($_SESSION['departamentos_ids'])) {
            $departamentos_usuario = $_SESSION['departamentos_ids'];
        }

        $rspta = $consultas->obtenerConsultas($departamento, $estado, $fecha_desde, $fecha_hasta, $departamentos_usuario);
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $badge_estado = '';
            switch ($reg->estado) {
                case 1:
                    $badge_estado = '<span class="badge badge-warning">Registrado</span>';
                    break;
                case 2:
                    $badge_estado = '<span class="badge badge-info">Con Respuesta</span>';
                    break;
                case 3:
                    $badge_estado = '<span class="badge badge-success">Solucionado</span>';
                    break;
                case 4:
                    $badge_estado = '<span class="badge badge-danger">Imposibilidad</span>';
                    break;
                case 5:
                    $badge_estado = '<span class="badge badge-secondary">Archivadas</span>';
                    break;
                case 6:
                    $badge_estado = '<span class="badge badge-primary">Aprobado</span>';
                    break;
                default:
                    $badge_estado = '<span class="badge badge-light">Sin estado</span>';
                    break;
            }

            $badge_respuestas = '';
            if ($reg->total_respuestas == 0) {
                $badge_respuestas = '<span class="badge badge-danger">Sin respuestas</span>';
            } else {
                $badge_respuestas = '<span class="badge badge-success">' . $reg->total_respuestas . ' respuesta(s)</span>';
            }

            $ultima_actividad = '';
            if ($reg->ultima_actividad) {
                $fecha_actividad = new DateTime($reg->ultima_actividad);
                $ultima_actividad = $fecha_actividad->format('d/m/Y H:i');
            } else {
                $ultima_actividad = '<span class="text-muted">Sin actividad</span>';
            }

            $titulo = html_entity_decode($reg->titulo, ENT_QUOTES, 'UTF-8');
                
            $producto='<strong>'.$titulo.'</strong> <br/>'.$reg->producto;

            $fecha_registro = new DateTime($reg->fecha_reg);

            $data[] = array(
                "0" => $reg->id,
                "1" => $fecha_registro->format('d/m/Y H:i'),
                "2" => $reg->nombre_departamento,
                "3" => $producto,
                "4" => $badge_estado,
                "5" => $badge_respuestas,
                "6" => $ultima_actividad,
                "7" => '<button class="btn btn-info btn-sm" title="Ver comentario" onclick="verDetalle(' . $reg->id . ')"><i class="fas fa-eye"></i></button>'
            );
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;

    case 'obtenerDetalle':
        $id_solicitud = isset($_POST["id_solicitud"]) ? limpiarCadena($_POST["id_solicitud"]) : '';
        
        if (empty($id_solicitud)) {
            echo json_encode(array("status" => "error", "message" => "ID de solicitud requerido"));
            break;
        }

        $solicitud = $consultas->obtenerDetalleSolicitud($id_solicitud);
        
        if (!$solicitud) {
            echo json_encode(array("status" => "error", "message" => "Solicitud no encontrada"));
            break;
        }

        $respuestas = $consultas->obtenerRespuestasSolicitud($id_solicitud);
        $info_producto = $consultas->obtenerProductoInfo($solicitud['id_producto']);
        
        $data_respuestas = array();
        while ($resp = $respuestas->fetch_object()) {
            $fecha_respuesta = new DateTime($resp->fecha);
            $data_respuestas[] = array(
                "id" => $resp->id,
                "comentario" => html_entity_decode($resp->comentario, ENT_QUOTES, 'UTF-8'),
                "fecha" => $fecha_respuesta->format('d/m/Y H:i'),
                "usuario" => $resp->nombre_usuario ? $resp->nombre_usuario : 'Usuario desconocido',
                "estado" => $resp->estado_respuesta_texto
            );
        }

        $fecha_registro = new DateTime($solicitud['fecha_reg']);

        echo json_encode(array(
            "status" => "success",
            "solicitud" => array(
                "id" => $solicitud['id'],
            
                "departamento" => $solicitud['nombre_departamento'],
                "estado" => $solicitud['estado_texto'],
                "fecha_registro" => $fecha_registro->format('d/m/Y H:i'),
                "producto_id" => $solicitud['id_producto'],
                "producto_info" => $info_producto ? $info_producto['codigo'] . ' - ' . $info_producto['descripcion'] : 'Producto no encontrado'
            ),
            "respuestas" => $data_respuestas
        ));
        break;

    case 'obtenerEstadisticas':
        $departamento = isset($_POST["departamento"]) ? limpiarCadena($_POST["departamento"]) : '';
        $fecha_desde = isset($_POST["fecha_desde"]) ? limpiarCadena($_POST["fecha_desde"]) : '';
        $fecha_hasta = isset($_POST["fecha_hasta"]) ? limpiarCadena($_POST["fecha_hasta"]) : '';

        // Verificar si el usuario es administrador
        $idUsuario = $_SESSION['id'];
        $esAdmin = ($idUsuario == 81 || $idUsuario == 162);
        
        // Si no es admin, filtrar por sus departamentos
        $departamentos_usuario = [];
        if (!$esAdmin && isset($_SESSION['departamentos_ids'])) {
            $departamentos_usuario = $_SESSION['departamentos_ids'];
        }

        $rspta = $consultas->obtenerEstadisticas($departamento, $fecha_desde, $fecha_hasta, $departamentos_usuario);
        
        $estadisticas = array();
        while ($reg = $rspta->fetch_object()) {
            $estadisticas[] = array(
                "estado" => $reg->estado,
                "estado_texto" => $reg->estado_texto,
                "total" => $reg->total
            );
        }

        echo json_encode(array("status" => "success", "data" => $estadisticas));
        break;

    case 'obtenerSinRespuesta':
        $departamento = isset($_POST["departamento"]) ? limpiarCadena($_POST["departamento"]) : '';
        $fecha_desde = isset($_POST["fecha_desde"]) ? limpiarCadena($_POST["fecha_desde"]) : '';
        $fecha_hasta = isset($_POST["fecha_hasta"]) ? limpiarCadena($_POST["fecha_hasta"]) : '';

        // Verificar si el usuario es administrador
        $idUsuario = $_SESSION['id'];
        $esAdmin = ($idUsuario == 81 || $idUsuario == 162);
        
        // Si no es admin, filtrar por sus departamentos
        $departamentos_usuario = [];
        if (!$esAdmin && isset($_SESSION['departamentos_ids'])) {
            $departamentos_usuario = $_SESSION['departamentos_ids'];
        }

        $rspta = $consultas->obtenerSinRespuesta($departamento, $fecha_desde, $fecha_hasta, $departamentos_usuario);
        
        $sin_respuesta = array();
        while ($reg = $rspta->fetch_object()) {
            $fecha_registro = new DateTime($reg->fecha_reg);
            $sin_respuesta[] = array(
                "id" => $reg->id,
                "titulo" => $reg->titulo,
                "departamento" => $reg->nombre_departamento,
                "fecha_registro" => $fecha_registro->format('d/m/Y H:i'),
                "dias_sin_respuesta" => $reg->dias_sin_respuesta,
                "producto_id" => $reg->id_producto
            );
        }

        echo json_encode(array("status" => "success", "data" => $sin_respuesta));
        break;

    default:
        echo json_encode(array("status" => "error", "message" => "Operación no válida"));
        break;
}
?>