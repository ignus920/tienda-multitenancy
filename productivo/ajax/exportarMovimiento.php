<?php 
require_once "../modelos/Movimiento.php";

$descargar =new Movimiento();


// $idlista=isset($_POST["idlista"])? limpiarCadena($_POST["idlista"]):"";

    // require_once "../modelos/Sincrud.php";
    // $empresa=new Sincrud();
    // $rspta=$empresa->empresa();
    // $firma=$rspta['r_social'];
    // //$firma='Ticsia';
    // $idlista=$_GET['id'];
    $rspta=$descargar->descargarMovimiento();
    $salida = "";
    $contador=1;
    $salida .= "<table>";
    $salida .= "<thead> <th>#</th>
    <th>Codigo</th>
    <th>Descripcion</th>
    <th>Salidas WO</th>
    <th>Saldo WO</th>
    <th>Pedir</th>
    
    </thead>";
    while ($r=$rspta->fetch_object()){
        $salida .= "<tr><td>".$contador++."</td>
        <td>".$r->codigo."</td>
        <td>".$r->descripcion."</td>
        <td>".$r->total_salida."</td>
        <td>".$r->saldo_final."</td>
        <td>".$r->cantidad."</td>
        </tr>";
    }
    $salida .= "</table>";
    header("Content-type: application/vnd.ms-excel charset=iso-8859-1");
    header("Content-Disposition: attachment; filename=Movimiento".time().".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $salida;
    
// $r->descripcion
?>