<?php
/**
 * Endpoint: Logout
 * POST /api/auth/logout
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/AdminModel.php';
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
    // Verificar autenticación
    $payload = JWTHandler::requireAuth();

    // Cerrar sesión
    $adminModel = new AdminModel();
    $result = $adminModel->logout($payload['sub']);

    if (!$result['success']) {
        http_response_code(500);
        echo json_encode($result);
        exit();
    }

    // Respuesta exitosa
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Sesión cerrada correctamente'
    ]);

} catch (Exception $e) {
    error_log("Logout error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'code' => 'SERVER_ERROR'
    ]);
}
