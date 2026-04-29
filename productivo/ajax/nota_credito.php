<?php 
session_start(); 

require_once "../modelos/Nota_credito.php";
$notacredito=new NotaCredito();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$id_pedido=isset($_POST["id_pedido"])? limpiarCadena($_POST["id_pedido"]):"";
$cant_comercial=isset($_POST["cant_comercial"])? limpiarCadena($_POST["cant_comercial"]):"";
$estado=isset($_POST["estado"])? limpiarCadena($_POST["estado"]):"";
$imagen=isset($_POST["imagen"])? limpiarCadena($_POST["imagen"]):"";



switch ($_GET["op"]){


    /*
    FUNCIONES PARA COMERCIAL
    */
    //devolver uno a uno

case 'insertar':
    $rspta = $notacredito->insertar($_POST["id_producto"],$id_pedido,$_POST["cant_original"],$cant_comercial,$_POST["obs_comercial"],$_POST["id_mpt"]);
    echo $rspta ? "Nota de crédito creada exitosamente" : "No se pudo crear la nota de crédito";
    break;


    case 'editar':
    $rspta = $notacredito->editar($id, $cant_comercial, $_POST["obs_comercial"]);
    echo $rspta ? "Nota de crédito actualizada exitosamente" : "No se pudo actualizar la nota de crédito";
    break;


//     case 'guardaryeditar':

//     if (empty($id)) {
//        $rspta=$notacredito->insertar($_POST["id_producto"],$id_pedido,$_POST["cant_original"],$cant_comercial,$_POST["obs_comercial"],$_POST["id_mpt"]);
//        echo $rspta ? "Devolucion exitosa" : "No se pudo Devolver";
//    }else{

//     $rspta=$notacredito->editar($id, $cant_comercial, $_POST["obs_comercial"]);
//     echo $rspta ? "Devolucion Actualizada" : "Devolucion no se pudo actualizar";
// }
// break; 


//devolver todo el pedido
case 'guardarDevolucion':
$rspta=$notacredito->guardarDevolucion($_POST["id_producto"],$id_pedido,$_POST["cant_original"],$_POST["obs_comercial"],$estado,$_POST["id_mpt"]);
echo $rspta ? "Devolucion exitosa" : "No se pudo Devolver";
break;


//listar del pedido
case 'listarDetalles':
require_once "../modelos/Ventas.php";
$ventas = new Ventas();

$pedido = $_GET['pedido'];
$rspta = $ventas->listarDetalle($pedido);

while ($reg = $rspta->fetch_object()) {
    $botones = "";

    // Validar si la cantidad devuelta es menor que la cantidad total
    if ($reg->estadoN <> 5 && $reg->cant_comercial < $reg->cantidad) {
        $botones .= '<span class="btn btn-sm btn-warning"  title="Devoluciones" data-toggle="modal" data-target="#modalDevolver"  onclick="datos(' . $reg->id_producto . ',\'' . $reg->doc . '\',\'' . $reg->cantidad . '\',\'' . $reg->id_mpt . '\',\'' . $reg->id . '\')">Devoluciones</span>';
    }

    // Botón de anular siempre disponible si estadoN <> 5
    if ($reg->estadoN <> 5) {
        $botones .= '<span class="btn btn-sm btn-danger" title="Anular" onclick="anular(' . $reg->id . ')"><i class="fas fa-trash-alt"></i></span>';
    }

    // Construir la fila de la tabla
    $table = '
    <tr class="filas">
        <td><input type="hidden" name="id_producto[]" value="' . $reg->id_producto . '">' . $reg->codigo . '-' . $reg->descripcion . '</td>
        <td>' . number_format($reg->precio) . '</td>
        <td><input type="hidden" name="cant_original[]" value="' . $reg->cantidad . '">' . number_format($reg->cantidad) . '</td>
        <td>' . number_format($reg->cant_comercial) . '</td>
        <td>' . number_format($reg->subtotal) . '</td>
        <td>' . $botones . '</td>
    </tr>';
    echo $table;
}

break;


case 'anular':
$rspta=$notacredito->anular($id);
echo $rspta ? "Devoluicon anulada" : "devolucion no se pudo anular";
break;



/*
FIN FUNCIONES PARA COMERCIAL
*/








    /*=====================
    LISTAR DE VENTAS
    =====================*/

   case 'listarLaboratorio1':
    $fechaIni = $_POST['fechaIni'];
    $fechaFin = $_POST['fechaFin'];
    $condicion = isset($_POST['condicion']) ? limpiarCadena($_POST['condicion']) : "";

    // // Parámetros de DataTables
    // $start = isset($_POST['start']) ? (int)$_POST['start'] : 0;
    // $length = isset($_POST['length']) ? (int)$_POST['length'] : 10;

    // MANTENER TU CONSULTA ORIGINAL
    $consulta = " where date(nc.fecha_sol) BETWEEN '$fechaIni' and '$fechaFin' GROUP by p.id_ped ORDER by nc.id desc ";
    
    switch ($condicion) {
        case 'comercial':
            $consulta = " WHERE nc.estado=1 || nc.estado=6";
            break;
        case 'laboratorio':
            $consulta = " WHERE nc.estado=2";
            break;
        case 'bodega':
            $consulta = " WHERE nc.estado=3";
            break;
        default:
            $consulta = " where date(nc.fecha_sol) BETWEEN '$fechaIni' and '$fechaFin' GROUP by p.id_ped ORDER by nc.id desc ";
            break;
    }

    // Solo agregar LIMIT para paginación
    // $consultaConLimit = $consulta . " LIMIT $start, $length";
    
    $rspta = $notacredito->listarLaboratorio1($consulta);

    // Contar total (ejecutar consulta sin LIMIT)
    // $rsptaCount = $notacredito->listarLaboratorio1($consulta);
    // $totalRecords = 0;
    // while ($rsptaCount->fetch_object()) {
    //     $totalRecords++;
    // }

    // MANTENER TODO TU CÓDIGO ORIGINAL DEL WHILE
    $data = Array();

    while ($reg = $rspta->fetch_object()) {
        $botonesEstado = '';
        $estadoNotaCredito = '';
        $estadoNC = $notacredito->mostraEstado($reg->id_ped);

        foreach ($estadoNC as $key => $value) {
            if ($value['num_nc'] == 0) {
                switch ($value['estado']) {
                    case '1':
                        $estadoNotaCredito = '<span class="btn-sm btn-warning" title="NC-Comercial">NC-Comercial</span>';
                        break;
                    case '2':
                        $estadoNotaCredito = '<span class="btn-sm btn-warning" title="NC-Bodega">NC-Bodega</span>';
                        break;
                    case '3':
                        $estadoNotaCredito = '<span class="btn-sm btn-warning" title="NC-Contabilidad">NC-Contabilidad</span>';
                        break;
                    default:
                        $estadoNotaCredito = '<span class="btn-sm btn-danger" title="NC-Finalizado">NC-Finalizado</span>';
                        break;
                }
            } else {
                $estadoNotaCredito = '<span class="btn-sm btn-danger" title="NC-'.$value['num_nc'].'">NC-'.$value['num_nc'].'</span>' . 
                    ' <a target="blank" href="../files/notaCredito/'.$value['imagen'].'"><img src="../files/img/default.png" width="60px"></a>';
            }
        }

        // comercial
        if ($reg->estado == 1 || $reg->estado == 6) {
            if ($_SESSION['Laboratorio'] == 1 || $_SESSION['Almacen'] == 1) {
                $btl = '<span title="En Laboratorio" data-toggle="modal" data-target="#modalListar" onclick="listarLaboratorio('.$reg->id_ped.',1)" class="btn btn-sm bg-gray" style="margin: 1px;">L</span>';
            } else {
                $btl = '<span title="En Laboratorio" class="btn-sm bg-gray" style="margin: 1px;">L</span>';
            }

            $botonesEstado = '<div> <span title="En Comercial" class="btn-sm bg-yellow" style="margin: 1px;">C</span>' . $btl . 
                '<span title="En Bodega" class="btn-sm bg-gray" style="margin: 1px;">B</span>' .
                '<span title="En Contabilidad" class="btn-sm bg-gray" style="margin: 1px;">CO</span>';

        } else if ($reg->estado == 2) {
            if ($_SESSION['Almacen'] == 1) {
                $bta = '<span title="En Bodega" class="btn btn-sm bg-gray" style="margin: 1px;" onclick="obsBodega('.$reg->id_ped.')">B</span>';
            } else {
                $bta = '<span title="En Bodega" class="btn-sm bg-gray" style="margin: 1px;">B</span>';
            }

            $botonesEstado = '<div> <span title="En Comercial" class="btn-sm bg-yellow" style="margin: 1px;">C</span>' .
                '<span title="En Laboratorio" class="btn-sm bg-yellow" style="margin: 1px;">L</span>' . $bta .
                '<span title="En Contabilidad" class="btn-sm bg-gray" style="margin: 1px;">CO</span>';

        } else if ($reg->estado == 3) {
            if ($_SESSION['Contabilidad'] == 1) {
                $btc = '<button title="En Contabilidad" class="btn btn-sm bg-gray" data-toggle="modal" data-target="#modalContabilidad" onclick="idLaboratorio('.$reg->id_ped.')" style="margin: 1px;">CO</button>';
            } else {
                $btc = '<span title="En Contabilidad" class="btn-sm bg-gray" style="margin: 1px;">CO</span>';
            }
            
            $botonesEstado = '<div> <span title="En Comercial" class="btn-sm bg-yellow" style="margin: 1px;">C</span>' .
                '<span title="En Laboratorio" class="btn-sm bg-yellow" style="margin: 1px;">L</span>' .
                '<span title="En Bodega" class="btn-sm bg-yellow" style="margin: 1px;">B</span>' . $btc;

        } else if ($reg->estado == 4) {
            $botonesEstado = $estadoNotaCredito;
        }

        $data[] = array(
            "0" => $reg->id_op,
            "1" => $reg->fecha_sol,
            "2" => $reg->factura,
            "3" => $reg->cliente,
            "4" => $reg->vendedor,
            "5" => $botonesEstado,
            "6" => '<span class="btn btn-success" title="Ver" data-toggle="modal" data-target="#modalListar" onclick="listarLaboratorio('.$reg->id_ped.',2)"><i class="fa fa-eye"></i></span>',
            "7" => $reg->obs_comercial,
            "8" => $reg->obs_laboratorio,
            "9" => $reg->obs_bodega,
            "10" => $reg->obs_contabilidad
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



     case 'mostrarObsNotaCredito':
    // $id=$_POST['id'];
    $rspta=$notacredito->mostrarObsNotaCredito($id);

    

    while ($reg=$rspta->fetch_object()){

        $rsptaf=$notacredito->mostrarEvidencias($id);

        echo'<div class="card-body" bis_skin_checked="1">
        <h6>'.$reg->producto.'</h6>
        <div class="card card-info card-outline" bis_skin_checked="1">

        <div class="card-body" bis_skin_checked="1">
        <div class="custom-control " bis_skin_checked="1">
        <label >Comercial</label><br>
        '.$reg->obs_comercial.'
        </div>
        <div class="custom-control " bis_skin_checked="1">
        <label >Laboratorio</label><br>
        '.$reg->obs_laboratorio.'
        </div>
        <div class="custom-control " bis_skin_checked="1">
        <label >Bodega</label><br>
        '.$reg->obs_bodega.'
        </div>
        <div class="custom-control " bis_skin_checked="1">
        <label >Contabilidad</label><br>
        '.$reg->obs_contabilidad.'
        </div>';

        while ($reg=$rsptaf->fetch_object()){

            echo'<div class="inner float-left p-2" bis_skin_checked="1">

            <p><a target="blank" href="'.$reg->imagen.'"><img title="ver" src="../files/img/default.png" width="60px"></a></p>
            </div>';

        }

        echo'</div>
        </div>
        </div>';

    }

    break;



    case 'estadoDevoluciones':
    $fechaIni=$_POST['fechaIni'];
    $fechaFin=$_POST['fechaFin'];
    $rspta = $notacredito->estadoDevoluciones($fechaIni,$fechaFin);
    //Codificar el resultado utilizando json
    echo json_encode($rspta);
    break;



    case 'listarLaboratorio':
    $id_ped=$_POST['id_ped'];

    $vista=$_POST['vista'];

    $rspta=$notacredito->listarLaboratorio($id_ped);
        //Vamos a declarar un array
    $data= Array();

    while ($reg=$rspta->fetch_object()){

       if ($vista==1) {

           $botonEditar='<span class="btn mr-3 btn-warning" data-toggle="modal" data-target="#modalLaboratorio" onclick="idLaboratorio('.$reg->id.',\''.$reg->cant_comercial.'\')"><i class="fa fa-pencil" ></i></span>';
       }else{
        $botonEditar='';
    }

    $data[]=array(
             //consecutivo
        "0"=> $reg->fecha_sol,
        "1"=> $reg->producto,
        "2"=> $reg->cant_comercial,
        "3"=> ($reg->cant_laboratorio==0)?'<span >0</span>':$reg->cant_laboratorio,
        "4"=> ($reg->num_nc==0)?$botonEditar.
            //boton observaciones
        ' <span class="btn btn-success" title="Observaciones" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito('.$reg->id.')"><i class="fas fa-comments"></i></span>':'<span class="btn btn-success" title="Observaciones" data-toggle="modal" data-target="#modalComentario" onclick="mostrarObsNotaCredito('.$reg->id.')"><i class="fas fa-comments"></i></span>'
    );
}
$results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);
echo json_encode($results);
break;




     //devolver todo el pedido
case 'actualizarLaboratorio':
$obs_laboratorio=isset($_POST["obs_laboratorio"])? limpiarCadena($_POST["obs_laboratorio"]):"";
$cant_laboratorio=isset($_POST["cant_laboratorio"])? limpiarCadena($_POST["cant_laboratorio"]):"";

//funcion para subir multiples imagenes

    //Como el elemento es un arreglos utilizamos foreach para extraer todos los valores
foreach($_FILES["archivo"]['tmp_name'] as $key => $tmp_name)
{
        //Validamos que el archivo exista
    if($_FILES["archivo"]["name"][$key]) {
            $filename = $_FILES["archivo"]["name"][$key]; //Obtenemos el nombre original del archivo
            $source = $_FILES["archivo"]["tmp_name"][$key]; //Obtenemos un nombre temporal del archivo
            
            $directorio = '../files/notaCredito/imgLaboratorio'; //Declaramos un  variable con la ruta donde guardaremos los archivos
            
            //Validamos si la ruta de destino existe, en caso de no existir la creamos
            if(!file_exists($directorio)){
                mkdir($directorio, 0777) or die("No se puede crear el directorio de extracción");    
            }
            
            $dir=opendir($directorio); //Abrimos el directorio de destino
            $target_path = $directorio.'/'.$filename; //Indicamos la ruta de destino, así como el nombre del archivo
            
            //Movemos y validamos que el archivo se haya cargado correctamente
            //El primer campo es el origen y el segundo el destino
            if(move_uploaded_file($source, $target_path)) { 
                echo "El archivo $filename se ha almacenado en forma exitosa.<br>";
                $rspta = $notacredito->insertarEvidencia($id, $target_path);
            } else {    
                echo "Ha ocurrido un error, por favor inténtelo de nuevo.<br>";
            }
            closedir($dir); //Cerramos el directorio de destino
        }
    }

    $rspta=$notacredito->actualizarLaboratorio($id,$obs_laboratorio,$cant_laboratorio);
    echo $rspta ? "Devolucion registrada" : "No se pudo registrar";
    break;




    //devolver todo el pedido
    case 'obsBodega':
    $obs_bodega=isset($_POST["obs_bodega"])? limpiarCadena($_POST["obs_bodega"]):"";
    $rspta=$notacredito->obsBodega($id, $obs_bodega);
    echo $rspta ? "Observacion registrada" : "Observacion No se pudo registrada ";
    break;


   //actualizar contabilidad
    case 'actualizarContabiliad':
    $obs_contabilidad=isset($_POST["obs_contabilidad"])? limpiarCadena($_POST["obs_contabilidad"]):"";
    $num_nc=isset($_POST["num_nc"])? limpiarCadena($_POST["num_nc"]):"";

//funcion para subir un archivo de nota credito
    if (!file_exists($_FILES['imagen']['tmp_name']) || !is_uploaded_file($_FILES['imagen']['tmp_name']))
    {


        $imagen=$_POST["imagenactual"];


    }
    else 
    {
        $ext = explode(".", $_FILES["imagen"]["name"]);
        if ($_FILES['imagen']['type'] == "image/jpg" || $_FILES['imagen']['type'] == "image/jpeg" || $_FILES['imagen']['type'] == "image/png" || $_FILES['imagen']['type'] == "application/pdf")
        {
            $imagen = round(microtime(true)) . '.' . end($ext);
            move_uploaded_file($_FILES["imagen"]["tmp_name"], "../files/notaCredito/" . $imagen);
        }
    }
    $rspta=$notacredito->actualizarContabiliad($id,$obs_contabilidad,$num_nc,$imagen);
    echo $rspta ? "Observacion registrada" : "Observacion No se pudo registrada ";
    break;




   



    case 'MostarNotacredito':


    $rspta=$notacredito->MostarNotacredito($id_pedido);
    echo json_encode($rspta);
    break;



}

?>