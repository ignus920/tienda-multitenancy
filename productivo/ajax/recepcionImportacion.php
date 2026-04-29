<?php
require_once "../modelos/RecepcionImportacion.php";

if (strlen(session_id()) < 1)
    session_start();

$recepcion = new RecepcionImportacion();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$id_envio = isset($_POST["id_envio"]) ? limpiarCadena($_POST["id_envio"]) : "";
$id_picking = isset($_POST["id_picking"]) ? limpiarCadena($_POST["id_picking"]) : "";
$id_importacion = isset($_POST["id_importacion"]) ? limpiarCadena($_POST["id_importacion"]) : "";
$cant_recibida = isset($_POST["cant_recibida"]) ? limpiarCadena($_POST["cant_recibida"]) : "";
$comentario_recibido = isset($_POST["comentario_recibido"]) ? limpiarCadena($_POST["comentario_recibido"]) : "";
$justificacion = isset($_POST["justificacion"]) ? limpiarCadena($_POST["justificacion"]) : "";
$tipo_novedad = isset($_POST["tipo_novedad"]) ? limpiarCadena($_POST["tipo_novedad"]) : "";
$descripcion_novedad = isset($_POST["descripcion_novedad"]) ? limpiarCadena($_POST["descripcion_novedad"]) : "";
$nuevo_estado = isset($_POST["nuevo_estado"]) ? limpiarCadena($_POST["nuevo_estado"]) : "";

switch ($_GET["op"]) {

    case 'listarEnvios':
        $rspta = $recepcion->listarEnvios();
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $boton_recibir = '<button class="btn btn-primary" onclick="abrirRecepcion(' . $reg->id . ')">
                              <i class="fa fa-truck"></i> Recibir Envío</button>';

            $data[] = array(
                "0" => $reg->consecutivo,
                "1" => $reg->del,
                "2" => date('d/m/Y', strtotime($reg->etd)),
                "3" => $reg->transportadora,
                "4" => '<span class="badge badge-info">' . $reg->packings_count . '</span>',
                // "5" => '<span class="badge badge-success">' . number_format($reg->total_productos) . '</span>',
                // "6" => '$' . number_format($reg->valor_total, 2),
                "5" => $boton_recibir
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

    case 'obtenerDetalleEnvio':
        $rspta = $recepcion->obtenerDetalleEnvio($id_envio);
        echo json_encode($rspta);
        break;

    case 'listarProductosEnvio':
        $rspta = $recepcion->listarProductosEnvio($id_envio);
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $diferencia_clase = '';
            if ($reg->diferencia > 0) {
                $diferencia_clase = 'text-danger';
            } elseif ($reg->diferencia < 0) {
                $diferencia_clase = 'text-success';
            }

            $estado_badge = '';
            switch ($reg->estado) {
                case 7:
                    $estado_badge = '<span class="badge badge-warning">En Tránsito</span>';
                    break;
                case 8:
                    $estado_badge = '<span class="badge badge-success">Recibido</span>';
                    break;
                case 9:
                    $estado_badge = '<span class="badge badge-danger">Retrasado</span>';
                    break;
            }

            $novedades_icon = $reg->novedades ? '<i class="fas fa-exclamation-triangle text-warning" title="Tiene novedades"></i>' : '';

            $acciones = '';
            if ($reg->estado == 7) {
                $acciones = '<button class="btn btn-sm btn-primary" onclick="abrirModalRecepcion(' . $reg->id . ')" title="Recibir">
                            <i class="fa fa-check"></i></button>
                            <button class="btn btn-sm btn-warning ml-1" onclick="abrirModalNovedades(' . $reg->id . ')" title="Novedades">
                            <i class="fa fa-exclamation"></i></button>';
            } else {
                $acciones = '<button class="btn btn-sm btn-info" onclick="verDetallesRecepcion(' . $reg->id . ')" title="Ver Detalles">
                            <i class="fa fa-eye"></i></button>';
            }

            $data[] = array(
                "0" => $reg->numero_packing,
                "1" => $reg->codigo . '<br><small>' . substr($reg->producto_desc, 0, 50) . '...</small>',
                "2" => number_format($reg->cant_enviada),
                "3" => '<span class="' . $diferencia_clase . '">' . number_format($reg->cant_recibida ?: 0) . '</span>',
                "4" => '<span class="' . $diferencia_clase . '">' . $reg->diferencia . '</span>',
                "5" => '$' . number_format($reg->precio, 3),
                "6" => '$' . number_format($reg->valor_producto, 2),
                "7" => $estado_badge . ' ' . $novedades_icon,
                "8" => $acciones
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

    case 'listarProductosPorPacking':
        $rspta = $recepcion->listarProductosPorPacking($id_picking);
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "id" => $reg->id,
                "producto" => $reg->tx_pedido,
                "descripcion" => $reg->producto_desc,
                "cant_enviada" => $reg->cant_enviada,
                "cant_recibida" => $reg->cant_recibida ?: 0,
                "diferencia" => $reg->diferencia,
                "precio" => $reg->precio,
                "valor_total" => $reg->valor_producto,
                "estado" => $reg->estado_nombre
            );
        }
        echo json_encode($data);
        break;

    case 'actualizarCantidadRecibida':
        $rspta = $recepcion->actualizarCantidadRecibida($id_importacion, $cant_recibida, $comentario_recibido, $justificacion);
        echo $rspta ? "Cantidad recibida actualizada correctamente" : "Error al actualizar la cantidad recibida";
        break;

    case 'insertarNovedad':
        $rspta = $recepcion->insertarNovedad($id_importacion, $tipo_novedad, $descripcion_novedad);
        echo $rspta ? "Novedad registrada correctamente" : "Error al registrar la novedad";
        break;

    case 'cambiarEstadoProducto':
        $rspta = $recepcion->cambiarEstadoProducto($id_importacion, $nuevo_estado, $comentario_recibido);
        echo $rspta ? "Estado del producto actualizado correctamente" : "Error al actualizar el estado del producto";
        break;

    case 'obtenerResumenEnvio':
        $rspta = $recepcion->obtenerResumenEnvio($id_envio);
        echo json_encode($rspta);
        break;

    case 'confirmarRecepcionCompleta':
        $rspta = $recepcion->confirmarRecepcionCompleta($id_envio);
        echo $rspta ? "Recepción confirmada completamente" : "Error al confirmar la recepción";
        break;

    case 'obtenerJustificaciones':
        $rspta = $recepcion->obtenerJustificaciones($id_importacion);
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "justificacion" => $reg->justificacion,
                "usuario" => $reg->usuario_nombre,
                "fecha" => date('d/m/Y H:i', strtotime($reg->fecha_reg))
            );
        }
        echo json_encode($data);
        break;

    case 'obtenerNovedades':
        $rspta = $recepcion->obtenerNovedades($id_importacion);
        $data = Array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "tipo" => $reg->tipo_novedad,
                "descripcion" => $reg->descripcion,
                "usuario" => $reg->usuario_nombre,
                "fecha" => date('d/m/Y H:i', strtotime($reg->fecha_reg))
            );
        }
        echo json_encode($data);
        break;

    case 'listarEstados':
        $rspta = $recepcion->listarEstados();
        $options = '<option value="">Seleccione un estado</option>';

        while ($reg = $rspta->fetch_object()) {
            $options .= '<option value="' . $reg->id . '">' . $reg->nombre . '</option>';
        }
        echo $options;
        break;

    case 'listarShipsDisponibles':
        $rspta = $recepcion->listarShipsDisponibles();
        $options = '<option value="">Todos los envíos</option>';

        while ($reg = $rspta->fetch_object()) {
            $options .= '<option value="' . $reg->consecutivo . '">Ship - ' . $reg->consecutivo . '</option>';
        }
        echo $options;
        break;

    case 'mostrarProducto':
        $sql = "SELECT ii.*, p.codigo,p.descripcion as producto_desc, pk.numero_packing
                FROM i_importacion ii
                LEFT JOIN c_productos p ON ii.idproducto = p.codigo
                LEFT JOIN i_picking pk ON ii.id_picking = pk.id
                WHERE ii.id = '$id_importacion'";
        $rspta = ejecutarConsultaSimpleFila($sql);
        echo json_encode($rspta);
        break;
}
?>