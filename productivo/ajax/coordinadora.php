<?php 

require_once "../modelos/Orden_p.php";

$descargar = new Orden_p();


// $idlista=isset($_POST["idlista"])? limpiarCadena($_POST["idlista"]):"";

    // require_once "../modelos/Sincrud.php";
    // $empresa=new Sincrud();
    // $rspta=$empresa->empresa();
    // $firma=$rspta['r_social'];
    // //$firma='Ticsia';
    // $idlista=$_GET['id'];
    $rspta=$descargar->coordinadora();

    // echo $rspta;
    $salida = "";
    $contador=1;
    $salida .= "<table>";
    $salida .= "<thead> <th>#</th>
    <th>CEDULA REMITENTE</th>
    <th>NOMBRE REMITENTE</th>
    <th>DIRECCION REMITENTE</th>
    <th>TELEFONO REMITENTE</th>
    <th>CIUDAD REMITENTE</th>
    <th>CEDULA DESTINATARIO</th>
    <th>NOMBRE DEL DESTINATARIO</th>
    <th>DIRECCION DESTINO</th>
    <th>TELEFONO </th>
    <th>CIUDAD DESTINO</th>
    <th>DESCRIPCION CONTENIDO</th>
    <th>VALOR ASEGURADO  POR EL TOTAL DE CAJAS O PAQUETES </th>
    <th>TOTAL PESO</th>
    <th>TOTAL VOLUMEN</th>
    <th>TOTAL UNIDADES</th>
    <th>REFERENCIA</th>
    <th>CENTRO DE COSTO</th>
    <th>CUENTA CONTABLE</th>
    <th>OBSERVACIONES</th>
    <th>NOTIFICAR CORREO (1 SI, 0 NO)</th>
    <th>CORREO A NOTIFICAR</th>
    
    </thead>";
    while ($r=$rspta->fetch_object()){
        $salida .= "<tr><td>".$contador++."</td>
        <td>900440810</td>
        <td>FERVICOM SAS</td>
        <td>CARRERA 70B #3A-18</td>
        <td>3164665835</td>
        <td>BOGOTA</td>
        <td>".$r->num_idente."</td>
        <td>".utf8_decode($r->nombree)."</td>
        <td>".utf8_decode($r->direccione)."</td>
        <td>".$r->telefonoe."</td>
        <td>".utf8_decode($r->ciudade)."</td>
        <td>Fuentes y productos LED</td>
        <td>".$r->total."</td>
        <td>".$r->peso."</td>
        <td></td>
        <td>".$r->cant."</td>
        <td> #OP - ".$r->id_op."</td>
        <td></td>
        <td></td>
        <td>".utf8_decode($r->obs_entrega)."</td>
        <td>1</td>
        <td>".$r->correoe."</td>
        </tr>";
    }
    $salida .= "</table>";
    header("Content-type: application/vnd.ms-excel; charset=utf-8");
    header("Content-type: application/vnd.ms-excel charset=iso-8859-1");
    header("Content-Disposition: attachment; filename=Coordina".time().".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    echo $salida;
    
// $r->descripcion
?>