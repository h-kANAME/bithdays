<?php
/**
 * Modelo de Administradores
 * Maneja autenticación y gestión de administradores
 */

require_once __DIR__ . '/../config/database.php';

class AdminModel {
    private $db;
    private $table = 'administradores';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Autenticar administrador
     */
    public function authenticate($usuario, $password) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE usuario = :usuario";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->execute();

            $admin = $stmt->fetch();

            if (!$admin) {
                return [
                    'success' => false,
                    'error' => 'Usuario o contraseña incorrectos',
                    'code' => 'INVALID_CREDENTIALS'
                ];
            }

            // Verificar contraseña
            if (!password_verify($password, $admin['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'Usuario o contraseña incorrectos',
                    'code' => 'INVALID_CREDENTIALS'
                ];
            }

            $data = [
                'id' => $admin['id'],
                'usuario' => $admin['usuario']
            ];

            // Agregar created_at solo si existe
            if (isset($admin['created_at'])) {
                $data['created_at'] = $admin['created_at'];
            }

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (Exception $e) {
            error_log("AdminModel authenticate error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error en la autenticación',
                'code' => 'AUTH_ERROR'
            ];
        }
    }

    /**
     * Guardar token de acceso
     */
    public function saveAccessToken($adminId, $token) {
        try {
            // Calcular fecha de expiración
            $expiry = getenv('ACCESS_TOKEN_EXPIRY') ?: '30d';
            $expirySeconds = $this->parseExpiry($expiry);
            $expiryDate = date('Y-m-d H:i:s', time() + $expirySeconds);

            $sql = "UPDATE {$this->table}
                    SET access_token = :token,
                        token_expiry = :expiry
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expiry', $expiryDate);
            $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);

            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Token guardado correctamente'
            ];

        } catch (Exception $e) {
            error_log("AdminModel saveAccessToken error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al guardar token',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Verificar token de acceso
     */
    public function verifyAccessToken($token) {
        try {
            $sql = "SELECT * FROM {$this->table}
                    WHERE access_token = :token
                      AND (token_expiry IS NULL OR token_expiry > NOW())";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':token', $token);
            $stmt->execute();

            $admin = $stmt->fetch();

            if (!$admin) {
                return [
                    'success' => false,
                    'error' => 'Token inválido o expirado',
                    'code' => 'INVALID_TOKEN'
                ];
            }

            $data = [
                'id' => $admin['id'],
                'usuario' => $admin['usuario']
            ];

            // Agregar created_at solo si existe
            if (isset($admin['created_at'])) {
                $data['created_at'] = $admin['created_at'];
            }

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (Exception $e) {
            error_log("AdminModel verifyAccessToken error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al verificar token',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Cerrar sesión (invalidar token)
     */
    public function logout($adminId) {
        try {
            $sql = "UPDATE {$this->table}
                    SET access_token = NULL,
                        token_expiry = NULL
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Sesión cerrada correctamente'
            ];

        } catch (Exception $e) {
            error_log("AdminModel logout error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al cerrar sesión',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Obtener administrador por ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT id, usuario, created_at FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $admin = $stmt->fetch();

            if (!$admin) {
                return [
                    'success' => false,
                    'error' => 'Administrador no encontrado',
                    'code' => 'NOT_FOUND'
                ];
            }

            return [
                'success' => true,
                'data' => $admin
            ];

        } catch (Exception $e) {
            error_log("AdminModel getById error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener administrador',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Cambiar contraseña
     */
    public function changePassword($adminId, $oldPassword, $newPassword) {
        try {
            // Obtener administrador
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
            $stmt->execute();

            $admin = $stmt->fetch();

            if (!$admin) {
                return [
                    'success' => false,
                    'error' => 'Administrador no encontrado',
                    'code' => 'NOT_FOUND'
                ];
            }

            // Verificar contraseña actual
            if (!password_verify($oldPassword, $admin['password_hash'])) {
                return [
                    'success' => false,
                    'error' => 'Contraseña actual incorrecta',
                    'code' => 'INVALID_PASSWORD'
                ];
            }

            // Actualizar contraseña
            $newHash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 10]);

            $sql = "UPDATE {$this->table}
                    SET password_hash = :hash
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':hash', $newHash);
            $stmt->bindParam(':id', $adminId, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ];

        } catch (Exception $e) {
            error_log("AdminModel changePassword error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al cambiar contraseña',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Parsear tiempo de expiración (ej: "30d", "24h", "3600s")
     */
    private function parseExpiry($expiry) {
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
}
