<?php
session_start();
require_once "../modelos/ObsCliente.php";
$obscliente = new ObsCliente();




$id = isset($_POST["idc"]) ? limpiarCadena($_POST["idc"]) : "";
$idproducto = isset($_POST["idproducto"]) ? limpiarCadena($_POST["idproducto"]) : "";
$obs_cliente = isset($_POST["obs_cliente"]) ? limpiarCadena($_POST["obs_cliente"]) : "";
$respuestaC = isset($_POST["respuestaC"]) ? limpiarCadena($_POST["respuestaC"]) : "";

$obsComerciales = isset($_POST["obsComerciales"]) ? limpiarCadena($_POST["obsComerciales"]) : "";
$user = $_SESSION['id'];



switch ($_GET["op"]) {


   case 'insertarObsCliente':
    // Obtener los valores de los campos
    $obsComerciales = $_POST['obsComerciales'];
    $obs_cliente = $_POST['obs_cliente'];
    $idproducto = $_POST['idproducto'];

    // Verificar si ambos campos están llenos
    if (!empty($obsComerciales) && !empty($obs_cliente)) {
        // Si ambos están llenos, ejecutar ambas funciones
        $rspta_comerciales = $obscliente->insertarObserComerciales($idproducto, $obsComerciales);
        $rspta_cliente = $obscliente->insertarObserProducto($idproducto, $obs_cliente);
        
        // Verificar si ambas funciones fueron exitosas
        if ($rspta_comerciales && $rspta_cliente) {
            echo "Observaciones registradas correctamente";
        } else {
            echo "Error al registrar las observaciones";
        }
    } elseif (!empty($obsComerciales)) {
        // Si solo obsComerciales está lleno, ejecutar la función para obsComerciales
        $rspta = $obscliente->insertarObserComerciales($idproducto, $obsComerciales);
        
        echo $rspta ? "Observaciones comerciales registradas correctamente" : "Error al registrar las observaciones comerciales";
    } elseif (!empty($obs_cliente)) {
        // Si solo obs_cliente está lleno, ejecutar la función para obs_cliente
        $rspta = $obscliente->insertarObserProducto($idproducto, $obs_cliente);
        
        echo $rspta ? "Observaciones del cliente registradas correctamente" : "Error al registrar las observaciones del cliente";
    } else {
        // Si ninguno está lleno, retornar un mensaje indicando que no se realizaron cambios
        echo "No se han proporcionado observaciones";
    }
    break;




    case 'editarObserProductoEditar':


    $rspta = $obscliente->editarObserProductoEditar($id,$respuestaC);
    echo $rspta ? "Observacion Actualizadas" : "Observacion no se pudo actualizar";

    break;





    case 'mostrarRespuestaCliente':
    $rspta = $obscliente->mostrarRespuestaCliente($id);
    echo json_encode($rspta);
    break;


    case 'listarObsCliente':
    $idproducto = $_POST['idproducto'];
    $userLogin = $_SESSION['nombre'];

    $rspta = $obscliente->listarObsCliente($idproducto);
        //Vamos a declarar un array
    $data = array();

    $contador = 0; // Inicializamos el contador

    while ($reg = $rspta->fetch_object()) {
        $bteditar = '';

        if ($user == 81 ||  $user == 170) {
            $bteditar = '<button class="btn-sm btn-warning" data-toggle="modal" data-target="#modalRespuestaComercial" onclick="mostrarRespuestaCliente(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>' . ' <button class="btn-sm btn-danger" onclick="eliminarObsClien(' . $reg->id . ')"><i class="fa fa-trash"></i></button>';
        }
        $contador++; // Incrementamos el contador en cada iteración

        $data[] = array(
            "0" => $contador.' . '.$reg->obs_cliente,
            "1" => $reg->nombre,
            "2" => $reg->fecha_reg,
            "3" => $reg->respuestaC.'<br>'.$reg->fecha_respuesta.'<br>'.$reg->txnombre,
            "4" => $bteditar
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




    


    case 'eliminarObsClien':


    $rspta = $obscliente->eliminarObsClien($id);

    echo $rspta ? "Observacion eliminada" : "Observacion no se pudo eliminar";

    break;



    case 'verObs':

    $rspta = $obscliente->verObs($idproducto);
    echo json_encode($rspta);
    break;
}
