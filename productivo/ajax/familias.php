<?php

require_once "../modelos/Familias.php";


if (strlen(session_id()) < 1) 
	session_start();

$familias=new Familias();

$id_familia=isset($_POST["id_familia"])? limpiarCadena($_POST["id_familia"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";
$padre=isset($_POST["padre"])? limpiarCadena($_POST["padre"]):"";





switch ($_GET["op"]){
	case 'guardaryeditar':


	if (empty($id_familia)){
		$rspta=$familias->insertar($nombre,$padre);
		echo $rspta ? "Usuario registrado" : "No se pudieron registrar todos los datos del usuario";
	}
	else {
		$rspta=$familias->editar($id_familia,$nombre,$padre);
		echo $rspta ? "Usuario actualizado" : "Usuario no se pudo actualizar";
	}
	
	break;

	case 'desactivar':
	$rspta=$familias->desactivar($id_familia);
	echo $rspta ? "Usuario Desactivado" : "Usuario no se puede desactivar";
	break;

	case 'activar':
	$rspta=$familias->activar($id_familia);
	echo $rspta ? "Usuario activado" : "Usuario no se puede activar";
	break;


	case 'mostrar':
	$rspta=$familias->mostrar($id_familia);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':
	$rspta=$familias->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			
			"0"=>$reg->nombre,
			"1"=>$reg->padre,
			"2"=>($reg->estado)?'<span class="btn-sm label bg-green">Activado</span>':
			'<span class="btn-sm label bg-red">Desactivado</span>',
			"3"=>($reg->estado)?'<button class="btn btn-warning " data-toggle="modal" data-target="#ModalUsuario" onclick="mostrar('.$reg->id_familia.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger " onclick="desactivar('.$reg->id_familia.')"><i class="fa fa-trash"></i></button>':
			'<button class="btn btn-warning "  data-toggle="modal" data-target="#ModalUsuario" onclick="mostrar('.$reg->id_familia.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activar('.$reg->id_familia.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	

	case 'selectfamilias':

	echo '<option value="">Seleccione una opción</option>';
	
	$rspta = $familia->selectfamilias();

	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->id_familia.'>' . $reg->nombre . '</option>';
	}
	break;









}
?>