<?php

require_once "../modelos/Catalogo.php";

if (strlen(session_id()) < 1) 
	session_start();

$catalogo=new Catalogo();
$dominio = 'http://' . $_SERVER['HTTP_HOST'];
$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";
$familia=isset($_POST["familia"])? limpiarCadena($_POST["familia"]):"";
$titulo=isset($_POST["titulo"])? limpiarCadena($_POST["titulo"]):"";
$archivo=isset($_POST["archivo"])? limpiarCadena($_POST["archivo"]):"";

// Función para generar el slug a partir del título
function generarSlug($string) {
    $string = strtolower($string); // Convierte a minúsculas
    $string = preg_replace('/[^a-z0-9-]/', '-', $string); // Reemplaza caracteres no deseados
    $string = preg_replace('/-+/', '-', $string); // Reemplaza múltiples guiones por uno solo
    return trim($string, '-'); // Elimina guiones al inicio y al final
}


switch ($_GET["op"]){

    case 'guardaryeditarCatalogo':
    // Recuperar los datos actuales del catálogo si se está actualizando
    if (!empty($id)) {
        $sql = "SELECT * FROM catalogo WHERE id='$id'";
        $rspta = ejecutarConsultaSimpleFila($sql);
        $archivo_actual = $rspta['archivo'];
        $vinculo_actual = $rspta['vinculo'];
    } else {
        $archivo_actual = "";
        $vinculo_actual = "";
    }

    if (!file_exists($_FILES['archivo']['tmp_name']) || !is_uploaded_file($_FILES['archivo']['tmp_name'])) {
        // Si no se sube un nuevo archivo, se mantiene el archivo actual
        $archivo = $archivo_actual; // Nombre del archivo actual
        $nuevo_nombre_archivo = $vinculo_actual; // Vinculo actual con el nombre original
    } else {
        // Si se sube un nuevo archivo
        $archivo_original = $_FILES["archivo"]["name"]; // Nombre original del archivo
        $titulo_slug = generarSlug($titulo); // Genera el slug a partir del título
        $extension = pathinfo($archivo_original, PATHINFO_EXTENSION); // Obtén la extensión del archivo
        $nuevo_nombre_archivo = $titulo_slug . "." . $extension; // Renombra el archivo al slug
        $ruta = "../files/catalogo/" . $nuevo_nombre_archivo; // Ruta completa donde se guardará el archivo
        move_uploaded_file($_FILES["archivo"]["tmp_name"], $ruta); // Mueve el archivo a la carpeta deseada con el nuevo nombre

        // Actualiza el archivo con el nuevo nombre
        $archivo = $nuevo_nombre_archivo;
    }

    if (empty($id)) {
        // Inserción de un nuevo registro
        $vinculo = "files/catalogo/" . $nuevo_nombre_archivo; // Vínculo completo basado en el slug
        $rspta = $catalogo->insertar($familia, $titulo, $archivo_original, $vinculo);
        echo $rspta ? "Catálogo registrado" : "Catálogo no se pudo registrar";
    } else {
        // Actualización de un registro existente
        date_default_timezone_set('America/Bogota');
        $updatedAt = date("Y-m-d H:i:s");
        $vinculo = "files/catalogo/" . $nuevo_nombre_archivo; // Vínculo completo basado en el slug
        $sql = "UPDATE catalogo SET archivo='$archivo_original', vinculo='$vinculo', updatedAt='$updatedAt' WHERE id='$id'";
        $rspta = ejecutarConsulta($sql);
        echo $rspta ? "Catálogo actualizado" : "Catálogo no se pudo actualizar";
    }
    break;

    case 'listar':
    $rspta=$catalogo->listar();
 		//Vamos a declarar un array
    $data= Array();

    while ($reg=$rspta->fetch_object()){
    	$data[]=array(

    		"0" => $reg->familia,
    		"1" => $reg->titulo,
    		"2" => $reg->archivo,
    		"3" => "<a href=" .$dominio.'/'. $reg->vinculo . " target='_blank'>" . $reg->vinculo . "</a>",
    		"4" => $reg->createdAt,
    		"5" => $reg->updatedAt,
    		"6" => '<button class="btn btn-warning" data-toggle="modal" data-target="#ModalCatalogo" onclick="mostrar('.$reg->id.')"><i class="fa fa-pencil"></i></button>  <button class="btn btn-primary" onclick="copiarVinculo(\''.$dominio.'/'.$reg->vinculo.'\')"><i class="fa fa-copy"></i></button>',
    	);
    }
    $results = array(
 			"sEcho"=>1, //Información para el datatables
 			"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 			"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 			"aaData"=>$data);
    echo json_encode($results);
    break;



    case 'mostrar':
    $rspta=$catalogo->mostrar($id);
 		//Codificar el resultado utilizando json
    echo json_encode($rspta);
    break;



    case 'verificarTituloUnico':

    $rspta = $catalogo->verificarTituloUnico($titulo);

    if ($rspta->num_rows > 0) {
        echo json_encode(["existe" => true]);
    } else {
        echo json_encode(["existe" => false]);
    }
    break;





}
?>