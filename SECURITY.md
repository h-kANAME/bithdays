# 🔐 Guía de Seguridad - Sistema de Cumpleaños

## ⚠️ INFORMACIÓN CRÍTICA

**NUNCA** commitees los siguientes archivos a GitHub:
- `.env`
- `.env.local`
- `.env.production`
- Cualquier archivo `.env.*` con valores reales
- Archivos SQL con datos sensibles

## 📋 Checklist de Seguridad Antes de Hacer Push a GitHub

- [ ] Confirmar que NO hay archivos `.env` con credenciales reales
- [ ] Verificar que `.gitignore` está correctamente configurado
- [ ] Usar `git status` para asegurarse que no hay archivos `.env` listos para commit
- [ ] Si accidentalmente commiteaste credenciales, usar `git rm --cached` e invalidar las credenciales inmediatamente

## 🔧 Configuración Local

1. **Clonar el repositorio:**
   ```bash
   git clone <repo-url>
   cd bithdays
   ```

2. **Crear archivos de configuración local:**
   ```bash
   # Copiar el archivo de ejemplo
   cp .env.example .env.local
   cp backend/.env.example backend/.env.local (si existe)
   ```

3. **Configurar credenciales reales:**
   Editar `.env.local` y agregar:
   - Credenciales de base de datos
   - Claves de EmailJS
   - JWT_SECRET seguro
   - URLs correctas

## 🚀 Instalación en Servidor de Producción

1. En el servidor, copiar `.env.example` a `.env`
2. Actualizar credenciales de producción en `.env`
3. **IMPORTANTE:** Cambiar permisos del archivo `.env`:
   ```bash
   chmod 600 .env
   ```
4. Configurar variables en `docker-compose.yml` según el entorno

## 🔑 Generando un JWT_SECRET Seguro

```bash
# En Linux/Mac
openssl rand -hex 32

# En Windows PowerShell
[System.Convert]::ToHexString([System.Security.Cryptography.RandomNumberGenerator]::GetBytes(32))
```

## 📧 Credenciales de EmailJS

1. Crear cuenta en: https://www.emailjs.com/
2. Obtener:
   - `EMAILJS_SERVICE_ID`: ID del servicio de correo
   - `EMAILJS_TEMPLATE_ID`: ID del template de email
   - `EMAILJS_PUBLIC_KEY`: Clave pública (NO la clave privada)

## 🗄️ Credenciales de Base de Datos

- **Local:** Usar las credenciales del docker-compose.yml (en `.env.local`)
- **Producción:** Usar credenciales seguras del servidor de hosting

## ✅ Si Accidentalmente Commiteaste Credenciales

1. **INMEDIATAMENTE invalidar las credenciales en los servicios**
2. Ejecutar en el repositorio local:
   ```bash
   git rm --cached .env.local
   git commit -m "Remove sensitive .env.local file"
   git push
   ```
3. Cambiar todas las credenciales comprometidas

## 📌 Buenas Prácticas

- ✅ Usar `.env.example` como plantilla
- ✅ Documentar qué variables se necesitan
- ✅ Usar variables de entorno para todo lo sensible
- ✅ Revisar `.gitignore` regularmente
- ✅ Hacer un `git status` ANTES de hacer commit
- ✅ Usar `git log` para verificar que no hay credenciales en el historial

---

**Última actualización:** 2026-02-04
