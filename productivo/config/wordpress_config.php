<?php
/**
 * Configuración para conexión con WordPress
 * Cambia automáticamente entre localhost y servidor
 */

// Evitar redeclaraciones si el archivo ya fue incluido
if (defined('WP_CONFIG_LOADED')) {
    return;
}
define('WP_CONFIG_LOADED', true);

// Detectar el entorno actual
$host = $_SERVER['HTTP_HOST'] ?? '';
$is_localhost = in_array($host, ['localhost', '127.0.0.1', '::1']) || 
                strpos($host, 'localhost') !== false ||
                strpos($host, '.local') !== false;
$is_pruebas = strpos($host, 'pruebas.fervicom.com') !== false;
$is_produccion = $host === 'www.fervicom.com' || $host === 'fervicom.com' || $host === 'erp.fervicom.com';

// Configuración según el entorno
if ($is_localhost) {
    // Configuración para localhost
    define('WP_URL', 'https://www.fervicom.com');
    define('WP_USER', 'fervicom');
    define('WP_PASSWORD', 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC');
    define('USE_WP_LOAD', false); // No usar wp-load.php desde localhost
} elseif ($is_pruebas) {
    // Configuración para servidor de pruebas
    define('WP_URL', 'https://www.fervicom.com');
    define('WP_USER', 'fervicom');
    define('WP_PASSWORD', 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC');
    define('USE_WP_LOAD', false); // No usar wp-load.php desde servidor de pruebas
} else {
    // Configuración para servidor de producción
    define('WP_URL', 'https://www.fervicom.com');
    define('WP_USER', 'fervicom');
    define('WP_PASSWORD', 'KNRJ kYEm Bau2 KSG7 CEHJ AhqC');
    define('USE_WP_LOAD', true); // Usar wp-load.php solo si estamos en el mismo servidor que WordPress
}

// Configuración de cURL para conexiones remotas
define('CURL_TIMEOUT', 30);
define('CURL_CONNECT_TIMEOUT', 10);

// Función helper para obtener headers de autenticación
if (!function_exists('getWPAuthHeaders')) {
    function getWPAuthHeaders() {
        return [
            'Authorization: Basic ' . base64_encode(WP_USER . ':' . WP_PASSWORD),
            'Content-Type: application/json'
        ];
    }
}

// Función helper para configurar cURL
if (!function_exists('setupWPCurl')) {
    function setupWPCurl($url) {
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => CURL_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => CURL_CONNECT_TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => false, // Para evitar problemas con SSL en algunos servidores
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => getWPAuthHeaders()
        ]);
        return $ch;
    }
}

// Función helper para ejecutar peticiones cURL con manejo de errores
if (!function_exists('executeWPCurl')) {
    function executeWPCurl($ch) {
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        $curl_info = curl_getinfo($ch);
        
        // Log para debugging (opcional)
        error_log("WordPress API Request - URL: " . $curl_info['url'] . " - HTTP Code: " . $http_code);
        
        if ($response === false) {
            curl_close($ch);
            return [
                'success' => false,
                'error' => 'ERROR: cURL error - ' . $curl_error . ' (Code: ' . curl_errno($ch) . ')',
                'http_code' => 0,
                'curl_info' => $curl_info
            ];
        }
        
        if (!in_array($http_code, [200, 201])) {
            curl_close($ch);
            return [
                'success' => false,
                'error' => 'ERROR: HTTP ' . $http_code . ' - ' . substr($response, 0, 500),
                'http_code' => $http_code,
                'response' => $response,
                'curl_info' => $curl_info
            ];
        }
        
        curl_close($ch);
        return [
            'success' => true,
            'response' => $response,
            'http_code' => $http_code
        ];
    }
}
?>