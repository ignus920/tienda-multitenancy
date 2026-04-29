<?php 
require_once "../modelos/Clientes.php";
require_once "../ajax/funciones.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php'; // Ruta al archivo PHPMailer.php
require '../PHPMailer/src/Exception.php'; // Ruta al archivo Exception.php

if (strlen(session_id()) < 1) 
	session_start();

$clientes=new Clientes();

$idcliente=isset($_POST["idcliente"])? limpiarCadena($_POST["idcliente"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";
$num_ident=isset($_POST["num_ident"])? limpiarCadena($_POST["num_ident"]):"";
$telefonoc=isset($_POST["telefonoc"])? limpiarCadena($_POST["telefonoc"]):"";
$correoc=isset($_POST["correoc"])? limpiarCadena($_POST["correoc"]):"";
$direccionc=isset($_POST["direccionc"])? limpiarCadena($_POST["direccionc"]):"";
$ciudadc=isset($_POST["ciudadc"])? limpiarCadena($_POST["ciudadc"]):"";
$depto=isset($_POST["depto"])? limpiarCadena($_POST["depto"]):"";
$regimen=isset($_POST["regimen"])? limpiarCadena($_POST["regimen"]):"";
$tipo_orden=isset($_POST["tipo_orden"])? limpiarCadena($_POST["tipo_orden"]):"";
$campo = isset($_POST['campo'])? limpiarCadena($_POST['campo']):"";
$valorClienteActualizar = isset($_POST['valorClienteActualizar'])? limpiarCadena($_POST['valorClienteActualizar']): "";
$empresa = isset($_POST['empresa'])? limpiarCadena($_POST['empresa']): "";
$actividadComercial = isset($_POST['actividadComercial'])? limpiarCadena($_POST['actividadComercial']): "";
$productosInteres = isset($_POST['productosInteres'])? limpiarCadena($_POST['productosInteres']): "";
$vista = isset($_POST['vista'])? limpiarCadena($_POST['vista']): "";
$estado_retencion = isset($_POST['estado_retencion'])? limpiarCadena($_POST['estado_retencion']): "";
$obs_pedido = isset($_POST['obs_pedido'])? limpiarCadena($_POST['obs_pedido']): "";
$login=isset($_POST["login"])? limpiarCadena($_POST["login"]):"";
$clave=isset($_POST["clave"])? limpiarCadena($_POST["clave"]):"";
$documentacion=isset($_POST["documentacion"])? limpiarCadena($_POST["documentacion"]):"";






// Función para enviar notificaciones por correo
function enviarCredencialesCorreo($nombre, $correoc, $login, $clave)
{

	$dominio = 'https://pruebas.fervicom.com';
	$url= "https://pruebas.fervicom.com/vistas/login.html";

	date_default_timezone_set("America/Bogota");
	$mail = new PHPMailer(true);
	$mail->CharSet = 'UTF-8';
	$mail->isMail(); // Usar la función mail() de PHP para enviar correos
	$mail->setFrom('compras@aplicaciones.mab.com.co', 'Fervicom iluminacion led');
	$mail->addAddress('compras@mab.com.co');
	$mail->addAddress($correoc);
	$mail->isHTML(true);
	$mail->Subject = "Credenciales de acceso - Fervicom iluminacion led";
	$mail->Body = '
	<head>
	<meta charset="UTF-8">
	<link rel="shortcut icon" href="' . $dominio . '/files/img/logomenu.png">
	
	</head>
	<body>
	<div style="width:100%; background:#f6f6f6; position:relative; font-family:sans-serif; padding-bottom:40px; border:1px solid #000000">

	<div style="position:relative; margin:auto; width:600px; background:white; padding:20px">
	<center>
	<img style="padding:20px; width:14%" src="' . $dominio . '/files/img/logomenu.png">

	<br>
	</center>

	<center>
	<h3 style="font-weight:100; color:#000000">¡Bienvenido/a a nuestro sistema de cotizaciones en línea!</h3>

	<h4 style="font-weight:100; color:#000000">' . $nombre . '</h4>

	

	<hr style="border:1px solid #000000; width:80%">

	<h4 style="font-weight:100; color:#000000; padding:0 30px; text-align: justify;" >Le damos la más cordial bienvenida a nuestro sistema de cotizaciones en línea. Con este acceso, podrá realizar sus cotizaciones de manera rápida y sencilla desde la comodidad de su hogar u oficina.
	A continuación, encontrará sus credenciales de acceso:</h4>


	<h4 style="font-weight:100; color:#000000;">Usuario: ' . $login . '</h4>
	<h4 style="font-weight:100; color:#000000;">Contraseña: ' . $clave . '</h4>

	<br>
	<h4 style="font-weight:100; color:#000000; padding:0 20px">"Por favor, haga clic en el siguiente enlace para acceder al sistema y comenzar a realizar sus cotizaciones"</h4>

	<a href="'.$url.'" target="_blank" style="text-decoration:none">

	<div style="line-height:60px; background:#1C3352; width:60%; color:white">! Click aqui ¡</div>

	</a>

	<p style="font-weight:100; color:#000000; padding:0 30px; text-align: justify;">Si tiene alguna pregunta o necesita asistencia, no dude en ponerse en contacto con nuestro equipo de soporte.</p>

	<br>

	<hr style="border:1px solid #ccc; width:80%">

	<h5 style="font-weight:100; color:#000000">¡Gracias por elegirnos para sus cotizaciones! Esperamos brindarle una experiencia excepcional.

	Saludos cordiales,
	Fervicom iluminacion led</h5>

	</center>
	

	</div>

	</div>

	</body>
	</html>';

	$envioc = $mail->Send();

}






switch ($_GET["op"]){
	case 'guardaryeditar':

	$msg = "";
	  $clavehash = ""; // Inicializar la variable de la contraseña

    // Verificar si se envió una nueva contraseña
    $actualizarClave = (isset($_POST['actualizarClave']) && $_POST['actualizarClave'] == "true");
    $clientes->editarDoc($documentacion,$idcliente);

    if ($actualizarClave) {
        $clavehash = $_POST['clave'];
    }

	if (empty($idcliente)){
		$rspta=$clientes->insertar(strtoupper($nombre),strtoupper($num_ident),$telefonoc,$correoc,strtoupper($direccionc),$depto,$ciudadc,$regimen,$login,$clavehash, $documentacion);


		echo json_encode($rspta);

		if ($login) {
			enviarCredencialesCorreo($nombre, $correoc, $login, $clave);
		}


		
		
	}
	else {

		if ($vista==1) {

			

			$rspta = $clientes->editar($idcliente, strtoupper($nombre), strtoupper($num_ident), $telefonoc, $correoc, strtoupper($direccionc), $depto, $ciudadc, $regimen, $login, $clavehash, $actualizarClave);
		}
		
		//edicion al crear orden de pedido
		if ($tipo_orden==1) {
			$id_op=isset($_POST["id_op"])?limpiarCadena($_POST["id_op"]):"";
			$id_ped=isset($_POST["id_ped"])?limpiarCadena($_POST["id_ped"]):"";
			$estado=isset($_POST["estado"])?limpiarCadena($_POST["estado"]):"";
			$tipo_entrega=isset($_POST["tipo_entrega"])?limpiarCadena($_POST["tipo_entrega"]):"";
			$obs_entrega=isset($_POST["obs_entrega"])?limpiarCadena($_POST["obs_entrega"]):"";
			$factura=isset($_POST["factura"])?limpiarCadena($_POST["factura"]):"";
			$obs_factura=isset($_POST["obs_factura"])?limpiarCadena($_POST["obs_factura"]):"";
			$impresa=isset($_POST["impresa"])?limpiarCadena($_POST["impresa"]):"";
			$idorden = isset($_POST["idorden"]) ? limpiarCadena($_POST["idorden"]) : "";

			$num=isset($_POST["num"])? limpiarCadena($_POST["num"]):"";

			require_once "../modelos/Orden_p.php";
			$orden= new Orden_p();
			//funcion subir adjuntos 
			$imagen = subirAdjunto();

            foreach ($_POST['id_formap'] as $i => $id_forma_pago) {
					$adjunto = isset($imagen[$id_forma_pago]) ? $imagen[$id_forma_pago] : 'default.jpg';
					// Aquí usas $adjunto como corresponde
				}

			if (empty($idorden)){

				$rsptaO = $orden->insertar($id_ped, $tipo_entrega, $obs_entrega, $_POST['id_formap'], $_POST['valorpago'], $_POST['detalleopcion'],$_POST['orden'],$imagen,$estado_retencion,$obs_pedido);
			}else{
				$rsptaO = $orden->editar($idorden, $id_ped, $tipo_entrega, $obs_entrega, $_POST['id_formap'], $_POST['valorpago'], $_POST['detalleopcion'],$_POST['orden'], $imagen);
				echo $rsptaO ? "Orden registrada" : "Orden no se pudo registrar";
			}
			
			require_once "../modelos/Ventas.php";
			$ventas=new Ventas();
			$rsptaV=$ventas->estadoFacturado($id_ped);

			$rsptad = $ventas->listarDetalle($id_ped);
			$encabezado="";
			$detalle="";

			while ($regd = $rsptad->fetch_object()) {
				$descuento = preg_replace("/[$.]+/", ",", $regd->descuento);
			  //$encabezado="ARTURO ARIAS RODRIGUEZ\tTOLIMA\t\tcontado\t\t156072050\t1\t\t71924116368\t1\t\t";
			  //$encabezado=$regd->nombre."\t\t\t\t\t\tcontado\t\t";
				$detalle.=$regd->codigo."\t".round($regd->cantidad)."\t\t".$descuento."\t\t";

			}

			//$info=$encabezado.$detalle;
			$info=$detalle;
			include('../public/phpqrcode/qrlib.php'); 
			$codesDir = "../files/qrcot/";   
			$codeFile = 'qr'.$id_ped.'.png';
			$ecc="H";
			$size="10";
			QRcode::png($info, $codesDir.$codeFile, $ecc, $size); 			

			echo $rsptaV; // ? "Orden Creada" : "Orden no se registro";
		}else{
			
			echo $rspta; // ? "Cliente actualizado" : "Cliente no se pudo actualizar";
		}
	}
	break;

	case 'editarRegimen':
	$rspta=$clientes->editarRegimen($idcliente, $regimen,$depto,$ciudadc,$documentacion);
	echo $rspta ? "Cliente actualizado r" : "Cliente no se pudo actualizar r";
	break;

	case 'desactivar':
	$rspta=$clientes->desactivar($idcliente);
	echo $rspta ? "Cliente Eliminada" : "Cliente no se puede eliminar";
	break;

	case 'activar':
	$rspta=$clientes->activar($idcliente);
	echo $rspta ? "Cliente activada" : "Cliente no se puede activar";
	break;

	case 'mostrar':
	$rspta=$clientes->mostrar($idcliente);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':

	$importado=$_REQUEST["importado"];

	$rspta=$clientes->listar($importado);
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(

			"0"=>$reg->nombre,
			"1"=>$reg->num_ident,
			"2"=>$reg->telefonoc,
			"3"=>$reg->direccionc,
			"4"=>$reg->correoc,
			"5"=>$reg->ciudadc.' ('.$reg->depto.')',
			"6"=>($reg->estado)?'<span class="btn-sm label bg-green">Activo</span>':
			'<span class="btn-sm label bg-red">Anulado</span>',
			"7"=>($reg->estado)?'<button class="btn btn-warning " data-toggle="modal" data-target="#ModalClientes" onclick="mostrar('.$reg->idcliente.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger " onclick="desactivar('.$reg->idcliente.')"><i class="fa fa-trash"></i></button>':
			'<button class="btn btn-warning "  data-toggle="modal" data-target="#ModalClientes" onclick="mostrar('.$reg->idcliente.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activar('.$reg->idcliente.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	
	case 'subirClientes':

	$rspta=$clientes->subirClientes();

	echo $rspta ? "La importacion de clientes no se pudo cargar" : "Importacion de clientes finalizado";

	break;





		//Solo clientes activos para modal de domicilios
	case 'listarClientesActivos':
	$rspta=$clientes->listarClientesActivos();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>'<a href="#cliente"><button class="btn btn-warning" id="cliente" onclick="tomar('.$reg->idcliente.')"><i class="fa fa-check"></i></button><a/>',
			"1"=>$reg->nombre,
			"2"=>$reg->num_ident,
			"3"=>$reg->telefonoc.'<br>'.$reg->correoc.'<br>'.$reg->direccionc
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;


	case 'buscar':
	$valor=$_REQUEST["valor"];
	$rspta=$clientes->buscar($valor);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;



		//Solo clientes activos para modal de domicilios
	case 'listarClientesBuscar':

	$buscar=$_REQUEST['buscar'];

	$rspta=$clientes->listarClientesBuscar($buscar);
 		//Vamos a declarar un listarClientes
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			"0"=>'<button class="btn btn-warning" onclick="escogerCliente('.$reg->idcliente.')"><i class="fa fa-check"></i></button>',
			"1"=>$reg->nombre,
			"2"=>$reg->num_ident,
			"3"=>$reg->telefonoc.'<br>'.$reg->correoc.'<br>'.$reg->direccionc
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	case 'actualizarCliente':
	$rspta = $clientes->actualizarCliente($idcliente, $campo, $valorClienteActualizar);
	echo $rspta ? "Cliente actualizado" : "No se pudo actualizar el cliente";
	break;

	case 'crearClienteMercadeo':
	$rspta = $clientes->crearClienteMercadeo($nombre, $direccionc, $telefonoc, $empresa, $actividadComercial, $productosInteres);
	echo $rspta ? "Cliente creado" : "No se pudo crear el cliente";
	break;

	case 'selectCliente':

	$rspta = $clientes->selectCliente();

	echo '<option  value="">Seleccione una opción</option>';

	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->idcliente.'>' . $reg->nombre . '</option>';
	}
	break;








    //verificar cliente 
	case 'verificarCliente':
	$logina = $_POST['logina'];
	$clavea = $_POST['clavea'];

    // Hash SHA256 en la contraseña
	$clavehash = hash("SHA256", $clavea);

	$rspta = $clientes->verificarCliente($logina, $clavehash);

	if ($rspta->num_rows > 0) {
		$fetch = $rspta->fetch_object();
        //Declaramos las variables de sesión
		$_SESSION['idcliente'] = $fetch->idcliente;
		$_SESSION['nombre'] = $fetch->nombre;
		$_SESSION['estado'] = $fetch->estado;

		$fetch->idcliente == $idcliente ? $_SESSION['Cliente'] = 1 : $_SESSION['Cliente'] = 0;

        echo json_encode(array("idcliente" => $fetch->idcliente)); // Devolvemos el id del cliente
    } else {
        echo json_encode(array("error" => "Usuario y/o contraseña incorrectos")); // Devolvemos un error si no se encontró el cliente
    }
    break;





    case 'salirCliente':

		//Limpiamos las variables de sesión   
    session_unset();
        //Destruìmos la sesión
    session_destroy();

       //Redireccionamos al login
    header("Location: ../vistas/login.html");

    break;









}

?>