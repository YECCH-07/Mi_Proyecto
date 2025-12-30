# Guía de Despliegue - Sistema de Denuncias Ciudadanas

Esta guía te ayudará a desplegar tu aplicación en un hosting con cPanel (o similar) que soporte PHP y MySQL.

## Requisitos del Hosting

- **PHP**: 7.4 o superior
- **MySQL**: 5.7 o superior
- **Extensiones PHP necesarias**:
  - mysqli
  - json
  - pdo_mysql
- **Espacio en disco**: Mínimo 500 MB
- **Acceso**: FTP/SFTP o File Manager

---

## PASO 1: Preparar el Backend

### 1.1 Crear Base de Datos en el Hosting

1. Accede a **cPanel** de tu hosting
2. Ve a **MySQL® Databases**
3. Crea una nueva base de datos:
   - Nombre: `denuncia_ciudadana` (o el que prefieras)
4. Crea un usuario MySQL:
   - Usuario: `denuncia_user`
   - Contraseña: (genera una segura)
5. Asigna el usuario a la base de datos con **TODOS los privilegios**
6. **Anota estos datos** (los necesitarás después)

### 1.2 Importar el Schema de la Base de Datos

1. Ve a **phpMyAdmin** en cPanel
2. Selecciona tu base de datos
3. Click en la pestaña **Importar**
4. Sube el archivo: `database/schema.sql`
5. Click en **Continuar**
6. (Opcional) Importa también: `database/seed_data.sql` para datos de prueba

### 1.3 Configurar Variables de Entorno

1. En la carpeta `backend/`, crea un archivo `.env`:

```env
# Configuración de Base de Datos (EDITA ESTOS VALORES)
DB_HOST=localhost
DB_NAME=tu_usuario_denuncia_ciudadana
DB_USER=tu_usuario_denuncia_user
DB_PASS=tu_contraseña_segura

# JWT Secret Key (CAMBIA ESTO POR UNA CLAVE ALEATORIA)
JWT_SECRET_KEY=tu_clave_secreta_muy_larga_y_aleatoria_123456789

# Configuración de Entorno
ENVIRONMENT=production
```

**IMPORTANTE**: Reemplaza:
- `tu_usuario_denuncia_ciudadana` con el nombre completo de tu BD (ej: `usuario_denuncia_ciudadana`)
- `tu_usuario_denuncia_user` con tu usuario MySQL completo
- `tu_contraseña_segura` con la contraseña que creaste
- `tu_clave_secreta...` con una cadena aleatoria larga

### 1.4 Subir Archivos del Backend

**Opción A: FTP/SFTP (FileZilla, etc.)**
1. Conecta a tu hosting vía FTP
2. Navega a la carpeta `public_html` (o `www`)
3. Crea una carpeta llamada `api`
4. Sube TODO el contenido de la carpeta `backend/` a `public_html/api/`

**Opción B: File Manager de cPanel**
1. Abre File Manager en cPanel
2. Ve a `public_html`
3. Crea carpeta `api`
4. Sube un ZIP de la carpeta `backend`
5. Extrae el ZIP dentro de `api/`

**Estructura final en el servidor:**
```
public_html/
├── api/
│   ├── api/
│   ├── config/
│   ├── middleware/
│   ├── models/
│   ├── uploads/
│   ├── vendor/
│   ├── .env
│   ├── .htaccess
│   └── composer.json
```

### 1.5 Configurar Permisos

En File Manager o por FTP, establece estos permisos:
- Carpeta `uploads/`: **755** o **777** (para que PHP pueda escribir)
- Archivo `.env`: **644** (solo lectura para seguridad)

---

## PASO 2: Preparar y Desplegar el Frontend

### 2.1 Actualizar URL del API

El archivo `frontend/.env.production` ya está configurado con tu dominio:

```env
VITE_API_URL=https://denunciaciudadana.muniprovincialcotabambas.gob.pe/api/api
```

✅ Este archivo ya está listo para usar.

### 2.2 Construir el Frontend para Producción

En tu computadora local, ejecuta:

```bash
cd frontend
npm run build
```

Esto creará una carpeta `dist/` con los archivos optimizados.

### 2.3 Subir el Frontend

**Opción A: FTP/SFTP**
1. Sube TODO el contenido de `frontend/dist/` a `public_html/`

**Opción B: File Manager**
1. Comprime la carpeta `dist/` en un ZIP
2. Sube el ZIP a `public_html/`
3. Extráelo
4. Mueve los archivos de dentro de `dist/` directamente a `public_html/`

**Estructura final:**
```
public_html/
├── assets/
├── images/
├── index.html
├── logo-municipalidad.png
├── api/
└── ...
```

### 2.4 Crear archivo .htaccess para el Frontend

Crea `public_html/.htaccess`:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /

  # Redirigir a HTTPS (opcional pero recomendado)
  RewriteCond %{HTTPS} off
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

  # Redirigir todo a index.html excepto archivos reales
  RewriteRule ^index\.html$ - [L]
  RewriteCond %{REQUEST_FILENAME} !-f
  RewriteCond %{REQUEST_FILENAME} !-d
  RewriteRule . /index.html [L]
</IfModule>

# Habilitar compresión
<IfModule mod_deflate.c>
  AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript application/json
</IfModule>

# Habilitar caché
<IfModule mod_expires.c>
  ExpiresActive On
  ExpiresByType image/jpg "access plus 1 year"
  ExpiresByType image/jpeg "access plus 1 year"
  ExpiresByType image/png "access plus 1 year"
  ExpiresByType text/css "access plus 1 month"
  ExpiresByType application/javascript "access plus 1 month"
</IfModule>
```

---

## PASO 3: Verificación y Pruebas

### 3.1 Probar el Backend

Visita en tu navegador:
```
https://denunciaciudadana.muniprovincialcotabambas.gob.pe/api/api/test.php
```

Deberías ver un mensaje de conexión exitosa.

### 3.2 Probar el Frontend

1. Visita: `https://denunciaciudadana.muniprovincialcotabambas.gob.pe`
2. Deberías ver la página principal con el carrusel de imágenes
3. Prueba registrar una cuenta
4. Prueba iniciar sesión
5. Prueba crear una denuncia

### 3.3 Verificar Conexión Frontend-Backend

1. Abre las **Herramientas de Desarrollador** (F12)
2. Ve a la pestaña **Network**
3. Intenta hacer login
4. Verifica que las peticiones a `/api/api/auth/login.php` funcionen
5. Si ves errores de CORS, revisa el archivo `.htaccess` del backend

---

## PASO 4: Configuración Adicional (Opcional)

### 4.1 Habilitar HTTPS/SSL

1. En cPanel, busca **SSL/TLS Status**
2. Activa **AutoSSL** (gratis) para tu dominio
3. Espera 5-10 minutos para que se active

### 4.2 Configurar Email (para notificaciones)

Si quieres enviar emails:
1. Crea cuentas de email en cPanel
2. Configura SMTP en el backend (requiere código adicional)

### 4.3 Optimización

- Habilita **Gzip Compression** en cPanel
- Activa **HTTP/2** si está disponible
- Configura **CloudFlare** para CDN (opcional)

---

## Solución de Problemas Comunes

### Error: "Access denied for user"
- Verifica usuario y contraseña en `.env`
- Asegúrate de usar el nombre completo de usuario (ej: `cpanel_usuario`)

### Error 500 en el Backend
- Revisa logs de PHP en cPanel → Error Logs
- Verifica que las extensiones PHP estén instaladas
- Comprueba permisos de carpetas

### Error CORS
- Verifica que el `.htaccess` del backend esté configurado
- En cPanel → MultiPHP INI Editor, habilita `allow_url_fopen`

### Frontend muestra página en blanco
- Verifica que VITE_API_URL apunte a la URL correcta
- Revisa la consola del navegador (F12) para errores
- Asegúrate de haber copiado TODOS los archivos de `dist/`

### Las imágenes no cargan
- Verifica que la carpeta `public_html/images/` exista
- Comprueba que las imágenes se hayan subido

---

## Mantenimiento

### Backups
1. **Base de datos**: Exporta desde phpMyAdmin cada semana
2. **Archivos**: Descarga carpeta `uploads/` regularmente
3. **Código**: Mantén actualizado tu repositorio Git

### Actualizaciones
Cuando hagas cambios:
1. Haz cambios en local
2. Prueba en local (XAMPP)
3. Sube solo los archivos modificados
4. Si cambias el frontend, haz `npm run build` y sube el nuevo `dist/`

---

## URLs Importantes

- **Sitio Principal**: https://denunciaciudadana.muniprovincialcotabambas.gob.pe
- **API**: https://denunciaciudadana.muniprovincialcotabambas.gob.pe/api/api
- **cPanel**: https://muniprovincialcotabambas.gob.pe:2083 (o el que te proporcione tu hosting)
- **phpMyAdmin**: (accesible desde cPanel)

---

## Contacto y Soporte

Si tienes problemas:
1. Revisa los logs de error en cPanel
2. Verifica la consola del navegador (F12)
3. Consulta con el soporte de tu hosting si es problema del servidor

---

¡Tu Sistema de Denuncias Ciudadanas está listo para usar! 🎉
