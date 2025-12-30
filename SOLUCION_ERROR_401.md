# Solución al Error 401 - Authorization Header Not Found

## Problema Identificado

**Error:** `Access denied. Authorization header not found.`
**Código HTTP:** 401 Unauthorized

## Causa Raíz

El middleware `validate_jwt.php` estaba buscando la variable de entorno `JWT_SECRET_KEY` pero no tenía un valor por defecto (fallback). Cuando la variable de entorno no está definida, `getenv()` devuelve `false`, lo que causa que la validación del JWT falle.

---

## ✅ Solución Implementada

### Archivo Corregido: `backend/middleware/validate_jwt.php`

**Antes (línea 23):**
```php
$secret_key = getenv('JWT_SECRET_KEY');
```

**Ahora (línea 23):**
```php
$secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
```

**Cambio:** Agregado el operador `?:` con un valor por defecto que coincide con el usado en `login.php`.

---

## 🧪 Pasos para Verificar la Solución

### Paso 1: Limpiar Caché del Navegador

1. Abre el navegador en modo incógnito O
2. Limpia el localStorage:
   - Abre DevTools (F12)
   - Ve a la pestaña "Application" o "Almacenamiento"
   - En "Local Storage" → Selecciona tu dominio
   - Elimina todo haciendo clic derecho → "Clear"

### Paso 2: Cerrar Sesión y Volver a Iniciar

```bash
1. Ve a tu aplicación
2. Si estás logueado, cierra sesión
3. Vuelve a iniciar sesión con tu usuario ciudadano
4. El sistema debe redirigirte automáticamente a /ciudadano/mis-denuncias
5. ✅ Ya NO debe aparecer el error 401
```

### Paso 3: Verificar en DevTools (Opcional)

1. Abre DevTools (F12)
2. Ve a la pestaña "Network" (Red)
3. Recarga la página
4. Busca la petición a `read.php`
5. Haz clic en ella
6. Ve a "Headers" → "Request Headers"
7. ✅ Debe aparecer: `Authorization: Bearer eyJ0eXAiOiJKV1...`

---

## 🔍 Diagnóstico Adicional

Si el problema persiste, verifica lo siguiente:

### 1. Verificar que el Token se Guardó Correctamente

Abre la consola del navegador (F12) y ejecuta:

```javascript
console.log('Token:', localStorage.getItem('jwt'));
```

**Resultado Esperado:**
```
Token: eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRw...
```

**Si aparece `null`:**
- El token no se guardó después del login
- Vuelve a iniciar sesión

### 2. Verificar que el Interceptor Funciona

Abre la consola y ejecuta:

```javascript
// Ver configuración del axios
import { denunciaService } from './services/denunciaService.js';
console.log('API Client:', denunciaService);
```

### 3. Verificar Manualmente la Petición

Ejecuta esto en la consola para hacer una petición manual:

```javascript
const token = localStorage.getItem('jwt');
fetch('http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
})
.then(r => r.json())
.then(data => console.log('Respuesta:', data))
.catch(err => console.error('Error:', err));
```

**Resultado Esperado:**
```json
{
  "records": [...]
}
```

---

## 🔧 Soluciones Alternativas

### Si el Token No se Guarda Después del Login

**Problema:** El `localStorage.setItem('jwt', jwt)` no funciona

**Solución:** Verifica el archivo `Login.jsx` (línea 36):

```javascript
// Debe estar guardando el token
localStorage.setItem('jwt', jwt);

// Verifica inmediatamente después
console.log('Token guardado:', localStorage.getItem('jwt'));
```

### Si el Interceptor No Agrega el Header

**Problema:** El interceptor de axios no funciona

**Solución:** Agrega logging temporal en `denunciaService.js`:

```javascript
apiClient.interceptors.request.use(
    config => {
        const token = getAuthToken();
        console.log('Token obtenido:', token ? 'SÍ' : 'NO');
        if (token) {
            config.headers['Authorization'] = `Bearer ${token}`;
            console.log('Header agregado:', config.headers['Authorization']);
        }
        return config;
    },
    error => {
        return Promise.reject(error);
    }
);
```

### Si el Backend No Recibe el Header

**Problema:** PHP no encuentra `$_SERVER['HTTP_AUTHORIZATION']`

**Solución 1:** Verifica la configuración de Apache

Agrega en `.htaccess` del backend:

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

**Solución 2:** Modifica `validate_jwt.php` para buscar en múltiples lugares:

```php
$authHeader = $_SERVER['HTTP_AUTHORIZATION']
    ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION']
    ?? apache_request_headers()['Authorization']
    ?? null;
```

---

## 📋 Checklist de Verificación

Usa este checklist para verificar que todo funciona:

- [ ] ✅ Corregido `validate_jwt.php` con fallback del secret_key
- [ ] ✅ Limpiado caché del navegador
- [ ] ✅ Cerrado sesión y vuelto a iniciar
- [ ] ✅ Token visible en localStorage
- [ ] ✅ Peticiones a `read.php` con código 200 (no 401)
- [ ] ✅ Dashboard del ciudadano carga correctamente
- [ ] ✅ Tabla de denuncias se muestra (si hay denuncias)
- [ ] ✅ No aparece error "Authorization header not found"

---

## 🎯 Resultado Esperado

Después de aplicar la solución:

1. **Login exitoso** → Token guardado en localStorage
2. **Redirección automática** → `/ciudadano/mis-denuncias`
3. **Peticiones con Authorization** → `Bearer eyJ0eXAi...`
4. **Backend valida token** → Usuario identificado
5. **Denuncias filtradas** → Solo del usuario actual
6. **Dashboard carga** → Sin errores 401

---

## 🐛 Si el Problema Persiste

Si después de aplicar todas las soluciones el problema continúa:

### 1. Reinicia el Servidor de Desarrollo

```bash
# Frontend
cd frontend
npm run dev

# Reinicia XAMPP/Apache si es necesario
```

### 2. Verifica los Archivos Modificados

```bash
# Asegúrate de que el cambio se guardó
backend/middleware/validate_jwt.php (línea 23)
```

### 3. Revisa los Logs de PHP

```bash
# En XAMPP, revisa:
xampp/apache/logs/error.log
```

### 4. Prueba con un Usuario Nuevo

```bash
1. Registra un nuevo usuario ciudadano
2. Inicia sesión con ese usuario
3. Verifica si el problema persiste
```

---

## 📝 Notas Importantes

1. **Mismo Secret Key:** El `secret_key` debe ser EXACTAMENTE el mismo en:
   - `backend/api/auth/login.php` (para generar el JWT)
   - `backend/middleware/validate_jwt.php` (para validar el JWT)

2. **Formato del Token:** El token debe tener el formato:
   ```
   Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
   ```

3. **CORS:** Asegúrate de que CORS esté configurado correctamente en `backend/config/cors.php`

4. **Producción:** En producción, usa una variable de entorno real para `JWT_SECRET_KEY`:
   ```bash
   # En tu servidor
   export JWT_SECRET_KEY="tu_clave_super_secreta_aqui"
   ```

---

**Fecha de Solución:** 2025-12-18
**Archivo Modificado:** `backend/middleware/validate_jwt.php`
**Estado:** ✅ Corregido - Listo para pruebas
