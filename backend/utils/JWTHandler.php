<?php
/**
 * Manejador de JWT (JSON Web Tokens)
 * Implementación simple de JWT sin dependencias externas
 */

class JWTHandler {
    private static $secret;
    private static $algorithm = 'HS256';

    /**
     * Inicializar secret desde variables de entorno
     */
    private static function init() {
        if (self::$secret === null) {
            self::$secret = getenv('JWT_SECRET') ?: 'default_secret_key_change_in_production_min_32_chars';

            if (strlen(self::$secret) < 32) {
                throw new Exception('JWT_SECRET debe tener al menos 32 caracteres');
            }
        }
    }

    /**
     * Codificar en Base64URL
     */
    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * Decodificar desde Base64URL
     */
    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }

    /**
     * Generar token JWT
     */
    public static function generateToken($userId, $usuario) {
        self::init();

        $expiry = getenv('ACCESS_TOKEN_EXPIRY') ?: '30d';
        $expirySeconds = self::parseExpiry($expiry);

        $header = [
            'alg' => self::$algorithm,
            'typ' => 'JWT'
        ];

        $payload = [
            'iss' => 'birthdays-system',
            'sub' => $userId,
            'usuario' => $usuario,
            'iat' => time(),
            'exp' => time() + $expirySeconds
        ];

        $headerEncoded = self::base64UrlEncode(json_encode($header));
        $payloadEncoded = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureEncoded = self::base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Verificar y decodificar token JWT
     */
    public static function verifyToken($token) {
        self::init();

        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        list($headerEncoded, $payloadEncoded, $signatureEncoded) = $parts;

        // Verificar firma
        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", self::$secret, true);
        $signatureCheck = self::base64UrlEncode($signature);

        if ($signatureCheck !== $signatureEncoded) {
            return false;
        }

        // Decodificar payload
        $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);

        // Verificar expiración
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            return false;
        }

        return $payload;
    }

    /**
     * Obtener token desde headers de autorización
     */
    public static function getTokenFromHeaders() {
        // Intentar obtener headers de diferentes maneras (compatibilidad con diferentes servidores)
        $headers = null;

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
        } elseif (function_exists('getallheaders')) {
            $headers = getallheaders();
        } else {
            // Fallback: construir headers desde $_SERVER
            $headers = [];
            foreach ($_SERVER as $key => $value) {
                if (substr($key, 0, 5) === 'HTTP_') {
                    $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                    $headers[$header] = $value;
                }
            }
        }

        if (isset($headers['Authorization'])) {
            $auth = $headers['Authorization'];
            if (preg_match('/Bearer\s+(.*)$/i', $auth, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Parsear tiempo de expiración (ej: "30d", "24h", "3600s")
     */
    private static function parseExpiry($expiry) {
        if (is_numeric($expiry)) {
            return (int)$expiry;
        }

        preg_match('/^(\d+)([dhms])$/', $expiry, $matches);

        if (count($matches) !== 3) {
            return 2592000; // 30 días por defecto
        }

        $value = (int)$matches[1];
        $unit = $matches[2];

        switch ($unit) {
            case 'd':
                return $value * 86400;
            case 'h':
                return $value * 3600;
            case 'm':
                return $value * 60;
            case 's':
                return $value;
            default:
                return 2592000;
        }
    }

    /**
     * Middleware para proteger endpoints
     */
    public static function requireAuth() {
        $token = self::getTokenFromHeaders();

        if (!$token) {
            error_log("JWTHandler: Token no encontrado en headers");
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token de autenticación requerido',
                'code' => 'AUTH_TOKEN_MISSING'
            ]);
            exit();
        }

        $payload = self::verifyToken($token);

        if (!$payload) {
            error_log("JWTHandler: Token inválido o expirado. Token: " . substr($token, 0, 50) . "...");
            http_response_code(401);
            echo json_encode([
                'success' => false,
                'error' => 'Token inválido o expirado',
                'code' => 'AUTH_TOKEN_INVALID'
            ]);
            exit();
        }

        return $payload;
    }
}
