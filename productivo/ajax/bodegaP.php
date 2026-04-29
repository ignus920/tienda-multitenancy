<?php
session_start();
require_once "../modelos/Ventas.php";
$ventas = new Ventas();
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
if(isset($_SESSION['id'])){
    $idusuario=$_SESSION['id'];
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
            $condicion = " WHERE p.existencias=0 and p.estado=1";
        break;
        default:
        $condicion = " WHERE p.estado = 1";
    }
}

$rspta = $ventas->listarProductos($condicion);
$data = array();

while ($reg = $rspta->fetch_object()) {
    

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


    if ($reg->Accesorios != null) {
        $botonAccesorios = '
        <a title="Accesorio del producto '.$reg->codigo.'-'.$reg->descripcion.' " class="mr-1" data-toggle="modal" href="#modalAccesorios" 
        onclick="listarAccesorios(' . $reg->id .',\'' . $reg->codigo.'-'.$reg->descripcion . '\')">
        <i class="fas fa-wrench txcolori"></i>
        </a>';
    } else {
    $botonAccesorios = '';
   }

  


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
    
    
    <li class="nav-item dropdown mr-2">' . $botonBodega . '</li>
    <li class="nav-item dropdown mr-2">' . $botonSlicitudMercadeo . '</li>

    <li class="nav-item dropdown mr-2">' . $botonAccesorios . '</li>
    
     
    </ul>';

    //<li class="nav-item dropdown mr-2"> '.$botnObservaciones.'</li>
    //<li class="nav-item dropdown mr-2"> '.$botonCalculos.'</li>


    // existencias
    $txreservas = $reg->existencias;

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

    // Tabla de reservas    data-toggle="modal" data-target="#modalResarva"
    $txreservas = '
    <table class="tabla-reservas"  
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


    //transito
    $txreservas1=$reg->cantM;
    // Validar si la descripción contiene "CJ"
     $tieneCJ = strpos($reg->descripcion, 'CJ') !== false;

   
     //Capacidad de picking
    
         $capacidadPicking = '';
    if (!empty($reg->Capacidad_picking)) {
    $capacidadPicking =$reg->Capacidad_picking;
    }


    // Formatear min y max solo si son numéricos para evitar error con strings (ej: '13g')
    $min_f = is_numeric($reg->minimo) ? number_format($reg->minimo, 0) : $reg->minimo;
    $max_f = is_numeric($reg->maximo) ? number_format($reg->maximo, 0) : $reg->maximo;
    $vitrina_f = is_numeric($reg->saldo_vitrina) ? number_format($reg->saldo_vitrina, 0) : $reg->saldo_vitrina;

    $data[] = array(
        "0" => "<span class='$decoracion' title='$reg->codigo-$reg->descripcion'>
        <strong>$reg->ubicacion# </strong> - $reg->codigo-$reg->descripcion
        </span>$btnOpciones",
        "1" => $txreservas,
        "2" => $reg->cantTra2,
        "3" => $reg->cantTra1,
        "4" => $reg->cantidadxcaja,
        "5" => "<strong>" . $reg->ubicacion . "</strong><br><small class='text-muted'>" . $min_f . " - " . $max_f . "</small>",
        "6" => $capacidadPicking,
        "7" => $reg->baja,
        "8" => $vitrina_f
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








}
