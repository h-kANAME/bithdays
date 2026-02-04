<?php
/**
 * Modelo de Destinatarios
 * Maneja operaciones CRUD de destinatarios de emails
 */

require_once __DIR__ . '/../config/database.php';

class DestinatarioModel {
    private $db;
    private $table = 'destinatarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los destinatarios con paginación y búsqueda
     */
    public function getAll($page = 1, $limit = 15, $search = '') {
        try {
            $offset = ($page - 1) * $limit;

            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];

            if (!empty($search)) {
                $countSql .= " WHERE nombre LIKE :search OR apellido LIKE :search OR email LIKE :search";
                $params[':search'] = "%$search%";
            }

            $stmt = $this->db->prepare($countSql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            $total = $stmt->fetch()['total'];

            // Obtener registros
            $sql = "SELECT * FROM {$this->table}";

            if (!empty($search)) {
                $sql .= " WHERE nombre LIKE :search OR apellido LIKE :search OR email LIKE :search";
            }

            $sql .= " ORDER BY apellido ASC, nombre ASC LIMIT :limit OFFSET :offset";

            $stmt = $this->db->prepare($sql);

            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
            }

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetchAll();

            return [
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => (int)$total,
                    'totalPages' => ceil($total / $limit)
                ]
            ];

        } catch (Exception $e) {
            error_log("DestinatarioModel getAll error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener destinatarios',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Obtener todos los destinatarios sin paginación (para envío de emails)
     */
    public static function getAllRecipients() {
        try {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT * FROM destinatarios ORDER BY apellido ASC, nombre ASC";
            $stmt = $db->prepare($sql);
            $stmt->execute();

            return $stmt->fetchAll();

        } catch (Exception $e) {
            error_log("DestinatarioModel getAllRecipients error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener un destinatario por ID
     */
    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $data = $stmt->fetch();

            if (!$data) {
                return [
                    'success' => false,
                    'error' => 'Destinatario no encontrado',
                    'code' => 'NOT_FOUND'
                ];
            }

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (Exception $e) {
            error_log("DestinatarioModel getById error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener destinatario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Crear nuevo destinatario
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, apellido, email, observaciones)
                    VALUES (:nombre, :apellido, :email, :observaciones)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':apellido', $data['apellido']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':observaciones', $data['observaciones']);

            $stmt->execute();
            $id = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Destinatario creado correctamente',
                'data' => $this->getById($id)['data']
            ];

        } catch (PDOException $e) {
            // Verificar si es error de duplicado
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'error' => 'El email ya está registrado',
                    'code' => 'DUPLICATE_EMAIL'
                ];
            }

            error_log("DestinatarioModel create error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al crear destinatario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Actualizar destinatario
     */
    public function update($id, $data) {
        try {
            // Verificar que existe
            $exists = $this->getById($id);
            if (!$exists['success']) {
                return $exists;
            }

            $sql = "UPDATE {$this->table}
                    SET nombre = :nombre,
                        apellido = :apellido,
                        email = :email,
                        observaciones = :observaciones
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':apellido', $data['apellido']);
            $stmt->bindParam(':email', $data['email']);
            $stmt->bindParam(':observaciones', $data['observaciones']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Destinatario actualizado correctamente',
                'data' => $this->getById($id)['data']
            ];

        } catch (PDOException $e) {
            // Verificar si es error de duplicado
            if ($e->getCode() == 23000) {
                return [
                    'success' => false,
                    'error' => 'El email ya está registrado',
                    'code' => 'DUPLICATE_EMAIL'
                ];
            }

            error_log("DestinatarioModel update error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al actualizar destinatario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Eliminar destinatario
     */
    public function delete($id) {
        try {
            // Verificar que existe
            $exists = $this->getById($id);
            if (!$exists['success']) {
                return $exists;
            }

            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Destinatario eliminado correctamente'
            ];

        } catch (Exception $e) {
            error_log("DestinatarioModel delete error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al eliminar destinatario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Verificar si un email ya existe
     */
    public function emailExists($email, $excludeId = null) {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = :email";

            if ($excludeId !== null) {
                $sql .= " AND id != :id";
            }

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':email', $email);

            if ($excludeId !== null) {
                $stmt->bindParam(':id', $excludeId, PDO::PARAM_INT);
            }

            $stmt->execute();
            $result = $stmt->fetch();

            return $result['count'] > 0;

        } catch (Exception $e) {
            error_log("DestinatarioModel emailExists error: " . $e->getMessage());
            return false;
        }
    }
}
