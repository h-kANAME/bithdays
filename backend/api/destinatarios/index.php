<?php
/**
 * Endpoint: Destinatarios CRUD
 * GET    /api/destinatarios       - Listar todos (con paginación y búsqueda)
 * GET    /api/destinatarios/{id}  - Obtener uno
 * POST   /api/destinatarios       - Crear nuevo
 * PUT    /api/destinatarios/{id}  - Actualizar
 * DELETE /api/destinatarios/{id}  - Eliminar
 */

require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/DestinatarioModel.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';
require_once __DIR__ . '/../../utils/Validator.php';

CorsHandler::setJsonHeaders();

// Requerir autenticación
$payload = JWTHandler::requireAuth();

$method = $_SERVER['REQUEST_METHOD'];
$destinatarioModel = new DestinatarioModel();

try {
    switch ($method) {
        case 'GET':
            handleGet($destinatarioModel);
            break;

        case 'POST':
            handlePost($destinatarioModel);
            break;

        case 'PUT':
            handlePut($destinatarioModel);
            break;

        case 'DELETE':
            handleDelete($destinatarioModel);
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
    error_log("Destinatarios API error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error en el servidor',
        'code' => 'SERVER_ERROR'
    ]);
}

/**
 * GET - Listar destinatarios
 */
function handleGet($destinatarioModel) {
    // Verificar si es request de un solo destinatario
    $uri = $_SERVER['REQUEST_URI'];
    $parts = explode('/', trim($uri, '/'));
    $lastPart = end($parts);

    if (is_numeric($lastPart)) {
        // Obtener un destinatario específico
        $result = $destinatarioModel->getById($lastPart);

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

    $result = $destinatarioModel->getAll($page, $limit, $search);

    http_response_code($result['success'] ? 200 : 500);
    echo json_encode($result);
}

/**
 * POST - Crear destinatario
 */
function handlePost($destinatarioModel) {
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
        'email' => Validator::sanitizeEmail($input['email'] ?? ''),
        'observaciones' => Validator::sanitizeString($input['observaciones'] ?? '')
    ];

    // Validar
    $validator = Validator::validateDestinatario($data);

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
    $result = $destinatarioModel->create($data);

    http_response_code($result['success'] ? 201 : 500);
    echo json_encode($result);
}

/**
 * PUT - Actualizar destinatario
 */
function handlePut($destinatarioModel) {
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
        'email' => Validator::sanitizeEmail($input['email'] ?? ''),
        'observaciones' => Validator::sanitizeString($input['observaciones'] ?? '')
    ];

    // Validar
    $validator = Validator::validateDestinatario($data, true, $id);

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
    $result = $destinatarioModel->update($id, $data);

    http_response_code($result['success'] ? 200 : ($result['code'] === 'NOT_FOUND' ? 404 : 500));
    echo json_encode($result);
}

/**
 * DELETE - Eliminar destinatario
 */
function handleDelete($destinatarioModel) {
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
    $result = $destinatarioModel->delete($id);

    http_response_code($result['success'] ? 200 : ($result['code'] === 'NOT_FOUND' ? 404 : 500));
    echo json_encode($result);
}
