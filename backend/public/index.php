<?php
/**
 * Router Principal
 * Maneja el enrutamiento de todas las peticiones API
 */

// Cargar variables de entorno desde .env
require_once __DIR__ . '/../config/load_env.php';

// Configurar timezone para Buenos Aires
date_default_timezone_set('America/Argentina/Buenos_Aires');

require_once __DIR__ . '/../config/cors.php';

// Obtener la ruta solicitada
$request_uri = $_SERVER['REQUEST_URI'];

// Extraer solo el path (sin query string)
$request_path = parse_url($request_uri, PHP_URL_PATH);

// Obtener el directorio base del script (para manejar subdirectorios)
$script_name = $_SERVER['SCRIPT_NAME'];
$base_path = str_replace('/index.php', '', $script_name);

// Remover el base path del request path
if (!empty($base_path) && strpos($request_path, $base_path) === 0) {
    $request_path = substr($request_path, strlen($base_path));
}

$request_path = trim($request_path, '/');

// Dividir el path en segmentos
$segments = explode('/', $request_path);

// Verificar que comience con 'api'
if (empty($segments[0]) || $segments[0] !== 'api') {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Endpoint no encontrado',
        'code' => 'ENDPOINT_NOT_FOUND'
    ]);
    exit();
}

// Obtener el módulo (auth, usuarios, destinatarios, cron)
$module = $segments[1] ?? '';

// Obtener el action/id
$action = $segments[2] ?? '';

// Mapear rutas a archivos
$routes = [
    // Autenticación
    'auth' => [
        'login' => __DIR__ . '/../api/auth/login.php',
        'verify-token' => __DIR__ . '/../api/auth/verify-token.php',
        'logout' => __DIR__ . '/../api/auth/logout.php',
        'test-token' => __DIR__ . '/../api/auth/test-token.php',
        'test-login' => __DIR__ . '/../api/auth/test-login.php'
    ],

    // Usuarios
    'usuarios' => [
        '' => __DIR__ . '/../api/usuarios/index.php',
        'default' => __DIR__ . '/../api/usuarios/index.php'
    ],

    // Destinatarios
    'destinatarios' => [
        '' => __DIR__ . '/../api/destinatarios/index.php',
        'default' => __DIR__ . '/../api/destinatarios/index.php'
    ],

    // CRON
    'cron' => [
        'check-birthdays' => __DIR__ . '/../api/cron/check-birthdays.php',
        'test-email' => __DIR__ . '/../api/cron/test-email.php'
    ],

    // Debug (ELIMINAR en producción después de diagnosticar)
    'debug' => [
        'check-auth' => __DIR__ . '/../api/debug/check-auth.php'
    ]
];

// Verificar si el módulo existe
if (!isset($routes[$module])) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Módulo no encontrado',
        'code' => 'MODULE_NOT_FOUND',
        'module' => $module
    ]);
    exit();
}

// Determinar el archivo a incluir
$file = null;

if (isset($routes[$module][$action])) {
    $file = $routes[$module][$action];
} elseif (isset($routes[$module]['default'])) {
    $file = $routes[$module]['default'];
} elseif (isset($routes[$module][''])) {
    $file = $routes[$module][''];
}

// Si no se encontró el archivo
if (!$file || !file_exists($file)) {
    http_response_code(404);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => 'Endpoint no encontrado',
        'code' => 'ENDPOINT_NOT_FOUND',
        'module' => $module,
        'action' => $action,
        'file' => $file,
        'file_exists' => $file ? file_exists($file) : false,
        'segments' => $segments
    ]);
    exit();
}

// Incluir el archivo correspondiente
require_once $file;
