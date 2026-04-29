<?php
session_start();
require_once "../modelos/ListarCliente.php";
$lisatarcliente = new LisatarCliente();




$id_formap = isset($_POST["id_formap"]) ? limpiarCadena($_POST["id_formap"]) : "";
$obs_pedido = isset($_POST["obs_pedido"]) ? limpiarCadena($_POST["obs_pedido"]) : "";
$id_ped = isset($_POST["id_ped"]) ? limpiarCadena($_POST["id_ped"]) : "";





switch ($_GET["op"]) {


    case 'editarFormapago':
    $rspta = $lisatarcliente->editarFormapago($id_formap, $obs_pedido ,$id_ped);
    echo $rspta ? "Datos actualizados" : "Datos no se pudo Actualizar";
    break;


    case 'listarVentas':
    $fechaIni = $_POST['fechaIni'];
    $fechaFin = $_POST['fechaFin'];
    $idcliente=$_SESSION['idcliente'];
    $condicion = " WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' and p.pedido_on_line='1' and cliente='$idcliente' ";

    $cot = '../reportes/cotimprimir.php?id=';
    $cot2 = '../reportes/exCotizaciondos.php?id=';
    $urlq = '../reportes/reporte.php?id=';
    $url1 = 'ordenp.php?m=1&p=';
    $urlc = '../reportes/catalogo.php?id=';
    $rspta = $lisatarcliente->listarVentas($condicion);

   //Vamos a declarar un array
    $data = array();

    while ($reg = $rspta->fetch_object()) {

            // forma de estraer el año de la fecha reg
        $fechaComoEntero = strtotime($reg->fch_reg);
        $anio = date("y", $fechaComoEntero);
            //ERP22001940
        $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;


            $data[] = array(
                //consecutivo
                "0" => '<span style=" white-space: pre;">' . $string . '</span><br>',
                //estado cambio
                "1" => ($reg->estado == 10) ? '<span class="btn  btn-success">'.$reg->estadop.'</span> <br>' . $reg->fch_reg : '<a target="_blank" href="' . $urlq . $reg->id_ped . '" ><button class="btn ' . $reg->class_color . '"><i class="fas fa-qrcode"></i> <br>' . '#OP- ' . $reg->id_op . ' </button> </a><br>' . $reg->fch_reg,

                "2" => '$' . number_format($reg->total) . '<br>' . $reg->obs,
                //imprimir
                "3" => ($reg->observaciones == NULL) ? '<a target="_blank" href="' . $cot . $reg->id_ped . '"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> ' : '<a target="_blank" href="' . $cot . $reg->id_ped . '"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> '. ' <a title="Catalogo" target="_blank" href="' . $urlc . $reg->id_ped . '"><button class="btn btn-primary" ><i class="fas fa-store"></i> Catalogo </button><a>'


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






        case 'editarClaveLogin':
        $clave = isset($_POST["clave"]) ? limpiarCadena($_POST["clave"]) : "";
        $clave1 = isset($_POST["clave1"]) ? limpiarCadena($_POST["clave1"]) : "";
        
        if ($clave === $clave1) {
            $clavehash = hash("SHA256", $clave);
            $rspta = $lisatarcliente->editarClaveLogin($clavehash);
            echo $rspta ? "Contraseña actualizada" : "La contraseña no se pudo actualizar";
        } else {
            echo "Las contraseñas no coinciden";
        }
        break;




    }
