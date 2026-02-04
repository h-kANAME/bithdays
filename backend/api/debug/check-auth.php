<?php
/**
 * Script de diagnóstico de autenticación
 * ELIMINAR después de depurar
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

CorsHandler::setJsonHeaders();

header('Content-Type: application/json');

$diagnostics = [];

// 1. Verificar JWT_SECRET
$jwtSecret = getenv('JWT_SECRET');
$diagnostics['jwt_secret_configured'] = !empty($jwtSecret);
$diagnostics['jwt_secret_length'] = strlen($jwtSecret ?: 'default_secret_key_change_in_production_min_32_chars');
$diagnostics['using_default_secret'] = empty($jwtSecret);

// 2. Verificar headers
$diagnostics['php_sapi'] = php_sapi_name();
$diagnostics['has_apache_request_headers'] = function_exists('apache_request_headers');
$diagnostics['has_getallheaders'] = function_exists('getallheaders');

// 3. Intentar obtener headers
try {
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    } elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
    } else {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }
    }

    $diagnostics['headers_available'] = true;
    $diagnostics['authorization_header_present'] = isset($headers['Authorization']);

    if (isset($headers['Authorization'])) {
        $diagnostics['authorization_header'] = substr($headers['Authorization'], 0, 20) . '...';

        // Intentar extraer token
        if (preg_match('/Bearer\s+(.*)$/i', $headers['Authorization'], $matches)) {
            $token = $matches[1];
            $diagnostics['token_extracted'] = true;
            $diagnostics['token_preview'] = substr($token, 0, 30) . '...';

            // Intentar verificar token
            $payload = JWTHandler::verifyToken($token);
            $diagnostics['token_valid'] = $payload !== false;

            if ($payload) {
                $diagnostics['token_payload'] = [
                    'iss' => $payload['iss'] ?? null,
                    'sub' => $payload['sub'] ?? null,
                    'usuario' => $payload['usuario'] ?? null,
                    'iat' => $payload['iat'] ?? null,
                    'exp' => $payload['exp'] ?? null
                ];
                $diagnostics['token_expired'] = isset($payload['exp']) && $payload['exp'] < time();
            } else {
                $diagnostics['token_error'] = 'Token inválido o expirado';
            }
        } else {
            $diagnostics['token_extracted'] = false;
            $diagnostics['error'] = 'No se pudo extraer token del header Authorization';
        }
    }
} catch (Exception $e) {
    $diagnostics['headers_error'] = $e->getMessage();
}

// 4. Verificar variables de entorno
$diagnostics['env_vars'] = [
    'APP_ENV' => getenv('APP_ENV') ?: 'not_set',
    'ACCESS_TOKEN_EXPIRY' => getenv('ACCESS_TOKEN_EXPIRY') ?: 'not_set'
];

echo json_encode([
    'success' => true,
    'diagnostics' => $diagnostics
], JSON_PRETTY_PRINT);
