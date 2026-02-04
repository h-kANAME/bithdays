<?php
/**
 * Validador de datos
 * Valida y sanitiza datos de entrada
 */

class Validator {
    private $errors = [];

    /**
     * Validar campo requerido
     */
    public function required($field, $value, $fieldName = null) {
        $name = $fieldName ?? $field;

        if (empty($value) && $value !== '0') {
            $this->errors[$field] = "El campo $name es requerido";
            return false;
        }

        return true;
    }

    /**
     * Validar longitud mínima
     */
    public function minLength($field, $value, $min, $fieldName = null) {
        $name = $fieldName ?? $field;

        if (strlen($value) < $min) {
            $this->errors[$field] = "El campo $name debe tener al menos $min caracteres";
            return false;
        }

        return true;
    }

    /**
     * Validar longitud máxima
     */
    public function maxLength($field, $value, $max, $fieldName = null) {
        $name = $fieldName ?? $field;

        if (strlen($value) > $max) {
            $this->errors[$field] = "El campo $name no puede exceder $max caracteres";
            return false;
        }

        return true;
    }

    /**
     * Validar email
     */
    public function email($field, $value, $fieldName = null) {
        $name = $fieldName ?? $field;

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El campo $name debe ser un email válido";
            return false;
        }

        return true;
    }

    /**
     * Validar fecha
     */
    public function date($field, $value, $format = 'Y-m-d', $fieldName = null) {
        $name = $fieldName ?? $field;

        $d = DateTime::createFromFormat($format, $value);

        if (!$d || $d->format($format) !== $value) {
            $this->errors[$field] = "El campo $name debe ser una fecha válida ($format)";
            return false;
        }

        return true;
    }

    /**
     * Validar que la fecha no sea futura
     */
    public function notFutureDate($field, $value, $fieldName = null) {
        $name = $fieldName ?? $field;

        $date = strtotime($value);
        $today = strtotime('today');

        if ($date > $today) {
            $this->errors[$field] = "El campo $name no puede ser una fecha futura";
            return false;
        }

        return true;
    }

    /**
     * Validar solo letras y espacios
     */
    public function alpha($field, $value, $fieldName = null) {
        $name = $fieldName ?? $field;

        // Permitir letras con acentos y espacios
        if (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s]+$/', $value)) {
            $this->errors[$field] = "El campo $name solo puede contener letras";
            return false;
        }

        return true;
    }

    /**
     * Validar valor único en base de datos
     */
    public function unique($field, $value, $table, $column, $excludeId = null, $fieldName = null) {
        $name = $fieldName ?? $field;

        try {
            $db = Database::getInstance()->getConnection();

            $sql = "SELECT COUNT(*) FROM $table WHERE $column = :value";

            if ($excludeId !== null) {
                $sql .= " AND id != :id";
            }

            $stmt = $db->prepare($sql);
            $stmt->bindParam(':value', $value);

            if ($excludeId !== null) {
                $stmt->bindParam(':id', $excludeId);
            }

            $stmt->execute();
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $this->errors[$field] = "El $name ya está registrado";
                return false;
            }

            return true;

        } catch (Exception $e) {
            error_log("Validation unique error: " . $e->getMessage());
            $this->errors[$field] = "Error al validar $name";
            return false;
        }
    }

    /**
     * Obtener todos los errores
     */
    public function getErrors() {
        return $this->errors;
    }

    /**
     * Verificar si hay errores
     */
    public function hasErrors() {
        return !empty($this->errors);
    }

    /**
     * Limpiar errores
     */
    public function clearErrors() {
        $this->errors = [];
    }

    /**
     * Sanitizar string
     */
    public static function sanitizeString($value) {
        return htmlspecialchars(strip_tags(trim($value)), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizar email
     */
    public static function sanitizeEmail($value) {
        return filter_var(trim($value), FILTER_SANITIZE_EMAIL);
    }

    /**
     * Validar datos de usuario
     */
    public static function validateUsuario($data, $isUpdate = false, $excludeId = null) {
        $validator = new self();

        // Nombre
        if (!$validator->required('nombre', $data['nombre'] ?? '', 'nombre')) {
            return $validator;
        }
        $validator->minLength('nombre', $data['nombre'], 2, 'nombre');
        $validator->maxLength('nombre', $data['nombre'], 100, 'nombre');
        $validator->alpha('nombre', $data['nombre'], 'nombre');

        // Apellido
        if (!$validator->required('apellido', $data['apellido'] ?? '', 'apellido')) {
            return $validator;
        }
        $validator->minLength('apellido', $data['apellido'], 2, 'apellido');
        $validator->maxLength('apellido', $data['apellido'], 100, 'apellido');
        $validator->alpha('apellido', $data['apellido'], 'apellido');

        // Fecha de nacimiento
        if (!$validator->required('fecha_nacimiento', $data['fecha_nacimiento'] ?? '', 'fecha de nacimiento')) {
            return $validator;
        }
        $validator->date('fecha_nacimiento', $data['fecha_nacimiento'], 'Y-m-d', 'fecha de nacimiento');
        $validator->notFutureDate('fecha_nacimiento', $data['fecha_nacimiento'], 'fecha de nacimiento');

        return $validator;
    }

    /**
     * Validar datos de destinatario
     */
    public static function validateDestinatario($data, $isUpdate = false, $excludeId = null) {
        $validator = new self();

        // Nombre
        if (!$validator->required('nombre', $data['nombre'] ?? '', 'nombre')) {
            return $validator;
        }
        $validator->minLength('nombre', $data['nombre'], 2, 'nombre');
        $validator->maxLength('nombre', $data['nombre'], 100, 'nombre');
        $validator->alpha('nombre', $data['nombre'], 'nombre');

        // Apellido
        if (!$validator->required('apellido', $data['apellido'] ?? '', 'apellido')) {
            return $validator;
        }
        $validator->minLength('apellido', $data['apellido'], 2, 'apellido');
        $validator->maxLength('apellido', $data['apellido'], 100, 'apellido');
        $validator->alpha('apellido', $data['apellido'], 'apellido');

        // Email
        if (!$validator->required('email', $data['email'] ?? '', 'email')) {
            return $validator;
        }
        $validator->email('email', $data['email'], 'email');
        $validator->unique('email', $data['email'], 'destinatarios', 'email', $excludeId, 'email');

        return $validator;
    }
}
