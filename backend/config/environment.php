<?php
/**
 * Configuración de Entorno
 * Detecta automáticamente si está en desarrollo o producción
 */

class Environment {
    /**
     * Detecta y retorna el path base según el entorno
     */
    public static function getBasePath(): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0)
            ? '/'
            : '/birthdays/backend/public/';
    }

    /**
     * Retorna la URL completa del frontend
     */
    public static function getFrontendPath(): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        if ($host === 'localhost' || $host === '127.0.0.1' || strpos($host, 'localhost:') === 0) {
            return 'http://localhost:3000';
        } else {
            return 'https://kyz.com.ar/birthdays/frontend';
        }
    }

    /**
     * Verifica si está en modo desarrollo
     */
    public static function isDevelopment(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $appEnv = getenv('APP_ENV') ?: 'production';

        return (
            $host === 'localhost' ||
            $host === '127.0.0.1' ||
            strpos($host, 'localhost:') === 0 ||
            $appEnv === 'local' ||
            $appEnv === 'development'
        );
    }

    /**
     * Obtener URL base de la API
     */
    public static function getApiUrl(): string {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $basePath = self::getBasePath();

        return $protocol . '://' . $host . $basePath;
    }

    /**
     * Obtener configuración de CORS
     */
    public static function getAllowedOrigins(): array {
        $allowedOrigins = getenv('ALLOWED_ORIGINS');

        if ($allowedOrigins) {
            return array_map('trim', explode(',', $allowedOrigins));
        }

        // Valores por defecto según entorno
        if (self::isDevelopment()) {
            return [
                'http://localhost:3000',
                'http://localhost:8000',
                'http://127.0.0.1:3000',
                'http://127.0.0.1:8000'
            ];
        } else {
            return [
                'https://kyz.com.ar',
                'https://kyz.com.ar/birthdays',
                'https://kyz.com.ar/birthdays/frontend'
            ];
        }
    }

    /**
     * Obtener configuración completa del entorno
     */
    public static function getConfig(): array {
        return [
            'isDevelopment' => self::isDevelopment(),
            'basePath' => self::getBasePath(),
            'frontendPath' => self::getFrontendPath(),
            'apiUrl' => self::getApiUrl(),
            'allowedOrigins' => self::getAllowedOrigins()
        ];
    }
}
