
<?php
if (strlen(session_id())<1)
	session_start(); 
require_once "../modelos/Escritorio.php";

$escritorio = new Escritorio();
error_reporting(E_ALL);

$idcaja=isset($_POST["idcaja"])?limpiarCadena($_POST["idcaja"]):"";
$id_producto=isset($_POST["id_producto"])?limpiarCadena($_POST["id_producto"]):"";




switch ($_GET["op"]){

	case 'totalventasDia':
	$rspta=$escritorio->totalventasDia();
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;




	case 'cantPcreados':
	$rspta=$escritorio->cantPcreados();
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'meseroactivo':
	$rspta=$escritorio->meseroactivo();
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;





	case 'ListarInventario':

	$rspta=$escritorio->ListarInventario();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
 			  
			"0"=>"<img class='zoom' src='../files/productos/".$reg->foto."' height='50px' width='50px' >",
 				//precios

			"1"=>$reg->tx_categoria,
			"2"=>$reg->nombre,

			"3"=>($reg->saldo<>0)?'<a><span class="btn-sm label bg-green" >'.$reg->saldo.'</span></a>':
 				'<a><span class="btn-sm label bg-red" >'.$reg->saldo.'</span></a>',
			
 	

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