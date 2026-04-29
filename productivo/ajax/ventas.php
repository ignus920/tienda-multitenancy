<?php
session_start();
require_once "../modelos/Ventas.php";
$ventas = new Ventas();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// require_once "../modelos/Reservas.php";
// $reservas=new Reservas();
// require_once "../modelos/Productos.php";
// $productos=new Productos();



$id_prod = isset($_POST["id_prod"]) ? limpiarCadena($_POST["id_prod"]) : "";
$id_ped = isset($_POST["id_ped"]) ? limpiarCadena($_POST["id_ped"]) : "";
$pedido = isset($_POST["pedido"]) ? limpiarCadena($_POST["pedido"]) : "";
$obs_anulado = isset($_POST["obs_anulado"]) ? limpiarCadena($_POST["obs_anulado"]) : "";
$id_ped2 = isset($_POST["id_ped2"]) ? limpiarCadena($_POST["id_ped2"]) : "";

$valor = isset($_POST["valor"]) ? limpiarCadena($_POST["valor"]) : "";
$cliente = isset($_POST["cliente"]) ? limpiarCadena($_POST["cliente"]) : "";
$total = isset($_POST["total"]) ? limpiarCadena($_POST["total"]) : "";
$obs = isset($_POST["obs"]) ? limpiarCadena($_POST["obs"]) : "";

$id_mpt = isset($_POST["id_mpt"]) ? limpiarCadena($_POST["id_mpt"]) : "";
$idusuario = isset($_POST["idusuario"]) ? limpiarCadena($_POST["idusuario"]) : "";
$modo = isset($_POST["modo"]) ? limpiarCadena($_POST["modo"]) : "";
$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";

$pedido_online = isset($_POST["pedido_online"]) ? limpiarCadena($_POST["pedido_online"]) : "";
// $idproducto=isset($_POST["idproducto"])? limpiarCadena($_POST["idproducto"]):"";

$cantidad = isset($_POST["cantidad"]) ? $_POST["cantidad"] : [];
$idproducto = isset($_POST["idproducto"]) ? $_POST["idproducto"] : [];
$descripcion = isset($_POST["descripcion"]) ? $_POST["descripcion"] : [];
$precio = isset($_POST["precio"]) ? $_POST["precio"] : [];
$precio1 = isset($_POST["precio1"]) ? $_POST["precio1"] : [];
$precio2 = isset($_POST["precio2"]) ? $_POST["precio2"] : [];
$precio3 = isset($_POST["precio3"]) ? $_POST["precio3"] : [];

if(isset($_SESSION['id']) && !empty($_SESSION['id'])){
    $idusuario = $_SESSION['id'];
} else {
    // Si no hay sesión válida, redirigir al login inmediatamente
    header("Location: ../vistas/login.html");
    exit();
}

     // Generar botones de precios
    function generarBotonPrecio($precio, $id, $codigo, $descripcion, $txurl, $txpdf, $precio1, $precio2, $precio3, $peso, $modo, $factor, $clase = 'primary', $porcentaje = '', $tipo = '') {
        if ($precio == 0) return $precio;
    
        $precioFormateado = number_format($precio, 0, ',', '.');
        $textoBoton = $porcentaje ? "$precioFormateado $porcentaje%" : $precioFormateado;
    
        return ($modo == 'cot') 
        ? "<button class='btn btn-block btn-$clase elevation-1 boton letras' 
        onclick='mostrarPedido($id,\"$precio\",\"$codigo\",\"$descripcion\",\"$precio1\",\"$precio2\",\"$precio3\",\"$peso\",\"$factor\", \"$tipo\")'>
        <span class='info-box-text'>$textoBoton</span>
        </button>"
        : "<button class='btn btn-block btn-$clase elevation-1 boton letras' 
        onclick='controlc($id,\"$precio\",\"$codigo\",\"$descripcion\",\"$txurl\",\"$txpdf\",\"$precio1\",\"$precio2\",\"$precio3\")'>
        <span class='info-box-text'>$textoBoton</span>
        </button>";
    }




switch ($_GET["op"]) {

    case 'guardaryeditar':

    if (empty($id_mpt)) {

        $rspta = $ventas->insertar($cliente, $total, $obs, $cantidad, $idproducto, $descripcion, $precio, $idusuario, $precio1, $precio2, $precio3, $pedido_online);
        if ($rspta == 0) {
            header("Location: ../vistas/login.html");
        } else {
            echo $rspta;
        }
    } else {
        $precio = isset($_POST["precio"]) ? limpiarCadena($_POST["precio"]) : "";

        $rspta = $ventas->editar($id_mpt, $precio);
        echo $rspta ? " Cambio de precio registrado" : "Precio no se pudo registrar";
    }
    break;



        //editar
    case 'InsertarPedido':

    $precio = isset($_POST["precio"]) ? limpiarCadena($_POST["precio"]) : "";
    $descripcion = isset($_POST["descripcion"]) ? limpiarCadena($_POST["descripcion"]) : "";
    $rspta = $ventas->InsertarPedido($_POST["idproducto"], $pedido, $precio, $descripcion);
    echo $rspta ? "Detalle registrado" : "Detalle no se pudo registrar";
    break;

        //editar
    case 'cambioCliente':
    $rspta = $ventas->cambioCliente($cliente, $id_ped);
    echo $rspta ? "Detalle registrado" : "Detalle no se pudo registrar";
    break;

    case 'Guardarcantidad':
    $cantidad = isset($_POST["cantidad"]) ? limpiarCadena($_POST["cantidad"]) : "";
    $rspta = $ventas->Guardarcantidad($id_mpt, $cantidad);
    echo $rspta ? "Detalle registrado" : "Detalle no se pudo registrar";
    break;

        //eliminar el pedido
    case 'Eliminar':
    $rspta = $ventas->Eliminar($id_ped2, $obs_anulado, $user);
    echo $rspta ? "Pedido Eliminado" : "Pedido no se puede eliminar";
    break;



    case 'mostrar':
    $rspta = $ventas->mostrar($pedido);
    echo json_encode($rspta);
    break;

    case 'copyPedido':
    $copyPedido = $ventas->copyPedido($pedido);
    echo $copyPedido;
    break;

    case 'estadoFacturado':
    $rspta = $ventas->estadoFacturado($id_ped);
        // echo $rspta ? "Artículo activado" : "Artículo no se puede activar";
    break;



        /*=====================
    LISTAR DE VENTAS
    =====================*/
    case 'listarVentas':


    $fechaIni = $_POST['fechaIni'];
    $fechaFin = $_POST['fechaFin'];

    $condicion = " WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' and p.pedido_on_line='0' ";

    $roll = $_SESSION['txroll'];
    $tercero = $_SESSION['id'];
    $cot = '../reportes/cotimprimir.php?id=';
    $cot2 = '../reportes/exCotizaciondos.php?id=';
    $urlq = '../reportes/reporte.php?id=';
    $url1 = 'ordenp.php?m=1&p=';
    $urlc = '../reportes/catalogo.php?id=';



    $rspta = $ventas->listarVentas($condicion);


        //Vamos a declarar un array
    $data = array();

    while ($reg = $rspta->fetch_object()) {

            // forma de estraer el año de la fecha reg
        $fechaComoEntero = strtotime($reg->fch_reg);
        $anio = date("y", $fechaComoEntero);
            //ERP22001940
        $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;


            $data[] = array(
                //consecutivo
                "0" => '<span style=" white-space: pre;">' . $string . '</span><br>',
                //estado cambio
                "1" => ($reg->estado == 10) ? '<a title="Crear orden de pedido" target="_self" href="' . $url1 . $reg->id_ped . '&vi=1"><button class="btn ' . $reg->class_color . '" ><i class="fas fa-paste"></i> ' . $reg->tx_epedido . ' </button></a> <br>' . $reg->fch_reg : '<a target="_blank" href="' . $urlq . $reg->id_ped . '" ><button class="btn ' . $reg->class_color . '"><i class="fas fa-qrcode"></i> <br>' . '#OP- ' . $reg->id_op . ' </button> </a><br>' . $reg->fch_reg,
                //valor
                "2" => $reg->nombre,

                "3" => '$' . number_format($reg->total) . '<br>' . $reg->obs,
                //imprimir
                "4" => ($reg->observaciones == NULL) ? '<a target="_blank" href="' . $cot . $reg->id_ped . '"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> ' . 
                // ' <button class="btn btn-success" onclick="EnviarCothtml(' . $reg->telefonoc . ',\'' . $reg->id_ped . '\')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button> ' .
                ' <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalObservaciones" onclick="idpde(' . $reg->id_ped . ')">
                Observaciones
                </button>' : '<a target="_blank" href="' . $cot . $reg->id_ped . '"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> ' 
                // . ' <button class="btn btn-success" onclick="EnviarCothtml(' . $reg->telefonoc . ',\'' . $reg->id_ped . '\')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button>' 
                . ' <a title="Catalogo" target="_blank" href="' . $urlc . $reg->id_ped . '"><button class="btn btn-primary" ><i class="fas fa-store"></i> Catalogo </button><a>',
                //editar Anular
                "5" => ($reg->estado == 10) ? '<button class="btn btn-warning"  onclick="copyPedido(' . $reg->id_ped . ')"><i class="fa fa-pencil"></i></button><button class="btn btn-danger " data-toggle="modal" data-target="#ModalEliminarP" onclick="mostrar(' . $reg->id_ped . ')"><i class="fas fa-trash"></i></button>' : ''

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



        /*=====================
    Detalle venta
    =====================*/

    case 'Detalleventa':

        //$pedido=$_REQUEST['pedido'];
    $pedido=$_GET['pedido'];


    $rspta = $ventas->listarDetalle($pedido);
        //Vamos a declarar un array
    $data = array();

    while ($reg = $rspta->fetch_object()) {
        $data[] = array(

            "0" => $reg->codigo . '-' . $reg->descripcion,
            "1" => '<span class="btn btn-warning elevation-1" onclick="mostrarPrecio(' . $reg->id_mpt . ',\'' . $reg->id_producto . '\')">$' . number_format($reg->precio, 0, ',', '.') . '</span>',
            "2" => '<button class="btn btn-warning elevation-1" onclick="cantidad(' . $reg->id_mpt . ',\'' . $reg->descripcion . '\')">' . number_format($reg->cantidad, 0, ',', '.'), '</button> ',
            "3" => '$' . number_format($reg->subtotal, 0, ',', '.'),
            "4" => ($reg->estado == 1) ? ' <button class="btn btn-danger "onclick="desactivar(' . $reg->id_mpt . ',\'' . $reg->doc . '\')"><i class="fas fa-trash"></i></button>' : ''



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

    case 'listarDetalle':
    $pedido = $_GET['pedido'];
    $rspta = $ventas->listarDetalle($pedido);
    $data = array();
    foreach ($rspta as $row) {
        $data[] = $row;
    }
    echo json_encode($data);
    break;







       case 'listarProductos':
    $existencias = $_POST['existencias'];
    $idpro = $_REQUEST['idpro'];

    // Define la condición de búsqueda
    $condicion = "";
    if ($idpro != "") {
        $condicion = " WHERE p.id='$idpro'";
    } else {
        switch ($existencias) {
            case 4: // en stock
            $condicion = " WHERE existencias > 0 AND p.estado = 1";
            break;
            case 1: // producto negro
            $condicion = " WHERE p.descripcion LIKE '%NVP%'";
            break;
            case 2: // productos rojo
            $condicion = " WHERE p.decoracion = 'text-bold' AND p.estado = 1";
            break;
            case 3: // productos rojo
            $condicion = " WHERE p.decoracion LIKE '%text-danger%' AND p.estado = 1";
            break;
            case 5: // reservas
            $condicion = " WHERE EXISTS (
            SELECT 1 
            FROM reservas r 
            WHERE r.idproducto = p.id 
            AND r.estado = 1 
            AND r.fecha_vencimiento >= CURDATE() 
            GROUP BY r.idproducto
        )";
        break;
            case 6: // comentarios
            $condicion = " WHERE EXISTS (
            SELECT 1 
            FROM s_solicitudes r 
            WHERE r.idproducto = p.id 
            AND r.estado = 1
        )";
        break;
            case 7: // Observación o Notas Técnicas
            $condicion = " WHERE EXISTS (
            SELECT 1 
            FROM producto_obs r 
            WHERE r.idproducto = p.id 
            AND (r.obs != '' OR r.c_tecnicas != '')
        )";
        break;
            case 8: // Bajo stock 30%
            $condicion = " WHERE EXISTS (
            SELECT IFNULL(SUM(m.saldo_final), 0) 
            FROM movimiento m 
            WHERE m.codigo = p.codigo
        ) / p.existencias * 100 < 30";
        break;
            case 9: // productos agotados 5%
            $condicion = " WHERE EXISTS (
            SELECT IFNULL(SUM(m.saldo_final), 0) 
            FROM movimiento m 
            WHERE m.codigo = p.codigo
        ) / p.existencias * 100 < 5";
        break;
        default:
        $condicion = " WHERE p.estado = 1";
    }
}

$rspta = $ventas->listarProductos($condicion);
$data = array();

while ($reg = $rspta->fetch_object()) {
    // Verificar si hay cantidad ajustada por arqueo
    require_once "../modelos/Arqueos.php";
    $arqueos = new Arqueos();
    $cantidad_ajustada = $arqueos->obtenerCantidadAjustada($reg->id);

    $existencias_mostrar = number_format($reg->existencias, 0);
    $indicador_arqueo = '';

    // CORREGIDO: Mantener existencia original y mostrar diferencia por separado
    if ($cantidad_ajustada && $cantidad_ajustada['diferencia'] !== null && $cantidad_ajustada['diferencia'] != 0) {
        $diferencia = number_format($cantidad_ajustada['diferencia'], 0);
        $signo = $cantidad_ajustada['diferencia'] > 0 ? '+' : '';
        $color_diferencia = $cantidad_ajustada['diferencia'] > 0 ? 'text-success' : ($cantidad_ajustada['diferencia'] < 0 ? 'text-danger' : 'text-info');
        $indicador_arqueo = ' <small class="' . $color_diferencia . '" title="Diferencia por arqueo: ' . $signo . $diferencia . '">(' . $signo . $diferencia . ')</small>';

        // Agregar existencia original y diferencia en el formato: 13 (+28)
        $existencias_mostrar = number_format($reg->existencias, 0);
    }

    // Tabla de reservas
    $txreservas = '
    <table class="tabla-reservas" data-toggle="modal" data-target="#modalResarva" 
    onclick="mostrarReserva(' . $reg->id . ',\'' . $reg->codigo . '\')">
    <tbody>
    <tr>
    <td>S ' .$indicador_arqueo . '</td>
    <td><span class="cantidad-normal">' . $existencias_mostrar .'</span></td>
    <td><span class="cantidad-alerta">' . $reg->cantTra1 . '</span></td>
    </tr>
    <tr>
    <td>T</td>
    <td><span class="cantidad-normal">' . $reg->cantM . '</span></td>
    <td><span class="cantidad-alerta">' . $reg->cantTra2 . '</span></td>
    </tr>
    </tbody>
    </table>';

    // Determinar decoración
    $decoracion = strpos($reg->descripcion, 'NVP') !== false ? 'text-primary' : $reg->decoracion;

    // Obtener observaciones y notas
    $rsptaObs = $ventas->mostrarObserva($reg->id);
    $rsptNotaAser = $ventas->notasAsesor($reg->id);
    $obs = $rsptaObs ? $rsptaObs['obs'] : '';
    $c_tecnicas = $rsptaObs ? $rsptaObs['c_tecnicas'] : '';
    $c_comercial = $rsptNotaAser ? $rsptNotaAser['detalle'] : '';
    // Determinar el icono de observaciones
    if (!empty($c_comercial)) {
        $iconClass = 'coloRojo';
    } elseif (!empty($obs) || !empty($c_tecnicas)) {
        $iconClass = 'coloAzul';
    } else {
        $iconClass = 'coloGris';
    }

     //boton bodega confirmar
     $botonBodega = '
    <a title="Solicitar Confirmación" class="mr-1" data-toggle="modal" href="#" 
    onclick="abrirModalSolicitudConfirmacion(' . $reg->id . ',\'' . $reg->estadoBodega . '\')">
    <i class="fas fa-copyright"></i>
    </a>';
    
    ///boton mercadeo 
    $botonSlicitudMercadeo = '
    <a title="Solicitudes a Mercadeo / Laboratorio / Importaciones" class="mr-1" data-toggle="modal" href="#modalSolicitudesmercadeo" 
    onclick="modalSolicitudesmercadeo(' . $reg->id .');limpiarSolicitudmercadeo();">
    <i class="fas fa-clipboard"></i>
    </a>';

    ///boton Laboratorio
    // $botonSlicitudLaboratorio = '
    // <a title="Solicitudes a laboratorio" class="mr-1" data-toggle="modal" href="#modalSolicitudeslaboratorio" 
    // onclick="modalSolicitudeslaboratorio(' . $reg->id .');limpiarLaboratorio();">
    //  <i class="nav-icon fas fa-vial" style="color: #007bff;"></i>
    // </a>';


    //boton calculos
    $botonCalculos='<a title="Calculos" class="mr-1" data-toggle="modal" href="#modalCalculoPrecio" 
    onclick="mostrarProducto(' . $reg->id . ',\'' . $reg->min . '\')">
    <i class="fas fa-weight-hanging"></i>
    </a>';
    //boton observaciones clientes
    $botnObservaciones = '<a title="Observacines cliente" class="mr-1" data-toggle="modal" href="#modalObservacionesClientes" onclick="mostrarObserva(' . $reg->id . ')">
    <i class="fas fa-comment-alt"></i>
    </a>';
    //boton observaciones productos
    $btnObsPro=' <a title="Observaciones" class="mr-1" data-toggle="modal" href="#modalObservacionesProductos" 
    onclick="mostrarObserva(' . $reg->id . ')">
    <i class="fas fa-comments ' . $iconClass . '"></i>
    </a>';
    // Manejo de imagen
    $img = $reg->tximagen ? '
    <a title="Imagen" class="mr-1" data-toggle="modal" href="#modalFoto" aria-expanded="false" 
    onclick="imagen(' . $reg->id . ')">
    <i class="fas fa-images txcolori"></i>
    </a>' : '';
    // <li class="nav-item dropdown mr-2">' . $botonSlicitudLaboratorio . '</li>
    $btnOpciones = '
    <ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
    <li class="nav-item dropdown mr-2">' . $img . '</li>
    <li class="nav-item dropdown mr-2">'.$btnObsPro.'</li>
    
    <li class="nav-item dropdown mr-2"> '.$botonCalculos.'</li>
    <li class="nav-item dropdown mr-2">' . $botonBodega . '</li>
    <li class="nav-item dropdown mr-2">' . $botonSlicitudMercadeo . '</li>
    </ul>';
    ///  <li class="nav-item dropdown mr-2"> '.$botnObservaciones.'</li>
    //
    // Validar si la descripción contiene "CJ"
$tieneCJ = strpos($reg->descripcion, 'CJ') !== false;

    $data[] = array(
        "0" => "<span class='$decoracion' title='$reg->codigo-$reg->descripcion'>
        <strong>$reg->ubicacion# </strong> - $reg->codigo-$reg->descripcion
        </span>$btnOpciones",
        "1" => $txreservas,
        
        "2" => generarBotonPrecio($reg->lista, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor, 'success'),
        "3" => generarBotonPrecio($reg->tres, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor),
        "4" => generarBotonPrecio($reg->cinco, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor),
        "5" => generarBotonPrecio($reg->siete, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor),
        "6" => generarBotonPrecio($reg->diez, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor),
        "7" => generarBotonPrecio($reg->quince, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor),
        "8" => generarBotonPrecio($reg->min, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor,'danger', $reg->pormin),
        "9" => $tieneCJ ? generarBotonPrecio($reg->preciounitarioxcaja, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
         $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor, 'orange', '', 'mayor') : '',
        "10" => generarBotonPrecio($reg->credito, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, 
           $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo,$reg->factor, 'warning')
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





case 'mostrarPedido':
$rspta = $ventas->mostrarPedido($id_prod);
        //Codificar el resultado utilizando json
echo json_encode($rspta);
break;


case 'idpde':
$rspta = $ventas->idpde($id_ped);
        //Codificar el resultado utilizando json
echo json_encode($rspta);
break;



case 'CambioEstado':
$rspta = $ventas->CambioEstado($id_ped);
        // echo $rspta ? "Domiciliario asignado " : "El domiciliario no se pudo asignar";
break;


case 'desactivar':
$rspta = $ventas->desactivar($id_mpt, $id_ped);
echo $rspta ? "Producto eliminado" : "Producto no puede ser eliminado";
break;



case 'cabeza':
$rspta = $ventas->cabeza($pedido);
echo json_encode($rspta);
break;




case 'imagen':
$rspta = $ventas->imagen($id);
echo json_encode($rspta);
break;



case 'listarClientes':

        $existencias = isset($_REQUEST['existencias']) ? $_REQUEST['existencias'] : ''; // Verifica si 'existencias' está definido


        $idpro = $_REQUEST['idpro'];

        $keyword = isset($_POST['keyword']) ? $_POST['keyword'] : ''; // Palabra clave de búsqueda



        $condicion = " WHERE p.estado=1";

        if ($idpro != "") {
            $condicion .= " AND p.id='$idpro'";
        } else if ($existencias == 1) {
            $condicion .= " AND existencias>0 and p.estado=1";
        } // Agrega la condición para la búsqueda por palabra clave
        elseif (!empty($keyword)) {
            $condicion .= " AND (c_tecnicas LIKE '%$keyword%')";
        }
        //CONFIGURAR UN MODO DE TRABAJO PARA QUE AL PICAR EN EL PRECIO SE COPIE EL DATO Y SE PUEDA PEGAR EN OTRO ESPACIO O PROGRAMA
        $rspta = $ventas->listarClientes($condicion);
        //Vamos a declarar un array
        $data = array();
        $por = 0;
        while ($reg = $rspta->fetch_object()) {


            //globo de observacionnes tecnica del producto
            $rsptaObs = $ventas->mostrarObserva($reg->id);
            $obs = isset($rsptaObs['obs']);
            $c_tecnicas = isset($rsptaObs['c_tecnicas']);
            $btnObservaciones = "";



            if (!empty($obs) || !empty($c_tecnicas)) {
                // Si todos los campos están vacíos, mostrar el ícono en gris
                $btnObservaciones = '<a class="" data-toggle="modal" href="#modalObservacionesCliente" onclick="mostrarObserva(' . $reg->id . ')">
                <i class="fas fa-comments coloGris"></i>
                </a>';
            }







            if ($reg->existencias == 0) {

                $cant = '<span class="txobs" title="Este producto está agotado, pero tenemos importacion en proceso" >Llega pronto </span><br>
                <span class="txtittle">“ Importacion en proceso”</span>';
            } else if ($reg->existencias <= $reg->cant_minima * 3) {
                $por = $reg->cant_minima * 3;

                $cant = '<span class="txobs btn-sm btn-danger" title="Menos de ' . $por . ' disponibles
                " >Bajo </span> <br><span class="txtittle">“Menos de ' . $por . ' disponibles”</span>';
            } else if ($reg->cant_minima * 3 < $reg->existencias && $reg->existencias <= $reg->cant_minima * 7) {
                $por = $reg->cant_minima * 7;

                $cant = '<span class="txobs btn-sm btn-warning" title="Hasta ' . $por . ' disponible aprox”
                " >Moderado </span> <br><span class="txtittle">“Hasta ' . $por . ' disponible aprox”</span>';
            } else if ($reg->existencias > $reg->cant_minima * 7) {

                $por = $reg->cant_minima * 7;

                $cant = '<span class="txobs btn-sm btn-success" title="Mas de ' . $por . ' disponible
                " >Suficiente </span><br><span class="txtittle">“Mas de ' . $por . ' disponible”</span>';
                // code...
            } else {

                $cant = $reg->existencias;
            }


            if ($reg->tximagen == "") {

                $img = '<img    heig src="../files/img/default.jpg" class="mediana  profile-user-img img-fluid img-circle " alt="No imagen">';
            } else {

                $img = '<img   data-toggle="modal" data-target="#modalFoto" onclick="imagen(' . $reg->codigo . ')"   src="' . $reg->tximagen . '" class="btn mediana  profile-user-img img-fluid img-circle">';
            }


            $data[] = array(

                "0" => '<span class="txobs" title="' . $reg->codigo . '-' . $reg->descripcion . '" >' . $reg->codigo . '-' . $reg->descripcion . '</span></br>' . $btnObservaciones,

                "1" => $img,
                // "2" => $cant,

                "2" => '<span class="btn-sm btn-secondary info-box-text txobs">' . number_format($reg->credito, 0, ',', '.') . '</span>',



                "3" => '<span class="btn-sm btn-secondary info-box-text txobs">' . number_format($reg->lista, 0, ',', '.') . '</span>',


                "4" => ($reg->min == 0) ? '<span class="info-box-text txobs">' . number_format($reg->min, 0, ',', '.') . '</span>
                ' : '<button style="text-transform: uppercase;font-weight: bold;" class="btn btn-block btn-danger elevation-1 boton letras" onclick="mostrarPedido(' . $reg->id . ',\'' . $reg->min . '\',\'' . $reg->codigo . '\',\'' . $reg->descripcion . '\',\'' . $reg->precio1 . '\',\'' . $reg->precio2 . '\',\'' . $reg->precio3 . '\',\'' . $reg->peso . '\')">
                <span class="info-box-text txobs">' . number_format($reg->min, 0, ',', '.') . '</span>
                </button>',

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



        case 'insertObser':
        $observaciones = isset($_POST["observaciones"]) ? limpiarCadena($_POST["observaciones"]) : "";

        $rspta = $ventas->insertObser($observaciones, $id_ped);
        echo $rspta ? "Detalle registrado" : "Detalle no se pudo registrar";
        break;












        //funcion parainsertas comentarios observaciones y notas asesor 
        case 'insertarComercial':
        $user = $_SESSION['id'];
        $permisoC = $_SESSION['Comercial'];
        $obsP = isset($_POST["obsP"]) ? limpiarCadena($_POST["obsP"]) : "";
        $c_tecnicas = isset($_POST["c_tecnicas"]) ? limpiarCadena($_POST["c_tecnicas"]) : "";
        $idobs = isset($_POST["idobs"]) ? limpiarCadena($_POST["idobs"]) : "";
        $idproducto = isset($_POST["idproducto"]) ? limpiarCadena($_POST["idproducto"]) : "";
        $obsComerciales = isset($_POST["c_comercial"]) ? limpiarCadena($_POST["c_comercial"]) : "";

            if (empty($idobs)) {
                $rspta = $ventas->ObserProducto($idproducto, $obsP, $c_tecnicas,$obsComerciales);
                echo json_encode($rspta);
            } else {

                $rspta2 = $ventas->ObserProductoEditar($idobs,  $obsP, $c_tecnicas,$obsComerciales);
                echo $rspta2 ? "Observacion Actualizadas" : "Observacion no se pudo actualizar";
            }
        
        break;





        case 'mostrarObserva':
        $idproducto = $_POST['idproducto'];
        $rspta = $ventas->mostrarObserva($idproducto);
        echo json_encode($rspta);
        break;


        case 'txObserva':
        $rspta = $ventas->txObserva($idproducto);
        if ($rspta) {
            // Verifica si $rspta es iterable (array o objeto)
            if (is_array($rspta) || is_object($rspta)) {
                echo json_encode($rspta);
            } else {
                // Manejo de otro tipo de respuesta que no sea iterable
                echo json_encode(['error' => 'Formato de respuesta incorrecto']);
            }
        } else {
            // Manejo de una respuesta vacía o nula
            echo json_encode(['error' => 'Respuesta vacía']);
        }
        break;


        case 'txReservas':
        $rspta = $ventas->txReservas($idproducto);
        if ($rspta) {
            // Verifica si $rspta es iterable (array o objeto)
            if (is_array($rspta) || is_object($rspta)) {
                echo json_encode($rspta);
            } else {
                // Manejo de otro tipo de respuesta que no sea iterable
                echo json_encode(['error' => 'Formato de respuesta incorrecto']);
            }
        } else {
            // Manejo de una respuesta vacía o nula
            echo json_encode(['error' => 'Respuesta vacía']);
        }
        break;


        case 'listarDetalleObservaciones':
        $idproducto = $_POST['idproducto'];
        $user = $_SESSION['id'];
        $userLogin = $_SESSION['nombre'];

        $rspta = $ventas->listarDetalleObservaciones($idproducto);
        //Vamos a declarar un array
        $data = array();

        while ($reg = $rspta->fetch_object()) {
            $bteditar = '';

            if ($user == 81 ||  $user == 170) {
                $bteditar = '<button class="btn-sm btn-warning" data-toggle="modal" data-target="#modalRespuesta" onclick="mostrarRespuesta(' . $reg->id_sol . ')"><i class="fa fa-pencil"></i></button>' . ' <button class="btn-sm btn-danger" onclick="desactivarRespuesta(' . $reg->id_sol . ')"><i class="fa fa-trash"></i></button>';
            } else if ($user == 192) {
                $bteditar = '<button class="btn-sm btn-warning" data-toggle="modal" data-target="#modalRespuesta" onclick="mostrarRespuesta(' . $reg->id_sol . ')"><i class="fa fa-pencil"></i></button>';
            }



            if ($reg->login == $userLogin) {

                $desactivarBtn = '<button class="btn-sm btn-danger">N/A</button>';
            }

            $data[] = array(
                "0" => $reg->detalle,
                "1" => $reg->login,
                "2" => $reg->fecha_reg,
                "3" => $reg->respuesta . '<br>' . $reg->fecha_procesa . '<br>' . $reg->procesa,
                "4" => $bteditar
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




        case 'insertarRespuesta':

        $respuesta = isset($_POST["respuesta"]) ? limpiarCadena($_POST["respuesta"]) : "";
        $idres = isset($_POST["idres"]) ? limpiarCadena($_POST["idres"]) : "";
        $rspta = $ventas->insertarRespuesta($idres, $respuesta);

        echo $rspta ? "Observacion actualizada" : "Observacion no se pudo actualizar";

        break;




        case 'desactivarRespuesta':
        $rspta = $ventas->desactivarRespuesta($id);
        echo $rspta ? "Respuesta anulada" : "Respuesta no se puede anular";
        break;


        case 'selectVistaPreoduct':

        $rspta = $ventas->selectVistaPreoduct();

        echo '<option selected value="0">Seleccione una opción</option>';

        while ($reg = $rspta->fetch_object()) {
            echo '<option value=' . $reg->id . ' style="font-weight: bold; font-size: 20px;">' . $reg->codigo . '-' . $reg->descripcion . '</option>';
        }
        break;


































       case 'listarCargarImagenes':
    try {
        $existencias = $_POST['existencias'];
        $idpro = $_REQUEST['idpro'];
        $start = isset($_POST['start']) ? intval($_POST['start']) : 0;
        $length = isset($_POST['length']) ? intval($_POST['length']) : 1000;
        $url = '../vistas/galeriawordpress.php?id=';
        
        // Caso especial: Productos NO en eCommerce
        if ($existencias == 15) {
            $rspta = $ventas->listarProductosNoEcommerce($start, $length);
            $totalRegistros = $ventas->contarProductosNoEcommerce(); // Obtener total real
        } 
        // Caso especial: Solo productos PADRE (sin variantes)
        elseif ($existencias == 16) {
            $condicion = " WHERE p.estado=1 solo_padres";
            $rspta = $ventas->listarProductos($condicion);
        } else {
            // Construir la condición para todos los demás casos
            if ($idpro != "") {
                $condicion = " WHERE p.id='$idpro'";
            } elseif ($existencias == 4) {
                $condicion = " WHERE existencias>0 and p.estado=1";
            } elseif ($existencias == 1) {
                $condicion = " WHERE p.descripcion LIKE '%NVP%'";
            } elseif ($existencias == 2) {
                $condicion = " WHERE p.decoracion='text-bold' and p.estado=1";
            } elseif ($existencias == 3) {
                $condicion = " WHERE p.decoracion LIKE '%text-danger%' AND p.estado = 1";
            } elseif ($existencias == 5) {
                $condicion = " WHERE EXISTS (SELECT 1 FROM reservas r WHERE r.idproducto = p.id and r.estado=1 and r.fecha_vencimiento >= CURDATE() GROUP BY r.idproducto)";
            } elseif ($existencias == 6) {
                $condicion = " WHERE EXISTS (SELECT 1 FROM s_solicitudes r WHERE r.idproducto = p.id and r.estado=1)";
            } elseif ($existencias == 7) {
                $condicion = " WHERE EXISTS (SELECT 1 FROM producto_obs r WHERE r.idproducto = p.id AND (r.obs != '' OR r.c_tecnicas != ''))";
            } elseif ($existencias == 8) {
                $condicion = " WHERE (SELECT IFNULL(SUM(m.saldo_final), 0) FROM movimiento m WHERE m.codigo = p.codigo) / NULLIF(p.existencias, 0) * 100 < 30";
            } elseif ($existencias == 9) {
                $condicion = " WHERE (SELECT IFNULL(SUM(m.saldo_final), 0) FROM movimiento m WHERE m.codigo = p.codigo) / NULLIF(p.existencias, 0) * 100 < 5";
            } elseif ($existencias == 12) {
                $condicion = " WHERE (p.tximagen IS NULL OR p.tximagen = '')";
            } elseif ($existencias == 13) {
                $condicion = " WHERE p.existencias=0";
            } elseif ($existencias == 14) {
                // Productos EN eCommerce
                $condicion = " INNER JOIN (
                    SELECT TRIM(refprod) AS sku FROM ps_productos 
                    WHERE refprod IS NOT NULL AND TRIM(refprod) != ''
                    UNION
                    SELECT TRIM(refatrib) AS sku FROM ps_productos 
                    WHERE refatrib IS NOT NULL AND TRIM(refatrib) != ''
                ) vista_ecommerce ON TRIM(p.codigo) = vista_ecommerce.sku
                WHERE p.estado=1";
            } else {
                $condicion = " WHERE p.estado=1";
            }

            // Ejecutar la consulta normal
            $rspta = $ventas->listarProductos($condicion);
            $totalRegistros = null; // Para otros casos, calcular dinámicamente
        }

        // Procesar los resultados
        $data = array();
        while ($reg = $rspta->fetch_object()) {
            // Determinar decoración
            $decoracion = strpos($reg->descripcion, 'NVP') !== false ? 'text-primary' : $reg->decoracion;

            $txreservas = '
            <table class="tabla-reservas" data-toggle="modal" data-target="#modalResarva" 
            onclick="mostrarReserva(' . $reg->id . ',\'' . $reg->codigo . '\')">
            <tbody>
            <tr>
            <td>S</td>
            <td><span class="cantidad-normal">' . $reg->existencias . '</span></td>
            <td><span class="cantidad-alerta">' . (isset($reg->cantTra1) ? $reg->cantTra1 : 0) . '</span></td>
            </tr>
            <tr>
            <td>T</td>
            <td><span class="cantidad-normal">' . (isset($reg->cantM) ? $reg->cantM : 0) . '</span></td>
            <td><span class="cantidad-alerta">' . (isset($reg->cantTra2) ? $reg->cantTra2 : 0) . '</span></td>
            </tr>
            </tbody>
            </table>';
            
            // $img = '<a class="mr-1" data-toggle="modal" href="#modalFoto" aria-expanded="false" onclick="imagen(' . $reg->id . ')">
            // <i class="fas fa-images txcolori"></i>
            // </a>';

            $img = '<a class="mr-1" href="' . $url . $reg->id . '" aria-expanded="false">
            <i class="fas fa-images txcolori"></i>
            </a>';

            $btnObservaciones = '<ul class="order-1 order-md-3 navbar-nav navbar-no-expand ml-auto">
            <li class="nav-item dropdown">
            ' . $img . '
            </li>
            </ul>';
            
            $numeral = '#';
            if (isset($reg->estadoBodega) && $reg->estadoBodega == 1) {
                $botonBOdega = '<button title="Solicitar Confirmación" class="btn btn-warning btn-sm" onclick="abrirModalSolicitudConfirmacion(' . $reg->id . ',\'' . $reg->estadoBodega . '\')">
                SC
                </button>';
            } elseif (isset($reg->estadoBodega) && $reg->estadoBodega == 2) {
                $botonBOdega = '<button title="Solicitar Confirmación" class="btn btn-success btn-sm" onclick="abrirModalConfirmacion(' . $reg->id . ',\'' . $reg->estadoBodega . '\')">
                SC
                </button>';
            } else {
                $botonBOdega = '';
            }

            $data[] = array(
                "0" => '<span class="'.$decoracion.'" title="' . $reg->codigo . '-' . $reg->descripcion . '"><strong>' . $reg->ubicacion . $numeral . ' </strong>-' . $reg->codigo . '-' . $reg->descripcion . '</span>' . $btnObservaciones,
                "1" => $txreservas,
                "2" => generarBotonPrecio($reg->lista, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor, 'success'),
                "3" => generarBotonPrecio($reg->tres, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor),
                "4" => generarBotonPrecio($reg->cinco, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor),
                "5" => generarBotonPrecio($reg->siete, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor),
                "6" => generarBotonPrecio($reg->diez, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor),
                "7" => generarBotonPrecio($reg->quince, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor),
                "8" => generarBotonPrecio($reg->min, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor, 'danger', $reg->pormin),
                "9" => generarBotonPrecio($reg->credito, $reg->id, $reg->codigo, $reg->descripcion, $reg->txurl, $reg->txpdf, $reg->precio1, $reg->precio2, $reg->precio3, $reg->peso, $modo, $reg->factor, 'warning')
            );
        }

        // Calcular totales
        $totalMostrado = count($data);
        $totalReal = $totalRegistros ?? $totalMostrado;

        // Generar la respuesta JSON
        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => $totalReal,
            "iTotalDisplayRecords" => $totalReal,
            "aaData" => $data
        );

        echo json_encode($results);
    } catch (Exception $e) {
        echo json_encode(array("error" => $e->getMessage()));
    }
    break;







}
