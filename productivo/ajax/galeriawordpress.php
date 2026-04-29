<?php
/**
 * Ajax handler para sincronización de imágenes con WordPress
 */
require "../modelos/Galeriawordpress.php";

$productos = new Galeriawordpress();
$ubicacionp=isset($_POST["ubicacionp"])? limpiarCadena($_POST["ubicacionp"]):"";

switch ($_GET["op"]) {
    
    case 'obtener_producto_padre':
        $codigo = $_POST['codigo'] ?? '';
        
        if (empty($codigo)) {
            echo json_encode(['success' => false, 'message' => 'Código requerido']);
            break;
        }
        
        try {
            // Primero buscar la variante
            $wp_info = $productos->buscarProductoWordPressDirecto($codigo);
            
            if (!$wp_info['encontrado']) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado en WordPress']);
                break;
            }
            
            if (!$wp_info['es_variante']) {
                // Si no es variante, ya es el producto principal
                echo json_encode([
                    'success' => true, 
                    'es_variante' => false,
                    'producto' => $wp_info,
                    'message' => 'Este ya es un producto principal'
                ]);
                break;
            }
            
            // Si es variante, obtener el producto padre
            $padre_info = $productos->obtenerProductoPadre($wp_info['parent_id']);
            
            if (!$padre_info['encontrado']) {
                echo json_encode(['success' => false, 'message' => 'Producto padre no encontrado']);
                break;
            }
            
            echo json_encode([
                'success' => true,
                'es_variante' => true,
                'variante' => $wp_info,
                'padre' => $padre_info,
                'message' => 'Producto padre obtenido exitosamente'
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
    
    case 'sincronizar_imagen_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        $codigo_variante = $_POST['codigo_variante'] ?? '';
        
        if (empty($parent_id) || empty($codigo_variante)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros requeridos faltantes']);
            break;
        }
        
        try {
            // Obtener información del producto ERP usando el código de la variante
            $producto_erp = $productos->mostrar('codigo', $codigo_variante);
            
            if (!$producto_erp || empty($producto_erp['tximagen'])) {
                echo json_encode(['success' => false, 'message' => 'Producto ERP sin imagen']);
                break;
            }
            
            // IMPORTANTE: Usar la función para asignar imagen DIRECTAMENTE al parent_id de WordPress
            // No buscar por código, sino usar directamente el ID de WordPress
            $resultado = $productos->asignarImagenPrincipalDirecta($parent_id, $producto_erp['tximagen']);
            
            if (strpos($resultado, 'ERROR') === false) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Imagen del producto padre sincronizada exitosamente',
                    'attachment_id' => $resultado
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => $resultado]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
    
    case 'sincronizar_imagen_principal':
        error_log("POST recibido: " . print_r($_POST, true));
        
        if (!isset($_POST['idproducto'])) {
            echo json_encode(['success' => false, 'message' => 'idproducto no recibido', 'debug_post' => $_POST]);
            break;
        }
        
        $idproducto = $_POST['idproducto'];
        $forzar_sincronizacion = isset($_POST['forzar']) ? $_POST['forzar'] : false;
        
        try {
            // Obtener información del producto
            $producto_info = $productos->mostrar('id', $idproducto);
            
            if (!$producto_info) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
                break;
            }
            
            $codigo = $producto_info['codigo'];
            $imagen_local = $producto_info['tximagen'];
            
            // Verificar si tiene imagen (local o URL de WordPress)
            if (empty($imagen_local)) {
                echo json_encode(['success' => false, 'message' => 'El producto no tiene imagen para sincronizar']);
                break;
            }

            // Si es URL de WordPress, permitir la sincronización descargándola temporalmente
            if (strpos($imagen_local, 'http') === 0) {
                // Es una URL de WordPress - descargar temporalmente para re-subir
                $temp_file = tempnam(sys_get_temp_dir(), 'wp_sync_') . '.jpg';
                $imagen_data = file_get_contents($imagen_local);
                if ($imagen_data !== false && file_put_contents($temp_file, $imagen_data) !== false) {
                    $imagen_local = $temp_file; // Usar archivo temporal
                } else {
                    echo json_encode(['success' => false, 'message' => 'No se pudo procesar la imagen de WordPress']);
                    break;
                }
            } else {
                // Es archivo local - validar que existe
                if (!file_exists($imagen_local)) {
                    echo json_encode(['success' => false, 'message' => 'Archivo de imagen local no encontrado: ' . $imagen_local]);
                    break;
                }
            }
            
            // Verificar si ya existe en WordPress
            $wp_info = $productos->linkcodigo($codigo);
            $ya_existe_wp = $wp_info && !empty($wp_info['existe']) && $wp_info['existe'] > 0;
            
            if ($ya_existe_wp && !$forzar_sincronizacion) {
                // Producto ya existe en WordPress, preguntar al usuario
                echo json_encode([
                    'success' => false, 
                    'requires_confirmation' => true,
                    'message' => 'El producto ya tiene imagen en WordPress. ¿Desea reemplazarla?',
                    'existing_image' => $wp_info,
                    'producto' => $producto_info
                ]);
                break;
            }
            
            // Convertir ruta relativa a absoluta
            $ruta_absoluta = realpath($imagen_local);
            if (!$ruta_absoluta) {
                echo json_encode(['success' => false, 'message' => 'No se pudo encontrar la imagen: ' . $imagen_local]);
                break;
            }
            
            // Sincronizar con WordPress (ahora detecta automáticamente variantes)
            $resultado = $productos->asignarImagenPrincipal($codigo, $ruta_absoluta);
            
            if (strpos($resultado, 'ERROR') === 0) {
                echo json_encode(['success' => false, 'message' => $resultado]);
            } else {
                // Obtener la URL de la imagen desde WordPress
                $url_imagen_wp = $productos->obtenerUrlImagenWordPress($resultado);
                
                // Actualizar la imagen en la base de datos del ERP con la URL de WordPress
                $productos->editarImagenProducto($idproducto, $url_imagen_wp,$ubicacionp);
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Imagen sincronizada exitosamente con WordPress',
                    'wp_image_id' => $resultado,
                    'wp_image_url' => $url_imagen_wp,
                    'codigo' => $codigo
                ]);
            }

            // Limpiar archivo temporal si se creó uno
            if (isset($temp_file) && $temp_file && file_exists($temp_file)) {
                unlink($temp_file);
            }

        } catch (Exception $e) {
            // Limpiar archivo temporal en caso de error también
            if (isset($temp_file) && $temp_file && file_exists($temp_file)) {
                unlink($temp_file);
            }
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'verificar_imagen_wordpress':
        $codigo = $_POST['codigo'];
        
        try {
            // NUEVO: Verificar usando búsqueda directa en WordPress API
            $wp_info = $productos->buscarProductoWordPressDirecto($codigo);
            $producto_info = $productos->mostrar('codigo', $codigo);
            
            if ($wp_info['encontrado']) {
                echo json_encode([
                    'existe_wp' => true,
                    'wp_info' => [
                        'existe' => $wp_info['id'],
                        'es_variante' => $wp_info['es_variante'],
                        'parent_id' => $wp_info['parent_id'] ?? null,
                        'variation_id' => $wp_info['es_variante'] ? $wp_info['id'] : null,
                        'metodo_encontrado' => $wp_info['metodo'],
                        'product_type' => $wp_info['product_type']
                    ],
                    'producto_info' => $producto_info,
                    'mensaje' => $wp_info['es_variante'] ? 
                        'Producto encontrado como VARIANTE en WordPress (Método: ' . $wp_info['metodo'] . ')' :
                        'Producto encontrado como PRODUCTO PADRE en WordPress (Método: ' . $wp_info['metodo'] . ')'
                ]);
            } else {
                echo json_encode([
                    'existe_wp' => false,
                    'producto_info' => $producto_info,
                    'mensaje' => 'Producto no encontrado en WordPress: ' . ($wp_info['error'] ?? 'Razón desconocida')
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;
        
    case 'guardar_imagen_con_web_flag':
        $idproducto = $_POST['idp'];
        $codigo = $_POST['codigo'];
        $seleccionable_web = isset($_POST['seleccionable_web']) ? 1 : 0;
        
        
        try {
            // Manejar la imagen como siempre
            if (!file_exists($_FILES['tximagen']['tmp_name']) || !is_uploaded_file($_FILES['tximagen']['tmp_name'])) {
                echo json_encode(['success' => false, 'message' => 'No se subió ninguna imagen']);
                break;
            }
            
            $ext = pathinfo($_FILES["tximagen"]["name"], PATHINFO_EXTENSION);
            if (!in_array($_FILES['tximagen']['type'], array("image/jpg", "image/jpeg", "image/png", "image/webp"))) {
                echo json_encode(['success' => false, 'message' => 'Formato de imagen no válido']);
                break;
            }
            
            // Generar un nombre único para la imagen
            $nuevo_nombre = round(microtime(true)) . '.' . $ext;
            $ruta_destino = "../files/productos/" . $nuevo_nombre;
            
            if (move_uploaded_file($_FILES["tximagen"]["tmp_name"], $ruta_destino)) {
                $ruta_relativa = '../files/productos/' . $nuevo_nombre;
                
                // Actualizar la imagen en la base de datos del ERP
                $resultado_erp = $productos->editarImagenProducto($idproducto, $ruta_relativa,$ubicacionp);
                
                if ($resultado_erp) {
                    $respuesta = ['success' => true, 'message' => 'Imagen guardada exitosamente', 'id' => $idproducto];
                    
                    // Si está marcado para web, sincronizar con WordPress
                    if ($seleccionable_web == 1) {
                        $ruta_absoluta = realpath($ruta_destino);
                        $resultado_wp = $productos->asignarImagenPrincipal($codigo, $ruta_absoluta);
                        
                        if (strpos($resultado_wp, 'ERROR') === 0) {
                            $respuesta['wp_warning'] = 'Imagen guardada localmente, pero falló la sincronización con WordPress: ' . $resultado_wp;
                        } else {
                            // Obtener la URL de la imagen desde WordPress y actualizar en BD
                            $url_imagen_wp = $productos->obtenerUrlImagenWordPress($resultado_wp);
                            if ($url_imagen_wp) {
                                $productos->editarImagenProducto($idproducto, $url_imagen_wp,$ubicacionp);
                                $respuesta['wp_success'] = 'Imagen sincronizada con WordPress y actualizada en ERP (ID: ' . $resultado_wp . ')';
                            } else {
                                $respuesta['wp_success'] = 'Imagen sincronizada con WordPress (ID: ' . $resultado_wp . ')';
                            }
                        }
                    }
                    
                    echo json_encode($respuesta);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al mover el archivo']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'sincronizar_galeria':
        $idproducto = $_POST['idproducto'];
        
        try {
            // Obtener información del producto
            $producto_info = $productos->mostrar('id', $idproducto);
            $codigo = $producto_info['codigo'];
            
            // Ya no necesitamos verificar WordPress - usamos la bandera sincronizada
            
            // PASO 2: Obtener imágenes del ERP marcadas para sincronizar que NO estén ya sincronizadas
            $query = "SELECT * FROM galeria_productos WHERE idproducto = '$idproducto' AND estado = 1 AND seleccionable_web = 1 AND (sincronizada = 0 OR sincronizada IS NULL)";
            $result = ejecutarConsulta($query);
            
            $sincronizadas = 0;
            $errores = 0;
            $detalles = [];
            
            if (!$result || mysqli_num_rows($result) == 0) {
                echo json_encode([
                    'success' => true,
                    'sincronizadas' => 0,
                    'errores' => 0,
                    'detalles' => [],
                    'message' => "No hay imágenes pendientes de sincronizar con WordPress"
                ]);
                break;
            }
            
            // PASO 3: Procesar cada imagen (ya filtradas por sincronizada = 0)
            while ($imagen = mysqli_fetch_assoc($result)) {
                $id_galeria = $imagen['id'];
                $nombre_archivo = $imagen['tximagen']; // Solo contiene el nombre: "01716824088.jpg"
                $ruta_imagen = "../files/galeria/" . $nombre_archivo;
                
                if (file_exists($ruta_imagen)) {
                    $ruta_absoluta = realpath($ruta_imagen);
                    
                    // Sincronizar imagen con WordPress (para galería)
                    $resultado_wp = $productos->subirImagenGaleria($codigo, $ruta_absoluta);
                    
                    if (strpos($resultado_wp, 'ERROR') === 0) {
                        $errores++;
                        $detalles[] = [
                            'imagen' => $nombre_archivo, 
                            'status' => 'error',
                            'error' => $resultado_wp
                        ];
                    } else {
                        $sincronizadas++;
                        
                        // MARCAR COMO SINCRONIZADA
                        $update_query = "UPDATE galeria_productos SET sincronizada = 1 WHERE id = '$id_galeria'";
                        ejecutarConsulta($update_query);
                        
                        $detalles[] = [
                            'imagen' => $nombre_archivo, 
                            'status' => 'sincronizada',
                            'wp_id' => $resultado_wp
                        ];
                    }
                } else {
                    $errores++;
                    $detalles[] = [
                        'imagen' => $nombre_archivo, 
                        'status' => 'error',
                        'error' => 'Archivo no encontrado en: ' . $ruta_imagen
                    ];
                }
            }
            
            // Construir mensaje detallado
            $mensaje_partes = [];
            if ($sincronizadas > 0) $mensaje_partes[] = "$sincronizadas sincronizadas";
            if ($errores > 0) $mensaje_partes[] = "$errores errores";
            
            $mensaje_final = "Sincronización completada: " . implode(', ', $mensaje_partes);
            if (empty($mensaje_partes)) $mensaje_final = "No había imágenes pendientes de sincronizar";
            
            echo json_encode([
                'success' => true,
                'sincronizadas' => $sincronizadas,
                'errores' => $errores,
                'total_procesadas' => $sincronizadas + $errores,
                'detalles' => $detalles,
                'message' => $mensaje_final
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'comparar_galeria':
        $idproducto = $_POST['idproducto'];
        
        try {
            // Obtener información del producto
            $producto_info = $productos->mostrar('id', $idproducto);
            $codigo = $producto_info['codigo'];
            $imagen_principal_erp = $producto_info['tximagen'] ? basename($producto_info['tximagen']) : null;
            
            // Obtener imágenes del ERP
            $query_erp = "SELECT tximagen FROM galeria_productos WHERE idproducto = '$idproducto' AND estado = 1";
            $result_erp = ejecutarConsulta($query_erp);
            
            $imagenes_erp = [];
            while ($row = mysqli_fetch_assoc($result_erp)) {
                $nombre_imagen = basename($row['tximagen']);
                $imagenes_erp[] = [
                    'nombre' => $nombre_imagen,
                    'es_principal' => ($nombre_imagen === $imagen_principal_erp)
                ];
            }
            
            // Obtener imágenes de WordPress usando API directa
            $wp_info_directo = $productos->buscarProductoWordPressDirecto($codigo);
            $imagenes_wp = [];
            $imagenes_padre = [];
            
            // Debug: Log información del producto encontrado
            error_log("COMPARAR_GALERIA - Código: $codigo");
            error_log("COMPARAR_GALERIA - WP Info: " . json_encode($wp_info_directo));
            
            if ($wp_info_directo['encontrado']) {
                if ($wp_info_directo['es_variante']) {
                    // Para VARIANTE: obtener su imagen + imágenes del padre
                    $parent_id = $wp_info_directo['parent_id'];
                    $variation_id = $wp_info_directo['id'];
                    
                    // Imagen de la variante
                    $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id . '/variations/' . $variation_id);
                    $result = executeWPCurl($ch);
                    
                    if ($result['success']) {
                        $variante_wp = json_decode($result['response'], true);
                        $imagen_variante = $variante_wp['image'] ?? null;
                        
                        error_log("COMPARAR_GALERIA - Imagen variante: " . json_encode($imagen_variante));
                        
                        if ($imagen_variante) {
                            $imagenes_wp[] = [
                                'nombre' => basename($imagen_variante['src']),
                                'url' => $imagen_variante['src'],
                                'id' => $imagen_variante['id'],
                                'es_principal' => true,
                                'tipo' => 'variante'
                            ];
                        }
                    } else {
                        error_log("COMPARAR_GALERIA - Error al obtener variante: " . json_encode($result));
                    }
                    
                    // Imágenes del producto padre
                    $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
                    $result = executeWPCurl($ch);
                    
                    if ($result['success']) {
                        $padre_wp = json_decode($result['response'], true);
                        $images_padre = $padre_wp['images'] ?? [];
                        
                        error_log("COMPARAR_GALERIA - Imágenes padre encontradas: " . count($images_padre));
                        
                        foreach ($images_padre as $index => $image) {
                            $imagenes_padre[] = [
                                'nombre' => basename($image['src']),
                                'url' => $image['src'],
                                'id' => $image['id'],
                                'es_principal' => ($index === 0),
                                'tipo' => 'padre'
                            ];
                        }
                    } else {
                        error_log("COMPARAR_GALERIA - Error al obtener producto padre: " . json_encode($result));
                    }
                } else {
                    // Para PRODUCTO NORMAL: obtener sus imágenes normalmente
                    $idProductoWP = $wp_info_directo['id'];
                    
                    $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $idProductoWP);
                    $result = executeWPCurl($ch);
                    
                    if ($result['success']) {
                        $producto_wp = json_decode($result['response'], true);
                        $images = $producto_wp['images'] ?? [];
                        
                        error_log("COMPARAR_GALERIA - Imágenes producto normal encontradas: " . count($images));
                        
                        foreach ($images as $index => $image) {
                            $imagenes_wp[] = [
                                'nombre' => basename($image['src']),
                                'url' => $image['src'],
                                'id' => $image['id'],
                                'es_principal' => ($index === 0),
                                'tipo' => 'producto'
                            ];
                        }
                    } else {
                        error_log("COMPARAR_GALERIA - Error al obtener producto normal: " . json_encode($result));
                    }
                }
            }
            
            // Debug: Log resultados finales
            error_log("COMPARAR_GALERIA - Total imágenes WP encontradas: " . count($imagenes_wp));
            error_log("COMPARAR_GALERIA - Imágenes WP: " . json_encode($imagenes_wp));
            
            // Comparar arrays - extraer solo nombres para comparación
            $nombres_erp = array_map(function($img) { return $img['nombre']; }, $imagenes_erp);
            $nombres_wp = array_map(function($img) { return $img['nombre']; }, $imagenes_wp);
            
            $solo_erp = array_filter($imagenes_erp, function($img) use ($nombres_wp) {
                return !in_array($img['nombre'], $nombres_wp);
            });
            
            $solo_wp = array_filter($imagenes_wp, function($img) use ($nombres_erp) {
                return !in_array($img['nombre'], $nombres_erp);
            });
            
            // Preparar respuesta
            $respuesta = [
                'success' => true,
                'total_erp' => count($imagenes_erp),
                'total_wp' => count($nombres_wp),
                'solo_erp' => array_values($solo_erp),
                'solo_wp' => array_values($solo_wp),
                'imagenes_erp' => $imagenes_erp,
                'imagenes_wp' => $imagenes_wp,
                'imagen_principal_erp' => $imagen_principal_erp,
                'es_variante' => $wp_info_directo['encontrado'] ? $wp_info_directo['es_variante'] : false
            ];
            
            // Si es variante, agregar información del producto padre
            if ($wp_info_directo['encontrado'] && $wp_info_directo['es_variante']) {
                $respuesta['parent_id'] = $wp_info_directo['parent_id'];
                $respuesta['imagenes_padre'] = $imagenes_padre;
                $respuesta['total_padre'] = count($imagenes_padre);
            }
            
            echo json_encode($respuesta);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'eliminar_imagen_wp':
        $idproducto = $_POST['idproducto'];
        $nombre_imagen = $_POST['nombre_imagen'];
        
        try {
            // Obtener información del producto
            $producto_info = $productos->mostrar('id', $idproducto);
            $codigo = $producto_info['codigo'];
            
            // Buscar producto en WordPress usando método robusto
            $wp_info_directo = $productos->buscarProductoWordPressDirecto($codigo);
            if (!$wp_info_directo['encontrado']) {
                echo json_encode(['success' => false, 'message' => 'Producto no encontrado en WordPress']);
                break;
            }
            
            // Determinar el ID del producto correcto según el tipo
            if ($wp_info_directo['es_variante']) {
                // Para variantes, necesitamos el parent_id para acceder a las imágenes
                $idProductoWP = $wp_info_directo['parent_id'];
            } else {
                // Para productos normales, usar el ID directo
                $idProductoWP = $wp_info_directo['id'];
            }
            
            // Obtener imágenes actuales del producto
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $idProductoWP);
            $result = executeWPCurl($ch);
            
            if (!$result['success']) {
                echo json_encode(['success' => false, 'message' => 'No se pudo obtener producto de WordPress']);
                break;
            }
            
            $producto_wp = json_decode($result['response'], true);
            $images = $producto_wp['images'] ?? [];
            
            // Buscar la imagen a eliminar y filtrar las demás
            $imagenes_filtradas = [];
            $imagen_eliminada = false;
            
            foreach ($images as $image) {
                if (basename($image['src']) != $nombre_imagen) {
                    $imagenes_filtradas[] = ['id' => $image['id']];
                } else {
                    $imagen_eliminada = true;
                }
            }
            
            if (!$imagen_eliminada) {
                echo json_encode(['success' => false, 'message' => 'Imagen no encontrada en WordPress']);
                break;
            }
            
            // Actualizar producto con la lista filtrada de imágenes
            $datosProducto = [
                'images' => $imagenes_filtradas
            ];
            
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $idProductoWP);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => json_encode($datosProducto)
            ]);
            
            $result = executeWPCurl($ch);
            
            if ($result['success']) {
                // Actualizar el campo sincronizada=0 en la tabla galeria_productos
                $query_update = "UPDATE galeria_productos SET seleccionable_web = 0 ,sincronizada = 0  WHERE idproducto = '$idproducto'";
                ejecutarConsulta($query_update);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Imagen eliminada exitosamente de WordPress',
                    'imagen' => $nombre_imagen
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar producto en WordPress']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'comparar_imagen_principal':
        $idproducto = $_POST['idproducto'];
        
        try {
            // Obtener información del producto
            $producto_info = $productos->mostrar('id', $idproducto);
            $codigo = $producto_info['codigo'];
            $imagen_erp = $producto_info['tximagen'];
            
            // NUEVO: Verificar usando búsqueda directa en WordPress API
            $wp_info_directo = $productos->buscarProductoWordPressDirecto($codigo);
            $imagen_wp = null;
            $url_imagen_wp = null;

            // Si no se encuentra directamente, intentar con la vista ps_productos como respaldo
            if (!$wp_info_directo['encontrado']) {
                $wp_info_vista = $productos->linkcodigo($codigo);
                if ($wp_info_vista && !empty($wp_info_vista['existe']) && $wp_info_vista['existe'] > 0) {
                    $wp_info_directo = [
                        'encontrado' => true,
                        'metodo' => 'vista_ps_productos',
                        'id' => $wp_info_vista['existe'],
                        'parent_id' => $wp_info_vista['es_variante'] ? $wp_info_vista['parent_id'] : null,
                        'es_variante' => $wp_info_vista['es_variante'],
                        'sku' => $codigo,
                        'name' => 'Producto encontrado por vista'
                    ];
                }
            }

            if ($wp_info_directo['encontrado']) {
                // Determinar endpoint según tipo de producto
                if ($wp_info_directo['es_variante']) {
                    // Para VARIANTES: usar endpoint /variations/
                    $parent_id = $wp_info_directo['parent_id'];
                    $variation_id = $wp_info_directo['id'];
                    $endpoint_url = WP_URL . '/wp-json/wc/v3/products/' . $parent_id . '/variations/' . $variation_id;
                } else {
                    // Para PRODUCTOS PADRE: usar endpoint /products/
                    $producto_id = $wp_info_directo['id'];
                    $endpoint_url = WP_URL . '/wp-json/wc/v3/products/' . $producto_id;
                }
                
                $ch = setupWPCurl($endpoint_url);
                $result = executeWPCurl($ch);
                
                if ($result['success']) {
                    $producto_wp = json_decode($result['response'], true);
                    
                    if ($wp_info_directo['es_variante']) {
                        // Para variantes, la imagen está en 'image' (singular)
                        $imagen_data = $producto_wp['image'] ?? null;
                        if ($imagen_data) {
                            $imagen_wp = basename($imagen_data['src']);
                            $url_imagen_wp = $imagen_data['src'];
                        }
                    } else {
                        // Para productos padre, la imagen principal está en 'images[0]'
                        $images = $producto_wp['images'] ?? [];
                        if (!empty($images)) {
                            $imagen_principal = $images[0];
                            $imagen_wp = basename($imagen_principal['src']);
                            $url_imagen_wp = $imagen_principal['src'];
                        }
                    }
                }
                
                // Crear wp_info compatible con código existente
                $wp_info = [
                    'existe' => $wp_info_directo['id'],
                    'es_variante' => $wp_info_directo['es_variante'],
                    'parent_id' => $wp_info_directo['parent_id'] ?? null,
                    'metodo' => $wp_info_directo['metodo']
                ];
            } else {
                $wp_info = null;
            }
            
            // Determinar URLs para mostrar
            $url_imagen_erp = null;
            $nombre_imagen_erp = null;
            
            if (!empty($imagen_erp)) {
                if (strpos($imagen_erp, 'http') === 0) {
                    // Es una URL de WordPress
                    $url_imagen_erp = $imagen_erp;
                    $nombre_imagen_erp = basename(parse_url($imagen_erp, PHP_URL_PATH));
                } else if (file_exists($imagen_erp)) {
                    // Es un archivo local
                    $url_imagen_erp = str_replace('../', '', $imagen_erp);
                    $nombre_imagen_erp = basename($imagen_erp);
                }
            }
            
            // Comparación mejorada - detectar si son la misma imagen
            $son_diferentes = true;
            if ($nombre_imagen_erp && $imagen_wp) {
                // Si el ERP tiene una URL de WordPress, comparar directamente los nombres
                if (strpos($imagen_erp, 'http') === 0) {
                    // ERP tiene URL de WordPress, comparar nombres completos
                    $son_diferentes = ($nombre_imagen_erp !== $imagen_wp);
                } else {
                    // ERP tiene archivo local, comparar solo nombres base sin extensión
                    $nombre_erp_limpio = pathinfo($nombre_imagen_erp, PATHINFO_FILENAME);
                    $nombre_wp_limpio = pathinfo($imagen_wp, PATHINFO_FILENAME);
                    $son_diferentes = ($nombre_erp_limpio !== $nombre_wp_limpio);
                }
            }
            
            echo json_encode([
                'success' => true,
                'codigo_producto' => $codigo,
                'codigo' => $codigo, // Para compatibilidad con JavaScript
                'imagen_erp' => [
                    'nombre' => $nombre_imagen_erp,
                    'url' => $url_imagen_erp,
                    'existe' => !empty($imagen_erp) && (strpos($imagen_erp, 'http') === 0 || file_exists($imagen_erp))
                ],
                'imagen_wp' => [
                    'nombre' => $imagen_wp,
                    'url' => $url_imagen_wp,
                    'existe' => !empty($imagen_wp)
                ],
                'producto_existe_wp' => $wp_info && !empty($wp_info['existe']) && $wp_info['existe'] > 0,
                'son_diferentes' => $son_diferentes,
                // NUEVA INFORMACIÓN PARA DETECTAR VARIANTES
                'es_variante' => $wp_info_directo['encontrado'] ? $wp_info_directo['es_variante'] : false,
                'parent_id' => $wp_info_directo['encontrado'] ? $wp_info_directo['parent_id'] : null,
                'variation_id' => ($wp_info_directo['encontrado'] && $wp_info_directo['es_variante']) ? $wp_info_directo['id'] : null
            ]);
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'obtener_imagen_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        
        if (empty($parent_id)) {
            echo json_encode(['success' => false, 'message' => 'ID del producto padre requerido']);
            break;
        }
        
        try {
            // Obtener imagen principal del producto padre desde WordPress
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
            $result = executeWPCurl($ch);
            
            if ($result['success']) {
                $producto_padre = json_decode($result['response'], true);
                $images = $producto_padre['images'] ?? [];
                
                if (!empty($images)) {
                    // La primera imagen es la principal
                    $imagen_principal = $images[0];
                    echo json_encode([
                        'success' => true,
                        'imagen_url' => $imagen_principal['src'],
                        'imagen_id' => $imagen_principal['id'],
                        'imagen_alt' => $imagen_principal['alt'] ?? ''
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El producto padre no tiene imágenes'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al conectar con WordPress: ' . $result['error']
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'obtener_galeria_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        
        if (empty($parent_id)) {
            echo json_encode(['success' => false, 'message' => 'ID del producto padre requerido']);
            break;
        }
        
        try {
            // Obtener galería completa del producto padre desde WordPress
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
            $result = executeWPCurl($ch);
            
            if ($result['success']) {
                $producto_padre = json_decode($result['response'], true);
                $images = $producto_padre['images'] ?? [];
                
                if (!empty($images)) {
                    $galeria = [];
                    foreach ($images as $imagen) {
                        $galeria[] = [
                            'id' => $imagen['id'],
                            'url' => $imagen['src'],
                            'alt' => $imagen['alt'] ?? '',
                            'name' => $imagen['name'] ?? ''
                        ];
                    }
                    
                    echo json_encode([
                        'success' => true,
                        'imagenes' => $galeria,
                        'total' => count($galeria)
                    ]);
                } else {
                    echo json_encode([
                        'success' => false, 
                        'message' => 'El producto padre no tiene galería de imágenes'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al conectar con WordPress: ' . $result['error']
                ]);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'subir_imagen_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        
        if (empty($parent_id)) {
            echo json_encode(['success' => false, 'message' => 'ID del producto padre requerido']);
            break;
        }
        
        if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'message' => 'No se recibió imagen o hay error en la subida']);
            break;
        }
        
        try {
            // Validar tipo de archivo
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            if (!in_array($_FILES['imagen']['type'], $allowed_types)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de archivo no válido. Solo se permiten JPG, PNG y WebP']);
                break;
            }
            
            // Generar nombre único y mover archivo
            $ext = pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
            $nuevo_nombre = round(microtime(true)) . '.' . $ext;
            $ruta_destino = "../files/productos/" . $nuevo_nombre;
            
            if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $ruta_destino)) {
                $ruta_absoluta = realpath($ruta_destino);
                
                // PASO 1: Obtener imagen principal actual antes de cambiarla
                $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
                $result = executeWPCurl($ch);
                
                $imagen_principal_anterior = null;
                if ($result['success']) {
                    $producto_padre = json_decode($result['response'], true);
                    $images = $producto_padre['images'] ?? [];
                    if (!empty($images)) {
                        $imagen_principal_anterior = $images[0]['id']; // ID de la imagen principal actual
                    }
                }
                
                // PASO 2: Subir nueva imagen via API
                $idAttachment = $productos->subirImagenViaAPI($ruta_absoluta);
                if (strpos($idAttachment, 'ERROR') === 0) {
                    echo json_encode(['success' => false, 'message' => $idAttachment]);
                    break;
                }
                
                // PASO 3: Reemplazar solo la imagen principal, conservar galería
                $nuevas_imagenes = [];
                
                // Nueva imagen como principal
                $nuevas_imagenes[] = ['id' => $idAttachment];
                
                // Conservar imágenes de galería (saltarse solo la principal anterior)
                if ($result['success']) {
                    $producto_padre = json_decode($result['response'], true);
                    $images = $producto_padre['images'] ?? [];
                    
                    foreach ($images as $index => $imagen) {
                        // Conservar todas excepto la imagen principal anterior (index 0)
                        if ($index > 0 && $imagen['id'] != $idAttachment) {
                            $nuevas_imagenes[] = ['id' => $imagen['id']];
                        }
                    }
                }
                
                $datosProducto = [
                    'images' => $nuevas_imagenes
                ];
                
                $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_POSTFIELDS => json_encode($datosProducto)
                ]);
                
                $result = executeWPCurl($ch);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imagen principal reemplazada correctamente (imagen anterior eliminada)',
                        'attachment_id' => $idAttachment,
                        'imagen_anterior_eliminada' => $imagen_principal_anterior
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar imagen en WordPress']);
                }
                
                // Limpiar archivo temporal
                if (file_exists($ruta_destino)) {
                    unlink($ruta_destino);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al mover archivo subido']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'eliminar_imagenes_galeria_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        $imagenes_ids = $_POST['imagenes_ids'] ?? [];
        
        if (empty($parent_id)) {
            echo json_encode(['success' => false, 'message' => 'ID del producto padre requerido']);
            break;
        }
        
        if (empty($imagenes_ids) || !is_array($imagenes_ids)) {
            echo json_encode(['success' => false, 'message' => 'IDs de imágenes requeridos']);
            break;
        }
        
        try {
            // Obtener imágenes actuales del producto padre
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
            $result = executeWPCurl($ch);
            
            if (!$result['success']) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener producto de WordPress']);
                break;
            }
            
            $producto_padre = json_decode($result['response'], true);
            $images = $producto_padre['images'] ?? [];
            
            if (empty($images)) {
                echo json_encode(['success' => false, 'message' => 'El producto no tiene imágenes']);
                break;
            }
            
            // Filtrar las imágenes, eliminando las seleccionadas
            $imagenes_filtradas = [];
            $eliminadas = 0;
            
            foreach ($images as $imagen) {
                if (!in_array($imagen['id'], $imagenes_ids)) {
                    $imagenes_filtradas[] = ['id' => $imagen['id']];
                } else {
                    $eliminadas++;
                }
            }
            
            // Actualizar producto con la lista filtrada de imágenes
            $datosProducto = [
                'images' => $imagenes_filtradas
            ];
            
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST => 'PUT',
                CURLOPT_POSTFIELDS => json_encode($datosProducto)
            ]);
            
            $result = executeWPCurl($ch);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Imágenes eliminadas exitosamente',
                    'eliminadas' => $eliminadas,
                    'restantes' => count($imagenes_filtradas)
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar producto en WordPress']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    case 'subir_imagenes_galeria_padre':
        $parent_id = $_POST['parent_id'] ?? '';
        
        if (empty($parent_id)) {
            echo json_encode(['success' => false, 'message' => 'ID del producto padre requerido']);
            break;
        }
        
        if (!isset($_FILES['imagenes']) || empty($_FILES['imagenes']['name'][0])) {
            echo json_encode(['success' => false, 'message' => 'No se recibieron imágenes']);
            break;
        }
        
        try {
            $archivos = $_FILES['imagenes'];
            $total_archivos = count($archivos['name']);
            
            // Validar todos los archivos primero
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
            for ($i = 0; $i < $total_archivos; $i++) {
                if ($archivos['error'][$i] !== UPLOAD_ERR_OK) {
                    echo json_encode(['success' => false, 'message' => 'Error en la subida del archivo ' . ($i + 1)]);
                    break 2;
                }
                if (!in_array($archivos['type'][$i], $allowed_types)) {
                    echo json_encode(['success' => false, 'message' => 'Tipo de archivo no válido en imagen ' . ($i + 1) . '. Solo se permiten JPG, PNG y WebP']);
                    break 2;
                }
            }
            
            // Obtener imágenes actuales del producto padre
            $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
            $result = executeWPCurl($ch);
            
            if (!$result['success']) {
                echo json_encode(['success' => false, 'message' => 'Error al obtener producto padre']);
                break;
            }
            
            $producto_padre = json_decode($result['response'], true);
            $imagenes_actuales = $producto_padre['images'] ?? [];
            
            // Subir cada archivo y agregarlo a la galería
            $imagenes_subidas = [];
            $errores = 0;
            
            for ($i = 0; $i < $total_archivos; $i++) {
                try {
                    // Generar nombre único
                    $ext = pathinfo($archivos["name"][$i], PATHINFO_EXTENSION);
                    $nuevo_nombre = round(microtime(true) + $i) . '.' . $ext;
                    $ruta_destino = "../files/productos/" . $nuevo_nombre;
                    
                    if (move_uploaded_file($archivos["tmp_name"][$i], $ruta_destino)) {
                        $ruta_absoluta = realpath($ruta_destino);
                        
                        // Subir imagen a WordPress Media Library
                        $idAttachment = $productos->subirImagenViaAPI($ruta_absoluta);
                        
                        if (strpos($idAttachment, 'ERROR') !== 0) {
                            $imagenes_subidas[] = ['id' => $idAttachment];
                        } else {
                            $errores++;
                        }
                        
                        // Limpiar archivo temporal
                        if (file_exists($ruta_destino)) {
                            unlink($ruta_destino);
                        }
                    } else {
                        $errores++;
                    }
                } catch (Exception $e) {
                    $errores++;
                }
            }
            
            if (!empty($imagenes_subidas)) {
                // Combinar imágenes actuales con las nuevas
                $todas_las_imagenes = array_merge($imagenes_actuales, $imagenes_subidas);
                
                // Actualizar producto con todas las imágenes
                $datosProducto = [
                    'images' => $todas_las_imagenes
                ];
                
                $ch = setupWPCurl(WP_URL . '/wp-json/wc/v3/products/' . $parent_id);
                curl_setopt_array($ch, [
                    CURLOPT_CUSTOMREQUEST => 'PUT',
                    CURLOPT_POSTFIELDS => json_encode($datosProducto)
                ]);
                
                $result = executeWPCurl($ch);
                
                if ($result['success']) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Imágenes agregadas a la galería exitosamente',
                        'subidas' => count($imagenes_subidas),
                        'errores' => $errores,
                        'total' => count($todas_las_imagenes)
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Error al actualizar galería en WordPress']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo subir ninguna imagen']);
            }
            
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Operación no válida']);
        break;
}
?>