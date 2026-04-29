<?php

require_once "../modelos/I_etiquetas.php";

if (strlen(session_id()) < 1)
    session_start();

$etiqueta = new Etiquetas();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$asap = isset($_POST["asap"]) ? limpiarCadena($_POST["asap"]) : "";
$fecha_estimada = isset($_POST["fecha_estimada"]) ? limpiarCadena($_POST["fecha_estimada"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";








switch ($_GET["op"]) {


    case 'guardaryeditar':
        if (empty($id)) {

            $rspta = $etiqueta->insertar($nombre, $asap, $fecha_estimada, $descripcion);
            echo $rspta ? "Etiqueta registrada" : "Etiqueta No se pudo registrar";
        } else {
            $rspta = $etiqueta->editar($id, $nombre, $asap, $fecha_estimada, $descripcion);
            echo $rspta ? "Etiqueta registrada" : "Etiqueta se pudo actualizar";
        }
        break;



    case 'listar':
        $rspta = $etiqueta->listar();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $data[] = array(

                "0" => $reg->nombre,
                "1" => $reg->asap,
                // "2" => $reg->fecha_estimada,
                "2" => $reg->descripcion,
                "3" => ($reg->estado) ? '<a href="javascript:desactivar('.$reg->id.')"><span class="btn-sm label bg-green">Activado</span></a>' :
                    '<a href="javascript:activar('.$reg->id.')"><span class="btn-sm label bg-red">Desactivado</span></a>',
                "4" => '<button class="btn btn-warning " data-toggle="modal" data-target="#ModalEtiquetas" onclick="mostrar(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>'
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


    case 'mostrar':
        $rspta = $etiqueta->mostrar($id);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;


    case 'desactivar':
        $rspta = $etiqueta->desactivar($id);
        echo $rspta ? "Etiqueta Desactivado" : "Etiqueta no se puede desactivar";
        break;

    case 'activar':
        $rspta = $etiqueta->activar($id);
        echo $rspta ? "Etiqueta activado" : "Etiqueta no se puede activar";
        break;



   

    case 'selectEtiquetas':


		$rspta = $etiqueta->selectEtiquetas();
		echo '<option selected value="0">Programming</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id_etiqueta . '>' . $reg->nombre  . '</option>';
		}
		break;




   case 'listarEtiquetasActivas':

    // Verificamos año actual y siguiente
    $anio_actual = date('Y');
    $anio_siguiente = date('Y', strtotime('+1 year'));

    // Generamos etiquetas si no existen para ambos años
    $etiqueta->generarEtiquetasAnio($anio_actual);
    $etiqueta->generarEtiquetasAnio($anio_siguiente);

    // Luego listamos las etiquetas futuras (como ya ajustamos en el modelo)
    $etiquetas = $etiqueta->listarActivas();

    $data = array();
    $hoy = date('Y-m-01'); // Primer día del mes actual

    foreach ($etiquetas as $reg) {
        // Para futuras implementaciones, podemos agregar lógica específica aquí
        $tiene_cantidades = false;

        // Verificar si es una etiqueta pasada
        $es_pasada = false;
        if (isset($reg['fecha_estimada']) && $reg['fecha_estimada'] && $reg['fecha_estimada'] < $hoy) {
            $es_pasada = true;
        }

        $data[] = array(
            "id" => $reg['id'],
            "nombre" => strtoupper($reg['nombre']),
            "fecha_estimada" => $reg['fecha_estimada'],
            "cantidades" => isset($reg['cantidades']) ? $reg['cantidades'] : [],
            "tiene_cantidades" => $tiene_cantidades,
            "es_pasada" => $es_pasada
        );
    }

    echo json_encode($data);
    break;

case 'listarEtiquetasConPasadas':
    // Verificamos año actual y siguiente
    $anio_actual = date('Y');
    $anio_siguiente = date('Y', strtotime('+1 year'));

    // Generamos etiquetas si no existen para ambos años
    $etiqueta->generarEtiquetasAnio($anio_actual);
    $etiqueta->generarEtiquetasAnio($anio_siguiente);

    // Obtener etiquetas normales
    $etiquetas = $etiqueta->listarActivas();

    // Obtener etiquetas pasadas con proceso
    $etiquetas_pasadas = $etiqueta->obtenerEtiquetasPasadasConProceso();

    // Combinar ambas listas
    $todas_etiquetas = array_merge($etiquetas, $etiquetas_pasadas);

    $data = array();
    $hoy = date('Y-m-01');

    foreach ($todas_etiquetas as $reg) {
        // Para futuras implementaciones, podemos agregar lógica específica aquí
        $tiene_cantidades = false;

        // Verificar si es una etiqueta pasada
        $es_pasada = false;
        if (isset($reg['fecha_estimada']) && $reg['fecha_estimada'] && $reg['fecha_estimada'] < $hoy) {
            $es_pasada = true;
        }

        $data[] = array(
        "id" => $reg['id'],
        "nombre" => strtoupper($reg['nombre']),
        "fecha_estimada" => $reg['fecha_estimada'],  // ← LÍNEA AGREGADA
        "cantidades" => isset($reg['cantidades']) ? $reg['cantidades'] : [],
        "tiene_cantidades" => $tiene_cantidades,
        "es_pasada" => $es_pasada
        );
    }

    echo json_encode($data);
    break;




case 'generarEtiquetas':
   
    $anio = isset($_POST['anio']) ? $_POST['anio'] : date('Y');
    $cantidad = $etiqueta->generarEtiquetasAnio($anio);

    echo json_encode([
        'ok' => true,
        'mensaje' => "$cantidad etiquetas generadas para el año $anio"
    ]);
break;


case 'obtenerEtiquetasPasadasPorCodigo':
    $codigo = isset($_POST['codigo']) ? limpiarCadena($_POST['codigo']) : "";

    if (!empty($codigo)) {
        $etiquetas_pasadas = $etiqueta->obtenerEtiquetasPasadasPorCodigo($codigo);

        $data = array();

        foreach ($etiquetas_pasadas as $reg) {
            $data[] = array(
                "id" => $reg['id'],
                "nombre" => strtoupper($reg['nombre']),
                "cant_sol" => $reg['cant_sol'],
                "cant_enviada" => $reg['cant_enviada'],
                "estado_nombre" => $reg['estado_nombre'],
                "es_pasada" => true
            );
        }

        echo json_encode($data);
    } else {
        echo json_encode([]);
    }
    break;

}
