<?php
/**
 * Configuración de Base de Datos
 * Maneja la conexión a MySQL usando PDO
 */

class Database {
    private static $instance = null;
    private $connection;

    // Configuración desde variables de entorno o valores por defecto
    private $host;
    private $db_name;
    private $username;
    private $password;
    private $port;

    private function __construct() {
        // Cargar variables de entorno
        $this->loadEnvVariables();

        // Configurar parámetros de conexión
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'birthdays_db';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
        $this->port = getenv('DB_PORT') ?: '3306';

        $this->connect();
    }

    /**
     * Cargar variables de entorno desde archivo .env
     */
    private function loadEnvVariables() {
        $envFile = __DIR__ . '/../../.env';

        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue;
                }

                if (strpos($line, '=') !== false) {
                    list($key, $value) = explode('=', $line, 2);
                    $key = trim($key);
                    $value = trim($value);

                    if (!array_key_exists($key, $_ENV) && !array_key_exists($key, $_SERVER)) {
                        putenv("$key=$value");
                        $_ENV[$key] = $value;
                        $_SERVER[$key] = $value;
                    }
                }
            }
        }
    }

    /**
     * Establecer conexión con la base de datos
     */
    private function connect() {
        $this->connection = null;

        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->db_name};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci, time_zone = '-03:00'"
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);

        } catch(PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos");
        }
    }

    /**
     * Obtener instancia única de Database (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Obtener conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prevenir clonación
     */
    private function __clone() {}

    /**
     * Prevenir unserialize
     */
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
