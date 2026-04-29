<?php 
require_once "../modelos/Encuesta.php";

if (strlen(session_id()) < 1) 
	session_start();

$encuesta=new Encuesta();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";
$pregunta=isset($_POST["pregunta"])? limpiarCadena($_POST["pregunta"]):"";
$observacion=isset($_POST["observacion"])? limpiarCadena($_POST["observacion"]):"";
$pregunta_dos=isset($_POST["pregunta_dos"])? limpiarCadena($_POST["pregunta_dos"]):"";
$observacion2=isset($_POST["observacion2"])? limpiarCadena($_POST["observacion2"]):"";
$pregunta_tres=isset($_POST["pregunta_tres"])? limpiarCadena($_POST["pregunta_tres"]):"";
$observacion3=isset($_POST["observacion3"])? limpiarCadena($_POST["observacion3"]):"";
$pregunta_cuatro=isset($_POST["pregunta_cuatro"])? limpiarCadena($_POST["pregunta_cuatro"]):"";
$observacion4=isset($_POST["observacion4"])? limpiarCadena($_POST["observacion4"]):"";
$pregunta_cinco=isset($_POST["pregunta_cinco"])? limpiarCadena($_POST["pregunta_cinco"]):"";
$observacion5=isset($_POST["observacion5"])? limpiarCadena($_POST["observacion5"]):"";



switch ($_GET["op"]){
	case 'guardaryeditar':
	if (empty($id)){
		$rspta=$encuesta->insertar($nombre,$pregunta,$observacion,$pregunta_dos,$observacion2,$pregunta_tres,$observacion3,$pregunta_cuatro,$observacion4,$pregunta_cinco,$observacion5);
			echo $rspta ? "Encuesta registrada" : "Encuesta no se pudo`registrar";
	}
	else {
		$rspta=$encuesta->editar($id,$nombre,$pregunta,$observacion,$pregunta_dos,$observacion2,$pregunta_tres,$observacion3,$pregunta_cuatro,$observacion4,$pregunta_cinco,$observacion5);
		echo $rspta ? "Encuesta actualizada" : "Encuesta no se pudo actualizar";
	}
	break;

	case 'desactivar':
	$rspta=$encuesta->desactivar($id);
	echo $rspta ? "Encuesta Eliminada" : "Encuesta no se puede eliminar";
	break;

	case 'activar':
	$rspta=$encuesta->activar($id);
	echo $rspta ? "Encuesta activada" : "Encuesta no se puede activar";
	break;

	case 'mostrar':
	$rspta=$encuesta->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':

		$opciones[]=array(
		"1"=>"Insatisfecho",
		"2"=>"Poco satisfecho",
		"3"=>"Satisfecho",
		"4"=>"Muy Satisfecho",
	);



	$rspta=$encuesta->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->id,
			"1"=>$reg->nombre,
			"2"=>$opciones[0][$reg->pregunta],
			"3"=>$opciones[0][$reg->pregunta_dos],
			"4"=>$opciones[0][$reg->pregunta_tres],
			"5"=>$opciones[0][$reg->pregunta_cuatro],
			"6"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"7"=>($reg->estado)?'<button class="btn btn-warning " data-toggle="modal" data-target="#ModalClientes" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger " onclick="desactivar('.$reg->id.')"><i class="fa fa-trash"></i></button>':
			'<button class="btn btn-warning "  data-toggle="modal" data-target="#ModalClientes" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	
	case 'subirClientes':

	$rspta=$clientes->subirClientes();

	echo $rspta ? "La importacion de clientes no se pudo cargar" : "Importacion de clientes finalizado";

	break;





		//Solo clientes activos para modal de domicilios
	case 'listarClientesActivos':
		$rspta=$clientes->listarClientesActivos();
 		//Vamos a declarar un array
 		$data= Array();

 		while ($reg=$rspta->fetch_object()){
 			$data[]=array(
 				"0"=>'<button class="btn btn-warning" onclick="tomar('.$reg->id.')"><i class="fa fa-check"></i></button>',
 				"1"=>$reg->nombre,
 				"2"=>$reg->num_ident,
 				"3"=>$reg->telefonoc.'<br>'.$reg->correoc.'<br>'.$reg->direccionc
 				);
 		}
 		$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
 		echo json_encode($results);

	break;









}
?>