<?php
/**
 * Endpoint: Usuarios CRUD
 * GET    /api/usuarios       - Listar todos (con paginación y búsqueda)
 * GET    /api/usuarios/{id}  - Obtener uno
 * POST   /api/usuarios       - Crear nuevo
 * PUT    /api/usuarios/{id}  - Actualizar
 * DELETE /api/usuarios/{id}  - Eliminar
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';
require_once __DIR__ . '/../../utils/Validator.php';

CorsHandler::setJsonHeaders();

// Requerir autenticación
$payload = JWTHandler::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$usuarioModel = new UsuarioModel();

try {
    switch ($method) {
        case 'GET':
            handleGet($usuarioModel);
            break;

        case 'POST':
            handlePost($usuarioModel);
            break;

        case 'PUT':
            handlePut($usuarioModel);
            break;

        case 'DELETE':
            handleDelete($usuarioModel);
            break;

        default:
            http_response_code(405);
            echo json_encode([
                'success' => false,
                'error' => 'Método no permitido',
                'code' => 'METHOD_NOT_ALLOWED'
            ]);
            break;
    }

} catch (Exception $e) {
    error_log("Usuarios API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * GET - Listar usuarios
 */
function handleGet($usuarioModel) {
    // Verificar si es request de un solo usuario
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $lastPart = end($parts);

    if (is_numeric($lastPart)) {
        // Obtener un usuario específico
        $result = $usuarioModel->getById($lastPart);

        if (!$result['success']) {
            http_response_code(404);
        } else {
            http_response_code(200);
        }

        echo json_encode($result);
        return;
    }

    // Listar todos con paginación
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 15;
    $search = $_GET['search'] ?? '';

    $result = $usuarioModel->getAll($page, $limit, $search);

    http_response_code($result['success'] ? 200 : 500);
    echo json_encode($result);
}

/**
 * POST - Crear usuario
 */
function handlePost($usuarioModel) {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos inválidos',
            'code' => 'INVALID_DATA'
        ]);
        return;
    }

    // Sanitizar datos
    $data = [
        'nombre' => Validator::sanitizeString($input['nombre'] ?? ''),
        'apellido' => Validator::sanitizeString($input['apellido'] ?? ''),
        'fecha_nacimiento' => $input['fecha_nacimiento'] ?? '',
        'observaciones' => Validator::sanitizeString($input['observaciones'] ?? '')
    ];

    // Validar
    $validator = Validator::validateUsuario($data);

    if ($validator->hasErrors()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Errores de validación',
            'code' => 'VALIDATION_ERROR',
            'details' => $validator->getErrors()
        ]);
        return;
    }

    // Crear
    $result = $usuarioModel->create($data);

    http_response_code($result['success'] ? 201 : 500);
    echo json_encode($result);
}

/**
 * PUT - Actualizar usuario
 */
function handlePut($usuarioModel) {
    // Obtener ID de la URL
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $id = end($parts);

    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID inválido',
            'code' => 'INVALID_ID'
        ]);
        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Datos inválidos',
            'code' => 'INVALID_DATA'
        ]);
        return;
    }

    // Sanitizar datos
    $data = [
        'nombre' => Validator::sanitizeString($input['nombre'] ?? ''),
        'apellido' => Validator::sanitizeString($input['apellido'] ?? ''),
        'fecha_nacimiento' => $input['fecha_nacimiento'] ?? '',
        'observaciones' => Validator::sanitizeString($input['observaciones'] ?? '')
    ];

    // Validar
    $validator = Validator::validateUsuario($data, true, $id);

    if ($validator->hasErrors()) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Errores de validación',
            'code' => 'VALIDATION_ERROR',
            'details' => $validator->getErrors()
        ]);
        return;
    }

    // Actualizar
    $result = $usuarioModel->update($id, $data);

    http_response_code($result['success'] ? 200 : ($result['code'] === 'NOT_FOUND' ? 404 : 500));
    echo json_encode($result);
}

/**
 * DELETE - Eliminar usuario
 */
function handleDelete($usuarioModel) {
    // Obtener ID de la URL
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $id = end($parts);

    if (!is_numeric($id)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID inválido',
            'code' => 'INVALID_ID'
        ]);
        return;
    }

    // Eliminar
    $result = $usuarioModel->delete($id);

    http_response_code($result['success'] ? 200 : ($result['code'] === 'NOT_FOUND' ? 404 : 500));
    echo json_encode($result);
}
