<?php
/**
 * Endpoint: Test Email
 * GET /api/cron/test-email
 * Envía un email de prueba
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../utils/EmailService.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

CorsHandler::setJsonHeaders();

// Requerir autenticación para este endpoint
$payload = JWTHandler::requireAuth();

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido',
        'code' => 'METHOD_NOT_ALLOWED'
    ]);
    exit();
}

try {
    $emailService = new EmailService();

    // Verificar configuración
    $config = $emailService->getConfig();

    if (!$config['configured']) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'EmailJS no está configurado correctamente',
            'code' => 'EMAILJS_NOT_CONFIGURED',
            'config' => $config
        ]);
        exit();
    }

    // Obtener email de prueba del query param
    $testEmail = $_GET['email'] ?? null;

    // Enviar email de prueba
    $result = $emailService->sendTestEmail($testEmail);

    http_response_code($result['success'] ? 200 : 500);
    echo json_encode($result);

} catch (Exception $e) {
    error_log("Test email error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al enviar email de prueba',
        'code' => 'SERVER_ERROR',
        'message' => $e->getMessage()
    ]);
}
