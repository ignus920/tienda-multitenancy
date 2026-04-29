<?php 
require_once "../modelos/Imagenes.php";

if (strlen(session_id()) < 1) 
	session_start();

$imagen=new Imagen();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$telefono=isset($_POST["telefono"])? limpiarCadena($_POST["telefono"]):"";
$texto=isset($_POST["texto"])? limpiarCadena($_POST["texto"]):"";
$formato=isset($_POST["formato"])? limpiarCadena($_POST["formato"]):"";



switch ($_GET["op"]){


	case 'guardaryeditar':
	
	$rspta=$imagen->editar($id);
	echo $rspta ? "Imagen Publicada" : "Imagen no se pudo publicar";
	
	break;
	

	case 'listarImagen':
	$url='https://fervicom.com/erp/files/productos/';
	$rspta=$imagen->listarImagen($id);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(



		

			"0"=>"<img src='../files/productos/".$reg->imagen."' height='50px' width='50px' >",
			"1"=>($reg->estado)?'<button class="btn btn-success mr-1" data-toggle="modal" data-target="#modalWhatsapp" onclick="mostrar('.$reg->id.')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button> ' . '<button class="btn btn-primary " onclick="cargarImagen('.$reg->idproducto.',\''.$reg->imagen.'\',\''.$reg->id.'\')">Publicar</button>':'<button class="btn btn-success mr-1" data-toggle="modal" data-target="#modalWhatsapp" onclick="mostrar('.$reg->id.')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button> ' . '<span class="btn btn-success"><i class="fas fa-thumbs-up"></i> Publicada</span>',
		);

	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break; 



	case 'mostrar':
	$rspta=$imagen->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	 //FUNCION BUSCAR UN CLIENTE EN LA BBDD
	case 'search':
	$buscar=$_REQUEST["buscar"];
	$rspta=$imagen->search($buscar);
	echo json_encode($rspta);
	break;




		case 'insertarW':

		$rspta=$imagen->insertarW($texto,$telefono,$formato);
		echo $rspta ? "Cliente actualizado" : "Cliente no se pudo actualizar";
	
	
	break;











}
?>