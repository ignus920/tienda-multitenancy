<?php 
require_once "../modelos/CalculoPrecio.php";

if (strlen(session_id()) < 1) 
	session_start();

$calculoPrecio=new CalculoPrecio();


$id=isset($_POST["idcp"])? limpiarCadena($_POST["idcp"]):"";
$trm=isset($_POST["trm"])? limpiarCadena($_POST["trm"]):"";
$pKilo=isset($_POST["pKilo"])? limpiarCadena($_POST["pKilo"]):"";
$idp=isset($_POST["idp"])? limpiarCadena($_POST["idp"]):"";
$campo=isset($_POST["campo"])? limpiarCadena($_POST["campo"]):"";




switch ($_GET["op"]){
	case 'agregarCalculoPrecio':

	if (empty($id)){

		$rspta=$calculoPrecio->insertar($trm,$pKilo);
		echo $rspta ? "Agregado" : "No se pudo agregar";
	}
	else {
		$rspta=$calculoPrecio->editar($id,$trm,$pKilo);
		echo $rspta ? "Actualizado" : "No se pudo aActualizado";
	}



	case 'mostrarCalculo':
	$rspta=$calculoPrecio->mostrarCalculo();
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	
}
?>