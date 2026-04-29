<?php

require_once "../modelos/Usuario.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/PHPMailer.php'; // Ruta al archivo PHPMailer.php
require '../PHPMailer/src/Exception.php'; // Ruta al archivo Exception.php

if (strlen(session_id()) < 1) 
	session_start();

$usuario=new Usuario();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$nombre=isset($_POST["nombre"])? limpiarCadena($_POST["nombre"]):"";
$telefono=isset($_POST["telefono"])? limpiarCadena($_POST["telefono"]):"";
$correo=isset($_POST["correo"])? limpiarCadena($_POST["correo"]):"";
$cargo=isset($_POST["cargo"])? limpiarCadena($_POST["cargo"]):"";
$login=isset($_POST["login"])? limpiarCadena($_POST["login"]):"";
$clave=isset($_POST["clave"])? limpiarCadena($_POST["clave"]):"";

//variables para envio de coreo y cambio de contraseña


$password=isset($_POST["password"])? limpiarCadena($_POST["password"]):"";
$password1=isset($_POST["password1"])? limpiarCadena($_POST["password1"]):"";
$password2=isset($_POST["password2"])? limpiarCadena($_POST["password2"]):"";
$password3=isset($_POST["password3"])? limpiarCadena($_POST["password3"]):"";
$idusuarioclave=isset($_POST["idusuarioclave"])? limpiarCadena($_POST["idusuarioclave"]):"";
$claveAntigua=isset($_POST["claveAntigua"])? limpiarCadena($_POST["claveAntigua"]):"";




switch ($_GET["op"]){
	case 'guardaryeditar':
	
	if($clave == ""){

		$clavehash = $claveAntigua;

	}else{

		$clavehash=hash("SHA256",$clave);

	}



	if (empty($id)){
		$rspta=$usuario->insertar($nombre,$telefono,$correo,$cargo,$login,$clavehash);
		echo $rspta ? "Usuario registrado" : "No se pudieron registrar todos los datos del usuario";
	}
	else {
		$rspta=$usuario->editar($id,$nombre,$telefono,$correo,$cargo,$login,$clavehash,$_POST['permiso']);
		echo $rspta ? "Usuario actualizado" : "Usuario no se pudo actualizar";
	}
	
	break;

	case 'desactivar':
	$rspta=$usuario->desactivar($id);
	echo $rspta ? "Usuario Desactivado" : "Usuario no se puede desactivar";
	break;

	case 'activar':
	$rspta=$usuario->activar($id);
	echo $rspta ? "Usuario activado" : "Usuario no se puede activar";
	break;

	// case 'marcarIngreso':
	// $rspta=$usuario->marcarIngreso($id);
 	// 	// echo $rspta ? "Usuario activado" : "Usuario no se puede activar";
	// break;

	case 'mostrar':
	$rspta=$usuario->mostrar($id);
 		//Codificar el resultado utilizando json
	echo json_encode($rspta);
	break;

	case 'listar':
	$rspta=$usuario->listar();
 		//Vamos a declarar un array
	$data= Array();

	while ($reg=$rspta->fetch_object()){
		$data[]=array(
			
			"0"=>$reg->nombre,
			"1"=>$reg->telefono,
			"2"=>$reg->correo,
			"3"=>$reg->roll,
			"4"=>($reg->estado)?'<span class="btn-sm label bg-green">Activado</span>':
			'<span class="btn-sm label bg-red">Desactivado</span>',
			"5"=>($reg->estado)?'<button class="btn btn-warning " data-toggle="modal" data-target="#ModalUsuario" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-danger " onclick="desactivar('.$reg->id.')"><i class="fa fa-trash"></i></button>'.
			' <button class="btn btn-primary " onclick="generarQRCode('.$reg->id.')"><i class="fa fa-qrcode"></i></button>':
			'<button class="btn btn-warning "  data-toggle="modal" data-target="#ModalUsuario" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>'.
			' <button class="btn btn-primary" onclick="activar('.$reg->id.')"><i class="fa fa-check"></i></button>',
		);
	}
	$results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
	echo json_encode($results);

	break;

	case 'permisos':
		//Obtenemos todos los permisos de la tabla permisos
	require_once "../modelos/Permiso.php";
	$permiso = new Permiso();
	$rspta = $permiso->listar();

		//Obtener los permisos asignados al usuario
	$id=$_GET['id'];
	$marcados = $usuario->listarmarcados($id);
		//Declaramos el array para almacenar todos los permisos marcados
	$valores=array();

		//Almacenar los permisos asignados al usuario en el array
	while ($per = $marcados->fetch_object())
	{
		array_push($valores, $per->idpermiso);
	}

		//Mostramos la lista de permisos en la vista y si están o no marcados
	while ($reg = $rspta->fetch_object())
	{
		$sw=in_array($reg->idpermiso,$valores)?'checked':'';
		echo '<li > <input class="mr-2" type="checkbox" '.$sw.'  name="permiso[]" value="'.$reg->idpermiso.'">'.$reg->nombre.'</li>';
	}
	break;

case 'verificar':
		$logina = $_POST['logina'];
		$clavea = $_POST['clavea'];
	
		// Hash SHA256 en la contraseña
		$clavehash = hash("SHA256", $clavea);
	
		$rspta = $usuario->verificar($logina, $clavehash);
		$fetch = $rspta->fetch_object();
	
		if (isset($fetch)) {
			// Variables de sesión básicas
			$_SESSION['id']      = $fetch->id;
			$_SESSION['login']   = $fetch->login;
			$_SESSION['nombre']  = $fetch->nombre;
			$_SESSION['txroll']  = $fetch->txroll;
			$_SESSION['cargo']   = $fetch->cargo;
			$_SESSION['estado']  = $fetch->estado;
	
			// Permisos del usuario
			$marcados = $usuario->listarmarcados($fetch->id);
	
			$valores = [];
			while ($per = $marcados->fetch_object()) {
				$valores[] = $per->idpermiso;
			}
	
			// Array asociativo con los nombres de permisos y sus IDs
			$permisos = [
				'Sia'           => 1,
				'Escritorio'    => 2,
				'Usuarios'      => 3,
				'Parametros'    => 4,
				'Comercial'     => 5,
				'Contabilidad'  => 6,
				'SuperUsuario'  => 7,
				'Almacen'       => 8,
				'Facturacion'   => 9,
				'Laboratorio'   => 10,
				'Proveedor'     => 11,
				'Mercadeo'      => 12,
				'Cartera'       => 13,
				'Importaciones' => 14,
				'Secretaria' 	=> 15,
				'AlmacenSolo' 	=> 16
			];
	
			// Guardar en $_SESSION tanto el booleano como el id del permiso
			foreach ($permisos as $nombre => $idPermiso) {
				if (in_array($idPermiso, $valores)) {
					$_SESSION[$nombre] = 1;
					$_SESSION["idPermiso_$nombre"] = $idPermiso;
				} else {
					$_SESSION[$nombre] = 0;
				}
			}

			// Obtener departamentos del usuario
			$departamentosResult = $usuario->obtenerDepartamentosUsuario($fetch->id);
			$departamentos = [];
			$departamentos_ids = [];
			$departamentos_nombres = [];
			
			while ($dep = $departamentosResult->fetch_object()) {
				$departamentos[] = [
					'id' => $dep->id,
					'nombre' => $dep->nombre
				];
				$departamentos_ids[] = $dep->id;
				$departamentos_nombres[] = $dep->nombre;
			}
			
			// Guardar departamentos en la sesión
			$_SESSION['departamentos'] = $departamentos;
			$_SESSION['departamentos_ids'] = $departamentos_ids;
			$_SESSION['departamentos_nombres'] = $departamentos_nombres;
		}
	
		echo json_encode($fetch);
		break;

	case 'salir':

		//Limpiamos las variables de sesión   
	session_unset();
        //Destruìmos la sesión
	session_destroy();
        //Redireccionamos al login
	header("Location: ../vistas/login.html");

	break;

	



	case 'verificarCorreo':
    $correo = $_POST['correo'];
    
    $rspta = $usuario->verificarCorreo($correo);

    if ($rspta !== null) {
        // Si hay resultados, entonces envía el correo
        $fetch = $rspta->fetch_object();

        $url = "https://fervicom.com/erp/vistas/cambioClave.php";

        date_default_timezone_set("America/Bogota");
        $mail = new PHPMailer;
        $mail->CharSet = 'UTF-8';
        $mail->isMail();
        $mail->setFrom('Fervicom');
        $mail->Subject = "Cambio de contraseña";
        $mail->addAddress($correo);
        $mail->msgHTML('<html lang="es">
            <head>
            <meta charset="UTF-8">
            <link rel="shortcut icon" href="../public/img/favicon.png">
            <title>Fervicom/Contraseña</title>
            </head>
            <body>

            <div style="width:100%; background:#f6f6f6; position:relative; font-family:sans-serif; padding-bottom:40px">

            <center>

            <img style="padding:50px; width:30%" src="../files/img/logop.png">

            </center>

            <div style="position:relative; margin:auto; width:600px; background:white; padding:20px">

            <center>

            <h3 style="font-weight:100; color:#000000">SU CORREO HA SIDO CONFIRMADO</h3>

            <hr style="border:1px solid #000000; width:80%">

            <h4 style="font-weight:100; color:#000000; padding:0 20px">"Bienvenido a Fervicom, su cambio de contraseña a sido confirmado por favor ingrese al siguiente enlace para cambiar la contraseña"</h4>

            <a href="'.$url.'" target="_blank" style="text-decoration:none">

            <div style="line-height:60px; background:#1C3352; width:60%; color:white">! Click aqui ¡</div>

            </a>

            <br>

            <hr style="border:1px solid #ccc; width:80%">

            <h5 style="font-weight:100; color:#000000">Si no envio una solicitud de cambio de contraseña,por favor  puede ignorar este correo electrónico.</h5>

            </center>

            </div>

            </div>

            </body>
            </html>');

        $envio = $mail->send();
        if ($envio) {
            echo json_encode(array("message" => "Correo registrado, por favor revisar el correo " . $correo . " o tu bandeja de entrada o spam"));
        } else {
            echo json_encode(array("error" => "Error al enviar el correo"));
        }
    } else {
        echo json_encode(array("error" => "El correo no está registrado en nuestros registros"));
    }

    break;




	
    //case para verificar el login del usuario
	case 'verificarCorreoYEditarClave':

    $correo1=isset($_POST["correo1"])? limpiarCadena($_POST["correo1"]):"";
    $clavehash=hash("SHA256",$password);
    $tabla = $usuario->verificarCorreoYEditarClave($clavehash, $correo);

    if ($tabla) {
        // Contraseña actualizada correctamente
        echo json_encode(array("success" => "Contraseña actualizada correctamente"));
    } else {
        // El correo no existe en ninguna tabla
        echo json_encode(array("error" => "Correo no registrado, verifique e intentelo de nuevo"));
    }
    break;


    //case para cambiar la contraseña despues del envio del correo
	// case 'editarClaveCorreo':
	// if ($password==$password1) {
	// 	$clavehash=hash("SHA256",$password);
	// 	$rspta=$usuario->editarClaveCorreo($clavehash,$correo1);
	// 	echo $rspta ? "Contraseña actualizada" : "Contraseñas no se pudo actualizar";
	// }
	// else{
	// 	echo "No coinciden las contraseñas";
	// }
	// break;

	case 'verificarClave':
	$clavea1=$_POST['clavea1'];
	$clavehash=hash("SHA256",$clavea1);
	$rspta=$usuario->verificarClave($clavehash);
	$fetch=$rspta->fetch_object();
	echo json_encode($fetch);
	break;


	case 'editarClaveLogin':
	if ($password2==$password3) {
		$clavehash=hash("SHA256",$password2);
		$rspta=$usuario->editarClaveLogin($clavehash,$idusuarioclave);
		echo $rspta ? "Contraseña actualizada" : "Contraseñas no se pudo actualizar";
	}
	else{
		echo "No coinciden las contraseñas";
	}
	break;




	case 'roll':
	echo '<option  >Seleccione una opción</option>';
	$rspta = $usuario->roll();

	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->id.'>' . $reg->nombre . '</option>';
	}
	break;




	case 'selectComercial':

	echo '<option value="">Seleccione una opción</option>';
	
	$rspta = $usuario->selectComercial();

	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->id.'>' . $reg->nombre . '</option>';
	}
	break;





	case 'selectUsuario':

	$rspta = $usuario->selectUsuario();

	echo '<option value="">Seleccione una opción</option>';

	while ($reg = $rspta->fetch_object())
	{
		echo '<option value='.$reg->id.'>' . $reg->nombre . '</option>';
	}
	break;




	    case 'listar_activos':
        try {
            $usuarios = $usuario->listarUsuariosActivos();
            
            echo json_encode([
                'estado' => true,
                'mensaje' => 'Usuarios obtenidos correctamente',
                'datos' => $usuarios,
                'total' => count($usuarios)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                'estado' => false,
                'mensaje' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
        }
        break;





}
?>