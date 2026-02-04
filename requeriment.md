# Sistema de Gestión de Cumpleaños

## 📋 Información General

| Campo | Valor |
|-------|-------|
| **Nombre** | Sistema de Gestión de Cumpleaños |
| **Objetivo** | Sistema web para gestionar usuarios y enviar notificaciones diarias de cumpleaños |
| **Stack Tecnológico** | PHP 8.1, MySQL 8.0, React 18, Material MUI, Bootstrap, EmailJS |

### Estructura del Proyecto

```text
birthdays/
├── backend/           # API REST en PHP
├── frontend/          # Aplicación React
├── docker-compose.yml # Orquestación de contenedores
├── .env              # Variables de entorno
└── README.md         # Documentación principal
```

---

## 🗄️ Base de Datos

### Esquema de Tablas

#### Tabla: `usuarios`
```sql
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_fecha_nacimiento (fecha_nacimiento)
);
```

#### Tabla: `destinatarios`
```sql
CREATE TABLE destinatarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    observaciones TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);
```

#### Tabla: `administradores`
```sql
CREATE TABLE administradores (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    access_token VARCHAR(255),
    token_expiry TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

### Datos Iniciales

```sql
-- Usuario: elopez | Contraseña: birthdais*
INSERT INTO administradores (usuario, password_hash)
VALUES ('elopez', '$2y$10$TuHashDeSeguridadAqui');
```

---

## 🔧 Backend (PHP)

### Estructura de Carpetas

```text
backend/
├── api/
│   ├── auth/
│   │   ├── login.php
│   │   ├── verify-token.php
│   │   └── logout.php
│   ├── usuarios/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── delete.php
│   ├── destinatarios/
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── delete.php
│   └── cron/
│       ├── check-birthdays.php
│       └── test-email.php
├── config/
│   ├── database.php
│   ├── environment.php
│   └── cors.php
├── models/
│   ├── UsuarioModel.php
│   ├── DestinatarioModel.php
│   └── AdminModel.php
├── utils/
│   ├── EmailService.php
│   ├── JWTHandler.php
│   └── Validator.php
├── public/
│   ├── index.php
│   └── .htaccess
└── Dockerfile
```

### Endpoints API

#### 🔐 Autenticación
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `POST` | `/api/auth/login` | Autenticación de usuario |
| `POST` | `/api/auth/verify-token` | Verificación de token JWT |
| `POST` | `/api/auth/logout` | Cierre de sesión |

#### 👥 Usuarios
| Método | Endpoint | Descripción | Query Params |
|--------|----------|-------------|--------------|
| `GET` | `/api/usuarios` | Listar usuarios | `?page=1&limit=15&search=` |
| `POST` | `/api/usuarios` | Crear usuario | - |
| `PUT` | `/api/usuarios/{id}` | Actualizar usuario | - |
| `DELETE` | `/api/usuarios/{id}` | Eliminar usuario | - |

#### 📧 Destinatarios
| Método | Endpoint | Descripción | Query Params |
|--------|----------|-------------|--------------|
| `GET` | `/api/destinatarios` | Listar destinatarios | `?page=1&limit=15&search=` |
| `POST` | `/api/destinatarios` | Crear destinatario | - |
| `PUT` | `/api/destinatarios/{id}` | Actualizar destinatario | - |
| `DELETE` | `/api/destinatarios/{id}` | Eliminar destinatario | - |

#### ⏰ CRON
| Método | Endpoint | Descripción |
|--------|----------|-------------|
| `GET` | `/api/cron/check-birthdays` | Ejecutar verificación de cumpleaños |
| `GET` | `/api/cron/test-email` | Prueba de envío de email |

### Script CRON

```php
<?php
// backend/api/cron/check-birthdays.php
require_once '../../config/database.php';
require_once '../../models/UsuarioModel.php';
require_once '../../models/DestinatarioModel.php';
require_once '../../utils/EmailService.php';

class BirthdayChecker {
    public function checkAndNotify() {
        // Obtener cumpleaños del día actual
        $usuariosCumpleanios = UsuarioModel::getBirthdaysToday();

        if (empty($usuariosCumpleanios)) {
            return [
                'success' => true,
                'message' => 'No hay cumpleaños hoy',
                'count' => 0
            ];
        }

        // Obtener todos los destinatarios
        $destinatarios = DestinatarioModel::getAll();

        if (empty($destinatarios)) {
            return [
                'success' => false,
                'message' => 'No hay destinatarios configurados',
                'count' => 0
            ];
        }

        // Enviar email a todos los destinatarios
        $emailService = new EmailService();
        $result = $emailService->sendBirthdayNotification(
            $destinatarios,
            $usuariosCumpleanios
        );

        return $result;
    }
}

// Ejecutar
$checker = new BirthdayChecker();
$result = $checker->checkAndNotify();
header('Content-Type: application/json');
echo json_encode($result);
?>
```

---

## ⚛️ Frontend (React)

### Estructura de Componentes

```text
frontend/
├── public/
│   ├── index.html
│   └── favicon.ico
├── src/
│   ├── components/
│   │   ├── Layout/
│   │   │   ├── Header.jsx
│   │   │   ├── Sidebar.jsx
│   │   │   └── MainLayout.jsx
│   │   ├── Auth/
│   │   │   ├── Login.jsx
│   │   │   └── PrivateRoute.jsx
│   │   ├── Usuarios/
│   │   │   ├── UsuariosList.jsx
│   │   │   ├── UsuarioForm.jsx
│   │   │   └── UsuarioTable.jsx
│   │   ├── Destinatarios/
│   │   │   ├── DestinatariosList.jsx
│   │   │   ├── DestinatarioForm.jsx
│   │   │   └── DestinatarioTable.jsx
│   │   └── Common/
│   │       ├── Pagination.jsx
│   │       ├── SearchBar.jsx
│   │       └── ConfirmDialog.jsx
│   ├── services/
│   │   ├── api.js
│   │   ├── authService.js
│   │   ├── usuariosService.js
│   │   └── destinatariosService.js
│   ├── contexts/
│   │   └── AuthContext.jsx
│   ├── utils/
│   │   ├── validators.js
│   │   └── formatters.js
│   ├── styles/
│   │   └── theme.js
│   ├── App.jsx
│   └── index.js
├── package.json
└── Dockerfile
```

### Rutas y Vistas

| Ruta | Componente | Descripción | Protegida |
|------|-----------|-------------|-----------|
| `/login` | `Login.jsx` | Formulario de autenticación | ❌ |
| `/dashboard` | `Dashboard.jsx` | Vista principal | ✅ |
| `/usuarios` | `UsuariosList.jsx` | CRUD de usuarios | ✅ |
| `/destinatarios` | `DestinatariosList.jsx` | CRUD de destinatarios | ✅ |

### Características de la Interfaz

- **Design System**: Material MUI + Bootstrap
- **Responsive**: Mobile-first approach
- **Paginación**: 15 registros por página
- **Búsqueda**: Por nombre y apellido (debounce 300ms)
- **Tablas**: Ordenamiento, edición inline, confirmación de eliminación
- **Manejo de Estado**: React Context API
- **Notificaciones**: Snackbar para feedback de operaciones

---

## ⚙️ Configuración de Entornos

### Variables de Entorno (`.env`)

```bash
# === Entorno ===
APP_ENV=production
APP_URL=https://kyz.com.ar/birthdays

# === Base de Datos ===
DB_HOST=localhost
DB_PORT=3306
DB_NAME=birthdays_db
DB_USER=birthdays_user
DB_PASS=password_seguro

# === EmailJS ===
EMAILJS_SERVICE_ID=your_service_id
EMAILJS_TEMPLATE_ID=your_template_id
EMAILJS_PUBLIC_KEY=your_public_key

# === Autenticación ===
JWT_SECRET=your_jwt_secret_minimum_32_characters
ACCESS_TOKEN_EXPIRY=30d

# === Frontend ===
REACT_APP_API_URL=/birthdays/backend/public
```

### Detección Automática de Entorno

```php
<?php
// backend/config/environment.php

class Environment {
    /**
     * Detecta y retorna el path base según el entorno
     */
    public static function getBasePath(): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return ($host === 'localhost' || $host === '127.0.0.1')
            ? '/'
            : '/birthdays/backend/public/';
    }

    /**
     * Retorna la URL completa del frontend
     */
    public static function getFrontendPath(): string {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';

        return ($host === 'localhost' || $host === '127.0.0.1')
            ? 'http://localhost:3000'
            : 'https://kyz.com.ar/birthdays/frontend';
    }

    /**
     * Verifica si está en modo desarrollo
     */
    public static function isDevelopment(): bool {
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        return ($host === 'localhost' || $host === '127.0.0.1');
    }
}
?>
```

---

## 🐳 Configuración Docker

### `docker-compose.yml`

```yaml
version: '3.8'

services:
  backend:
    build: ./backend
    container_name: birthdays_backend
    ports:
      - "8000:80"
    volumes:
      - ./backend:/var/www/html
    environment:
      - APP_ENV=local
      - DB_HOST=database
      - DB_PORT=3306
      - DB_NAME=birthdays_db
      - DB_USER=birthdays_user
      - DB_PASS=local_password
    depends_on:
      - database
    networks:
      - birthdays_network

  frontend:
    build: ./frontend
    container_name: birthdays_frontend
    ports:
      - "3000:3000"
    volumes:
      - ./frontend:/app
      - /app/node_modules
    environment:
      - REACT_APP_API_URL=http://localhost:8000
      - CHOKIDAR_USEPOLLING=true
    depends_on:
      - backend
    networks:
      - birthdays_network

  database:
    image: mysql:8.0
    container_name: birthdays_db
    environment:
      MYSQL_ROOT_PASSWORD: root_password
      MYSQL_DATABASE: birthdays_db
      MYSQL_USER: birthdays_user
      MYSQL_PASSWORD: local_password
    ports:
      - "3306:3306"
    volumes:
      - mysql_data:/var/lib/mysql
      - ./database/init.sql:/docker-entrypoint-initdb.d/init.sql
    networks:
      - birthdays_network

  phpmyadmin:
    image: phpmyadmin/phpmyadmin
    container_name: birthdays_phpmyadmin
    ports:
      - "8080:80"
    environment:
      PMA_HOST: database
      MYSQL_ROOT_PASSWORD: root_password
    depends_on:
      - database
    networks:
      - birthdays_network

volumes:
  mysql_data:

networks:
  birthdays_network:
    driver: bridge
```

### Backend `Dockerfile`

```dockerfile
FROM php:8.1-apache

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Habilitar mod_rewrite para Apache
RUN a2enmod rewrite

# Configurar directorio de trabajo
WORKDIR /var/www/html

# Copiar archivos
COPY . /var/www/html

# Permisos
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
```

### Frontend `Dockerfile`

```dockerfile
FROM node:18-alpine

# Configurar directorio de trabajo
WORKDIR /app

# Copiar archivos de dependencias
COPY package.json package-lock.json ./

# Instalar dependencias
RUN npm install

# Copiar código fuente
COPY . .

EXPOSE 3000

# Iniciar servidor de desarrollo
CMD ["npm", "start"]
```

---

## ⏰ Configuración CRON

### Configuración del Servidor

```bash
# Editar crontab
crontab -e

# Agregar la siguiente línea para ejecutar diariamente a las 8:00 AM
0 8 * * * curl -X GET https://kyz.com.ar/birthdays/backend/public/api/cron/check-birthdays >> /var/log/birthdays-cron.log 2>&1
```

### Template EmailJS

**Subject:** `🎂 Cumpleaños de Hoy - {{fecha_actual}}`

**Content:**
```html
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { background-color: #4CAF50; color: white; padding: 20px; }
        .birthday-item { padding: 10px; border-bottom: 1px solid #ddd; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🎉 Cumpleaños de Hoy</h1>
    </div>
    <div class="content">
        <p>Hoy cumplen años las siguientes personas:</p>
        {{#each cumpleanios}}
        <div class="birthday-item">
            <strong>{{this.nombre}} {{this.apellido}}</strong>
            <br>
            Fecha de nacimiento: {{this.fecha_nacimiento}}
            {{#if this.observaciones}}
            <br>
            Observaciones: {{this.observaciones}}
            {{/if}}
        </div>
        {{/each}}
    </div>
</body>
</html>
```

---

## 🔒 Seguridad

### Autenticación

| Aspecto | Implementación |
|---------|----------------|
| **Hash de contraseñas** | `bcrypt` con cost factor 10 |
| **Tokens** | JWT con expiración de 30 días |
| **Validación** | Middleware en cada request protegido |
| **Credenciales admin** | Usuario: `elopez` / Contraseña: `birthdais*` |

### Medidas de Protección

- ✅ **CORS**: Configurado para permitir solo dominios autorizados
- ✅ **SQL Injection**: Uso de prepared statements (PDO)
- ✅ **XSS**: Sanitización de inputs y outputs
- ✅ **CSRF**: Validación de tokens en formularios
- ✅ **Rate Limiting**: Límite de intentos de login
- ✅ **HTTPS**: Forzado en producción

---

## ✅ Criterios de Aceptación

### Funcionalidades Obligatorias

- [ ] Login funcional con credenciales: `elopez` / `birthdais*`
- [ ] CRUD completo de usuarios con validaciones
- [ ] CRUD completo de destinatarios con validaciones
- [ ] Paginación automática a partir de 15 registros
- [ ] Búsqueda en tiempo real por nombre y apellido
- [ ] Envío automático de emails diarios a las 8:00 AM
- [ ] Diseño responsive (mobile, tablet, desktop)
- [ ] Funcionamiento correcto en Docker local
- [ ] Configuración funcional en producción: `https://kyz.com.ar/birthdays/`

### Validaciones de Formularios

| Campo | Validación |
|-------|-----------|
| **Nombre** | Requerido, mínimo 2 caracteres, solo letras |
| **Apellido** | Requerido, mínimo 2 caracteres, solo letras |
| **Email** | Requerido, formato válido, único en destinatarios |
| **Fecha de nacimiento** | Requerida, formato válido (YYYY-MM-DD), no futura |
| **Contraseña** | Mínimo 8 caracteres (solo en cambio de admin) |

### Testing

- [ ] Pruebas manuales de todas las funcionalidades CRUD
- [ ] Verificación de envío de emails con endpoint de prueba
- [ ] Validación de responsive en dispositivos móviles
- [ ] Pruebas de autenticación y autorización
- [ ] Verificación de paginación y búsqueda

---

## 📦 Entregables

1. ✅ **Código fuente** completo en estructura especificada
2. ✅ **README.md** con instrucciones de instalación y despliegue
3. ✅ **Script SQL** (`database/init.sql`) con estructura y datos iniciales
4. ✅ **Docker Compose** funcional para desarrollo local
5. ✅ **Documentación de API** (endpoints, requests, responses)
6. ✅ **Guía de configuración** para producción
7. ✅ **Variables de entorno** de ejemplo (`.env.example`)

---

## 🚀 Comandos Rápidos

### Desarrollo Local

```bash
# Iniciar todos los servicios
docker-compose up -d

# Ver logs
docker-compose logs -f

# Detener servicios
docker-compose down

# Reconstruir contenedores
docker-compose up -d --build
```

### Accesos Locales

| Servicio | URL |
|----------|-----|
| **Frontend** | http://localhost:3000 |
| **Backend** | http://localhost:8000 |
| **phpMyAdmin** | http://localhost:8080 |
| **MySQL** | localhost:3306 |

---

## 📚 Notas Técnicas

### Formato de Respuestas API

```json
{
  "success": true,
  "data": {...},
  "message": "Operación exitosa",
  "pagination": {
    "page": 1,
    "limit": 15,
    "total": 100,
    "totalPages": 7
  }
}
```

### Formato de Errores

```json
{
  "success": false,
  "error": "Descripción del error",
  "code": "ERROR_CODE",
  "details": []
}
```
