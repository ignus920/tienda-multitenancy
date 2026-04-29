<?php 
require_once "../modelos/Baner.php";


if (strlen(session_id()) < 1) 
  session_start();

$baner=new Baner();

$id=isset($_POST["id"])? limpiarCadena($_POST["id"]):"";

$banner=isset($_POST["banner"])? limpiarCadena($_POST["banner"]):"";
$textobanner=isset($_POST["textobanner"])? limpiarCadena($_POST["textobanner"]):"";
$textobanner1=isset($_POST["textobanner1"])? limpiarCadena($_POST["textobanner1"]):"";
$colorLetra1=isset($_POST["colorLetra1"])? limpiarCadena($_POST["colorLetra1"]):"";
$colorLetra2=isset($_POST["colorLetra2"])? limpiarCadena($_POST["colorLetra2"]):"";
$txboton=isset($_POST["txboton"])? limpiarCadena($_POST["txboton"]):"";
$txlink=isset($_POST["txlink"])? limpiarCadena($_POST["txlink"]):"";
$txcolor=isset($_POST["txcolor"])? limpiarCadena($_POST["txcolor"]):"";





switch ($_GET["op"]){






//guardar fotos

  case 'guardaryeditarlogo':
  
          /*=============================================
                         FOTO DEL BANNER
                         =============================================*/ 

                         if (!file_exists($_FILES['banner']['tmp_name']) || !is_uploaded_file($_FILES['banner']['tmp_name'])){

                          if ($_POST["imagenabanner"]=="") {



                          }else{

                           $banner=$_POST["imagenabanner"];
                         }


                       }else{

                        if(isset($_FILES["banner"]["tmp_name"])){

                          list($ancho, $alto) = getimagesize($_FILES["banner"]["tmp_name"]);

                          $nuevoAncho = 1920;
                          $nuevoAlto = 200;


/*=============================================
DE ACUERDO AL TIPO DE IMAGEN APLICAMOS LAS FUNCIONES POR DEFECTO DE PHP
=============================================*/

$ext = explode(".", $_FILES["banner"]["name"]);

if($_FILES["banner"]["type"] == "image/jpeg"){

/*=============================================
GUARDAMOS LA IMAGEN EN EL DIRECTORIO
=============================================*/ 
$banner = round(microtime(true)) . '.' . end($ext);

$origen = imagecreatefromjpeg($_FILES["banner"]["tmp_name"]);            

$destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto );

imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

imagejpeg($destino, "../files/banner/".$banner);

}

$ext = explode(".", $_FILES["banner"]["name"]);
if($_FILES["banner"]["type"] == "image/png"){

            /*=============================================
            GUARDAMOS LA IMAGEN EN EL DIRECTORIO
            =============================================*/

            $banner = round(microtime(true)) . '.' . end($ext);
            
            $origen = imagecreatefrompng($_FILES["banner"]["tmp_name"]);           

            $destino = imagecreatetruecolor($nuevoAncho, $nuevoAlto);

            imagecopyresized($destino, $origen, 0, 0, 0, 0, $nuevoAncho, $nuevoAlto, $ancho, $alto);

            imagepng($destino, "../files/banner/".$banner);

          }

        }

      }

      if (empty($id)) {
        $rspta= $baner->guardaryeditarlogo($banner,$textobanner,$textobanner1,$colorLetra1,$colorLetra2,$txboton,$txlink,$txcolor);
        echo $rspta ? "Baner  Agregado": "Bener no se pudo agregar";
      }else{

       $rspta= $baner->editar($id,$banner,$textobanner,$textobanner1,$colorLetra1,$colorLetra2,$txboton,$txlink,$txcolor);
       echo $rspta ? "Baner  Editado": "Bener no se pudo editar";
     }


     break;



     case 'mostrar':
     $rspta=$baner->mostrar($id);

     echo json_encode($rspta);
     break;


     case 'desactivar':
     $rspta=$baner->desactivar($id);
     echo $rspta ? "Baner desactivado" : "Banner no se pudo desactivar";
     break;

     case 'activar':
     $rspta=$baner->activar($id);
     echo $rspta ? "Baner activada" : "Baner no se pudo activar";
     break;






     case 'listar':

     $rspta=$baner->listar();
    //Vamos a declarar un array
     $data= Array();

     while ($reg=$rspta->fetch_object()){
      $data[]=array(
        "0"=>$reg->id,
        "1"=>$reg->textobanner,
        "2"=>$reg->textobanner1,
        "3"=>($reg->estado)?'<span class="btn btn-sm label bg-green" onclick="desactivar('.$reg->id.')">Activo</span>':
        '  <span class="btn btn-sm label bg-red" onclick="activar('.$reg->id.')">Desactivado</span>',

        "4"=>'<button type="button" class="btn btn-primary"  onclick="mostrar('.$reg->id.')"><i class="fa fa-eye"></i></button> '  
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