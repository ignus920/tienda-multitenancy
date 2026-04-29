<?php
require_once "../modelos/GestionImportaciones.php";

$gestion = new GestionImportaciones();

$op = $_GET['op'] ?? '';
$filtro_busqueda = $_POST['filtro_busqueda'] ?? '';
$filtro_estado = $_POST['filtro_estado'] ?? '';
$id_envio = $_POST['id_envio'] ?? '';
$id_importacion = $_POST['id_importacion'] ?? '';
$cantidad_contada = $_POST['cantidad_contada'] ?? '';
$observaciones = $_POST['observaciones'] ?? '';

switch ($op) {
    case 'listarImportaciones':
        $rspta = $gestion->listarImportaciones($filtro_busqueda, $filtro_estado);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            // Calcular diferencias
            $diferencia_cantidad = $reg->cantidad_recibida - $reg->cantidad_esperada;
            $diferencia_valor = $reg->valor_recibido - $reg->valor_esperado;

            // Determinar clase de estado
            $estado_clase = '';
            switch ($reg->estado_general) {
                case 'En Revisión':
                    $estado_clase = 'status-revision';
                    break;
                case 'Pendiente':
                    $estado_clase = 'status-pendiente';
                    break;
                case 'Completado':
                    $estado_clase = 'status-completado';
                    break;
            }

            $data[] = array(
                'id' => $reg->id,
                'consecutivo' => $reg->consecutivo,
                'del' => $reg->del,
                'etd' => $reg->etd,
                'transportadora' => $reg->transportadora,
                'origen' => $reg->origen,
                'productos_esperados' => $reg->productos_esperados,
                'cantidad_esperada' => $reg->cantidad_esperada,
                'cantidad_recibida' => $reg->cantidad_recibida,
                'valor_esperado' => $reg->valor_esperado,
                'valor_recibido' => $reg->valor_recibido,
                'items_diferentes' => $reg->items_diferentes,
                'diferencia_cantidad' => $diferencia_cantidad,
                'diferencia_valor' => $diferencia_valor,
                'estado_general' => $reg->estado_general,
                'estado_clase' => $estado_clase
            );
        }

        echo json_encode($data);
        break;

    case 'obtenerDetalleImportacion':
        $rspta = $gestion->obtenerDetalleImportacion($id_envio);

        if ($rspta) {
            // Calcular diferencias
            $diferencia_cantidad = $rspta['cantidad_recibida'] - $rspta['cantidad_esperada'];
            $diferencia_valor = $rspta['valor_recibido'] - $rspta['valor_esperado'];

            $rspta['diferencia_cantidad'] = $diferencia_cantidad;
            $rspta['diferencia_valor'] = $diferencia_valor;
        }

        echo json_encode($rspta);
        break;

    case 'listarProductosDetalle':
        $rspta = $gestion->listarProductosDetalle($id_envio);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            // Determinar clase de diferencia
            $diferencia_clase = '';
            if ($reg->diferencia == 0) {
                $diferencia_clase = 'text-success';
            } elseif ($reg->diferencia > 0) {
                $diferencia_clase = 'text-warning';
            } else {
                $diferencia_clase = 'text-danger';
            }

            // Determinar estado badge
            $estado_badge = '';
            switch ($reg->estado) {
                case 7:
                    $estado_badge = '<span class="badge badge-warning">En Tránsito</span>';
                    break;
                case 8:
                    $estado_badge = '<span class="badge badge-success">Recibido</span>';
                    break;
                case 9:
                    $estado_badge = '<span class="badge badge-danger">No Recibido</span>';
                    break;
                default:
                    $estado_badge = '<span class="badge badge-secondary">' . $reg->estado_nombre . '</span>';
            }

            $acciones = '<button class="btn btn-sm btn-primary" onclick="abrirModalConteo(' . $reg->id . ')" title="Contar Producto">
                        <i class="fas fa-calculator"></i></button>';

            $data[] = array(
                "0" => $reg->codigo ?: $reg->idproducto,
                "1" => substr($reg->producto_descripcion ?: 'Sin descripción', 0, 60) . '...',
                "2" => number_format($reg->cant_enviada),
                "3" => number_format($reg->cant_recibida ?: 0),
                "4" => '<span class="' . $diferencia_clase . '">' . $reg->diferencia . '</span>',
                "5" => $estado_badge,
                "6" => $acciones
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

    case 'obtenerProductoParaConteo':
        $rspta = $gestion->obtenerProductoParaConteo($id_importacion);
        echo json_encode($rspta);
        break;

    case 'actualizarConteoProducto':
        $rspta = $gestion->actualizarConteoProducto($id_importacion, $cantidad_contada, $observaciones);
        echo $rspta ? "Conteo actualizado correctamente" : "Error al actualizar el conteo";
        break;

    case 'obtenerProductosEnvio':
        $rspta = $gestion->obtenerProductosEnvio($id_envio);
        $productos = array();

        while ($reg = $rspta->fetch_object()) {
            $productos[] = $reg->id;
        }

        echo json_encode($productos);
        break;

    case 'obtenerEstadisticasEnvio':
        $rspta = $gestion->obtenerEstadisticasEnvio($id_envio);

        if ($rspta) {
            // Calcular diferencias
            $diferencia_cantidad = $rspta['cantidad_recibida'] - $rspta['cantidad_esperada'];
            $diferencia_valor = $rspta['valor_recibido'] - $rspta['valor_esperado'];

            $rspta['diferencia_cantidad'] = $diferencia_cantidad;
            $rspta['diferencia_valor'] = $diferencia_valor;
        }

        echo json_encode($rspta);
        break;
}
?>