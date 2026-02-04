<?php
/**
 * Modelo de Usuarios
 * Maneja operaciones CRUD y consultas específicas de usuarios
 */

require_once __DIR__ . '/../config/database.php';

class UsuarioModel {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Obtener todos los usuarios con paginación y búsqueda
     */
    public function getAll($page = 1, $limit = 15, $search = '') {
        try {
            $offset = ($page - 1) * $limit;

            // Contar total
            $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
            $params = [];

            if (!empty($search)) {
                $countSql .= " WHERE nombre LIKE :search OR apellido LIKE :search";
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
                $sql .= " WHERE nombre LIKE :search OR apellido LIKE :search";
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
            error_log("UsuarioModel getAll error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener usuarios',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Obtener un usuario por ID
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
                    'error' => 'Usuario no encontrado',
                    'code' => 'NOT_FOUND'
                ];
            }

            return [
                'success' => true,
                'data' => $data
            ];

        } catch (Exception $e) {
            error_log("UsuarioModel getById error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener usuario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Crear nuevo usuario
     */
    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (nombre, apellido, fecha_nacimiento, observaciones)
                    VALUES (:nombre, :apellido, :fecha_nacimiento, :observaciones)";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':apellido', $data['apellido']);
            $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
            $stmt->bindParam(':observaciones', $data['observaciones']);

            $stmt->execute();
            $id = $this->db->lastInsertId();

            return [
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data' => $this->getById($id)['data']
            ];

        } catch (Exception $e) {
            error_log("UsuarioModel create error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al crear usuario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Actualizar usuario
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
                        fecha_nacimiento = :fecha_nacimiento,
                        observaciones = :observaciones
                    WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':apellido', $data['apellido']);
            $stmt->bindParam(':fecha_nacimiento', $data['fecha_nacimiento']);
            $stmt->bindParam(':observaciones', $data['observaciones']);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data' => $this->getById($id)['data']
            ];

        } catch (Exception $e) {
            error_log("UsuarioModel update error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al actualizar usuario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Eliminar usuario
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
                'message' => 'Usuario eliminado correctamente'
            ];

        } catch (Exception $e) {
            error_log("UsuarioModel delete error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al eliminar usuario',
                'code' => 'DB_ERROR'
            ];
        }
    }

    /**
     * Obtener cumpleaños del día actual
     * Usa la fecha de PHP en lugar de CURDATE() para evitar problemas de timezone
     */
    public static function getBirthdaysToday() {
        try {
            // Log timezone actual
            error_log("getBirthdaysToday - Timezone PHP: " . date_default_timezone_get());
            error_log("getBirthdaysToday - Fecha PHP completa: " . date('Y-m-d H:i:s'));

            $db = Database::getInstance()->getConnection();

            // Usar fecha de PHP (respeta date_default_timezone_set)
            $mes = (int)date('m');
            $dia = (int)date('d');

            error_log("getBirthdaysToday - Buscando cumpleaños para mes: $mes, día: $dia");

            $sql = "SELECT * FROM usuarios
                    WHERE MONTH(fecha_nacimiento) = :mes
                      AND DAY(fecha_nacimiento) = :dia
                    ORDER BY apellido ASC, nombre ASC";

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':mes', $mes, PDO::PARAM_INT);
            $stmt->bindParam(':dia', $dia, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll();

            error_log("getBirthdaysToday - Encontrados: " . count($results) . " cumpleaños");

            // Log de cada cumpleaños encontrado
            foreach ($results as $result) {
                error_log("getBirthdaysToday - Cumpleaños: {$result['nombre']} {$result['apellido']} ({$result['fecha_nacimiento']})");
            }

            return $results;

        } catch (Exception $e) {
            error_log("UsuarioModel getBirthdaysToday error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtener próximos cumpleaños (próximos 30 días)
     */
    public function getUpcomingBirthdays($days = 30) {
        try {
            $sql = "SELECT
                        id,
                        nombre,
                        apellido,
                        fecha_nacimiento,
                        observaciones,
                        YEAR(CURDATE()) - YEAR(fecha_nacimiento) AS edad,
                        DATEDIFF(
                            DATE_ADD(
                                fecha_nacimiento,
                                INTERVAL (YEAR(CURDATE()) - YEAR(fecha_nacimiento)) +
                                IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(fecha_nacimiento), 1, 0) YEAR
                            ),
                            CURDATE()
                        ) AS dias_faltantes
                    FROM {$this->table}
                    WHERE DATEDIFF(
                        DATE_ADD(
                            fecha_nacimiento,
                            INTERVAL (YEAR(CURDATE()) - YEAR(fecha_nacimiento)) +
                            IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(fecha_nacimiento), 1, 0) YEAR
                        ),
                        CURDATE()
                    ) BETWEEN 0 AND :days
                    ORDER BY dias_faltantes ASC";

            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':days', $days, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'data' => $stmt->fetchAll()
            ];

        } catch (Exception $e) {
            error_log("UsuarioModel getUpcomingBirthdays error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => 'Error al obtener próximos cumpleaños',
                'code' => 'DB_ERROR'
            ];
        }
    }
}
