<?php 
require_once "../modelos/Proveedores.php";

if (strlen(session_id()) < 1) 
	session_start();

$proveedor=new Proveedor();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";



switch ($_GET["op"]){
	case 'guardaryeditar':
	if (empty($id)){
		$rspta=$proveedor->insertar(strtoupper($nombre));
		echo $rspta ? "Proveedor Registrado" : "Proveedor no se pudo registrar";
	}
	else {
		$rspta=$proveedor->editar($id,strtoupper($nombre));
		echo $rspta ? "Proveedor actualizado" : "Proveedor no se pudo actualizar";
	}
	break;

	

	case 'desactivar':
	$rspta=$proveedor->desactivar($id);
	echo $rspta ? "Proveedor Eliminada" : "Proveedor no se puede eliminar";
	break;

	case 'activar':
	$rspta=$proveedor->activar($id);
	echo $rspta ? "Proveedor activada" : "Proveedor no se puede activar";
	break;

	case 'mostrar':
	$rspta=$proveedor->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':

	$rspta=$proveedor->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->nombre,
			"1"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"2"=>($reg->estado)?'<button class="btn btn-warning " data-toggle="modal" data-target="#ModalProveedores" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger " onclick="desactivar('.$reg->id.')"><i class="fa fa-trash"></i></button>':
			'<button class="btn btn-warning "  data-toggle="modal" data-target="#ModalProveedores" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;




		case 'selectProveedores':

	$rspta = $proveedor->selectProveedores();
	echo '<option selected value="0">Seleccione una opción</option>';
	while ($reg = $rspta->fetch_object())
	{
		echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
	}
	break;


		case 'selectProveedoresLista':

	$rspta = $proveedor->selectProveedoresLista();
	echo '<option selected value="">Seleccione una opción</option>';
	while ($reg = $rspta->fetch_object())
	{
		echo '<option value=' . $reg->idproveedor . '>' . $reg->nombre . '</option>';
	}
	break;

}
?>