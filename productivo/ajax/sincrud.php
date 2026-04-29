<?php
require_once "../modelos/Sincrud.php";

$sincrud = new Sincrud();

$id_boton = isset($_POST["id_boton"]) ? limpiarCadena($_POST["id_boton"]) : "";
$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
$vinculo = isset($_POST["vinculo"]) ? limpiarCadena($_POST["vinculo"]) : "";
$color = isset($_POST["color"]) ? limpiarCadena($_POST["color"]) : "";
$orden = isset($_POST["orden"]) ? limpiarCadena($_POST["orden"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$modulo = isset($_POST["modulo"]) ? limpiarCadena($_POST["modulo"]) : "";
$modo = isset($_POST["modo"]) ? limpiarCadena($_POST["modo"]) : "";
$idforma_pago = isset($_POST["idforma_pago"]) ? limpiarCadena($_POST["idforma_pago"]) : "";


switch ($_GET["op"]) {

	case 'selectCiudades':


		$rspta = $sincrud->selectCiudades();
		echo '<option selected value="0">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->cod_ciudad . '>' . $reg->nombre  . '</option>';
		}
		break;



	case 'selectIdentificacion':

		$rspta = $sincrud->selectIdentificacion();
		echo '<option selected value="0">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->code . '>' . $reg->descripcion . '</option>';
		}
		break;




	case 'selectTipopqr':

		$rspta = $sincrud->selectTipopqr();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->motivo . '</option>';
		}
		break;




	case 'selectEstadopqr':

		$rspta = $sincrud->selectEstadopqr();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->descripcion . '</option>';
		}
		break;

	case "selectEstadoPqrFiltro":


		$rspta = $sincrud->selectEstadoPqrFiltro();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '> ' . $reg->descripcion . '</option>';
		}

		break;


	case "estadoCot":

		$rspta = $sincrud->estadoCot();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id_ep . '> ' . $reg->tx_epedido . '</option>';
		}

		break;

	case "selectFpago":

		$rspta = $sincrud->selectFpago();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idforma_pago . '> ' . $reg->nombre . '</option>';
		}

		break;

	case 'selectEntrega':

		$rspta = $sincrud->selectEntrega();
		echo '<option value="">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id_tipo . '>' . $reg->t_nombre  . '</option>';
		}
		break;

	case 'listarFpago':
		//lista de checkbox

		$rspta = $sincrud->listarFpago($idforma_pago);

		//Mostramos la lista de permisos en la vista y si están o no marcados
		while ($reg = $rspta->fetch_object()) {
			echo '<li > <input class="mr-2" type="checkbox" name="listafp[]" onchange="updlista($(this).val())" value="' . $reg->idforma_pago . '">' . $reg->nombre . '</li>';
		}
		break;

	case 'mostrarFpago':
		$rspta = $sincrud->mostrarFpago($idforma_pago);
		echo json_encode($rspta);
		break;

	case 'otros':

		$rspta = $sincrud->otros($modo);
		echo '<option selected value="0">Todos los estados</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id_estado . '>' . $reg->txestado  . '</option>';
		}
		break;

	//crud botones personalizados

	case 'insertaBoton':
		if (empty($id_boton)) {
			$rspta = $sincrud->insertaBoton($titulo, $vinculo, $color, $modulo);
			echo $rspta ?  "boton registrado" : "No se guardo el registro";
		} else {
			$rspta = $sincrud->editarBoton($id_boton, $titulo, $vinculo, $color, $modulo);
			echo $rspta ?  "boton actualizado" : "No se actualizo el registro";
		}
		break;

	case 'mostrarBoton':
		$rspta = $sincrud->mostrarBoton($id_boton);
		echo json_encode($rspta);
		break;

	case 'estadoBoton':
		$rspta = $sincrud->estadoBoton($id_boton, $estado);
		echo $rspta ?  "Boton actualizado" : "No se actualizo el registro";
		break;

	case 'listarBoton':

		$rspta = $sincrud->listarBoton();
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$color = "btn-default";
			if ($reg->estado) {
				$color = $reg->color;
			}
			$muestra = '<a href="' . $reg->vinculo . '" target="blank"><button class="btn ' . $color . ' " >' . $reg->titulo . '</button>';
			$data[] = array(

				"0" => $muestra,
				"1" => $reg->modulo,
				"2" => ($reg->estado) ? '<button class="btn-flat btn-warning " onclick="mostrar(' . $reg->id_boton . ')"><i class="fa fa-pencil"></i></button>' .
					' <button class="btn-flat btn-danger " onclick="estadoBoton(' . $reg->id_boton . ',0)"><i class="fa fa-trash"> Desactivar</i></button>' :
					' <button class="btn-flat btn-primary" onclick="estadoBoton(' . $reg->id_boton . ',1)"><i class="fa fa-check"> Activar</i></button>'
			);
		}
		$results = array(
			"sEcho" => 1, //Información para el datatables
			"iTotalRecords" => count($data), //enviamos el total registros al datatable
			"iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
			"aaData" => $data
		);
		echo json_encode($results);

		break;

	case 'botonera':
		$rspta = $sincrud->botonera($modulo);
		while ($reg = $rspta->fetch_object()) {
			echo ' <a href="' . $reg->vinculo . '" target="blank"><button class="btn ' . $reg->color . ' " >' . $reg->titulo . '</button>';
		}

		break;



	case 'motivoSalida':

		$tipos = $_REQUEST['tipos'];

		$rspta = $sincrud->motivoSalida($tipos);

		echo '<option selected value="0">Seleccione una opcion</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id_motivo . '>' . $reg->nombre . '</option>';
		}
		break;


	// case 'motivoEntrada':

	// 	$rspta = $sincrud->motivoEntrada();

	// 	echo '<option value="0">Seleccione una opción</option>';

	// 	while ($reg = $rspta->fetch_object())
	// 	{
	// 		echo '<option value=' . $reg->id_motivo . '>' . $reg->nombre.'</option>';
	// 	}
	// 	break;


	case 'selectMotivo_caja':

		echo '<option value="0">Seleccione una opción</option>';

		$rspta = $sincrud->selectMotivo_caja();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->idmotivo_caja  . '>' . $reg->nombre . '</option>';
		}
		break;


	//select para traer los proveedores de empresas
	case 'selectProveedor':
		$rspta = $sincrud->selectProveedor();
		echo '<option value="0">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
		break;


	case 'selectTercero':
		echo '<option  value="">Seleccione una opción</option>';
		$rspta = $sincrud->selectTercero();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
		break;




	case 'selectTipo':
		echo '<option  value="">Seleccione una opción</option>';
		$rspta = $sincrud->selectTipo();

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
		break;
}
