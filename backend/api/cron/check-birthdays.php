<?php
/**
 * Endpoint: Check Birthdays
 * GET /api/cron/check-birthdays
 * Verifica cumpleaños del día y envía notificaciones
 */

// Cargar variables de entorno
require_once __DIR__ . '/../../config/load_env.php';

// Configurar timezone para Buenos Aires (IMPORTANTE para comparación de fechas)
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';
require_once __DIR__ . '/../../models/DestinatarioModel.php';
require_once __DIR__ . '/../../utils/EmailService.php';

CorsHandler::setJsonHeaders();

// Verificar método solo si es petición HTTP (no CLI)
if (php_sapi_name() !== 'cli') {
    if (!isset($_SERVER['REQUEST_METHOD']) || $_SERVER['REQUEST_METHOD'] !== 'GET') {
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido',
            'code' => 'METHOD_NOT_ALLOWED'
        ]);
        exit();
    }
}

try {
    error_log("=== CRON INICIO: Verificando cumpleaños para " . date('Y-m-d H:i:s') . " ===");
    
    // Obtener cumpleaños del día actual
    $usuariosCumpleanios = UsuarioModel::getBirthdaysToday();

    if (empty($usuariosCumpleanios)) {
        error_log("✗ NO HAY CUMPLEAÑOS - No se enviarán notificaciones");
        error_log("=== CRON FIN: Sin cumpleaños ===\n");
        
        // NO ENVIAR EMAILS - Solo informar al log
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'No hay cumpleaños hoy - No se enviaron notificaciones',
            'count' => 0,
            'emails_sent' => false,
            'fecha' => date('Y-m-d')
        ]);
        exit();
    }

    error_log("✓ CUMPLEAÑOS ENCONTRADOS: " . count($usuariosCumpleanios));
    
    // Obtener todos los destinatarios
    $destinatarios = DestinatarioModel::getAllRecipients();

    if (empty($destinatarios)) {
        error_log("✗ NO HAY DESTINATARIOS - No se pueden enviar notificaciones");
        error_log("=== CRON FIN: Sin destinatarios ===\n");
        
        http_response_code(200);
        echo json_encode([
            'success' => false,
            'message' => 'No hay destinatarios configurados - No se enviaron notificaciones',
            'count' => count($usuariosCumpleanios),
            'birthdays' => $usuariosCumpleanios,
            'emails_sent' => false,
            'error' => 'NO_RECIPIENTS'
        ]);
        exit();
    }

    error_log("✓ Preparando envío de notificaciones a " . count($destinatarios) . " destinatario(s)");
    
    // Enviar notificaciones por email
    $emailService = new EmailService();
    $result = $emailService->sendBirthdayNotification(
        $destinatarios,
        $usuariosCumpleanios
    );

    error_log("✓ Envío completado: " . json_encode($result));
    error_log("=== CRON FIN: Cumpleaños notificados ===\n");

    http_response_code($result['success'] ? 200 : 500);
    echo json_encode(array_merge($result, [
        'fecha' => date('Y-m-d'),
        'birthdays' => $usuariosCumpleanios,
        'emails_sent' => true
    ]));

} catch (Exception $e) {
    error_log("Check birthdays error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al verificar cumpleaños',
        'code' => 'SERVER_ERROR',
        'message' => $e->getMessage()
    ]);
}
