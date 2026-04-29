<?php 
require_once "../modelos/Pqr.php";

if (strlen(session_id()) < 1) 
	session_start();

$pqr=new Pqr();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";
$correo=isset($_POST["correo"])? limpiarCadena($_POST["correo"]):"";
$telefono=isset($_POST["telefono"])? limpiarCadena($_POST["telefono"]):"";
$tipo=isset($_POST["tipo"])? limpiarCadena($_POST["tipo"]):"";
$contenido=isset($_POST["contenido"])? limpiarCadena($_POST["contenido"]):"";
$motivo=isset($_POST["motivo"])? limpiarCadena($_POST["motivo"]):"";
$login=isset($_POST["login"])? limpiarCadena($_POST["login"]):"";


//variables editar
$razon=isset($_POST["razon"])? limpiarCadena($_POST["razon"]):"";
$categoria=isset($_POST["categoria"])? limpiarCadena($_POST["categoria"]):"";
$estado=isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";

$estado1=isset($_POST["estado1"])? limpiarCadena($_POST["estado1"]):"";
$fechaIni=isset($_POST["fechaIni"])? limpiarCadena($_POST["fechaIni"]):"";
$fechaFin=isset($_POST["fechaFin"])? limpiarCadena($_POST["fechaFin"]):"";




switch ($_GET["op"]){
	case 'guardaryeditar':
	if (empty($id)){
		if ($login=="") {
			$login=$_SESSION['id'];
		}
		$rspta=$pqr->insertar($nombre,$correo,$telefono,$tipo,$contenido,$motivo,$login);
		echo $rspta? "Pqr creado" : "No se pudo crear el pqr";
	}
	else {
		$rspta=$pqr->editar($id,$razon,$categoria,$estado);
		echo $rspta ? "Pqr actualizado" : "Pqr no se pudo actualizar";
	}
	break;

	case 'desactivar':
	$rspta=$pqr->desactivar($id);
	echo $rspta ? "Pqr Eliminada" : "Pqr no se puede eliminar";
	break;

	case 'activar':
	$rspta=$pqr->activar($id);
	echo $rspta ? "Pqr activada" : "Pqr no se puede activar";
	break;

	case 'mostrar':
	$rspta=$pqr->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listarpqr':

	$fechaIni=$_REQUEST["fechaIni"];
	$fechaFin=$_REQUEST["fechaFin"];
	$estado1=$_REQUEST["estado1"];

	$rspta=$pqr->listarpqr($fechaIni,$fechaFin,$estado1);


 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$gestion='';
		$opciones='';
		switch ($reg->estado) {
			case '1':
		// registrado
			$gestion=$reg->responsable;
			$opciones='<button class="btn btn-warning "  onclick="mostrar('.$reg->id.')">'.$reg->txestado.'</button>';
			break;
			case '2':
		// gestion
			$gestion=$reg->responsable.'<br>'.$reg->razon.'<br>'.$reg->fecha_gestion.' - '.$reg->responsable;
			$opciones='<button class="btn btn-warning "  onclick="mostrar('.$reg->id.')">'.$reg->txestado.'</button>';
			break;
			case '3':
		// solucionado
			$gestion=$reg->responsable.'<br>'.$reg->razon.'<br>'.$reg->fecha_solucion.' - '.$reg->responsable;
			$opciones='<span class="btn-sm label bg-green">'.$reg->txestado.'</span>';
			break;
			case '4':
		// imposibilidad
			$gestion=$reg->responsable.'<br>'.$reg->razon.'<br>'.$reg->fecha_gestion.' - '.$reg->responsable;
			$opciones='<span class="btn-sm label bg-red">'.$reg->txestado.'</span>';
			break;

		}
		$data[]=array(

			"0"=>$reg->nombre.'<br>'.$reg->correo.'<br>'.$reg->telefono,
			"1"=>$reg->registra.'<br>'.$reg->fecha_reg,
			"2"=>'<b>'.$reg->txmotivo.'</b><br>'.$reg->contenido,
			"3"=>$gestion,
			"4"=>$opciones
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