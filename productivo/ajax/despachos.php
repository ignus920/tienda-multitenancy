<?php
require_once "../modelos/Despachos.php";

$despachos = new Despachos();





switch ($_GET["op"]) {


  case 'guardar_orden_completa':
    // Limpiar los valores recibidos
    $userqr = isset($_POST['userqr']) ? limpiarCadena($_POST['userqr']) : "";
    $empresa_transportadora = isset($_POST['empresa_transportadora']) ? limpiarCadena($_POST['empresa_transportadora']) : "";
    $paquetes = isset($_POST['paquetes']) ? $_POST['paquetes'] : [];

    // Inicializamos el contador de errores
    $errores = 0;

    // Procesar cada paquete
    foreach ($paquetes as $paquete) {
        $id_op = isset($paquete['op']) ? limpiarCadena($paquete['op']) : "";  // Ahora obtenemos 'op' directamente de cada paquete
        $guia = isset($paquete['guia']) ? limpiarCadena($paquete['guia']) : "";
        $numeroPaquete = isset($paquete['paquetes']) ? limpiarCadena($paquete['paquetes']) : "";

        // Verifica que los valores de guia y numeroPaquete no estén vacíos
        if ($guia && $numeroPaquete) {
            // Llamar a la función del modelo para guardar la guía
            $rspta = $despachos->procesar_guia($id_op, $userqr, $guia, $numeroPaquete, $empresa_transportadora);

            $rspta1 = $despachos->actualiarOp($id_op,$empresa_transportadora);
            
            if (!$rspta) {
                $errores++;
            }
        } else {
            $errores++;
        }
    }

    // Mensaje de éxito o error
    echo $errores === 0 ? "Guías agregadas correctamente." : "Hubo errores al procesar algunas guías.";
    break;







   




    case 'verificarUsuarioActivo':
    $userqr = isset($_POST['userqr']) ? limpiarCadena($_POST['userqr']) : "";
        // Llama al método correcto del modelo
    $rspta = $despachos->usuariosActivos($userqr);

        // Verifica si hay resultados
    if ($fila = $rspta->fetch_assoc()) {
        echo json_encode([
            'success' => true, 
            'message' => 'Usuario activo', 
            'data' => [
                'id' => $fila['id'],
                'nombre' => $fila['nombre'] ?? 'Usuario',
                'cargo' => $fila['cargo'] ?? ''
                ]
            ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Usuario no activo o no encontrado'
        ]);
    }
    break;




    case 'opEstadoEmpacado':
  $id_op = isset($_POST['id_op']) ? limpiarCadena($_POST['id_op']) : "";
    // Llama al método del modelo
    $rspta = $despachos->opEstadoEmpacado($id_op);

    // Verifica si hay resultados
    if ($fila = $rspta->fetch_assoc()) {
        echo json_encode(['success' => true, 'message' => 'Usuario activo', 'data' => $fila]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no activo o no encontrado']);
    }
    break;

   case 'listarGuias':
        $fechaIni = $_POST['fechaIni'];
        $fechaFin = $_POST['fechaFin'];
    
        $rspta = $despachos->listarGuias($fechaIni, $fechaFin);
    
        $data = array();
    
        while ($reg = $rspta->fetch_object()) {
            // Generar el enlace según la transportadora
            $guia = $reg->guia;
            $guia_html = $guia; // Por defecto, mostrar solo el número de la guía
    
            if ($reg->empresa_transportadora == 'Coordinadora') {
                $enlace = "https://coordinadora.com/rastreo/rastreo-de-guia/detalle-de-rastreo-de-guia/?guia=".$guia;
                $guia_html = "<a href='javascript:void(0);' data-url='$enlace'>$guia</a>";
            } elseif ($reg->empresa_transportadora == 'Servientrega') {
                $enlace = "https://www.servientrega.com/wps/portal/rastreo-envio";
                $guia_html = "<a href='javascript:void(0);' data-url='$enlace'>$guia</a>";
            }
    
            $data[] = array(
                "0" => $reg->fecha_reg,
                "1" => $reg->id_op,
                "2" => $guia_html .'<br>'.$reg->empresa_transportadora, // Columna de la guía con el enlace (si aplica)
                "3" => $reg->txcliente, // Nombre del cliente
                "4" => $reg->usuario, // Nombre del usuario
                "5" => $reg->cuenta_paquetes // Cuenta de paquetes por guía
            );
        }
    
        $results = array(
            "sEcho" => 1, // Información para el datatables
            "iTotalRecords" => count($data), // Enviamos el total de registros al datatable
            "iTotalDisplayRecords" => count($data), // Enviamos el total de registros a visualizar
            "aaData" => $data
        );
    
        echo json_encode($results);
        break;




}
