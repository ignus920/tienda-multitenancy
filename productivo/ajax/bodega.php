<?php

require_once "../modelos/Bodega.php";


if (strlen(session_id()) < 1) 
	session_start();

$bodega=new Bodega();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";


//variables para envio de coreo y cambio de contraseña

switch ($_GET["op"]){
	

	case 'obtenerProductosBajoStock':
	$rspta=$bodega->obtenerProductosBajoStock();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"id"=>$reg->id,
			"producto"=>$reg->codigo.'-'.$reg->descripcion,
			"existencias"=>$reg->existencias,
			"movimiento"=>$reg->movimiento,
			"porcentaje"=>$reg->porcentaje,
			"nombre"=>$reg->nombre,
			"fecha_mod"=>$reg->fecha_mod
			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;



	case 'confirmarInventario':
    $idProducto = $_POST['idProducto']; // ID del producto
    // $cantidadSolicitada = $_POST['cantidadSolicitada']; // Cantidad solicitada
    $cantidadConfirmada = $_POST['cantidadConfirmada']; // Cantidad confirmada
    $observaciones = $_POST['observaciones']; // Observaciones

    $rspta = $bodega->confirmarInventario($idProducto, $cantidadConfirmada, $observaciones);
    echo $rspta ? "Inventario confirmado correctamente" : "Error al confirmar el inventario";
    break;



    case 'confirmarUpdate':

    // $cantidadSolicitada = $_POST['cantidadSolicitada']; // Cantidad solicitada
    $cantidadConfirmada = $_POST['cantidadConfirmada']; // Cantidad confirmada
    $observaciones = $_POST['observaciones']; // Observaciones

    $rspta = $bodega->confirmarUpdate($id, $cantidadConfirmada, $observaciones);
    echo $rspta ? "Inventario confirmado correctamente" : "Error al confirmar el inventario";
    break;





    case 'solicitarConfirmacion':
    $idProducto = $_POST['idProducto'];
    $cantidadSolicitada = $_POST['cantidadSolicitada'];
    // $observaciones = $_POST['observaciones'];
    $solicitante = $_SESSION['id']; // ID del usuario que está haciendo la solicitud

    // Llama a la función en el modelo
    $rspta = $bodega->solicitarConfirmacion($idProducto, $cantidadSolicitada, $solicitante);

    // Devuelve el resultado
    echo $rspta ? "Solicitud enviada correctamente" : "Error al enviar la solicitud";
    break;



    case 'obtenerConfirmacionInventario':
    $idProducto = $_POST['id_producto']; // Asegúrate de que el nombre coincida
    $rspta = $bodega->obtenerConfirmacionInventario($idProducto);
    echo json_encode($rspta);
    break;



    case 'cambiarEstadoProducto':
    $idProducto = $_POST['id_producto'];
    $estado = $_POST['estado'];

        // Llamamos al modelo de ventas para cambiar el estado
    $rspta = $bodega->cambiarEstadoProducto($idProducto, $estado);

    if ($rspta) {
    	echo json_encode(['status' => true, 'message' => 'Estado actualizado correctamente.']);
    } else {
    	echo json_encode(['status' => false, 'message' => 'Error al actualizar el estado.']);
    }
    break;







   
    case 'listarConfirmar':

    $verTodo = isset($_GET['verTodo']) && $_GET['verTodo'] == 1;
    $rspta = $bodega->listarConfirmar($verTodo);
    
 		//Vamos a declarar un array
    $data= Array();

    while ($reg=$rspta->fetch_object()){
    	$data[]=array(
    "id" => $reg->id,
    "fechaSolicitud" => $reg->fecha_solicitud,
    "txproducto" => '<strong>'.$reg->ubicacion.'-</strong>'.$reg->txproducto,
    "existentes" => $reg->existentes,
    "cantidad_solicitada" => $reg->cantidad_solicitada,
    "nombre" => $reg->nombre,
    "cantidad_confirmada" => $reg->cantidad_confirmada,
    "observaciones" => $reg->observaciones,
    "estado" => ($verTodo ? 2 : 1) // si quieres saber luego si viene del botón "ver todas"
);

    }
    $results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
    echo json_encode($results);

    break;




    case 'listarHistorial':

    $idProducto = $_POST['idProducto'];
    $rspta=$bodega->listarHistorial($idProducto);
        //Vamos a declarar un array
    $data= Array();

    while ($reg=$rspta->fetch_object()){
        $data[]=array(
            "0"=>$reg->fecha_solicitud,
            "1"=>$reg->solicitante_nombre,
            "2"=>$reg->existentes,
            "3"=>$reg->cantidad_solicitada,
            "4"=>$reg->cantidad_confirmada,
            "5"=>$reg->confirmador_nombre,
            "6"=>$reg->observaciones
        );
    }
    $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);
    echo json_encode($results);

    break;



    case 'listarSolicitudesAtendidas':


    // Ejecutar la función listarSolicitudesAtendidas del modelo
    $resultados = $bodega->listarSolicitudesAtendidas();

    $data = array();
    while ($row = $resultados->fetch_object()) {
        $data[] = array(
            "id_producto" => $row->id_producto,
            "txproducto" => $row->txproducto,
            "ultima_confirmacion" => $row->ultima_confirmacion,
            "ultima_cantidad_confirmada" => $row->ultima_cantidad_confirmada,
            "ultima_observacion" => $row->ultima_observacion,
            "ultimo_confirmador_nombre" => $row->ultimo_confirmador_nombre
        );
    }

    // Enviar la respuesta en formato JSON
    echo json_encode(array("data" => $data));
    break;




    




}
?>