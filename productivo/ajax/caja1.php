<?php



require_once '../modelos/Sincrud.php';
require_once '../modelos/Orden_p.php';

$sinCrud = new Sincrud();
$ordenP = new Orden_p();

$desde = isset($_POST["desde"])? limpiarCadena($_POST["desde"]):"";
$hasta = isset($_POST["hasta"])? limpiarCadena($_POST["hasta"]):"";
$fechaFactura = isset($_POST["fechaFactura"])? limpiarCadena($_POST["fechaFactura"]):"";
$formas="";
$Rtefte=19;
$Rteica=20;


switch ($_GET['op']) {

    //funcion para mostrar en pantalla
    case 'listarPantalla':
    $formasPago = $sinCrud->Fpago($fechaFactura);

    $encabezadosPago = '';

    foreach ($formasPago as $value1) {
    // Omitir las formas de pago "Rtefte" y "Rteica"
        if ($value1['idforma_pago'] == '19' || $value1['idforma_pago'] == '20') {
            continue;
        }
        $encabezadosPago .= '<th>' . $value1['nombre'] . '</th>';
        $titulo = $value1['nombre'];
    }

    $encabezadosExtras = '<th>Subtotal</th>
    <th>Iva</th>
    <th>Rtefte</th>
    <th>Rteica</th>
    <th>Total a pagar</th>
    <th>Total pagado</th>
    <th>Obs Pedido</th>';

    echo '<thead>
    <th>#</th>
    <th>Cliente</th>
    <th>Cotizacion</th>
    <th>OP</th>
    <th># factura</th>
    <th>Fecha factura</th>
    <th>Recaudo</th>

   <th>Forma de pago</th>' // Imprimir los encabezados de pago
      .$encabezadosExtras . // Imprimir los encabezados de Subtotal, IVA, Rtefte, Rteica, Total y Obs Pedido
      '</thead>';

      $pagos = $ordenP->listarCaja($fechaFactura);
      $total = 0;
      $totalSubtotal = 0;
      $totalIva = 0;
      $totalpagar = 0;
      $totalpagado = 0;
      $Vtefte = 0; // Inicializa $Vtefte fuera del bucle
      $Vteica = 0; // Inicializa $Vteica fuera del bucle
      $totalret =0;
      $totalIca =0;
      

      foreach ($pagos as $key => $value) {
        $retencion = 0;

        $totalFilaPagado=0;
        $totalFila=0;
        $txformaPago ="";
        $activos = $ordenP->mostraFormasPago($value['id_op']);

        echo '<tr>
        <td title="Orden">' . ($key + 1) . '</td>
        <td title="Cliente">' . $value['nombre'] . '</td>
        <td title="cotizacion">' . $value['consecutivo'] . '</td>
        <td title="OP">' . $value['id_op'] . '</td>
        <td title="Factura">' . $value['factura'] . '</td>
        <td title="fecha factura">' . $value['fecha_factura'] . '</td>
        <td title="fecha recaudo">' . $value['fecha_recaudo'] . '</td>';

        foreach ($formasPago as $valueForma) {
          //funcion valores de  Rtefte y Rteica
          foreach ($activos as $valuePagosRte) {
            if ($valuePagosRte['idforma_pago'] == $valueForma['idforma_pago']) {
                if ($valueForma['idforma_pago'] == '19') {
                    $Vtefte = intval($valuePagosRte['valor']);

                } elseif ($valueForma['idforma_pago'] == '20') {

                    $Vteica = intval($valuePagosRte['valor']);
                } else {
            $Vtefte = 0; // Inicializar $Vtefte en 0 en otros casos
            $Vteica = 0; // Inicializar $Vteica en 0 en otros casos
        }
    }
}


// Omitir las formas de pago "Rtefte" y "Rteica"
if ($valueForma['idforma_pago'] == '19' || $valueForma['idforma_pago'] == '20') {
    continue;
}
$Columna = 0;


foreach ($activos as $valuePagos) {
        $totalFilaPagado = $valuePagos['valor']; //total pagado
        $totalFila = $valuePagos['valores'];
        $txformaPago = $valuePagos['formaPago'];
    } 
}

echo '<th title=" '.$value['nombre'].' OP:'.$value['id_op'].' Fac:'.$value['factura'].' Rec:'.$value['fecha_recaudo'].'">' .$totalFila. '</th>';



$totalFilaPagado-=$retencion;
            // Calcule y muestre las columnas Subtotal, IVA y Total para cada fila
            $baseIva = round($value['total']/1.19); //calcula bese de iva con base en la venta
            $iva = round($baseIva * 0.19); // Suponiendo 19% de IVA, ajuste según sea necesario
            $totalAmount = $baseIva - $retencion + $iva; //total a pagar

            echo '<th title="Subtotal">$ ' . number_format($baseIva, 0, ',', '.') . '</th>
            <th title="Iva">$ ' . number_format($iva, 0, ',', '.') . '</th>
            <th title="Rtefte">$ ' . number_format($Vtefte, 0, ',', '.') . '</th>
            <th title="Rteica">$ ' . number_format($Vteica, 0, ',', '.') . '</th>
            <th title="Total a pagar">$ ' . number_format($totalAmount, 0, ',', '.') . '</th>
            <th title="Total pagado">$ ' . number_format($totalFilaPagado, 0, ',', '.') . '</th>
            <th title="Obs OP">' . $value['obs_pedido'] . '</th>
            </tr>';

            // Actualizar los valores totales.
            $total += $totalAmount;
            $totalSubtotal += $baseIva;
            $totalIva += $iva;
            $totalpagado +=$totalFilaPagado;
            $totalret +=$Vtefte;
            $totalIca +=$Vteica;
        }

        echo '<tr>
        <td>TOTAL</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>';

        // Calcular y mostrar totales para cada método de pago
        $pagos1 = $ordenP->listarCaja1($fechaFactura);
        foreach ($formasPago as $valueForma) {

            // Omitir las formas de pago "Rtefte" y "Rteica"
          if ($valueForma['idforma_pago'] == '19' || $valueForma['idforma_pago'] == '20') {
            continue;
        }
        $totalColumna = 0;
        foreach ($pagos1 as $valuePagos) {
            if ($valuePagos['id_formap'] == $valueForma['idforma_pago']) {
                $totalColumna += $valuePagos['valor'];
            }
        }

        
    }


    // Mostrar el total en las columnas Subtotal, IVA y Total en el pie de página
    echo '<td> $' . number_format($totalColumna, 0, ',', '.') . '</td>';
    echo '<td>$ ' . number_format($totalSubtotal, 0, ',', '.') . '</td>

    <td>$ ' . number_format($totalIva, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalret, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalIca, 0, ',', '.') . '</td>
    <td>$ ' . number_format($total, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalpagado, 0, ',', '.') . '</td>
    </tr>
    <tfoot>
    <th>#</th>
    <th>Cliente</th>
    <th>Cotizacion</th>
    <th>OP</th>
    <th># factura</th>
    <th>Fecha factura</th>
    <th>Recaudo</th>  
    <th>Forma de pago</th>';

//         // Agregar encabezados de columna para métodos de pago
//     $formasPago = $sinCrud->Fpago($fechaFactura);

//     foreach ($formasPago as $value1) {
//       if ($value1['idforma_pago'] == '19' || $value1['idforma_pago'] == '20') {
//         continue;
//     }
//     echo '<th>' . $value1['nombre'] . '</th>';
// }

        // Agregue encabezados de columna para Subtotal, IVA y Total
echo '<th>Subtotal</th>
<th>Iva</th>
<th>Rtefte</th>
<th>Rteica</th>
<th>Total a pagar</th>
<th>Total pagado</th>
<th>Obs Pedido</th>
</tfoot>';


break;



























//funcion para mostrar en pantalla
case 'listar':



$formasPago = $sinCrud->selectFpago($fechaFactura);

$encabezadosPago = '';

foreach ($formasPago as $value1) {
    // Omitir las formas de pago "Rtefte" y "Rteica"
    if ($value1['idforma_pago'] == '19' || $value1['idforma_pago'] == '20') {
        continue;
    }
    $encabezadosPago .= '<th>' . $value1['nombre'] . '</th>';
    $titulo = $value1['nombre'];
}

$encabezadosExtras = '<th>Subtotal</th>
<th>Iva</th>
<th>Rtefte</th>
<th>Rteica</th>
<th>Total a pagar</th>
<th>Total pagado</th>
<th>Obs Pedido</th>';




echo '<thead>
<th>#</th>
<th>Cliente</th>
<th>Cotizacion</th>
<th>OP</th>
<th># factura</th>
<th>Fecha factura</th>
<th>Recaudo</th>'

   . $encabezadosPago . // Imprimir los encabezados de pago
      $encabezadosExtras . // Imprimir los encabezados de Subtotal, IVA, Rtefte, Rteica, Total y Obs Pedido
      '</thead>';

      $pagos = $ordenP->listarCaja($fechaFactura);
      $total = 0;
      $totalSubtotal = 0;
      $totalIva = 0;
      $totalpagar = 0;
      $totalpagado = 0;
      $Vtefte = 0; // Inicializa $Vtefte fuera del bucle
      $Vteica = 0; // Inicializa $Vteica fuera del bucle
      $totalret =0;
      $totalIca =0;
      
      foreach ($pagos as $key => $value) {
        $retencion = 0;

        $totalFilaPagado=0;
        $activos = $ordenP->mostraFormasPago($value['id_op']);

        echo '<tr>
        <td title="Orden">' . ($key + 1) . '</td>
        <td title="Cliente">' . $value['nombre'] . '</td>
        <td title="cotizacion">' . $value['consecutivo'] . '</td>
        <td title="OP">' . $value['id_op'] . '</td>
        <td title="Factura">' . $value['factura'] . '</td>
        <td title="fecha factura">' . $value['fecha_factura'] . '</td>
        <td title="fecha recaudo">' . $value['fecha_recaudo'] . '</td>';

        foreach ($formasPago as $valueForma) {
          //funcion valores de  Rtefte y Rteica
          foreach ($activos as $valuePagosRte) {
            if ($valuePagosRte['idforma_pago'] == $valueForma['idforma_pago']) {
                if ($valueForma['idforma_pago'] == '19') {
                    $Vtefte = intval($valuePagosRte['valor']);

                } elseif ($valueForma['idforma_pago'] == '20') {

                    $Vteica = intval($valuePagosRte['valor']);
                } else {
            $Vtefte = 0; // Inicializar $Vtefte en 0 en otros casos
            $Vteica = 0; // Inicializar $Vteica en 0 en otros casos
        }
    }
}



// Omitir las formas de pago "Rtefte" y "Rteica"
if ($valueForma['idforma_pago'] == '19' || $valueForma['idforma_pago'] == '20') {
    continue;
}
$Columna = 0;

foreach ($activos as $valuePagos) {

    if ($valuePagos['idforma_pago'] == $valueForma['idforma_pago']) {
        $Columna += $valuePagos['valor'];
        if ($valueForma['idforma_pago'] == $Rtefte) {
            $retencion += intval($valuePagos['valor']);
        }
        if ($valueForma['idforma_pago'] == $Rteica) {
            $retencion += intval($valuePagos['valor']);

        }
                    $totalFilaPagado += $valuePagos['valor']; //total pagado
                }


            }

            echo '<th title="'.$valueForma['nombre'].' '.$value['nombre'].' OP:'.$value['id_op'].' Fac:'.$value['factura'].' Rec:'.$value['fecha_recaudo'].'"> $' . number_format($Columna, 0, ',', '.') . '</th>';
        }
        $totalFilaPagado-=$retencion;
            // Calcule y muestre las columnas Subtotal, IVA y Total para cada fila
            $baseIva = round($value['total']/1.19); //calcula bese de iva con base en la venta
            $iva = round($baseIva * 0.19); // Suponiendo 19% de IVA, ajuste según sea necesario
            $totalAmount = $baseIva - $retencion + $iva; //total a pagar

            echo '<th title="Subtotal">$ ' . number_format($baseIva, 0, ',', '.') . '</th>
            <th title="Iva">$ ' . number_format($iva, 0, ',', '.') . '</th>
            <th title="Rtefte">$ ' . number_format($Vtefte, 0, ',', '.') . '</th>
            <th title="Rteica">$ ' . number_format($Vteica, 0, ',', '.') . '</th>
            <th title="Total a pagar">$ ' . number_format($totalAmount, 0, ',', '.') . '</th>
            <th title="Total pagado">$ ' . number_format($totalFilaPagado, 0, ',', '.') . '</th>
            <th title="Obs OP">' . $value['obs_pedido'] . '</th>
            </tr>';

            // Actualizar los valores totales.
            $total += $totalAmount;
            $totalSubtotal += $baseIva;
            $totalIva += $iva;
            $totalpagado +=$totalFilaPagado;
            $totalret +=$Vtefte;
            $totalIca +=$Vteica;
        }

        echo '<tr>
        <td>TOTAL</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>

        <td></td>';

        // Calcular y mostrar totales para cada método de pago
        $pagos1 = $ordenP->listarCaja1($fechaFactura);
        foreach ($formasPago as $valueForma) {

            // Omitir las formas de pago "Rtefte" y "Rteica"
          if ($valueForma['idforma_pago'] == '19' || $valueForma['idforma_pago'] == '20') {
            continue;
        }
        $totalColumna = 0;
        foreach ($pagos1 as $valuePagos) {
            if ($valuePagos['id_formap'] == $valueForma['idforma_pago']) {
                $totalColumna += $valuePagos['valor'];
            }
        }

        echo '<td> $' . number_format($totalColumna, 0, ',', '.') . '</td>';
    }




        // Mostrar el total en las columnas Subtotal, IVA y Total en el pie de página
    echo '<td>$ ' . number_format($totalSubtotal, 0, ',', '.') . '</td>

    <td>$ ' . number_format($totalIva, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalret, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalIca, 0, ',', '.') . '</td>
    <td>$ ' . number_format($total, 0, ',', '.') . '</td>
    <td>$ ' . number_format($totalpagado, 0, ',', '.') . '</td>
    </tr>
    <tfoot>
    <th>#</th>
    <th>Cliente</th>
    <th>Cotizacion</th>
    <th>OP</th>
    <th># factura</th>
    <th>Fecha factura</th>
    <th>Recaudo</th>';

        // Agregar encabezados de columna para métodos de pago
    $formasPago = $sinCrud->selectFpago($fechaFactura);

    foreach ($formasPago as $value1) {
      if ($value1['idforma_pago'] == '19' || $value1['idforma_pago'] == '20') {
        continue;
    }
    echo '<th>' . $value1['nombre'] . '</th>';
}

        // Agregue encabezados de columna para Subtotal, IVA y Total
echo '<th>Subtotal</th>
<th>Iva</th>
<th>Rtefte</th>
<th>Rteica</th>
<th>Total a pagar</th>
<th>Total pagado</th>
<th>Obs Pedido</th>
</tfoot>';


break;


}


?>