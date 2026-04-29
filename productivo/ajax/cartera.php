<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once "../modelos/Cartera.php";
require_once "../ajax/funciones.php";

$cartera = new Cartera();

$id_op = isset($_POST["id_op"]) ? limpiarCadena($_POST["id_op"]) : "";
$id_ped = isset($_POST["id_ped"]) ? limpiarCadena($_POST["id_ped"]) : "";
$festado = isset($_POST["festado"]) ? limpiarCadena($_POST["festado"]) : "";
$tipo_entrega = isset($_POST["tipo_entrega"]) ? limpiarCadena($_POST["tipo_entrega"]) : "";
$obs_entrega = isset($_POST["obs_entrega"]) ? limpiarCadena($_POST["obs_entrega"]) : "";
$factura = isset($_POST["factura"]) ? limpiarCadena($_POST["factura"]) : "";
$obs_factura = isset($_POST["obs_factura"]) ? limpiarCadena($_POST["obs_factura"]) : "";
$impresa = isset($_POST["impresa"]) ? limpiarCadena($_POST["impresa"]) : "";
$obs_impresa = isset($_POST["obs_impresa"]) ? limpiarCadena($_POST["obs_impresa"]) : "";

$fecha1 = isset($_POST["fecha1"]) ? limpiarCadena($_POST["fecha1"]) : "";
$fecha2 = isset($_POST["fecha2"]) ? limpiarCadena($_POST["fecha2"]) : "";
$fpago = isset($_POST["fpago"]) ? limpiarCadena($_POST["fpago"]) : "";
$selectFiltrar = isset($_POST["selectFiltrar"]) ? limpiarCadena($_POST["selectFiltrar"]) : "";
$datoFiltrar = isset($_POST["datoFiltrar"]) ? limpiarCadena($_POST["datoFiltrar"]) : "";


$obs_pedido = isset($_POST['obs_pedido']) ? limpiarCadena($_POST['obs_pedido']) : "";
$campo_obs = isset($_POST['campo_obs']) ? limpiarCadena($_POST['campo_obs']) : "";
$esAlmacen = isset($_POST['esAlmacen']) ? $_POST['esAlmacen'] : null;
$impresiones = isset($_POST['impresiones']) ? limpiarCadena($_POST['impresiones']) : "";

$condicion = isset($_POST['condicion']) ? limpiarCadena($_POST['condicion']) : "";
$campo = isset($_POST["campo"]) ? limpiarCadena($_POST["campo"]) : "";
$valor = isset($_POST["valor"]) ? limpiarCadena($_POST["valor"]) : "";
$fechaFactura = isset($_POST["fechaFactura"]) ? limpiarCadena($_POST["fechaFactura"]) : "";
$fecha_recaudo = isset($_POST["fecha_recaudo"]) ? limpiarCadena($_POST["fecha_recaudo"]) : "";

switch ($_GET["op"]) {



    case 'listar':

    // --- Sanitizar / listas blancas básicas ---
    // Mapea campos permitidos cuando filtras por tarjeta
    $camposPermitidos = [
        'o.despacho'            => 'o.despacho',
        'po.estado_confir_pago' => 'po.estado_confir_pago',
        'o.autorizacion_empaque'=> 'o.autorizacion_empaque',
    ];

 

    $conditions = [];
    $filtrandoPorTarjeta = false;

    // --- Lógica tarjeta / condición especial ---
    if ( ($campo !== '' && $valor !== '') || $condicion === 'anulado' ) {
        $filtrandoPorTarjeta = true;
    }

    // Si la tarjeta fue "anulado"
    if ($condicion === 'anulado') {
        $conditions[] = "o.estado IN (2,22,21)";
    } else {
        // Por defecto, excluimos anulados
        $conditions[] = "o.estado NOT IN (2,22,21)";
    }

    // Si se filtró por tarjeta (despacho / pago / empaque)
    if ($campo !== '' && $valor !== '') {
        // Valida que el campo esté permitido
        if (isset($camposPermitidos[$campo])) {
            // IMPORTANTE: castea numéricamente cuando sabes que es numérico
            $valorSQL = (int)$valor;
            $conditions[] = "{$camposPermitidos[$campo]} = {$valorSQL}";
        } else {
            // Campo no permitido; podrías ignorar, loguear o lanzar error
        }
    }

    // ----------------
    // Filtros adicionales (estado de despacho y pago)
    // ----------------
    // NOTA: antes lo tenías como elseif (solo un filtro); ahora se pueden combinar.
    if ($festado === '1') {
        $conditions[] = "o.despacho = 1";
    } elseif ($festado === '0') {
        $conditions[] = "o.despacho = 0";
    }

    if ($fpago === '1') {
        $conditions[] = "po.estado_confir_pago = 1";
    } elseif ($fpago === '0') {
        $conditions[] = "po.estado_confir_pago = 0";
    }

    // ----------------
    // Filtro select + dato
    // ----------------
    if ($selectFiltrar !== '' && $datoFiltrar !== '') {
        // Escapa (usa función limpiarCadena o prepared statements)
        $dato = limpiarCadena($datoFiltrar); // <-- tu helper
        switch ($selectFiltrar) {
            case 'pedido':
                // Asumiendo que "pedido" en realidad es la OP (id_op)
                $conditions[] = "o.id_op = '{$dato}'";
                break;
            case 'cotizacion':
                $conditions[] = "p.consecutivo LIKE '%{$dato}%'";
                break;
            case 'cliente':
                $conditions[] = "c.nombre LIKE '%{$dato}%'";
                break;
        }
    } else {
        // No hay búsqueda específica...
       // No hay búsqueda específica...
if (!$filtrandoPorTarjeta) {
    if ($fecha1 !== '' && $fecha2 !== '') {
        $f1 = limpiarCadena($fecha1);
        $f2 = limpiarCadena($fecha2);
        $conditions[] = "DATE(o.fecha_reg) BETWEEN '{$f1}' AND '{$f2}'";
    } else {
        $conditions[] = "YEAR(o.fecha_reg) = YEAR(CURDATE())";
    }
} else {
    // Sí se está filtrando por tarjeta -> mantener coherencia con tarjetas
    $conditions[] = "YEAR(o.fecha_reg) = YEAR(CURDATE())";
}
        // Si es tarjeta, puedes decidir si quieres restringir también al año actual:
        // if ($filtrandoPorTarjeta) { $conditions[] = "YEAR(o.fecha_reg) = YEAR(CURDATE())"; }
    }

    // ----------------
    // Construcción del fragmento WHERE
    // ----------------
    $consulta = '';
    if (count($conditions) > 0) {
        $consulta .= ' WHERE ' . implode(' AND ', $conditions);
    }

    // GROUP BY + ORDER BY
    // ¡OJO! Esto debe ser consistente con tu SELECT.
    // Incluye todas las columnas no agregadas o agrega agregaciones.
    $consulta .= " GROUP BY p.id_ped, o.id_op ORDER BY o.id_op DESC";

    // Ejecutar
    $rspta = $cartera->listar($consulta);
        $data = array();
        $cot = '../reportes/cotimprimir.php?id=';
        $envio = '../reportes/envio.php?id=';
        $pedido = '../reportes/ordenp.php?id=';
        $just = "";
        while ($reg = $rspta->fetch_object()) {

            if ($reg->justificacion == "") {
                $just = "";
            } else {
                $just = '<br>Justificacion: ' . $reg->justificacion;
            }
            // Extraer el año de la fecha reg
            $fechaComoEntero = strtotime($reg->fechacot);
            $anio = date("y", $fechaComoEntero);
            $ceros = '000000';
            $number = strlen($reg->consecutivo);
            $length = strlen($ceros);
            $dif = $length - $number;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;

            // Obtener formas de pago
            $activos = $cartera->mostraFormasPago($reg->id_op);



            $rtefte = 0;  // Acumulará valor con id_formap = 19
            $rteica = 0;  // Acumulará valor con id_formap = 20
            $totalPagos = 0; // Acumulará el total de todos los pagos (o lo que desees)
            $adjunto = "";
            $formaPago = "";

            foreach ($activos as $activo) {

                if (
                    $activo['idforma_pago'] != 19
                    && $activo['idforma_pago'] != 20
                    && $activo['idforma_pago'] != 1
                ) {
                    // Mostrar el adjunto
                    $adjunto = $activo['adjunto'];
                }


                // Sumar para un total general (opcional, según tu lógica)
                $totalPagos += $activo['valor'];

                // Verificar si es Rtefte o Rteica
                if ($activo['idforma_pago'] == 19) {
                    $rtefte += $activo['valor'];
                } elseif ($activo['idforma_pago'] == 20) {
                    $rteica += $activo['valor'];
                }

                // Aquí va tu código para construir $formapagoHtml
                // ...
            }



            $formapagoHtml = "";
            // Ejemplo de “Total” y “Menos reten” 
            // (aquí asumo que $total se refiere a un valor total de la orden, y $reten 
            // al total de retenciones; ajústalo según tu lógica)

            $valorCalculado = ($rtefte + $rteica) - $reg->total;


            $total = number_format($reg->total, 0, ',', '.'); // O el valor que tengas
            $reten = number_format(abs((($rtefte + $rteica) - $reg->total)), 0, ',', '.'); // Si tu “Menos reten” es la suma de Rtefte y Rteica

            // Bloque con la estructura visual
            $retencionesHtml = '<div style="border: 0px solid #000; display: inline-block; padding: 10px; margin: 5px; white-space: nowrap; background-color: #E0E0E0;">';

            // Primera fila: Total y Menos reten
            $retencionesHtml .= '<div>';
            $retencionesHtml .= 'Total: <span style="color: #0077FF;">$' . $total . '</span> ';
            $retencionesHtml .= 'Rfte: <span style="color: #0077FF;">$' . number_format($rtefte, 0, ',', '.') . '</span> ';
            $retencionesHtml .= '</div>';

            // Segunda fila: Rfte y Rtica
            $retencionesHtml .= '<div>';
            $retencionesHtml .= '- Ret: <span style="color: #0077FF;">$' . $reten . '</span>';
            $retencionesHtml .= '  Rtica: <span style="color: #0077FF;">$' . number_format($rteica, 0, ',', '.') . '</span> ';

            // $retencionesHtml .= '<span style="color: #0077FF;">$' . number_format(($rtefte + $rteica), 0, ',', '.') . '</span>';
            $retencionesHtml .= '</div>';

            $retencionesHtml .= '</div>';

            // Concatenar $retencionesHtml al final de $formapagoHtml (o donde quieras mostrarlo)
            $formapagoHtml .= $retencionesHtml;


            // Botón de notas fuera del ciclo
            $formapagoHtml .= '<div style="margin-top: 10px; text-align: right;">';

            $formapagoHtml .= '</div>';

            $notasHtml = '
            <div style="display: flex; align-items: center; gap: 10px;">
             <span title="EstAdo" class="btn-sm d-flex align-items-center justify-content-center ' . $reg->class_color . '" style="width: 100px; background-color: #f0f0f0; border: 1px solid #ccc; border-radius: 5px; text-align: center;">' . $reg->tx_epedido . '</span>
            <button onclick="nota(' . $reg->id_op . ')" type="button" title="Notas" class="btn-sm btn btn-primary d-flex align-items-center justify-content-center" >
            <i class="fas fa-sticky-note"></i>
           </button>
           </div>';



            $usuarioC = (isset($reg) && $reg->user_despacho != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                'Autoriza: ' . $reg->txconfirma . '<br>' . $reg->fecha_despacho  .
                '</div>'
                : ''
            );

            $usuarioD = (isset($reg) && $reg->user_desconfirma != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                'Desautoriza: ' . $reg->txdesconfirma .
                '</div>'
                : ''
            );



            $usuariotC = (isset($reg) && $reg->user_confirma != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                'Autoriza: ' . $reg->txpconfirma .
                '</div>'
                : ''
            );

            $usuariotD = (isset($reg) && $reg->desconfirma != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                'Desautoriza: ' . $reg->txpdesconfirma .
                '</div>'
                : ''
            );


            $usuariotEM = (isset($reg) && $reg->user_empaque != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                'Autoriza: ' . $reg->txempaque . '<br>' . $reg->fecha_autorizacion_empaque  .
                '</div>'
                : ''
            );

            $usuarioEmpaqueReal = (isset($reg) && $reg->user_empaque_real != 0
                ? '<div style="margin-top: 5px; color: #28a745; font-weight: bold;">' .
                'Empacado por: ' . $reg->txempaque_real .
                '</div>'
                : ''
            );



            $formapagoHtml1 = "";
            foreach ($activos as $activo) {
                // Excluir formas de pago con valores 19 y 20
                if (in_array($activo['idforma_pago'], ['19', '20'])) {
                    continue; // Saltar este ciclo si la forma de pago es 19 o 20
                }

                $formapagoHtml1 .= '<div style="display: flex; align-items: center; background-color: #E0E0E0; padding: 5px; margin: 5px; border-radius: 5px; white-space: nowrap;">';

                // Nombre de la forma de pago y el valor
                if ($activo['con_detalle'] !== 0) {
                    // Si tiene detalle = 1, verificar si hay adjunto
                    if (!empty($activo['adjunto']) && $activo['adjunto'] !== 'default.jpg') {
                        // Mostrar el nombre de la forma de pago como enlace a la imagen
                        $formapagoHtml1 .= '<div>';
                        $formapagoHtml1 .= '<a target="_blank" href="../files/pagos/' . $activo['adjunto'] . '" style="text-decoration: underline; color: #0077FF;">' .
                            '<strong>' . $activo['formaPago'] . '</strong></a><br>';
                        $formapagoHtml1 .= '<span style="color: #0077FF;">$' . number_format($activo['valor']) . '</span>';
                        $formapagoHtml1 .= '</div>';
                    } else {
                        // Si no hay adjunto, mostrar el botón "Adjuntar archivo"
                        $formapagoHtml1 .= '<div>';
                        $formapagoHtml1 .= '<strong>' . $activo['formaPago'] . '</strong><br>';
                        $formapagoHtml1 .= '<span style="color: #0077FF;">$' . number_format($activo['valor']) . '</span>';
                        $formapagoHtml1 .= '</div>';
                        $formapagoHtml1 .= '<div style="margin-left: 10px;">';
                        $formapagoHtml1 .= '</div>';
                    }
                } else {
                    // Formas de pago sin detalle
                    $formapagoHtml1 .= '<div>';
                    $formapagoHtml1 .= '<strong>' . $activo['formaPago'] . '</strong><br>';
                    $formapagoHtml1 .= '<span style="color: #0077FF;">$' . number_format($activo['valor']) . '</span>';
                    $formapagoHtml1 .= '</div>';
                }

                // Cerrar el contenedor principal
                $formapagoHtml1 .= '</div>';
            }

            // Muestra el resultado generado
            // echo $formapagoHtml;
            $notas = (isset($reg) && $reg->obs_factura != 0
                ? '<div style="margin-top: 5px; color: #555;">' .
                str_replace('<br>', '<br>', $reg->obs_factura) .
                '</div>'
                : ''
            );

            $informacionVenta = "";

            // Validación para adjunto
            // if ($adjunto === 'default.jpg') {
            // Verificar si alguna forma de pago tiene con_detalle = 1
            $tieneConDetalle = false;
            foreach ($activos as $activo) {
                if ($activo['con_detalle'] == 1) {
                    $tieneConDetalle = true;
                    break; // Salimos del ciclo si encontramos al menos una forma de pago con con_detalle = 1
                }
            }

            // Si hay formas de pago con con_detalle = 1, mostramos el botón

            $informacionVenta .= '
                    <div class="info-row d-flex align-items-center">
                    <button class="btn-sm btn btn-info mr-2" title="Ver observaciones" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')">
                        <span class="fa fa-eye"></span>
                    </button>
                    <button class="btn-sm btn btn-warning" title="Adjuntar archivo" onclick="mostrarAdjuntar(' . $reg->id_op . ')">
                            <i class="far fa-image"></i>
                        </button>
                </div>';



            // Deshabilitar los checkboxes si el estado es 21 o 22
            $disabledDespacho = ($reg->estado == 21 || $reg->estado == 22) ? 'disabled' : '';
            $disabledPago = ($reg->estado == 21 || $reg->estado == 22) ? 'disabled' : '';

            // Crear la fila de datos
            $data[] = array(
                "0" => $reg->id_op . '<br>' . $reg->fecha_reg,
                "1" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega . $just,
                "2" =>  $notasHtml  . $notas,
                "3" => 'V: ' . $reg->vendedor . '<br>' . $informacionVenta,

                "4" => $formapagoHtml,
                "5" => $formapagoHtml1,

                "6" => '<input type="checkbox" class="autorizacion-empaque" data-id="' . $reg->id_op . '" ' .
                    ($reg->autorizacion_empaque == 1 ? 'checked' : '') . ' ' . $disabledPago . '>' . $usuariotEM . $usuarioEmpaqueReal,

                "7" => '<input type="checkbox" class="confirm-despacho" data-id="' . $reg->id_op . '" ' .
                    ($reg->despacho == 1 ? 'checked' : '') . ' ' . $disabledDespacho . '>' . $usuarioC . $usuarioD,

                "8" => '<input type="checkbox" class="confirm-payment" data-id="' . $reg->id_op . '" ' .
                    ($activo['estado_confir_pago'] == 1 ? 'checked' : '') . ' ' . $disabledPago . '>' . $usuariotC . $usuariotD

                

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





   

    case 'insertarComentario':
        $justificacion = isset($_POST["justificacion"]) ? limpiarCadena($_POST["justificacion"]) : "";

        $rspta = $cartera->insertarComentario($id_ped, $justificacion);
        echo $rspta ? "Registrado confirmado" : "No se puede registrar";
        break;





   


    case 'obtenerDatosPedido':
        $rspta = $cartera->obtenerDatosPedido($id_ped);
        echo json_encode($rspta);
        break;



    case 'guardarObsPedido':
        $rspta = $cartera->guardarObsPedido($id_op, $campo_obs, $obs_pedido);
        echo ($rspta) ? 'Observacion registrada' : 'Observacion No se registro';
        break;



    case 'editarAdjunto':
        $id_pago = $_POST['id_pago'];
        $id_formap = $_POST['id_formap'];
        $detalleopcion = $_POST['detalleopcion'];
        $orden = $_POST['orden'];
        $adjunto_archivos = subirAdjunto();
        $adjuntos = [];

        foreach ($id_pago as $i => $id_pago_item) {
            // Buscar manualmente el id_formap correspondiente a este id_pago
            // Suponiendo que vienen en el mismo orden, podemos usar $id_formap[$i]
            $id_forma_pago = $id_formap[$i] ?? null;

            if ($id_forma_pago !== null && isset($adjunto_archivos[$id_forma_pago])) {
                $adjuntos[$i] = $adjunto_archivos[$id_forma_pago];
            } else {
                $adjuntos[$i] = 'default.jpg';
            }
        }

        // Llamada real al modelo
        $rspta = $cartera->editarAdjunto($id_pago, $detalleopcion, $orden, $adjuntos);

        echo ($rspta) ? '✔️ Adjunto registrado' : '❌ Adjunto no se pudo registrar';
        break;




    



    case 'estadoOrdep':
        $rspta = $cartera->estadoOrdep($fecha1, $fecha2);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;




// funciones de ago

 case 'desactivar':
        $rspta = $cartera->desactivar($id_ped);
        echo $rspta ? "Desconfirmar Pago" : " no se puede COnfirmar";
        break;

    case 'activar':
        $rspta = $cartera->activar($id_ped);
        echo $rspta ? "Pago confirmado" : "No se puede Deascornfirma";
        break;

 ///////estado de despacho autorizado

    case 'desactivarDespacho':
        $rspta = $cartera->desactivarDespacho($id_ped);
        if ($rspta) {
            echo json_encode(array(
                "status" => true,
                "message" => "Despacho desconfirmado"
            ));
        } else {
            echo json_encode(array(
                "status" => false,
                "message" => "Despacho no se puede desconfirmar"
            ));
        }
        break;

    case 'activarDespacho':
        $rspta = $cartera->activarDespacho($id_ped);
        if ($rspta) {
            echo json_encode(array(
                "status" => true,
                "message" => "Despacho confirmado"
            ));
        } else {
            echo json_encode(array(
                "status" => false,
                "message" => "Despacho no se puede confirmar"
            ));
        }
        break;



        case 'actualizarAutorizacionEmpaque':

        $autorizacion_empaque = isset($_POST["autorizacion_empaque"]) ? limpiarCadena($_POST["autorizacion_empaque"]) : "0";

        $rspta = $cartera->actualizarAutorizacionEmpaque($id_op, $autorizacion_empaque);
        echo json_encode([
            "status" => $rspta ? "success" : "error",
            "message" => $rspta ? "Empaque autorizado" : "Empaque No se pudo confirmar"
        ]);

        break;
}
