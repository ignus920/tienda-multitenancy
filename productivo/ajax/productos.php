<?php
require_once "../modelos/Productos.php";

if (strlen(session_id()) < 1)
	session_start();

$productos = new Productos();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$precio1 = isset($_POST["precio1"]) ? limpiarCadena($_POST["precio1"]) : "";
$precio2 = isset($_POST["precio2"]) ? limpiarCadena($_POST["precio2"]) : "";
$precio3 = isset($_POST["precio3"]) ? limpiarCadena($_POST["precio3"]) : "";
$existencias = isset($_POST["existencias"]) ? limpiarCadena($_POST["existencias"]) : "";
$campo = isset($_POST["campo"]) ? limpiarCadena($_POST["campo"]) : "";
$cant_minima = isset($_POST["cant_minima"]) ? limpiarCadena($_POST["cant_minima"]) : "";
$porcentaje = isset($_POST["porcentaje"]) ? limpiarCadena($_POST["porcentaje"]) : "";
$id_proveedor = isset($_POST["id_proveedor"]) ? limpiarCadena($_POST["id_proveedor"]) : "";
$voltaje = isset($_POST["voltaje"]) ? limpiarCadena($_POST["voltaje"]) : "";
$potencia = isset($_POST["potencia"]) ? limpiarCadena($_POST["potencia"]) : "";

$idproducto = isset($_POST["idproducto"]) ? limpiarCadena($_POST["idproducto"]) : "";
$peso = isset($_POST["peso"]) ? limpiarCadena($_POST["peso"]) : "0";
$tximagen = isset($_POST["tximagen"]) ? limpiarCadena($_POST["tximagen"]) : "";
$txpdf = isset($_POST["txpdf"]) ? limpiarCadena($_POST["txpdf"]) : "";
$txurl = isset($_POST["txurl"]) ? limpiarCadena($_POST["txurl"]) : "";
$factor = isset($_POST["factor"]) ? limpiarCadena($_POST["factor"]) : "";
$suma = isset($_POST["suma"]) ? limpiarCadena($_POST["suma"]) : "";
$ref_fabrica = isset($_POST["ref_fabrica"]) ? limpiarCadena($_POST["ref_fabrica"]) : "";
$largo = isset($_POST["largo"]) ? limpiarCadena($_POST["largo"]) : "";
$exw = isset($_POST["exw"]) ? limpiarCadena($_POST["exw"]) : "";
$tipo = isset($_POST["tipo"]) ? limpiarCadena($_POST["tipo"]) : "";
$mostrar = isset($_POST["mostrar"]) ? limpiarCadena($_POST["mostrar"]) : "";
$preciounitarioxcaja = isset($_POST["preciounitarioxcaja"]) ? limpiarCadena($_POST["preciounitarioxcaja"]) : "";

$cantidadxcaja = isset($_POST["cantidadxcaja"]) ? limpiarCadena($_POST["cantidadxcaja"]) : "";






$incr_fletes = isset($_POST["incr_fletes"]) ? limpiarCadena($_POST["incr_fletes"]) : "";
$factor_pvp1 = isset($_POST["factor_pvp1"]) ? limpiarCadena($_POST["factor_pvp1"]) : "";
$factor_pvp_min = isset($_POST["factor_pvp_min"]) ? limpiarCadena($_POST["factor_pvp_min"]) : "";
$desc_max = isset($_POST["desc_max"]) ? limpiarCadena($_POST["desc_max"]) : "";
$porcentaje_precio3 = isset($_POST["porcentaje_precio3"]) ? limpiarCadena($_POST["porcentaje_precio3"]) : "";



function mostrarDecimalesReales($numero) {
    // Convertimos a string y removemos ceros innecesarios solo a la derecha
    $str = rtrim(rtrim((string)$numero, '0'), '.');
    return $str;
}

switch ($_GET["op"]) {

	case 'guardaryeditar':
		// Manejo de la imagen
		if (!file_exists($_FILES['tximagen']['tmp_name']) || !is_uploaded_file($_FILES['tximagen']['tmp_name'])) {
			// Si no se subió una nueva imagen, mantener la imagen actual
			$tximagen = $_POST["imagenactual"];
		} else {
			$ext = pathinfo($_FILES["tximagen"]["name"], PATHINFO_EXTENSION);
			if (in_array($_FILES['tximagen']['type'], array("image/jpg", "image/jpeg", "image/png"))) {
				// Generar un nombre único para la imagen
				$tximagen = round(microtime(true)) . '.' . $ext;
				// Mover el archivo a la carpeta destino
				move_uploaded_file($_FILES["tximagen"]["tmp_name"], "../files/productos/" . $tximagen);
				$tximagen = '../files/productos/' . $tximagen;
			}
		}
		// Manejo del PDF
		if (!file_exists($_FILES['txpdf']['tmp_name']) || !is_uploaded_file($_FILES['txpdf']['tmp_name'])) {
			// Si no se subió un nuevo PDF, mantener el PDF actual
			$txpdf = $_POST["txpdf"];
		} else {
			$ext = pathinfo($_FILES["txpdf"]["name"], PATHINFO_EXTENSION);
			if (in_array($_FILES['txpdf']['type'], array("image/jpg", "image/jpeg", "image/png", "application/pdf"))) {
				// Generar un nombre único para el PDF
				$txpdf = round(microtime(true)) . '.' . $ext;
				// Mover el archivo a la carpeta destino
				move_uploaded_file($_FILES["txpdf"]["tmp_name"], "../files/pdf/" . $txpdf);
			}
		}

		// Resto de tu código para guardar o actualizar los productos
		if (empty($id)) {
			// Código para insertar productos
			$rspta = $productos->insertar($codigo, $descripcion, $precio1, $precio2, $precio3, $existencias, $cant_minima, $porcentaje, $id_proveedor, $voltaje, $potencia, $peso, $tximagen, $txurl, $txpdf, $factor, $ref_fabrica, $largo, $exw, $tipo, $mostrar, $cantidadxcaja,$preciounitarioxcaja,$incr_fletes,$factor_pvp1,$factor_pvp_min,$desc_max,$porcentaje_precio3);
			echo $rspta ? "Productos registrado" : "Productos no se pudo registrar";
		} else {
			// Código para editar productos
			$rspta = $productos->editar($id, $codigo, $descripcion, $precio1, $precio2, $precio3, $existencias, $cant_minima, $porcentaje, $id_proveedor, $voltaje, $potencia, $peso, $tximagen, $txurl, $txpdf, $factor, $ref_fabrica, $largo, $exw, $tipo, $mostrar,$cantidadxcaja, $preciounitarioxcaja,$incr_fletes,$factor_pvp1,$factor_pvp_min,$desc_max,$porcentaje_precio3);
			echo $rspta ? "Productos actualizado" : "Productos no se pudo actualizar";
		}
		break;





	




	case 'alfaNumerico':
		$ubicacion = isset($_POST["ubicacion"]) ? limpiarCadena($_POST["ubicacion"]) : "xx";
		$minimo = isset($_POST["minimo"]) ? limpiarCadena($_POST["minimo"]) : "";
		$maximo = isset($_POST["maximo"]) ? limpiarCadena($_POST["maximo"]) : "";
		$baja = isset($_POST["baja"]) ? limpiarCadena($_POST["baja"]) : "";
		$Capacidad_picking = isset($_POST["Capacidad_picking"]) ? limpiarCadena($_POST["Capacidad_picking"]) : "";
		$rspta = $productos->alfaNumerico($idproducto, $ubicacion, $minimo, $maximo, $baja, $Capacidad_picking);
		echo $rspta ? "Productos actualizado" : "Productos no se pudo actualizar";
		break;




	case 'desactivar':
		$rspta = $productos->desactivar($id);
		echo $rspta ? "Productos Eliminada" : "Productos no se puede eliminar";
		break;

	case 'activar':
		$rspta = $productos->activar($id);
		echo $rspta ? "Productos activada" : "Productos no se puede activar";
		break;

	case 'mostrar':
		$rspta = $productos->mostrar($campo, $id);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;


	case 'ActualizarImagen':
		$rspta = $productos->ActualizarImagen();
		echo json_encode($rspta);
		break;

	case 'mostrarItems':
		$rspta = $productos->mostrarItems($id);
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;

	case 'listar':

		$prestahop = $_REQUEST["prestahop"];

		$rspta = $productos->listar($prestahop);
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {

			$minimo = $reg->minimo;
			$maximo = $reg->maximo;

			if (empty($reg->ubicacion)) {
				$campo = '<bottom class="btn btn-sm btn-primary" style="width: 70%;" data-toggle="modal" data-target="#modalUbicasion" onclick="mostrar(' . $reg->id . ')"><i class="fas fa-map-marker-alt"></i></bottom>';
			} else {
				$contenidoUbicacion = $reg->ubicacion;
				
				// Si existe mínimo o máximo, agregamos el separador y los valores con saltos de línea
		

				$campo = '<bottom class="btn btn-sm btn-primary" style="width: 70%;" data-toggle="modal" data-target="#modalUbicasion" onclick="mostrar(' . $reg->id . ')">' . $contenidoUbicacion . '</bottom>';
			}

			$data[] = array(

				"0" => $reg->codigo,
				"1" => $reg->descripcion,
				"2" => '$' . round($reg->precio1 * 1.19),0,
				"3" => '$' . round($reg->precio2 * 1.19),0,
				"4" => '$' . round($reg->precio3 * 1.19),0,
				"5" => $reg->existencias,
				"6" => $reg->cant_minima,
				"7" => $reg->porcentaje . '%',
				"8" => $reg->voltaje,
				"9" => $reg->potencia,
				"10" => $reg->peso,
				"11" => $reg->factor,
				"12" => $reg->movimiento,
				"13" => $reg->largo,
				"14" => '$' . mostrarDecimalesReales($reg->exw),
				"15" => $reg->proveedor . '</br>' . $reg->ref_fabrica,
				"16" => $campo,
				"17" => $minimo,
				"18" => $maximo,
				"19" => $reg->baja,
				"20" => $reg->Capacidad_picking,
				// "19" => $reg->baja,
				"21" => $reg->cantidadxcaja,
				"22" => ($reg->estado) ? '<span class="btn-sm label bg-green">Activo</span>' :
					'<span class="btn-sm label bg-red">Desactivado</span>',
				"23" => '<button class="btn btn-success btn-sm" onclick="agregarAColaQR(' . $reg->id . ',\'' . $reg->codigo . '\',\'' . addslashes($reg->descripcion) . '\')" title="Agregar a cola"><i class="fas fa-plus"></i></button>' .
					' <button class="btn btn-success btn-sm" onclick="imprimirSoloQR(' . $reg->id . ',\'' . $reg->codigo . '\',\'' . addslashes($reg->descripcion) . '\')" title="Imprimir solo este"><i class="fas fa-print"></i></button>',
				"24" => ($reg->estado) ? '<button class="btn btn-warning"  data-toggle="modal" data-target="#ModalProductos" onclick="mostrar(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>' .
					' <button class="btn btn-danger" onclick="desactivar(' . $reg->id . ')"><i class="fa fa-close"></i></button>' :
					'<button class="btn btn-warning"  data-toggle="modal" data-target="#ModalProductos" onclick="mostrar(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>' .
					' <button class="btn btn-primary" onclick="activar(' . $reg->id . ')"><i class="fa fa-check"></i></button>',
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


	case 'subirProductos':

		$rspta = $productos->porpartes($suma);
		//echo json_encode($spta);

		echo $rspta ? "Importacion de productos finalizado" : "La importacion de productos no se pudo cargar";

		break;



	case 'subirPrecios':

		$rspta = $productos->subirPrecios();
		//echo json_encode($spta);

		echo $rspta ? "La importacion de  precios de promocion no se pudo cargar" : "Importacion de  precios de promocion finalizado";

		break;





	case 'listarProductosActivos':
		$rspta = $productos->listar("");
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => '<button class="btn btn-warning" onclick="tomarProducto(' . $reg->id . ')"><i class="fa fa-check"></i></button>',
				"1" => $reg->codigo . '/' . $reg->descripcion
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


	case 'insertarImagen':

		if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name'])) {
			$imagen = $_POST["imagenactual"];
		} else {
			$ext = explode(".", $_FILES["imagen"]["name"]);
			if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png") {
				$imagen = round(microtime(true)) . '.' . end($ext);
				move_uploaded_file($_FILES["imagen"]["tmp_name"], "files/productos/" . $imagen);
			}
		}

		$rspta = $productos->insertarImagen($id, $imagen);
		echo $rspta ? "Imagen registrada" : "Imagen no se pudo registrar";

		break;

	case 'imagencot':
		$rspta = $productos->imagencot($id);
		echo json_encode($rspta);
		break;

	case 'linkcodigo':
		$rspta = $productos->linkcodigo($codigo);
		echo json_encode($rspta);
		break;


	// case 'buscarprod':
	// 	$rspta = $productos->buscarprod($codigo);
	// 	echo json_encode($rspta);
	// 	break;


	// case 'buscaratri':
	// 	$rspta = $productos->buscaratri($codigo);
	// 	echo json_encode($rspta);
	// 	break;

	case 'productosSelect':
		$rspta = $productos->listar("");

		echo '<option value="0">Seleccione una opción</option>';
		foreach ($rspta as $valueProductos) {

			if ($valueProductos['estado'] == 1) {

				echo '<option value="' . $valueProductos['id'] . '">(' . $valueProductos['codigo'] . ') - ' . $valueProductos['descripcion'] . '</option>';
			}
		}
		break;


	// case 'selectItems':

	// $fechaIni=$_POST["fechaIni"];
	// $fechaFin=$_POST["fechaFin"];

	// $rspta = $productos->selectItems($fechaIni,$fechaFin);
	// echo '<option value="0">Seleccione una opción</option>';
	// while ($reg = $rspta->fetch_object())
	// {
	// 	echo '<option value=' . $reg->id_producto . '>' . $reg->codigo.'-'. $reg->descripcion . '</option>';
	// }
	// break;



	///funciones para el complemento 



	case 'eliminarTabla':
		$tabla = $_POST['idtabla'];
		$rspta = $productos->eliminarTabla($tabla);
		echo $rspta ? "Tabla eliminada" : "tabla no se puede eliminar";
		break;






	case 'listarComplementario':
		$tabla = $_POST['idtabla'];
		$rspta = $productos->listarComplementario($tabla);
		//Vamos a declarar un array
		$data = array();
		while ($reg = $rspta->fetch_object()) {

			$data[] = array(
				"0" => $reg->codigo . '-' . $reg->descripcion,
				"1" => $reg->dato1,
				"2" => $reg->dato2,
				"3" => $reg->dato3,
				"4" => $reg->dato4
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


	case 'encabezados':
		$tabla = $_POST['idtabla'];
		$rspta = $productos->encabezados($tabla);


		// Genera los encabezados de la tabla dinámicamente
		echo "
	<h3 class='p-1' style='text-transform: uppercase;'>" . $rspta['id_prod'] . ' - ' . $rspta['fecha'] . "<h3>
	<th>Producto</th>
	<th> " . $rspta['dato1'] . " </th>
	<th> " . $rspta['dato2'] . " </th>
	<th> " . $rspta['dato3'] . " </th>
	<th> " . $rspta['dato4'] . " </th>";


		break;



	case 'subProductos':

		$rspta = $productos->subProductos();
		//echo json_encode($spta);

		echo $rspta ? "Importacion de productos finalizado" : "La importacion de productos no se pudo cargar";

		break;


	case 'selectTabla':

		$rspta = $productos->selectTabla();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->tabla . '>' . $reg->nombretabla . ' / ' . $reg->fecha . '</option>';
		}
		break;



	case 'selectTablaProveedor':

		$rspta = $productos->selectTablaProveedor();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->tabla . '>' . $reg->nombretabla . ' / ' . $reg->fecha . '</option>';
		}
		break;


	// case 'mostrarTransito':
	// 	$rspta = $productos->mostrarTransito($codigo);
	// 	//Codificar el resultado utilizando json
	// 	echo json_encode($rspta);
	// 	break;






	case 'selectProductos':

		$rspta = $productos->selectProductos();

		echo '<option selected value="0">Seleccione una opción</option>';

		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->codigo . ' / ' . $reg->descripcion . '</option>';
		}
		break;




		case 'consecutivoProdnew':
			$rspta = $productos->consecutivoProdnew(); // Llama al modelo
			$codigos = []; // Array para almacenar los códigos
			
			// Extraer los códigos del resultado de la consulta
			while ($row = $rspta->fetch_assoc()) {
				$codigos[] = $row['codigo'];
			}
			
			// Devolver los códigos como JSON
			echo json_encode($codigos ? $codigos : []); // Asegura que siempre sea un array
			break;







	case 'validarCodigo':
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";

		// Debug: escribir en un archivo de log
		error_log("=== VALIDACIÓN DE CÓDIGO ===");
		error_log("Código recibido: " . $codigo);
		error_log("ID recibido: " . $id);

		$rspta = $productos->validarCodigo($codigo, $id);
		$resultado = $rspta ? "existe" : "disponible";

		error_log("Resultado de validación: " . $resultado);
		echo $resultado;
		break;

	case 'editarImagenProducto':
     try {


        $id = $_POST['id'];
        $codigo = $_POST['codigo']; // SKU del producto
        
        // Validar que se haya subido una imagen
        if (!isset($_FILES['tximagen']) || $_FILES['tximagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode("ERROR: No se subió ninguna imagen o hubo un error en la subida");
            return;
        }
        
        // Validar tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($_FILES['tximagen']['type'], $tiposPermitidos)) {
            echo json_encode("ERROR: Tipo de archivo no válido. Solo se permiten JPG, PNG, GIF y WebP");
            return;
        }
        
        // Validar tamaño de archivo (máximo 5MB)
        $tamañoMaximo = 5 * 1024 * 1024; // 5MB
        if ($_FILES['tximagen']['size'] > $tamañoMaximo) {
            echo json_encode(['success' => false, 'message' => 'El archivo es demasiado grande. Máximo 5MB']);
            return;
        }
        
        // Validar que el código no esté vacío
        if (empty($codigo)) {
            echo json_encode(['success' => false, 'message' => 'Código de producto requerido']);
            return;
        }
        
        // Generar nombre único para la imagen
        $ext = pathinfo($_FILES["tximagen"]["name"], PATHINFO_EXTENSION);
        $nombreArchivo = $codigo . '_' . time() . '.' . $ext;
        $rutaLocal = "files/productos/" . $nombreArchivo;
        
        // Crear directorio si no existe
        $directorioDestino = dirname($rutaLocal);
        if (!file_exists($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }
        
        // Mover el archivo a la carpeta destino
        if (!move_uploaded_file($_FILES["tximagen"]["tmp_name"], $rutaLocal)) {
            echo json_encode(['success' => false, 'message' => 'No se pudo guardar la imagen en el servidor']);
            return;
        }
        
        // PASO 1: Actualizar imagen en ERP PRIMERO
        $rsptaERP = $productos->editarImagenProducto($id, $rutaLocal);
        
        if ($rsptaERP <= 0) {
            // Si falla ERP, eliminar imagen local y salir
            if (file_exists($rutaLocal)) {
                unlink($rutaLocal);
            }
            echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la imagen en el ERP']);
            return;
        }
        
        // PASO 2: Si ERP fue exitoso, ahora actualizar WordPress
        $resAsignacionWP = $productos->asignarImagenPrincipal($codigo, $rutaLocal);
        
        // Verificar resultado de WordPress
        if (strpos($resAsignacionWP, 'ERROR') !== false) {
            // WordPress falló, pero ERP ya se actualizó
            $response = [
                'success' => true, // ERP funcionó
                'warning' => true,
                'id' => $rsptaERP,
                'wp_error' => $resAsignacionWP,
                'message' => 'Imagen actualizada en ERP, pero falló la sincronización con WordPress: ' . $resAsignacionWP,
                'codigo' => $codigo
            ];
            echo json_encode($response);
            return;
        }
        
        // ÉXITO COMPLETO: ambos sistemas actualizados
        $response = [
            'success' => true,
            'id' => $rsptaERP,
            'wp_attachment_id' => $resAsignacionWP,
            'message' => 'Imagen actualizada correctamente en ERP y WordPress',
            'codigo' => $codigo
        ];
        echo json_encode($response);
        
    } catch (Exception $e) {
        // Si hay excepción, intentar limpiar archivo local
        if (isset($rutaLocal) && file_exists($rutaLocal)) {
            unlink($rutaLocal);
        }
        echo json_encode(['success' => false, 'message' => 'Excepción capturada: ' . $e->getMessage()]);
    }
    break;
}
