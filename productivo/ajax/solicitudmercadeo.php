<?php

require_once "../modelos/SolicitudMercadeo.php";
require_once "../ajax/funciones.php";



if (strlen(session_id()) < 1)
	session_start();

$solicitudMercadeo = new SolicitudMercadeo();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$solicitud = isset($_POST["solicitud"]) ? limpiarCadena($_POST["solicitud"]) : "";
$id_user_respuesta = $_SESSION['id'];
$fecha1 = isset($_POST["fecha1"]) ? limpiarCadena($_POST["fecha1"]) : "";
$fecha2 = isset($_POST["fecha2"]) ? limpiarCadena($_POST["fecha2"]) : "";
$solicitud_respuesta = isset($_POST["solicitud_respuesta"]) ? limpiarCadena($_POST["solicitud_respuesta"]) : "";
$userglobal = isset($_POST["userglobal"]) ? limpiarCadena($_POST["userglobal"]) : "";
$id_producto = isset($_POST["id_producto"]) ? limpiarCadena($_POST["id_producto"]) : "";
$titulo = isset($_POST["titulo"]) ? limpiarCadena($_POST["titulo"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$id_solicitud = isset($_POST["idsolicitud"]) ? limpiarCadena($_POST["idsolicitud"]) : "";
$comentario = isset($_POST["comentario"]) ? limpiarCadena($_POST["comentario"]) : "";
$idtipo = isset($_POST["idtipo"]) ? limpiarCadena($_POST["idtipo"]) : "";
$ids = isset($_POST["ids"]) ? limpiarCadena($_POST["ids"]) : "";
$id_user = $_SESSION['id'];



function getColorFromUserId($userId)
{
	// Asignar un color específico para el usuario 81
	if ($userId == 81) {
		return '#34A853'; // Color verde exclusivo para el usuario 81
	}

	// Paleta de colores predefinida (colores neutros y legibles)
	$colorPalette = [
		'#4285F4', // Azul
		'#DB4437', // Rojo
		'#F4B400', // Amarillo
		'#0F9D58', // Verde oscuro
		'#AB47BC', // Púrpura
		'#FF7043', // Naranja
		'#03A9F4', // Cian
		'#9E9D24', // Verde oliva
		'#5C6BC0', // Azul índigo
		'#EC407A', // Rosa
	];

	// Obtener un índice basado en el id_user
	$index = $userId % count($colorPalette);

	// Devolver el color correspondiente al índice
	return $colorPalette[$index];
}


switch ($_GET["op"]) {


	case 'guardaryeditar':
		if (empty($id)) {
			// Crear nueva solicitud
			$titulo = substr($comentario, 0, 20);
			$id_solicitud = $solicitudMercadeo->insertar($id_producto, $titulo, $estado, $idtipo);

			if ($id_solicitud) {
				$rspta1 = $solicitudMercadeo->insertarDetalle($id_solicitud, $comentario, $id_user, $estado);

				if ($rspta1) {
					// ✅ Insertar notificación solo si todo fue exitoso
					$rolDestino = obtenerRolPorIdTipoSolicitud($idtipo);

					if ($rolDestino) {
						crearNotificacion(
							"Nueva Solicitud de $rolDestino",
							"Hay una nueva solicitud pendiente de revisión",
							strtolower($rolDestino), // módulo en minúsculas
							$rolDestino
						);
					}

					echo "Solicitud registrada";
				} else {
					echo "Solicitud no se pudo registrar (detalle)";
				}
			} else {
				echo "Solicitud no se pudo registrar (cabecera)";
			}
		} else {
			// Editar solicitud existente
			$rspta = $solicitudMercadeo->editarEstado($id, $estado);

			if ($rspta) {
				if (!empty($ids)) {
					// Editar comentario ya existente
					$rspta2 = $solicitudMercadeo->editarDetalle($ids, $comentario);
					echo $rspta2 ? "Comentario actualizado" : "Comentario no se pudo actualizar";
				} else {
					// Insertar nuevo comentario
					$rspta2 = $solicitudMercadeo->insertarDetalle($id, $comentario, $id_user, $estado);
					if ($rspta2) {
						// ✅ También puede notificarse si aplica en edición
						$rolDestino = obtenerRolPorIdTipoSolicitud($idtipo);

						if ($rolDestino) {
							crearNotificacion(
								"Nueva Solicitud de $rolDestino",
								"Hay una nueva solicitud pendiente de revisión",
								strtolower($rolDestino), // módulo en minúsculas
								$rolDestino
							);
						}

						echo "Solicitud registrada";
					} else {
						echo "Comentario no se pudo registrar";
					}
				}
			}
		}
		break;










	case 'mostrarDetall':
		$rspta = $solicitudMercadeo->mostrarDetall($id);
		echo json_encode($rspta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		break;


	case 'mostrarMercadeo':
		$rspta = $solicitudMercadeo->mostrarMercadeo($id);
		echo json_encode($rspta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		break;




	case 'listar':
		$idAdmin = $_SESSION['id'];

		$fecha1 = $_POST['fecha1'] ?? '';
		$fecha2 = $_POST['fecha2'] ?? '';
		$estado = $_POST['estado'] ?? '';
		$idtipo = $_POST['idtipo'] ?? '';
		$forzarSinFechas = isset($_POST['forzarSinFechas']) ? $_POST['forzarSinFechas'] : 0;

		$condicion = " WHERE 1=1 ";

		if (!$forzarSinFechas && !empty($fecha1) && !empty($fecha2)) {
			$condicion .= " AND date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' ";
		}

		if (!empty($estado)) {
			$condicion .= " AND s.estado = '$estado' ";
		}

		// Si es admin especial (81 o 162)
		if (($idAdmin == 81 || $idAdmin == 162) && !empty($idtipo)) {
			$condicion .= " AND s.idtipo = '$idtipo' ";
		} elseif ($idAdmin == 81 || $idAdmin == 162) {
			// Administradores ven todas las solicitudes sin filtro por departamento
		} else {
			// Para usuarios normales, filtrar por sus departamentos asignados
			if (isset($_SESSION['departamentos_ids']) && !empty($_SESSION['departamentos_ids'])) {
				$departamentos_ids = $_SESSION['departamentos_ids'];
				$tiposStr = implode(",", $departamentos_ids);
				$condicion .= " AND s.idtipo IN ($tiposStr) ";
			} else {
				// Si no tiene departamentos asignados, devolver vacío
				echo json_encode([
					"sEcho" => 1,
					"iTotalRecords" => 0,
					"iTotalDisplayRecords" => 0,
					"aaData" => []
				]);
				break;
			}
		}

		$rspta = $solicitudMercadeo->listar($condicion);
		$data = [];

		while ($reg = $rspta->fetch_object()) {
			$estadoTexto = "";
			$estadoColor = "";

			switch ($reg->estado) {
				case 1:
					$estadoTexto = "Registrado";
					$estadoColor = "bg-green";
					break;
				case 2:
					$estadoTexto = "Respuesta";
					$estadoColor = "bg-yellow";
					break;
				case 3:
					$estadoTexto = "Solucionado";
					$estadoColor = "bg-blue";
					break;
				case 4:
					$estadoTexto = "Imposibilidad";
					$estadoColor = "bg-danger";
					break;
				case 5:
					$estadoTexto = "Archivado";
					$estadoColor = "bg-secondary";
					break;
				case 6:
					$estadoTexto = "Aprobado";
					$estadoColor = "bg-cyan";
					break;
			}

			$data[] = [
				"0" => $reg->id,
				"1" => $reg->fecha_reg,
				"2" => '<span>' . $reg->txproducto . '</span>'  ,
				"3" => '<span class="btn-sm label-info">' . $reg->txpermiso . '</span>',
				"4" => '<span class="btn-sm label ' . $estadoColor . '">' . $estadoTexto . '</span>',
				"5" => '<button class="btn btn-warning" data-toggle="modal" data-target="#modalSolicitudesmercadeo" 
                        onclick="mostrarMercadeo(' . $reg->id . ',\'' . $reg->estado . '\'); limpiarSolicitudmercadeo();">
                        <i class="fa fa-pencil"></i></button>'
			];
		}

		echo json_encode([
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		]);
		break;









	case 'listarDetalle':
		// Recibimos el id de entrada
		$rspta = $solicitudMercadeo->listarDetalle($id);

		echo '<div class="historial-container">'; // Contenedor principal

		while ($reg = $rspta->fetch_object()) {
			$estadoTexto = "";
			$estadoColor = "";
			switch ($reg->estado_sol) {
				case 1:
					$estadoTexto = "Registrado";
					$estadoColor = "badge-success";
					break;
				case 2:
					$estadoTexto = "Respuesta";
					$estadoColor = "badge-warning";
					break;
				case 3:
					$estadoTexto = "Solucionado";
					$estadoColor = "badge-primary";
					break;
				case 4:
					$estadoTexto = "Imposibilidad";
					$estadoColor = "badge-danger";
					break;

				case 6:
					$estadoTexto = "Aprobado";
					$estadoColor = "bg-cyan";
					break;
			}

			$solicitud_decoded = html_entity_decode($reg->comentario, ENT_QUOTES, 'UTF-8');
			$userColor = getColorFromUserId($reg->id_user);

			echo '<div class="historial-item">';
			echo '  <h6 class="mt-0 mb-1" style="color: ' . $userColor . '; display: flex; justify-content: space-between; align-items: center;">';
			echo      $reg->nombre;
			echo '    <span>';
			echo '      <span class="badge ' . $estadoColor . '">' . $estadoTexto . '</span>';

			// 👇 Solo mostrar botón si el usuario actual es el creador del comentario
			if ($reg->id_user == $id_user) {
				echo '      <a class="btn btn-xs btn-link btneditaras" title="Comentar" style="color:#333;" onclick="mostrarDetall(' . $reg->id_user . ')">';
				echo '        <i class="fas fa-comments"></i>';
				echo '      </a>';
			}

			echo '    </span>';
			echo '  </h6>';
			echo '  <small class="text-muted">' . $reg->fecha . '</small>';
			echo '  <p class="mb-0">' . nl2br($solicitud_decoded) . '</p>';
			echo '</div>';
		}

		echo '</div>'; // Cierre del contenedor principal
		break;












	case 'listarProd':

		$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and s.id_producto='$id_producto'";
		$contador = 1;
		$rspta = $solicitudMercadeo->listar($condicion);

		$data = array();

		while ($reg = $rspta->fetch_object()) {
			$estadoTexto = "";
			$estadoColor = "";
			switch ($reg->estado) {
				case 1:
					$estadoTexto = "Registrado";
					$estadoColor = "bg-green";
					break;
				case 2:
					$estadoTexto = "Respuesta";
					$estadoColor = "bg-yellow";
					break;
				case 3:
					$estadoTexto = "Solucionado";
					$estadoColor = "bg-blue";
					break;
				case 4:
					$estadoTexto = "Imposibilidad";
					$estadoColor = "bg-danger";
					break;
			}

			$data[] = array(
				"0" => $contador,
				"1" => $reg->fecha_reg,
				"2" => '<span>' . $reg->txproducto . '</span>'  ,
				"3" => '<span class="btn-sm label-info">' . $reg->txpermiso . '</span> ',
				"4" => '<span class="btn-sm label ' . $estadoColor . '">' . $estadoTexto . '</span> ',
				"5" => '<button class="btn btn-sm btn-success" onclick="listarDetalle(' . $reg->id . ',\'' . $reg->estado . '\')"><i class="fas fa-eye"></i></button>'


			);
			$contador++;
		}

		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);

		echo json_encode($results);
		break;



	// funcion para contar estado en tarjetas
	case 'contarEstados':
		$idtipo = isset($_POST["idtipo"]) ? limpiarCadena($_POST["idtipo"]) : '';
		$idUsuario = $_SESSION['id']; // 👈 Añadir el ID del usuario actual
		$rspta = $solicitudMercadeo->contarEstados($idtipo, $idUsuario);
		echo json_encode($rspta);
		break;





	// funcion archivar solicitude despues de 15 das 
	case 'archivar':
		$rspta = $solicitudMercadeo->archivarSolicitudes();
		echo json_encode(["status" => true]);
		break;





	case 'cambiarEstado':
		$id = $_POST['id'];
		$estado = $_POST['estado'];
		$comentario = $_POST['comentario'];
		$usuario = $_SESSION['id'];

		$rspta = $solicitudMercadeo->cambiarEstado($id, $estado, $usuario, $comentario);
		echo json_encode(['status' => $rspta ? true : false]);
		break;

	case 'contarNotificaciones':
		$idUsuario = $_SESSION['id'];
		$rspta = $solicitudMercadeo->contarSolicitudesPendientes($idUsuario);
		echo json_encode($rspta);
		break;

	case 'obtenerNotificaciones':
		$idUsuario = $_SESSION['id'];
		$limite = isset($_POST['limite']) ? intval($_POST['limite']) : 10;
		$rspta = $solicitudMercadeo->obtenerSolicitudesPendientes($idUsuario, $limite);
		
		$data = [];
		if ($rspta) {
			while ($reg = $rspta->fetch_object()) {
				$estadoTexto = ($reg->estado == 1) ? "Registrado" : "Respuesta";
				$estadoColor = ($reg->estado == 1) ? "badge-success" : "badge-warning";
				
				$data[] = [
					"id" => $reg->id,
					"titulo" => $reg->titulo,
					"fecha_reg" => $reg->fecha_reg,
					"estado" => $reg->estado,
					"estado_texto" => $estadoTexto,
					"estado_color" => $estadoColor,
					"departamento" => $reg->departamento
				];
			}
		}
		
		echo json_encode($data);
		break;
}
