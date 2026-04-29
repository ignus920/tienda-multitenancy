<?php 
require_once "../modelos/Actualizar_precio.php";

if (strlen(session_id()) < 1) 
	session_start();

$actualizar=new Actualizar_precio();

$id_doc=isset($_POST["id_doc"])? limpiarCadena($_POST["id_doc"]):"";
$idlista=isset($_POST["idlista"])? limpiarCadena($_POST["idlista"]):"";
$item=isset($_POST["item"])? limpiarCadena($_POST["item"]):"";
$precio1=isset($_POST["precio1"])? limpiarCadena($_POST["precio1"]):"";
$precio2=isset($_POST["precio2"])? limpiarCadena($_POST["precio2"]):"";
$precio3=isset($_POST["precio3"])? limpiarCadena($_POST["precio3"]):"";
$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
$existencias=isset($_POST["existencias"])? limpiarCadena($_POST["existencias"]):"";



switch ($_GET["op"]){
	case 'agregarPrecio':

	if (empty($idlista)){
		$rspta=$actualizar->insertar($item,$precio1,$precio2,$precio3,$codigo,$existencias);
		echo json_encode($rspta);
	}
	else {
		$rspta=$actualizar->insertarDetalle($idlista,$item,$precio1,$precio2,$precio3,$codigo,$existencias);
		echo $rspta ? "Item agregado" : "Item no se pudo agregar";
	}

	break;

	
	case 'planilla':
	$rspta=$actualizar->planilla($idlista);
	echo $rspta ? "Entrada anulada" : "Entrada no se puede anular";
	break;



	case 'mostrar':
	$rspta=$actualizar->mostrar($idlista);

	echo json_encode($rspta);
	break;



	case 'listar':
	$fechaIni=$_REQUEST['fechaIni'];
	$fechaFin=$_REQUEST['fechaFin'];
	$rspta=$actualizar->listar($fechaIni,$fechaFin);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->idlista,
			"1"=>$reg->fecha,
			"2"=>$reg->nombre,
			"3"=>($reg->estado)?'<span class="btn btn-sm label bg-green" onclick="planilla('.$reg->idlista.')">Sin descargar</span>':
			'<span class="btn btn-sm label bg-red" onclick="planilla('.$reg->idlista.')">Descargado</span>',
			"4"=>'<button type="button" class="btn btn-primary"  onclick="mostrar('.$reg->idlista.')"><i class="fa fa-eye"></i></button> '	
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	//lista detalles de entradas y salidas
	case 'listarpreciodetalle':
	$idlista=$_REQUEST['idlista'];

	$rspta=$actualizar->listarpreciodetalle($idlista);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->descripcion .'/' .$reg->codigo ,
			"1"=>$reg->precio1,
			"2"=>$reg->precio2, 				
			"3"=>$reg->precio3
			
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