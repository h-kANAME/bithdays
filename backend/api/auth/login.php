<?php
/**
 * Endpoint: Login
 * POST /api/auth/login
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
    error_log("Login: Inicio del proceso");

    // Obtener datos del request
    $input = json_decode(file_get_contents('php://input'), true);
    error_log("Login: Input recibido: " . json_encode($input));

    $usuario = $input['usuario'] ?? '';
    $password = $input['password'] ?? '';

    // Validar campos requeridos
    if (empty($usuario) || empty($password)) {
        error_log("Login: Campos faltantes");
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Usuario y contraseña son requeridos',
            'code' => 'MISSING_FIELDS'
        ]);
        exit();
    }

    error_log("Login: Intentando autenticar usuario: $usuario");

    // Autenticar
    $adminModel = new AdminModel();
    $authResult = $adminModel->authenticate($usuario, $password);

    error_log("Login: Resultado de autenticación: " . json_encode($authResult));

    if (!$authResult['success']) {
        error_log("Login: Autenticación fallida");
        http_response_code(401);
        echo json_encode($authResult);
        exit();
    }

    error_log("Login: Generando token JWT");

    // Generar token JWT
    $token = JWTHandler::generateToken(
        $authResult['data']['id'],
        $authResult['data']['usuario']
    );

    error_log("Login: Token generado: " . substr($token, 0, 30) . "...");

    // Guardar token en la base de datos
    $adminModel->saveAccessToken($authResult['data']['id'], $token);

    error_log("Login: Token guardado en BD");

    // Respuesta exitosa
    $response = [
        'success' => true,
        'message' => 'Autenticación exitosa',
        'data' => [
            'token' => $token,
            'usuario' => $authResult['data']  // Devolver el objeto completo
        ]
    ];

    error_log("Login: Enviando respuesta exitosa: " . json_encode($response));

    http_response_code(200);
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Login error: " . $e->getMessage());
    error_log("Login error trace: " . $e->getTraceAsString());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'code' => 'SERVER_ERROR',
        'debug' => $e->getMessage()  // Solo para debugging, remover en producción
    ]);
}
