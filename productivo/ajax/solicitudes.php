<?php 
require_once "../modelos/Solicitudes.php";

$solicitudes=new Solicitudes();

$id_sol=isset($_POST["id_sol"])?limpiarCadena($_POST["id_sol"]):"";
$detalle=isset($_POST["detalle"])?limpiarCadena($_POST["detalle"]):"";
$imagensol=isset($_POST["imagensol"])?limpiarCadena($_POST["imagensol"]):"";
$estado=isset($_POST["estado"])?limpiarCadena($_POST["estado"]):"";

$filtro=isset($_POST["filtro"])?limpiarCadena($_POST["filtro"]):"";
$fecha1=isset($_POST["fecha1"])?limpiarCadena($_POST["fecha1"]):"";
$fecha2=isset($_POST["fecha2"])?limpiarCadena($_POST["fecha2"]):"";



switch ($_GET["op"]){
	
	
	case 'insertar':
	if (empty($id_sol)) {


		$ext = explode(".", $_FILES["imagensol"]["name"]);
		if ($_FILES['imagensol']['type'] == "image/jpg" || $_FILES['imagensol']['type'] == "image/jpeg" || $_FILES['imagensol']['type'] == "image/png")
		{
			$imagen = round(microtime(true)) . '.' . end($ext);
			move_uploaded_file($_FILES["imagensol"]["tmp_name"], "../files/solicitud/" . $imagen);
		}

		$rspta=$solicitudes->insertar($detalle, $imagen);
		echo $rspta ?  "Solicitud registrada": "No se guardo el registro";
	}else{
		$rspta=$solicitudes->editar($id_sol, $detalle, $imagen);
		echo $rspta ?  "Solicitud actualizada": "No se actualizo el registro";
	}
	break;

	case 'mostrar':
	$rspta=$solicitudes->mostrar($id_sol);
	echo json_encode($rspta);
	break;

	case 'estado':
	$rspta=$solicitudes->estado($id_sol, $estado);
	echo $rspta ?  "Solicitud actualizada": "No se actualizo el registro";
	break;

	case 'listar':

	$rspta=$solicitudes->listar($fecha1, $fecha2, $filtro);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$imagen='<img src="../files/img/default.jpg" height="50px" width="50px" >';
		if ($reg->imagen!="") {
			$imagen='<a href="../files/solicitud/'.$reg->imagen.'" target="blank"><img class="zoom" src="../files/solicitud/'.$reg->imagen.'" height="50px" width="50px" ></a>';
		}
		$botones="";
		$estado="";
		switch ($reg->estado) {
			case 1:
			$botones='<button title="Procesar" class="btn-flat btn-warning " onclick="estado('.$reg->id_sol.',2)"><i class="fa fa-cogs"></i>';
			$estado='<span class="btn-sm label bg-yellow">'.$reg->txestado.'</span>';
			break;
			case 2:
			$botones='<button title="Declinar" class="btn-flat btn-danger " onclick="estado('.$reg->id_sol.',3)"><i class="fa fa-trash"></i></button> <button title="Aceptar" class="btn-flat btn-primary" onclick="estado('.$reg->id_sol.',4)"><i class="fa fa-check"></i></button>';
			$estado='<span class="btn-sm label bg-green">'.$reg->txestado.'</span>';
			break;
			case 3:
			$estado='<span class="btn-sm label bg-red">'.$reg->txestado.'</span>';
			break;
			case 4:
			$estado='<span class="btn-sm label bg-blue">'.$reg->txestado.'</span>';
			break;

		}
		$data[]=array(

			"0"=>$estado.'<br>'.$reg->fecha_reg,
			"1"=>$reg->login,
			"2"=>$reg->detalle,
			"3"=>$imagen,
			"4"=>$botones,
			"5"=>$reg->procesa.'<br>'.$reg->fecha_procesa,
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