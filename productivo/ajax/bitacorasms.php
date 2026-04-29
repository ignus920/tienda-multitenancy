<?php 
require_once "../modelos/Bitacorasms.php";

if (strlen(session_id()) < 1) 
	session_start();

$bitacora=new Bitacora();


$fechaIni=isset($_POST["fechaIni"])? limpiarCadena($_POST["fechaIni"]):"";
$fechaFin=isset($_POST["fechaFin"])? limpiarCadena($_POST["fechaFin"]):"";




switch ($_GET["op"]){
	



	
	case 'listarBitacora':

    $fechaIni=$_REQUEST["fechaIni"];
	$fechaFin=$_REQUEST["fechaFin"];


	$rspta=$bitacora->listarBitacora($fechaIni,$fechaFin);


 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->id,
			"1"=>$reg->texto,
			"2"=>$reg->telefono,
			"3"=>'<span>'.$reg->usuario.'</span>',
			"4"=>'<span>'.$reg->fecha_reg.'</span>'
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