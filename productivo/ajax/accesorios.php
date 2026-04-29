<?php 
require_once "../modelos/Accesorios.php";

if (strlen(session_id()) < 1) 
	session_start();

$accesorios = new Accesorios();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$idproducto=isset($_POST["idproducto"])? limpiarCadena($_POST["idproducto"]):"";
$idaccesorio=isset($_POST["idaccesorio"])? limpiarCadena($_POST["idaccesorio"]):"";
$cantidad=isset($_POST["cantidad"])? limpiarCadena($_POST["cantidad"]):"";
$ubicacion=isset($_POST["ubicacion"])? limpiarCadena($_POST["ubicacion"]):"";

$producto=isset($_POST["producto"])? limpiarCadena($_POST["producto"]):"";

$imagen = "";

// Procesar la imagen si se subió
if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] == 0) {
    $uploaddir = "../files/accesorios/";
    $allowed = array('jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp');
    $filename = $_FILES['imagen']['name'];
    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        $newname = date("dmYHis") . "." . $ext;
        $uploadfile = $uploaddir . $newname;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $uploadfile)) {
            $imagen = $newname;
        }
    }
}





switch ($_GET["op"]){

	case 'guardaryeditar':
    if (empty($producto)) {
        $rspta = $accesorios->insertar($idproducto, $idaccesorio, $cantidad, $ubicacion,$imagen);
        echo json_encode($rspta);
    } else {
        if (empty($id)) {
            $rspta = $accesorios->insertarDetalle($idproducto, $idaccesorio, $cantidad, $ubicacion, $imagen);
            echo $rspta ? "Accesorio insertado" : "Accesorio no se pudo insertar";
        } else {
            $rspta = $accesorios->editar($id, $idaccesorio, $cantidad, $ubicacion, $imagen);
            echo $rspta ? "Accesorio Editado" : "Accesorio no se pudo editar";
        }
    }
    break;






	case 'desactivar':
	$rspta=$accesorios->desactivar($id);
	echo $rspta ? "Producto Desactivado" : "Producto no se puede eliminar";
	break;

	case 'activar':
	$rspta=$accesorios->activar($id);
	echo $rspta ? "Producto Activado" : "Producto no se puede activar";
	break;


	case 'desactivarDetalle':
	$rspta=$accesorios->desactivarDetalle($id);
	echo $rspta ? "Accesorio Desactivado" : "Accesorio no se puede eliminar";
	break;

	case 'activarDetalle':
	$rspta=$accesorios->activarDetalle($id);
	echo $rspta ? "Accesorio Activado" : "Accesorio no se puede activar";
	break;

	case 'mostrar':
	$rspta=$accesorios->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'mostrarDetalle':
	$rspta=$accesorios->mostrarDetalle($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	

	case 'listar':

	

	$rspta=$accesorios->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){



		$data[]=array(

			"0"=>$reg->producto,
			"1"=>round($reg->cantdetalles),
			"2"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Desactivado</span>',
			"3"=>($reg->estado)?'<button class="btn btn-warning"  data-toggle="modal" data-target="#ModalAccesorios" onclick="mostrar('.$reg->idproducto.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger" onclick="desactivar('.$reg->idproducto.')"><i class="fa fa-close"></i></button>':''.
			' <button class="btn btn-primary" onclick="activar('.$reg->idproducto.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;




	case 'listarDetalles':


	$rspta=$accesorios->listarDetalles($idproducto);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){

		$imagen_html = "";
		if (!empty($reg->imagen)) {
			$imagen_html = '<a href="../files/accesorios/'.$reg->imagen.'" target="_blank" title="Ver imagen completa">
							<img src="../files/accesorios/'.$reg->imagen.'" alt="Imagen" style="width:50px;height:50px;object-fit:cover;cursor:pointer;border-radius:4px;">
							</a>';
		} else {
			$imagen_html = '<span class="text-muted">Sin imagen</span>';
		}

		$data[]=array(

			"0"=>$reg->accesorio,
			"1"=>round($reg->cantidad),
			"2"=>$reg->ubicacion,
			"3"=>$imagen_html,
			"4"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Desactivado</span>',
			"5"=>($reg->estado)?'<button class="btn btn-warning"   onclick="mostrarDetalle('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger" onclick="desactivarDetalle('.$reg->id.')"><i class="fa fa-close"></i></button>':
			'<button class="btn btn-warning"  onclick="mostrarDetalle('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activarDetalle('.$reg->id.')"><i class="fa fa-check"></i></button>',
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