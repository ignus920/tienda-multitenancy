<?php
session_start();
require_once "../modelos/Arqueos.php";

$arqueos = new Arqueos();



$lista_id = isset($_POST["lista_id"]) ? limpiarCadena($_POST["lista_id"]) : (isset($_GET["lista_id"]) ? limpiarCadena($_GET["lista_id"]) : "");
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$valor_limite = isset($_POST["valor_limite"]) ? limpiarCadena($_POST["valor_limite"]) : "";
$palabra_busqueda = isset($_POST["palabra_busqueda"]) ? limpiarCadena($_POST["palabra_busqueda"]) : "";
$producto_arqueo_id = isset($_POST["producto_arqueo_id"]) ? limpiarCadena($_POST["producto_arqueo_id"]) : (isset($_GET["producto_arqueo_id"]) ? limpiarCadena($_GET["producto_arqueo_id"]) : "");
$cantidad_contada = isset($_POST["cantidad_contada"]) ? limpiarCadena($_POST["cantidad_contada"]) : "";
$observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : "";
$nuevo_estado = isset($_POST["nuevo_estado"]) ? limpiarCadena($_POST["nuevo_estado"]) : "";

$idusuario = isset($_SESSION['id']) ? $_SESSION['id'] : 0;

switch ($_GET["op"]) {

    case 'crearLista':
        if ($idusuario == 0) {
            echo json_encode(array("status" => "error", "message" => "Sesión no válida"));
            break;
        }

        $lista_id = $arqueos->crearLista($descripcion, $valor_limite, $palabra_busqueda, $idusuario);

        if ($lista_id) {
            echo json_encode(array("status" => "success", "message" => "Lista creada exitosamente", "lista_id" => $lista_id));
        } else {
            echo json_encode(array("status" => "error", "message" => "Error al crear la lista"));
        }
        break;

    case 'listarListas':
        $rspta = $arqueos->listarListas();
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            // Obtener conteo más preciso de productos realmente contados (con cantidad válida)
            $sql_conteos = "SELECT COUNT(*) as contados_reales FROM arqueos_productos
                           WHERE lista_id = '{$reg->id}'
                           AND cantidad_contada IS NOT NULL
                           AND cantidad_contada >= 0";
            $conteos_reales = ejecutarConsultaSimpleFila($sql_conteos);
            $productos_contados_reales = $conteos_reales ? $conteos_reales['contados_reales'] : 0;

            $porcentaje_avance = $reg->total_productos > 0 ? round(($productos_contados_reales / $reg->total_productos) * 100, 2) : 0;

            $estado_badge = '';
            switch ($reg->estado) {
                case 'pendiente':
                    $estado_badge = '<span class="badge badge-warning">Pendiente</span>';
                    break;
                case 'contado':
                    $estado_badge = '<span class="badge badge-info">Contado</span>';
                    break;
                case 'ajustado':
                    $estado_badge = '<span class="badge badge-success">Ajustado</span>';
                    break;
                case 'anulado':
                    $estado_badge = '<span class="badge badge-danger">Anulado</span>';
                    break;
            }

            $botones = '';
            if ($reg->estado == 'pendiente' || $reg->estado == 'contado') {
                $botones .= '<button class="btn btn-sm btn-primary mr-1" onclick="abrirListaConteo(' . $reg->id . ')" title="Abrir para conteo">
                    <i class="fas fa-mobile-alt"></i>
                </button>';

                // $botones .= '<button class="btn btn-sm btn-info mr-1" onclick="imprimirLista(' . $reg->id . ')" title="Imprimir">
                //     <i class="fas fa-print"></i>
                // </button>';

                // $botones .= '<button class="btn btn-sm btn-success mr-1" onclick="exportarExcel(' . $reg->id . ')" title="Exportar Excel">
                //     <i class="fas fa-file-excel"></i>
                // </button>';
            }

            if ($reg->estado == 'contado') {
                $botones .= '<button class="btn btn-sm btn-warning mr-1" onclick="cambiarEstado(' . $reg->id . ', \'ajustado\')" title="Marcar como ajustado">
                    <i class="fas fa-check"></i>
                </button>';
            }

            if ($reg->estado != 'ajustado') {
                $botones .= '<button class="btn btn-sm btn-danger" onclick="cambiarEstado(' . $reg->id . ', \'anulado\')" title="Anular">
                    <i class="fas fa-times"></i>
                </button>';
            }else {
                $botones .= '<button class="btn btn-sm btn-primary mr-1" onclick="abrirListaConteo(' . $reg->id . ')" title="Abrir para conteo">
                    <i class="fas fa-mobile-alt"></i>
                </button>';
            }

            $data[] = array(
                "0" => $reg->consecutivo,
                "1" => $reg->descripcion,
                "2" => $reg->palabra_busqueda,
                "3" => number_format($reg->valor_limite, 0),
                "4" => $reg->total_productos,
                "5" => $productos_contados_reales . ' (' . $porcentaje_avance . '%)',
                "6" => $estado_badge,
                "7" => date('d/m/Y H:i', strtotime($reg->fecha_creacion)),
                "8" => $reg->usuario_nombre,
                "9" => $botones
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

    case 'obtenerLista':
        if (empty($lista_id)) {
            echo json_encode(array("error" => "ID de lista requerido"));
            break;
        }

        $rspta = $arqueos->obtenerLista($lista_id);

        if ($rspta) {
            echo json_encode($rspta);
        } else {
            echo json_encode(array("error" => "Lista no encontrada"));
        }
        break;

    case 'listarProductosLista':
        // Obtener el estado de la lista para determinar qué botones mostrar
        $lista_info = $arqueos->obtenerLista($lista_id);
        $estado_lista = $lista_info['estado'] ?? 'pendiente';

        $rspta = $arqueos->listarProductosLista($lista_id);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $diferencia_display = '';
            if ($reg->diferencia !== null) {
                $color = $reg->diferencia > 0 ? 'text-success' : ($reg->diferencia < 0 ? 'text-danger' : 'text-info');
                $signo = $reg->diferencia > 0 ? '+' : '';
                $diferencia_display = '<span class="' . $color . '">' . $signo . $reg->diferencia . '</span>';
            }

            $estado_conteo = $reg->estado_conteo == 'Contado' ?
                '<span class="badge badge-success">Contado</span>' :
                '<span class="badge badge-warning">Pendiente</span>';

            $botones = '';

            // Si la lista está ajustada, no permitir modificar conteos, solo ver
            if ($estado_lista == 'ajustado') {
                if ($reg->estado_conteo == 'Contado') {
                    $botones = '<button class="btn btn-sm btn-secondary" onclick="verConteo(' . $reg->id . ')" title="Ver conteo (solo lectura)">
                        <i class="fas fa-eye"></i>
                    </button>';
                } else {
                    // Productos pendientes en lista ajustada - sin botón
                    $botones = '<span class="text-muted"><small>Lista ajustada</small></span>';
                }
            } else {
                // Lista en estado pendiente o contado - funcionalidad normal
                if ($reg->estado_conteo == 'Pendiente') {
                    $botones = '<button class="btn btn-sm btn-primary" onclick="abrirModalConteo(' . $reg->id . ')" title="Contar">
                        <i class="fas fa-calculator"></i>
                    </button>';
                } else {
                    $botones = '<button class="btn btn-sm btn-info" onclick="verConteo(' . $reg->id . ')" title="Ver conteo">
                        <i class="fas fa-eye"></i>
                    </button>';
                }
            }

            //    "0" => $reg->codigo,
            //     "1" => $reg->usuario_conteo_nombre.'<br>'.$reg->fecha_conteo,
            //     "2" => $reg->descripcion,
            //     // "3" => number_format($alrededor, 0),
            //     "3" => $reg->ubicacion,
            //     // "5" => $reg->cantidad_contada !== null ? number_format($reg->cantidad_contada, 0) : '',
            //     "4" => $diferencia_display,
            //     "5" => $estado_conteo,
            //     "6" => $reg->observaciones,
            //     "7" => $botones
            $existencia_sistema = $reg->existencia_sistema;
            $alrededor = ceil(($existencia_sistema * 1.1) / 10) * 10;

            $data[] = array(
                "0" => $reg->codigo,
                "1" => $reg->usuario_conteo_nombre.'<br>'.$reg->fecha_conteo,
                "2" => $reg->descripcion,
                "3" => $reg->ubicacion,
                "4" => $reg->cantidad_contada !== null ? number_format($reg->cantidad_contada, 0) : '',
                "5" => $estado_conteo,
                "6" => $reg->observaciones,
                "7" => $botones
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

    case 'registrarConteo':
        if ($idusuario == 0) {
            echo json_encode(array("status" => "error", "message" => "Sesión no válida"));
            break;
        }

        $resultado = $arqueos->registrarConteo($producto_arqueo_id, $cantidad_contada, $observaciones, $idusuario);

        if ($resultado) {
            // Verificar si la lista está completa (todos los productos contados con valores válidos)
            $producto = $arqueos->obtenerProductoLista($producto_arqueo_id);
            $lista_completa = $arqueos->verificarListaCompleta($producto['lista_id']);

            $mensaje = "Conteo registrado exitosamente";

            if ($lista_completa) {
                $arqueos->cambiarEstadoLista($producto['lista_id'], 'contado');
                $mensaje = "Conteo registrado exitosamente. ¡Lista completada al 100%!";
            }

            echo json_encode(array("status" => "success", "message" => $mensaje));
        } else {
            echo json_encode(array("status" => "error", "message" => "Error al registrar el conteo"));
        }
        break;

    case 'obtenerProductoConteo':
        if (empty($producto_arqueo_id)) {
            echo json_encode(array("error" => "ID de producto requerido"));
            break;
        }

        $rspta = $arqueos->obtenerProductoLista($producto_arqueo_id);

        if ($rspta) {
            echo json_encode($rspta);
        } else {
            echo json_encode(array("error" => "Producto no encontrado"));
        }
        break;

    case 'obtenerSiguienteProducto':
        $producto_actual = $arqueos->obtenerProductoLista($producto_arqueo_id);
        $siguiente = $arqueos->obtenerSiguienteProducto($producto_actual['lista_id'], $producto_arqueo_id);
        echo json_encode($siguiente);
        break;

    case 'obtenerAnteriorProducto':
        $producto_actual = $arqueos->obtenerProductoLista($producto_arqueo_id);
        $anterior = $arqueos->obtenerAnteriorProducto($producto_actual['lista_id'], $producto_arqueo_id);
        echo json_encode($anterior);
        break;

    case 'verificarUltimoProducto':
        $producto_actual = $arqueos->obtenerProductoLista($producto_arqueo_id);
        $esUltimo = $arqueos->esUltimoProducto($producto_actual['lista_id'], $producto_arqueo_id);
        echo json_encode(array('esUltimo' => $esUltimo));
        break;

    case 'verificarPrimerProducto':
        $producto_actual = $arqueos->obtenerProductoLista($producto_arqueo_id);
        $esPrimero = $arqueos->esPrimerProducto($producto_actual['lista_id'], $producto_arqueo_id);
        echo json_encode(array('esPrimero' => $esPrimero));
        break;

    case 'cambiarEstadoLista':
        // Verificar permisos para cambiar a estado "ajustado"
        if ($nuevo_estado == 'ajustado') {
            // Solo el jefe de inventario puede marcar como ajustado
            // Aquí puedes agregar validación de rol específico si tienes tabla de roles
            if ($idusuario == 0) {
                echo json_encode(array("status" => "error", "message" => "Sin permisos para ajustar inventario"));
                break;
            }
        }

        $resultado = $arqueos->cambiarEstadoLista($lista_id, $nuevo_estado);

        if ($resultado) {
            $mensaje = $nuevo_estado == 'ajustado' ?
                "Lista marcada como ajustada. Las diferencias están registradas pero NO afectan las existencias del sistema." :
                "Estado cambiado exitosamente";
            echo json_encode(array("status" => "success", "message" => $mensaje));
        } else {
            echo json_encode(array("status" => "error", "message" => "Error al cambiar el estado"));
        }
        break;

    case 'exportarExcel':
        $lista = $arqueos->obtenerLista($lista_id);
        $productos = $arqueos->obtenerDatosExportacion($lista_id);

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="Arqueo_' . $lista['consecutivo'] . '_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');

        echo "<table border='1'>";
        echo "<tr><th colspan='8' style='text-align:center; font-weight:bold;'>LISTA DE ARQUEO: " . $lista['consecutivo'] . "</th></tr>";
        echo "<tr><th colspan='8' style='text-align:center;'>Descripción: " . $lista['descripcion'] . "</th></tr>";
        echo "<tr><th colspan='8' style='text-align:center;'>Fecha: " . date('d/m/Y H:i', strtotime($lista['fecha_creacion'])) . "</th></tr>";
        echo "<tr><th></th></tr>";
        echo "<tr>";
        echo "<th>Código</th>";
        echo "<th>Descripción</th>";
        echo "<th>Existencias Sistema</th>";
        echo "<th>Ubicación</th>";
        echo "<th>Cantidad Contada</th>";
        echo "<th>Diferencia</th>";
        echo "<th>Estado</th>";
        echo "<th>Observaciones</th>";
        echo "</tr>";

        while ($reg = $productos->fetch_object()) {
            echo "<tr>";
            echo "<td>" . $reg->codigo . "</td>";
            echo "<td>" . $reg->descripcion . "</td>";
            echo "<td>" . $reg->existencia_sistema . "</td>";
            echo "<td>" . $reg->ubicacion . "</td>";
            echo "<td>" . ($reg->cantidad_contada ?? '') . "</td>";
            echo "<td>" . ($reg->diferencia ?? '') . "</td>";
            echo "<td>" . $reg->estado_conteo . "</td>";
            echo "<td>" . $reg->observaciones . "</td>";
            echo "</tr>";
        }

        echo "</table>";
        break;

    case 'eliminarLista':
        $resultado = $arqueos->eliminarLista($lista_id);

        if ($resultado) {
            echo json_encode(array("status" => "success", "message" => "Lista eliminada exitosamente"));
        } else {
            echo json_encode(array("status" => "error", "message" => "Error al eliminar la lista"));
        }
        break;

    case 'listarProductos':
        $rspta = $arqueos->listarProductos($valor_limite, $palabra_busqueda);
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            // Determinar color según existencias
            $color_existencias = '';
            if ($reg->existencias <= 0) {
                $color_existencias = 'text-danger font-weight-bold';
            } else if ($reg->existencias <= $reg->cant_minima) {
                $color_existencias = 'text-warning font-weight-bold';
            }

            $data[] = array(
                "0" => $reg->codigo,
                "1" => $reg->descripcion,
                "2" => '<span class="' . $color_existencias . '">' . number_format($reg->existencias, 0) . '</span>',
                "3" => $reg->ubicacion,
                // "4" => number_format($reg->precio1, 0),
                // "5" => number_format($reg->precio2, 0),
                // "6" => number_format($reg->precio3, 0),
                "4" => $reg->cant_minima,
                // "5" => $reg->ref_fabrica
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

    default:
        echo json_encode(array("status" => "error", "message" => "Operación no válida"));
        break;
}
?>