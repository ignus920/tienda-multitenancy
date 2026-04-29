<?php 
require_once "../modelos/MovimientoCopia.php";

if (strlen(session_id()) < 1) 
	session_start();

$movimiento=new Movimiento();


$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$codigo = isset($_POST["codigo"]) ? $_POST["codigo"] : [];

function mostrarDecimalesReales($numero) {
    // Convertimos a string y removemos ceros innecesarios solo a la derecha
    $str = rtrim(rtrim((string)$numero, '0'), '.');
    return $str;
}





switch ($_GET["op"]){


	case 'subirMovimiento':

	$rspta=$movimiento->subirMovimiento();
	//echo json_encode($spta);
	
	echo $rspta;

	break;






	case 'subirImportaciones':

	$fechaIni=$_POST['fechaIni'];
	$fechaFin=$_POST['fechaFin'];

	$rspta=$movimiento->subirImportaciones($fechaIni,$fechaFin);
	//echo json_encode($spta);

	echo $rspta;

	break;






	//funcion listar
	case 'listarInventario':
  // $fechaIni=$_POST['fechaIni'];
  // $fechaFin=$_POST['fechaFin'];

    // $fechacant = $_POST['fechacant'];
	// $tipoF = $_POST['tipoF'];
    $searchTerm = isset($_POST["searchTerm"]) ? limpiarCadena($_POST["searchTerm"]) : "";
    
    // Condición base según la fecha
    // if ($fechacant == '') {
        $condicion = " WHERE p.estado=1 and p.mostrar=1 ";
    // } else {
    //     $condicion = " WHERE p.estado=1 and m.fecha_cant='$fechacant' and p.mostrar=1 ";
    // }
    
    // Filtro por tipo (1 Nacional, 2 Importado)
    // if (!empty($tipoF)) {
    //     $condicion .= " AND p.tipo = '$tipoF'";
    // }

    // Si hay término de búsqueda, modificamos la condición para filtrar solo los que están en movimiento_cantidad
//     if ($searchTerm != "") {

//         $condicion .= " and (p.codigo LIKE '%$searchTerm%' OR p.descripcion LIKE '%$searchTerm%')";
//     }

	$rspta = $movimiento->listarInventario($condicion);

	$data = Array();

	$posicion = 0;

	$titulos = $movimiento->encabezado();
	$data1 = [];
	foreach ($titulos as $row) {
		$data1[] = $row;
	}

	$cant = count($data1);

	while ($reg = $rspta->fetch_object()) {


       // Manejo de imagen
			$img = $reg->tximagen ? '
			<a title="Imagen" class="mr-1" data-toggle="modal" href="#modalFoto" aria-expanded="false" 
			onclick="imagen(' . $reg->idp . ')">
			<i class="fas fa-images txcolori"></i>
			</a>' : '';

           $txpdf =  $reg->txpdf  ? '
			<a title="Pdf" class="mr-1"  href="../files/pdf/'.$reg->txpdf.'" target="_blank" aria-expanded="false" 
			onclick="imagen(' . $reg->idp . ')">
			<i class="fas fa-file-pdf"></i>
			</a>' : '';


			$historia = '
			<a title="Historial" class="mr-1" data-toggle="modal" href="#modaHImportaciones" aria-expanded="false" 
			onclick="mostrarHistrorial(' . $reg->codigo . ')">
			<i class="fas fa-h-square"></i>
			</a>'; 

			$cheocultar='<input type="checkbox" class="mr-1"  title="Ocultar"   value="' . $reg->codigo . '" onchange="seleccionarProducto(this)"';

    // Inicializar el botón de checkbox vacío
        $btncheck = '';
        
        // Solo agregamos el checkbox si hay término de búsqueda y el producto está en movimiento_cantidad
        if ($searchTerm != '' && $reg->cantidad > 0) {
            $btncheck = '<input type="checkbox" class="mr-2" title="Reset seleccionado" name="resetVarios[]"  value="' . $reg->codigo . '" onchange="seleccionar(this)" checked> ';
        }

        	$btn = '
    <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">

    <li class="nav-item dropdown mr-2">' . $img . '</li>

   <li class="nav-item dropdown mr-2">' . $txpdf . '</li>

    <li class="nav-item dropdown mr-2">'. $historia .'</li>


    </ul>';

    	// <li class="nav-item dropdown mr-2">'. $cheocultar .'</li>

    // <button type="submit" class="btn-sm badge-info " onclick="reset(' . $reg->codigo . ')">Reset</button>
	// 				<button class="btn-sm badge-primary" onclick="InsertayEditarCant($(\'#cantidad_' . $reg->codigo . '\').val(), \'' . $reg->codigo . '\', \'' . $reg->id . '\')">' . $reg->cantidad . '</button>


		$data[] = array(
			"0" => $btncheck . $reg->codigo. '<br>'.$btn,
			"1" => $reg->descripcion,
			"2" => $reg->existencias . '<br>' . $reg->id,

			"3" => '<div class="input-group input-group-sm" bis_skin_checked="1">
					<input type="number" 
					onchange="InsertayEditarCant($(this).val(), ' . $reg->codigo . ', ' . $reg->id . ')" 
					name="cantidad" 
                    readonly
					id="cantidad_' . $reg->codigo . '" 
					value="' . $reg->cantidad . '" 
					class="form-control" 
					placeholder="Cantidad">
					<span class="input-group-append"></span>
					</div><br>
					<div class="input-group input-group-sm" bis_skin_checked="1">
					
					</div>',
			"4" => $reg->fecha_cant,
			"5" => $reg->estado,
			"6" => $reg->columna,
			"7" => $reg->total_salida,
			"8" => $reg->saldo_final,
			"9" => $reg->erp,
			"10" => '$' . number_format($reg->precio, 0, '.', ','),
            "11" => '$' . mostrarDecimalesReales($reg->exw)
		);

		switch ($cant) {
			case 1:
			$data[$posicion][] = $reg->transito1;
			break;
			case 2:
			$data[$posicion][] = $reg->transito1;
			$data[$posicion][] = $reg->transito2;
			break;
			case 3:
			$data[$posicion][] = $reg->transito1;
			$data[$posicion][] = $reg->transito2;
			$data[$posicion][] = $reg->transito3;
			break;
			case 4:
			$data[$posicion][] = $reg->transito1;
			$data[$posicion][] = $reg->transito2;
			$data[$posicion][] = $reg->transito3;
			$data[$posicion][] = $reg->transito4;
			break;
		}

		$posicion++;
	}

	$results = array(
    "sEcho" => 1, //Información para el datatables
    "iTotalRecords" => count($data), //enviamos el total registros al datatable
    "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
    "aaData" => $data
);
	echo json_encode($results);

	break;


	// case 'imagen':
	// $rspta=$movimiento->imagen($id);
	// echo json_encode($rspta);
	// break;


	case 'listarOrders':

	$fechacant=$_GET['fechacant'];


	if ($fechacant=='') {
		$condicion = " ";
	}else{

		$condicion = " WHERE mc.fecha_cant='$fechacant' and p.estado=1";
	}



	$rspta=$movimiento->listarOrders($condicion);


 		//Vamos a declarar un array
	$data= Array();


	$posicion=0;

	$titulos = $movimiento->encabezado();
	$data1=[];
	foreach ($titulos as $row) {
		$data1[]=$row;
	}

	
	$cant=count($data1);
	// echo $cant;

	while ($reg=$rspta->fetch_object()){
		
		if ($reg->tximagen=="" || empty($reg->tximagen)) {

			$img='';
		}else{

			$img='<a class="mr-1"   data-toggle="modal" href="#modalFoto" aria-expanded="false" onclick="imagen('.$reg->idp.')">
			<i class="fas fa-images txcolori"></i>

			</a> ';

		}

		$data[]=array(
			
			"0"=>$reg->txcodigo.'<br>'.$img,
			"1"=>$reg->txdescripcion,
			"2"=>$reg->ref_fabrica,
			"3"=>$reg->cantidad,
			"4"=>$reg->precio,
			"5"=>$reg->fecha_cant,
			"6"=>$reg->estado,


		);
		switch ($cant) {
			case 1:
			$data[$posicion][]=$reg->transito1;
			break;
			case 2:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			break;
			case 3:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			$data[$posicion][]=$reg->transito3;
			break;
			case 4:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			$data[$posicion][]=$reg->transito3;
			$data[$posicion][]=$reg->transito4;
			break;
		}

		$posicion++;
	}

	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;







		case 'InsertayEditarCant':
	$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
	$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
	$cantidad=isset($_POST["cantidad"])? limpiarCadena($_POST["cantidad"]):"";

	$rspta = $movimiento->verCodigo($codigo);
$cuantos = isset($rspta['cuantos']) ? intval($rspta['cuantos']) : 0;

if ($cuantos == 0) {
    $rspta = $movimiento->insertarCantidad($codigo, $cantidad);
    echo $rspta ? "Cantidad registrada" : "Cantidad no se pudo registrar";
} else {
    $rspta = $movimiento->editarCantidad($codigo, $cantidad);
    echo $rspta ? "Cantidad Actualizada" : "Cantidad no se pudo actualizar";
}
	break;




	case 'eliminarMovimiento':

	$rspta=$movimiento->eliminarMovimiento();
	echo $rspta ? "Movimiento Eliminado" : "Movimiento no se pudo eliminar";

	break;


	case 'eliminarImportaciones':

	$rspta=$movimiento->eliminarImportaciones();
	echo $rspta ? "Importaciones Eliminadas" : "Importaciones no se pudo eliminar";

	break;




	case 'eliminarMovimientoCantidad':

	$rspta=$movimiento->eliminarMovimientoCantidad();
	echo $rspta ? "Tabla de movimiento Eliminada" : "La tabla de movimiento no se pudo eliminar";

	break;



	case 'totalMovimiento':
	$rspta=$movimiento->totalMovimiento();
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;



	case 'salidaserp':

	$fechaIni=$_POST['fechaIni'];
	$fechaFin=$_POST['fechaFin'];

	$rspta=$movimiento->salidaserp($fechaIni,$fechaFin);
	echo $rspta ? "Salidas ERP actualizadas": "Salidas ERP no se pudo registrar";
	break;




	case 'fechas':
	$rspta= $movimiento-> fechas();
	//codificar el resultado utilizando json
	echo json_encode($rspta);
	break;






		//funcion listar para comercial
	case 'listarInventarioComercio':

	$fechaIni=$_POST['fechaIni'];
	$fechaFin=$_POST['fechaFin'];
	$pedido="";

	$rspta=$movimiento->listarInventarioComercio($fechaIni,$fechaFin);
 		//Vamos a declarar un array
	$data= Array();


	$posicion=0;

	$titulos = $movimiento->encabezado();
	$data1=[];
	foreach ($titulos as $row) {
		$data1[]=$row;
	}

	
	$cant=count($data1);
	// echo $cant;

	while ($reg=$rspta->fetch_object()){

		$data[]=array(
			
			"0"=>$reg->codigo,
			"1"=>$reg->descripcion,
			// "2"=>'$'.number_format($reg->precio,0,',','.'),


		);
		switch ($cant) {
			case 1:
			$data[$posicion][]=$reg->transito1;
			break;
			case 2:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			break;
			case 3:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			$data[$posicion][]=$reg->transito3;
			break;
			case 4:
			$data[$posicion][]=$reg->transito1;
			$data[$posicion][]=$reg->transito2;
			$data[$posicion][]=$reg->transito3;
			$data[$posicion][]=$reg->transito4;
			break;
		}

		$posicion++;
	}

	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;


	case 'selectOrders':

	$rspta = $movimiento->selectOrders();
	echo '<option selected value="">show all</option>';
	while ($reg = $rspta->fetch_object())
	{
		echo '<option value=' . $reg->fecha_cant . '>' . $reg->fecha_cant . '</option>';
	}
	break;




	case 'reset':
	$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
	$rspta=$movimiento->reset($codigo);
	echo $rspta ? "Reset confirmado" : "Reset no se pudo realizar";

	break;





	case 'resetVarios':

	$rspta=$movimiento->resetVarios($_POST["codigo"]);
	echo $rspta ? "Reset no se pudo realizar" : "Reset confirmado";
	break;


	case 'obtenerCodigosPorBusqueda':
	$searchTerm = $_POST['searchTerm'];
	
  // Consulta para obtener los códigos en movimiento que coinciden con la descripción
	$sql = "SELECT codigo FROM movimiento WHERE descripcion LIKE '%$searchTerm%'";
	$result = ejecutarConsulta($sql);
	
	$codigos = array();
	while ($row = $result->fetch_assoc()) {
		$codigo = $row['codigo'];

    // Subconsulta para verificar si el código tiene una cantidad en movimiento_cantidad
		$sqlCheck = "SELECT codigo FROM movimiento_cantidad WHERE codigo = '$codigo' AND cantidad IS NOT NULL";
		$resultCheck = ejecutarConsulta($sqlCheck);
		
    // Si el código existe en movimiento_cantidad con una cantidad, se añade al array
		if ($resultCheck->num_rows > 0) {
			$codigos[] = $codigo;
		}
	}

	echo json_encode(['codigos' => $codigos]);
	break;





    case 'listarHistorialimportaciones':

      
        $condicion = " where s.codigo='$codigo'";



		$rspta=$movimiento->listarHistorialimportaciones($condicion);
			 //Vamos a declarar un array
		$data= Array();
	
		while ($reg=$rspta->fetch_object()){
			$data[]=array(
				
				"0"=>$reg->cantidad,
				"1"=>$reg->fecha_reg
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