<?php
/**
 * Cargador de variables de entorno desde archivo .env
 * Se debe incluir al inicio de cada script
 */

class EnvLoader {
    /**
     * Cargar variables desde archivo .env
     */
    public static function load($path = null) {
        if ($path === null) {
            // Por defecto, buscar .env en la carpeta backend/
            $path = __DIR__ . '/../.env';
        }

        if (!file_exists($path)) {
            error_log("EnvLoader: archivo .env no encontrado en: $path");
            return false;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Parsear línea: KEY=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);

                $key = trim($key);
                $value = trim($value);

                // Remover comillas si existen
                if (
                    (substr($value, 0, 1) === '"' && substr($value, -1) === '"') ||
                    (substr($value, 0, 1) === "'" && substr($value, -1) === "'")
                ) {
                    $value = substr($value, 1, -1);
                }

                // Solo cargar si la variable no está ya definida
                if (getenv($key) === false) {
                    putenv("$key=$value");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }

        return true;
    }
}

// Auto-cargar cuando se incluye este archivo
EnvLoader::load();
