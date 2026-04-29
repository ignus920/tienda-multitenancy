<?php
require_once "../modelos/Orden_p.php";

$orden = new Orden_p();

$id_op = isset($_POST["id_op"]) ? limpiarCadena($_POST["id_op"]) : "";
$id_ped = isset($_POST["id_ped"]) ? limpiarCadena($_POST["id_ped"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$tipo_entrega = isset($_POST["tipo_entrega"]) ? limpiarCadena($_POST["tipo_entrega"]) : "";
$obs_entrega = isset($_POST["obs_entrega"]) ? limpiarCadena($_POST["obs_entrega"]) : "";
$factura = isset($_POST["factura"]) ? limpiarCadena($_POST["factura"]) : "";
$obs_factura = isset($_POST["obs_factura"]) ? limpiarCadena($_POST["obs_factura"]) : "";
$impresa = isset($_POST["impresa"]) ? limpiarCadena($_POST["impresa"]) : "";
$obs_impresa = isset($_POST["obs_impresa"]) ? limpiarCadena($_POST["obs_impresa"]) : "";

$fecha1 = isset($_POST["fecha1"]) ? limpiarCadena($_POST["fecha1"]) : "";
$fecha2 = isset($_POST["fecha2"]) ? limpiarCadena($_POST["fecha2"]) : "";
$festado = isset($_POST["festado"]) ? limpiarCadena($_POST["festado"]) : "";
$selectFiltrar = isset($_POST["selectFiltrar"]) ? limpiarCadena($_POST["selectFiltrar"]) : "";
$datoFiltrar = isset($_POST["datoFiltrar"]) ? limpiarCadena($_POST["datoFiltrar"]) : "";

$estado = isset($_POST['estado']) ? limpiarCadena($_POST['estado']) : "";
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


    case 'revivirOp':
        $rspta = $orden->revivirOp($id_op);
        echo $rspta ? "Orden activada" : "Orden no se puede activar";
        break;

    case 'incrementarTickets':
        $rspta = $orden->incrementarTickets($id_op);
        echo $rspta; // Retorna el nuevo contador
        break;


    case 'registrarEmpaque':
        $id_usuario = isset($_POST["id_usuario"]) ? limpiarCadena($_POST["id_usuario"]) : "";
        $cant_cajas = isset($_POST["cant_cajas"]) ? limpiarCadena($_POST["cant_cajas"]) : "";
        $rspta = $orden->registrarEmpaque($id_op, $id_usuario, $cant_cajas);
        echo $rspta ? "Empaque registrado correctamente" : "No se pudo registrar el empaque";
        break;

    case 'reversarEmpaque':
        $userqr_jefe = isset($_POST["userqr_jefe"]) ? limpiarCadena($_POST["userqr_jefe"]) : "";
        $nombre_jefe = isset($_POST["nombre_jefe"]) ? limpiarCadena($_POST["nombre_jefe"]) : "";
        $justificacion = isset($_POST["justificacion"]) ? limpiarCadena($_POST["justificacion"]) : "";
        
        $rspta = $orden->reversarEmpaque($id_op, $userqr_jefe, $nombre_jefe, $justificacion);
        echo $rspta ? "Orden devuelta a Alistamiento correctamente" : "No se pudo realizar la reversa";
        break;


    case 'validarExistencias':
        $rspta = $orden->validarExistencias($id_ped);
        echo json_encode($rspta);
        break;





    case 'agregarImagen':

        $imagen = isset($_POST["foto"]) ? limpiarCadena($_POST["foto"]) : "";
        //funcion para subir un archivo almacen
        if (!file_exists($_FILES['foto']['tmp_name']) || !is_uploaded_file($_FILES['foto']['tmp_name'])) {
            $imagen = $_POST["imagenactual"];
        } else {
            $ext = explode(".", $_FILES["foto"]["name"]);
            if ($_FILES['foto']['type'] == "image/jpg" || $_FILES['foto']['type'] == "image/jpeg" || $_FILES['foto']['type'] == "image/png") {
                $imagen = round(microtime(true)) . '.' . end($ext);
                move_uploaded_file($_FILES["foto"]["tmp_name"], "../files/notaCredito/" . $imagen);
            }
        }
        $rspta = $orden->agregarImagen($id_op, $imagen);
        echo $rspta ? "Imagen se agrego" : "Imagen no se agrego";
        break;







     /**
      * LISTAR DE ALMACEN autorizacion_empaque
      */
    case 'listar':

        $limit = 100;
    
        switch ($condicion) {
            case 'registrado':
                $consulta = " WHERE o.estado=3 GROUP BY p.id_ped, o.id_op";
                break;
            case 'alistamiento':
                $consulta = " WHERE o.estado=17 GROUP BY p.id_ped, o.id_op";
                break;
            case 'Sin_entregar':
                $consulta = " WHERE o.estado between 17 and 19 GROUP BY p.id_ped, o.id_op";
                break;
            case 'facturado':
                $consulta = " WHERE (o.factura='' OR o.factura IS NULL OR o.factura='0') AND o.estado NOT IN (2,21,22) GROUP BY p.id_ped, o.id_op";
                break;
            case 'sinCartera':
                $consulta = " WHERE o.despacho=0 AND o.estado NOT IN (21, 22) GROUP BY p.id_ped, o.id_op";
                break;
            default:
                $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' GROUP BY p.id_ped, o.id_op";
                break;
        }


                // de Almacen

                if ($selectFiltrar != "" && $datoFiltrar != "") {
                    ($selectFiltrar == 'pedido') ? $consulta = " WHERE o.id_op = '$datoFiltrar' " : null;
                    ($selectFiltrar == 'cotizacion') ? $consulta = " WHERE p.consecutivo  LIKE '%$datoFiltrar%' " : null;
                    ($selectFiltrar == 'cliente') ? $consulta = " WHERE c.nombre LIKE '%$datoFiltrar%' " : null;
                }

        


                ($festado != "") ? $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and o.estado='$festado'  GROUP BY p.id_ped, o.id_op" : null;

                    // ✅ Condición global para todas las consultas
                    if (strpos($consulta, 'WHERE') !== false) {
                        $consulta = str_replace('GROUP BY', "AND o.autorizacion_empaque=1 GROUP BY", $consulta);
                    } else {
                        $consulta = " WHERE o.autorizacion_empaque=1 " . $consulta;
                    }

        $rspta = $orden->listar($consulta);


        

        $rspta = $orden->listar($consulta);
        $data = array();
        $cot = '../reportes/cotimprimir.php?id=';
        $envio = '../reportes/envio.php?id=';
        $pedido = '../reportes/ordenp.php?id=';

        while ($reg = $rspta->fetch_object()) {

            //numero de entregas parciales 
            $txentrega = "";
            $numero_entrega = $reg->max_numero_entrega;

            if ($numero_entrega > 0) {
                $txentrega = $numero_entrega;
            } else {

                $txentrega = 'C';
            }
            // forma de estraer el año de la fecha reg
            $fechaComoEntero = strtotime($reg->fechacot);
            $anio = date("y", $fechaComoEntero);
            //ERP22001940
            $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;

            $botonesEstado = '';

            $activos = $orden->mostraFormasPago($reg->id_op);
            // Inicializa la variable $botonImpreso
            $botonImpreso = '';

            foreach ($activos as $key => $value) {
                if ($value['idforma_pago'] == '16') {
                    // Si se encuentra una forma de pago igual a 16, genera el botón y detén el bucle
                    $botonImpreso = '<button class="btn btn-primary" title="Imprimir Juntos" onclick="imprimePedF(' . $reg->id_op . ', true)"><i class="fa fa-print"></i> ' . $reg->impresa . '</button>';
                    break; // Detén el bucle después de encontrar la forma de pago 16
                }
            }

            // Si no se encontró una forma de pago igual a 16, genera el botón con la otra función
            if (empty($botonImpreso)) {
                $botonImpreso = '<button class="btn btn-primary" title="Imprimir pedido" onclick="imprimePed(' . $reg->id_op . ', true)"><i class="fa fa-print"></i> ' . $reg->impresa . '</button>';
            }
            // Imprime el botón
            //echo $botonImpreso;
            $estadoNotaCredito = '';
            $estadoNC = $orden->mostraEstado($reg->id_ped);

            foreach ($estadoNC as $value) {
                if ($value['total_nc'] == 0) {
                    // No hay notas de crédito
                    switch ($value['estado']) {
                        case '1':
                        case '6':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Comercial" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Comercial</span>';
                            break;
                        case '2':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Laboratorio" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Laboratorio</span>';
                            break;
                        case '3':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Bodega" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Bodega</span>';
                            break;
                        case '4':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Contabilidad" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Contabilidad</span>';
                            break;
                    }
                } else {
                    // Si solo hay una nota de crédito, mostrar su número
                    if ($value['total_nc'] == 1) {
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['num_nc'] . '" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['num_nc'] . '</span>';
                    } else {
                        // Si hay varias, mostrar solo el contador
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['total_nc'] . ' Notas de Crédito" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['total_nc'] . '</span>';
                    }
                }
            }
            $botonEnvio = $reg->despacho == 1
                ? '<a title="Imprimir guía de envío" href="' . $envio . $reg->id_op . '" target="_blank"><button class="btn btn-success"><i class="fa fa-truck"></i></button></a>'
                : '';

            $btnOpciones = $botonEnvio . $botonImpreso . ' <button class="btn btn-info" title="Ver observaciones" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button>';

            //.
            //boton de nota credito
            // ' <a title="Nota credito" href="' . $url . $reg->id_ped . '" target="_self"><button class="btn btn-success "><i class="fas fa-comment-dots"></i></button>
            // </a>';

            // Estado general cuando `estado == 3`
            if ($reg->estado == 3) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>';
                } else {
                    // Con autorización: todos los botones
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>
                    </div>';
                }
                 if (isset($_SESSION['SuperUsuario']) && $_SESSION['SuperUsuario'] == 1) {
                    $btnOpciones .= '<button class="btn btn-danger" title="Cancelar pedido" onclick="cancelarPedido(' . $reg->id_op . ')"><span class="fa fa-trash"></span></button>';
                }

                
            }


            //boton de parciales
            if ($reg->parciales == null) {
                $botonParciees = "";
            } else {
                $botonParciees = '<button title="Entregado"  data-toggle="modal" data-target="#modaEparciales" onclick="listardeEntregaParciales(' . $reg->id_ped . ')"class="btn bg-gray" style="margin: 1px;"> 
                <span class="badge bg-danger" title="Resumen entrega parcial">' . $txentrega . '</span>
                </button>';
            }



            $botonEntrega = ($reg->despacho == 0) ? "" : '<button title="Entregado" class="btn btn-sm bg-gray" onclick="cambioEstadoPedido(20, ' . $reg->id_op . ')" style="margin: 1px;">ET</button>';

            // Validar si se requiere confirmación por cartera
            if (isset($reg->estado) && $reg->estado == 3 && isset($reg->confirm) && $reg->confirm == 1) {

                // Si la confirmación por cartera aún no se ha realizado (`confirmado_cartera` == 0)

                 if (isset($_SESSION['SuperUsuario']) && $_SESSION['SuperUsuario'] == 1) {
                    $botonesEstado .= ' <button class="btn btn-danger" title="Anular pedido" onclick="anularPedido(' . $reg->id_op . ')"><i class="fa fa-trash"></i></button>';
                }
            } else if ($reg->estado == 17) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <button title="Empacado" class="btn btn-sm bg-gray"  style="margin: 1px;">E</button>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>';
                } else {
                    // Con autorización: todos los botones onclick="cambioEstadoPedido(18, ' . $reg->id_op . ')" onclick="cambioEstadoPedido(18, ' . $reg->id_op . ')"
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <button title="Empacado" class="btn btn-sm bg-gray"  style="margin: 1px;">E</button>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span></div>';
                }
                $btnOpciones .= '  <button class="btn btn-danger" title="Cancelar pedido" onclick="cancelarPedido(' . $reg->id_op . ')"><span class="fa fa-trash"></span></button>';
            } else if ($reg->estado == 18) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>';
                } else {
                    // Con autorización: todos los botones
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <button title="Entregado a Ruta" class="btn btn-sm bg-gray" onclick="cambioEstadoPedido(19, ' . $reg->id_op . ')" style="margin: 1px;">ER</button>
                    <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span></div>';
                }
            } else if ($reg->estado == 19) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>';
                } else {
                    // Con autorización: todos los botones
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
                    ' . $botonEntrega . '</div>';
                }
                $btnOpciones .= $botonParciees;
            } else if ($reg->estado == 20) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>';
                } else {
                    // Con autorización: todos los botones
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET</span></div>';
                    
                    $btnOpciones .= '<button title="Entregado" data-toggle="modal" data-target="#modaEparciales" onclick="listardeEntregaParciales(' . $reg->id_ped . ')" class="btn bg-yellow" style="margin: 1px;"> 
                    <span class="badge bg-danger" title="Resumen entrega parcial">' . $txentrega . '</span>C
                    </button>';
                }
            } else if ($reg->estado == 4) {
                if ($reg->despacho == 0) {
                    // Sin autorización: solo R, A, E + badge
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span class="badge badge-danger" style="font-size:13px;padding:6px 10px;">
                        <i class="fa fa-ban"></i> Entrega no Autorizada
                    </span></div>
                    <div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
                } else {
                    // Con autorización: todos los botones incluyendo F
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET</span> 
                    <span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div> 
                    <div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
                }
            }

            // Solo mostrar botón F si despacho=1 (autorizado)
            if ($reg->despacho == 1) {
                if ($reg->factura && $reg->factura != '') {
                    $botonesEstado = $botonesEstado . '<span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div><div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
                } else if ($reg->estado != 4) {
                    $botonesEstado = $botonesEstado . '<span title="Facturado" class="btn btn-sm bg-gray" style="margin: 1px;">F</span></div><div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
                }
            }

            if ($reg->estado == 21 || $reg->estado == 22) {
                $botonesEstado = '<span class="btn-sm bg-red"><b>' . $reg->tx_epedido . '</b></span>';
                $btnOpciones = '<button class="btn btn-info" title="Ver observaciones" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button>';
            }


            $superusuario = $_SESSION['SuperUsuario'];

            if ($superusuario == 1) {
                if ($reg->estado == 22) {

                    //revivir la op de almacen
                    $optsuper = '<button class="btn btn-success" title="Revivir pedido" onclick="revivirOp(' . $reg->id_op . ')"><span class="fa fa-thumbs-up"></span></button>';
                } else {
                    

                    $optsuper = '<button class="btn btn-danger" title="Cancelar pedido" onclick="anularPedido(' . $reg->id_op . ')"><span class="fa fa-trash"></span></button>';
                }
            } else {

                $optsuper = "";
            }

            if ($reg->factura == "") {
                $factura = "";
            } else {
                $factura = '<span class="btn-sm btn-danger mr-1">' . $reg->factura . '</span>';
            }

            $filaModificada = $reg->modificada == 1 ? 'fila-modificada' : '';

            $data[] = array(

                "0" => $reg->id_op . '<br>' . $reg->fecha_reg,
                "1" => ($reg->factura) ? '<span class="btn btn-sm bg-red" >' . $factura . '</span><br>' . $reg->fecha_factura . '<br>' . $estadoNotaCredito :  $estadoNotaCredito,
                "2" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega,
                "3" => $reg->vendedor,
                "4" => $reg->formapago,
                "5" => $botonesEstado,
                "6" => $btnOpciones .= ($reg->imagen) ? ' <a target="blank" href="../files/notaCredito/' . $reg->imagen . '"><img title="Ver" src="../files/img/default.png" width="50px"></a>'
                    :
                    ' <button class="btn btn-success" data-toggle="modal" data-target="#modalImagen" onclick="mostrarObsPedido(' . $reg->id_op . ')"><i class="fas fa-images"></i></span></button>',

                "7" => $optsuper,
                "DT_RowClass" => $filaModificada // Aplicar la clase a toda la fila
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





    case 'consultarEstadoOP':
        $id_op = $_POST['id_op'];
        $rspta = $orden->consultarEstadoOP($id_op);
        echo json_encode($rspta);
        break;









    case 'desactivarRecaudo':
        $rspta = $orden->desactivarRecaudo($id_op, $id_ped);
        echo $rspta ? "Con retencion" : "Retencion no se puede desactivar";
        break;



    case 'activarRecaudo':
        $rspta = $orden->activarRecaudo($id_op, $id_ped);
        echo $rspta ? "Sin retencion" : "Retencion no se puede activar";
        break;











    case 'listarFacturacion': ///////LISTAR DE FACTURACION este listar es el unico con el boton de eliminar factura

        $fechaFactura = $_POST['fechaFactura'];
        $limit = 100;
        //echo $condicion;

        if ($fechaFactura == "") {

            switch ($condicion) {
                // registrado
                case 'registrado':
                    $consulta = " WHERE o.estado = 3 AND o.despacho = 1 GROUP BY p.id_ped, o.id_op";
                    break;

                // alistamiento
                case 'alistamiento':
                    $consulta = " WHERE o.estado = 17 AND o.despacho = 1 GROUP BY p.id_ped, o.id_op";
                    break;

                // sin entregar
                case 'Sin_entregar':
                    $consulta = " WHERE o.estado BETWEEN 17 AND 19 GROUP BY p.id_ped, o.id_op";
                    break;

                // factura
                case 'facturado':
                    $consulta = " WHERE (o.factura = '' OR o.factura IS NULL OR o.factura = '0') 
                         AND o.despacho = 1 
                         AND o.estado NOT IN (2, 21, 22) 
                         GROUP BY p.id_ped, o.id_op";
                    break;

                // default
                default:
                    $consulta = " WHERE DATE(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' GROUP BY p.id_ped, o.id_op";
                    break;
            }
        } else {
            $consulta = " WHERE DATE(o.fecha_factura) = '$fechaFactura' AND o.despacho = 1";
        }


        // $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2'";

        if ($selectFiltrar != "" && $datoFiltrar != "") {
            ($selectFiltrar == 'pedido') ? $consulta = " WHERE o.id_op = '$datoFiltrar' " : null;
            ($selectFiltrar == 'cotizacion') ? $consulta = " WHERE p.consecutivo  LIKE '%$datoFiltrar%' " : null;
            ($selectFiltrar == 'cliente') ? $consulta = " WHERE c.nombre LIKE '%$datoFiltrar%' " : null;
        }


        if ($festado == "100") {
            $consulta = " where (o.factura='' or o.factura is null or o.factura='0') and o.estado not in (2,21,22) ";
        } elseif ($festado != "") {
            $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and o.estado='$festado' GROUP BY p.id_ped, o.id_op";
        }


        // ($festado != "") ? $consulta = $consulta . " AND o.estado='$festado'" : null;

        $rspta = $orden->listar($consulta);
        $data = array();
        $cot = '../reportes/cotimprimir.php?id=';
        $envio = '../reportes/envio.php?id=';
        $pedido = '../reportes/ordenp.php?id=';

        $url1 = 'ordenp.php?m=1&p=';

        while ($reg = $rspta->fetch_object()) {

            //numero de entregas parciales 
            $txentrega = "";
            $numero_entrega = $reg->max_numero_entrega;

            if ($numero_entrega > 0) {
                $txentrega = $numero_entrega;
            } else {

                $txentrega = 'C';
            }

            // forma de estraer el año de la fecha reg
            $fechaComoEntero = strtotime($reg->fechacot);
            $anio = date("y", $fechaComoEntero);
            //ERP22001940
            $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;

            $botonesEstado = '';
            $estadoNotaCredito = "";

            $estadoNC = $orden->mostraEstado($reg->id_ped);

            foreach ($estadoNC as $value) {
                if ($value['total_nc'] == 0) {
                    // No hay notas de crédito
                    switch ($value['estado']) {
                        case '1':
                        case '6':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Comercial" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Comercial</span>';
                            break;
                        case '2':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Laboratorio" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Laboratorio</span>';
                            break;
                        case '3':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Bodega" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Bodega</span>';
                            break;
                        case '4':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Contabilidad" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Contabilidad</span>';
                            break;
                    }
                } else {
                    // Si solo hay una nota de crédito, mostrar su número
                    if ($value['total_nc'] == 1) {
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['num_nc'] . '" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['num_nc'] . '</span>';
                    } else {
                        // Si hay varias, mostrar solo el contador
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['total_nc'] . ' Notas de Crédito" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['total_nc'] . '</span>';
                    }
                }
            }


            $btnOpciones = '<button class="btn btn-primary " onclick="imprimePed(' . $reg->id_op . ', false)"><i class=""></i> ' . $reg->impresa_factura . '</button> 
            <button class="btn btn-info" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button>' .
                //boton de editar forma de pago
                ' <a title="Cambiar forma de pago" target="_self" href="' . $url1 . $reg->id_ped . '&vi=2"><button class="btn btn-success" ><i class="fa fa-dollar"></i> </button></a>';


            if ($reg->estado == 3) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <button title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</button>
        <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';
            }

            // Validar si se requiere confirmación por cartera
            if (isset($reg->estado) && $reg->estado == 3 && isset($reg->confirm) && $reg->confirm == 1) {
                // Si la confirmación por cartera aún no se ha realizado (`confirmado_cartera` == 0)

                // Si la confirmación por cartera ya se ha realizado (`confirmado_cartera` == 1)
                $botonesEstado = '<div> 
        <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>
        </div>';

                if ($_SESSION['id'] != 178) {
                    $btnOpciones .= '<button class="btn btn-danger" title="Anular pedido" onclick="anularPedido(' . $reg->id_op . ')"><i class="fa fa-trash"></i></button>';
                }
            } else if ($reg->estado == 17) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <button title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</button>
        <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';
            } else if ($reg->estado == 18) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <button title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</button>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';
            } else if ($reg->estado == 19) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
        <button title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</button>';
            } else if ($reg->estado == 20) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET</span>';
                $btnOpciones .= '<button title="Entregado"  data-toggle="modal" data-target="#modaEparciales" onclick="listardeEntregaParciales(' . $reg->id_ped . ')"class="btn bg-yellow" style="margin: 1px;"> 
        <span class="badge bg-danger" title="Resumen entrega parcial">' . $txentrega . '</span>
        </button>';
            } else if ($reg->estado == 4) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET</span> 
        <span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div> 
        <div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
            }

            if ($reg->factura && $reg->factura != '') {
                $botonesEstado = $botonesEstado . '<span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div><div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
            } else if ($reg->estado != 4) {
                $botonesEstado = $botonesEstado . '<a title="Facturar" href="#" data-toggle="modal" data-target="#modalfac" onclick="mostrarOrden(' . $reg->id_op . ')" style="margin: 1px;"><span class="btn btn-sm bg-gray">F</span></a></div>
        <div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
            }

            if ($reg->estado == 21 || $reg->estado == 22) {
                $botonesEstado = '<span class="btn-sm bg-red"><b>' . $reg->tx_epedido . '</b></span>';
                $btnOpciones = '<button class="btn btn-info" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button>';
            }

            if ($reg->factura == "") {
                $factura = "";
            } else {
                $factura = '<span class="btn-sm btn-danger mr-1">' . $reg->factura . '</span>';
            }


            //funcion que trae la forma de pago del pedido
            $txretencion = "";
            $txvalores = "";
            $activos = $orden->mostraRetemciones($reg->id_op);
            foreach ($activos as $key => $value) {

                // number_format($reg->cantidad,0,',','.'),
                if ($reg->estado_retencion == 1) {

                    $txretencion = $value['retenciones'];

                    $txvalores = '<span class="btn-sm btn-success">CR $' . number_format($reg->total - $value['valor'], 0, ',', '.') . '</span> - SR $' . number_format($reg->total, 0, ',', '.');
                } else {
                    $botonImpreso = ' <button class="btn btn-primary" title="Imprimir pedido" onclick="imprimePed(' . $reg->id_op . ', true)"><i class="fa fa-print"></i> ' . $reg->impresa . '</button>';

                    $txvalores = '<span class="btn-sm btn-warning">SR $' . number_format($reg->total - $value['valor'], 0, ',', '.') . '</span>';
                }
            }


            //botn check
            if ($festado == "100") {
                $btncheck = ' <input type="checkbox" name="facturar[]" value="' . $reg->id_op . '" onchange="seleccionar(this)">  ';
            } else {
                $btncheck = "";
            }



            $data[] = array(

                "0" => $btncheck . $reg->id_op . '<br>' . $reg->fecha_reg,
                "1" => ($reg->factura) ? '<span class="btn btn-sm bg-red" onclick="eliminarFactura(' . $reg->id_op . ')">' . $factura . '</span><br>' . $reg->fecha_factura . '<br>' . $estadoNotaCredito :  $estadoNotaCredito,
                "2" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega,
                "3" => $reg->vendedor,
                "4" => $reg->formapago,
                "5" => $txretencion . '<br>' . $txvalores,
                "6" => $botonesEstado,
                "7" => $btnOpciones .= ($reg->imagen) ? ' <a target="blank" href="../files/notaCredito/' . $reg->imagen . '"><img title="Ver" src="../files/img/default.png" width="50px"></a>' : ''

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














    case 'listarVendedor': //LISTAR ORDEN DE PEDIDO VENDEDOR ordenp_vendedor.php

        $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2'";


        $limit = 100;

        switch ($condicion) {
            //registrado
            case 'registrado':
                $consulta = " WHERE  o.estado=3 GROUP BY p.id_ped, o.id_op";
                break;

            //alistamiento
            case 'alistamiento':
                $consulta = " WHERE  o.estado=17 GROUP BY p.id_ped, o.id_op";
                break;

            //sin entregar
            case 'Sin_entregar':
                $consulta = " WHERE  o.estado between 17 and 19 GROUP BY p.id_ped, o.id_op";
                break;

            //sin factura
            case 'facturado':
                $consulta = " where (o.factura='' or o.factura is null or o.factura='0') and o.estado not in (2,21,22) GROUP BY p.id_ped, o.id_op";
                break;

            //default
            default:
                $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' GROUP BY p.id_ped, o.id_op";
                break;
        }
        if ($selectFiltrar != "" && $datoFiltrar != "") {
            ($selectFiltrar == 'pedido') ? $consulta = " WHERE o.id_op = '$datoFiltrar' " : null;
            ($selectFiltrar == 'cotizacion') ? $consulta = " WHERE p.consecutivo  LIKE '%$datoFiltrar%' " : null;
            ($selectFiltrar == 'cliente') ? $consulta = " WHERE c.nombre LIKE '%$datoFiltrar%' " : null;
        }


        ($festado != "") ? $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and o.estado='$festado' GROUP BY p.id_ped, o.id_op" : null;

        $idUsuario = $_SESSION['id'];
        //$consulta = $consulta . " AND p.idusuario = '$idUsuario'";

        $rspta = $orden->listar($consulta);
        $data = array();
        $cot = '../reportes/cotimprimir.php?id=';
        $envio = '../reportes/envio.php?id=';
        $pedido = '../reportes/ordenp.php?id=';

        //url nota
        $url = 'nota_credito.php?m=1&p=';

        while ($reg = $rspta->fetch_object()) {

            //numero de entregas parciales 
            $txentrega = "";
            $numero_entrega = $reg->max_numero_entrega;

            if ($numero_entrega > 0) {
                $txentrega = $numero_entrega;
            } else {

                $txentrega = 'C';
            }
            // forma de estraer el año de la fecha reg
            $fechaComoEntero = strtotime($reg->fechacot);
            $anio = date("y", $fechaComoEntero);
            //ERP22001940
            $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;

            $botonesEstado = '';

            $estadoNotaCredito = "";
            // $arreglo=["1"=>'comercial',
            //         "2"=>'Laboratorio'];
            //         $estado=$arreglo[$reg->estado];
            $estadoNC = $orden->mostraEstado($reg->id_ped);

            $rsptap = $orden->mostrConfir($reg->id_op);
            $estado_confir_pago = $rsptap['estado_confir_pago'];

            //         if ($reg->estado != 22) {

            // }

            foreach ($estadoNC as $value) {
                if ($value['total_nc'] == 0) {
                    // No hay notas de crédito
                    switch ($value['estado']) {
                        case '1':
                        case '6':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Comercial" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Comercial</span>';
                            break;
                        case '2':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Laboratorio" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Laboratorio</span>';
                            break;
                        case '3':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Bodega" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Bodega</span>';
                            break;
                        case '4':
                            $estadoNotaCredito = '<span class="btn btn-sm btn-warning" title="NC-Contabilidad" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-Contabilidad</span>';
                            break;
                    }
                } else {
                    // Si solo hay una nota de crédito, mostrar su número
                    if ($value['total_nc'] == 1) {
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['num_nc'] . '" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['num_nc'] . '</span>';
                    } else {
                        // Si hay varias, mostrar solo el contador
                        $estadoNotaCredito = '<span class="btn btn-sm btn-success" title="NC-' . $value['total_nc'] . ' Notas de Crédito" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito(' . $reg->id_ped . ')">NC-' . $value['total_nc'] . '</span>';
                    }
                }
            }

            $btnOpciones = ' <button class="btn btn-info" title="Ver observaciones" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button> ' . ' <button type="button" title="Editar datos de envio" class="btn btn-success" data-toggle="modal" data-target="#editarEnvio" onclick="mostrarEnvio(' . $reg->id_op . ')">
            <i class="fa fa-envelope"></i>
            </button>';




           if ($reg->estado == 3) {

            $botonesEstado = '<div>
                <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                <button title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</button>
                <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
                <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
                <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';

                // Mostrar botón solo si es superusuario
                if (isset($_SESSION['SuperUsuario']) && $_SESSION['SuperUsuario'] == 1) {
                    $botonesEstado .= ' <button class="btn btn-danger" title="Anular pedido" onclick="anularPedido(' . $reg->id_op . ')"><i class="fa fa-trash"></i></button>';
                }

                $botonesEstado .= '</div>';
            }

            //funcion para validar si toca confirmar por cartera
            // Validar si se requiere confirmación por cartera
            if (isset($reg->estado) && $reg->estado == 3 && isset($reg->confirm) && $reg->confirm == 1) {
                // Si la confirmación por cartera aún no se ha realizado (`confirmado_cartera` == 0)
                if ($estado_confir_pago == 0) {
                    $botonesEstado = '<div> 
                    <span title="Confirmar en cartera" class="btn btn-sm bg-gray" style="margin: 1px;">R</span>
                    <button title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</button>
                    <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>
                    </div>';
                } else {
                    // Si la confirmación por cartera ya se ha realizado (`confirmado_cartera` == 1)
                    $botonesEstado = '<div> 
                    <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                    <span title="En Alistamiento" class="btn btn-sm bg-gray" style="margin: 1px;">A</span>
                    <span title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</span>
                    <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
                    <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>
                    </div>';
                }


                $btnOpciones .= ' <button class="btn btn-danger" title="Anular pedido" onclick="anularPedido(' . $reg->id_op . ')"><i class="fa fa-trash"></i></button>';


            } else if ($reg->estado == 17) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <button title="Empacado" class="btn btn-sm bg-gray" style="margin: 1px;">E</button>
        <span title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';
            } else if ($reg->estado == 18) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <button title="Entregado a Ruta" class="btn btn-sm bg-gray" style="margin: 1px;">ER</button>
        <span title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</span>';
            } else if ($reg->estado == 19) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
        <button title="Entregado" class="btn btn-sm bg-gray" style="margin: 1px;">ET</button>';
            } else if ($reg->estado == 20) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
        <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
        <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
        <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
        <span title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET </span>';

                $btnOpciones .= '<button data-toggle="modal" data-target="#modaEparciales" onclick="listardeEntregaParciales(' . $reg->id_ped . ')"class="btn bg-yellow" style="margin: 1px;"> 
        <span class="badge bg-danger" title="Resumen entrega parcial">' . $txentrega . '</span>
        </button>';
            } else if ($reg->estado == 4) {
                $botonesEstado = '<div> <span title="Impreso en Bodega" class="btn btn-sm bg-yellow" style="margin: 1px;">R</span>
                <span title="En Alistamiento" class="btn btn-sm bg-yellow" style="margin: 1px;">A</span>
                <span title="Empacado" class="btn btn-sm bg-yellow" style="margin: 1px;">E</span>
                <span title="Entregado a Ruta" class="btn btn-sm bg-yellow" style="margin: 1px;">ER</span>
                <button title="Entregado" class="btn btn-sm bg-yellow" style="margin: 1px;">ET</button> 
                <span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div> 
                <div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
            }




            if ($reg->factura && $reg->factura != '') {

                $botonesEstado = $botonesEstado . '<span title="Facturado" class="btn btn-sm bg-yellow" style="margin: 1px;">F</span></div><div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';

                // Verificar si el estado es igual a 22
                $fechaRegistro = new DateTime($reg->fecha_reg);
                $fechaActual = new DateTime();
                $intervalo = $fechaRegistro->diff($fechaActual);

                 //Solo mostrar el botón si no han pasado más de 6 meses  op habilitado  40040  se devbe obtener es el id de la tabla v_orden_p
                if ($reg->estado != 22 && $intervalo->m + ($intervalo->y * 12) < 6 || 
                    $reg->id_op == 86 ) {
                    $btnOpciones .= ' <a title="Devolucion" href="' . $url . $reg->id_ped . '" target="_self"><button class="btn btn-success"><i class="fab fa-dropbox"></i></button></a>';
                }
            } else if ($reg->estado != 4) {

                $botonesEstado = $botonesEstado . '<span title="Facturado" class="btn btn-sm bg-gray" style="margin: 1px;">F</span></div><div style="color: #232b54;"><b>' . $reg->tx_epedido . '</b></div>';
            }

            if ($reg->estado == 21 || $reg->estado == 22) {

                $botonesEstado = '<span class="btn btn-sm bg-red"><b>' . $reg->tx_epedido . '</b></span>' . '  <button class="btn btn-danger" title="Anular pedido" onclick="anularPedido(' . $reg->id_op . ')"><i class="fa fa-trash"></i></button>';
            }

            if ($reg->estado == 22) {
                $botonesEstado = '<span class="btn btn-sm bg-red"><b>' . $reg->tx_epedido . '</b></span>';
            }

            $acciones = "";

            if ($reg->factura == "") {
                $factura = "";
            } else {
                $factura = '<span class="btn-sm btn-danger mr-1">' . $reg->factura . '</span>';
            }

            $data[] = array(

                "0" => $reg->id_op . '<br>' . $reg->fecha_reg,
                "1" => ($reg->factura) ? '<span class="btn btn-sm bg-red" >' . $factura . '</span><br>' . $reg->fecha_factura . '<br>' . $estadoNotaCredito :  $estadoNotaCredito,
                "2" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega,
                "3" => $reg->vendedor,
                "4" => $reg->formapago,
                "5" => $botonesEstado,
                "6" => $btnOpciones .= ($reg->imagen) ? ' <a target="blank" href="../files/notaCredito/' . $reg->imagen . '"><img title="Ver" src="../files/img/default.png" width="50px"></a>' : ''
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

    case 'cambioEstadoPedido':
        $rspta = $orden->cambioEstadoPedido($estado, $id_op, $campo_obs, $obs_pedido);
        echo ($rspta) ? 'Se modifico el estado' : 'No se modifico el estado';
        break;

    case 'guardarObsPedido':
        $rspta = $orden->guardarObsPedido($id_op, $campo_obs, $obs_pedido);
        echo ($rspta) ? 'Obsersacion registrada' : 'Observacion No se registro';
        break;


    // funcion para guardar una factura
    case 'factura':
        $rspta = $orden->factura($factura, $id_op, $fecha_recaudo);
        echo ($rspta) ? 'Factura registrada' : 'Factura no se pudo registrar';
        break;


    // funcion para guardar una factura

    case 'facturaVarios':
        $bandera = isset($_POST["bandera"]) ? limpiarCadena($_POST["bandera"]) : "";
        $rspta = $orden->facturaVarios($factura, $_POST['arr_cant'], $fecha_recaudo, $bandera);

        if ($rspta) {

            $rspta1 = $orden->mostrarFacturas($_POST['arr_cant']);
            ob_start();
            $salida = "";
            $contador = 1;
            $salida .= "<table>";
            $salida .= "<thead> 
    <th>#</th>
    <th>Empresa</th>
    <th>Tipo Documento</th>
    <th>Prefijo</th>
    <th>Documento Numero</th>
    <th>Fecha</th>
    <th>Tercero Interno</th>
    <th>Tercero Externo</th>
    <th>Nota</th>
    <th>FormaPago </th>
    <th>Fecha Entrega</th>
    <th>Prefijo Documento Externo</th>
    <th>Numero_Documento_Externo</th>
    <th>Verificado</th>
    <th>Anulado</th>
    <th>Personalizado 1</th>
    <th>Personalizado 2</th>
    <th>Personalizado 3</th>
    <th>Personalizado 4</th>
    <th>Personalizado 5</th>
    <th>Personalizado 6</th>
    <th>Personalizado 7</th>
    <th>Personalizado 8</th>
    <th>Personalizado 9</th>
    <th>Personalizado 10</th>
    <th>Personalizado 11</th>
    <th>Personalizado 12</th>
    <th>Personalizado 13</th>
    <th>Personalizado 14</th>
    <th>Personalizado 15</th>
    <th>Sucursal</th>
    <th>Clasificación</th>
    <th>Producto</th>
    <th>Bodega</th>
    <th>Unidad De Medida</th>
    <th>Cantidad</th>
    <th>IVA</th>
    <th>Valor Unitario</th>
    <th>Vencimiento</th>
    <th>Nota</th>
    <th>Centro costos</th>
    <th>Cantidad</th>
    <th>Personalizado 1</th>
    <th>Personalizado 2</th>
    <th>Personalizado 3</th>
    <th>Personalizado 4</th>
    <th>Personalizado 5</th>
    <th>Personalizado 6</th>
    <th>Personalizado 7</th>
    <th>Personalizado 8</th>
    <th>Personalizado 9</th>
    <th>Personalizado 10</th>
    <th>Personalizado 11</th>
    <th>Personalizado 12</th>
    <th>Personalizado 13</th>
    <th>Personalizado 14</th>
    <th>Personalizado 15</th>
    <th>Codigo Centro Costos</th>
    </thead>";

            while ($r = $rspta1->fetch_object()) {
                $salida .= "<tr>
        <td>" . $contador++ . "</td>
        <td>FERVICOM S.A.S</td>
        <td>FV</td>
        <td>FE</td>
        <td>" . $r->factura . "</td>
        <td>" . $r->fecha_factura . "</td>
        <td>" . $r->fecha_factura . "</td>
        <td>" . $r->num_ident . "</td>
        <td>Factura de Venta</td>
        <td>" . $r->nombre . "</td>
        <td>E</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>" . $r->codigo . "</td>
        <td>principal</td>
        <td>Und.</td>
        <td>" . $r->cantidad . "</td>
        <td>0,19</td>
        <td>" . $r->precio1 . "</td>
        <td></td>
        <td>" . $r->fecha_factura . "</td>
        <td></td>
        <td></td>
        <td>" . $r->num_ident . "</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        </tr>";
            }

            $salida .= "</table>";

            ob_end_clean();

            // Configurar encabezados para descargar el archivo
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=nombreArchivoQueDescarga.xls");

            // Imprimir el contenido del archivo Excel
            echo $salida;
            exit;
        }
        //CORCHETE DEL IF

        break;






    case 'ultimaFactura':
        $rspta = $orden->ultimaFactura();
        echo json_encode($rspta);
        break;

    case 'mostrarOrden':
        $rspta = $orden->mostrarOrden($id_op);
        echo json_encode($rspta);
        break;

    case 'mostrarPedido':
        $rspta = $orden->mostrarPedido($id_ped);
        echo json_encode($rspta);
        break;

    case 'mostrarItemsObservacion':
        $rspta = $orden->mostrarItemsObservacion($id_op);
        echo json_encode($rspta);
        break;



    case 'imprimePed':
        $rspta = $orden->imprimePed($id_op, $obs_impresa, $esAlmacen, $impresiones);
        
        // Obtenemos el valor actualizado para retornarlo al JS
        $sql_select = "SELECT cantTiquets FROM v_orden_p WHERE id_op = '$id_op'";
        $res = ejecutarConsultaSimpleFila($sql_select);
        echo $res['cantTiquets'];
        break;


    case 'eliminarFactura':
        $rspta = $orden->eliminarFactura($id_op);
        echo $rspta ? "Factura eliminada" : "Factura no se puede eliminar";
        break;


    case 'estadoOrdep':
        $rspta = $orden->estadoOrdep($fecha1, $fecha2);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;







    case 'listarExistencias':

        $count = 1;

        $rspta = $orden->listarExistencias($id_ped);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $existencias = $reg->existencias;

            $existencias = $reg->existencias;

            if ($existencias > $reg->cantidad) {
                $mensaje = '<span class="badge btn-primary">' . $reg->existencias . '</span>';
            } else {
                $mensaje = '<span style="  white-space: nowrap;" class="badge btn-danger" data-toggle="modal" data-target="#modalJust" onclick="mostrarMovp(' . $reg->id_mpt . ',\'' . $reg->codigo . '\')">Disponible ' . $existencias . '   </span>';
            }

            $txreservas =
                '<table class="table table-sm tablereservas " data-toggle="modal" data-target="#modalResarva" onclick="mostrarReserva(' . $reg->id_producto . ',\'' . $reg->codigo . '\')">
    <tbody>
    <tr>
    <td>S</td>
    <td>' . $mensaje . '</td>
    <td><span class="badge bg-warning">' . $reg->cantTra1 . '</span></td>
    </tr>
    <tr>
    <td>T</td>
    <td><span class="badge bg-success">' . $reg->cantM . '</td>
    <td><span class="badge bg-warning">' . $reg->cantTra2 . '</span></td>
    </tbody>
    </table>';



            $data[] = array(
                "0" => $count,
                "1" => $reg->descripcion,
                "2" => number_format($reg->cantidad, 0, ',', '.'),
                "3" => $reg->peso,
                "4" => $txreservas

            );
            $count++;
        }
        $results = array(
            "sEcho" => 1, //Información para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;



    case 'guardaJust':
        $id_mpt = isset($_POST["id_mpt"]) ? limpiarCadena($_POST["id_mpt"]) : "";
        $observacion = isset($_POST["observacion"]) ? limpiarCadena($_POST["observacion"]) : "";
        $descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
        $rspta = $orden->guardaJust($id_mpt, $observacion, $descripcion);
        echo $rspta ? "Observacion registrada" : "Observacion no se pudo registrar";
        break;





    case 'mostrarFormaPago':
        $rspta = $orden->mostrarFormaPago($id_op);
        echo json_encode($rspta);
        break;




    case 'listarFpagos':
        $id_formap = isset($_POST["id_formap"]) ? limpiarCadena($_POST["id_formap"]) : "";
        require_once "../modelos/Sincrud.php";
        $sincrud = new Sincrud();
        $rspta = $sincrud->listarFpago($id_formap);




        //Obtener los permisos asignados al usuario
        $id = $_GET['id'];
        $marcados = $orden->listarFpagos($id);
        //Declaramos el array para almacenar todos los permisos marcados
        $valores = array();

        //Almacenar los permisos asignados al usuario en el array
        while ($per = $marcados->fetch_object()) {
            array_push($valores, $per->id_formap);
        }

        //Mostramos la lista de permisos en la vista y si están o no marcados
        $ID_19 = null; // Variable para almacenar el ID 19
        while ($reg = $rspta->fetch_object()) {
            $sw = in_array($reg->idforma_pago, $valores) ? 'checked' : '';

            // Verificar si el ID es igual a 19
            if ($reg->idforma_pago == 19) {
                $ID_19 = $reg->idforma_pago; // Almacenar el ID 19 en la variable
            }

            echo '<li> <input class="mr-1" type="checkbox" ' . $sw . ' name="listafp[]" onchange="updlista($(this).val())" value="' . $reg->idforma_pago . '">' . $reg->nombre . '</li>';
        }


        break;





    case 'listarF':
        //Recibimos el idingreso
        $id_ordenp = $_GET['idp'];

        $rspta = $orden->listarF($id_ordenp);

        while ($reg = $rspta->fetch_object()) {

            if ($reg->con_detalle == 1) {
                $input = '<tr class="filas"  id="fila' . $reg->idforma_pago . '" >
     <td><input type="hidden" name="orden[]" value="0">
     <input type="hidden" name="id_pago[]" value="' . $reg->id_pago . '">
     <input type="hidden" name="detalle[]" value="0" >
     <input type="hidden" name="id_formap[]" value="' . $reg->idforma_pago . '">
     <span>' . $reg->nombre . '</span></td>
     <td><input type="text" name="valorpago[]" id="val' . $reg->idforma_pago . '" value="' . $reg->valor . '" onchange="calcularpago(' . $reg->idforma_pago . ')" required></td>
     <td><input type="text" name="detalleopcion[]" value="' . $reg->depago . '" required></td>
     <td>
     <input type="file" name="adjunto_' . $reg->idforma_pago . '">
     </td>
     </tr>';
            } else {

                $input = '<tr class="filas" id="fila' . $reg->idforma_pago . '">
    <td><input type="hidden" name="orden[]" value="1">
    <input type="hidden" name="id_pago[]" value="' . $reg->id_pago . '">
    <input type="hidden" name="detalle[]" value="1" >
    <input type="hidden" name="id_formap[]" value="' . $reg->idforma_pago . '">
    <span>' . $reg->nombre . '</span>
    </td>
    <td>
    <input type="hidden" name="valorpago[]" id="valorpago">
    <span id="valfijo">' . $reg->valor . '</span>
    </td>
    <td><input type="hidden" name="detalleopcion[]" ></td>
    <td></td>';
            }
            echo $input;
        }

        break;




    //funcion de adjuntos pàra cartera
    case 'listarP':
        //Recibimos el idingreso
        $id_ordenp = $_GET['idp'];

        $rspta = $orden->listarF($id_ordenp);

        while ($reg = $rspta->fetch_object()) {


            $input = '<tr class="filas"  id="fila' . $reg->idforma_pago . '" >
 <td><input type="hidden" name="orden[]" value="' . $reg->orden . '">
 <input type="hidden" name="id_pago[]" value="' . $reg->id_pago . '">
 <input type="hidden" name="detalle[]" value="0" >
 <input type="hidden" name="id_formap[]" value="' . $reg->idforma_pago . '">
 <span>' . $reg->nombre . '</span></td>
 <td><input type="text" name="valorpago[]" id="val' . $reg->idforma_pago . '" value="' . $reg->valor . '" onchange="calcularpago(' . $reg->idforma_pago . ')" readonly></td>
 <td><input type="text" name="detalleopcion[]" value="' . $reg->depago . '" readonly></td>
 <td>
 <input type="file" name="adjunto_' . $reg->idforma_pago . '" required>
 </td>
 </tr>';

            echo $input;
        }

        break;




    case 'mostraPago':
        $rspta = $orden->mostraPago($id_ped);
        echo json_encode($rspta);
        break;



    // funcion para mostrar las observacines de la nota credito
    case 'mostrarObsNotaCredito':

        $rspta = $orden->mostrarObsNotaCredito($id_ped);
        echo '<div class="mb-3">
        <h5>Nota crédito</h5>';

        while ($reg = $rspta->fetch_object()) {

            $idfoto = $reg->id;
            $imagen = ''; // Valor por defecto para evitar errores

            // Obtener evidencias específicas de la nota de crédito
            $rspt = $orden->Evidencias($idfoto);

            // Verifica si hay evidencias y recógelas correctamente
            if ($reg1 = $rspt->fetch_object()) {
                $imagen = '
                <a target="blank" href="../files/notaCredito/' . $reg1->imagen . '">
                    <img title="ver" src="../files/img/default.png" width="40px" class="img-thumbnail ml-2">
                </a>';
            }

            echo '</div>';

            // Tarjeta con información de la nota de crédito
            echo '<div class="card card-info card-outline mb-3">
            <div class="card-header d-flex align-items-center">
                <h6 class="mb-0">
                    <span class="badge badge-danger p-2" style="font-size: 16px;">NC- ' . $reg->num_nc . '</span>' . $imagen . '
                </h6>
            </div>

            <div class="card-body">
                <h6><strong>' . $reg->producto . '</strong></h6>

                <div class="mb-2">
                    <label><strong>Comercial</strong></label><br>
                    <p>' . $reg->obs_comercial . '</p>
                </div>

                <div class="mb-2">
                    <label><strong>Laboratorio</strong></label><br>
                    <p>' . $reg->obs_laboratorio . '</p>
                </div>

                <div class="mb-2">
                    <label><strong>Bodega</strong></label><br>
                    <p>' . $reg->obs_bodega . '</p>
                </div>

                <div class="mb-2">
                    <label><strong>Contabilidad</strong></label><br>
                    <p>' . $reg->obs_contabilidad . '</p>
                </div>';

            // Obtener y mostrar más evidencias de la nota de crédito
            $rsptaf = $orden->mostrarEvidencias($reg->id);
            while ($regImg = $rsptaf->fetch_object()) {
                echo '<div class="inner float-left p-2">
                <a target="blank" href="' . $regImg->imagen . '">
                    <img title="ver" src="../files/img/default.png" width="60px" class="img-thumbnail">
                </a>
            </div>';
            }

            echo '</div></div>';
        }

        break;




    case 'buscarFactura':
        $rspta = $orden->buscarFactura($factura);
        echo json_encode($rspta);
        break;



    case 'mostrarUltimaFactura':
        $rspta = $orden->mostrarUltimaFactura();
        echo json_encode($rspta);
        break;


    case 'mostrarFacturas':
        $rspta = $orden->mostrarFacturas($ids_str);
        echo json_encode($rspta);
        break;

    case 'editarEnvio':
        $nombree = isset($_POST["nombree"]) ? limpiarCadena($_POST["nombree"]) : "";
        $num_idente = isset($_POST["num_idente"]) ? limpiarCadena($_POST["num_idente"]) : "";
        $deptoe = isset($_POST["deptoe"]) ? limpiarCadena($_POST["deptoe"]) : "";
        $ciudade = isset($_POST["ciudade"]) ? limpiarCadena($_POST["ciudade"]) : "";
        $telefonoe = isset($_POST["telefonoe"]) ? limpiarCadena($_POST["telefonoe"]) : "";
        $direccioneD = isset($_POST["direccioneD"]) ? limpiarCadena($_POST["direccioneD"]) : "";
        $correoe = isset($_POST["correoe"]) ? limpiarCadena($_POST["correoe"]) : "";
        $idclienteD = isset($_POST["idclienteD"]) ? limpiarCadena($_POST["idclienteD"]) : "";


        $rspta = $orden->editarEnvio($id_op, $tipo_entrega, $obs_entrega, $obs_pedido, $nombree, $num_idente, $deptoe, $ciudade, $telefonoe, $direccioneD, $correoe, $idclienteD);
        echo $rspta ? "Datos de envio actualizados" : "Datos de envio no se pudo editar";
        break;

    case 'verificarGuiaModificada':

        $respuesta = $orden->verificarGuiaModificada($id_op);
        echo json_encode($respuesta);
        break;



    case 'editarQR':
        $usuario_qr = isset($_POST["usuario_qr"]) ? limpiarCadena($_POST["usuario_qr"]) : "";
        $rspta = $orden->editarQR($id_op, $usuario_qr);
        echo $rspta ? "QR registrado" : "Datos de envio no se pudo editar";
        break;






    case 'listardeParciales':
        $rspta = $orden->listardeParciales($id_ped);
        $data = array();
        while ($reg = $rspta->fetch_object()) {
            $cantidad_faltante = $reg->cantidad - $reg->cantEntregada;

            $data[] = array(
                "0" => $reg->descripcion,
                "1" => $reg->cantidad,
                "2" => $reg->cantEntregada,
                "3" => '<input type="number" min="0" max="' . $cantidad_faltante . '"  name="cantidad_entregar[]" value="0" class="form-control" placeholder="Cantidad a entregar"> 
        <input type="hidden" name="id_producto[]" value="' . $reg->id_producto . '">'
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


    case 'insertarParciales':
        $id_ped = $_POST['id_ped'];
        $productos_entregar = $_POST['productos']; // Capturar el array de productos

        $rspta = $orden->insertarParciales($id_ped, $productos_entregar);
        echo $rspta ? "Cantidad agregada" : "Cantidad no se agregó";
        break;



    case 'guardarEntregaCompleta':
        $rspta = $orden->guardarEntregaCompleta($id_ped);
        echo $rspta ? "Pedido completo agregado" : "Pedido no se pudo registrar";
        break;




    case 'listardeEntregaParciales':
        $rspta = $orden->listardeEntregaParciales($id_ped);
        $data = array();
        $pendientes = "";
        while ($reg = $rspta->fetch_object()) {
            // Separar las entregas parciales, cantidades y usuarios en arrays
            $entregas = explode(',', $reg->entregas);
            $cantidades = explode(',', $reg->cantidades);
            $usuarios = explode(',', $reg->usuarios);

            $pendientes = (float)$reg->cantidadMovimientos - (float)$reg->cantidadTotal;

            // Iniciar la tabla de entregas parciales en una sola fila
            $txParciales = '
    <table class="table table-sm ">
    <tbody>
    <tr>
    <td><srong>Cant</strong>: </td>';

            // Iterar sobre las entregas parciales y agregar todo a la misma fila
            foreach ($entregas as $key => $entrega) {
                $txParciales .= '
        <td><span>' . $cantidades[$key] . '</span></td>';
            }

            $txParciales .= '
    </tr>
    <tr>
    <td><srong>Parcial</strong>: </td>';

            foreach ($entregas as $key => $entrega) {

                if ($entrega == 0) {
                    $entrega = 'Completo';
                } else {
                    $entrega = $entrega;
                }



                $txParciales .= '
        <td>
        <span>' . $entrega . '</span><br>
        <span class="badge badge-info right" title="' . $usuarios[$key] . '">  ' . $usuarios[$key] . '</span>
        </td>';
            }

            // Cerrar la tabla
            $txParciales .= '
    </tr>
    </tbody>
    </table>';

            // Agregar la fila a la tabla DataTable
            $data[] = array(
                "0" => $reg->fechar_reg,
                "1" => $reg->descripcion,
                "2" => $reg->cantidadMovimientos,
                "3" => $pendientes, // Muestra la cantidad total arriba
                "4" => $txParciales         // La tabla mostrará las entregas parciales en una fila
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


    case 'editarEstadoEnvio':
        $rspta = $orden->editarEstadoEnvio($id_op);
        echo $rspta ? "Cantidad agregada" : "Cantidad no se agregó";
        break;




    case 'incrementarTickets':
        $rspta = $orden->incrementarTiquets($id_op);
        echo $rspta;
        break;

    case 'verificarDuplicadoOP':
        $total = isset($_POST["total"]) ? limpiarCadena($_POST["total"]) : "";
        $id_actual = isset($_POST["id_actual"]) ? limpiarCadena($_POST["id_actual"]) : "";
        $rspta = $orden->verificarDuplicadoOP($total, $id_actual);
        echo json_encode($rspta);
        break;
}
