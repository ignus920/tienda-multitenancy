<?php 
require_once "../modelos/Entrada.php";

if (strlen(session_id()) < 1) 
	session_start();

$entrada=new Entrada();

$id_doc=isset($_POST["id_doc"])? limpiarCadena($_POST["id_doc"]):"";
$tipo=isset($_POST["tipo"])? limpiarCadena($_POST["tipo"]):"";
$motivo=isset($_POST["id_motivo"])? limpiarCadena($_POST["id_motivo"]):"";
$proveedor=isset($_POST["proveedor"])? limpiarCadena($_POST["proveedor"]):"";
$cliente=isset($_POST["cliente"])? limpiarCadena($_POST["cliente"]):"";
$factura=isset($_POST["factura"])? limpiarCadena($_POST["factura"]):"";
$op=isset($_POST["op"])? limpiarCadena($_POST["op"]):"";
$fechav=isset($_POST["fechav"])? limpiarCadena($_POST["fechav"]):"";
$usuario=isset($_POST["usuario"])? limpiarCadena($_POST["usuario"]):"";
$destino=isset($_POST["destino"])? limpiarCadena($_POST["destino"]):"";
$obs=isset($_POST["obs"])? limpiarCadena($_POST["obs"]):"";
$id_saldo=isset($_POST["id_saldo"])? limpiarCadena($_POST["id_saldo"]):"";
$cantidad=isset($_POST["cantidad"])? limpiarCadena($_POST["cantidad"]):"";
$costo=isset($_POST["costo"])? limpiarCadena($_POST["costo"]):"";
$id_mov=isset($_POST["id_mov"])? limpiarCadena($_POST["id_mov"]):"";
$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";


switch ($_GET["op"]){
	case 'agregarEntrada':

	if (empty($id_doc)){
		$rspta=$entrada->insertar($motivo,$proveedor,$cliente,$factura,$op,$fechav,$usuario,$destino,$obs,$id_saldo,$cantidad,$costo,$codigo);
		echo json_encode($rspta);
	}
	else {
		$rspta=$entrada->insertarDetalle($id_doc,$id_saldo,$cantidad,$costo,$codigo);
		echo $rspta ? "Item no se pudo agregar" : "Item agregado";
	}

	break;

	
	case 'anular':
	$rspta=$entrada->anular($id_doc);
	echo $rspta ? "Entrada anulada" : "Entrada no se puede anular";
	break;

	case 'quitarDetalle':
	$rspta=$entrada->quitarDetalle($id_mov);
	echo $rspta ? "Item retirado" : "Item no se puede retirar";
	break;

	case 'mostrar':
	$rspta=$entrada->mostrar($id_doc);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'mostrarMotivo':
	$rspta=$entrada->mostrarMotivo($id_doc);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'listar':
	$fechaIni=$_REQUEST['fechaIni'];
	$fechaFin=$_REQUEST['fechaFin'];
	$rspta=$entrada->listar($fechaIni,$fechaFin);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->id_doc,
			"1"=>$reg->fecha,
			"2"=>$reg->motivo,
			"3"=>$reg->obs,
			"4"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"5"=>($reg->estado)?'<button type="button" class="btn btn-primary"  onclick="mostrar('.$reg->id_doc.')"><i class="fa fa-eye"></i></button> ':'',
			"6"=>($reg->estado==1 )?'<button type="button" class="btn btn-danger"  onclick="anular('.$reg->id_doc.')"><i class="fa fa-close"></i></button>':''
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
	case 'listardetalle':
	$id_doc=$_REQUEST['id_doc'];
	$tipo=$_REQUEST['tipo'];
	$rspta=$entrada->listardetalle($tipo,$id_doc);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->descripcion,
			"1"=>number_format($reg->cantidad),
			// "2"=>$reg->costo, 				
			// "3"=>$reg->costo*$reg->cantidad,
			// "2"=>($reg->existencias-$reg->cantidad>=0 && $tipo='e')?'<button type="button" class="btn btn-danger" onclick="quitarDetalle('.$reg->id_mov.')"><i class="fa fa-close"></i></button>':''
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