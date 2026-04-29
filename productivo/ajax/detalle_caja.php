<?php 
if (strlen(session_id())<1)
	session_start(); 
require_once "../modelos/Detalle_caja.php";

$detalle_caja= new Detalle_caja();
error_reporting(E_ALL);


$iddetalle_caja=isset($_POST["iddetalle_caja"])?limpiarCadena($_POST["iddetalle_caja"]):"";
$d_pago=isset($_POST["d_pago"])?limpiarCadena($_POST["d_pago"]):"";
$idfactura=isset($_POST["idfactura"])?limpiarCadena($_POST["idfactura"]):"";
$idmotivo=isset($_POST["idmotivo"])?limpiarCadena($_POST["idmotivo"]):"";
$idforma_pago=isset($_POST["idforma_pago"])?limpiarCadena($_POST["idforma_pago"]):"";
$valor=isset($_POST["valor"])?limpiarCadena($_POST["valor"]):"";
$abierta=isset($_POST["abierta"])?limpiarCadena($_POST["abierta"]):"";
$idventa=isset($_POST["idventa"])?limpiarCadena($_POST["idventa"]):"";
$caja=isset($_POST["caja"])?limpiarCadena($_POST["caja"]):"";
$idcaja=isset($_POST["idcaja"])?limpiarCadena($_POST["idcaja"]):"";
$result=isset($_POST["result"])?limpiarCadena($_POST["result"]):"";
$resultd=isset($_POST["resultd"])?limpiarCadena($_POST["resultd"]):"";

$detalle=isset($_POST["detalle"])?limpiarCadena($_POST["detalle"]):"";
$observaciones=isset($_POST["observaciones"])?limpiarCadena($_POST["observaciones"]):"";

$idusuario=$_SESSION['id'];



switch ($_GET["op"]){
	case 'guardaryeditar':
	
	if(empty($iddetalle_caja)){
		$rspta= $detalle_caja->insertar($idfactura,$idmotivo,$idforma_pago,$valor,$idusuario,$d_pago,$result,$detalle,$resultd,$observaciones);
		echo $rspta ? "Registrada": "No se pudo registrar";
	}
	else {
		$rspta= $detalle_caja->editar($iddetalle_caja,$idcaja,$idfactura,$idmotivo,$idforma_pago,$valor,$result,$resultd);
		echo $rspta ? "actualizado": "No se pudo actualizar";
	}
	break;




	case "selectCaja":
	require_once "../modelos/Caja.php";
	$caja= new Caja();
	$rspta=$caja ->listarcaja();

	while ($reg=$rspta -> fetch_object())
	{
		echo'<option value='.$reg->idcaja.'>'.$reg->idcaja.'/'.$reg->fecha.'</option>';
	}
	break;


	case "selectMotivo_caja":
	require_once "../modelos/Motivo_caja.php";
	$motivo_caja= new Motivo_caja();
	echo'<option selected value="0">Seleccione una opcion</option>';
	$rspta=$motivo_caja ->selectMovimiento();

	while ($reg=$rspta -> fetch_object())
	{
		echo'<option value='.$reg->idmotivo_caja.'>'.$reg->nombre.'</option>';
	}
	break;


	case "caja":
	
	$rspta=$detalle_caja->caja();
	echo'<option >Seleccione una opcion</option>';
	while ($reg=$rspta -> fetch_object())
	{
		echo'<option value='.$reg->idcaja.'>'.$reg->idcaja.' / '.$reg->fecha.'/ Caja '. $reg->estado .'</option>';
	}
	break;


	case "selectVentas":
	
	$rspta=$detalle_caja->selectVentas();
	echo'<option >Seleccione una opcion</option>';
	while ($reg=$rspta -> fetch_object())
	{
		echo'<option value='.$reg->id.'>'.'Placa'.$reg->placa.' / '.'$'.number_format($reg->saldo,0,',','.').'</option>';
	}

	break;




	case 'anular':
	$rspta= $detalle_caja-> validarMotivo($iddetalle_caja);
	echo json_encode($rspta);

	$motivo=$rspta['idmotivo'];
	
	if ($motivo==3) {
		$rspta= $detalle_caja-> anularMotVenta($iddetalle_caja,$idfactura);
		echo $rspta ? "Pago anulado 1": "No puede ser eliminado";
	}else{
		$rspta= $detalle_caja-> anular($iddetalle_caja,$idfactura);
		echo $rspta ? "Pago anulado 2": "No puede ser eliminado";
	}
	break;
	

	case 'mostrar':
	$rspta= $detalle_caja-> mostrar($iddetalle_caja);
	//codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'listar':
	$idcaja=$_REQUEST['idcaja'];
	$rspta=$detalle_caja ->listar($idcaja);
	//vamos a declarar un array
	$data = array();

	while ($reg=$rspta -> fetch_object()){
		$data[]=array(
			"0"=>'<span class="btn-sm label bg-primary">'.$reg->idcaja.'</span>',
			"1"=> $reg->factura,
			"2"=> $reg->nombre.'<br>'.$reg->detalle,
			"3"=> $reg->forma_pago,
			"4"=> '$ '.number_format($reg->valor,0,',','.'),
			"5"=> $reg->fecha_reg,
			"6"=> ($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"7"=> ($reg->estado and $reg->idmotivo!=4)?' <button class="btn btn-danger" onclick="anular('.$reg->id_pago.',\''.$reg->id_ordenp.'\')"><i class="fa fa-close"></i></button>' . " <a target='blank' href='../files/pagos/".$reg->adjunto."'><img  src='../files/img/default.png'  width='60px' ></a>":'',


		);
	}
	$results = array(
		"sEcho"=>1, //informacion para el datatables
		"iTotalRecords"=>count($data), //enviamos el tota registros al datatable
		"iTotalDisplayRecords"=>count($data),//enviamos el total de registros a visualizar
		"aaData"=>$data);
	echo json_encode($results);

	break;
}
?>