<?php 
require_once "../modelos/Doc.php";

if (strlen(session_id()) < 1) 
	session_start();

$doc=new Doc();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$imagen=isset($_POST["imagen"])? limpiarCadena($_POST["imagen"]):"";
$sigla=isset($_POST["sigla"])? limpiarCadena($_POST["sigla"]):"";



switch ($_GET["op"]){
	case 'guardaryeditar':



	if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name']))
	{
		$imagen=$_POST["imagenactual"];
	}
	else 
	{
		$ext = explode(".", $_FILES["imagen"]["name"]);
		if ($_FILES['imagen']['type'] == "application/vnd.ms-excel" || $_FILES['imagen']['type'] == "application/pdf")
		{
			$imagen = round(microtime(true)) . '.' . end($ext);
			move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/doc/" . $imagen);
		}
	}
	$nombre=$_FILES["imagen"]["name"];
	if (empty($id)){
		$rspta=$doc->insertar($imagen,$sigla,$nombre);
		echo $rspta ? "Documento Registrado" : "Documento no se pudo registrar";
	}
	else {
		$rspta=$doc->editar($id,$imagen,$sigla,$nombre);
		echo $rspta ? "Documento actualizado" : "Documento no se pudo actualizar";
	}
	break;

	

	case 'mostrar':
	$rspta=$doc->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;



	case 'desactivar':
	
	$rspta=$doc->desactivar($id);
	echo $rspta ? "Documento Eliminado" : "Documento no se puede eliminar";

	break;





	case 'listar':

	$rspta=$doc->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->sigla,
			"1"=>$reg->fecha_reg,
			"2"=>"<a target='blank' href='../files/doc/".$reg->imagen."'><img  src='../files/img/pdf.png' height='50px' width='50px' ></a>",

			"3"=>'<button class="btn btn-warning" data-toggle="modal" data-target="#Modaldoc" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'. ' <button class="btn btn-danger" onclick="desactivar('.$reg->id.')"><i class="fa fa-trash"></i></button>'
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	

	//vinculo de descarga en cotizador
	case 'docTable':

	$rspta=$doc->listar();

	while ($reg = $rspta -> fetch_object())
	{  

		echo"<div class='float-left'>
		<div class='col-12 col-sm-12 col-md-12' style='width: 100%; border-radius: 10px; padding: 10px; margin-bottom: 5px; '>
		<a href='#' onclick='mostrarPDF(".$reg->id.")' title='".$reg->sigla." de ".$reg->fecha_reg."'><span class='letras2'>".$reg->sigla."</span></a>
		</div>
		</div>
		</div>
		</div>
		</div>
		";

		
	}
	
	break;



	





}
?>