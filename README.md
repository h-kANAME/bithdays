# Sistema de Gestión de Cumpleaños

Sistema web completo para gestionar usuarios y enviar notificaciones diarias de cumpleaños por email.

## Tecnologías

- **Backend**: PHP 8.1 con arquitectura REST API
- **Frontend**: React 18 con Material MUI y Bootstrap
- **Base de Datos**: MySQL 8.0
- **Email**: EmailJS
- **Contenedores**: Docker y Docker Compose

## Estructura del Proyecto

```
birthdays/
├── backend/              # API REST en PHP
│   ├── api/             # Endpoints de la API
│   ├── config/          # Configuración
│   ├── models/          # Modelos de datos
│   ├── utils/           # Utilidades
│   └── public/          # Punto de entrada
├── frontend/            # Aplicación React
│   ├── src/
│   │   ├── components/  # Componentes React
│   │   ├── services/    # Servicios API
│   │   ├── contexts/    # Context API
│   │   └── utils/       # Utilidades
│   └── public/
├── database/            # Scripts SQL
├── docker-compose.yml   # Orquestación Docker
├── .env.local           # Variables para desarrollo local
├── .env.production      # Variables para producción
├── .env.example         # Template de variables de entorno
└── switch-env.sh        # Script para cambiar entre entornos
```

## Instalación Local con Docker

### Prerequisitos

- Docker Desktop instalado
- Git

### Pasos de Instalación

1. **Clonar el repositorio**
   ```bash
   git clone <https://github.com/h-kANAME/bithdays.git>
   cd birthdays
   ```

2. **Configurar entorno local**
   ```bash
   # Usar el script helper para configurar el entorno local
   ./switch-env.sh local

   # O manualmente:
   cp .env.local backend/.env
   cp .env.local frontend/.env
   ```

3. **Editar configuración (opcional)**
   ```bash
   cp .env.example .env
   ```

   Editar `.env` y configurar:
   - Credenciales de base de datos
   - Credenciales de EmailJS
   - JWT_SECRET (mínimo 32 caracteres)

3. **Iniciar los contenedores**
   ```bash
   docker-compose up -d
   ```

4. **Verificar que los servicios estén corriendo**
   ```bash
   docker-compose ps
   ```

5. **Acceder a la aplicación**
   - Frontend: http://localhost:3000
   - Backend API: http://localhost:8000
   - phpMyAdmin: http://localhost:8080

### Credenciales por Defecto

- **Usuario admin**: `elopez`
- **Contraseña**: `birthdais*`

## Configuración de EmailJS

1. Crear cuenta en https://www.emailjs.com/
2. Crear un servicio de email
3. Crear una plantilla con las siguientes variables:
   - `to_email`
   - `to_name`
   - `fecha_actual`
   - `cumpleanios_list`
   - `cumpleanios` (array)

4. Obtener las credenciales:
   - Service ID
   - Template ID
   - Public Key

5. Agregar las credenciales al archivo `.env`

## Endpoints de la API

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/api/auth/login` | Login |
| POST | `/api/auth/verify-token` | Verificar token |
| POST | `/api/auth/logout` | Logout |

### Usuarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/usuarios?page=1&limit=15&search=` | Listar usuarios |
| GET | `/api/usuarios/{id}` | Obtener un usuario |
| POST | `/api/usuarios` | Crear usuario |
| PUT | `/api/usuarios/{id}` | Actualizar usuario |
| DELETE | `/api/usuarios/{id}` | Eliminar usuario |

### Destinatarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/destinatarios?page=1&limit=15&search=` | Listar destinatarios |
| GET | `/api/destinatarios/{id}` | Obtener un destinatario |
| POST | `/api/destinatarios` | Crear destinatario |
| PUT | `/api/destinatarios/{id}` | Actualizar destinatario |
| DELETE | `/api/destinatarios/{id}` | Eliminar destinatario |

### CRON

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/cron/check-birthdays` | Verificar y enviar cumpleaños del día |
| GET | `/api/cron/test-email?email=test@example.com` | Enviar email de prueba |

## Configuración de CRON en Producción

### En el servidor (Linux)

```bash
# Editar crontab
crontab -e

# Agregar línea para ejecutar diariamente a las 8:00 AM
0 8 * * * curl -X GET https://kyz.com.ar/birthdays/backend/public/api/cron/check-birthdays >> /var/log/birthdays-cron.log 2>&1
```

## Despliegue en Producción

### Backend (PHP)

1. Subir carpeta `backend` al servidor
2. Configurar archivo `.env` con credenciales de producción
3. Asegurar que Apache tenga mod_rewrite habilitado
4. Configurar virtual host apuntando a `backend/public`

### Frontend (React)

1. Compilar la aplicación:
   ```bash
   cd frontend
   npm install
   npm run build
   ```

2. Subir carpeta `build` al servidor
3. Configurar servidor web para servir archivos estáticos

### Base de Datos

1. Crear base de datos en MySQL
2. Ejecutar script: `database/init.sql`
3. Configurar usuario y permisos

## Comandos Útiles

### Docker

```bash
# Iniciar servicios
docker-compose up -d

# Detener servicios
docker-compose down

# Ver logs
docker-compose logs -f

# Reconstruir contenedores
docker-compose up -d --build

# Acceder al contenedor de backend
docker exec -it birthdays_backend bash

# Acceder al contenedor de base de datos
docker exec -it birthdays_db mysql -u birthdays_user -p
```

### Base de Datos

```bash
# Backup
docker exec birthdays_db mysqldump -u birthdays_user -plocal_password birthdays_db > backup.sql

# Restore
docker exec -i birthdays_db mysql -u birthdays_user -plocal_password birthdays_db < backup.sql
```

## Desarrollo

### Backend

El backend está desarrollado en PHP puro sin frameworks, utilizando:
- PDO para acceso a base de datos
- Arquitectura MVC
- JWT para autenticación
- Prepared statements para prevenir SQL injection

### Frontend

El frontend utiliza:
- React 18 con hooks
- Context API para estado global
- Material UI para componentes
- Bootstrap para layout
- Axios para peticiones HTTP
- React Router para navegación

## Seguridad

- Contraseñas hasheadas con bcrypt (cost factor 10)
- Tokens JWT con expiración de 30 días
- Validación de inputs en backend y frontend
- Prepared statements para prevenir SQL injection
- Sanitización de datos
- CORS configurado
- Headers de seguridad

## Pruebas

### Probar API con curl

```bash
# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"usuario":"elopez","password":"birthdais*"}'

# Listar usuarios (requiere token)
curl -X GET http://localhost:8000/api/usuarios \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"

# Verificar cumpleaños del día
curl -X GET http://localhost:8000/api/cron/check-birthdays
```

## Troubleshooting

### Error de conexión a la base de datos

- Verificar que el contenedor de MySQL esté corriendo
- Verificar credenciales en `.env`
- Esperar a que MySQL termine de inicializar (puede tomar 30 segundos)

### Frontend no conecta con backend

- Verificar que REACT_APP_API_URL en `.env` sea correcto
- Verificar configuración de CORS en `backend/config/cors.php`
- Verificar que el backend esté corriendo

### Emails no se envían

- Verificar configuración de EmailJS en `.env`
- Probar endpoint `/api/cron/test-email`
- Verificar logs del navegador o servidor

## Licencia

Propietario

## Soporte

Para soporte, contactar al administrador del sistema.
