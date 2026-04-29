<?php 
require_once "../modelos/Reservas.php";

if (strlen(session_id()) < 1) 
	session_start();

$reservas=new Reservas();

$idproducto=isset($_POST["idproducto"])? limpiarCadena($_POST["idproducto"]):"";

$idreserva=isset($_POST["idreserva"])? limpiarCadena($_POST["idreserva"]):"";
$cantidaR=isset($_POST["cantidaR"])? limpiarCadena($_POST["cantidaR"]):"";
$idclienteR=isset($_POST["idclienteR"])? limpiarCadena($_POST["idclienteR"]):"";
$fecha_vencimiento=isset($_POST["fecha_vencimiento"])? limpiarCadena($_POST["fecha_vencimiento"]):"";
$anticipo=isset($_POST["anticipo"])? limpiarCadena($_POST["anticipo"]):"";
$observacionR=isset($_POST["observacionR"])? limpiarCadena($_POST["observacionR"]):"";
$trnsito_stock=isset($_POST["transito"])? limpiarCadena($_POST["transito"]):"";

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";



switch ($_GET["op"]){


	case 'insertarReservas':
	$rspta=$reservas->insertarReservas($cantidaR,$idclienteR,$idproducto,$fecha_vencimiento,$anticipo,$observacionR,$trnsito_stock);
	echo $rspta ? "Reserva registrada" : "Reserva no se pudo registrar";  
	break;



	case 'editarReservas':
	$obsR=isset($_POST["obsR"])? limpiarCadena($_POST["obsR"]):"";
	$estadoR=isset($_POST["estadoR"])? limpiarCadena($_POST["estadoR"]):"";
	$rspta=$reservas->editarReservas($id,$obsR,$estadoR);
	echo $rspta ? "Reserva Actualizada" : "Reserva no se pudo actualizar"; 
	break;


	case 'listarreservas':
    $btn='';
    $currentDate = date('Y-m-d'); // Obtener la fecha actual
    $rspta = $reservas->listarreservas($idproducto);

    // Vamos a declarar un array
    $data = Array();

    while ($reg = $rspta->fetch_object()) {

        if ($reg->estado==2 || $reg->estado==4 ||  $reg->estado==3) {
           $btn='';
       }else{
        $btn='<button class="btn btn-warning " data-toggle="modal" data-target="#modalResarvaEditar" onclick="mosReserva('.$reg->idreserva.')"><i class="fa fa-pencil"></i></button>';
    }

    if ($_SESSION['id']=='81') {
        $btn='<button class="btn btn-danger "   onclick="eliminarReserva('.$reg->idreserva.')"><i class="fa fa-trash"></i></button>';
 }

 $fechaVencimiento = $reg->fecha_vencimiento;
 $fechaAnterior = date('Y-m-d', strtotime($fechaVencimiento . ' -1 day'));

 $class = ($fechaAnterior === $currentDate) ? 'resaltar' : '';

 $data[] = array(
  "0" => '<span class="'.$class.'">'.$reg->cantidad.'<br>'.$reg->txtipo.'</span>',
  "1" => $reg->nombre,
  "2" => '<p style="white-space: nowrap;">FR:'.$reg->fechareg.'<br> FV:'.$reg->fecha_vencimiento .'</p>',
  "3" => $reg->tipo_anticipo,
  "4" => $reg->txestado.'<br>'.$reg->txusuario,
  "5" => $reg->observaciones,
  "6" => $reg->obs,
  "7" => $btn,

            "DT_RowClass" => $class // Aplicar la clase a toda la fila
        );
}

$results = array(
        "sEcho" => 1, // Información para el datatables
        "iTotalRecords" => count($data), // enviamos el total registros al datatable
        "iTotalDisplayRecords" => count($data), // enviamos el total registros a visualizar
        "aaData" => $data
    );

echo json_encode($results);
break;



case 'selectEstadoReserva':

$rspta = $reservas->selectEstadoReserva();

echo '<option selected value="0">Seleccione una opción</option>';

while ($reg = $rspta->fetch_object())
{
 echo '<option value=' . $reg->idreserva  . '>' . $reg->txestado . '</option>';
}
break;




case 'cambiReservas':
$rspta=$reservas->cambiReservas();
echo $rspta ? "no" : "si";
break;


case 'mostrarReservas':
$trnsito_stock = $_POST["trnsito_stock"];
$rspta=$reservas->mostrarReservas($idproducto,$trnsito_stock);
//Codificar el resultado utilizando json
echo json_encode($rspta);
break;




case 'eliminarReserva':

$rspta=$reservas->eliminarReserva($idreserva);
echo $rspta ? "Reserva eliminada" : "Reserva no se puede eliminar";
            //Fin de las validaciones de acceso

break;







}
?>