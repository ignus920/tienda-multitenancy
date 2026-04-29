<?php 
require_once "../modelos/Importacion.php";

if (strlen(session_id()) < 1) 
	session_start();

$importacion=new Importacion();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$id_impor=isset($_POST["id_impor"])? limpiarCadena($_POST["id_impor"]):"";
$codigo=isset($_POST["codigo"])? limpiarCadena($_POST["codigo"]):"";
$idproducto=isset($_POST["idproducto"])? limpiarCadena($_POST["idproducto"]):"";
$cantidad=isset($_POST["cantidad"])? limpiarCadena($_POST["cantidad"]):"";
$idproveedor=isset($_POST["idproveedor"])? limpiarCadena($_POST["idproveedor"]):"";
$tx_pedido=isset($_POST["tx_pedido"])? limpiarCadena($_POST["tx_pedido"]):"";
$customRadio=isset($_POST["customRadio"])? limpiarCadena($_POST["customRadio"]):"";
$precio=isset($_POST["precio"])? limpiarCadena($_POST["precio"]):"";

$proveedor=isset($_POST["proveedor"])? limpiarCadena($_POST["proveedor"]):"";
$id_compras=isset($_POST["id_compras"])? limpiarCadena($_POST["id_compras"]):"";




switch ($_GET["op"]){





	case 'subirInventario':

	$rspta=$importacion->subirInventario();

	echo $rspta ? "La importacion  no se pudo cargar" : "Importacion finalizada";

	break;





	case 'mostrar':
	$rspta=$importacion->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	case 'listarInventario':

	$consulta = "";
	$abajo = "";

	if ( $customRadio!="") {

		switch ($customRadio) {
			case '200':
				// salidas =0
			$consulta= " WHERE r.total_salidas='0'";
			break;

			case '0':
				// existencias =0
			$consulta= " WHERE r.saldo_final='0'";

			break;
			
			default:
			$abajo=$customRadio-20;
			$consulta= " WHERE (r.total_salidas/(r.saldo_inicial+r.total_entradas))*100 BETWEEN '$abajo' and '$customRadio'";
			break;
		}

	}
	
	$rspta=$importacion->listarInventario($consulta);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){

		if ($reg->cantidad=="") {
			//  SIN CANTIDAD
			$boton='<button class="btn btn-warning "  data-toggle="modal" data-target="#modalInventario" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>';
		}else{

			// CON CANTIDAD
			$boton='<span class="btn label bg-green" data-toggle="modal" data-target="#modalInventario" onclick="mostrar('.$reg->id.')">'.$reg->cantidad.'</span>';
		} 
		if ($reg->idproducto=="") {

            // SIN NO EXISTE EN EL ERP
			$boton='<h5 class="card-block text-danger text-info">No esta en el erp</h5>';

		}
		$data[]=array(
			
			"0"=>$reg->descripcion,
			"1"=>number_format($reg->inventario),
			"2"=>$reg->total_salidas.'<br>'.number_format($reg->porcentaje_ventas).' %' , //pendiente quitarle los decimales, ya que no me funciono cuando ,hice el cambio
			"3"=>$reg->saldo_final,
			"4"=>$boton,
			"5"=>$reg->saldo_inicial,
			"6"=>'E-'.$reg->entrada1.'<br>S-'.$reg->salida1,
			"7"=>'E-'.$reg->entrada2.'<br>S-'.$reg->salida2,
			"8"=>'E-'.$reg->entrada3.'<br>S-'.$reg->salida3,
			"9"=>'E-'.$reg->entrada4.'<br>S-'.$reg->salida4,
			"10"=>'E-'.$reg->entrada5.'<br>S-'.$reg->salida5,
			"11"=>'E-'.$reg->entrada6.'<br>S-'.$reg->salida6,
			"12"=>'E-'.$reg->total_entradas.'<br>S-'. $reg->total_salidas
			
			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;



	case 'insertar':
	if (empty($id_impor)) {
		//inserto nuevo
		$rspta=$importacion->insertar($codigo,$idproducto,$cantidad,$tx_pedido);
		echo $rspta ? "Cantidad registrada" : "Cantidad no se pudo registrar";
	}else{
		//edito lacantidad
		$rspta=$importacion->editar($id_impor,$cantidad);
		echo $rspta ? "Cantidad actualizada" : "Cantidad no se pudo actualizar";
	}

	

	break;


	case 'actualizarIdpedido':

	$rspta=$importacion->actualizarIdpedido($id_compras,$id);
	echo $rspta ? "Pedido registrada" : "Pedido no se pudo registrar";

	break;










	


/////////////////////////////////////////

	case 'mostrarImportacion':
	$rspta=$importacion->mostrarImportacion($idproducto);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;


	    //al cambiar el estado los productos salen del almacen
	case 'cambiarEstado':
	$rspta=$importacion->cambiarEstado($id);
	echo $rspta;
	break;


	case 'listarImportacion':

	$valor=$_GET['valor'];
	$condicion=$_GET['condicion'];
	$consulta="";
	//recibo una condicion
	switch ($condicion) {
		case '0':
				//todos los registros

		$consulta="";
		break;
		case '1':
			//los de un compra para ver los detalles de una venta

		$consulta="  where i.id_compras='$valor'";
		break;
		case '2':			
	//sin proveedor
		$consulta="  where i.idproveedor='0'";
		break;
		case '3':
			//sin orden de compra
		$consulta="  where i.id_compras is null";
		break;

	}
	
	

	$rspta=$importacion->listarImportacion($consulta);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){


			$boton='';

		if ($reg->id_compras==0 || $reg->id_compras=='null') {

			$rspta1=$importacion->mostrarPed($reg->id);
			

			if ($rspta1['cuenta']!='0') {

				$valor=$rspta1['id_compras'];
				
			$boton='<button class="btn btn-success" onclick="actualizarIdpedido('.$valor.',\''.$reg->id.'\')"><i class="fa fa-plus"></i></button>';
			}


		}

		$data[]=array(
			
			"0"=>$reg->tx_pedido.'<br>'.$reg->descripcion,
			"1"=>($reg->id_compras==0)?'':$reg->id_compras,
			"2"=>$reg->nombre,
			"3"=>'<span class="btn label bg-green">'.$reg->cantidad.'</span>',
			"4"=>($reg->precio=="")?'':$reg->precio,
			"5"=>'<span class="btn-sm label '.$reg->class_color.' ">'.$reg->tx_epedido.'</span>',
			"6"=>($reg->id_ep>11 or $reg->id_compras=='null')?'':'<button class="btn btn-warning "  data-toggle="modal" data-target="#modalProveedor" onclick="mostrarImportacion('.$reg->idproducto.')"><i class="fa fa-pencil"></i></button>'.' <button class="btn btn-danger" onclick="eliminarDetalle('.$reg->id.')"><i class="fa fa-trash"></i></button>',
			"7"=>$boton
			
			
			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;






	case 'listarCompras':

	$ped='../reportes/exPedido.php?id=';

	$rspta=$importacion->listarCompras();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){

		$data[]=array(
			"0"=>$reg->id_compras,
			"1"=>$reg->nombre,
			"2"=>'<a href="javascript:cambiarEstado('.$reg->id_compras.')"><span class="btn label '.$reg->class_color.' elevation-1">'.$reg->tx_epedido.'</span></a>',
			"3"=>' <a href="#tblImportacion"> <button class="btn btn-warning " onclick="listarImportacion(1,'.$reg->id_compras.')"><i class="fa fa-pencil"></i></button></a>' .' <button class="btn btn-danger" onclick="eliminarCompras('.$reg->id_compras.')"><i class="fa fa-trash"></i></button>'.' <a target="_blank" href="'.$ped.$reg->id_compras.'"> <button class="btn btn-primary"><i class="fa fa-print" aria-hidden="true"></i></button></a>'			
			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;


	case 'insertarProveedor':

	$rspta=$importacion->insertarProveedor($idproducto,$tx_pedido,$idproveedor,$precio,$cantidad,$id_compras);
	echo $rspta ? "Lista de pedido actualizado" : "La lista del pedido no se pudo actualizar";
	
	
	break;


	case 'insertarCompra':
	
	$rspta=$importacion->insertarCompra($proveedor);
	echo $rspta ? "Compra registrada" : "Compra no se pudo registrar";
	

	break;


	case 'eliminarDetalle':
	$rspta=$importacion->eliminarDetalle($id);
	echo $rspta ? "Detalle eliminado" : "Detalle no se puede eliminar";
	break;


	case 'eliminarCompras':
	$rspta=$importacion->eliminarCompras($id_compras);
	echo $rspta ? "Compra desactivada" : "Caompar no se puede desactivar";
	break;





	case 'mostrarPed':
	$rspta=$importacion->mostrarPed($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;



}
?>