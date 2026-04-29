<?php 
session_start(); 
require_once "../modelos/PedidosOnline.php";
$pedidos=new PedidosOnline();
// require_once "../modelos/Reservas.php";
// $reservas=new Reservas();
// require_once "../modelos/Productos.php";
// $productos=new Productos();



$id_prod=isset($_POST["id_prod"])? limpiarCadena($_POST["id_prod"]):"";
$id_ped=isset($_POST["id_ped"])? limpiarCadena($_POST["id_ped"]):"";
$pedido=isset($_POST["pedido"])? limpiarCadena($_POST["pedido"]):"";
$obs_anulado=isset($_POST["obs_anulado"])? limpiarCadena($_POST["obs_anulado"]):"";
$id_ped2=isset($_POST["id_ped2"])? limpiarCadena($_POST["id_ped2"]):"";

$valor=isset($_POST["valor"])? limpiarCadena($_POST["valor"]):"";
$cliente=isset($_POST["cliente"])? limpiarCadena($_POST["cliente"]):"";
$total=isset($_POST["total"])? limpiarCadena($_POST["total"]):"";
$obs=isset($_POST["obs"])? limpiarCadena($_POST["obs"]):"";

$id_mpt=isset($_POST["id_mpt"])? limpiarCadena($_POST["id_mpt"]):"";
$idusuario=isset($_POST["idusuario"])? limpiarCadena($_POST["idusuario"]):"";
$modo=isset($_POST["modo"])? limpiarCadena($_POST["modo"]):"";
$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";

$pedido_online=isset($_POST["pedido_online"])? limpiarCadena($_POST["pedido_online"]):"";
// $idproducto=isset($_POST["idproducto"])? limpiarCadena($_POST["idproducto"]):"";


switch ($_GET["op"]){


   /*=====================
    LISTAR DE VENTAS
    =====================*/
    case 'listarVentas':

    
    $fechaIni=$_POST['fechaIni'];
    $fechaFin=$_POST['fechaFin'];

    $condicion = " WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' and p.pedido_on_line='1' ";

    $roll=$_SESSION['txroll'];
    $tercero=$_SESSION['id'];
    $cot='../reportes/cotimprimir.php?id=';
    $cot2='../reportes/exCotizaciondos.php?id=';
    $urlq='../reportes/reporte.php?id=';
    $url1='ordenp.php?m=1&p=';
    $urlc='../reportes/catalogo.php?id=';



    $rspta=$pedidos->listarVentas($condicion);


        //Vamos a declarar un array
    $data= Array();

    while ($reg=$rspta->fetch_object()){

        // forma de estraer el año de la fecha reg
      $fechaComoEntero = strtotime($reg->fch_reg);
      $anio = date("y", $fechaComoEntero);
        //ERP22001940
      $ceros='000000';
        $number = strlen($reg->consecutivo); //4;
        $length = strlen($ceros); //6;

        $dif=$length-$number;//2;
        $difceros=substr($ceros, 0, $dif);
        $string = $anio.$difceros.$reg->consecutivo;


        $data[]=array(
             //consecutivo
            "0"=> '<span style=" white-space: pre;">'.$string.'</span><br>',
             //estado cambio
            "1"=>($reg->estado==10)?'<a title="Crear orden de pedido" target="_self" href="'.$url1.$reg->id_ped.'&vi=3"><button class="btn '.$reg->class_color.'" ><i class="fas fa-paste"></i> ' .$reg->tx_epedido. ' </button></a> <br>'.$reg->fch_reg :'<a target="_blank" href="'.$urlq.$reg->id_ped.'" ><button class="btn '.$reg->class_color.'"><i class="fas fa-qrcode"></i> <br>'.'#OP- '. $reg->id_op. ' </button> </a><br>'.$reg->fch_reg,
            //valor
            "2"=>$reg->nombre.'<br>  <a class="quitar" href="tel:'.$reg->telefonoc.'"><i class="fas fa-phone-alt"></i> ' . $reg->telefonoc.'</a>',

            "3"=>'$'.number_format($reg->total).'<br>'.$reg->obs.'<br><span class="btn-sm btn-success">'.$reg->txformapago.'<span>',
        //imprimir
            "4"=>($reg->observaciones==NULL)?'<a target="_blank" href="'.$cot.$reg->id_ped.'"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> ' .' <button class="btn btn-success" onclick="EnviarCothtml('.$reg->telefonoc.',\''.$reg->id_ped.'\')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button> ' . 
            ' <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalObservaciones" onclick="idpde('.$reg->id_ped.')">
            Observaciones
            </button>':'<a target="_blank" href="'.$cot.$reg->id_ped.'"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a> ' .' <button class="btn btn-success" onclick="EnviarCothtml('.$reg->telefonoc.',\''.$reg->id_ped.'\')"><i class="fa fa-whatsapp" aria-hidden="true"></i></button>'.' <a title="Catalogo" target="_blank" href="'.$urlc.$reg->id_ped.'"><button class="btn btn-primary" ><i class="fas fa-store"></i> Catalogo </button><a>', 
         //editar Anular
            "5"=>($reg->estado==10)?'<button class="btn btn-warning"  onclick="copyPedido('.$reg->id_ped.')"><i class="fa fa-pencil"></i></button>   <button class="btn btn-danger " data-toggle="modal" data-target="#ModalEliminarP" onclick="mostrar('.$reg->id_ped.')"><i class="fas fa-trash"></i></button>':''

        );
    }
    $results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);
    echo json_encode($results);
    break;



    case 'mostraFormaPago':
    $rspta = $pedidos->mostraFormaPago($id_ped);
    echo json_encode($rspta);
    break;


}
?>