<?php
require_once "../modelos/Devoluciones.php";

$devoluciones = new Devoluciones();

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










    case 'listar':  ///////////LISTAR DE ALMACEN/////////////

    $limit = 100;

    $consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and o.factura IS NOT NULL GROUP BY p.id_ped, o.id_op";

    if ($selectFiltrar != "" && $datoFiltrar != "") {
        ($selectFiltrar == 'pedido') ? $consulta = " WHERE o.id_op = '$datoFiltrar' " : null;
        ($selectFiltrar == 'cotizacion') ? $consulta = " WHERE p.consecutivo  LIKE '%$datoFiltrar%' " : null;
        ($selectFiltrar == 'cliente') ? $consulta = " WHERE c.nombre LIKE '%$datoFiltrar%' " : null;
    }

    ($festado != "") ? $consulta = $consulta . " AND o.estado='$festado'" : null;

    $rspta = $devoluciones->listar($consulta);
    $data = array();
    $cot = '../reportes/cotimprimir.php?id=';
    $envio = '../reportes/envio.php?id=';
    $pedido = '../reportes/ordenp.php?id=';
    
    while ($reg = $rspta->fetch_object()) {

        //numero de entregas parciales 
        $txentrega="";
        $numero_entrega = $reg->max_numero_entrega;

        if ($numero_entrega>0) {
            $txentrega = $numero_entrega;
        }else{

         $txentrega='C';
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



            $estadoNotaCredito='';


            $btnOpciones =  ' <button class="btn btn-info" title="Ver observaciones" data-toggle="modal" data-target="#modalObsPedido" onclick="mostrarObsPedido(' . $reg->id_op . ')"><span class="fa fa-eye"></span></button>';





            if ($reg->parciales == null) {

                $botonParciees="";

            }else{
                $botonParciees='<button title="Entregado"  data-toggle="modal" data-target="#modaEparciales" onclick="listardeEntregaParciales(' . $reg->id_ped . ')"class="btn bg-gray" style="margin: 1px;"> 
                <span class="badge bg-danger" title="Resumen entrega parcial">'.$txentrega.'</span>
                </button>';
            }


            $btnOpciones .=$botonParciees;


            if ($reg->factura=="") {

                $factura="";

            }else
            {
                $factura='<span class="btn-sm btn-danger mr-1">'.$reg->factura.'</span>';
            }

            $filaModificada = $reg->modificada == 1 ? 'fila-modificada' : '';

            $data[] = array(

                "0" => $reg->id_op.'<br>'.$reg->fecha_reg,
                "1" => ($reg->factura)?'<span class="btn btn-sm bg-red" >'.$factura.'</span><br>'.$reg->fecha_factura.'<br>'.$estadoNotaCredito :  $estadoNotaCredito,
                "2" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega,
                "3" => $reg->vendedor,
                "4" => $reg->formapago,
                "5" => $botonesEstado,
                "6" => $btnOpciones,

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



       
        case 'listardeEntregaParciales':
        $rspta = $devoluciones->listardeEntregaParciales($id_ped);
        $data = array();
        $pendientes="";
        while ($reg = $rspta->fetch_object()) {
    // Separar las entregas parciales, cantidades y usuarios en arrays
            $entregas = explode(',', $reg->entregas);
            $cantidades = explode(',', $reg->cantidades);
            $usuarios = explode(',', $reg->usuarios);

            $pendientes = (float)$reg->cantidadMovimientos-(float)$reg->cantidadTotal;

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

                if($entrega==0){
                    $entrega='Completo';
                }else{
                    $entrega=$entrega;
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

                "id" => $reg->id_producto,
                "fecha" => $reg->fechar_reg,
                "desc" => $reg->descripcion,
                "cant" => $reg->cantidadMovimientos,
                "pend" => $pendientes, // Muestra la cantidad total arriba
                "parc" => $txParciales         // La tabla mostrará las entregas parciales en una fila
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




        case 'registrarDevolucion':
        $id_ped = $_POST['id_ped'];
        $id_producto = $_POST['id'];
        $cantidad = $_POST['cantidad'];
        $motivo = $_POST['motivo'];

        $rspta = $devoluciones->registrarDevolucion($id_ped,$id_producto, $cantidad, $motivo);
        echo $rspta ? "Devolución registrada correctamente" : "No se pudo registrar la devolución";
        break;



    }
