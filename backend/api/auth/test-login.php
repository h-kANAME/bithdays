<?php
/**
 * Test de login (ELIMINAR después de depurar)
 */

// Cargar variables de entorno
require_once __DIR__ . '/../../config/load_env.php';
require_once __DIR__ . '/../../config/cors.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/AdminModel.php';
require_once __DIR__ . '/../../utils/JWTHandler.php';

CorsHandler::setJsonHeaders();

try {
    // Probar obtener un admin de la BD
    $db = Database::getInstance()->getConnection();
    $sql = "SELECT * FROM administradores LIMIT 1";
    $stmt = $db->query($sql);
    $admin = $stmt->fetch();

    echo json_encode([
        'success' => true,
        'debug' => [
            'admin_found' => $admin !== false,
            'admin_columns' => $admin ? array_keys($admin) : [],
            'has_created_at' => $admin && isset($admin['created_at'])
        ]
    ], JSON_PRETTY_PRINT);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
