-- =====================================================
-- Script de Inicialización - Sistema de Cumpleaños
-- =====================================================

-- Configurar timezone para Buenos Aires
SET time_zone = '-03:00';

-- Crear base de datos si no existe
CREATE DATABASE IF NOT EXISTS birthdays_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE birthdays_db;

-- =====================================================
-- Tabla: usuarios
-- =====================================================
CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha_nacimiento (fecha_nacimiento),
    INDEX idx_nombre_apellido (nombre, apellido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: destinatarios
-- =====================================================
CREATE TABLE IF NOT EXISTS destinatarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nombre_apellido (nombre, apellido)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Tabla: administradores
-- =====================================================
CREATE TABLE IF NOT EXISTS administradores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    access_token VARCHAR(500),
    token_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario),
    INDEX idx_access_token (access_token(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Datos Iniciales
-- =====================================================

-- Insertar administrador por defecto
-- Usuario: elopez | Contraseña: birthdais*
-- Hash generado con: password_hash('birthdais*', PASSWORD_BCRYPT, ['cost' => 10])
INSERT INTO administradores (usuario, password_hash)
VALUES ('elopez', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')
ON DUPLICATE KEY UPDATE usuario = usuario;

-- =====================================================
-- Datos de Ejemplo (Opcional - Comentar en producción)
-- =====================================================

-- Usuarios de ejemplo
INSERT INTO usuarios (nombre, apellido, fecha_nacimiento, observaciones) VALUES
('Juan', 'Pérez', '1990-05-15', 'Cliente VIP'),
('María', 'González', '1985-08-22', 'Contacto principal'),
('Carlos', 'Rodríguez', '1992-03-10', NULL),
('Ana', 'Martínez', '1988-11-30', 'Gerente de ventas'),
('Luis', 'Fernández', '1995-07-18', NULL)
ON DUPLICATE KEY UPDATE nombre = nombre;

-- Destinatarios de ejemplo
INSERT INTO destinatarios (nombre, apellido, email, observaciones) VALUES
('Pedro', 'López', 'pedro.lopez@example.com', 'Recursos Humanos'),
('Laura', 'Sánchez', 'laura.sanchez@example.com', 'Administración'),
('Diego', 'Ramírez', 'diego.ramirez@example.com', 'Gerencia')
ON DUPLICATE KEY UPDATE email = email;

-- =====================================================
-- Vistas útiles
-- =====================================================

-- Vista para cumpleaños de hoy
CREATE OR REPLACE VIEW v_cumpleanios_hoy AS
SELECT
    id,
    nombre,
    apellido,
    fecha_nacimiento,
    observaciones,
    YEAR(CURDATE()) - YEAR(fecha_nacimiento) AS edad
FROM usuarios
WHERE MONTH(fecha_nacimiento) = MONTH(CURDATE())
  AND DAY(fecha_nacimiento) = DAY(CURDATE());

-- Vista para próximos cumpleaños (próximos 30 días)
CREATE OR REPLACE VIEW v_proximos_cumpleanios AS
SELECT
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
FROM usuarios
WHERE DATEDIFF(
    DATE_ADD(
        fecha_nacimiento,
        INTERVAL (YEAR(CURDATE()) - YEAR(fecha_nacimiento)) +
        IF(DAYOFYEAR(CURDATE()) > DAYOFYEAR(fecha_nacimiento), 1, 0) YEAR
    ),
    CURDATE()
) BETWEEN 0 AND 30
ORDER BY dias_faltantes;

-- =====================================================
-- Fin del Script
-- =====================================================
