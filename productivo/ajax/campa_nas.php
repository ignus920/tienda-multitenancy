<?php
require_once "../modelos/Campa_nas.php";



if (strlen(session_id()) < 1)
    session_start();

$camp_a = new Campa_nas();

//variables para envio de coreo y cambio de contrase�a
$id_cliente = isset($_POST["id_cliente"]) ? limpiarCadena($_POST["id_cliente"]) : "";
$id_campana = isset($_POST["id_campana"]) ? limpiarCadena($_POST["id_campana"]) : "";


$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$nombre = isset($_POST["nombre"]) ? limpiarCadena($_POST["nombre"]) : "";
$descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
$fecha_inicio = isset($_POST["fecha_inicio"]) ? limpiarCadena($_POST["fecha_inicio"]) : "";
$fecha_fin = isset($_POST["fecha_fin"]) ? limpiarCadena($_POST["fecha_fin"]) : "";
$estado = isset($_POST["estado"]) ? limpiarCadena($_POST["estado"]) : "";
$cantidad_regalos = isset($_POST["cantidad_regalos"]) ? limpiarCadena($_POST["cantidad_regalos"]) : "";
$can_entregar = isset($_POST["can_entregar"]) ? limpiarCadena($_POST["can_entregar"]) : "";
$observacion = isset($_POST["observacion"]) ? limpiarCadena($_POST["observacion"]) : "";
$tipo_asignacion = isset($_POST["tipo_asignacion"]) ? limpiarCadena($_POST["tipo_asignacion"]) : "";
$id_orden = isset($_POST["id_orden"]) ? limpiarCadena($_POST["id_orden"]) : "";





switch ($_GET["op"]) {


    case 'obtenerCampaActivas':
        // ? Paso previo: actualizar campa�as vencidas
        $fechaActual = date('Y-m-d');
        $sql_vencidas = "UPDATE campa_nas 
                     SET estado = 'anulado' 
                     WHERE estado != 'anulado' AND fecha_fin < '$fechaActual'";
        ejecutarConsulta($sql_vencidas);

        // ? Ahora s�, listar campa�as activas o pausadas

        $cont = 1;
        $rspta = $camp_a->obtenerCampaActivas();
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $estado_actual = ($reg->estado === 'activo') ? 'pausado' : 'activo'; // Alternar entre activo y pausado
            $data[] = array(

                "0" => $cont,
                "1" => $reg->nombre . '<br> <strong>Descripcion:</strong> <br> ' . $reg->descripcion,
                "2" => $reg->fecha_inicio,
                "3" => $reg->fecha_fin,
                "4" => '<a href="javascript:cambiarEstadoCampana(' . $reg->id . ', \'' . $reg->estado . '\')">' .
                    (($reg->estado === 'activo') ? '<span class="btn-sm label bg-green">'.$reg->estado.'</span>' : (($reg->estado === 'pausado') ? '<span class="btn-sm label bg-yellow">'.$reg->estado.'</span>' :
                        '<span class="btn-sm label bg-red">'.$reg->estado.'</span>')) .
                    '</a>',
                "5" => $reg->tipo_asignacion,
                "6" => $reg->cantidad_regalos,
                "7" => $reg->regalos_enviados,
                "8" => '<button class="btn btn-warning " data-toggle="modal" data-target="#ModalCampa" onclick="mostrarCamp(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>'
            );
            $cont++;
        }
        $results = array(
            "sEcho" => 1, //Informaci�n para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;



    case 'guardaryeditarC':
        if (empty($id)) {
            $rspta = $camp_a->crearCampa($nombre, $descripcion, $fecha_inicio, $fecha_fin, $cantidad_regalos, $can_entregar, $tipo_asignacion);
            echo $rspta ? "Campa�a Registrada" : "Campa�a no se puede registrar";
        } else {
            $rspta = $camp_a->editarCampa($id, $nombre, $descripcion, $fecha_inicio, $fecha_fin, $cantidad_regalos, $can_entregar, $tipo_asignacion);
            echo $rspta ? "Campa�a actualizado" : "Campa�a no se pudo actualizar";
        }
        break;




    case 'mostrarCamp':
        $rspta = $camp_a->mostrarCamp($id);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;




    case 'cambiarEstadoCampana':
        $id = $_POST['id'];
        $estado_actual = $_POST['estado_actual']; // Recibe el estado actual ('activo', 'pausado', 'anulado')

        // Determinar el siguiente estado
        if ($estado_actual === 'activo') {
            $nuevo_estado = 'pausado';
        } elseif ($estado_actual === 'pausado') {
            $nuevo_estado = 'anulado';
        } else {
            $nuevo_estado = 'activo'; // Si es 'anulado', vuelve a 'activo'
        }

        // Llamar a la funci�n para cambiar el estado
        $resultado = $camp_a->cambiarEstadoCampana($id, $nuevo_estado);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Estado de la campa�a actualizado exitosamente.', 'nuevo_estado' => $nuevo_estado]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado de la campa�a.']);
        }
        break;






    case 'registrarRegaloEntregado':
        // Llamar a la funci�n para registrar el regalo entregado
        $resultado = $camp_a->registrarRegaloEntregado($id_cliente, $id_orden,$id_campana, $observacion);

        if ($resultado) {
            echo json_encode(['success' => true, 'message' => 'Regalo entregado exitosamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se pudo entregar el regalo.']);
        }
        break;



    case 'clienteRecibioRegalo':
    // $id_cliente = $_POST['id_cliente'];
    // $id_campana = $_POST['id_campana'];
    // $tipo_asignacion = $_POST['tipo_asignacion'] ?? null;
    $recibido = $camp_a->clienteRecibioRegalo($id_cliente, $id_campana, $tipo_asignacion);
    echo json_encode(['recibido' => $recibido]);
    break;





    case 'obtenerCampActivas':
        $rspta = $camp_a->obtenerActivas();
        $data = [];
        while ($fila = $rspta->fetch_assoc()) {
            $data[] = $fila;
        }

        echo json_encode(['success' => true, 'campanas_activas' => $data]);
        break;





    case 'selectCampanas':

        $rspta = $camp_a->obtenerActivas();
        echo '<option value="">Seleccione una opci�n</option>';
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->id . '>' . $reg->nombre  . '</option>';
        }
        break;


    case 'ClienteObsequio':

        $rspta = $camp_a->ClienteObsequio($id_campana);
        echo '<option value="">Seleccione una opci�n</option>';
        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->idcliente . '>' . $reg->nombre  . '</option>';
        }
        break;





    case 'validarClienteManual':
        $rspta = $camp_a->validarClienteManual($id_campana, $id_cliente);
        echo json_encode(['autorizado' => $rspta ? true : false]);
        break;







        case 'seleccionClientes':
      
        $rspta = $camp_a->seleccionClientes($id_campana);
        //Vamos a declarar un array�
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $btncheck = '<input type="checkbox" class="mr-2" title="Reset seleccionado" name="seleccion[]" value="' . $reg->idcliente . '">';
            $data[] = array(

                "0" => $btncheck,
                "1" => $reg->nombre
                
            );
         
        }
        $results = array(
            "sEcho" => 1, //Informaci�n para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);

        break;







   case 'agregarClientesManuales':
    $clientes = $_POST["clientes"]; // array de IDs
    $id_campana = $_POST["id_campana"];

    // Ver cu�ntos ya hay asignados
    $asignados = $camp_a->contarClientesManuales($id_campana);
    $cantidad_regalos = $camp_a->obtenerCantidadRegalos($id_campana);

    $totalAsignados = (int)$asignados['total'];
    $totalPermitidos = (int)$cantidad_regalos['cantidad_regalos'];
    $totalAInsertar = count($clientes);

    if (($totalAsignados + $totalAInsertar) > $totalPermitidos) {
        echo json_encode(['success' => false, 'message' => 'Supera la cantidad de regalos permitidos']);
        return;
    }

    // Guardar si no supera
    $todoBien = true;
    foreach ($clientes as $id_cliente) {
        if (!$camp_a->agregarClienteManual($id_campana, $id_cliente)) {
            $todoBien = false;
        }
    }

    echo json_encode(['success' => $todoBien]);
    break;







case 'clientesAsignadosManual':
    $rspta = $camp_a->obtenerClientesManualPorCampana($id_campana);
    $contador = 1;
    $data = [];

    while ($reg = $rspta->fetch_object()) {
        $boton = $reg->entregado
            ? '<span class="badge badge-success">Entregado</span>'
            : '<a class="btn btn-danger btn-sm" onclick="eliminarClienteManual(' . $reg->id_cliente . ')"><i class="fa fa-trash"></i></a>';

        $data[] = [
            "0" => $contador,
            "1" => $reg->nombre,
            "2" => $boton,
            "3" => $reg->txcampana // <-- aquí lo agregas como cuarta columna
        ];
        $contador++;
    }

    echo json_encode(["data" => $data]);
    break;



    case 'ClientesConregalo':
    $rspta = $camp_a->ClientesConregalo($id_campana);
    $contador = 1;
    $data = [];

    while ($reg = $rspta->fetch_object()) {

        $data[] = [
            "0" => $contador,
            "1" => $reg->nombre,
            "2" => '<span class="badge badge-success">Entregado ' . $reg->fecha_entrega . '</span>',
            "3" => $reg->txcampana // <-- aquí lo agregas como cuarta columna
        ];
        $contador++;
    }

    echo json_encode(["data" => $data]);
    break;




case 'eliminarClienteManual':
    $rspta = $camp_a->eliminarClienteManual($id_campana, $id_cliente);
    echo json_encode(['success' => $rspta ? true : false]);
    break;



    case 'eliminarTodosClientesManual':
    $id_campana = $_POST['id_campana'];
    $res = $camp_a->eliminarTodosClientesManual($id_campana);
    echo json_encode([
        "success" => $res,
        "message" => $res ? "Todos los clientes han sido eliminados correctamente." : "Error al eliminar los clientes."
    ]);
    break;




    

}
