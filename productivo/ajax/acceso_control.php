<?php
date_default_timezone_set('America/Bogota'); // O la zona que corresponda
if (strlen(session_id()) < 1) 
	session_start();
require_once "../modelos/AccesoControlModel.php";
$acceso = new AccesoControlModel();

switch ($_GET["op"]) {
    case 'verificar_acceso':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        $resultado = $acceso->verificarAcceso($usuario_id);
        
        echo json_encode([
            'estado' => $resultado['permitido'],
            'mensaje' => $resultado['motivo'],
            'codigo' => $resultado['codigo'],
            'ip_actual' => $acceso->obtenerIPReal()
        ]);
        break;
        
    case 'listar_ips_usuario':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        $ips = $acceso->obtenerIPsUsuario($usuario_id);
        
        echo json_encode([
            'estado' => true,
            'datos' => $ips
        ]);
        break;
        
    case 'agregar_ip':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        $ip = limpiarCadena($_POST['ip']);
        $descripcion = limpiarCadena($_POST['descripcion']);
        $admin_id = $_SESSION['id']; // ID del admin logueado
        
        $resultado = $acceso->agregarIP($usuario_id, $ip, $descripcion, $admin_id);
        
        if ($resultado) {
            echo json_encode([
                'estado' => true,
                'mensaje' => 'IP agregada correctamente'
            ]);
        } else {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Error al agregar IP. Verifique que sea válida y no esté duplicada'
            ]);
        }
        break;
        
    case 'eliminar_ip':
        $ip_id = limpiarCadena($_POST['ip_id']);
        $admin_id = $_SESSION['id'];
        
        $resultado = $acceso->eliminarIP($ip_id, $admin_id);
        
        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado ? 'IP eliminada correctamente' : 'Error al eliminar IP'
        ]);
        break;
        
    // case 'guardar_horarios':
    //     $usuario_id = limpiarCadena($_POST['usuario_id']);
    //     $horarios = json_decode($_POST['horarios'], true);
        
    //     $errores = 0;
    //     foreach ($horarios as $horario) {
    //         if (!empty($horario['hora_inicio']) && !empty($horario['hora_fin'])) {
    //             $resultado = $acceso->guardarHorario(
    //                 $usuario_id, 
    //                 $horario['dia'], 
    //                 $horario['hora_inicio'], 
    //                 $horario['hora_fin']
    //             );
    //             if (!$resultado) $errores++;
    //         }
    //     }
        
    //     echo json_encode([
    //         'estado' => $errores === 0,
    //         'mensaje' => $errores === 0 ? 'Horarios guardados correctamente' : "Se produjeron {$errores} errores"
    //     ]);
    //     break;
        
    case 'listar_log_accesos':
        $filtros = [
            'usuario_id' => limpiarCadena($_POST['usuario_id'] ?? ''),
            'fecha_desde' => limpiarCadena($_POST['fecha_desde'] ?? ''),
            'fecha_hasta' => limpiarCadena($_POST['fecha_hasta'] ?? '')
        ];
        
        $log = $acceso->obtenerLogAccesos($filtros);
        
        echo json_encode([
            'estado' => true,
            'datos' => $log
        ]);
        break;
        
    case 'obtener_ip_actual':
        echo json_encode([
            'estado' => true,
            'ip' => $acceso->obtenerIPReal()
        ]);
        break;









    case 'verificar_acceso_completo':
    $usuario_id = limpiarCadena($_POST['usuario_id']);
    
    if (empty($usuario_id)) {
        echo json_encode([
            'estado' => false,
            'permitido' => false,
            'mensaje' => 'ID de usuario requerido',
            'codigo' => 'INVALID_USER'
        ]);
        break;
    }
    
    try {
        // Verificar que el usuario existe y está activo
        if (!$acceso->usuarioExisteYActivo($usuario_id)) {
            echo json_encode([
                'estado' => false,
                'permitido' => false,
                'mensaje' => 'Usuario inactivo o no encontrado',
                'codigo' => 'USER_INACTIVE'
            ]);
            break;
        }
        
        // Realizar verificación completa de acceso
        $resultado = $acceso->verificarAcceso($usuario_id);
        
        echo json_encode([
            'estado' => true,
            'permitido' => $resultado['permitido'],
            'mensaje' => $resultado['motivo'],
            'codigo' => $resultado['codigo'],
            'ip_actual' => $acceso->obtenerIPReal(),
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
    } catch (Exception $e) {
        error_log("Error en verificar_acceso_completo: " . $e->getMessage());
        
        echo json_encode([
            'estado' => false,
            'permitido' => false,
            'mensaje' => 'Error interno del servidor',
            'codigo' => 'SERVER_ERROR'
        ]);
    }
    break;








    case 'obtener_horarios_usuario':
    $usuario_id = limpiarCadena($_POST['usuario_id']);
    
    if (empty($usuario_id)) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'ID de usuario requerido'
        ]);
        break;
    }
    
    try {
        $horarios = $acceso->obtenerHorariosUsuario($usuario_id);
        
        echo json_encode([
            'estado' => true,
            'mensaje' => 'Horarios obtenidos correctamente',
            'datos' => $horarios
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'estado' => false,
            'mensaje' => 'Error al obtener horarios: ' . $e->getMessage()
        ]);
    }
    break;

    case 'aplicar_horario_estandar':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        
        if (empty($usuario_id)) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'ID de usuario requerido'
            ]);
            break;
        }
        
        $resultado = $acceso->aplicarHorarioLaboralEstandar($usuario_id);
        
        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado ? 
                'Horario laboral estándar aplicado (L-V: 8:00-17:00, S: 8:00-12:00)' : 
                'Error al aplicar horario estándar'
        ]);
        break;

    case 'aplicar_horario_personalizado':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        $configuracion = json_decode($_POST['configuracion'], true);
        
        if (empty($usuario_id)) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'ID de usuario requerido'
            ]);
            break;
        }
        
        $resultado = $acceso->aplicarHorarioPersonalizado($usuario_id, $configuracion);
        
        echo json_encode([
            'estado' => $resultado,
            'mensaje' => $resultado ? 
                'Horario personalizado aplicado correctamente' : 
                'Error al aplicar horario personalizado'
        ]);
        break;

    case 'obtener_configuracion_horario':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        
        if (empty($usuario_id)) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'ID de usuario requerido'
            ]);
            break;
        }
        
        try {
            $configuracion = $acceso->obtenerConfiguracionHorario($usuario_id);
            
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Configuración obtenida correctamente',
                'datos' => $configuracion
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Error al obtener configuración: ' . $e->getMessage()
            ]);
        }
        break;






        /**
     * Listar de horarios 
     */
       case 'listarHorarios':
        $usuario_id = limpiarCadena($_POST['usuario_id']);
        $rspta = $acceso->listarHorarios($usuario_id);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
        
            $data[] = array(
                "0" => $reg->nombre_dia,
                "1" => $reg->hora_inicio,
                "2" => $reg->hora_fin
                // "3" => $reg->activo
                
            );
        }
        $results = array(
            "sEcho" => 1, //Información para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;
}
?>