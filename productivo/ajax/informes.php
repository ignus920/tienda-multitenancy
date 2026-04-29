<?php 
if (strlen(session_id())<1)
	session_start();
require_once "../modelos/Informes.php";

$informes=new Informes();

$fechaIni=isset($_POST["fechaIni"])?limpiarCadena($_POST["fechaIni"]):"";
$fechaFin=isset($_POST["fechaFin"])?limpiarCadena($_POST["fechaFin"]):"";
$id_saldo=isset($_POST["id_saldo"])?limpiarCadena($_POST["id_saldo"]):"";
$operario=isset($_POST["operario"])?limpiarCadena($_POST["operario"]):"";
$idcliente=isset($_POST["idcliente"])?limpiarCadena($_POST["idcliente"]):"";
$placac=isset($_POST["placac"])?limpiarCadena($_POST["placac"]):"";
$idmotivof=isset($_POST["idmotivof"])?limpiarCadena($_POST["idmotivof"]):"";





switch ($_GET["op"]){



	case 'ventaxVendedor':
	$fechaIni=$_GET["fechaIni"];
	$fechaFin=$_GET["fechaFin"];
	$idtercero=$_GET["idtercero"];
	
	$rspta=$informes->ventaxVendedor($fechaIni,$fechaFin,$idtercero);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){

		if ($reg->estado==2 || $reg->estado==21 || $reg->estado==22) {
			$iva=0;
			$total=0;
		}else{
			$iva=$reg->sin_iva;
			$total=$reg->total;
		}
		$data[]=array(
			"0"=>$reg->vendedor,
			"1"=>$reg->cot,
			"2"=>$reg->tx_epedido,
			"3"=>$reg->orden_pedido,
			"4"=>$reg->factura,
			"5"=>$reg->fecha_pedido,
			"6"=>$reg->descripcion,
			"7"=>'$'.number_format($iva,0,'.',','),
			"8"=>'$'.number_format($total,0,'.',','),
			"9"=>($reg->id_producto==1)?'<span>GENERICO</span>':$reg->clasificacion,
			"10"=>$reg->formapago
			
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;


	case 'selectVendedor':
		//echo '<option  >Seleccione una opción</option>';
	$rspta = $informes->selectVendedor($fechaIni,$fechaFin);
	
	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->idusuario.'>' . $reg->nombre . '</option>';
	}
	break;






	case 'ventaxCotProducto':
	$fechaIni=$_GET["fechaIni"];
	$fechaFin=$_GET["fechaFin"];
	
	
	$rspta=$informes->ventaxCotProducto($fechaIni,$fechaFin);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>$reg->codigo.'-'.$reg->descripcion,
			"1"=>$reg->cotizaciones,
			"2"=>$reg->pedidos,
			"3"=>$reg->porpedidos.' %',
			"4"=>$reg->unid,
			"5"=>$reg->unidpedido,
            "6" => '<input type="checkbox" onclick="obtenerProductosSeleccionados()" name="productoSeleccionado[]" value="'. $reg->id_producto.'" />'
            

            
        );
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;








	case 'ventaxCotProductoDetalle':
	$fechaIni=$_GET["fechaIni"];
	$fechaFin=$_GET["fechaFin"];
	$idItems=$_GET["idItems"];

      $idItems = isset($_GET["idItems"]) ? $_GET["idItems"] : array(); // Obtener la lista de IDs de clientes seleccionados

    // Convertir la lista de IDs de clientes en una cadena separada por comas
      $idItemsString = implode(",", $idItems);
      $cot = '../reportes/cotimprimir.php?id=';
      
      $rspta=$informes->ventaxCotProductoDetalle($fechaIni,$fechaFin,$idItemsString);
 		//Vamos a declarar un array
      $data= Array();

      while ($reg=$rspta->fetch_object()){
		// forma de estraer el año de la fecha reg
          $fechaComoEntero = strtotime($reg->fecha);
          $anio = date("y", $fechaComoEntero);
            //ERP22001940
          $ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;
            $data[]=array(
            	"0"=>$reg->descripcion,
                "1"=>$reg->descripcion,
            	"2"=>$reg->fecha,
            	"3"=>'<a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a>',
            	"4"=>$reg->cliente ,
            	"5"=>$reg->txasesor ,
            	"6"=>$reg->num_ident,
            	"7"=>$reg->cantidad,
            	"8"=>$reg->tx_epedido,
            	"9"=>$reg->clasificacion
            );
        }
        $results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
        echo json_encode($results);

        break;







        //funcion de listar por estados
        case 'listarEstados':


        $fechaIni = $_GET["fechaIni"];
        $fechaFin = $_GET["fechaFin"];
        $idestado = $_GET["idestado"];

        $consulta=" WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' ";


        if ($idestado=="") {
        	$consulta=" WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' ";
        }else{

        	$consulta=" WHERE date(p.fecha) BETWEEN '$fechaIni' and '$fechaFin' and o.estado='$idestado' ";
        }

        // $limit = 100;

       //  switch ($condicion) {
       //  //registrado
       //  	case 'registrado':
       //  	$consulta = " WHERE o.estado=3" ;
       //  	break;

       //  //alistamiento
       //  	case 'alistamiento':
       //  	$consulta = " WHERE o.estado=17" ;
       //  	break;

       // //sin entregar
       //  	case 'Sin_entregar':
       //  	$consulta = " WHERE o.estado between 17 and 19" ;
       //  	break;

       //  //sin factura
       //  	case 'facturado':
       //  	$consulta = " where (o.factura='' or o.factura is null or o.factura='0') and o.estado not in (2,21,22) " ;
       //  	break;

       //  //default
       //  	default:
       //  	$consulta = " WHERE date(o.fecha_reg) BETWEEN '$fecha1' AND '$fecha2'";
       //  	break;
       //  }
       //  // de Almacen

       //  if ($selectFiltrar != "" && $datoFiltrar != "") {
       //  	($selectFiltrar == 'pedido') ? $consulta = " WHERE o.id_op = '$datoFiltrar' " : null;
       //  	($selectFiltrar == 'cotizacion') ? $consulta = " WHERE p.consecutivo  LIKE '%$datoFiltrar%' " : null;
       //  	($selectFiltrar == 'cliente') ? $consulta = " WHERE c.nombre LIKE '%$datoFiltrar%' " : null;
       //  }

       //  ($festado != "") ? $consulta = $consulta . " AND o.estado='$festado'" : null;

        $rspta = $informes->listarEstados($consulta);
        $data = array();
        $cot = '../reportes/cotimprimir.php?id=';
        $envio = '../reportes/envio.php?id=';
        $pedido = '../reportes/ordenp.php?id=';

        while ($reg = $rspta->fetch_object()) {

        	// forma de estraer el a駉 de la fecha reg
        	$fechaComoEntero = strtotime($reg->fechacot);
        	$anio = date("y", $fechaComoEntero);
            //ERP22001940
        	$ceros = '000000';
            $number = strlen($reg->consecutivo); //4;
            $length = strlen($ceros); //6;

            $dif = $length - $number; //2;
            $difceros = substr($ceros, 0, $dif);
            $string = $anio . $difceros . $reg->consecutivo;



            $data[] = array(

            	"0" => $reg->id_op,
            	"1" => $reg->fecha_reg,
            	"2" => $reg->cliente . '<br><a target="_blank" href="' . $cot . $reg->id_ped . '">ERP' . $string . '</a> <br> <b>Tipo de entrega:</b> ' . $reg->tipoEntrega,
            	"3" => $reg->tx_epedido,
            	"4" => $reg->obs_entrega,
            	"5" => $reg->obs_factura,
            	"6" => $reg->obs_impresa,
            	"7" => $reg->obs_entregado,
            	"8" => $reg->obs_cancelar,
            	"9" => $reg->obs_pedido

            	
            );
        }
        $results = array(
            "sEcho" => 1, //Informaci髇 para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;




        case 'selectEstados':

        $rspta = $informes->selectEstados();

        echo '<option selected value="">Seleccione una opcion</option>';

        while ($reg = $rspta->fetch_object())
        {
        	echo '<option value=' . $reg->id_ep . '>' . $reg->tx_epedido . '</option>';
        }
        break;







        case 'resumenCliente':
        $fechaIni=$_GET["fechaIni"];
        $fechaFin=$_GET["fechaFin"];

        $rspta=$informes->resumenCliente($fechaIni,$fechaFin);
 		//Vamos a declarar un array
        $data= Array();

        while ($reg=$rspta->fetch_object()){

        	$data[]=array(
        		"0"=>$reg->nombre,
        		"1"=>$reg->cotizaciones,
        		"2"=>$reg->pedidos,
        		"3"=>$reg->v_cotizado,
        		"4"=>$reg->v_pedidos,
        		"5"=>$reg->porcen,
        		"6" => '<input type="checkbox" onclick="obtenerClientesSeleccionados()" name="clienteSeleccionado[]" value="'. $reg->idcliente.'" />'

        	);
        }
        $results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
        echo json_encode($results);

        break;




        case 'detalleProducto':
        $fechaIni = $_GET["fechaIni"];
        $fechaFin = $_GET["fechaFin"];
    $idcliente = isset($_GET["idcliente"]) ? $_GET["idcliente"] : array(); // Obtener la lista de IDs de clientes seleccionados

    // Convertir la lista de IDs de clientes en una cadena separada por comas
    $clientesString = implode(",", $idcliente);

    // Llamar a la funci髇 detalleProducto con la lista de IDs de clientes
    $rspta = $informes->detalleProducto($fechaIni, $fechaFin, $clientesString);

    // Inicializar el array de datos
    $data = array();

    // Procesar los resultados de la consulta
    while ($reg = $rspta->fetch_object()) {
        $data[] = array(
            "0" => $reg->nombre,
            "1" => $reg->codigo . '-' . $reg->descripcion,
            "2" => $reg->cotizaciones,
            "3" => $reg->pedidos,
            "4" => $reg->cotizado,
            "5" => $reg->pedido
        );
    }

    // Preparar la respuesta para DataTables
    $results = array(
        "sEcho" => 1, // Informaci髇 para el datatables
        "iTotalRecords" => count($data), // Total de registros
        "iTotalDisplayRecords" => count($data), // Total de registros a visualizar
        "aaData" => $data // Datos a mostrar
    );

    // Enviar la respuesta como JSON
    echo json_encode($results);

    break;




    








}
?>