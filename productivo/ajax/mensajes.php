<?php 
require_once "../modelos/Mensajes.php";

$mensajes=new Mensajes();

$idmen=isset($_POST["idmen"])?limpiarCadena($_POST["idmen"]):"";
$titulo=isset($_POST["titulo"])?limpiarCadena($_POST["titulo"]):"";
$contenido=isset($_POST["contenido"])?limpiarCadena($_POST["contenido"]):"";
$imagen=isset($_POST["imagen"])?limpiarCadena($_POST["imagen"]):"";
$imgactual=isset($_POST["imgactual"])?limpiarCadena($_POST["imgactual"]):"";
$estado=isset($_POST["estado"])?limpiarCadena($_POST["estado"]):"";
$idmen=isset($_POST["idmen"])?limpiarCadena($_POST["idmen"]):"";
$filtro=isset($_POST["filtro"])?limpiarCadena($_POST["filtro"]):"";




switch ($_GET["op"]){
	
	
	case 'insertar':

	if (!file_exists($_FILES['imagen']['tmp_name']) )
	{
		if ($imgactual!="") {

			$imagen=$imgactual;
		}else{
			$imagen="default.jpg";

		}
	}else{

		$ext = explode(".", $_FILES["imagen"]["name"]);
		if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png")
		{
			$imagen = round(microtime(true)) . '.' . end($ext);
			move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/solicitud/" . $imagen);
		}else{
			$imagen="default.jpg";
			echo "Formato de archivo ".$_FILES["imagen"]["name"]." invalido";
		}
	}

	if (empty($idmen)) {		

		$rspta=$mensajes->insertar($titulo, $contenido, $imagen);
		echo $rspta ?  "Mensaje registrado": "No se guardo el registro";
	}else{
		$rspta=$mensajes->editar($idmen, $titulo, $contenido, $imagen);
		echo $rspta  ?  "Mensaje actualizado": "No se actualizo el registro";
	}
	break;

	case 'mostrar':
	$rspta=$mensajes->mostrar($idmen);
	echo json_encode($rspta);
	break;

	case 'estado':
	$rspta=$mensajes->estado($idmen, $estado);
	echo $rspta ?  "Mensaje actualizado": "No se actualizo el registro";
	break;

	case 'listar':

	$rspta=$mensajes->listar($filtro);
	$dominio="https://fervicom.com/erp_prueba";
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$imagen='<img src="../files/img/default.jpg" height="50px" >';

		if ($filtro==1) {

			$data[]=array(

				"0"=>'<b>'.$reg->titulo.'</b><br>'.$reg->contenido,
				"1"=>'<img class="zoom" src="../files/solicitud/'.$reg->imagen.'" height="50px">',
				"2"=>'<button title="Copiar" class="btn-flat btn-primary " onclick="copiares('.$reg->idmen.')"><i class="fa fa-copy"></i></button>',
			);
		}else{
			$data[]=array(

				"0"=>$reg->titulo,
				"1"=>$reg->contenido,
				"2"=>'<a href="../files/solicitud/'.$reg->imagen.'" target="blank"><img class="zoom" src="../files/solicitud/'.$reg->imagen.'" height="50px" ></a>',
				"3" =>($reg->estado)?'<button title="Desactivar" class="btn-flat btn-success " onclick="estado('.$reg->idmen.',0)">Activo</button> <button title="Editar" class="btn btn-warning " onclick="mostrar('.$reg->idmen.')"><i class="fa fa-pencil"></i></button>':'<button title="Activar" class="btn-flat btn-danger " onclick="estado('.$reg->idmen.',1)">Inactivo</button>',
				"4"=>$reg->login.'<br>'.$reg->fecha_reg
			);
		}
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