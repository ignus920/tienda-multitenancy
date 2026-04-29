<?php 
require_once "../modelos/Gestion.php";
$gestion=new Gestion();


require_once "../modelos/Ventas.php";
$ventas=new Ventas();

if (strlen(session_id()) < 1) 
	session_start();

$id_ped=isset($_POST["id_ped"])? limpiarCadena($_POST["id_ped"]):"";
$comentario=isset($_POST["comentario"])? limpiarCadena($_POST["comentario"]):"";
$estado_segimiento=isset($_POST["estado_segimiento"])? limpiarCadena($_POST["estado_segimiento"]):"";
$idcot=isset($_POST["idcot"])? limpiarCadena($_POST["idcot"]):"";
$idusuario=isset($_POST["idusuario"])? limpiarCadena($_POST["idusuario"]):"";




switch ($_GET["op"]){
	

	case 'mostrar':
	$rspta=$gestion->mostrar($id_ped);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'mostrarNombre':
	$cat=$_REQUEST["cat"];
	$rspta=$gestion->mostrarNombre($cat);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;
	

	


	case 'listar':

	$fechaIni=$_REQUEST["fechaIni"];
	$fechaFin=$_REQUEST["fechaFin"];
	$cat=$_REQUEST["cat"];
	$estado=$_REQUEST["estado"];

	


	$rspta=$gestion->listar($fechaIni,$fechaFin,$cat,$estado,$idusuario);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>($cat==$reg->cliente)?'':'<button class="btn btn-success" onclick="ver('.$reg->id_ped.')"><i class="fa fa-eye"></i></button>',
			"1"=>($cat==$reg->cliente)?'<span class="btn-lg label bg-green"  style="font-size: 14px";>'.$reg->nombre.'</span>':$reg->nombre,
			"2"=>$reg->usuario,
			"3"=>'<span class="btn-sm label bg-primary">'.$reg->txestado.'</span>',
			"4"=>$reg->total,
			"5"=>$reg->totalestado



			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;




	case 'listarCot':

	$fechaIni=$_REQUEST["fechaIni"];
	$fechaFin=$_REQUEST["fechaFin"];
	$id_ped=$_REQUEST["id_ped"];


	$rspta=$gestion->listarCot($fechaIni,$fechaFin,$id_ped);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){

		        // forma de estraer el año de la fecha reg
        $fechaComoEntero = strtotime($reg->fch_reg);
        $anio = date("y", $fechaComoEntero);
        //ERP22000001
        //ERP22001940
        $ceros='000000';
        $number = strlen($reg->consecutivo); //4;
        $length = strlen($ceros); //6;

        $dif=$length-$number;//2;
        $difceros=substr($ceros, 0, $dif);
        $string = $anio.$difceros.$reg->consecutivo;
		$data[]=array(

			"0"=>'<span style=" white-space: pre;">'.$string.'</span>',
			"1"=>$reg->usuario,
			"2"=>$reg->fecha,
			"3"=>'<span class="btn-sm label bg-primary">'.$reg->tx_epedido.'</span>',
			"4"=>($reg->pedido=='Sin seguimiento')?'<span class="btn-sm label bg-red">'.$reg->pedido.'</span>':'<span class="btn-sm label '.$reg->color.'">'.$reg->pedido.'</span>',
			"5"=>$reg->observacion,
			"6"=>'<button class="btn btn-success" data-toggle="modal" data-target="#modalGestion" onclick="mostrar('.$reg->id_ped.')"><i class="fa fa-file"></i></button>'.' <button class="btn btn-primary" data-toggle="modal" data-target="#modalComentario" onclick="listarComentarios('.$reg->idcot.')"><i class="fa fa-comment"></i></button>'

		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;






	case 'listarDetalle':

	$pedido=$_GET['pedido'];

	$rspta=$ventas->listarDetalle($pedido);
        //Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->descripcion.$reg->id_mpt,
			"1"=>'$'.number_format($reg->precio,0,',','.').' X '.$reg->cantidad,
			"2"=>'$'.number_format($reg->subtotal,0,',','.')

		);
	}
	$results = array(
            "sEcho"=>1, //Información para el datatables
            "iTotalRecords"=>count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
            "aaData"=>$data);
	echo json_encode($results);
	break;





	case 'guardareditar':
	
		$rspta=$gestion->insertar($comentario,$estado_segimiento,$idcot);
		echo $rspta ? "Seguimiento registrado" : "Seguimiento no se pudo registrar";
	break;





	case 'listarCot_Vendedor':

	$fechaIni=$_REQUEST["fechaIni"];
	$fechaFin=$_REQUEST["fechaFin"];

	$cat=$_REQUEST["cat"];



	$rspta=$gestion->listarCot_Vendedor($fechaIni,$fechaFin);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>'<a href="#idVendedor"><button class="btn btn-success" onclick="listar('.$reg->idusuario.')"><i class="fa fa-eye"></i></button></a>',
			"1"=>$reg->usuario,
			"2"=>$reg->cot,
			"3"=>$reg->vendidas,
			"4"=>$reg->pend



			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;







	case 'listarComentarios':


	$rspta=$gestion->listarComentarios($idcot);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->comentario,
			"1"=>$reg->fecha_reg
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;




	

}
?>