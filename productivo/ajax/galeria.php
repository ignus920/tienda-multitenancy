<?php
session_start();
require_once "../modelos/Galeria.php";
require_once "../modelos/Galeriawordpress.php";
require_once "../config/wordpress_config.php";
$galeria = new Galeria();
$productos = new Galeriawordpress();



$id = isset($_POST["id"]) ? limpiarCadena($_POST["id"]) : "";
$idproducto = isset($_POST["idproducto"]) ? limpiarCadena($_POST["idproducto"]) : "";
$ubicacion = isset($_POST["ubicacion"]) ? limpiarCadena($_POST["ubicacion"]) : "";



switch ($_GET["op"]) {

    case 'subirGaleria':
    $contador = 0;
    $rspta = false; // Inicializar variable
    $seleccionable_web = isset($_POST["seleccionable_web_galeria"]) ? 1 : 0;
    
    // Verificar si se enviaron archivos
    if (!isset($_FILES["miarchivo"]) || empty($_FILES["miarchivo"]["tmp_name"])) {
        echo "Error: No se enviaron archivos";
        break;
    }
    
    // Verificar si idproducto está definido
    if (empty($idproducto)) {
        echo "Error: ID de producto no especificado";
        break;
    }
    
    // Obtener código del producto para WordPress (si está marcado para web)
    $codigo_producto = '';
    if ($seleccionable_web) {
        $sql_codigo = "SELECT codigo FROM c_productos WHERE id = '$idproducto'";
        $result_codigo = ejecutarConsultaSimpleFila($sql_codigo);
        $codigo_producto = $result_codigo['codigo'] ?? '';
    }
    
    // Como el elemento es un arreglos utilizamos foreach para extraer todos los valores
    $archivos_subidos = 0;
    $total_archivos = count($_FILES["miarchivo"]['tmp_name']);
    
    foreach ($_FILES["miarchivo"]['tmp_name'] as $key => $tmp_name) {
        // Verificar si el archivo temporal existe
        if (empty($tmp_name) || !is_uploaded_file($tmp_name)) {
            echo 'Archivo #' . ($key + 1) . ' no válido<br>';
            continue;
        }
        
        $nombre_archivo = $_FILES['miarchivo']['name'][$key];
        $tipo_archivo = $_FILES['miarchivo']['type'][$key];
        $tamaño_archivo = $_FILES['miarchivo']['size'][$key];
        
        echo 'Procesando: ' . $nombre_archivo . ' (Peso: ' . round($tamaño_archivo / 1024, 2) . ' KB)<br>';
        
        // Validar peso del archivo (aumentado a 10MB)
        if ($tamaño_archivo > 10485760) { // 10MB
            echo '❌ Archivo ' . $nombre_archivo . ' muy grande (máximo 10MB)<br>';
            continue;
        }
        
        // Validar que el archivo no esté vacío
        if ($tamaño_archivo == 0) {
            echo '❌ Archivo ' . $nombre_archivo . ' está vacío<br>';
            continue;
        }
        
        $ext = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
        $ext = strtolower($ext);
        
        // Mejorar validación de tipos
        $tipos_permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
        $mime_permitidos = ['image/jpg', 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
        
        if (in_array($ext, $tipos_permitidos) && in_array($tipo_archivo, $mime_permitidos)) {
            // Crear nombre único para el archivo
            $miarchivo = $contador . round(microtime(true)) . '.' . $ext;
            $ruta_archivo = "../files/galeria/" . $miarchivo;
            
            // Verificar que el directorio existe
            if (!is_dir("../files/galeria/")) {
                mkdir("../files/galeria/", 0755, true);
            }
            
            // Mover archivo
            if (move_uploaded_file($tmp_name, $ruta_archivo)) {
                // Guardar en base de datos
                $resultado_bd = $galeria->subirGaleria($miarchivo, $idproducto, $seleccionable_web, $ubicacion);
                
                if ($resultado_bd) {
                    echo '✅ Archivo ' . $nombre_archivo . ' subido correctamente';
                    $archivos_subidos++;
                    $rspta = true; // Al menos un archivo se subió
                    
                    // Si está marcado para web, subir también a WordPress
                    if ($seleccionable_web && $codigo_producto) {
                        $ruta_absoluta = realpath($ruta_archivo);
                        if ($ruta_absoluta) {
                            $resultado_wp = $productos->subirImagenGaleria($codigo_producto, $ruta_absoluta);
                            
                            if (strpos($resultado_wp, 'ERROR') === 0) {
                                echo ' (⚠️ Error WordPress: ' . $resultado_wp . ')';
                            } else {
                                echo ' (✅ WordPress ID: ' . $resultado_wp . ')';
                            }
                        }
                    }
                    echo '<br>';
                } else {
                    echo '❌ Error al guardar ' . $nombre_archivo . ' en base de datos<br>';
                    // Eliminar archivo si no se guardó en BD
                    if (file_exists($ruta_archivo)) {
                        unlink($ruta_archivo);
                    }
                }
            } else {
                echo '❌ Error al mover archivo ' . $nombre_archivo . '<br>';
            }
        } else {
            echo '❌ Archivo ' . $nombre_archivo . ' formato inválido (permitidos: JPG, PNG, GIF, WEBP, PDF)<br>';
        }
        
        $contador++;
    }
    // Mensaje final más informativo
    if ($archivos_subidos > 0) {
        echo '<hr>✅ Proceso completado: ' . $archivos_subidos . ' de ' . $total_archivos . ' archivos subidos exitosamente';
    } else {
        echo '<hr>❌ No se pudo subir ningún archivo';
    }
    break;

case 'actualizarWebStatus':
    $id_imagen = $_POST['id_imagen'];
    $seleccionable_web = isset($_POST['seleccionable_web']) ? (int)$_POST['seleccionable_web'] : 0;
    
    // Log para debug
    error_log("Actualizando imagen ID: $id_imagen, valor: $seleccionable_web");
    
    // Si se desmarca para web, también resetear la bandera de sincronizada
  
    $sql = "UPDATE galeria_productos SET seleccionable_web = $seleccionable_web  WHERE id = $id_imagen";
  
    $rspta = ejecutarConsulta($sql);
    
    if ($rspta) {
        // Verificar que realmente se actualizó
        $verify_sql = "SELECT seleccionable_web, sincronizada FROM galeria_productos WHERE id = $id_imagen";
        $verify_result = ejecutarConsulta($verify_sql);
        $verify_row = mysqli_fetch_assoc($verify_result);
        
        $mensaje = $seleccionable_web ? 'Imagen marcada para web' : 'Imagen desmarcada para web';
        
        echo json_encode([
            'success' => true,
            'message' => $mensaje,
            'nuevo_valor' => $verify_row['seleccionable_web'],
            'sincronizada' => $verify_row['sincronizada'],
            'debug' => "ID: $id_imagen, Web: " . $verify_row['seleccionable_web'] . ", Sincronizada: " . $verify_row['sincronizada']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Error al actualizar estado',
            'debug' => "SQL: $sql"
        ]);
    }
    break;






    case 'MostraraGaleria':
        // Determinar filtro por ubicación según permisos
        $filtro_ubicacion = "";
        
        // Perfiles Admin/Gestión (Mercadeo, Laboratorio, Parámetros, SuperUsuario) ven TODO
        $perm_lab = (isset($_SESSION['Laboratorio']) && $_SESSION['Laboratorio'] == 1);
        $perm_par = (isset($_SESSION['Parametros']) && $_SESSION['Parametros'] == 1);
        $perm_mer = (isset($_SESSION['Mercadeo']) && $_SESSION['Mercadeo'] == 1);
        $perm_sup = (isset($_SESSION['SuperUsuario']) && $_SESSION['SuperUsuario'] == 1);
        
        // Eliminamos el ID 81 hardcoded por seguridad, ya que podría estar causando falsos positivos
        $es_admin = $perm_lab || $perm_par || $perm_mer || $perm_sup;
        
        $filtro_ubicacion = "";
        if (!$es_admin) {
            $hasAlmacen = (isset($_SESSION['Almacen']) && $_SESSION['Almacen'] == 1);
            $hasComercial = (isset($_SESSION['Comercial']) && $_SESSION['Comercial'] == 1);

            if ($hasAlmacen && $hasComercial) {
                $filtro_ubicacion = ""; // Ver ambos si tiene ambos permisos
            } elseif ($hasAlmacen) {
                $filtro_ubicacion = " AND g.ubicacion = '2'";
            } elseif ($hasComercial) {
                $filtro_ubicacion = " AND g.ubicacion = '1'";
            } else {
                $filtro_ubicacion = " AND g.ubicacion = '1'"; 
            }
        }

        $mostrar_wordpress = isset($_POST['mostrar_wordpress']) ? $_POST['mostrar_wordpress'] : false;

        $rspta = $galeria->MostraraGaleria($idproducto, $filtro_ubicacion);
        // Mostrar las imágenes de la galería
        while ($reg = $rspta->fetch_object()) {
            
            // REINICIAR VARIABLES DENTRO DEL LOOP PARA EVITAR QUE SE ARRASTREN VALORES
            $sw = "";
            $ubicacion_label = "";

            if ($reg->ubicacion == 2) {
               $ubicacion_label = '<br><span class="badge badge-success">Bodega</span>';
            } else {
               $ubicacion_label = '<br><span class="badge badge-warning">Comercial</span>';
            }

            // Indicador visual si está marcada para web
            if ($reg->seleccionable_web) {
                $sw = '<br><span class="badge badge-success"><i class="fab fa-wordpress"></i> WP</span>';
            }

            echo '<div class="float-left">
            <div class="col-12 col-sm-12 col-md-12" style="width: 100%; border-radius: 10px; padding: 10px; margin-bottom: 5px;">
            <div class="card bg-light elevation-2">
            <div class="card-body pt-0">
            <div class="row">
            <div class="col-12">
            <h4 class="text-center"><strong></strong></h4>';
            
            // Mostrar imagen si existe y estado = 1
            if ($reg->estado == 1) {
                $fileInfo = pathinfo($reg->tximagen);
                $extension = strtolower($fileInfo['extension']);  // Convertir la extensión a minúsculas
                if ($extension === 'pdf') {
                    echo $ubicacion_label.'<div style="text-align: center; margin-bottom: 10px;">
                    <a target="_blank" href="../files/galeria/' . $reg->tximagen . '">
                    <img src="../files/img/pdf.png" height="200px" width="200px" style="border: 1px solid #ccc; border-radius: 5px;" title="Ver PDF">
                    </a>
                    </div>';
                }else{
                    echo $ubicacion_label.'<div style="text-align: center; margin-bottom: 10px;">
                    <a target="_blank" href="../files/galeria/' . $reg->tximagen . '">
                    <img src="../files/galeria/' . $reg->tximagen . '" height="200px" width="200px" style="border: 1px solid #ccc; border-radius: 5px;" title="Ver Imagen">
                    </a>
                    </div>';

                }
            } else {
                echo '<div style="text-align: center; margin-bottom: 10px;"><span class="btn-sm label bg-red">Desactivado</span></div>';
            }
    
            echo '
            <ul class="ml-4 mb-0 fa-ul text-muted">
            <li></li>
            <li></li>
            </ul>
            
            ';
            
            // Solo mostrar funcionalidades de WordPress si está habilitado
            if ($mostrar_wordpress) {
                echo '
                <!-- Checkbox para seleccionar para web -->
                <div class="form-check mt-2">
                    <input type="checkbox" class="form-check-input mr-3" id="web_' . $reg->id . '" onchange="actualizarWebStatus(' . $reg->id . ', this.checked)" ' . ($reg->seleccionable_web ? 'checked' : '') . '>
                    <label class="form-check-label mr-5" for="web_' . $reg->id . '">
                        <small><strong>Para página web  '.$sw.'</strong> </small> 
                        
                    </label>';

                

                
                if ($_SESSION['id'] == '81' || $_SESSION['Laboratorio'] == 1 || $_SESSION['Parametros'] == 1  || $_SESSION['Mercadeo'] == 1) {
                    echo '<button class="btn-sm btn-danger"  title="eliminar" onclick="eliminarGaleria(' . $reg->id . ')"><i class="fa fa-close"></i></button>';
                } elseif ($reg->estado == 0) {
                    echo '<button class="btn-flat btn-sm btn-primary mr-2" onclick="activar(' . $reg->id . ')"><i class="fa fa-check"></i></button>';
                }
            
                    
               
                
                echo '
                </div>';
            }
            
            echo '
            </div>
            </div>
            </div>
            </div>
            
            <div class="text-right">';
            
            // Botones de acción según la vista y permisos
          
    
            echo '
            </div>
            </div>
            </div>
            </div>
            </div>';
        }
    
    
        break;




case 'eliminarGaleria':
$rspta = $galeria->eliminarGaleria($id);
echo $rspta ? "Foto eliminada" : "Foto no se puede desactivar";
break;
}
