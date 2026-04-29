<?php



//funcionpara subir adjuntos a un pago 
function subirAdjunto() {
    $imagenes = [];

    foreach ($_FILES as $key => $file) {
        // Detectar si es un adjunto
        if (strpos($key, 'adjunto_') === 0) {
            $id_forma_pago = str_replace('adjunto_', '', $key);

            if ($file['size'] == 0) {
                $imagenes[$id_forma_pago] = 'default.jpg';
                continue;
            }

            // if ($file['size'] > 600000) {
            //     $imagenes[$id_forma_pago] = 'default.jpg';
            //     continue;
            // }

            $ext = pathinfo($file["name"], PATHINFO_EXTENSION);
            $valid_types = ["image/jpg", "image/jpeg", "image/png", "application/pdf"];

            if (in_array($file["type"], $valid_types)) {
                $adjunto = $id_forma_pago . '_' . round(microtime(true)) . '.' . $ext;
                move_uploaded_file($file["tmp_name"], "../files/pagos/" . $adjunto);
                $imagenes[$id_forma_pago] = $adjunto;
            } else {
                $imagenes[$id_forma_pago] = 'default.jpg';
            }
        }
    }

    return $imagenes;
}









function crearNotificacion($titulo, $descripcion, $modulo, $rol_destino = null, $usuario_id = null, $prioridad = 1)
{
   

    // Limpieza básica
    $titulo = limpiarCadena($titulo);
    $descripcion = limpiarCadena($descripcion);
    $modulo = limpiarCadena($modulo);

    $rol_destino_sql = $rol_destino ? "'" . limpiarCadena($rol_destino) . "'" : "NULL";
    $usuario_id_sql  = $usuario_id ? "'" . limpiarCadena($usuario_id) . "'" : "NULL";

    $sql = "INSERT INTO notificaciones (titulo, descripcion, modulo, rol_destino, usuario_id, prioridad)
            VALUES ('$titulo', '$descripcion', '$modulo', $rol_destino_sql, $usuario_id_sql, $prioridad)";

    return ejecutarConsulta($sql);
}







function obtenerRolPorIdTipoSolicitud($idtipo)
{
    $sql = "SELECT nombre FROM s_departamento WHERE id = '$idtipo' AND estado = 1";
    $fila = ejecutarConsultaSimpleFila($sql);

    if ($fila && !empty($fila['nombre'])) {
        return $fila['nombre']; // Ej: 'Mercadeo', 'Laboratorio', etc.
    }

    return null; // Si no tiene departamento asociado
}

