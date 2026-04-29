<?php

require_once "../modelos/DepartamentoCrud.php";

if (strlen(session_id()) < 1) 
	session_start();

$departamento = new DepartamentoCrud();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";

switch ($_GET["op"]){
	case 'guardaryeditar':
		if (empty($id)) {
			$rspta = $departamento->insertar($nombre, $descripcion);
			echo $rspta ? "Departamento registrado" : "No se pudieron registrar los datos";
		} else {
			$rspta = $departamento->editar($id, $nombre, $descripcion);
			echo $rspta ? "Departamento actualizado" : "No se pudieron actualizar los datos";
		}
	break;

	case 'eliminar':
		$rspta = $departamento->eliminar($id);
		echo $rspta ? "Departamento eliminado" : "No se pudo eliminar el departamento";
	break;

	case 'activar':
		$rspta = $departamento->activar($id);
		echo $rspta ? "Departamento activado" : "No se pudo activar el departamento";
	break;

	case 'mostrar':
		$rspta = $departamento->mostrar($id);
		echo json_encode($rspta);
	break;

	case 'listar':
		$rspta = $departamento->listar();
		$data = Array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"0" => ($reg->estado) ? 
					'<button class="btn btn-warning" data-toggle="modal" data-target="#modalDepartamento" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
					' <button class="btn btn-danger " onclick="eliminar('.$reg->id.')"><i class="fa fa-trash"></i></button>' : 
					'<button class="btn btn-warning " data-toggle="modal" data-target="#modalDepartamento" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
					' <button class="btn btn-success " onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',
				"1" => $reg->nombre,
				"2" => $reg->descripcion,
				"3" => $reg->total_usuarios,
				"4" => ($reg->estado) ? '<span class="btn-sm label bg-green">Activado</span>' : '<span class="btn-sm label bg-red">Desactivado</span>',
				"5" => $reg->fecha_creacion
			);
		}
		$results = array(
			"sEcho" => 1,
			"iTotalRecords" => count($data),
			"iTotalDisplayRecords" => count($data),
			"aaData" => $data
		);
		echo json_encode($results);
	break;

	case 'selectDepartamento':
		$rspta = $departamento->selectDepartamento();
		echo '<option value="">Seleccione...</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>' . $reg->nombre . '</option>';
		}
	break;

	case 'listarUsuarios':
		$id_departamento = $_GET['id_departamento'];
		$rspta = $departamento->listarUsuarios($id_departamento);
		$data = Array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"id" => $reg->id,
				"text" => $reg->nombre,
				"fecha_asignacion" => $reg->fecha_asignacion
			);
		}
		echo json_encode($data);
	break;

	case 'usuariosDisponibles':
		$id_departamento = $_GET['id_departamento'];
		$rspta = $departamento->usuariosDisponibles($id_departamento);
		$data = Array();

		while ($reg = $rspta->fetch_object()) {
			$data[] = array(
				"id" => $reg->id,
				"text" => $reg->nombre 
			);
		}
		echo json_encode($data);
	break;

	case 'asignarUsuario':
		$id_departamento = $_POST['id_departamento'];
		$id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
		
		if (empty($id_usuario)) {
			echo "Error: Debe seleccionar al menos un usuario";
			break;
		}
		
		// Si id_usuario es un array, procesarlo uno por uno
		if (is_array($id_usuario)) {
			$errores = 0;
			$total = count($id_usuario);
			$usuarios_invalidos = array();
			
			foreach ($id_usuario as $usuario_id) {
				$rspta = $departamento->asignarUsuario($id_departamento, $usuario_id);
				if (!$rspta) {
					$errores++;
					$usuarios_invalidos[] = $usuario_id;
				}
			}
			
			if ($errores == 0) {
				echo "Todos los usuarios fueron asignados correctamente";
			} else {
				echo "Se asignaron " . ($total - $errores) . " de " . $total . " usuarios. " . $errores . " fallaron (IDs: " . implode(", ", $usuarios_invalidos) . " - usuarios inexistentes o inactivos).";
			}
		} else {
			// Si es un solo usuario
			$rspta = $departamento->asignarUsuario($id_departamento, $id_usuario);
			echo $rspta ? "Usuario asignado correctamente" : "Error al asignar usuario (ID: $id_usuario - usuario inexistente o inactivo)";
		}
	break;

	case 'quitarUsuario':
		$id_departamento = $_POST['id_departamento'];
		$id_usuario = isset($_POST['id_usuario']) ? $_POST['id_usuario'] : null;
		
		if (empty($id_usuario)) {
			echo "Error: Debe seleccionar al menos un usuario";
			break;
		}
		
		// Si id_usuario es un array, procesarlo uno por uno
		if (is_array($id_usuario)) {
			$errores = 0;
			$total = count($id_usuario);
			$usuarios_invalidos = array();
			
			foreach ($id_usuario as $usuario_id) {
				$rspta = $departamento->quitarUsuario($id_departamento, $usuario_id);
				if (!$rspta) {
					$errores++;
					$usuarios_invalidos[] = $usuario_id;
				}
			}
			
			if ($errores == 0) {
				echo "Todos los usuarios fueron removidos correctamente";
			} else {
				echo "Se removieron " . ($total - $errores) . " de " . $total . " usuarios. " . $errores . " fallaron (IDs: " . implode(", ", $usuarios_invalidos) . ").";
			}
		} else {
			// Si es un solo usuario
			$rspta = $departamento->quitarUsuario($id_departamento, $id_usuario);
			echo $rspta ? "Usuario removido correctamente" : "Error al remover usuario (ID: $id_usuario)";
		}
	break;
}

?>