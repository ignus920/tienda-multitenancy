<?php
session_start();
require_once "../modelos/MovimientoVitrina.php";

$movimientoVitrina = new MovimientoVitrina();





$op = $_POST["op"] ?? $_GET["op"] ?? '';

switch ($op) {
    case 'registrar_movimiento':
        $idproducto = $_POST["idproducto"];
        $tipo = $_POST["tipo_movimiento"];
        $cantidad = floatval($_POST["cantidad"]);
        $observaciones = $_POST["observaciones"] ?? null;
        $usuario_id = $_SESSION["id"] ?? null;

        if ($tipo == 'SALIDA') {
            if (!$movimientoVitrina->validarStock($idproducto, $cantidad)) {
                echo json_encode(array(
                    "success" => false,
                    "message" => "Stock insuficiente en vitrina"
                ));
                break;
            }
        }

        $rspta = $movimientoVitrina->registrarMovimiento($idproducto, $tipo, $cantidad, $usuario_id, $observaciones);

        if ($rspta) {
            echo json_encode(array(
                "success" => true,
                "message" => "Movimiento registrado correctamente",
                "nuevo_saldo" => $movimientoVitrina->obtenerSaldoProducto($idproducto)
            ));
        } else {
            echo json_encode(array(
                "success" => false,
                "message" => "Error al registrar el movimiento"
            ));
        }
        break;

    case 'obtener_saldo':
        $idproducto = $_POST["idproducto"] ?? $_GET["idproducto"];
        $saldo = $movimientoVitrina->obtenerSaldoProducto($idproducto);
        echo json_encode(array("saldo" => $saldo));
        break;

    case 'listar_productos_vitrina':
        $filtro_con_movimientos = isset($_POST['filtro_con_movimientos']) && $_POST['filtro_con_movimientos'] == 'true';
        $rspta = $movimientoVitrina->listarProductosVitrina($filtro_con_movimientos);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $fecha_ultimo = $reg->fecha_ultimo_movimiento ?
                            date("d/m/Y H:i", strtotime($reg->fecha_ultimo_movimiento)) :
                            '<span class="text-muted">Sin movimientos</span>';

            // Solo mostrar botón de salida si hay stock
            $botonSalida = $reg->saldo_vitrina > 0 ?
                          '<button class="btn btn-warning btn-sm" onclick="registrarSalida(' . $reg->id . ')">
                               <i class="fa fa-minus"></i> Salida
                           </button>' : '';

            $data[] = array(
                "0" => $reg->codigo,
                "1" => $reg->descripcion,
                "2" => $fecha_ultimo,
                "3" => number_format($reg->saldo_vitrina, 2),
                "4" => '<button class="btn btn-primary btn-sm" onclick="verHistorial(' . $reg->id . ', \'' . addslashes($reg->codigo) . '\', \'' . addslashes($reg->descripcion) . '\')">
                            <i class="fa fa-eye"></i> Historial
                        </button>
                        <button class="btn btn-success btn-sm" onclick="registrarEntrada(' . $reg->id . ')">
                            <i class="fa fa-plus"></i> Entrada
                        </button>
                        ' . $botonSalida
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

    case 'listar_movimientos':
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = $_POST["fecha_fin"] ?? null;
        $idproducto = $_POST["idproducto"] ?? null;

        $rspta = $movimientoVitrina->listarMovimientos($fecha_inicio, $fecha_fin, $idproducto);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $fecha = date("d/m/Y H:i", strtotime($reg->fecha_movimiento));
            $tipo_class = $reg->tipo_movimiento == 'ENTRADA' ? 'success' : 'danger';
            $tipo_icon = $reg->tipo_movimiento == 'ENTRADA' ? 'plus' : 'minus';

            $data[] = array(
                "0" => $fecha,
                "1" => $reg->codigo_producto,
                "2" => $reg->descripcion_producto,
                "3" => '<span class="badge badge-' . $tipo_class . '">
                            <i class="fa fa-' . $tipo_icon . '"></i> ' . $reg->tipo_movimiento . '
                        </span>',
                "4" => number_format($reg->cantidad, 2),
                "5" => number_format($reg->saldo_anterior, 2),
                "6" => number_format($reg->saldo_nuevo, 2),
                "7" => $reg->observaciones ?? '',
                "8" => $reg->usuario_nombre ?? 'Sistema'
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

    case 'buscar_productos':
        $termino = $_POST["termino"] ?? $_GET["q"];
        $rspta = $movimientoVitrina->buscarProductos($termino);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(
                "id" => $reg->id,
                "codigo" => $reg->codigo,
                "descripcion" => $reg->descripcion,
                "saldo_vitrina" => $reg->saldo_vitrina,
                "text" => $reg->codigo . " - " . $reg->descripcion . " (Stock: " . $reg->saldo_vitrina . ")"
            );
        }

        echo json_encode($data);
        break;

    case 'obtener_resumen':
        $resumen = $movimientoVitrina->obtenerResumenVitrina();
        echo json_encode($resumen);
        break;

    case 'historial_producto':
        $idproducto = $_POST["idproducto"] ?? $_GET["idproducto"];
        $fecha_inicio = $_POST["fecha_inicio"] ?? null;
        $fecha_fin = $_POST["fecha_fin"] ?? null;

        $rspta = $movimientoVitrina->obtenerHistorialProducto($idproducto, $fecha_inicio, $fecha_fin);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $fecha = date("d/m/Y H:i", strtotime($reg->fecha_movimiento));
            $tipo_class = $reg->tipo_movimiento == 'ENTRADA' ? 'success' : 'danger';
            $tipo_icon = $reg->tipo_movimiento == 'ENTRADA' ? 'plus' : 'minus';

            $data[] = array(
                "fecha" => $fecha,
                "tipo" => $reg->tipo_movimiento,
                "tipo_badge" => '<span class="badge badge-' . $tipo_class . '">
                                    <i class="fa fa-' . $tipo_icon . '"></i> ' . $reg->tipo_movimiento . '
                                </span>',
                "cantidad" => number_format($reg->cantidad, 2),
                "saldo_anterior" => number_format($reg->saldo_anterior, 2),
                "saldo_nuevo" => number_format($reg->saldo_nuevo, 2),
                "observaciones" => $reg->observaciones ?? '',
                "usuario" => $reg->usuario_nombre ?? 'Sistema'
            );
        }

        echo json_encode($data);
        break;

    default:
        echo json_encode(array("error" => "Operación no válida"));
        break;
}
?>