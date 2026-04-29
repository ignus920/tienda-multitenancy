<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once "../config/Conexion.php";
require_once "../modelos/Usuario.php";

function sendResponse($status, $data = null, $message = null, $code = 200) {
    http_response_code($code);

    // Generar bloque de texto personalizado
    $data_txt = 'No hay registros para consultar';
    if (!empty($data)) {
        $lineas = ["Sus guías de los últimos 15 días:\n"];
        foreach ($data as $item) {
            $lineas[] = "{$item['fecha_formateada']} | {$item['empresa_transportadora']} | Guía # | {$item['guia']}\n{$item['tracking_url']}";
        }
        $data_txt = implode("\n\n", $lineas);
    }

    echo json_encode([
        'status' => $status,
        'timestamp' => date('Y-m-d H:i:s'),
        'data' => $data ?? [],
        'message' => $message,
        'data_txt' => $data_txt
    ]);
    exit;
}

function validarToken($token) {
    if (empty($token)) sendResponse('error', [], 'Token no proporcionado', 401);
    $credentials = base64_decode($token);
    if (!$credentials || strpos($credentials, ':') === false) sendResponse('error', [], 'Token con formato inválido', 401);

    list($login, $clave) = explode(':', $credentials);
    if (empty($login) || empty($clave)) sendResponse('error', [], 'Credenciales incompletas', 401);

    $usuario = new Usuario();
    $clavehash = hash("SHA256", $clave);
    $result = $usuario->verificar($login, $clavehash);

    if (!$result || $result->num_rows === 0) sendResponse('error', [], 'Credenciales inválidas', 401);
    return true;
}

function getTrackingUrl($transportadora, $guia) {
    $urls = [
        'COORDINADORA' => "https://coordinadora.com/rastreo/rastreo-de-guia/detalle-de-rastreo-de-guia/?guia={$guia}",
        'TCC' => "https://www.tcc.com.co/rastreo?guia={$guia}",
        'SERVIENTREGA' => "https://www.servientrega.com/rastreo-envio?guia={$guia}",
        'ENVIA' => "https://envia.co/rastreo-de-guias/?guia={$guia}",
        'INTERRAPIDISIMO' => "https://www.interrapidisimo.com/rastreo-de-envios/?guia={$guia}"
    ];
    return $urls[strtoupper($transportadora)] ?? null;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendResponse('error', [], 'Método no permitido', 405);
    }

    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData);
    if (json_last_error() !== JSON_ERROR_NONE) sendResponse('error', [], 'JSON inválido', 400);
    if (empty($data->token)) sendResponse('error', [], 'Token no proporcionado', 401);
    validarToken($data->token);
    if (empty($data->documento)) sendResponse('error', [], 'Número de documento no proporcionado', 400);

    $numeroDocumento = limpiarCadena($data->documento);
    $fechaLimite = date('Y-m-d', strtotime('-15 days'));

    $sql = "SELECT DISTINCT g.guia, g.empresa_transportadora, g.fecha_reg
            FROM guias g
            INNER JOIN v_orden_p o ON o.id_op = g.id_op
            INNER JOIN v_pedidos p ON p.id_ped = o.id_ped
            INNER JOIN c_cliente c ON c.idcliente = p.cliente
            WHERE c.num_ident = ?
            AND g.empresa_transportadora <> 'cliente'
            AND o.estado IN (19, 20)
            AND DATE(g.fecha_reg) >= ?";

    $stmt = $conexion->prepare($sql);
    if (!$stmt) sendResponse('error', [], 'Error preparando consulta', 500);

    $stmt->bind_param('ss', $numeroDocumento, $fechaLimite);
    if (!$stmt->execute()) sendResponse('error', [], 'Error ejecutando consulta', 500);

    $result = $stmt->get_result();
    $guias = [];
    $meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

    while ($row = $result->fetch_assoc()) {
        $fecha = new DateTime($row['fecha_reg']);
        $dia = $fecha->format('d');
        $mes = ucfirst($meses[(int)$fecha->format('m') - 1]);
        $row['fecha_formateada'] = "Enviado el {$dia} de {$mes}";
        $row['tracking_url'] = getTrackingUrl($row['empresa_transportadora'], $row['guia']);
        $guias[] = $row;
    }

    $stmt->close();

    sendResponse(
        'success',
        $guias,
        empty($guias) ? 'No se encontraron guías dentro de los últimos 15 días' : 'Consulta realizada con éxito',
        200
    );

} catch (Exception $e) {
    error_log("Error en API guias: " . $e->getMessage());
    sendResponse('error', [], 'Error interno del servidor', 500);
}
