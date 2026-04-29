<?php

require_once "../modelos/Quotes.php";


if (strlen(session_id()) < 1)
    session_start();

$quotes = new Quotes();

$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";


function getColorFromUserId($userId)
{
    // Asignar un color específico para el usuario 81
    if ($userId == 81) {
        return '#34A853'; // Color verde exclusivo para el usuario 81
    }

    // Paleta de colores predefinida (colores neutros y legibles)
    $colorPalette = [
        '#4285F4', // Azul
        '#DB4437', // Rojo
        '#F4B400', // Amarillo
        '#0F9D58', // Verde oscuro
        '#AB47BC', // Púrpura
        '#FF7043', // Naranja
        '#03A9F4', // Cian
        '#9E9D24', // Verde oliva
        '#5C6BC0', // Azul índigo
        '#EC407A', // Rosa
    ];

    // Obtener un índice basado en el id_user
    $index = $userId % count($colorPalette);

    // Devolver el color correspondiente al índice
    return $colorPalette[$index];
}

switch ($_GET["op"]) {
    case 'guardaryeditar':

        if (empty($id)) {
            $rspta = $quotes->insertarCotizacion($_POST['producto_id'], $_POST['precio'], $_POST['cant']);
            echo $rspta ? "Quotes registered" : "Quotes could not be registered";
        } else {
            $rspta = $quotes->editar($id, $_POST['idcot'], $_POST['precio']);
            echo $rspta ? "Quotes updated" : "Quotes could not update";
        }

        break;

    case 'consecutivo':
        $rspta = $quotes->consecutivo();
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;

    case 'mostrarQuotes':
        $rspta = $quotes->mostrarQuotes($id);
        //Codificar el resultado utilizando json
        echo json_encode($rspta);
        break;






    case 'Aprobado':
        $rspta = $quotes->Aprobado($id);
        echo $rspta ? "Quote Approved" : "Quote cannot be approved - There are pending news/changes that must be accepted first";
        break;



    case 'marcarIngreso':
        $rspta = $usuario->marcarIngreso($id);
        // echo $rspta ? "Usuario activado" : "Usuario no se puede activar";
        break;




    case 'eliminarRegistro':
        $rspta = $quotes->eliminarRegistro($id);
        echo $rspta ? "ok" : "Product cannot be eliminate";
        break;






    //listar de cantidades de productos
    case 'listarOrders':

        $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

        if ($id > 0) {
            // Si el ID está definido, filtrar por cotizacion_id
            $condicion = " WHERE d.cotizacion_id = '$id'";
        } else {
            // Si NO hay id, excluir productos ya cotizados o liberar productos aprobados
            $condicion = " WHERE m.cantidad > 0 AND (
            d.cotizacion_id IS NULL OR 
            (c.estado = 'aprobado' AND m.cantidad > 0)
        ) GROUP BY p.id";
        }

        $rspta = $quotes->listarOrders($condicion);

        $data = array();
        while ($reg = $rspta->fetch_object()) {

            if ($id > 0) {
                $class = ($_SESSION['txroll'] == 'Administrador' && $reg->precio > $reg->exw) ? 'resaltar' : '';
                $cant = $reg->cant;
                $precio = '<input type="hidden" name="producto_id[]" value="' . $reg->idproducto . '" class="form-control">
                       <input type="hidden" name="idcot[]" value="' . $reg->idcot . '" class="form-control">
                       <input type="hidden" name="cant[]" value="' . $reg->cantidad . '" class="form-control">
                       <input type="number" name="precio[]" value="' . $reg->precio . '" class="form-control" step="any"' .
                    ($reg->estado_cot === 'aprobado' ? 'readonly' : '') . '>';
            } else {
                $class = "";
                $cant = $reg->cantidad;
                $precio = '<input type="hidden" name="producto_id[]" value="' . $reg->idproducto . '" class="form-control">
                       <input type="hidden" name="cant[]" value="' . $reg->cantidad . '" class="form-control">
                       <input type="number" name="precio[]" class="form-control" step="any"' .
                    ($reg->estado_cot === 'aprobado' ? 'readonly' : '') . '>';
            }

            $fila = array(
                "0" => '<span class="' . $class . '">' . $reg->codigo . '-' . $reg->descripcion . '</span>',
                "1" => $reg->ref_fabrica,
                "2" => $cant,
                "3" => $precio,

                "DT_RowClass" => $class,
                "5" => ($reg->estado_cot === 'aprobado')
                    ? ''
                    : '<button type="button" class="btn btn-danger" onclick="eliminarRegistro(' . $reg->idcot . ')"><i class="fa fa-trash"></i></button>'

            );

            // Mostrar EXW solo para administradores
            $fila["4"] = ($_SESSION['txroll'] == 'Administrador') ? $reg->exw : "";

            $data[] = $fila;
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );

        echo json_encode($results);
        break;





    //listar  de cotizaciones 
    case 'listarQuotes':
        $roll = $_SESSION['txroll']; // ID del usuario logueado

        $opcion = "";
        $rspta = $quotes->listarQuotes();

        $data = array();
        while ($reg = $rspta->fetch_object()) {
            // Traducir el estado al inglés
            $estado_ingles = '';
            switch ($reg->estado) {
                case 'pendiente':
                    $estado_ingles = 'Pending';
                    break;
                case 'aprobado':
                    $estado_ingles = 'Approved';
                    break;
                default:
                    $estado_ingles = 'Unknown';
                    break;
            }

            // Si el estado es "pendiente" y el usuario es Administrador, permitir aprobar
            if ($reg->estado == "pendiente") {
                if ($roll == 'Administrador') {
                    $estado = '<a href="javascript:Aprobado(' . $reg->id . ')"><span class="btn-sm label bg-green">' . $estado_ingles . '</span></a>';
                } else {
                    // Si no es el usuario Administrador, solo muestra el estado sin botón
                    $estado = '<span class="btn-sm label bg-green">' . $estado_ingles . '</span>';
                }
            } else {
                $estado = '<span class="btn-sm label bg-info">' . $estado_ingles . '</span>';
            }

            // Botón de edición, permitir siempre independientemente del usuario
            $opcion = '<button class="btn btn-warning" data-toggle="modal" data-target="#modalQuotes" onclick="mostrarQuotes(' . $reg->id . ')"><i class="fa fa-pencil"></i></button>';

            $data[] = array(
                "0" => $reg->fecha,
                "1" => $reg->consecutivo,
                "2" => $estado,
                "3" => $opcion
            );
        }

        $results = array(
            "sEcho" => 1,
            "iTotalRecords" => count($data),
            "iTotalDisplayRecords" => count($data),
            "aaData" => $data
        );
        echo json_encode($results);
        break;


























    /**
         * FUNCIONES  SP120
         * 
         */




        //funcion listar productos cen i_importacion 

    case 'productosPorEtiqueta':

    $idetiqueta = isset($_POST['idetiqueta']) ? $_POST['idetiqueta'] : (isset($_GET['idetiqueta']) ? $_GET['idetiqueta'] : '');
    $idestado = isset($_POST['idestado']) ? $_POST['idestado'] : (isset($_GET['idestado']) ? $_GET['idestado'] : '');
    $packings = isset($_POST['packings']) ? $_POST['packings'] : (isset($_GET['packings']) ? $_GET['packings'] : '');
    $id_envios = isset($_POST['id_envios']) ? $_POST['id_envios'] : (isset($_GET['id_envios']) ? $_GET['id_envios'] : '');
    $roll = $_SESSION['txroll'];

    $condiciones = [];

    // Solo aplicar el filtro que se envíe
    if ($idetiqueta != '' && $idetiqueta != '0') {
        $condiciones[] = "i.id_etiqueta = '$idetiqueta'";
    }
    
    if ($idestado != '' && $idestado != '0' && $idestado != 'undefined') {
        if ($idestado == '999') {
            // Filtro especial para novedades
            $condiciones[] = "i.novedades = 1";
        } else {
            $condiciones[] = "i.estado = '$idestado'";
        }
    }

    // Filtro por packings seleccionados
    if (!empty($packings)) {
        $packingIds = explode(',', $packings);
        $packingIds = array_map('intval', $packingIds); // Sanitizar IDs
        $packingIds = array_filter($packingIds); // Eliminar valores vacíos
        
        if (!empty($packingIds)) {
            $condiciones[] = "i.id_picking IN (" . implode(',', $packingIds) . ")";
        }
    }
    
    // Filtro por shippment (envíos)
    if ($id_envios != '' && $id_envios != '0') {
        $condiciones[] = "ii.id = '$id_envios'";
    }

    // Construir WHERE solo si hay condiciones
    if (!empty($condiciones)) {
        $condicion = " WHERE " . implode(' AND ', $condiciones);
    } else {
        $condicion = ""; // Sin filtros
    }
    
    //Debug temporal - quitar después
    // echo "Debug - Condición: " . $condicion . "<br>";
    // echo "Debug - idestado: " . $idestado . "<br>";

    $rspta = $quotes->productosPorEtiqueta($condicion);
    $data = [];

    while ($reg = $rspta->fetch_object()) {

        $btncheck = '';
        $etiqueta = '';
        $img = '';

        $estado_actual = $idestado;
        $id_picking_actual = $reg->id_picking;
        
        // Por defecto, checkbox habilitado y función básica
        $checkboxDisabled = '';
        $checkboxExtra = '';
        $checkboxOnchange = 'onchange="obtenerProductosSeleccionados(this)"';
        $accionExtra = '';
        
        // Packing - Disponible para packing
        if ($estado_actual == 5) {
            $checkboxDisabled = '';
            $checkboxExtra = 'title="Agregar al PACK"';
            // Aquí el checkbox ejecuta agregarProductoPick al seleccionar
            $checkboxOnchange = 'onchange="agregarProductoPick(' . $reg->id . ')"';
        }

        // Crear el contenido del checkbox y descripción de forma segura
        $checkboxContent = '
        <div class="producto-item-container">
            <div class="checkbox-container">
                <input type="checkbox" class="mr-2 check-producto" name="check[]" value="' . htmlspecialchars($reg->id) . '" ' . $checkboxOnchange . ' ' . $checkboxDisabled . ' ' . $checkboxExtra . '>
                <span class="producto-descripcion">' . htmlspecialchars($reg->codigo . '-' . $reg->txdescripcion) . '</span>
                ' . $accionExtra . '
            </div>
        </div>';

        $etiqueta = $reg->etiquetas;

        if ($roll == 'Proveedor') {
            // Bloquear edición de precio cuando está en estado=5 (Producción)
            $readonly = ($reg->estado == 5) ? 'readonly disabled' : '';

            $inputprecio = '<div class="input-group input-group-sm" bis_skin_checked="1">
                <input type="number" onchange="updatePrecio($(this).val(),' . $reg->id . ',' . $reg->estado . ', $(this).attr(\'data-valor-anterior\'), $(this))" 
                    name="precio" id="precio" value="' . $reg->precio . '" 
                    class="form-control input-precio" placeholder="Cantidad" ' . $readonly . '>
                <span class="input-group-append"></span>
            </div><br>';
        } else {
            $inputprecio = '$' . number_format($reg->precio, 2);
        }

        $Opciones = '
        <div class="d-flex flex-column">
    <span class="numero-packing" data-pack="' . htmlspecialchars($reg->id_picking) . '">
        <strong>Pack:</strong> ' . htmlspecialchars($reg->numero_packing) . '
    </span>
    <span>
        <strong>O.N:</strong> ' . htmlspecialchars($reg->del) . '
    </span>
    <span>
        <strong>ETD:</strong> ' . htmlspecialchars($reg->etd) . '
    </span>
    <span>
        <strong>Via:</strong>   # '.htmlspecialchars($reg->consecutivo) .' - '. htmlspecialchars($reg->via) . '
    </span>
    <span>
        <strong>Rec:</strong> ' . htmlspecialchars($reg->fecha_recibido) . '
    </span>
</div>
';

        // Botón de aceptar novedad - lógica de visibilidad
        $botonAceptarNovedad = '';
        if ($reg->novedades == 1) {
            // Mostrar botón solo si el usuario actual es quien inició la conversación
            if (!empty($reg->iniciador_idusuario) && $reg->iniciador_idusuario == $_SESSION['id']) {
                $botonAceptarNovedad = ' <button type="button" class="btn btn-sm btn-warning" onclick="aceptarNovedad(' . $reg->id . ')" title="Finalizar novedad"><i class="fas fa-check"></i> Finish </button> ';
            }
        }

        $comentarioInput = '<div class="input-group mb-3" bis_skin_checked="1">
        <input type="text" class="form-control form-control-sm comentario-input" data-id="' . $reg->id . '" onchange="guardarComentario(this)" />
        <div class="input-group-prepend" bis_skin_checked="1">
        <button type="button" class="btn btn-sm btn-success" data-toggle="modal" data-target="#modalHistorialComent" onclick="historiaComent(' . $reg->id . ')"><i class="fas fa-eye"></i></button>
        </div>
        </div>';

        // Crear el contenido de la imagen de forma segura
        $imagenContent = '';
        if ($reg->tximagen) {
            $imagenContent = '
            <div class="imagen-container">
                <a title="Photo" class="mr-1" data-toggle="modal" href="#modalFoto" aria-expanded="false" 
                onclick="imagen(' . htmlspecialchars($reg->idp) . ')">
                <i class="fas fa-images txcolori"></i>
                </a>
            </div>';
        }

        $estadosPermitidos = [1, 2, 4, 5];
        $esNoProveedor = isset($_SESSION['cargo']) && $_SESSION['cargo'] != 6;

        // Botón de eliminar si cantidad es 0
        $botonEliminar = '';
        if ($reg->cant_sol == 0) {
            $botonEliminar = '<button type="button" class="btn btn-sm btn-danger ml-2" onclick="eliminarProducto(' . $reg->id . ')" title="Eliminar producto"><i class="fas fa-trash"></i></button>';
        }

        $inputCantidad = $reg->cant_sol;
        if ($esNoProveedor && in_array($reg->estado, $estadosPermitidos)) {
            $inputCantidad = '<div class="d-flex align-items-center"><input type="number" class="form-control form-control-sm input-cantidad" value="' . $reg->cant_sol . '" data-id="' . $reg->id . '" onchange="actualizarCantidadConComentario(this)" data-valor-anterior="' . $reg->cant_sol . '">' . $botonEliminar . '</div>';
        } else {
            // Solo mostrar el valor, no editable, pero con botón si es 0
            $inputCantidad = '<div class="d-flex align-items-center"><span>' . $reg->cant_sol . '</span>' . $botonEliminar . '</div>';
        }

        // === COLUMNA 8: Cantidad enviada (editable por proveedor en estado 5) ===
        $inputCantidadEnviada = '<span>' . $reg->cant_enviada . '</span>';

        if ($roll === 'Proveedor' && ($estado_actual == 5 || $estado_actual == 6)) {
            $valorMostrar = ($reg->cant_enviada > 0) ? $reg->cant_enviada : $reg->cant_sol;
            $inputCantidadEnviada = '
            <input type="number"
                class="form-control form-control-sm input-cant-envio"
                value="' . $valorMostrar . '"
                data-id="' . $reg->id . '"
                data-cant-sol="' . $reg->cant_sol . '"
                data-anterior="' . $valorMostrar . '"
                onchange="confirmarCambioCantidadEnviada(this)">
            ';
        }

        // Construir la columna 0 de forma estructurada y segura
        $columna0 = '
        <div class="producto-completo">
            <div class="d-flex align-items-start">
                <input type="checkbox" class="mr-2 mt-1 check-producto" name="check[]" value="' . htmlspecialchars($reg->id) . '" ' . $checkboxOnchange . ' ' . $checkboxDisabled . ' ' . $checkboxExtra . '>
                <div class="d-flex flex-column">
                    <span class="producto-descripcion" style="font-weight: 500;">' . htmlspecialchars($reg->codigo . '-' . $reg->txdescripcion) . '</span>
                    <div class="d-flex align-items-center mt-1">
                        <button type="button" class="btn btn-xs btn-outline-info mr-2" onclick="verControlEtiquetas(\'' . htmlspecialchars($reg->idproducto) . '\', \'' . htmlspecialchars($reg->txdescripcion) . '\')" title="Analizar producto">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                        ' . ($reg->tximagen ? '
                        <a title="Photo" class="mr-2" data-toggle="modal" href="#modalFoto" aria-expanded="false" onclick="imagen(\'' . htmlspecialchars($reg->idp) . '\')">
                            <i class="fas fa-images txcolori" style="font-size: 1.1rem;"></i>
                        </a>' : '') . '
                        ' . $accionExtra . '
                    </div>
                </div>
            </div>
        </div>';



        //BOTON APROVADO POR PRECIO
        $botonAprobado='';

        if ($reg->estado == 2 && $reg->precio > 0 && $roll == 'Administrador') {
            
              if ($reg->novedades==1){
            $botonAprobado = '';
        }else{
            $botonAprobado = '<button type="button" class="btn btn-sm btn-success " onclick="aprobadoprecio(' . $reg->id . ',' . $reg->idproducto . ',' . $reg->precio . ',' . $reg->cant_sol . ')" title="Approve price"><i class="fas fa-check"></i> Approve price</button>';
        }

            
        }else if ($reg->estado == 4 && $reg->precio > 0 && $roll == 'Proveedor') {

            if ($reg->novedades==1){
            $botonAprobado = '';
        }else{
            $botonAprobado = '<button type="button" class="btn btn-sm btn-success " onclick="asignarProduccion(' . $reg->id . ',' . $reg->idproducto . ',' . $reg->precio . ',' . $reg->cant_sol . ')" title="Approve Production"><i class="fas fa-check"></i> Production </button>';
        }
            
        }else{
            $botonAprobado = 'N/A';
        }

        $data[] = [
            "0" => $columna0,
            "1" => $reg->ref_fabrica,
            "2" => htmlspecialchars($reg->exw),
            "3" => $inputCantidad,
            "4" => htmlspecialchars($etiqueta),
            "5" => $inputprecio,
            "6" => $comentarioInput . '<p>' . htmlspecialchars($reg->comentario) . $botonAceptarNovedad.'</p>',
            "7" => $botonAprobado,
            "8" => htmlspecialchars($reg->txestado),
            "9" => $inputCantidadEnviada,
            "10" => $Opciones,
            "precio_num" => $reg->precio
        ];
    }

    $results = [
        "sEcho" => 1,
        "iTotalRecords" => count($data),
        "iTotalDisplayRecords" => count($data),
        "aaData" => $data
    ];
    echo json_encode($results);
    break;








    case 'actualizarCantidadConComentario':
        $id = $_POST['id'];
        $cantidad = $_POST['cantidad'];
        $comentario = $_POST['comentario'];
        $result = $quotes->actualizarCantidadConComentario($id, $cantidad, $comentario);
        echo $result ? "Quantity and comment updated successfully" : "Error updating";
        break;

    //funcion para obtener estado y cantidad de un producto específico
    case 'obtenerEstadoProducto':
        $id_producto = isset($_POST['id_producto']) ? $_POST['id_producto'] : '';
        $rspta = $quotes->obtenerEstadoProducto($id_producto);
        echo json_encode($rspta);
        break;

    //funcion para obtener cantidad de un pack específico
    case 'obtener_cantidad_pack':
        $id_pack = isset($_POST['id_pack']) ? $_POST['id_pack'] : '';
        $rspta = $quotes->obtenerCantidadPack($id_pack);
        echo json_encode($rspta);
        break;

    //funcion para cuadro de estados
    case 'estadiFiltros':
        $id_etiqueta = isset($_POST['id_etiqueta']) ? $_POST['id_etiqueta'] : null;
        $rspta = $quotes->estadiFiltros($id_etiqueta);
        while ($reg = $rspta->fetch_object()) {
            $txroll = $_SESSION['txroll'];
            if ($txroll == 'Proveedor') {
                $nombre = $reg->name;
            } else {
                $nombre = $reg->nombre;
            }
            
            // Determinar el formato de la cantidad
            if ($id_etiqueta && $id_etiqueta != "0" && $id_etiqueta != "" && isset($reg->cant_total)) {
                $cantidadMostrar =  $reg->cant . '/' . $reg->cant_total;
            } else {
                $cantidadMostrar = $reg->cant;
            }
            
            echo '<div class="color col-12 col-sm-6 col-md-3 filtro-cuadro" bis_skin_checked="1" style="cursor: pointer;" data-filtro-id="' . $reg->id . '">
                            <div class="info-box" bis_skin_checked="1">
                                <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

                                <div class="info-box-content" bis_skin_checked="1">
                                    <span class="info-box-text">' . $nombre . '</span>
                                    <span class="info-box-number">
                                        ' . $cantidadMostrar . '
                                        <small></small>
                                    </span>
                                </div>
                            </div>
                        </div>';
        }

        break;

    // Obtener packings utilizados por el proveedor en estado 6
    case 'obtener_packings_utilizados':
        $login = $_SESSION['login'];
        $sql = "SELECT DISTINCT p.id, p.numero_packing, COUNT(i.id) as cantidad_productos
                FROM i_picking p 
                inner JOIN i_importacion i ON p.id = i.id_picking 
                WHERE i.estado = 6
                GROUP BY p.id, p.numero_packing
                ORDER BY p.numero_packing";
        
        $rspta = ejecutarConsulta($sql);
        $packings = [];
        
        while ($reg = $rspta->fetch_object()) {
            $packings[] = [
                'id' => $reg->id,
                'numero' => $reg->numero_packing,
                'cantidad' => $reg->cantidad_productos
            ];
        }
        
        echo json_encode($packings);
        break;

    // Asignar datos de envío a múltiples packings
    case 'asignar_datos_envio_packings':
        $etd = $_POST['etd'];
        $del = $_POST['del'];  
        $via = $_POST['via'];
        $transportadora = isset($_POST['transportadora']) ? $_POST['transportadora'] : '';
        $observaciones = isset($_POST['observaciones']) ? $_POST['observaciones'] : '';
        $packings = json_decode($_POST['packings'], true);
        
        if (empty($packings) || !is_array($packings)) {
            echo "Error: No se especificaron packings válidos";
            break;
        }
        
        // Validar campos obligatorios
        if (empty($etd) || empty($del) || empty($via)) {
            echo "Error: Faltan campos obligatorios (ETD, DEL, VIA)";
            break;
        }
        
        // Generar consecutivo según la vía
        $consecutivo = 1;
        if (strtoupper(trim($via)) == 'AEREA' || strtoupper(trim($via)) == 'AEREO') {
            // Obtener último consecutivo para vía aérea
            $sql_consecutivo = "SELECT MAX(consecutivo) as ultimo_consecutivo FROM i_envios WHERE UPPER(via) IN ('AEREA', 'AEREO')";
            $result_consecutivo = ejecutarConsulta($sql_consecutivo);
            if ($reg_consecutivo = $result_consecutivo->fetch_object()) {
                $consecutivo = ($reg_consecutivo->ultimo_consecutivo) ? $reg_consecutivo->ultimo_consecutivo + 1 : 1;
            }
        } elseif (strtoupper(trim($via)) == 'MARITIMA' || strtoupper(trim($via)) == 'MARITIMO') {
            // Obtener último consecutivo para vía marítima
            $sql_consecutivo = "SELECT MAX(consecutivo) as ultimo_consecutivo FROM i_envios WHERE UPPER(via) IN ('MARITIMA', 'MARITIMO')";
            $result_consecutivo = ejecutarConsulta($sql_consecutivo);
            if ($reg_consecutivo = $result_consecutivo->fetch_object()) {
                $consecutivo = ($reg_consecutivo->ultimo_consecutivo) ? $reg_consecutivo->ultimo_consecutivo + 1 : 1;
            }
        } else {
            // Para otras vías, obtener consecutivo general
            $sql_consecutivo = "SELECT MAX(consecutivo) as ultimo_consecutivo FROM i_envios";
            $result_consecutivo = ejecutarConsulta($sql_consecutivo);
            if ($reg_consecutivo = $result_consecutivo->fetch_object()) {
                $consecutivo = ($reg_consecutivo->ultimo_consecutivo) ? $reg_consecutivo->ultimo_consecutivo + 1 : 1;
            }
        }

        // Crear nuevo envío con consecutivo
        $sql_crear_envio = "INSERT INTO i_envios (consecutivo, etd, del, via, transportadora, observaciones) 
                           VALUES ('$consecutivo', '$etd', '$del', '$via', '$transportadora', '$observaciones')";
        
        $resultado_envio = ejecutarConsulta($sql_crear_envio);
        
        if ($resultado_envio) {
            $nuevo_idenvio = mysqli_insert_id($conexion);
            
            // Iniciar transacción para actualizar packings
            $sqls_transaccion = [];
            
            // Procesar cada packing
            foreach ($packings as $picking_id) {
                // Actualizar el picking con el idenvio
                $sqls_transaccion[] = "UPDATE i_picking SET 
                                      idenvio = '$nuevo_idenvio'
                                      WHERE id = '$picking_id'";
                
                // Actualizar productos del picking a estado 7 (tránsito)
                $sqls_transaccion[] = "UPDATE i_importacion SET estado = 7 WHERE id_picking = '$picking_id' AND estado = 6";
            }
            
            // Ejecutar todas las consultas como transacción
            $resultado = ejecutarTransaccion($sqls_transaccion);
            
            if ($resultado) {
                $packings_nombres = [];
                foreach ($packings as $picking_id) {
                    $obtener_nombre = "SELECT numero_packing FROM i_picking WHERE id = '$picking_id'";
                    $rspta_nombre = ejecutarConsulta($obtener_nombre);
                    if ($reg_nombre = $rspta_nombre->fetch_object()) {
                        $packings_nombres[] = $reg_nombre->numero_packing;
                    }
                }
                
                echo "Shipping data assigned correctly. Packings processed: " . implode(', ', $packings_nombres) . ". Consecutive: " . $consecutivo;
            } else {
                echo "Error al actualizar packings y productos";
            }
        } else {
            echo "Error al crear el envío";
        }
        break;

    //FUNCION CAMBIO DE ESTADO (F122)
    case 'cambiarEstado':
        $productos = $_POST['productos'];
        $estado = $_POST['estado'];
        // Asumimos que el modelo maneja arrays
        $respuesta = $quotes->cambiarEstadoProductos($productos, $estado);
        echo $respuesta ? "Status changed successfully" : "Error changing status - There are pending news/changes that must be accepted first";
        break;



    case 'aprobadoprecio':
        $productos = $_POST['productos'];

        // Asumimos que el modelo maneja arrays
        $respuesta = $quotes->aprobadoprecio($productos);
        echo $respuesta ? "Status changed successfully" : "Error changing status - There are pending news/changes that must be accepted first";
        break;





    //FUNCION  ASIGNAR PRECIOS  F121
    case 'asignarPrecio':
        $productos = $_POST['productos']; // ← es un array
        $precio = $_POST['precio'];
        $comentario = $_POST['comentario'];

        foreach ($productos as $idproducto) {
            $quotes->asignarPrecio($idproducto, $precio, $comentario);
        }

        echo json_encode(["status" => "ok", "message" => "Prices updated successfully."]);
        break;






    //FUNCION RETIRAR PRECIO
    case 'retirarPrecio':
        $productos = $_POST['productos'];
        foreach ($productos as $idproducto) {
            $quotes->retirarPrecio($idproducto);
        }
        echo json_encode(["status" => "ok", "message" => "Price removed successfully."]);
        break;






  





    case 'listarEnvios':
        $productos = isset($_POST['productos']) ? json_decode($_POST['productos'], true) : [];
        $condicion = " WHERE 1=1 ";

        if (!empty($productos)) {
            $ids = implode(",", array_map('intval', $productos));
            $condicion .= " AND i.id IN ($ids)";
        }

        $rspta = $quotes->listarEnvios($condicion);
        //Vamos a declarar un array
        $data = array();
        $img = '';

        while ($reg = $rspta->fetch_object()) {

            if ($reg->tximagen == NULL) {
                $img = '<img class="zoom" src="../files/img/default.png" height="50px" width="50px">';
            } else {
                $img = '<a href="' . $reg->tximagen . '" target="_blank"  title="click to see image">
        <img class="zoom" src="' . $reg->tximagen . '" height="50px" width="50px">
        </a>';
            }

            $cantidad = '<input type="number" class="form-control input-cantidad" 
          data-id="' . $reg->idp . '" placeholder="Cantidad" value="' . $reg->cant_sol . '">';

            $comentario = '<textarea type="text" class="form-control input-comentario" 
           data-id="' . $reg->idp . '" placeholder="Comentario (opcional)"></textarea>';

            // Determinar el estado del producto para los botones de acción
            $estado_actual = $reg->estado;
            $id_picking_actual = $reg->id_picking;

            // Generar botón de acción según el estado
            $boton_accion = '';

            if ($estado_actual == 6) { // Ya está en packing
                if ($id_picking_actual) {
                    // Está en algún PICK - agregar botón para asignar datos de envío
                    $boton_accion = '
                    <div class="text-center">
                        <span class="badge badge-warning mb-1">PICK #' . $id_picking_actual . '</span><br>
                        <button class="btn btn-sm btn-info" 
                            onclick="procesarAsignacionDatosEnvioConId(' . $reg->idp . ')" 
                            title="Asignar datos de envío">
                            <i class="fas fa-shipping-fast"></i> Envío
                        </button>
                    </div>';
                }
            } else if ($estado_actual == 5) { // Disponible para packing
                $boton_accion = '<button class="btn btn-sm btn-success btn-agregar-pick" 
                data-id="' . $reg->idp . '" 
                title="Agregar al PICK" 
                style="display:none;">
                <i class="fas fa-plus"></i>
            </button>';
            } else if ($estado_actual == 7) { // En tránsito
                $boton_accion = '<span class="badge badge-info">En Tránsito</span>';
            } else if ($estado_actual == 8) { // Recibido
                $boton_accion = '<span class="badge badge-success">Recibido</span>';
            }

            $data[] = array(
                "0" => $reg->idproducto . '-' . $reg->descripcion,
                "1" => $img,
                "2" => $reg->ref_fabrica,
                "3" => '$' . number_format($reg->precio, 2),
                "4" => $reg->cant_sol,
                "5" => $cantidad,
                "6" => $comentario,
                "7" => $boton_accion, // Columna de acciones
                "estado" => $estado_actual,
                "id_picking" => $id_picking_actual
            );
        }

        $results = array(
            "sEcho" => 1, //Información para el datatables
            "iTotalRecords" => count($data), //enviamos el total registros al datatable
            "iTotalDisplayRecords" => count($data), //enviamos el total registros a visualizar
            "aaData" => $data
        );
        echo json_encode($results);
        break;


    // case 'EditarCant':
    // 		$codigo = $_POST['id'];
    // 		$cantidad = $_POST['cantidad'];

    // 			$rpta = $quotes->EditarCant($cantidad, $codigo); // actualizar

    // 		echo json_encode([
    // 			"status" => $rpta ? "ok" : "error"
    // 		]);
    // 		break;


    case 'asignarDatosEnvioCompleto':
        $productos = json_decode($_POST['productos'], true);
        $etd = $_POST['etd'];
        $del = $_POST['del'];
        $via = $_POST['via'];
        $comentarioP = $_POST['comentarioP'];

        // Llama al modelo y pasa todo lo necesario
        $respuesta = $quotes->asignarDatosEnvioCompleto($productos, $etd, $del, $via, $comentarioP);

        // Intentar crear nueva etiqueta del mismo mes pero con el año siguiente
        if ($respuesta && count($productos) > 0) {
            require_once "../modelos/I_etiquetas.php";
            $etiqueta = new Etiquetas();

            // Tomamos la etiqueta del primer producto (todos usan la misma)
            $idPrimero = intval($productos[0]['id']);

            // Consultar nombre de la etiqueta actual
            $consulta = ejecutarConsultaSimpleFila("
            SELECT e.nombre 
            FROM i_importacion i
            INNER JOIN i_etiqueta e ON i.id_etiqueta = e.id
            WHERE i.id = '$idPrimero'
        ");

            if ($consulta && isset($consulta['nombre'])) {
                $nombreActual = strtoupper($consulta['nombre']); // ej: JUN25

                // Extraer mes y año
                $mes = substr($nombreActual, 0, 3); // JUN
                $anio = substr($nombreActual, -2);  // 25

                // Calcular la nueva etiqueta con el mismo mes y año siguiente
                $nuevoAnio = str_pad((intval($anio) + 1), 2, "0", STR_PAD_LEFT); // 26
                $nuevoNombre = $mes . $nuevoAnio;

                // Convertir mes a número (enero = 01, etc.)
                $mesNum = date("m", strtotime("01 $mes"));
                $nuevaFecha = "20" . $nuevoAnio . "-" . $mesNum . "-01";

                // Verificar si la nueva etiqueta ya existe
                $existe = ejecutarConsultaSimpleFila("
                SELECT COUNT(*) AS total 
                FROM i_etiqueta 
                WHERE nombre = '$nuevoNombre'
            ");

                if ($existe['total'] == 0) {
                    // Insertar nueva etiqueta automáticamente
                    $etiqueta->insertar($nuevoNombre, 'no', $nuevaFecha, '');
                    // Opcional: guardar log o mostrar notificación en consola
                    error_log("Etiqueta creada automáticamente: $nuevoNombre");
                }
            }
        }

        echo json_encode([
            "status" => $respuesta ? "ok" : "error"
        ]);
        break;



















    // FUNCIÓN MARCAR COMO RECIBIDO F122
    case 'marcarRecibido':
        $productos = $_POST['productos'];
        $respuesta = $quotes->marcarComoRecibido($productos);
        echo $respuesta ? "Products marked as received" : "Error updating status";
        break;




    // FUNCIÓN AGREGAR COMENTARIO DE FERVICOM F125
    case 'agregarComentarioFervicom':
        $productos = $_POST['productos'];
        $comentario = $_POST['comentario'];
        $respuesta = $quotes->guardarComentarioHistorial($productos, $comentario);
        echo $respuesta ? "Comment added successfully" : "Error saving comment";
        break;




    // FUNCIÓN CAMBIAR ESTADO MANUALMENTE F126
    case 'corregirEstado':
        $productos = $_POST['productos'];
        $estado = $_POST['estado'];
        $respuesta = $quotes->cambiarEstadoProductos($productos, $estado);
        echo $respuesta ? "Status corrected successfully" : "Error correcting status";
        break;


    // FUNCIÓN MARCAR COMO RETRASADO SI ETA VENCIDA F128
    case 'marcarRetrasados':
        $ok = $quotes->marcarRetrasados();
        echo $ok ? "Products updated as 'Delayed'" : "Could not update status";
        break;





    // FUNCIÓN F123 – CAMBIO A PRODUCCIÓN
    case 'asignarProduccion':
        $productos = $_POST['productos'];
        // $etd = $_POST['etd'];

        $resp = $quotes->asignarProduccion($productos);
        echo $resp ? "Updated to production successfully" : "Error changing status - There are pending news/changes that must be accepted first";
        break;



    // FUNCIÓN ANALIZAR ESTADOS SELECCIONADOS - F122
    case 'analizarEstadosSeleccionados':
        $productos = $_POST['productos'];
        $rspta = $quotes->estadosDeProductos($productos);

        $estados = [];
        while ($reg = $rspta->fetch_object()) {
            $estados[] = $reg->estado;
        }

        echo json_encode($estados);
        break;




    case 'updatePrecio':
        $id = $_POST['id'];
        $precio = $_POST['precio'];
        $estado = $_POST['estado'] ?? null;
        $comentario = $_POST['comentario'] ?? null;

        if ($estado == 1 ) {
            $rpta = $quotes->updatePrecio($id, $precio);
            // Guardar comentario y actualizar estado
       
        } else {

             $rpta = $quotes->updatePrecioConComentario($id, $precio, $comentario);
            
        }

        echo json_encode([
            "status" => $rpta ? "ok" : "error",
        ]);
        break;




    case 'guardarComentarioDirecto':
        $id_importacion = $_POST['id_importacion'];
        $comentario = $_POST['comentario'];
        $res = $quotes->guardarComentarioDirecto($id_importacion, $comentario);
        echo json_encode(["status" => $res ? "ok" : "error"]);
        break;

    case 'aceptarNovedad':
        $id = $_POST['id'];
        $comentario = $_POST['comentario'];
        $res = $quotes->aceptarNovedad($id, $comentario);
        echo $res ? "Novedad aceptada correctamente" : "Error al aceptar la novedad";
        break;

    case 'eliminarProducto':
        $id = $_POST['id'];
        $res = $quotes->eliminarProducto($id);
        echo $res ? "Producto eliminado correctamente" : "Error al eliminar el producto";
        break;










    case 'historiaComent':
        // Recibimos el id de entrada
        $rsptaComentarios = $quotes->historiaComent($id);
        $rsptaEstados = $quotes->historiaEstados($id);

        // Crear arrays para combinar ambos historiales
        $historial_completo = array();

        // Agregar comentarios al historial
        if ($rsptaComentarios->num_rows > 0) {
            while ($reg = $rsptaComentarios->fetch_object()) {
                $historial_completo[] = array(
                    'tipo' => 'comentario',
                    'fecha' => $reg->fecha,
                    'usuario' => $reg->nombre,
                    'usuario_id' => $reg->id,
                    'contenido' => $reg->comentario
                );
            }
        }

        // Agregar cambios de estado al historial
        if ($rsptaEstados->num_rows > 0) {
            while ($reg = $rsptaEstados->fetch_object()) {
                $historial_completo[] = array(
                    'tipo' => 'estado',
                    'fecha' => $reg->fecha_cambio,
                    'usuario' => $reg->usuario,
                    'estado_anterior' => $reg->estado_anterior_nombre ? $reg->estado_anterior_nombre : 'Initial',
                    'estado_nuevo' => $reg->estado_nuevo_nombre,
                    'contenido' => ''
                );
            }
        }

        // Ordenar por fecha descendente
        usort($historial_completo, function($a, $b) {
            return strtotime($b['fecha']) - strtotime($a['fecha']);
        });

        echo '<div class="historial-container">'; // Contenedor principal
        
        if (count($historial_completo) > 0) {
            // Separar comentarios y estados
            $comentarios = array_filter($historial_completo, function($item) { return $item['tipo'] == 'comentario'; });
            $estados = array_filter($historial_completo, function($item) { return $item['tipo'] == 'estado'; });
            
            // Mostrar comentarios primero
            if (count($comentarios) > 0) {
                echo '<div class="mb-4">';
                echo '<h6 class="text-primary mb-3"><i class="fas fa-comments mr-2"></i>Comments</h6>';
                echo '<div class="historial-horizontal">'; // Contenedor flex
                
                foreach ($comentarios as $item) {
                    $solicitud_decoded = html_entity_decode($item['contenido'], ENT_QUOTES, 'UTF-8');
                    $userColor = getColorFromUserId($item['usuario_id']);

                    echo '<div class="historial-item">';
                    echo '  <div style="display: flex; justify-content: space-between; align-items: center;">';
                    echo '    <div>';
                    echo '      <small class="text-muted">' . $item['fecha'] . '</small>';
                    echo '    </div>';
                    echo '    <div>';
                    echo '      <span class="badge" style="background:' . $userColor . '; color: #fff;">' . $item['usuario'] . '</span>';
                    echo '    </div>';
                    echo '  </div>';
                    
                    // Detectar si el comentario contiene información de cambio de cantidad o precio
                    $comentarioFormateado = $solicitud_decoded;
                    
                    // Buscar patrones de cambio de cantidad solicitada
                    if (preg_match('/Old Qty: (\d+(\.\d+)?) → New Qty: (\d+(\.\d+)?)/i', $solicitud_decoded, $matches)) {
                        $oldQty = $matches[1];
                        $newQty = $matches[3];
                        $comentarioSinOldQty = preg_replace('/Old Qty: \d+(\.\d+)? → New Qty: \d+(\.\d+)?[\s\n\r]*/i', '', $solicitud_decoded);
                        $comentarioFormateado = '<div class="mb-2"><strong class="text-warning">Old Qty: ' . $oldQty . ' → New Qty: ' . $newQty . '</strong></div><div>' . nl2br(trim($comentarioSinOldQty)) . '</div>';
                    }
                    // Buscar patrones de cambio de cantidad enviada
                    else if (preg_match('/Old Qty \(Sent\): (\d+(\.\d+)?) → New Qty \(Sent\): (\d+(\.\d+)?)/i', $solicitud_decoded, $matches)) {
                        $oldQtySent = $matches[1];
                        $newQtySent = $matches[3];
                        $comentarioSinOldQty = preg_replace('/Old Qty \(Sent\): \d+(\.\d+)? → New Qty \(Sent\): \d+(\.\d+)?[\s\n\r]*/i', '', $solicitud_decoded);
                        $comentarioFormateado = '<div class="mb-2"><strong class="text-warning">Old Qty (Sent): ' . $oldQtySent . ' → New Qty (Sent): ' . $newQtySent . '</strong></div><div>' . nl2br(trim($comentarioSinOldQty)) . '</div>';
                    }
                    // Buscar patrones de cambio de precio
                    else if (preg_match('/Old Price: \$?(\d+(\.\d+)?) → New Price: \$?(\d+(\.\d+)?)/i', $solicitud_decoded, $matches)) {
                        $oldPrice = $matches[1];
                        $newPrice = $matches[3];
                        $comentarioSinOldPrice = preg_replace('/Old Price: \$?\d+(\.\d+)? → New Price: \$?\d+(\.\d+)?[\s\n\r]*/i', '', $solicitud_decoded);
                        $comentarioFormateado = '<div class="mb-2"><strong class="text-info">Old Price: $' . number_format(floatval($oldPrice), 2) . ' → New Price: $' . number_format(floatval($newPrice), 2) . '</strong></div><div>' . nl2br(trim($comentarioSinOldPrice)) . '</div>';
                    }
                    else {
                        $comentarioFormateado = nl2br($solicitud_decoded);
                    }
                    
                    echo '  <div class="mt-2">' . $comentarioFormateado . '</div>';
                    echo '</div>';
                }
                echo '</div>'; // Cierre contenedor flex
                echo '</div>';
            }
            
            // Separador visual
            if (count($comentarios) > 0 && count($estados) > 0) {
                echo '<hr class="my-4" style="border-top: 2px solid #dee2e6;">';
            }
            
            // Mostrar cambios de estado
            if (count($estados) > 0) {
                echo '<div>';
                echo '<h6 class="text-info mb-3"><i class="fas fa-history mr-2"></i>Status History</h6>';
                echo '<div class="historial-horizontal">'; // Contenedor flex
                
                foreach ($estados as $item) {
                    echo '<div class="historial-item" style="background-color: #f1f8ff; border-left: 4px solid #007bff;">';
                    echo '  <div style="display: flex; justify-content: space-between; align-items: center;">';
                    echo '    <div>';
                    echo '      <small class="text-muted">' . $item['fecha'] . '</small>';
                    echo '    </div>';
                    echo '    <div>';
                    echo '      <span class="badge badge-secondary">' . $item['usuario'] . '</span>';
                    echo '    </div>';
                    echo '  </div>';
                    echo '  <div class="mt-2">';
                    echo '    <span class="badge badge-warning">' . $item['estado_anterior'] . '</span>';
                    echo '    <i class="fas fa-arrow-right mx-2 text-muted"></i>';
                    echo '    <span class="badge badge-success">' . $item['estado_nuevo'] . '</span>';
                    echo '  </div>';
                    echo '</div>';
                }
                echo '</div>'; // Cierre contenedor flex
                echo '</div>';
            }
        } else {
            echo '<p class="text-muted text-center py-3">No history available</p>';
        }

        echo '</div>'; // Cierre del contenedor principal
        break;


























































































    case 'obtener_picks_automaticos':

    $idestado = isset($_POST['idestado']) ? $_POST['idestado'] : '5';
    $sql = ""; // Inicializamos para evitar undefined

    if ($idestado == '5') {
        // Verificar que existan al menos 3 PICKs disponibles (sin idenvio)
        $verificar = "SELECT COUNT(*) as total FROM i_picking WHERE (idenvio = 0 OR idenvio IS NULL)";
        $resultado = ejecutarConsultaSimpleFila($verificar);

        if ($resultado['total'] < 3) {
            $ultimo_numero = 0;
            $obtener_ultimo = "SELECT numero_packing FROM i_picking 
                            WHERE numero_packing REGEXP '^PACK[0-9]+$' 
                            ORDER BY CAST(SUBSTRING(numero_packing, 5) AS UNSIGNED) DESC 
                            LIMIT 1";
            $ultimo_resultado = ejecutarConsultaSimpleFila($obtener_ultimo);

            if ($ultimo_resultado) {
                $ultimo_numero = intval(str_replace('PACK', '', $ultimo_resultado['numero_packing']));
            }

            $picks_a_crear = 3 - $resultado['total'];

            for ($i = 1; $i <= $picks_a_crear; $i++) {
                $nuevo_numero = $ultimo_numero + $i;
                $nuevo_pick = 'PACK' . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);

                $sql_crear = "INSERT INTO i_picking (numero_packing, idenvio, fecha_creacion) 
                            VALUES ('$nuevo_pick', 0, NOW())";
                ejecutarConsulta($sql_crear);
            }
        }

        // Obtener los picks disponibles
        $sql = "SELECT p.*, 
                    COUNT(i.id) as total_productos,
                    SUM(CASE WHEN i.estado = 6 THEN 1 ELSE 0 END) as productos_packing
                FROM i_picking p
                LEFT JOIN i_importacion i ON p.id = i.id_picking
                WHERE  (p.idenvio = 0 OR p.idenvio IS NULL)
                GROUP BY p.id
                ORDER BY CAST(SUBSTRING(p.numero_packing, 5) AS UNSIGNED) ASC";
    }

    elseif ($idestado == '9') {
        $sql = "SELECT p.*, 
                    COUNT(i.id) as total_productos,
                    SUM(CASE WHEN i.estado = 6 THEN 1 ELSE 0 END) as productos_packing
                FROM i_picking p
                LEFT JOIN i_importacion i ON p.id = i.id_picking
                WHERE  (p.idenvio = 0 OR p.idenvio IS NULL)
                  AND i.id IS NOT NULL
                GROUP BY p.id
                HAVING total_productos > 0
                ORDER BY CAST(SUBSTRING(p.numero_packing, 5) AS UNSIGNED) ASC";
    }

    // ✅ Validación: si $sql no se definió correctamente, salir con error
    if (empty($sql)) {
        echo json_encode(['error' => 'No valid query defined for received status.']);
        break;
    }

    $rspta = ejecutarConsulta($sql);
    $data = [];

    while ($reg = $rspta->fetch_object()) {
        $data[] = [
            "id" => $reg->id,
            "numero_packing" => $reg->numero_packing,
            "total_productos" => $reg->total_productos,
            "productos_packing" => $reg->productos_packing,
            "idenvio" => $reg->idenvio,
            "fecha_creacion" => $reg->fecha_creacion
        ];
    }

    echo json_encode($data);
    break;



    case 'agregar_producto_pick':
        $id_producto = limpiarCadena($_POST["id_producto"]);
        $id_pick = limpiarCadena($_POST["id_pick"]);
        $cantidad = limpiarCadena($_POST["cantidad"]);
        $comentario = limpiarCadena($_POST["comentario"]);


        // Validar que el producto esté disponible
        $verificar = "SELECT estado FROM i_importacion WHERE id = '$id_producto'";
        $resultado = ejecutarConsultaSimpleFila($verificar);

        // Debug: Mostrar información del producto
        error_log("DEBUG - Producto ID: $id_producto, Estado encontrado: " . json_encode($resultado));

        if (!$resultado || $resultado['estado'] != 5) {
            $estado_actual = $resultado ? $resultado['estado'] : 'NOT FOUND';
            echo "Error: Product not available for packing. Product ID: $id_producto, Current state: $estado_actual (Expected: 5 - Producción)";
            break;
        }

        $sql = "UPDATE i_importacion SET 
            id_picking = '$id_pick',
            estado = 6,
            cant_enviada = '$cantidad',
            comentario = '$comentario'
            WHERE id = '$id_producto'";

        $rspta = ejecutarConsulta($sql);
        echo $rspta ? "Producto agregado correctamente" : "Error al agregar producto";
        break;

    case 'quitar_producto_pick':
        $id_producto = limpiarCadena($_POST["id_producto"]);

        $sql = "UPDATE i_importacion SET 
            id_picking = NULL, 
            estado = 5,
            cant_enviada = 0,
            comentario = ''
            WHERE id = '$id_producto'";

        $rspta = ejecutarConsulta($sql);
        echo $rspta ? "Product removed successfully" : "Error removing product";
        break;

    case 'enviar_pick_transito':
        $id_pick = limpiarCadena($_POST["id_pick"]);

        // Verificar que el PICK tenga productos
        $verificar = "SELECT COUNT(*) as total FROM i_importacion WHERE id_envio = '$id_pick' AND estado = 6";
        $resultado = ejecutarConsultaSimpleFila($verificar);

        if ($resultado['total'] == 0) {
            echo "Error: PICK has no assigned products";
            break;
        }

        // Actualizar estado del envío y productos
        $sql1 = "UPDATE i_envios SET estado = 7 WHERE id = '$id_pick'";
        $sql2 = "UPDATE i_importacion SET estado = 7 WHERE id_envio = '$id_pick'";

        $rspta = ejecutarTransaccion([$sql1, $sql2]);

        if ($rspta) {
            // Verificar si necesitamos crear más PICKs
            $verificar_disponibles = "SELECT COUNT(*) as total FROM i_envios WHERE estado = 5";
            $disponibles = ejecutarConsultaSimpleFila($verificar_disponibles);

            if ($disponibles['total'] < 3) {
                // Crear nuevo PICK automáticamente
                $obtener_ultimo = "SELECT numero_packing FROM i_envios 
                              WHERE numero_packing REGEXP '^PICK[0-9]+$' 
                              ORDER BY CAST(SUBSTRING(numero_packing, 5) AS UNSIGNED) DESC 
                              LIMIT 1";
                $ultimo_resultado = ejecutarConsultaSimpleFila($obtener_ultimo);

                $ultimo_numero = 0;
                if ($ultimo_resultado) {
                    $ultimo_numero = intval(str_replace('PICK', '', $ultimo_resultado['numero_packing']));
                }

                $nuevo_numero = $ultimo_numero + 1;
                $nuevo_pick = 'PICK' . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);

                $sql_crear = "INSERT INTO i_envios (numero_packing, estado, fecha_creacion) 
                          VALUES ('$nuevo_pick', 5, NOW())";
                ejecutarConsulta($sql_crear);
            }

            echo "PICK sent to transit successfully";
        } else {
            echo "Error sending PICK to transit";
        }
        break;









    // Agregar este caso a tu switch en ajax/quotes.php

    case 'enviar_pick_transito_con_datos':
        $id_pick = limpiarCadena($_POST["id_pick"]);
        $etd = limpiarCadena($_POST["etd"]);
        $del = limpiarCadena($_POST["del"]);
        $via = limpiarCadena($_POST["via"]);
        $transportadora = limpiarCadena($_POST["transportadora"]);
        $observaciones = limpiarCadena($_POST["observaciones"]);

        // Verificar que el PICK tenga productos
        $verificar = "SELECT COUNT(*) as total FROM i_importacion WHERE id_picking = '$id_pick' AND estado = 6";
        $resultado = ejecutarConsultaSimpleFila($verificar);

        if ($resultado['total'] == 0) {
            echo "Error: PICK has no assigned products";
            break;
        }

        // 1. Insertar datos de envío en i_envios
        $sql_envio = "INSERT INTO i_envios (etd, del, via, transportadora, observaciones, fecha_creacion) 
                  VALUES ('$etd', '$del', '$via', '$transportadora', '$observaciones', NOW())";

        $id_envio = ejecutarConsulta_retornarID($sql_envio);

        if (!$id_envio) {
            echo "Error creating shipping data";
            break;
        }

        // 2. Actualizar i_picking con el idenvio y cambiar estado a 7 (tránsito)
        $sql_picking = "UPDATE i_picking SET 
                    idenvio = '$id_envio',
                    estado = 7
                    WHERE id = '$id_pick'";

        // 3. Actualizar estado de productos a 7 (tránsito)
        $sql_productos = "UPDATE i_importacion SET estado = 7 WHERE id_picking = '$id_pick'";

        // Ejecutar transacción
        $rspta = ejecutarTransaccion([$sql_picking, $sql_productos]);

        if ($rspta) {
            // Verificar si necesitamos crear más PICKs
            $verificar_disponibles = "SELECT COUNT(*) as total FROM i_picking WHERE estado = 5";
            $disponibles = ejecutarConsultaSimpleFila($verificar_disponibles);

            if ($disponibles['total'] < 3) {
                // Crear nuevo PICK automáticamente
                $obtener_ultimo = "SELECT numero_packing FROM i_picking 
                              WHERE numero_packing REGEXP '^PICK[0-9]+$' 
                              ORDER BY CAST(SUBSTRING(numero_packing, 5) AS UNSIGNED) DESC 
                              LIMIT 1";
                $ultimo_resultado = ejecutarConsultaSimpleFila($obtener_ultimo);

                $ultimo_numero = 0;
                if ($ultimo_resultado) {
                    $ultimo_numero = intval(str_replace('PICK', '', $ultimo_resultado['numero_packing']));
                }

                $nuevo_numero = $ultimo_numero + 1;
                $nuevo_pick = 'PICK' . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);

                $sql_crear = "INSERT INTO i_picking (numero_packing, estado, idenvio, fecha_creacion) 
                          VALUES ('$nuevo_pick', 5, 0, NOW())";
                ejecutarConsulta($sql_crear);
            }

            echo "PICK sent to transit successfully with shipping data";
        } else {
            echo "Error sending PICK to transit";
        }
        break;






















    case 'obtener_picking_producto':
        $id_producto = limpiarCadena($_POST["id_producto"]);

        $sql = "SELECT i.id_picking, p.id as id_picking_tabla, p.numero_packing, p.idenvio
            FROM i_importacion i
            INNER JOIN i_picking p ON i.id_picking = p.id
            WHERE i.id = '$id_producto' AND i.estado = 6";

        $rspta = ejecutarConsultaSimpleFila($sql);

        if ($rspta) {
            echo json_encode(array(
                "id_picking" => $rspta['id_picking_tabla'],
                "numero_packing" => $rspta['numero_packing'],
                "id_envio" => $rspta['idenvio']
            ));
        } else {
            echo json_encode(array("error" => "Picking no encontrado para este producto"));
        }
        break;












    case 'asignar_datos_envio':
        $etd = limpiarCadena($_POST["etd"]);
        $del = limpiarCadena($_POST["del"]);
        $via = limpiarCadena($_POST["via"]);
        $transportadora = limpiarCadena($_POST["transportadora"]);
        $observaciones = limpiarCadena($_POST["observaciones"]);
        $comentario_pick = isset($_POST["comentario_pick"]) ? limpiarCadena($_POST["comentario_pick"]) : '';


        // Obtener productos seleccionados
        $productos_seleccionados = isset($_POST["productos_seleccionados"]) ? $_POST["productos_seleccionados"] : array();

        // Si viene un picking específico (desde modal)
        $picking_especifico = isset($_POST["id_picking"]) ? $_POST["id_picking"] : null;

        $pickings_a_procesar = array();

        if ($picking_especifico) {
            if (is_array($picking_especifico)) {
                $pickings_a_procesar = $picking_especifico;
            } else {
                $pickings_a_procesar[] = $picking_especifico;
            }
        } else if (!empty($productos_seleccionados)) {
            // Detectar pickings desde los productos seleccionados
            $productos_limpio = array_map('limpiarCadena', $productos_seleccionados);
            $productos_string = implode(',', $productos_limpio);

            $sql_detectar_pickings = "SELECT DISTINCT id_picking 
                                 FROM i_importacion 
                                 WHERE id IN ($productos_string) 
                                 AND id_picking IS NOT NULL 
                                 AND estado = 6";

            $rspta_pickings = ejecutarConsulta($sql_detectar_pickings);

            while ($reg = $rspta_pickings->fetch_object()) {
                $pickings_a_procesar[] = $reg->id_picking;
            }

            if (empty($pickings_a_procesar)) {
                echo "Error: Selected products have no pickings assigned in packing status";
                break;
            }
        } else {
            echo "Error: No products or picking specified";
            break;
        }

        // Verificar que los pickings tengan productos en estado 9
        $productos_totales = 0;
        $pickings_validos = array();

        foreach ($pickings_a_procesar as $picking_id) {
            $verificar_productos = "SELECT COUNT(*) as total FROM i_importacion WHERE id_picking = '$picking_id' AND estado = 6";
            $resultado_productos = ejecutarConsultaSimpleFila($verificar_productos);

            if ($resultado_productos['total'] > 0) {
                $pickings_validos[] = $picking_id;
                $productos_totales += $resultado_productos['total'];
            }
        }

        if (empty($pickings_validos)) {
            echo "Error: None of the pickings have products in packing status";
            break;
        }

        // Verificar si algún picking ya tiene idenvio
        $picking_con_envio = null;
        $id_envio_existente = null;

        foreach ($pickings_validos as $picking_id) {
            $verificar_envio = "SELECT idenvio FROM i_picking WHERE id = '$picking_id' AND idenvio > 0";
            $resultado_envio = ejecutarConsultaSimpleFila($verificar_envio);

            if ($resultado_envio) {
                $picking_con_envio = $picking_id;
                $id_envio_existente = $resultado_envio['idenvio'];
                break;
            }
        }

        $sqls_transaccion = array();

        if ($id_envio_existente) {
            // Actualizar envío existente
            $sqls_transaccion[] = "UPDATE i_envios SET 
                               etd = '$etd',
                               del = '$del',
                               via = '$via',
                               transportadora = '$transportadora',
                               observaciones = '$observaciones'
                               WHERE id = '$id_envio_existente'";
            $id_envio = $id_envio_existente;
        } else {
            // Crear nuevo envío
            $sql_crear_envio = "INSERT INTO i_envios (etd, del, via, transportadora, observaciones, fecha_creacion) 
                           VALUES ('$etd', '$del', '$via', '$transportadora', '$observaciones', NOW())";

            $id_envio = ejecutarConsulta_retornarID($sql_crear_envio);

            if (!$id_envio) {
                echo "Error creating shipping data";
                break;
            }
        }

        // Procesar todos los pickings
        $pickings_procesados = array();

        foreach ($pickings_validos as $picking_id) {
            // Actualizar picking con idenvio y estado 7 (tránsito)
            $sqls_transaccion[] = "UPDATE i_picking SET 
                               idenvio = '$id_envio',
                               estado = 7
                            --    observaciones = '$comentario_pick'
                               WHERE id = '$picking_id'";

            // Actualizar productos del picking a estado 7 (tránsito)
            $sqls_transaccion[] = "UPDATE i_importacion SET estado = 7 WHERE id_picking = '$picking_id' AND estado = 6";

            $pickings_procesados[] = $picking_id;
        }

        // Ejecutar transacción
        $rspta = ejecutarTransaccion($sqls_transaccion);

        if ($rspta) {
            // Obtener nombres de pickings procesados
            $pickings_string = implode(',', $pickings_procesados);
            $obtener_nombres = "SELECT numero_packing FROM i_picking WHERE id IN ($pickings_string)";
            $rspta_nombres = ejecutarConsulta($obtener_nombres);

            $nombres_pickings = array();
            while ($reg = $rspta_nombres->fetch_object()) {
                $nombres_pickings[] = $reg->numero_packing;
            }

            // Crear más pickings si es necesario
            $verificar_disponibles = "SELECT COUNT(*) as total FROM i_picking WHERE estado = 5 AND (idenvio = 0 OR idenvio IS NULL)";
            $disponibles = ejecutarConsultaSimpleFila($verificar_disponibles);

            if ($disponibles['total'] < 3) {
                $obtener_ultimo = "SELECT numero_packing FROM i_picking 
                              WHERE numero_packing REGEXP '^PICK[0-9]+$' 
                              ORDER BY CAST(SUBSTRING(numero_packing, 5) AS UNSIGNED) DESC 
                              LIMIT 1";
                $ultimo_resultado = ejecutarConsultaSimpleFila($obtener_ultimo);

                $ultimo_numero = 0;
                if ($ultimo_resultado) {
                    $ultimo_numero = intval(str_replace('PICK', '', $ultimo_resultado['numero_packing']));
                }

                $picks_necesarios = 3 - $disponibles['total'];
                for ($i = 1; $i <= $picks_necesarios; $i++) {
                    $nuevo_numero = $ultimo_numero + $i;
                    $nuevo_pick = 'PICK' . str_pad($nuevo_numero, 3, '0', STR_PAD_LEFT);

                    $sql_crear = "INSERT INTO i_picking (numero_packing, estado, idenvio, fecha_creacion) 
                              VALUES ('$nuevo_pick', 5, 0, NOW())";
                    ejecutarConsulta($sql_crear);
                }
            }

            echo "Shipping data assigned successfully. Processed pickings: " . implode(', ', $nombres_pickings) .
                ". Total products sent to transit: " . $productos_totales . ". ID Shipment: " . $id_envio;
        } else {
            echo "Error assigning shipping data 1";
        }
        break;




    case 'guardarCambioCantidadEnviada':
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
    $comentario = isset($_POST['comentario']) ? limpiarCadena($_POST['comentario']) : '';
    $idusuario = $_SESSION['id'] ?? 0;

    if ($id > 0 && $comentario !== '') {
      
        $resultado = $quotes->guardarCambioCantidadYComentario($id, $cantidad, $comentario, $idusuario);
        echo json_encode($resultado);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Invalid data.']);
    }
    break;




    case 'selectshippment':


		$rspta = $quotes->selectshippment();
		echo '<option selected value="0">Select shippment</option>';
		while ($reg = $rspta->fetch_object()) {
			echo '<option value=' . $reg->id . '>ID=' . $reg->consecutivo  . ' - ' . $reg->via  . '</option>';
		}
		break;

}
