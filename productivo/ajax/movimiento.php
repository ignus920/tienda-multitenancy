<?php
require_once "../modelos/Movimiento.php";

if (strlen(session_id()) < 1)
	session_start();

$movimiento = new Movimiento();


$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$codigo = isset($_POST["codigo"]) ? $_POST["codigo"] : [];



function mostrarDecimalesReales($numero) {
	// Convertimos a string y removemos ceros innecesarios solo a la derecha
	$str = rtrim(rtrim((string)$numero, '0'), '.');
	return $str;
}





switch ($_GET["op"]) {


	case 'subirMovimiento':

		$rspta = $movimiento->subirMovimiento();
		//echo json_encode($spta);

		echo $rspta;

		break;






	case 'subirImportaciones':

		$fechaIni = $_POST['fechaIni'];
		$fechaFin = $_POST['fechaFin'];

		$rspta = $movimiento->subirImportaciones($fechaIni, $fechaFin);
		//echo json_encode($spta);

		echo $rspta;

		break;


	// case 'imagen':
	// $rspta=$movimiento->imagen($id);
	// echo json_encode($rspta);
	// break;


	case 'listarOrders':

		$fechacant = $_GET['fechacant'];


		if ($fechacant == '') {
			$condicion = " ";
		} else {

			$condicion = " WHERE mc.fecha_cant='$fechacant' and p.estado=1";
		}



		$rspta = $movimiento->listarOrders($condicion);


		//Vamos a declarar un array
		$data = array();


		$posicion = 0;

		$titulos = $movimiento->encabezado();
		$data1 = [];
		foreach ($titulos as $row) {
			$data1[] = $row;
		}


		$cant = count($data1);
		// echo $cant;

		while ($reg = $rspta->fetch_object()) {

			if ($reg->tximagen == "" || empty($reg->tximagen)) {

				$img = '';
			} else {

				$img = '<a class="mr-1"   data-toggle="modal" href="#modalFoto" aria-expanded="false" onclick="imagen(' . $reg->codigo . ')">
			<i class="fas fa-images txcolori"></i>

			</a> ';
			}

			$data[] = array(

				"0"=>$reg->txcodigo.'<br>'.$img,
			"1"=>$reg->txdescripcion,
				"2" => $reg->ref_fabrica,
				"3" => $reg->cantidad,
				"4" => $reg->precio,
				"5" => $reg->fecha_cant,
				"6" => $reg->estado,


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







	case 'InsertayEditarCant':
		$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$cantidad = isset($_POST["cantidad"]) ? limpiarCadena($_POST["cantidad"]) : "";

		$rspta = $movimiento->verCodigo($codigo);
		json_encode($rspta);
		$cuantos = $rspta['cuantos'];

		if (empty($cuantos)) {

			$rspta = $movimiento->insertarCantidad($codigo, $cantidad);
			echo $rspta ? "Cantidad registrada" : "Cantidad no se pudo registrar";
		} else {
			$rspta = $movimiento->editarCantidad($codigo, $cantidad);
			echo $rspta ? "Cantidad Actualizada" : "Cantidad no se pudo actualizar";
		}
		break;




	case 'eliminarMovimiento':

		$rspta = $movimiento->eliminarMovimiento();
		echo $rspta ? "Movimiento Eliminado" : "Movimiento no se pudo eliminar";

		break;


	case 'eliminarImportaciones':

		$rspta = $movimiento->eliminarImportaciones();
		echo $rspta ? "Importaciones Eliminadas" : "Importaciones no se pudo eliminar";

		break;




	case 'eliminarMovimientoCantidad':

		$rspta = $movimiento->eliminarMovimientoCantidad();
		echo $rspta ? "Tabla de movimiento Eliminada" : "La tabla de movimiento no se pudo eliminar";

		break;



	case 'totalMovimiento':
		$rspta = $movimiento->totalMovimiento();
		//Codificar el resultado utilizando json
		echo json_encode($rspta);
		break;



	case 'salidaserp':

		$fechaIni = $_POST['fechaIni'];
		$fechaFin = $_POST['fechaFin'];

		$rspta = $movimiento->salidaserp($fechaIni, $fechaFin);
		echo $rspta ? "Salidas ERP actualizadas" : "Salidas ERP no se pudo registrar";
		break;




	case 'fechas':
		$rspta = $movimiento->fechas();
		//codificar el resultado utilizando json
		echo json_encode($rspta);
		break;






	//funcion listar para comercial
	case 'listarInventarioComercio':

		$fechaIni = $_POST['fechaIni'];
		$fechaFin = $_POST['fechaFin'];
		$pedido = "";

		$rspta = $movimiento->listarInventarioComercio($fechaIni, $fechaFin);
		//Vamos a declarar un array
		$data = array();

		// echo $cant;

		while ($reg = $rspta->fetch_object()) {

			$data[] = array(

				"0" => $reg->codigo,
				"1" => $reg->descripcion,
				"2" => $reg->total_enviados,
				"3" => $reg->Pedidos


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






		


	case 'selectOrders':

		$rspta = $movimiento->selectOrders();
		echo '<option selected value="">show all</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->fecha_cant . '>' . $reg->fecha_cant . '</option>';
		}
		break;




	case 'reset':
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$rspta = $movimiento->reset($codigo);
		echo $rspta ? "Reset confirmado" : "Reset no se pudo realizar";

		break;





	case 'resetVarios':

		$rspta = $movimiento->resetVarios($codigo);
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
			$sqlCheck = "SELECT codigo FROM i_movimiento_cantidad WHERE codigo = '$codigo' AND cantidad IS NOT NULL";
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
		$rspta = $movimiento->listarHistorialimportaciones($condicion);
		//Vamos a declarar un array
		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(

				"0" => $reg->cantidad,
				"1" => $reg->fecha_reg
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



































	/*
	FUNCIONES DE LISTAR NUEVAS
	*/

	//funcion listar
	case 'listarInventario':
	$idetiquetas = $_POST['idetiquetas'];
	$tipoF = $_POST['tipoF'];
	$accion = isset($_POST["accion"]) ? limpiarCadena($_POST["accion"]) : 0;
	$modoAgregarProductos = isset($_POST['modoAgregarProductos']) ? filter_var($_POST['modoAgregarProductos'], FILTER_VALIDATE_BOOLEAN) : false;

	// $fechacant = $_POST['fechacant'];
	$searchTerm = isset($_POST["searchTerm"]) ? limpiarCadena($_POST["searchTerm"]) : "";
	// if ($fechacant == '') {
	$condicion = " WHERE p.estado=1 and p.mostrar=1 ";
	// } else {
	// 	$condicion = " WHERE p.estado=1 and m.fecha_cant='$fechacant' and p.mostrar=1 ";
	// }
	 // Filtro por tipo (1 Nacional, 2 Importado)
    if (!empty($tipoF)) {
        $condicion .= " AND p.tipo = '$tipoF'";
    }

	// Lógica del filtro de etiquetas
	if (!empty($idetiquetas)) {
        if (!$modoAgregarProductos) {
            // Modo normal: solo productos de la etiqueta
            $condicion .= " AND p.codigo in (SELECT i.idproducto FROM i_importacion i WHERE i.id_etiqueta = '$idetiquetas')";
        }
        // En modoAgregarProductos = true, mostrar TODOS los productos
        // La diferencia estará en cómo se renderizan (read-only vs seleccionables)
    }
	switch ($accion) {
		case 4:
			$condicion .= " AND ifnull(m.cantidad, 0) > 0 ";
			break;
		case 5:
			$condicion .= " AND p.descripcion LIKE '%LED%' ";
			break;
	}
	$etiquetas = [];
	foreach ($movimiento->encabezado() as $etq) {
		$etiquetas[$etq['etiqueta_id']] = $etq['nombre_etiqueta'];
	}

	// Modificar condición para ordenamiento si está en modo agregar productos
	if ($modoAgregarProductos && !empty($idetiquetas)) {
		$ordenamiento = " ORDER BY (CASE WHEN EXISTS(SELECT 1 FROM i_importacion WHERE idproducto = p.codigo AND id_etiqueta = '$idetiquetas') THEN 0 ELSE 1 END), p.descripcion";
	} else {
		$ordenamiento = " ORDER BY p.descripcion";
	}
	
	$rspta = $movimiento->listarInventario($condicion . $ordenamiento, $idetiquetas);
	$data = array();
	$posicion = 0;
	while ($reg = $rspta->fetch_object()) {
		// parse_str(str_replace(['|', ':'], ['&', '='], $reg->etiquetas_info), $etiquetas_parseadas);

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
			// Verificar si el producto ya tiene la etiqueta actual (siempre que haya etiqueta seleccionada)
			$tieneEtiqueta = false;
			if (!empty($idetiquetas)) {
				$sqlCheck = "SELECT 1 FROM i_importacion WHERE idproducto = '{$reg->codigo}' AND id_etiqueta = '$idetiquetas' LIMIT 1";
				$resultCheck = ejecutarConsulta($sqlCheck);
				$tieneEtiqueta = ($resultCheck->num_rows > 0);
			}

			$btncheck = '';
			if ($tieneEtiqueta) {
				// Producto ya asignado - mostrar read-only con indicador visual
				$btncheckImp = '<div class="inline-elements" style="background-color: #e8f5e8; padding: 5px; border-radius: 3px;">
					<i class="fas fa-check-circle text-success mr-2" title="Ya asignado a esta etiqueta"></i>
					<span style="font-weight: bold;">' . $reg->codigo . '</span>
					<small class="text-muted d-block">Ya en etiqueta</small>
					</div>';
			} else {
				// Producto disponible para seleccionar (solo si está en modo agregar o no hay etiqueta seleccionada)
				if ($modoAgregarProductos || empty($idetiquetas)) {
					$btncheckImp = '<div class="inline-elements">
						<input type="checkbox" class="mr-2 check-producto" title="Seleccionado" name="check[]" value="' . $reg->codigo . '" onchange="obtenerProductosSeleccionados(this)">
						' . $reg->codigo . '
						</div>';
				} else {
					// En modo normal con etiqueta pero sin el producto asignado, no mostrar checkbox
					$btncheckImp = '<div class="inline-elements">
						' . $reg->codigo . '
						</div>';
				}
			}

			$btn = '
				<ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
				<li class="nav-item dropdown mr-2">' . $img . '</li>
				<li class="nav-item dropdown mr-2">' . $txpdf . '</li>
				<li class="nav-item dropdown mr-2">' . $historia . '</li>
				</ul>';

			if ($tieneEtiqueta) {
				// Para productos con etiqueta, usar la cantidad de i_importacion si existe
				$cantidadMostrar = $reg->cantidad_etiqueta ? $reg->cantidad_etiqueta : $reg->cantidad;
				
				// Campo cantidad read-only para productos ya asignados a la etiqueta
				$botones = '<div class="input-group input-group-sm">
							<input type="number"
								name="cantidad" value="' . $cantidadMostrar . '"
								class="form-control"
								readonly
								style="background-color: #f8f9fa; color: #6c757d;"
								title="Cantidad de etiqueta - Producto ya asignado"
								placeholder="Ya asignado">
							<div class="input-group-append">
								<span class="input-group-text"><i class="fas fa-lock"></i></span>
							</div>
						</div>';
			} else {
				// Campo cantidad editable para productos disponibles
				$botones = '<div class="input-group input-group-sm">
							<input type="number"
								onfocus="cargarEtiquetas(\'' . $reg->codigo . '\',\'' . $modoAgregarProductos . '\')"
								onchange="InsertayEditarCant($(this).val(), \'' . $reg->codigo . '\', \'' . $reg->id . '\'); marcarCheckboxAutomatico(\'' . $reg->codigo . '\', $(this).val());"
								name="cantidad" value="' . $reg->cantidad . '"
								class="form-control"
								placeholder="Cantidad">
						</div>';
			}



		$fila = [
			"0" => '<div title="Codigo / Acciones">' . $btncheckImp . '<br>' . $btn . '</div>',
			"1" => '<div title="Descripción">' . $reg->descripcion . '</div>',
			"2" => '<div title="Existencias">' . $reg->existencias . '</div>',
			"3" => '<div title="Cantidad">' . $botones . '</div>',
			"4" => '<div title="%">' . $reg->columna . '</div>',
			// "5" => '<div title="Total salida">' . $reg->total_salida . '</div>',
			// "6" => '<div title="Saldo final">' . $reg->saldo_final . '</div>',
			"5" => '<div title="Salida 7m ERP">' . number_format($reg->salidas_7_meses, 0, '', '.') . '</div>',
			"6" => '<div title="Precio EXW">' . '$' . number_format($reg->exw, 2) . '</div>'
			// "9" => '<div title="Etiquetas">' . ($reg->etiquetas_info ?: '') . '</div>',
		];

		// foreach ($etiquetas as $id => $nombre) {
		//     $fila[] = isset($etiquetas_parseadas[$id]) ? $etiquetas_parseadas[$id] : '0';
		// }

		$fila[] = $reg->transito1 ?? '';
		$fila[] = $reg->transito2 ?? '';
		$fila[] = $reg->transito3 ?? '';
		$fila[] = $reg->transito4 ?? '';

		$data[] = $fila;
		$posicion++;
	}

	echo json_encode([
		"sEcho" => 1,
		"iTotalRecords" => count($data),
		"iTotalDisplayRecords" => count($data),
		"aaData" => $data
	]);
	break;



	 //funcion para mostrar las cantidades
	case 'cantidadesPorProductoPorEtiqueta':
	// Permitir recibir tanto 'codigo' (string) como 'codigos' (array JSON)
	$codigos = null;
	if (isset($_POST['codigos'])) {
		// Si viene como JSON (array de códigos)
		$codigos = json_decode($_POST['codigos'], true);
	} elseif (isset($_POST['codigo'])) {
		$codigos = $_POST['codigo'];
	}

	$data = [];

	if (is_array($codigos)) {
		// Respuesta agrupada por código y etiqueta
		foreach ($codigos as $codigo) {
			$codigoEsc = addslashes($codigo);
			$sql = "
				SELECT
					ie.nombre AS etiqueta,
					SUM(i.cant_sol) AS cantidad,
					MAX(ii.del) AS del,
					MAX(ii.etd) AS etd,
					MAX(ii.via) AS via,
					MAX(ii.transportadora) AS transportadora
					FROM i_importacion  i
                    left join i_picking pi on pi.id=i.id_picking 
					left join i_envios ii on ii.id=pi.idenvio
					INNER JOIN i_etiqueta ie ON i.id_etiqueta = ie.id
					WHERE i.idproducto = '$codigoEsc'
					GROUP BY ie.nombre ";
			$rspta = ejecutarConsulta($sql);
			$data[$codigo] = [];
			while ($reg = $rspta->fetch_object()) {
			$data[$codigo][$reg->etiqueta] = [
				'cantidad' => $reg->cantidad,
				'del' => $reg->del,
				'etd' => $reg->etd,
				'via' => $reg->via,
				'transportadora' => $reg->transportadora
			];
			}

		}
	} elseif (!empty($codigos)) {
		$codigo = addslashes($codigos);
		$sql = "
			SELECT ie.nombre AS etiqueta, SUM(ii.cant_sol) AS cantidad
			FROM i_importacion ii
			INNER JOIN i_etiqueta ie ON ii.id_etiqueta = ie.id
			WHERE ii.idproducto = '$codigo'
			GROUP BY ie.nombre
		";
		$rspta = ejecutarConsulta($sql);
		while ($reg = $rspta->fetch_object()) {
			$data[$reg->etiqueta] = $reg->cantidad;
		}
	}

	echo json_encode($data);
	break;



	case 'resumenEnvioPorProducto':
	$codigo = $_POST['codigo'];
	$sql = "SELECT e.nombre AS etiqueta, i.cant_sol, i.del, i.etd 
			FROM i_importacion i
			INNER JOIN i_etiqueta e ON i.id_etiqueta = e.id
			WHERE i.idproducto = '$codigo'";
	
	$res = ejecutarConsultaArray($sql);

	$resultado = [];
	foreach ($res as $row) {
		$resultado[$row['etiqueta']] = [
			'cant' => $row['cant_sol'],
			'del' => $row['del'],
			'etd' => $row['etd'],
		];
	}

	echo json_encode($resultado);
	break;



	//FUNCION DE INSERTAR EDITAR CANTIDAD 
	case 'insertarCantidad':
		$codigo = $_POST['codigo'];
		$cantidad = $_POST['cantidad'];

		$existe = $movimiento->getIdProducto($codigo); // ← tu función para verificar si ya existe

		if ($existe) {
			$rpta = $movimiento->updateCant($cantidad, $codigo); // actualizar
		} else {
			$rpta = $movimiento->insertCant($cantidad, $codigo); // insertar
		}

		echo json_encode([
			"status" => $rpta ? "ok" : "error",
			"accion" => $existe ? "update" : "insert",
		]);
		break;


	case 'retirarCantidad':
		$productos = json_decode($_POST['productos'], true);
		$errores = [];

		foreach ($productos as $codigo) {
			$mov = $movimiento->getIdProducto($codigo);
			if (!$mov) {
				$errores[] = "No se encontró movimiento para $codigo";
				continue;
			}

			$ok = $movimiento->eliminarCantidad($codigo);
			if (!$ok) {
				$errores[] = "No se pudo actualizar cantidad para $codigo";
			}
		}

		if (count($errores) > 0) {
			echo json_encode(["status" => "error", "message" => implode(", ", $errores)]);
		} else {
			echo json_encode(["status" => "ok", "message" => "Cantidades actualizadas a 0 correctamente."]);
		}
		break;






	//FUNCION PRODUCTOS CON ETIQUETAS 
	case 'productosConEtiqueta':
		$productos = $_POST['productos'];
		$resultado = $movimiento->productosConEtiqueta($productos);
		$codigosConEtiqueta = array_column($resultado, 'idproducto');
		echo json_encode($codigosConEtiqueta);
		break;




	//FUNCION DE ASIGNACION DE ETIQUETAS
	case 'asignarEtiqueta':
		$productos = json_decode($_POST['productos'], true); // array de [{codigo, cantidad}]
		$idetiqueta = $_POST['idetiqueta'];

		$errores = [];
		$duplicados = [];
		$asignados = [];
		$insertados = 0;
		foreach ($productos as $item) {
			$codigo = $item['codigo'];
			$cant_sol = $item['cantidad'];

			// Buscar ID del producto desde el modelo
			$prod = $movimiento->getIdProducto($codigo);
			if (!$prod) {
				$errores[] = "Producto no encontrado: $codigo";
				continue;
			}

			$idproducto = $prod['codigo']; // según cómo traes el ID

			// Validar si ya tiene esa etiqueta
			$yaExiste = $movimiento->existeEtiquetaAsignada($idproducto, $idetiqueta);
			if ($yaExiste) {
				$duplicados[] = $codigo;
				continue;
			}

			// Insertar con cantidad
			$movimiento->insertEtiqueta($idproducto, $idetiqueta, $cant_sol);
			$asignados[] = $codigo;
			$insertados++;
		}

		// Crear mensaje estructurado en columnas
		$mensaje = "";
		
		if (count($duplicados) > 0 && count($asignados) > 0) {
			// Ambos casos: duplicados a la izquierda, asignados a la derecha
			$mensaje = "<div style='display:flex; justify-content:space-between;'>";
			$mensaje .= "<div style='flex:1; padding-right:20px;'>";
			$mensaje .= "<strong>Productos antiguos con la etiqueta:</strong><br>";
			foreach ($duplicados as $codigo) {
				$mensaje .= "<span>" . $codigo . "</span><br>";
			}
			$mensaje .= "</div>";
			$mensaje .= "<div style='flex:1; padding-left:20px;'>";
			$mensaje .= "<strong>Producto nuevo agregado " . count($asignados) . "</strong><br>";
			foreach ($asignados as $codigo) {
				$mensaje .= "<span >" . $codigo . "</span><br>";
			}
			$mensaje .= "</div>";
			$mensaje .= "</div>";
		} elseif (count($asignados) > 0) {
			// Solo asignados
			$mensaje = "<strong >Se asignó la etiqueta correctamente a <br>";
			foreach ($asignados as $codigo) {
				$mensaje .= "<span >" . $codigo . "</span><br>";
			}
		} elseif (count($duplicados) > 0) {
			// Solo duplicados
			$mensaje = "<strong >Productos con la etiqueta:</strong><br>";
			foreach ($duplicados as $codigo) {
				$mensaje .= "<span >" . $codigo . "</span><br>";
			}
		}
		
		if (count($errores) > 0) {
			$mensaje .= (!empty($mensaje) ? "<br><br>" : "") . "<strong>Errores:</strong><br>" . implode("<br>", $errores);
		}

		if ($insertados > 0 || count($duplicados) > 0) {
			echo json_encode(["status" => "ok", "message" => $mensaje]);
		} else {
			echo json_encode(["status" => "error", "message" => $mensaje]);
		}
		break;



	//FUNCION PARA EDITAR ETIQUETA
	case 'cambiarEtiqueta':

		$productos = $_POST['productos'];
		$idetiqueta = $_POST['idetiqueta'];
		$errores = [];

		foreach ($productos as $codigo) {
			$prod = $movimiento->getIdProducto($codigo);
			if (!$prod) {
				$errores[] = "Producto no encontrado: $codigo";
				continue;
			}

			$idproducto = $prod['codigo'];


			// Actualiza la etiqueta existente
			$movimiento->actualizarEtiqueta($idproducto, $idetiqueta);
		}

		if (count($errores) > 0) {
			echo json_encode(["status" => "error", "message" => implode(", ", $errores)]);
		} else {
			echo json_encode(["status" => "ok", "message" => "Etiqueta actualizada correctamente."]);
		}
		break;




	case 'retirarEtiqueta':
		$productos = json_decode($_POST['productos'], true);

		$errores = [];

		foreach ($productos as $codigo) {
			// Obtener ID del producto
			$prod = $movimiento->getIdProducto($codigo);
			if (!$prod) {
				$errores[] = "Producto no encontrado: $codigo";
				continue;
			}

			$idproducto = $prod['codigo'];

			// Eliminar la etiqueta asignada
			$ok = $movimiento->eliminarEtiquetaProducto($idproducto);
			if (!$ok) {
				$errores[] = "No se pudo eliminar etiqueta para: $codigo";
			}
		}

		if (count($errores) > 0) {
			echo json_encode(["status" => "error", "message" => implode(", ", $errores)]);
		} else {
			echo json_encode(["status" => "ok", "message" => "Etiqueta retirada correctamente de los productos."]);
		}
		break;




	//FUNCION VER ENVIOS EN PROCESO
	case 'verEnviosProceso':

		$idestado = isset($_POST['idestado']) ? $_POST['idestado'] : '';
		$productos = isset($_POST['productos']) ? json_decode($_POST['productos'], true) : [];
		$condicion = " WHERE 1=1 ";
		// Si no se envía nada o es '0', mostramos todo
				if ($idestado !== '' && $idestado !== '0') {
			$condicion .= " AND i.estado = '$idestado'";
		}

		if (!empty($productos)) {
			$ids = implode(",", array_map('intval', $productos));
			$condicion .= " AND i.idproducto IN ($ids)";
		}
		$rspta = $movimiento->verEnviosProceso($condicion);
		//Vamos a declarar un array
		$data = array();
		$etiqueta = '';
			$img = '';

		while ($reg = $rspta->fetch_object()) {
			 $etiqueta = $reg->etiquetas;
			if ($reg->tximagen == NULL) {
				$img = '<img class="zoom" src="../files/img/default.png" height="50px" width="50px">';
			} else {
				$img = '<a href="' . $reg->tximagen . '" target="_blank"  title="click to see image">
			<img class="zoom" src="' . $reg->tximagen . '" height="50px" width="50px">
			</a>';
			}
			$data[] = array(

				"0" => $reg->idproducto . '-' . $reg->descripcion,
				"1" => $reg->ref_fabrica,
				"2" => $reg->exw,
				"3" => $img,
				"4" => $reg->cant_sol,
				"5" => $etiqueta,
				"6" => '$' . number_format($reg->precio, 2),
				"7" => $reg->comentario,
				"8" => $reg->txestado,
				"9" => $reg->transportadora,
				"10" => $reg->cant_enviada
				
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


	//SELECT DE ESTADOS DE ENVIO 
	case 'estadoEnvio':

		$rspta = $movimiento->estadoEnvio();
		echo '<option selected value="0">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
		break;


		case 'estadoEnvioProceso':

		$rspta = $movimiento->estadoEnvioProceso();
		echo '<option selected value="0">Seleccione una opción</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
		break;

		case 'estadoEnvioJson':
	$rspta = $movimiento->estadoEnvio();
	$estados = [];

	while ($reg = $rspta->fetch_object()) {
		$estados[$reg->id] = $reg->nombre;
	}

	echo json_encode($estados);
	break;










	
	case 'ocultarProductos':
	$productos = json_decode($_POST['productos'], true);

	$errores = [];

	foreach ($productos as $codigo) {
		// Obtener ID del producto (o trabajar directamente con el código)
		$ok = $movimiento->ocultarProductoPorCodigo($codigo);
		if (!$ok) {
			$errores[] = "No se pudo ocultar el producto: $codigo";
		}
	}

	if (count($errores) > 0) {
		echo json_encode(["status" => "error", "message" => implode(", ", $errores)]);
	} else {
		echo json_encode(["status" => "ok", "message" => "Productos ocultados correctamente."]);
	}
	break;

	case 'movimientosPorMes':
		$codigo = $_POST['codigo'];
		$rspta = $movimiento->obtenerMovimientosPorMes($codigo);

		$meses = [
			1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
			5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
			9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
		];

		$anoActual = date('Y');
		$mesActual = date('n');
		$movimientos = [];

		// Inicializar todos los meses con 0
		for ($i = 1; $i <= 12; $i++) {
			$movimientos[$i] = [
				'mes' => $meses[$i],
				'cantidad' => 0,
				'esAnoAnterior' => false,
				'cantidadAnoAnterior' => 0
			];
		}

		// Llenar con los datos obtenidos
		while ($reg = $rspta->fetch_object()) {
			$mes = (int)$reg->mes;
			$ano = (int)$reg->ano;

			if ($ano == $anoActual) {
				// Datos del año actual
				$movimientos[$mes]['cantidad'] = (int)$reg->cantidad;
				$movimientos[$mes]['esAnoAnterior'] = false;
			} else if ($ano == $anoActual - 1) {
				// Datos del año anterior
				if ($mes == $mesActual) {
					// Para el mes actual, guardar como cantidad del año anterior
					$movimientos[$mes]['cantidadAnoAnterior'] = (int)$reg->cantidad;
				} else if ($mes > $mesActual) {
					// Para meses futuros, usar datos del año anterior
					$movimientos[$mes]['cantidad'] = (int)$reg->cantidad;
					$movimientos[$mes]['esAnoAnterior'] = true;
				}
			}
		}

		// Marcar meses futuros como del año anterior si no tienen datos del año actual
		for ($i = $mesActual + 1; $i <= 12; $i++) {
			if ($movimientos[$i]['cantidad'] == 0) {
				$movimientos[$i]['esAnoAnterior'] = true;
			}
		}

		// IMPORTANTE: El mes actual siempre debe mostrar columna duplicada
		$movimientos[$mesActual]['mostrarDuplicado'] = true;

		echo json_encode([
			'movimientos' => array_values($movimientos),
			'mesActual' => $mesActual,
			'anoActual' => $anoActual
		]);
		break;


		

	case 'obtenerProductosEnTransito':
		$codigoProducto = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";

		$rspta = $movimiento->obtenerProductosEnTransito($codigoProducto);

		$data = array();
		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"idproducto" => $reg->idproducto,
				"cant_enviada" => $reg->cant_enviada,
				"numero_packing" => $reg->numero_packing,
				"eta" => $reg->eta,
				"fecha_reg" => $reg->fecha_reg
			);
		}

		echo json_encode($data);
		break;

	case 'verificarProductoEnEtiqueta':
		$codigo = isset($_POST["codigo"]) ? limpiarCadena($_POST["codigo"]) : "";
		$id_etiqueta = isset($_POST["id_etiqueta"]) ? limpiarCadena($_POST["id_etiqueta"]) : "";

		$rspta = $movimiento->verificarProductoEnEtiqueta($codigo, $id_etiqueta);

		echo json_encode(array("tiene_producto" => $rspta));
		break;

}
