<?php
/**
 * Endpoint de prueba de token (ELIMINAR después de debugging)
 * GET /api/auth/test-token
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

CorsHandler::setJsonHeaders();

// Solo permitir GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
    exit();
}

$diagnostics = [];

// 1. Verificar JWT_SECRET
$jwtSecret = getenv('JWT_SECRET');
$diagnostics['jwt_secret_configured'] = !empty($jwtSecret);
$diagnostics['jwt_secret_length'] = strlen($jwtSecret ?: 'default_secret_key_change_in_production_min_32_chars');
$diagnostics['using_default_secret'] = empty($jwtSecret);

if (!empty($jwtSecret)) {
    // Mostrar primeros 8 caracteres para verificar
    $diagnostics['jwt_secret_preview'] = substr($jwtSecret, 0, 8) . '...';
}

// 2. Verificar headers
$diagnostics['php_sapi'] = php_sapi_name();
$diagnostics['has_apache_request_headers'] = function_exists('apache_request_headers');

// 3. Intentar obtener token
$token = JWTHandler::getTokenFromHeaders();

if ($token) {
    $diagnostics['token_found'] = true;
    $diagnostics['token_preview'] = substr($token, 0, 30) . '...';

    // Intentar verificar token
    $payload = JWTHandler::verifyToken($token);

    if ($payload) {
        $diagnostics['token_valid'] = true;
        $diagnostics['token_payload'] = [
            'sub' => $payload['sub'] ?? null,
            'usuario' => $payload['usuario'] ?? null,
            'iat' => $payload['iat'] ?? null,
            'exp' => $payload['exp'] ?? null
        ];

        if (isset($payload['exp'])) {
            $diagnostics['token_expired'] = $payload['exp'] < time();
            $diagnostics['token_expires_in'] = $payload['exp'] - time();
            $diagnostics['token_expires_at'] = date('Y-m-d H:i:s', $payload['exp']);
        }
    } else {
        $diagnostics['token_valid'] = false;
        $diagnostics['token_error'] = 'Firma inválida o token expirado';

        // Info adicional para debugging
        $diagnostics['current_time'] = time();
        $diagnostics['current_datetime'] = date('Y-m-d H:i:s');
    }
} else {
    $diagnostics['token_found'] = false;
    $diagnostics['error'] = 'Token no encontrado en headers';
}

// 4. Variables de entorno relevantes
$diagnostics['env'] = [
    'APP_ENV' => getenv('APP_ENV') ?: 'not_set',
    'ACCESS_TOKEN_EXPIRY' => getenv('ACCESS_TOKEN_EXPIRY') ?: 'not_set'
];

http_response_code(200);
echo json_encode([
    'success' => true,
    'diagnostics' => $diagnostics
], JSON_PRETTY_PRINT);
