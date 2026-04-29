<?php 
require_once "../modelos/Salida.php";
if (strlen(session_id()) < 1) 
	session_start();

$salida=new Salida();


$id_doc=isset($_POST["id_doc"])? limpiarCadena($_POST["id_doc"]):"";
$motivo=isset($_POST["id_motivo"])? limpiarCadena($_POST["id_motivo"]):"";
$proveedor=isset($_POST["proveedor"])? limpiarCadena($_POST["proveedor"]):"";
$cliente=isset($_POST["cliente"])? limpiarCadena($_POST["cliente"]):"";
$factura=isset($_POST["factura"])? limpiarCadena($_POST["factura"]):"";
$op=isset($_POST["op"])? limpiarCadena($_POST["op"]):"";
$fechav=isset($_POST["fechav"])? limpiarCadena($_POST["fechav"]):"";
$usuario=isset($_POST["usuario"])? limpiarCadena($_POST["usuario"]):"";
$destino=isset($_POST["destino"])? limpiarCadena($_POST["destino"]):"";
$id_saldo=isset($_POST["id_saldo"])? limpiarCadena($_POST["id_saldo"]):"";
$cantidad=isset($_POST["cantidad"])? limpiarCadena($_POST["cantidad"]):"";
$obs=isset($_POST["obs"])? limpiarCadena($_POST["obs"]):"";
$costo=isset($_POST["costo"])? limpiarCadena($_POST["costo"]):"";
$id_sal=isset($_POST["id_sal"])? limpiarCadena($_POST["id_sal"]):"";
$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
$tipos=isset($_POST["tipos"])? limpiarCadena($_POST["tipos"]):"";


switch ($_GET["op"]){
	case 'agregarsalida':

	// if (!empty($id_sal)){
	// 	$id_doc=$id_sal;
	// }
	if (empty($id_doc)){
		$rspta=$salida->insertar($motivo,$proveedor,$cliente,$factura,$op,$fechav,$usuario,$destino,$id_saldo,$cantidad,$obs,$costo,$codigo,$tipos);
		echo json_encode($rspta);
	}
	else {

		$rspta=$salida->insertarDetalle($id_doc,$id_saldo,$cantidad,$costo,$tipos,$codigo);
		echo $rspta ? "Movimiento no se pudo agregar" : "Movimiento agregado";
	}
	break;

	

	case 'anular':
	$tipos=$_REQUEST['tipos'];
	$rspta=$salida->anular($id_doc,$tipos,$obs);
	echo $rspta ? "Movimiento anulado" : "Movimiento no se puede anular";
	break;


	
	case 'mostrarIdsaldo':
	
	$id_saldo = $_REQUEST['id_saldo'];
	$rspta=$salida->mostrarIdsaldo($id_saldo);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	

	case 'quitarDetalle':
	$rspta=$salida->quitarDetalle($id_mov);
	echo $rspta ? "Item retirado" : "Item no se puede retirar";
	break;

	case 'mostrar':
	$tipos=$_REQUEST['tipos'];
	$rspta=$salida->mostrar($id_doc,$tipos);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'mostrarMotivo':
	$tipos=$_REQUEST['tipos'];
	$rspta=$salida->mostrarMotivo($id_doc,$tipos);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':
	$fechaIni=$_REQUEST['fechaIni'];
	$fechaFin=$_REQUEST['fechaFin'];
	$tipos=$_REQUEST['tipos'];
	$rspta=$salida->listar($fechaIni,$fechaFin,$tipos);
 		//Vamos a declarar un array
	$data= Array();
	// && $reg->items==0
	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->id_doc,
			"1"=>$reg->fecha,
			"2"=>$reg->motivo,
			"3"=>$reg->obs,
			"4"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"5"=>($reg->estado)?'<button type="button" class="btn btn-primary"  onclick="mostrar('.$reg->id_doc.',\''.$reg->items.'\')"><i class="fa fa-eye"></i></button> ':'',
			"6"=>($reg->estado==1 )?'<button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalComentario"  onclick="mostrarMotivo('.$reg->id_doc.')"><i class="fa fa-close"></i></button>':''
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	//lista detalles de salidas y salidas
	case 'listardetalle':
	$id_doc=$_REQUEST['id_doc'];
	$tipo=$_REQUEST['tipo'];
	$rspta=$salida->listardetalle($tipo,$id_doc);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>'('.$reg->codigo.')'.$reg->descripcion,
			"1"=>number_format($reg->cantidad),
			"2"=>'<button type="button" class="btn btn-danger" onclick="quitarDetalle('.$reg->id_mov.')"><i class="fa fa-close"></i></button>'
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