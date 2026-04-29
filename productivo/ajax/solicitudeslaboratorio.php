<?php

require_once "../modelos/SolicitudesLaboratorio.php";

if (strlen(session_id()) < 1)
	session_start();

$solicitudMercadeo = new SolicitudLaboratorio();



$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";


$solicitud = isset($_POST["solicitud"]) ? limpiarCadena($_POST["solicitud"]) : "";

$id_user_respuesta = $_SESSION['id'];
$fecha1 = isset($_POST["fecha1l"]) ? limpiarCadena($_POST["fecha1l"]) : "";
$fecha2 = isset($_POST["fecha2l"]) ? limpiarCadena($_POST["fecha2l"]) : "";
$solicitud_respuesta = isset($_POST["solicitud_respuesta"]) ? limpiarCadena($_POST["solicitud_respuesta"]) : "";
$userglobal = isset($_POST["userglobal"]) ? limpiarCadena($_POST["userglobal"]) : "";





$id_producto = isset($_POST["id_productol"]) ? limpiarCadena($_POST["id_productol"]) : "";
$titulo = isset($_POST["titulol"]) ? limpiarCadena($_POST["titulol"]) : "";
$estado = isset($_POST["estadol"]) ? limpiarCadena($_POST["estadol"]) : "";
$id_solicitud = isset($_POST["idsolicitudl"]) ? limpiarCadena($_POST["idsolicitudl"]) : "";
$comentario = isset($_POST["comentariol"]) ? limpiarCadena($_POST["comentariol"]) : "";
$id_user = $_SESSION['id'];
switch ($_GET["op"]) {


	case 'guardaryeditar':
		if (empty($id)) {
			// Elimina addslashes() para evitar problemas con las URLs de las imágenes
			$rspta = $solicitudMercadeo->insertar($id_producto, $titulo, $estado);
			json_encode($rspta);
			$rspta1 = $solicitudMercadeo->insertarDetalle($rspta, $comentario, $id_user, $estado);
			echo $rspta1 ? "Solicitud registrada" : "Solicitud no se pudo resgistrar";
		} else {

			$rspta = $solicitudMercadeo->editarEstado($id, $estado);
			if ($rspta) {

				$rspta2 = $solicitudMercadeo->insertarDetalle($id, $comentario, $id_user, $estado);
				echo $rspta2 ? "Solicitud registrada" : "Solicitud no se pudo resgistrar";
			}
		}
		break;


	case 'mostrarLaboratorio':
		$rspta = $solicitudMercadeo->mostrarLaboratorio($id);
		echo json_encode($rspta, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
		break;



	case 'listar':

		switch ($estado) {
			case '1':
				$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and s.estado='$estado'";
				break;

			case '2':
				$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and s.estado='$estado'";
				break;

			case '3':
				$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and s.estado='$estado'";
				break;

			case '4':
				$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' and s.estado='$estado'";
				break;

			default:
				$condicion = " WHERE date(s.fecha_reg) BETWEEN '$fecha1' AND '$fecha2' ";
				break;
		}
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
				"2" => '<strong>' . $reg->titulo . '</strong><br>' . $reg->txproducto,
				"3" => '<span class="btn-sm label ' . $estadoColor . '">' . $estadoTexto . '</span>',
				"4" => '<button class="btn btn-success btnMostrarL" data-toggle="modal" data-target="#modalSolicitudeslaboratorio" 
							 onclick="mostrarLaboratorio(' . $reg->id . '); limpiarLaboratorio();">
							 <i class="fa fa-pencil"></i></button> '
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








	case 'listarDetalle';
		//recibimos el identrada
		$rspta = $solicitudMercadeo->listarDetalle($id);

		while ($reg = $rspta->fetch_object()) {
			$estadoTexto = "";
			$estadoColor = "";
			switch ($reg->estado_sol) {
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
			$solicitud_decoded = html_entity_decode($reg->comentario, ENT_QUOTES, 'UTF-8');
			echo '<div class="post">
                      <div class="user-block">
                        <span class="username">
                          <a href="#">' . $reg->nombre . '</a>
                          <span style="font-size: 10px;" class="float-right btn-tool btn-sm label ' . $estadoColor . '">' . $estadoTexto . '</span>
                        </span>
                        <span class="description">' . $reg->fecha . '</span>
                      </div>
                      <!-- /.user-block -->
                      ' . $solicitud_decoded . '
                    </div>';
		}
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
				"2" => '<strong>' . $reg->titulo . '</strong><br>' . $reg->txproducto,
				"3" => '<span class="btn-sm label ' . $estadoColor . '">' . $estadoTexto . '</span>',
				"4" => ($reg->estado != 1)
					? '<button class="btn btn-success" onclick="listarDetalleL(' . $reg->id . ')">  <i class="fa fa-pencil"></i></button>'
					: ''

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
































    




		case 'cargarSolicitudesPendientes':
			
			$modulos = [];
		
			if ($_SESSION['Laboratorio'] == 1) {
				$modulos[] = 'Laboratorio';
			}
			if ($_SESSION['Mercadeo'] == 1) {
				$modulos[] = 'Mercadeo';
			}
		
			$rspta = $solicitudMercadeo->cargarSolicitudesPendientes($modulos);
			echo json_encode($rspta);
			break;
		
}
