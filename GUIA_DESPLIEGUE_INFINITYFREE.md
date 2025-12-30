# Guía de Despliegue en InfinityFree - Sistema de Denuncias Ciudadanas

Esta guía te ayudará a desplegar tu aplicación **GRATIS** en InfinityFree en menos de 15 minutos.

## ¿Por qué InfinityFree?

- ✅ **100% Gratis** - Sin costo, sin tarjeta de crédito
- ✅ **PHP + MySQL** - Todo lo que necesitas para el backend
- ✅ **cPanel incluido** - Interfaz familiar y fácil
- ✅ **Sin anuncios** - Tu sitio sin publicidad forzada
- ✅ **SSL gratis** - HTTPS incluido
- ✅ **5GB de espacio** - Más que suficiente

---

## PASO 1: Crear Cuenta en InfinityFree

### 1.1 Registro

1. Ve a: **https://infinityfree.net**
2. Click en **"Sign Up"** (arriba derecha)
3. Completa el formulario:
   - Email (usa uno real, te enviarán confirmación)
   - Contraseña
4. Click en **"Create Account"**
5. **Revisa tu email** y confirma tu cuenta

### 1.2 Crear tu Sitio

1. Inicia sesión en InfinityFree
2. Click en **"Create Account"** (crear cuenta de hosting)
3. Elige un subdominio temporal o usa tu dominio propio
4. **IMPORTANTE**: Si ya tienes tu dominio `denunciaciudadana.muniprovincialcotabambas.gob.pe`:
   - Selecciona **"Use your own domain"**
   - Ingresa: `denunciaciudadana.muniprovincialcotabambas.gob.pe`
   - Sigue las instrucciones DNS que te darán (más adelante)
5. Acepta los términos y click en **"Create Account"**

**Espera 5-10 minutos** mientras se activa tu cuenta de hosting.

---

## PASO 2: Configurar Base de Datos

### 2.1 Acceder a cPanel

1. En tu panel de InfinityFree, click en **"Control Panel"** (cPanel)
2. Se abrirá el cPanel de tu sitio

### 2.2 Crear Base de Datos MySQL

1. En cPanel, busca **"MySQL Databases"**
2. En la sección **"Create New Database"**:
   - Database Name: `denuncia_ciudadana`
   - Click **"Create Database"**
3. **Anota el nombre completo** que te da (ejemplo: `epiz_12345678_denuncia_ciudadana`)

### 2.3 Crear Usuario MySQL

1. En la misma página, sección **"MySQL Users"**:
   - Username: `denuncia_user`
   - Password: (genera una fuerte o usa una que recuerdes)
   - Click **"Create User"**
2. **Anota el usuario completo** (ejemplo: `epiz_12345678_denuncia_user`)
3. **Anota la contraseña**

### 2.4 Asignar Usuario a la Base de Datos

1. En la sección **"Add User To Database"**:
   - Selecciona el usuario que creaste
   - Selecciona la base de datos que creaste
   - Click **"Add"**
2. Marca **"ALL PRIVILEGES"** (todos los privilegios)
3. Click **"Make Changes"**

### 2.5 Importar el Schema

1. En cPanel, busca **"phpMyAdmin"**
2. Click para abrirlo
3. En el panel izquierdo, selecciona tu base de datos (`epiz_xxxxx_denuncia_ciudadana`)
4. Click en la pestaña **"Import"**
5. Click **"Choose File"** y selecciona: `database/schema.sql`
6. Scroll hasta abajo y click **"Go"**
7. Deberías ver: "Import has been successfully finished"
8. **(Opcional)** Repite el proceso para importar `database/seed_data.sql` (datos de prueba)

---

## PASO 3: Subir el Backend

### 3.1 Preparar el archivo .env

**En tu computadora local**, edita el archivo `backend/.env`:

```env
# IMPORTANTE: Usa los datos exactos que InfinityFree te dio

# Configuración de Base de Datos
DB_HOST=localhost
DB_NAME=epiz_XXXXXXXX_denuncia_ciudadana
DB_USER=epiz_XXXXXXXX_denuncia_user
DB_PASS=tu_contraseña_mysql

# JWT Secret Key (cámbiala por algo aleatorio y largo)
JWT_SECRET_KEY=mi_clave_super_secreta_123456789_xyz_abc

# Configuración de Entorno
ENVIRONMENT=production
```

**Reemplaza**:
- `epiz_XXXXXXXX_denuncia_ciudadana` → Tu nombre completo de BD
- `epiz_XXXXXXXX_denuncia_user` → Tu usuario completo
- `tu_contraseña_mysql` → La contraseña que creaste
- `mi_clave_super_secreta...` → Una cadena aleatoria larga

### 3.2 Comprimir el Backend

1. En tu computadora, ve a la carpeta `backend/`
2. Selecciona **TODO** el contenido (no la carpeta, el contenido)
3. Clic derecho → **Comprimir** / **Add to archive**
4. Crea un archivo ZIP llamado `backend.zip`

### 3.3 Subir al Servidor

1. En cPanel, abre **"File Manager"**
2. Navega a la carpeta **`htdocs`** (no `public_html`, InfinityFree usa `htdocs`)
3. Crea una nueva carpeta llamada **`api`**:
   - Click en **"+ Folder"** (arriba)
   - Nombre: `api`
   - Click **"Create New Folder"**
4. Entra a la carpeta `api`
5. Click en **"Upload"** (arriba)
6. Sube el archivo `backend.zip`
7. Espera a que termine la carga
8. Vuelve al File Manager
9. Encuentra `backend.zip`, click derecho → **"Extract"**
10. Click **"Extract Files"**
11. **Elimina** el archivo `backend.zip` (ya no lo necesitas)

**Estructura final en `htdocs/`:**
```
htdocs/
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

### 3.4 Configurar Permisos

1. En File Manager, entra a `htdocs/api/`
2. Click derecho en la carpeta **`uploads`** → **"Change Permissions"**
3. Marca todas las casillas (777)
4. Click **"Change Permissions"**

---

## PASO 4: Subir el Frontend

### 4.1 Construir el Frontend

**En tu computadora local**, ejecuta:

```bash
cd frontend
npm run build
```

Esto creará la carpeta `frontend/dist/` con los archivos optimizados.

### 4.2 Comprimir el Dist

1. Entra a la carpeta `frontend/dist/`
2. Selecciona **TODO** el contenido (no la carpeta dist, sino su contenido)
3. Crea un ZIP llamado `frontend.zip`

### 4.3 Subir al Servidor

1. En cPanel → **File Manager**
2. Ve a la carpeta **`htdocs`** (raíz)
3. Click **"Upload"**
4. Sube `frontend.zip`
5. Espera a que termine
6. Vuelve al File Manager
7. Click derecho en `frontend.zip` → **"Extract"**
8. **Elimina** el `frontend.zip`

**Estructura final en `htdocs/`:**
```
htdocs/
├── assets/
├── images/
├── index.html
├── logo-municipalidad.png
├── api/
└── ...
```

### 4.4 Crear .htaccess del Frontend

1. En File Manager, asegúrate de estar en `htdocs/`
2. Click en **"+ File"**
3. Nombre: `.htaccess`
4. Click derecho en `.htaccess` → **"Edit"**
5. Pega este contenido:

```apache
<IfModule mod_rewrite.c>
  RewriteEngine On
  RewriteBase /

  # Redirigir a HTTPS
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
```

6. Click **"Save Changes"**

---

## PASO 5: Configurar tu Dominio (Si usas tu propio dominio)

Si elegiste usar tu dominio `denunciaciudadana.muniprovincialcotabambas.gob.pe`:

### 5.1 Obtener Nameservers de InfinityFree

1. En tu panel de InfinityFree, ve a **"Account Settings"**
2. Busca los **Nameservers** (algo como):
   - `ns1.byet.org`
   - `ns2.byet.org`
   - `ns3.byet.org`
   - `ns4.byet.org`
   - `ns5.byet.org`

### 5.2 Configurar DNS en tu Proveedor de Dominio

1. Inicia sesión donde compraste/administras `muniprovincialcotabambas.gob.pe`
2. Busca la sección **DNS Management** o **Nameservers**
3. Cambia los nameservers por los de InfinityFree
4. Guarda los cambios

**IMPORTANTE**: Los cambios DNS pueden tardar 24-48 horas en propagarse.

---

## PASO 6: Probar tu Sitio

### 6.1 Verificar el Backend

Visita (reemplaza con tu dominio):
```
https://tu-subdominio.infinityfreeapp.com/api/api/test.php
```

O si usas tu dominio:
```
https://denunciaciudadana.muniprovincialcotabambas.gob.pe/api/api/test.php
```

Deberías ver un mensaje de conexión exitosa.

### 6.2 Verificar el Frontend

1. Visita tu sitio principal
2. Deberías ver el carrusel de imágenes de fondo
3. Prueba:
   - Registrarte
   - Iniciar sesión
   - Crear una denuncia

### 6.3 Revisar Errores

Si algo falla:
1. Presiona **F12** para abrir las herramientas de desarrollador
2. Ve a la pestaña **Console**
3. Busca errores en rojo
4. Si ves errores de conexión al API, verifica:
   - El archivo `.env` del backend tenga los datos correctos
   - El archivo `frontend/.env.production` tenga la URL correcta

---

## URLs Importantes

Una vez desplegado:

**Si usas subdominio temporal de InfinityFree:**
- **Sitio**: `https://tu-subdominio.infinityfreeapp.com`
- **API**: `https://tu-subdominio.infinityfreeapp.com/api/api`

**Si usas tu dominio propio:**
- **Sitio**: `https://denunciaciudadana.muniprovincialcotabambas.gob.pe`
- **API**: `https://denunciaciudadana.muniprovincialcotabambas.gob.pe/api/api`

**Paneles de administración:**
- **Panel InfinityFree**: https://infinityfree.net/clientarea.php
- **cPanel**: (link desde tu panel de InfinityFree)
- **phpMyAdmin**: (dentro de cPanel)

---

## Limitaciones de InfinityFree (a tener en cuenta)

- **Límite de consultas**: 50,000 hits diarios (suficiente para inicio)
- **Sin soporte para envío de emails** (necesitarás un servicio externo como SendGrid)
- **CPU/RAM limitados**: Para uso moderado está bien
- **Timeout de 60 segundos**: Las operaciones largas pueden fallar

Si tu app crece mucho, considera migrar a un hosting de pago.

---

## Solución de Problemas

### Error 403 Forbidden
- Verifica que exista `index.html` en `htdocs/`
- Verifica permisos de archivos (644 para archivos, 755 para carpetas)

### Error 500 Internal Server Error
- Revisa el archivo `.htaccess`
- Verifica que el `.env` del backend esté correcto
- En cPanel → Error Logs, revisa qué error específico está ocurriendo

### "Cannot connect to database"
- Verifica que el nombre de BD, usuario y contraseña en `.env` sean exactos
- Asegúrate de usar el nombre COMPLETO (con el prefijo `epiz_XXXXXXXX_`)
- Verifica que el usuario tenga permisos sobre la base de datos

### Las imágenes no cargan
- Asegúrate de haber subido la carpeta `images/` a `htdocs/`
- Verifica que `logo-municipalidad.png` esté en `htdocs/`

### Frontend funciona pero backend no responde
- Verifica que VITE_API_URL en `frontend/.env.production` apunte a la URL correcta
- Haz `npm run build` de nuevo si cambiaste el .env.production
- Sube el nuevo `dist/` al servidor

---

## Mantenimiento

### Hacer Backups

**Base de datos:**
1. cPanel → phpMyAdmin
2. Selecciona tu base de datos
3. Click en **"Export"**
4. Click en **"Go"**
5. Descarga el archivo .sql

**Archivos subidos (denuncias):**
1. File Manager → `htdocs/api/uploads/`
2. Selecciona todo
3. Click derecho → **"Compress"**
4. Descarga el ZIP

**Código:**
- Mantén tu repositorio Git actualizado
- Push regularmente tus cambios

### Actualizar el Sitio

Cuando hagas cambios:

**Backend:**
1. Edita archivos en local
2. Prueba en XAMPP
3. Sube solo los archivos modificados por FTP o File Manager

**Frontend:**
1. Edita archivos en local
2. Ejecuta `npm run build`
3. Sube el nuevo contenido de `dist/` a `htdocs/`

---

## Siguientes Pasos

Una vez que tu sitio esté funcionando:

1. ✅ **Prueba exhaustivamente** todas las funcionalidades
2. ✅ **Crea un usuario administrador** desde phpMyAdmin
3. ✅ **Configura backups automáticos** (semanales)
4. ✅ **Monitorea el uso** de hits diarios en InfinityFree
5. ✅ **Documenta** tus credenciales de forma segura

---

¡Tu Sistema de Denuncias Ciudadanas está ahora en la nube, completamente gratis! 🎉

Si tienes problemas, revisa primero los logs de error en cPanel. La mayoría de problemas son por configuración incorrecta del `.env` o permisos de archivos.
