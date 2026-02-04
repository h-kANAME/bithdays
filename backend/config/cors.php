<?php
/**
 * Configuración de CORS
 * Maneja los headers de Cross-Origin Resource Sharing
 */

require_once __DIR__ . '/environment.php';

class CorsHandler {
    /**
     * Configurar headers CORS
     */
    public static function handle() {
        // Solo aplicar CORS si es una petición HTTP (no CLI)
        if (php_sapi_name() === 'cli') {
            return;
        }

        $allowedOrigins = Environment::getAllowedOrigins();
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';

        // Verificar si el origen está permitido
        if (in_array($origin, $allowedOrigins)) {
            header("Access-Control-Allow-Origin: $origin");
        } elseif (Environment::isDevelopment()) {
            // En desarrollo, permitir cualquier origen
            header("Access-Control-Allow-Origin: *");
        }

        // Headers permitidos
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Max-Age: 3600");

        // Manejar preflight requests
        if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Configurar headers de seguridad adicionales
     */
    public static function setSecurityHeaders() {
        // Solo aplicar headers HTTP si no es CLI
        if (php_sapi_name() === 'cli') {
            return;
        }

        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: DENY");
        header("X-XSS-Protection: 1; mode=block");

        if (!Environment::isDevelopment()) {
            header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
        }
    }

    /**
     * Configurar headers de respuesta JSON
     */
    public static function setJsonHeaders() {
        // Solo aplicar headers HTTP si no es CLI
        if (php_sapi_name() === 'cli') {
            return;
        }

        header("Content-Type: application/json; charset=utf-8");
    }
}

// Aplicar CORS automáticamente cuando se incluya este archivo
CorsHandler::handle();
CorsHandler::setSecurityHeaders();
