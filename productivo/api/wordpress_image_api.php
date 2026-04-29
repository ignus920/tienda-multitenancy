<?php
/**
 * API REST para actualización de imágenes de productos en WordPress
 * Conexión entre ERP Fervicom y WordPress/WooCommerce
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Manejo de preflight OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once "../config/Conexion.php";
require_once "../modelos/Productos.php";

class WordPressImageAPI {
    private $productos;
    private $api_key;
    
    public function __construct() {
        $this->productos = new Productos();
        $this->api_key = 'fervicom_wp_api_2025_secure'; // Cambiar por una clave más segura
    }
    
    /**
     * Valida la autenticación de la API
     */
    private function validateAuth() {
        $headers = getallheaders();
        $provided_key = isset($headers['X-API-Key']) ? $headers['X-API-Key'] : '';
        
        if ($provided_key !== $this->api_key) {
            $this->sendError('Acceso no autorizado', 401);
        }
    }
    
    /**
     * Envía respuesta de error
     */
    private function sendError($message, $code = 400) {
        http_response_code($code);
        echo json_encode([
            'success' => false,
            'error' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    /**
     * Envía respuesta de éxito
     */
    private function sendSuccess($data, $message = 'Operación exitosa') {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit();
    }
    
    /**
     * Actualizar imagen de producto por código SKU
     */
    public function updateProductImage() {
        $this->validateAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        // Validar datos requeridos
        if (!isset($input['codigo']) || !isset($input['imagen_path'])) {
            $this->sendError('Faltan datos requeridos: codigo, imagen_path');
        }
        
        $codigo = trim($input['codigo']);
        $imagen_path = trim($input['imagen_path']);
        
        // Validar que el archivo existe
        if (!file_exists($imagen_path)) {
            $this->sendError('Archivo de imagen no encontrado: ' . $imagen_path);
        }
        
        // Validar formato de imagen
        $allowed_types = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower(pathinfo($imagen_path, PATHINFO_EXTENSION));
        
        if (!in_array($extension, $allowed_types)) {
            $this->sendError('Formato de imagen no válido. Permitidos: ' . implode(', ', $allowed_types));
        }
        
        try {
            // Usar el método existente de la clase Productos
            $resultado = $this->productos->asignarImagenPrincipal($codigo, $imagen_path);
            
            if (strpos($resultado, 'ERROR') === 0) {
                $this->sendError($resultado);
            } else {
                // Actualizar la imagen en la base de datos del ERP
                $producto_info = $this->productos->mostrar('codigo', $codigo);
                if ($producto_info) {
                    $this->productos->editarImagenProducto($producto_info['id'], $resultado);
                }
                
                $this->sendSuccess([
                    'codigo' => $codigo,
                    'wp_image_id' => $resultado,
                    'imagen_path' => $imagen_path
                ], 'Imagen actualizada exitosamente en WordPress');
            }
            
        } catch (Exception $e) {
            $this->sendError('Error interno: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Actualizar múltiples imágenes por lote
     */
    public function updateMultipleImages() {
        $this->validateAuth();
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($input['productos']) || !is_array($input['productos'])) {
            $this->sendError('Se requiere array de productos con formato: [{codigo, imagen_path}, ...]');
        }
        
        $resultados = [];
        $exitosos = 0;
        $errores = 0;
        
        foreach ($input['productos'] as $producto) {
            if (!isset($producto['codigo']) || !isset($producto['imagen_path'])) {
                $resultados[] = [
                    'codigo' => $producto['codigo'] ?? 'N/A',
                    'success' => false,
                    'error' => 'Faltan datos requeridos'
                ];
                $errores++;
                continue;
            }
            
            $codigo = trim($producto['codigo']);
            $imagen_path = trim($producto['imagen_path']);
            
            try {
                if (!file_exists($imagen_path)) {
                    $resultados[] = [
                        'codigo' => $codigo,
                        'success' => false,
                        'error' => 'Archivo no encontrado'
                    ];
                    $errores++;
                    continue;
                }
                
                $resultado = $this->productos->asignarImagenPrincipal($codigo, $imagen_path);
                
                if (strpos($resultado, 'ERROR') === 0) {
                    $resultados[] = [
                        'codigo' => $codigo,
                        'success' => false,
                        'error' => $resultado
                    ];
                    $errores++;
                } else {
                    // Actualizar en la base de datos del ERP
                    $producto_info = $this->productos->mostrar('codigo', $codigo);
                    if ($producto_info) {
                        $this->productos->editarImagenProducto($producto_info['id'], $resultado);
                    }
                    
                    $resultados[] = [
                        'codigo' => $codigo,
                        'success' => true,
                        'wp_image_id' => $resultado
                    ];
                    $exitosos++;
                }
                
            } catch (Exception $e) {
                $resultados[] = [
                    'codigo' => $codigo,
                    'success' => false,
                    'error' => 'Error interno: ' . $e->getMessage()
                ];
                $errores++;
            }
            
            // Pequeña pausa para evitar sobrecarga
            usleep(100000); // 0.1 segundos
        }
        
        $this->sendSuccess([
            'total_procesados' => count($input['productos']),
            'exitosos' => $exitosos,
            'errores' => $errores,
            'resultados' => $resultados
        ], "Procesamiento completado: $exitosos exitosos, $errores errores");
    }
    
    /**
     * Obtener información de producto por código
     */
    public function getProductInfo() {
        $this->validateAuth();
        
        $codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : '';
        
        if (empty($codigo)) {
            $this->sendError('Parámetro codigo requerido');
        }
        
        try {
            $producto = $this->productos->mostrar('codigo', $codigo);
            
            if (!$producto) {
                $this->sendError('Producto no encontrado', 404);
            }
            
            // Obtener información de WordPress si existe
            $wp_info = $this->productos->linkcodigo($codigo);
            
            $this->sendSuccess([
                'codigo' => $producto['codigo'],
                'descripcion' => $producto['descripcion'],
                'imagen_erp' => $producto['tximagen'],
                'precio1' => $producto['precio1'],
                'precio2' => $producto['precio2'],
                'precio3' => $producto['precio3'],
                'existencias' => $producto['existencias'],
                'estado' => $producto['estado'],
                'wordpress' => $wp_info
            ], 'Información del producto obtenida exitosamente');
            
        } catch (Exception $e) {
            $this->sendError('Error interno: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Sincronizar imágenes desde WordPress hacia el ERP
     */
    public function syncImagesFromWordPress() {
        $this->validateAuth();
        
        try {
            $resultado = $this->productos->ActualizarImagen();
            
            if ($resultado) {
                $this->sendSuccess([], 'Sincronización de imágenes desde WordPress completada');
            } else {
                $this->sendError('Error en la sincronización');
            }
            
        } catch (Exception $e) {
            $this->sendError('Error interno: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Endpoint de prueba de conectividad
     */
    public function testConnection() {
        $this->validateAuth();
        
        $this->sendSuccess([
            'server_time' => date('Y-m-d H:i:s'),
            'php_version' => PHP_VERSION,
            'api_version' => '1.0.0'
        ], 'Conexión exitosa con la API');
    }
}

// Router principal
$api = new WordPressImageAPI();
$method = $_SERVER['REQUEST_METHOD'];
$path = isset($_GET['action']) ? $_GET['action'] : '';

try {
    switch ($method) {
        case 'POST':
            switch ($path) {
                case 'update_image':
                    $api->updateProductImage();
                    break;
                case 'update_multiple':
                    $api->updateMultipleImages();
                    break;
                case 'sync_from_wp':
                    $api->syncImagesFromWordPress();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint no encontrado']);
            }
            break;
            
        case 'GET':
            switch ($path) {
                case 'product_info':
                    $api->getProductInfo();
                    break;
                case 'test':
                    $api->testConnection();
                    break;
                default:
                    http_response_code(404);
                    echo json_encode(['error' => 'Endpoint no encontrado']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método no permitido']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno del servidor',
        'message' => $e->getMessage()
    ]);
}
?>