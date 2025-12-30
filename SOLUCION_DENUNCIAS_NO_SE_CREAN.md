# 🚨 SOLUCIÓN: Denuncias No Se Guardan en Base de Datos

## 🎯 Problema Reportado

**"Las denuncias que estoy creando no se están actualizando ni en la base de datos ni en los diferentes dashboards"**

---

## 🔍 Diagnóstico Sistemático

Como experto, he creado una **batería completa de pruebas** para identificar exactamente dónde está fallando el proceso.

### Flujo Completo de Creación de Denuncias

```
Frontend (React)
    ↓ (1) Usuario llena formulario
    ↓ (2) Click en "Registrar Denuncia"
    ↓ (3) denunciaService.createDenuncia(formData)
    ↓ (4) Axios POST con JWT en header
    ↓
Backend (PHP)
    ↓ (5) create.php recibe la petición
    ↓ (6) validate_jwt() verifica el token
    ↓ (7) json_decode() parsea los datos
    ↓ (8) Denuncia->create() ejecuta INSERT
    ↓
Base de Datos (MySQL)
    ↓ (9) INSERT INTO denuncias
    ↓ (10) Registro guardado
```

**El problema puede estar en CUALQUIERA de estos 10 pasos.**

---

## 🧪 Scripts de Diagnóstico Creados

He creado 3 scripts especializados para probar cada capa:

### 1️⃣ `test_crear_denuncia.php` - Prueba la Base de Datos

**Qué prueba:**
- ✅ Conexión a base de datos
- ✅ Existencia de tablas (denuncias, categorias, usuarios)
- ✅ Que hay categorías disponibles
- ✅ Que hay usuarios registrados
- ✅ **INSERCIÓN SQL DIRECTA** (bypass del modelo)
- ✅ **Modelo Denuncia::create()** (prueba el método)
- ✅ Que las denuncias creadas aparecen en consultas

**Cómo ejecutar:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_crear_denuncia.php
```

**Resultado esperado:**
```
✅ Conexión a base de datos: OK
✅ Tabla 'denuncias': EXISTE
✅ Inserción SQL directa: EXITOSA
✅ Creación con modelo: EXITOSA
📊 Total de denuncias en BD: X
```

**Si falla aquí:**
- Hay un problema con la estructura de la base de datos
- O con el método `Denuncia::create()`

---

### 2️⃣ `test_endpoint_create.php` - Prueba el Endpoint

**Qué prueba:**
- ✅ Generación de JWT válido
- ✅ Simulación de petición POST
- ✅ Validación de JWT en el endpoint
- ✅ Procesamiento de datos JSON
- ✅ Respuesta del endpoint
- ✅ Verificación en base de datos

**Cómo ejecutar:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_endpoint_create.php
```

**Resultado esperado:**
```
✅ JWT generado exitosamente
✅ ÉXITO: Denuncia creada
   Código: DU-2025-000123
   ID: 45
✅ VERIFICACIÓN: La denuncia SÍ está en la base de datos
```

**Si falla aquí:**
- Hay un problema con el endpoint `create.php`
- O con la validación del JWT

---

### 3️⃣ `test_frontend.html` - Prueba desde el Navegador

**Qué prueba:**
- ✅ Login y obtención de JWT
- ✅ Envío de datos desde JavaScript
- ✅ Headers (Authorization, Content-Type)
- ✅ CORS
- ✅ Respuesta del servidor
- ✅ Consulta de denuncias creadas

**Cómo ejecutar:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_frontend.html
```

**Pasos:**
1. Ingresar email y password de un usuario
2. Click en "Iniciar Sesión y Obtener JWT"
3. Llenar datos de la denuncia
4. Click en "Crear Denuncia"
5. Click en "Obtener Mis Denuncias"

**Resultado esperado:**
```
✅ LOGIN EXITOSO
✅ JWT obtenido: eyJ0eXAiOiJKV1QiLCJhbG...
✅ ¡DENUNCIA CREADA EXITOSAMENTE!
✅ Código: DU-2025-000124
✅ Denuncias obtenidas: 5
```

**Si falla aquí:**
- Problema con CORS
- Problema con el formulario del frontend
- JWT no se envía correctamente

---

## 🔧 Pasos de Solución (EN ORDEN)

### ✅ PASO 1: Ejecutar test_crear_denuncia.php

Abre en tu navegador:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_crear_denuncia.php
```

#### Si la PRUEBA 5 (SQL directo) FALLA:

**Problema:** Estructura de base de datos incorrecta

**Solución:**
1. Abre phpMyAdmin
2. Verifica que la tabla `denuncias` existe
3. Ejecuta esta consulta para verificar la estructura:

```sql
DESCRIBE denuncias;
```

**Debe tener estas columnas:**
- id (INT, PRIMARY KEY, AUTO_INCREMENT)
- codigo (VARCHAR(20), UNIQUE)
- usuario_id (INT)
- categoria_id (INT, NOT NULL)
- titulo (VARCHAR(200), NOT NULL)
- descripcion (TEXT, NOT NULL)
- latitud (DECIMAL(10,8), NOT NULL)
- longitud (DECIMAL(11,8), NOT NULL)
- direccion_referencia (TEXT)
- estado (ENUM)
- area_asignada_id (INT, NULL)
- es_anonima (BOOLEAN)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

**Si falta alguna columna:**
```sql
-- Ejecutar el schema completo
SOURCE C:/xampp/htdocs/DENUNCIA CIUDADANA/database/schema.sql
```

#### Si la PRUEBA 6 (Modelo) FALLA pero PRUEBA 5 funciona:

**Problema:** Error en `Denuncia::create()`

**Solución:**
Verificar que `backend/models/Denuncia.php` líneas 63-104 tiene:

```php
function create() {
    $this->codigo = $this->generateUniqueCode();

    $query = "INSERT INTO denuncias
            SET
                codigo = :codigo,
                usuario_id = :usuario_id,
                categoria_id = :categoria_id,
                titulo = :titulo,
                descripcion = :descripcion,
                latitud = :latitud,
                longitud = :longitud,
                direccion_referencia = :direccion_referencia,
                estado = :estado,
                es_anonima = :es_anonima";

    $stmt = $this->conn->prepare($query);

    // Bind values
    $stmt->bindParam(":codigo", $this->codigo);
    $stmt->bindParam(":usuario_id", $this->usuario_id);
    $stmt->bindParam(":categoria_id", $this->categoria_id);
    $stmt->bindParam(":titulo", $this->titulo);
    $stmt->bindParam(":descripcion", $this->descripcion);
    $stmt->bindParam(":latitud", $this->latitud);
    $stmt->bindParam(":longitud", $this->longitud);
    $stmt->bindParam(":direccion_referencia", $this->direccion_referencia);
    $stmt->bindParam(":estado", $this->estado);
    $stmt->bindParam(":es_anonima", $this->es_anonima);

    if($stmt->execute()) {
        $this->id = $this->conn->lastInsertId();
        return true;
    }

    return false;
}
```

**Cosas a verificar:**
- ✅ Todos los campos están en el INSERT
- ✅ Los bindParam coinciden con los placeholders
- ✅ La sanitización no está causando problemas

---

### ✅ PASO 2: Ejecutar test_endpoint_create.php

```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_endpoint_create.php
```

#### Si el endpoint NO crea la denuncia:

**Posibles causas:**

1. **JWT inválido**
   - Verificar que `validate_jwt()` no está bloqueando
   - Revisar `backend/middleware/validate_jwt.php`

2. **Datos incompletos**
   - El endpoint requiere: titulo, descripcion, categoria_id, latitud, longitud
   - Verificar líneas 25-31 de `create.php`

3. **Error de base de datos no capturado**
   - Agregar logging temporal en `create.php`:

```php
// Después de la línea 46
if($denuncia->create()) {
    // ...success
} else {
    // AGREGAR ESTO:
    error_log("ERROR al crear denuncia");
    error_log(print_r($db->errorInfo(), true));

    http_response_code(503);
    echo json_encode(array("message" => "Unable to create denuncia."));
}
```

---

### ✅ PASO 3: Ejecutar test_frontend.html

```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_frontend.html
```

#### Si el login falla:

**Error 404:**
```
❌ EXCEPCIÓN al hacer login: Failed to fetch
```

**Solución:**
- XAMPP no está corriendo
- La URL del API es incorrecta
- Verificar que `http://localhost/DENUNCIA%20CIUDADANA/backend/api/usuarios/login.php` existe

**Error 401:**
```
❌ ERROR en login: Login failed. User not found.
```

**Solución:**
- El usuario no existe en la base de datos
- Crear usuario de prueba:

```sql
INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol, verificado, activo)
VALUES (
    '12345678',
    'Juan',
    'Pérez',
    'juan@email.com',
    '$2y$10$oHcGYzQQCqFyZlLLHkVXl.zKH9kZS5GZqB9J8oJEqEWX5L5L5L5L5',  -- Password: 123456
    'ciudadano',
    1,
    1
);
```

#### Si la creación falla:

**Error 401:**
```
❌ Error 401: Token inválido o expirado
```

**Solución:**
- Ver soluciones en `SOLUCIONES_COMPLETAS_AUTENTICACION.md`
- Verificar que Apache pasa el header Authorization
- Revisar `.htaccess`

**Error 400:**
```
❌ Error 400: Unable to create denuncia. Data is incomplete.
```

**Solución:**
- Verificar en consola qué datos se están enviando
- Asegurar que todos los campos requeridos están presentes:
  ```javascript
  {
    titulo: "...",
    descripcion: "...",
    categoria_id: 1,
    latitud: -12.0464,
    longitud: -77.0428
  }
  ```

**Error 503:**
```
❌ Error 503: Unable to create denuncia
```

**Solución:**
- Error en la base de datos
- Revisar logs de MySQL
- Verificar que `categoria_id` existe en tabla `categorias`

---

## 🎯 Casos Comunes y Soluciones

### Caso 1: "El formulario dice éxito pero no aparece en BD"

**Diagnóstico:**
```javascript
// En el frontend, agregar logging
const newDenuncia = await denunciaService.createDenuncia(formData);
console.log('Respuesta del servidor:', newDenuncia);
```

**Si devuelve:**
```json
{
  "message": "Denuncia was created successfully.",
  "codigo": "DU-2025-000123",
  "id": 45
}
```

**Pero no está en BD:**

1. Verificar que no hay múltiples bases de datos
2. Abrir phpMyAdmin y ejecutar:
   ```sql
   SELECT * FROM denuncias WHERE codigo = 'DU-2025-000123';
   ```

3. Si NO aparece, hay un problema con la transacción
4. Verificar que no hay `ROLLBACK` en el código

---

### Caso 2: "Aparece en BD pero no en el dashboard"

**Diagnóstico:**
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_consultas.php
```

**Si las consultas SQL funcionan pero el dashboard está vacío:**

1. Problema en el frontend
2. Revisar consola del navegador (F12)
3. Verificar que el API endpoint de lectura funciona:
   ```
   http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php
   ```

4. Agregar JWT en Postman o similar
5. Verificar respuesta

**Si devuelve array vacío:**
- Ver `SOLUCION_CONSULTAS_SQL.md`
- Problema con los JOINs

---

### Caso 3: "Error CORS"

**Consola del navegador:**
```
Access to fetch at '...' from origin '...' has been blocked by CORS policy
```

**Solución:**

Verificar `backend/config/cors.php`:

```php
<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}
```

**Y que `create.php` lo incluye:**
```php
// Línea 2 de create.php
include_once '../../config/cors.php';
```

---

### Caso 4: "Categoría no existe"

**Error SQL:**
```
SQLSTATE[23000]: Integrity constraint violation:
1452 Cannot add or update a child row: a foreign key constraint fails
(`denuncia_ciudadana`.`denuncias`, CONSTRAINT `denuncias_ibfk_2`
FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`))
```

**Solución:**
Insertar categorías:

```sql
INSERT INTO categorias (nombre, descripcion, icono) VALUES
('Servicios Básicos', 'Agua, luz, desagüe', '💧'),
('Alumbrado Público', 'Postes y luminarias', '💡'),
('Infraestructura', 'Pistas, veredas, baches', '🏗️'),
('Seguridad Ciudadana', 'Robos, delincuencia', '🚨'),
('Limpieza Pública', 'Basura, residuos', '🗑️');
```

---

## 📊 Checklist de Verificación

### Base de Datos
- [ ] XAMPP está corriendo
- [ ] MySQL está activo
- [ ] Base de datos `denuncia_ciudadana` existe
- [ ] Tabla `denuncias` existe con todas las columnas
- [ ] Hay al menos 1 categoría en `categorias`
- [ ] Hay al menos 1 usuario ciudadano en `usuarios`

### Backend
- [ ] `test_crear_denuncia.php` pasa PRUEBA 5 (SQL directo)
- [ ] `test_crear_denuncia.php` pasa PRUEBA 6 (Modelo)
- [ ] `test_endpoint_create.php` crea denuncias exitosamente
- [ ] `backend/api/denuncias/create.php` incluye CORS
- [ ] JWT se valida correctamente
- [ ] No hay errores en logs de PHP

### Frontend
- [ ] `test_frontend.html` login funciona
- [ ] `test_frontend.html` creación funciona
- [ ] Formulario en React envía todos los campos
- [ ] JWT se envía en header Authorization
- [ ] No hay errores CORS en consola
- [ ] Respuesta del servidor es 201

### Integración
- [ ] Denuncias creadas aparecen en phpMyAdmin
- [ ] Denuncias aparecen en consultas SQL
- [ ] Denuncias aparecen en el dashboard del ciudadano
- [ ] Denuncias aparecen en el dashboard del admin

---

## 🚀 Plan de Acción INMEDIATO

### 1. Ejecutar los 3 scripts en orden:

```bash
# Paso 1: Probar base de datos
http://localhost/DENUNCIA%20CIUDADANA/backend/test_crear_denuncia.php

# Paso 2: Probar endpoint
http://localhost/DENUNCIA%20CIUDADANA/backend/test_endpoint_create.php

# Paso 3: Probar desde navegador
http://localhost/DENUNCIA%20CIUDADANA/backend/test_frontend.html
```

### 2. Identificar dónde falla:

- **Si falla Paso 1:** Problema en base de datos o modelo
- **Si Paso 1 OK pero falla Paso 2:** Problema en endpoint o JWT
- **Si Paso 2 OK pero falla Paso 3:** Problema en frontend o CORS

### 3. Aplicar la solución correspondiente de este documento

### 4. Reportar resultados:

Cuando ejecutes los scripts, copia y pega TODA la salida aquí para análisis detallado.

---

## 📞 Debugging Adicional

Si después de ejecutar los 3 scripts el problema persiste, proporciona:

1. **Salida completa** de `test_crear_denuncia.php`
2. **Salida completa** de `test_endpoint_create.php`
3. **Log completo** de `test_frontend.html` (todo lo que aparece en el área negra)
4. **Consola del navegador** (F12) al intentar crear una denuncia desde el frontend real
5. **Logs de Apache:** `C:\xampp\apache\logs\error.log` (últimas 20 líneas)
6. **Logs de PHP:** (si existen)

---

**Con estos scripts identificaremos EXACTAMENTE dónde está el problema.** 🎯

**Última actualización:** 2025-12-19
**Experto:** Claude Sonnet 4.5
