<?php
/**
 * Endpoint: Verify Token
 * POST /api/auth/verify-token
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

CorsHandler::setJsonHeaders();

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido',
        'code' => 'METHOD_NOT_ALLOWED'
    ]);
    exit();
}

try {
    // Obtener token
    $token = JWTHandler::getTokenFromHeaders();

    if (!$token) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Token no proporcionado',
            'code' => 'TOKEN_MISSING'
        ]);
        exit();
    }

    // Verificar token
    $payload = JWTHandler::verifyToken($token);

    if (!$payload) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Token inválido o expirado',
            'code' => 'TOKEN_INVALID'
        ]);
        exit();
    }

    // Token válido
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Token válido',
        'data' => [
            'usuario' => $payload['usuario'],
            'exp' => $payload['exp']
        ]
    ]);

} catch (Exception $e) {
    error_log("Verify token error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'code' => 'SERVER_ERROR'
    ]);
}
