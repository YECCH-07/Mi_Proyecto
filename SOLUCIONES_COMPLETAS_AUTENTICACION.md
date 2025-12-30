# 🔐 Soluciones Completas al Problema de Autenticación

## 📋 Índice
1. [Diagnóstico del Problema](#diagnóstico-del-problema)
2. [Solución 1: Configuración Apache (.htaccess)](#solución-1-configuración-apache-htaccess)
3. [Solución 2: Middleware Mejorado con Fallbacks](#solución-2-middleware-mejorado-con-fallbacks)
4. [Solución 3: Autenticación basada en Cookies](#solución-3-autenticación-basada-en-cookies)
5. [Comparación de Soluciones](#comparación-de-soluciones)
6. [Recomendación Final](#recomendación-final)

---

## 🔍 Diagnóstico del Problema

### ¿Qué estaba pasando?

```
Cliente (React) → Envía header Authorization: Bearer <token>
        ↓
Apache Server → ❌ BLOQUEA el header (configuración por defecto)
        ↓
PHP Backend → No recibe el header → Error 401
```

### Evidencia del problema

**En el navegador (Console):**
```
[Interceptor] Token encontrado: SÍ
[Interceptor] Header Authorization agregado
```

**En el servidor:**
```
Access denied. Authorization header not found
```

### Causa raíz

**Apache bloquea el header `Authorization` por defecto por razones de seguridad.** Este es un problema conocido documentado en:
- Stack Overflow: "Authorization header not being sent to PHP"
- Apache Documentation: CGI Security
- PHP Manual: $_SERVER variables

---

## ✅ Solución 1: Configuración Apache (.htaccess)

### Descripción
Forzar a Apache a pasar el header `Authorization` a PHP usando reglas de reescritura.

### Archivo creado: `backend/.htaccess`

```apache
# Configuracion para permitir que PHP reciba el header Authorization

RewriteEngine On

# Capturar el header Authorization y ponerlo disponible para PHP
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]

# Permitir CORS
Header set Access-Control-Allow-Origin "*"
Header set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header set Access-Control-Allow-Headers "Content-Type, Authorization, X-Requested-With"
```

### Cómo funciona

1. `RewriteEngine On` - Activa el módulo de reescritura de Apache
2. `RewriteCond %{HTTP:Authorization} ^(.*)` - Captura el header Authorization
3. `RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]` - Lo coloca en `$_SERVER['HTTP_AUTHORIZATION']`

### Pasos de implementación

#### Paso 1: Verificar que mod_rewrite está habilitado

**En Windows (XAMPP):**
1. Abrir `C:\xampp\apache\conf\httpd.conf`
2. Buscar la línea:
   ```apache
   #LoadModule rewrite_module modules/mod_rewrite.so
   ```
3. Quitar el `#` para descomentar:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

#### Paso 2: Permitir .htaccess en el directorio

En el mismo `httpd.conf`, buscar:
```apache
<Directory "C:/xampp/htdocs">
    AllowOverride None
</Directory>
```

Cambiar a:
```apache
<Directory "C:/xampp/htdocs">
    AllowOverride All
</Directory>
```

#### Paso 3: Reiniciar Apache

**IMPORTANTE:** Los cambios en `.htaccess` y `httpd.conf` requieren reiniciar Apache.

En XAMPP:
1. Click en "Stop" en Apache
2. Esperar 2-3 segundos
3. Click en "Start"

#### Paso 4: Verificar

Ejecutar el script de prueba:
```bash
curl -X GET http://localhost/DENUNCIA%20CIUDADANA/backend/test_validate.php \
  -H "Authorization: Bearer tu_token_aqui"
```

### Ventajas ✅
- No requiere cambios en el código
- Solución estándar y documentada
- Se aplica a todo el directorio backend
- Compatible con todos los navegadores

### Desventajas ❌
- Requiere acceso a configuración de Apache
- Requiere mod_rewrite habilitado
- Puede no funcionar en algunos hostings compartidos

---

## ✅ Solución 2: Middleware Mejorado con Fallbacks

### Descripción
Mejorar `validate_jwt.php` para buscar el header Authorization en múltiples ubicaciones.

### Archivo modificado: `backend/middleware/validate_jwt.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function validate_jwt($required_roles = []) {
    $jwt = null;
    $authHeader = null;

    // Método 1: HTTP_AUTHORIZATION (después de .htaccess)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    // Método 2: REDIRECT_HTTP_AUTHORIZATION (algunos servidores)
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Método 3: apache_request_headers (si está disponible)
    elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }
    // Método 4: getallheaders (alternativa)
    elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Authorization header not found."));
        exit();
    }

    $arr = explode(" ", $authHeader);
    $jwt = $arr[1] ?? null;

    if ($jwt) {
        try {
            $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            // Check for required roles
            if (!empty($required_roles)) {
                $user_role = $decoded->data->rol ?? null;
                if (!in_array($user_role, $required_roles)) {
                    http_response_code(403);
                    echo json_encode(array("message" => "Access forbidden."));
                    exit();
                }
            }

            return $decoded->data;

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array(
                "message" => "Access denied. Invalid token.",
                "error" => $e->getMessage()
            ));
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Token not found."));
        exit();
    }
}
```

### Cómo funciona

Intenta obtener el header Authorization en 4 formas diferentes:

1. **`$_SERVER['HTTP_AUTHORIZATION']`**
   - Funciona cuando .htaccess está configurado correctamente
   - Es la forma más común y estándar

2. **`$_SERVER['REDIRECT_HTTP_AUTHORIZATION']`**
   - Funciona en algunos servidores que usan redirecciones internas
   - Útil cuando Apache hace rewrite interno

3. **`apache_request_headers()`**
   - Función nativa de Apache
   - Accede directamente a los headers HTTP
   - Puede funcionar incluso sin .htaccess en algunos casos

4. **`getallheaders()`**
   - Alternativa más universal
   - Funciona en PHP-FPM y otros SAPIs

### Ventajas ✅
- Máxima compatibilidad con diferentes configuraciones
- No requiere cambios en httpd.conf
- Funciona como backup si .htaccess falla
- Código defensivo y robusto

### Desventajas ❌
- Más complejo
- No soluciona el problema de raíz si Apache está mal configurado
- `apache_request_headers()` puede no estar disponible en todos los servidores

---

## ✅ Solución 3: Autenticación basada en Cookies

### Descripción
**Evitar completamente el header Authorization** usando cookies HTTP-only. Esta es la solución más robusta.

### ¿Por qué cookies?

| Característica | localStorage + Header | Cookies HTTP-only |
|---------------|----------------------|-------------------|
| **Apache bloquea** | ✅ Sí (header Authorization) | ❌ No (cookies siempre pasan) |
| **Seguridad XSS** | ❌ Vulnerable | ✅ Protegido |
| **Auto-envío** | ❌ Manual con interceptor | ✅ Automático |
| **CSRF Protection** | N/A | ✅ SameSite |

### Archivos creados

#### 1. `backend/middleware/validate_jwt_cookie.php`

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function validate_jwt_cookie($required_roles = []) {
    $jwt = null;

    // Obtener token de la cookie
    if (isset($_COOKIE['jwt_token'])) {
        $jwt = $_COOKIE['jwt_token'];
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. No authentication cookie found."));
        exit();
    }

    if ($jwt) {
        try {
            $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            // Verificar roles
            if (!empty($required_roles)) {
                $user_role = $decoded->data->rol ?? null;
                if (!in_array($user_role, $required_roles)) {
                    http_response_code(403);
                    echo json_encode(array("message" => "Access forbidden."));
                    exit();
                }
            }

            return $decoded->data;

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array(
                "message" => "Access denied. Invalid token.",
                "error" => $e->getMessage()
            ));
            exit();
        }
    }
}
```

#### 2. `backend/api/usuarios/login_cookie.php`

```php
<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");  // ← CRÍTICO para cookies
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

// ... código de autenticación ...

if (password_verify($data->password, $usuario->password)) {
    $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
    $issued_at = time();
    $expiration_time = $issued_at + (60 * 60 * 24); // 24 horas

    $token = array(
        "iss" => "http://localhost",
        "aud" => "http://localhost",
        "iat" => $issued_at,
        "nbf" => $issued_at,
        "exp" => $expiration_time,
        "data" => array(
            "id" => $usuario->id,
            "nombres" => $usuario->nombres,
            "apellidos" => $usuario->apellidos,
            "email" => $usuario->email,
            "rol" => $usuario->rol
        )
    );

    $jwt = JWT::encode($token, $secret_key, 'HS256');

    // Establecer cookie HTTP-only
    setcookie(
        'jwt_token',
        $jwt,
        [
            'expires' => $expiration_time,
            'path' => '/',
            'domain' => 'localhost',
            'secure' => false,      // true solo en HTTPS
            'httponly' => true,     // ← CRÍTICO: No accesible desde JS
            'samesite' => 'Lax'     // ← Protección CSRF
        ]
    );

    http_response_code(200);
    echo json_encode(array(
        "message" => "Login successful",
        "user" => array(
            "id" => $usuario->id,
            "nombres" => $usuario->nombres,
            "apellidos" => $usuario->apellidos,
            "email" => $usuario->email,
            "rol" => $usuario->rol
        )
        // NO enviamos el JWT en el body
    ));
}
```

### Cambios en el Frontend

#### Modificar `frontend/src/services/denunciaService.js`

```javascript
const apiClient = axios.create({
    baseURL: 'http://localhost/DENUNCIA CIUDADANA/backend/api',
    headers: {
        'Content-Type': 'application/json',
    },
    withCredentials: true  // ← CRÍTICO: Permite enviar cookies
});

// Ya NO necesitas el interceptor que agrega el header Authorization
// Las cookies se envían automáticamente

export const denunciaService = {
    getDenuncias: () => apiClient.get('/denuncias/read.php'),
    // ... otros métodos
};
```

#### Modificar `frontend/src/pages/Login.jsx`

```javascript
import axios from 'axios';

const handleSubmit = async (e) => {
    e.preventDefault();
    setError(null);

    try {
        const response = await axios.post(
            'http://localhost/DENUNCIA CIUDADANA/backend/api/usuarios/login_cookie.php',
            { email, password },
            { withCredentials: true }  // ← CRÍTICO
        );

        if (response.data && response.data.user) {
            const { rol } = response.data.user;

            // Ya NO guardamos en localStorage
            // La cookie se guarda automáticamente

            // Redireccionar según rol
            switch (rol) {
                case 'admin':
                    navigate('/admin/dashboard', { replace: true });
                    break;
                case 'supervisor':
                    navigate('/supervisor/dashboard', { replace: true });
                    break;
                case 'operador':
                    navigate('/operador/dashboard', { replace: true });
                    break;
                case 'ciudadano':
                default:
                    navigate('/ciudadano/mis-denuncias', { replace: true });
                    break;
            }
        }
    } catch (err) {
        setError(err.response?.data?.message || 'Error al iniciar sesión');
    }
};
```

#### Modificar `frontend/src/hooks/useAuth.js`

```javascript
import { useState, useEffect } from 'react';
import axios from 'axios';

export const useAuth = () => {
    const [authState, setAuthState] = useState({
        isAuthenticated: false,
        userRole: null,
        userId: null,
        userName: null,
        isLoading: true
    });

    const checkAuth = async () => {
        try {
            // Hacer una petición al backend para verificar la cookie
            const response = await axios.get(
                'http://localhost/DENUNCIA CIUDADANA/backend/api/usuarios/verify_cookie.php',
                { withCredentials: true }
            );

            if (response.data && response.data.valid) {
                setAuthState({
                    isAuthenticated: true,
                    userRole: response.data.user.rol,
                    userId: response.data.user.id,
                    userName: `${response.data.user.nombres} ${response.data.user.apellidos}`,
                    isLoading: false
                });
            } else {
                setAuthState({
                    isAuthenticated: false,
                    userRole: null,
                    userId: null,
                    userName: null,
                    isLoading: false
                });
            }
        } catch (error) {
            setAuthState({
                isAuthenticated: false,
                userRole: null,
                userId: null,
                userName: null,
                isLoading: false
            });
        }
    };

    useEffect(() => {
        checkAuth();
    }, []);

    const logout = async () => {
        await axios.post(
            'http://localhost/DENUNCIA CIUDADANA/backend/api/usuarios/logout_cookie.php',
            {},
            { withCredentials: true }
        );

        setAuthState({
            isAuthenticated: false,
            userRole: null,
            userId: null,
            userName: null,
            isLoading: false
        });
    };

    return { ...authState, logout, checkAuth };
};
```

### Archivos adicionales necesarios

#### `backend/api/usuarios/verify_cookie.php`

```php
<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if (isset($_COOKIE['jwt_token'])) {
    try {
        $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
        $decoded = JWT::decode($_COOKIE['jwt_token'], new Key($secret_key, 'HS256'));

        echo json_encode(array(
            "valid" => true,
            "user" => array(
                "id" => $decoded->data->id,
                "nombres" => $decoded->data->nombres,
                "apellidos" => $decoded->data->apellidos,
                "email" => $decoded->data->email,
                "rol" => $decoded->data->rol
            )
        ));
    } catch (Exception $e) {
        echo json_encode(array("valid" => false));
    }
} else {
    echo json_encode(array("valid" => false));
}
```

#### `backend/api/usuarios/logout_cookie.php`

```php
<?php
header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json; charset=UTF-8");

// Eliminar la cookie
setcookie(
    'jwt_token',
    '',
    [
        'expires' => time() - 3600,  // Fecha en el pasado
        'path' => '/',
        'domain' => 'localhost',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]
);

echo json_encode(array("message" => "Logout successful"));
```

### Ventajas ✅
- **Evita completamente el problema de Apache** - Las cookies siempre se pasan
- **Mayor seguridad** - HTTP-only previene ataques XSS
- **Más simple** - No necesitas interceptores de Axios
- **Estándar de la industria** - Usado por grandes aplicaciones
- **Auto-envío** - El navegador envía la cookie automáticamente

### Desventajas ❌
- Requiere cambios en frontend y backend
- Necesitas configurar CORS con credentials
- En producción requiere HTTPS (secure: true)

---

## 📊 Comparación de Soluciones

| Criterio | Solución 1<br>(.htaccess) | Solución 2<br>(Middleware) | Solución 3<br>(Cookies) |
|----------|---------------------------|---------------------------|------------------------|
| **Complejidad implementación** | ⭐ Baja | ⭐⭐ Media | ⭐⭐⭐ Alta |
| **Cambios en código** | ❌ No | ✅ Sí (Backend) | ✅ Sí (Ambos) |
| **Requiere config Apache** | ✅ Sí | ❌ No | ❌ No |
| **Funciona sin .htaccess** | ❌ No | ⚠️ A veces | ✅ Sí |
| **Seguridad XSS** | ⚠️ Media | ⚠️ Media | ✅ Alta |
| **Compatible hosting compartido** | ⚠️ Depende | ✅ Sí | ✅ Sí |
| **Mantenimiento** | ⭐⭐⭐ Fácil | ⭐⭐ Medio | ⭐⭐ Medio |
| **Estándar de industria** | ✅ Sí | ✅ Sí | ✅✅ Muy usado |

---

## 🎯 Recomendación Final

### Para tu caso específico (XAMPP local):

**Implementar SOLUCIÓN 1 + SOLUCIÓN 2 juntas** (enfoque híbrido)

#### Por qué:
1. **Solución 1** (.htaccess) resuelve el problema de raíz
2. **Solución 2** (Middleware) actúa como backup por si .htaccess falla
3. Mínimos cambios en el código existente
4. No necesitas refactorizar el frontend

#### Pasos de implementación:

1. ✅ **Ya creaste** el archivo `backend/.htaccess`
2. ✅ **Ya modificaste** `backend/middleware/validate_jwt.php`
3. ⚠️ **DEBES HACER AHORA:**
   - Verificar que mod_rewrite está habilitado en `httpd.conf`
   - Cambiar `AllowOverride None` a `AllowOverride All`
   - **REINICIAR Apache**
4. 🧪 **Probar** haciendo login de nuevo

### Para producción futura:

**Migrar a SOLUCIÓN 3** (Cookies) cuando:
- Tengas tiempo para refactorizar
- Quieras máxima seguridad
- Estés listo para cambiar frontend y backend

---

## 🚀 Próximos Pasos INMEDIATOS

### 1. Verificar configuración Apache

```bash
# Abrir httpd.conf
C:\xampp\apache\conf\httpd.conf

# Buscar y descomentar:
LoadModule rewrite_module modules/mod_rewrite.so

# Buscar y cambiar:
<Directory "C:/xampp/htdocs">
    AllowOverride All  # ← Cambiar de None a All
</Directory>
```

### 2. Reiniciar Apache

En el Panel de Control de XAMPP:
1. Click "Stop" en Apache
2. Esperar 3 segundos
3. Click "Start"

### 3. Probar el login

1. Abrir http://localhost:5173
2. Hacer login
3. Abrir consola (F12)
4. Deberías ver tu dashboard sin errores 401

### 4. Si sigue fallando

Ejecutar script de diagnóstico:
```javascript
// En la consola del navegador
fetch('http://localhost/DENUNCIA%20CIUDADANA/backend/test_validate.php', {
    headers: {
        'Authorization': `Bearer ${localStorage.getItem('jwt')}`
    }
})
.then(r => r.text())
.then(console.log)
```

**Si muestra "✅ Header Authorization encontrado"** → Problema resuelto
**Si muestra "❌ ERROR: No se encontró el header"** → Necesitas revisar httpd.conf

---

## 📚 Referencias

- [Apache mod_rewrite](https://httpd.apache.org/docs/current/mod/mod_rewrite.html)
- [PHP $_SERVER](https://www.php.net/manual/en/reserved.variables.server.php)
- [MDN: HTTP Cookies](https://developer.mozilla.org/en-US/docs/Web/HTTP/Cookies)
- [JWT Best Practices](https://tools.ietf.org/html/rfc8725)
- [OWASP Authentication Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Authentication_Cheat_Sheet.html)

---

## 💡 Resumen Ejecutivo

**Problema:** Apache bloquea el header Authorization por defecto

**Solución Rápida (Recomendada):**
1. ✅ Crear `backend/.htaccess` con RewriteRule
2. ✅ Mejorar `validate_jwt.php` con fallbacks
3. ⚠️ Habilitar mod_rewrite en `httpd.conf`
4. ⚠️ Cambiar AllowOverride a All
5. ⚠️ **REINICIAR APACHE**

**Solución Robusta (Futuro):**
- Migrar a autenticación basada en cookies HTTP-only
- Mayor seguridad y compatibilidad
- Requiere refactorización frontend/backend

---

**Estado actual:** Soluciones 1 y 2 implementadas, falta reiniciar Apache y probar.
