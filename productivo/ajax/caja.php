<?php

require_once "../modelos/Caja.php";
if (strlen(session_id())<1)
	session_start(); 

$caja = new Caja();
error_reporting(E_ALL);

$idcaja=isset($_POST["idcaja"])?limpiarCadena($_POST["idcaja"]):"";
$fecha=isset($_POST["fecha"])?limpiarCadena($_POST["fecha"]):"";
$base=isset($_POST["base"])?limpiarCadena($_POST["base"]):"";
$movimiento=isset($_POST["movimiento"])?limpiarCadena($_POST["movimiento"]):"";
$total=isset($_POST["total"])?limpiarCadena($_POST["total"]):"";
$idusuario=$_SESSION['id'];

$estado=isset($_POST["estado"])?limpiarCadena($_POST["estado"]):"";


//Motivo caja

$idmotivo_caja=isset($_POST["idmotivo_caja"])?limpiarCadena($_POST["idmotivo_caja"]):"";
$nombre=isset($_POST["nombre"])?limpiarCadena($_POST["nombre"]):"";
$tipo=isset($_POST["tipo"])?limpiarCadena($_POST["tipo"]):"";

//

$totalc=isset($_POST["totals"])?limpiarCadena($_POST["totals"]):"";
$observacion=isset($_POST["observacion"])?limpiarCadena($_POST["observacion"]):"";
//$conteo=isset($_POST["conteo"])?limpiarCadena($_POST["conteo"]):"";
$finalizar=isset($_POST["finalizar"])?limpiarCadena($_POST["finalizar"]):"";
$Sinpago=isset($_POST["Sinpago"])?limpiarCadena($_POST["Sinpago"]):"";


switch ($_GET["op"]){
	case 'guardaryeditar':
	$rspta=$caja->caja();
	json_encode($rspta);
    $estado=$rspta['estado'];
	if ($estado==1) {
		echo"no se puede registrar, hay cajas abiertas";
	}else{
		
	// }
    
	// while ($reg=$rspta -> fetch_object()){
	// 	$data[]=array(
	// 		$estado=$reg->estado
	// 	);
	// }
	// switch ($estado) {
	// 	case 1:
		
	// 	break;
	// 	case 0;
		if(empty($idcaja)){
			$rspta= $caja->insertar($fecha,$base,$idusuario);
			echo $rspta ? "Registrada": "No se pudo registrar";
		}
		else {
			$rspta= $caja->editar($idcaja,$fecha,$base,$idusuario);
			echo $rspta ? "actualizado": "No se pudo actualizar";	
		}	
		break;
	}
	break;

	case 'guardarMotivo':
	if(empty($idcaja)){
		$rspta= $caja->insertarMotivo($nombre,$tipo);
		echo $rspta ? "Registrada": "No se pudo registrar";
	}
	else {
		$rspta= $caja->editarMotivo($idmotivo_caja,$nombre,$tipo);
		echo $rspta ? "actualizado": "No se pudo actualizar";

	}
	break;

	// case 'desactivar':
	// $rspta= $caja-> desactivar($idcaja,$idusuario);
	// echo $rspta ? "No se puede cerrar": "Cerrado";
	// break;

	case 'mostrar':
	$rspta= $caja-> mostrar($idcaja);
	//codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'mostrarMotivo':
	$rspta= $caja-> mostrarMotivo ($idcaja);
	//codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'activar':
	$rspta= $caja -> activar($idcaja);
	echo $rspta ? "No se puede abrir": "Abierta";
	break;


	case 'listar':
	$url1='../reportes/exCaja.php?id=';
	$urlcierre='../reportes/cierreCaja.php?id=';
	$idcaja=$_REQUEST["idcaja"];
	$rspta=$caja->listar($idcaja);
	//vamos a declarar un array
	$data = array();

	while ($reg=$rspta -> fetch_object()){
		$data[]=array(

			"0"=>($idcaja==$reg->idcaja)?'':'<button class="btn btn-primary" onclick="ver('.$reg->idcaja.',\''.$reg->idcaja.'\')"><i class="fa fa-eye"></i></button>',
			"1"=> ($idcaja==$reg->idcaja)?'<span class="btn-lg label bg-primary" style="font-size: 12px";>'.$reg->idcaja.'</span>':$reg->idcaja,
			"2"=>($reg->estado)?'<span class="btn-sm label bg-green">Abierto</span>'.'<br>'.$reg->fecha:'<span class="btn-sm label bg-red">Cerrado</span>'.'<br>'.$reg->fcha_cierre,
			"3"=> '$'.number_format($reg->base,0,',','.'), 
			"4"=> '$'.number_format($reg->total,0,',','.'), 
			"5"=> $reg->observacion,
			"6"=> $reg->usuario,
			"7"=> ($reg->estado)?'<button class="btn btn-danger" onclick="ventassinfinalizar('.$reg->idcaja.')"><i class="fa fa-close"> </i>  </button>' : '<a target="_blank" class="btn btn-primary" href="'.$urlcierre.$reg->idcaja.'" ><i class="fa fa-print"></i> Cierre</button> </a>'.' <button class="btn btn-success" onclick="activar('.$reg->idcaja.')"><i class="fa fa-check"> </i>  </button>',
			"8"=>'<a target="_blank" class="btn btn-primary" href="'.$url1.$reg->idcaja.'" ><i class="fa fa-print"></i> </button> </a>'

			

		);
	}
	$results = array(
		"sEcho"=>1, //informacion para el datatables
		"iTotalRecords"=>count($data), //enviamos el tota registros al datatable
		"iTotalDisplayRecords"=>count($data),//enviamos el total de registros a visualizar
		"aaData"=>$data);
	echo json_encode($results);

	break;


	case 'listarventas':

	$rspta = $caja->listarventas();
	$total=0;
	echo '<thead style="background-color:#A9D0F5">
	<th>Forma pago</th>
	<th>Conteo</th>
	<th>Sistema</th>
	<th><button type="button"  onclick="modificarSubototales()" class="btn-flat btn-warning elevation-1 ocultar"><i class="fa fa-refresh"></i> Calcular</button></th>
	</thead>';

	while ($reg = $rspta->fetch_object())
	{
		echo '<tr class="filas">
		<td><span class="btn-sm label bg-green">'.$reg->nombre.'</span>
		<input type="hidden" name="txfpago[]" id="txfpago[]" value="'.$reg->nombre.'"></td>
		<td><input type="number" class="form-control conteo" name="conteo[]" id="conteo[]" required="" onchange="modificarSubototales()"></td>
		<td><input type="hidden" name="sistema[]" id="sistema[]" value="'.$reg->sistema.'">'.'$'.number_format($reg->sistema,0,',','.').'</td>
		<td><span name="diferencia" id="diferencia"></span></td>
		</tr>';
		$total=$total+$reg->sistema;

	}
	echo '<tfoot>
	<th>TOTALES</th>
	<th><span name="totald" id="totald"></span></th>
	<th><span id="total">$'.number_format($total,0,',','.').'</span><input type="hidden" name="totals" id="totals" value="'.$total.'"></th>
	<th><span name="diferenciat" id="diferenciat"></span></th>
	</tfoot>';
	break;


    //funcion para saber si tiene cajas abiertas
	case 'cajaAbiertas':
	$rspta= $caja-> cajaAbiertas();
	//codificar el resultado utilizando json
	echo json_encode($rspta);;
	break;


	 //funcion para saber si tiene cajas abiertas
	case 'ventassinfinalizar':
	$rspta= $caja-> ventassinfinalizar();
	//codificar el resultado utilizando json
	echo json_encode($rspta);;
	break;


	case 'cierrecaja':
	
	
	//$conteo=json_encode($conteo);
	$rspta= $caja->cierrecaja($idcaja,$totalc,$observacion,$finalizar,$Sinpago,$_POST["conteo"],$_POST["txfpago"],$_POST["sistema"]);
	echo $rspta ? "Caja cerrada": "No se pudo cerrar";
	break;

	
}
?>