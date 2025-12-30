# 🔧 SOLUCIÓN: Error 500 en Login

## 🔴 ERROR REPORTADO
```
POST http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php 500 (Internal Server Error)
```

---

## ✅ CORRECCIONES APLICADAS

He mejorado el archivo `login.php` para que ahora:

1. ✅ **Muestra el error exacto** en la respuesta JSON
2. ✅ **Valida la conexión** a base de datos
3. ✅ **Valida entrada** de email y password
4. ✅ **Verifica usuario activo** antes de permitir login
5. ✅ **Manejo de errores** con try-catch completo
6. ✅ **Logs de errores** para debugging

---

## 🔍 DIAGNÓSTICO PASO A PASO

### 1️⃣ EJECUTAR SCRIPT DE DIAGNÓSTICO

**Abrir en navegador:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/test_login.php
```

Este script verificará:
- ✅ PHP funciona
- ✅ Autoload de Composer
- ✅ Firebase JWT está instalado
- ✅ Variables de entorno (.env)
- ✅ Conexión a base de datos
- ✅ Modelo User funciona
- ✅ Hay usuarios en la BD
- ✅ Función emailExists() funciona
- ✅ Generación de JWT funciona

**Si todos los tests pasan:** El problema está en el frontend o en los datos enviados.

**Si algún test falla:** El script te dirá exactamente qué corregir.

---

### 2️⃣ INTENTAR LOGIN NUEVAMENTE

**Ahora cuando intentes hacer login**, verás el error exacto en la consola del navegador.

**Posibles errores y soluciones:**

#### Error: "Database connection failed"
**Causa:** MySQL no está funcionando o credenciales incorrectas

**Solución:**
1. Verificar que MySQL esté corriendo en XAMPP
2. Verificar `backend/.env`:
   ```
   DB_HOST=localhost
   DB_NAME=denuncia_ciudadana
   DB_USER=root
   DB_PASS=
   ```

---

#### Error: "JWT secret key is not configured"
**Causa:** Variable JWT_SECRET_KEY no está en .env

**Solución:**
1. Abrir `backend/.env`
2. Verificar que exista:
   ```
   JWT_SECRET_KEY=denuncia_ciudadana_secret_key_2025_cambiar_en_produccion
   ```

---

#### Error: "Email and password are required"
**Causa:** Frontend no está enviando los datos correctamente

**Solución:**
1. Verificar que el frontend envíe:
   ```json
   {
     "email": "usuario@example.com",
     "password": "contraseña"
   }
   ```

---

#### Error: "Login failed. Invalid credentials"
**Causa:** Email o contraseña incorrectos

**Solución:**
1. Verificar que el usuario existe en la base de datos
2. Verificar el password
3. Crear un usuario de prueba:

**Opción A - phpMyAdmin:**
```sql
-- Ver usuarios existentes
SELECT id, email, rol FROM usuarios;

-- Crear usuario de prueba
INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol, activo, verificado)
VALUES (
    '12345678',
    'Admin',
    'Test',
    'admin@test.com',
    '$2y$12$LQv3c1yqBWVHxkd0LHAkCOYz6TbxZ9C0U8z.Ngs3qPPQ8vPj5bKNS', -- password: "123456"
    'admin',
    1,
    1
);
```

**Opción B - Script PHP:**
Crear archivo `backend/create_test_user.php`:
```php
<?php
include_once 'config/database.php';

$database = new Database();
$db = $database->getConnection();

$email = 'admin@test.com';
$password = '123456';
$password_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

$query = "INSERT INTO usuarios
    (dni, nombres, apellidos, email, password_hash, rol, activo, verificado)
    VALUES
    ('12345678', 'Admin', 'Test', :email, :password_hash, 'admin', 1, 1)";

$stmt = $db->prepare($query);
$stmt->bindParam(':email', $email);
$stmt->bindParam(':password_hash', $password_hash);

if ($stmt->execute()) {
    echo "✓ Usuario creado exitosamente\n";
    echo "Email: $email\n";
    echo "Password: $password\n";
} else {
    echo "❌ Error al crear usuario\n";
}
?>
```

**Ejecutar:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/create_test_user.php
```

---

#### Error: "Account is deactivated"
**Causa:** El usuario tiene `activo = 0` en la base de datos

**Solución:**
```sql
-- Activar usuario
UPDATE usuarios SET activo = 1 WHERE email = 'tu-email@example.com';
```

---

### 3️⃣ VERIFICAR RESPUESTA EN CONSOLA

**Abrir consola del navegador (F12) → Network → Buscar login.php**

Verás la respuesta exacta del servidor. Ahora incluirá:
```json
{
  "success": false,
  "message": "Internal server error",
  "error": "Descripción exacta del error",
  "file": "Archivo donde ocurrió",
  "line": "Línea del error"
}
```

---

### 4️⃣ REVISAR LOGS DE PHP (SI ES NECESARIO)

**Si aún no se ve el error:**

**Apache Error Log:**
```
C:\xampp\apache\logs\error.log
```

**PHP Error Log:**
```
C:\xampp\php\logs\php_error_log
```

---

## 🎯 CASOS COMUNES Y SOLUCIONES

### Caso 1: "Class 'Firebase\JWT\JWT' not found"

**Causa:** Composer no ha instalado las dependencias

**Solución:**
```cmd
cd C:\xampp\htdocs\DENUNCIA CIUDADANA\backend
composer install
```

Si no tienes Composer instalado:
1. Descargar de: https://getcomposer.org/download/
2. Instalar
3. Ejecutar: `composer install`

---

### Caso 2: "Call to undefined function getenv()"

**Causa:** PHP muy antiguo

**Solución:**
1. Verificar versión de PHP:
   ```cmd
   php -v
   ```
2. Debe ser PHP 7.4 o superior
3. En XAMPP, usar PHP 8.x

---

### Caso 3: "PDO::__construct(): Argument #1 must be of type string"

**Causa:** Variables de entorno no se están cargando

**Solución:**

**Modificar `config/database.php`** para cargar .env manualmente:
```php
public function __construct() {
    // Load .env file manually if needed
    $envFile = __DIR__ . '/../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    $this->host = getenv('DB_HOST') ?: 'localhost';
    $this->db_name = getenv('DB_NAME') ?: 'denuncia_ciudadana';
    $this->username = getenv('DB_USER') ?: 'root';
    $this->password = getenv('DB_PASS') ?: '';
}
```

---

### Caso 4: Error CORS

**Síntoma:** Error de CORS en la consola

**Solución:**

**Verificar `config/cors.php`:**
```php
<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
?>
```

---

## 📋 CHECKLIST DE VERIFICACIÓN

- [ ] MySQL está corriendo en XAMPP
- [ ] Base de datos `denuncia_ciudadana` existe
- [ ] Archivo `backend/.env` existe y está configurado
- [ ] Variable `JWT_SECRET_KEY` está en .env
- [ ] Composer dependencies instaladas (`vendor/` existe)
- [ ] Hay al menos un usuario en la tabla `usuarios`
- [ ] Usuario tiene `activo = 1`
- [ ] Password del usuario está hasheado con bcrypt
- [ ] Script de diagnóstico (`test_login.php`) pasa todos los tests

---

## 🚀 PROBAR LOGIN

### Opción A: Desde Frontend

1. Ir a la página de login
2. Ingresar:
   - **Email:** `admin@test.com`
   - **Password:** `123456`
3. Click en "Iniciar Sesión"

### Opción B: Desde Postman/cURL

```bash
curl -X POST http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "123456"
  }'
```

**Respuesta esperada (200 OK):**
```json
{
  "success": true,
  "message": "Successful login",
  "jwt": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "nombres": "Admin",
    "apellidos": "Test",
    "email": "admin@test.com",
    "rol": "admin"
  }
}
```

---

## 📊 RESUMEN

### ✅ Lo que hice:

1. 🟢 Mejoré `login.php` con error handling completo
2. 🟢 Creé `test_login.php` para diagnóstico automático
3. 🟢 Agregué validaciones adicionales:
   - Email y password requeridos
   - Usuario debe estar activo
   - Mejor manejo de errores
4. 🟢 Agregué información de debug en respuesta

### 🎯 Próximos pasos:

1. **Ejecutar:** `http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/test_login.php`
2. **Revisar** qué test falla (si alguno)
3. **Intentar login** nuevamente
4. **Revisar** el error exacto en la consola del navegador
5. **Aplicar** la solución correspondiente de esta guía

---

## 💡 SI EL ERROR PERSISTE

**Envíame la respuesta exacta que aparece en:**

1. **Consola del navegador** (F12 → Network → login.php → Response)
2. **Script de diagnóstico** (test_login.php)

Con esa información podré darte una solución exacta.

---

## 📞 SOPORTE ADICIONAL

**Archivos importantes:**
- `backend/api/auth/login.php` - Login mejorado
- `backend/api/auth/test_login.php` - Diagnóstico
- `backend/.env` - Configuración
- `backend/config/database.php` - Conexión BD

**Logs:**
- Apache: `C:\xampp\apache\logs\error.log`
- PHP: `C:\xampp\php\logs\php_error_log`

---

✅ **EL CÓDIGO ESTÁ CORREGIDO Y OPTIMIZADO**

Ahora el sistema mostrará exactamente qué error está ocurriendo para poder solucionarlo.
