<?php 
require_once "../modelos/Cortes.php";
if (strlen(session_id()) < 1) 
	session_start();

$cortes=new Cortes();

$id_corte=isset($_POST["id_corte"])? limpiarCadena($_POST["id_corte"]):"";
$productoC=isset($_POST["productoC"])? limpiarCadena($_POST["productoC"]):"";
$repetir_en=isset($_POST["repetir_en"])? limpiarCadena($_POST["repetir_en"]):"";
$idop=isset($_POST["idopv"])? limpiarCadena($_POST["idopv"]):"";
$acumulado=isset($_POST["acumulado"])? limpiarCadena($_POST["acumulado"]):"";
$sobrante=isset($_POST["sobrante"])? limpiarCadena($_POST["sobrante"]):"";
$idCliente=isset($_POST["idCliente"])? limpiarCadena($_POST["idCliente"]):"";
$obs=isset($_POST["obsCorte"])? limpiarCadena($_POST["obsCorte"]):"";

$justificacion=isset($_POST["justificacion"])? limpiarCadena($_POST["justificacion"]):"";

$vista=isset($_POST["vista"])? limpiarCadena($_POST["vista"]):"";

$acumuladocm=isset($_POST["acumuladocm"])? limpiarCadena($_POST["acumuladocm"]):"";
$sobrantecm=isset($_POST["sobrantecm"])? limpiarCadena($_POST["sobrantecm"]):"";

$largop_cm=isset($_POST["largop_cm"])? limpiarCadena($_POST["largop_cm"]):"";
$largop_mm=isset($_POST["valor"])? limpiarCadena($_POST["valor"]):"";






switch ($_GET["op"]){

	case 'agregarCortes':
    //consecutivo
    $consecutivo = $cortes->Consecutivo(); // Obtener el nuevo idcorte

    if (empty($id_corte)){
    	$rspta = $cortes->insertar($productoC, $repetir_en, html_entity_decode($_POST['planoCentimetros']),html_entity_decode($_POST['planoMilimetros']), $idop, $acumulado, $sobrante, $consecutivo, $idCliente, $obs, $justificacion, $acumuladocm, $sobrantecm,$largop_cm, $largop_mm);
    	echo json_encode($rspta);
    } else {
    	$rspta = $cortes->insertarDetalle($id_corte, $productoC, $repetir_en, html_entity_decode($_POST['planoCentimetros']),html_entity_decode($_POST['planoMilimetros']), $idop, $acumulado, $sobrante, $idCliente, $obs, $justificacion,$acumuladocm, $sobrantecm,$largop_cm, $largop_mm);
    	echo $rspta ? "Corte registrado" : "El corte no se pudo agregar";
    }
    break;



    case 'mostrarCortes':
    $rspta=$cortes->mostrarCortes($iddoc);
 		//Codificar el resultado utilizando json
    echo json_encode($rspta);
    break;





    // case 'ListarCortes':


    // $rspta=$cortes->ListarCortes($id_corte,$vista);
 	// 	//Vamos a declarar un array
    // $data= Array();

    // while ($reg=$rspta->fetch_object()){
    //     $txestado="";
    //     switch ($reg->estado) {
    //         case 3:
    //         $txestado='<span class="btn btn-sm label bg-warning"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Finalizado</span>';
    //         break;

    //         case 2:
    //         $txestado='<span class="btn btn-sm label bg-danger"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Imposibilidad</span>';
    //         break;

    //         default:
    //         $txestado='<span class="btn btn-sm label bg-green"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Registrado</span>';
    //         break;
    //     }


    //     if ($vista==1) {
    //         $opciones='<button class="btn btn-danger " onclick="Eliminar('.$reg->id.')"><i class="fa fa-trash"></i></button>';
    //     }else{
    //         $opciones=$txestado;
    //     }


    //     if ($reg->op==0) {
    //       $respuesta=$reg->justificacion;
    //     }else{

    //        $respuesta=$reg->op; 
    //     }



    //     ///listar interno
    //     $txcortes =
    //     '<table class="table table-sm tablecortes" >
    //     <tbody>
    //     <tr>
    //     <td>cm</td>
    //     <td>
    //     <div class="planoContainer">
    //     '.$reg->planoCentimetros.'
    //     </div>
    //     </td>
    //     </tr>
    //     <tr>
    //     <td>mm</td>
    //     <td>
    //     <div class="planoContainer">
    //     '.$reg->planoMilimetros.'
    //     </div>
    //     </td>

    //     </tbody>
    //     </table>';

    //     $data[]=array(


    //       "0"=>substr($reg->producto, 0, 20),
    //       "1"=>$respuesta,
    //       "2"=>$reg->repetir_en,
    //       "3"=>$txcortes,
    //       "4"=>$reg->largop_cm.' cm </br></br>'.$reg->largop_mm.' mm',
    //       "5"=>$reg->acumuladocm.' cm </br></br>'.$reg->acumulado.' mm',
    //       "6"=>$reg->sobrantecm.' cm </br></br>'.$reg->sobrante.' mm',
    //       "7"=>$reg->obs,
    //       "8"=>$opciones

    //   );
    // }
    // $results = array(
 	// 		"sEcho"=>1, //Información para el datatables
 	// 		"iTotalRecords"=>count($data), //enviamos el total registros al datatable
 	// 		"iTotalDisplayRecords"=>count($data), //enviamos el total registros a visualizar
 	// 		"aaData"=>$data);
    // echo json_encode($results);

    // break;








        case 'ListarCortes':



            $rspta = $cortes->ListarCortes($id_corte,$vista);

            echo'<thead style="background-color:#232b54;color:#fff">
            <tr>
            <th>Ref</th>
            <th>#op</th>
            <th># de Perfiles</th>
            <th>Largo perfil</th>
            <th>Acumulado</th>
            <th>Sobrante</th>
            <th>Observaciones</th>
            <th>Opciones</th>
            </tr>
            </thead>';

            while ($reg = $rspta->fetch_object()){

                $txestado="";
                switch ($reg->estado) {
                    case 3:
                    $txestado='<span class="btn btn-sm label bg-warning"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Finalizado</span>';
                    break;

                    case 2:
                    $txestado='<span class="btn btn-sm label bg-danger"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Imposibilidad</span>';
                    break;

                    default:
                    $txestado='<span class="btn btn-sm label bg-green"  data-toggle="modal" data-target="#modalEstado" onclick="idcorte('.$reg->id.')">Registrado</span>';
                    break;
                }



            if ($reg->op==0) {
             $respuesta=$reg->justificacion;
            }else{
             $respuesta=$reg->op; 
            }

           if ($vista==1) {
            $opciones='<button class="btn btn-danger " onclick="Eliminar('.$reg->id.')"><i class="fa fa-trash"></i></button>';
            }else{
            $opciones=$txestado. '<button class="btn btn-danger " onclick="Eliminar('.$reg->id.',\''.$reg->idcorte.'\',\''.$reg->idcliente.'\',3)"><i class="fa fa-trash"></i></button>';
             }


                 ///listar cortes
            $txcortes =
            '<table  >
            <tbody>
            <tr>
            <td><strong>cm</strong></td>
            <td colspan="8">
            <div class="corteInputsContainer">
            '.$reg->planoCentimetros.'
            </div>
            </td>
            </tr>
            <tr>
            <td><strong>mm</strong></td>
            <td colspan="8">
            <div class="corteInputsContainer">
            '.$reg->planoMilimetros.'
            </div>
            </td>

            </tbody>
            </table>';


            echo'<tbody>
            <tr >
            <td>'.substr($reg->producto, 0, 20).'</td>
            <td>'.$respuesta.'</td>
            <td>'.$reg->repetir_en.'</td>
            <td>'.$reg->largop_cm.' cm </br></br>'.$reg->largop_mm.' mm</td>
            <td>'.$reg->acumuladocm.' cm </br></br>'.$reg->acumulado.'mm</td>
            <td>'.$reg->sobrantecm.' cm </br></br>'.$reg->sobrante.' mm</td>
            <td>'.$reg->obs.'</td>
            <td>'.$opciones.'</td>
            </tr>
            <tr class="expandable-body">
            <td colspan="7">
            '.$txcortes.'
            </td>
            </tr>

            </tbody>
            ';


            }






break;



































case 'SelectCortes':

$rspta = $cortes->SelectCortes();
echo '<option selected value="0">Seleccione una opción</option>';
while ($reg = $rspta->fetch_object())
{
 echo '<option value=' . $reg->idcorte . '>( # ' . $reg->idcorte . ') ' . $reg->fecha_reg . ' / ' . $reg->nombre . '</option>';
}
break;





case 'CambioEstado':
$iddoc=$_POST['id'];
$estado=$_POST['estado'];
$rspta=$cortes->CambioEstado($iddoc,$estado);
echo $rspta ? "Estado" : "Proceso no se puedo realizar";
break;



	        //select para traer los proveedores de empresas
case 'SelectOp':

$idcliente=$_GET["id"];

$rspta = $cortes->SelectOp($idcliente);

echo '<option value="0">Seleccione una opción</option>';

while ($reg = $rspta->fetch_object())
{
 echo '<option value='.$reg->id_op.','.$reg->id_ped.'>' . $reg->op.'</option>';
}
break;






case 'validarCodOp':
$doc=$_GET['doc'];
$rspta=$cortes->validarCodOp($doc);
//Codificar el resultado utilizando json
echo json_encode($rspta);
break;





case 'Eliminar':
$doc=$_POST['doc'];
$rspta=$cortes->Eliminar($doc);
echo $rspta ? "Corte eliminado" : "Corte no se puede eliminar";
break;



}
?>