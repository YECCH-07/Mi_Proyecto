# 🏗️ ARQUITECTURA - MODIFICACIONES INCREMENTALES (SIN MIGRACIÓN)

**Versión:** Adaptada para usar estructura existente
**Fecha:** 20/12/2025
**Enfoque:** Modificaciones mínimas sobre la BD actual

---

## 📋 ÍNDICE

1. [Modificaciones a Tablas Existentes](#1-modificaciones-a-tablas-existentes)
2. [Enrutamiento Automático](#2-enrutamiento-automático)
3. [Gestión de Usuarios](#3-gestión-de-usuarios)
4. [Google Maps Heatmap](#4-google-maps-heatmap)
5. [Scripts de Implementación](#5-scripts-de-implementación)

---

## 1. MODIFICACIONES A TABLAS EXISTENTES

### 1.1 Estructura Actual (SIN CAMBIOS)

**Tablas que ya existen y NO se modifican:**
- ✅ `usuarios` - Se agrega solo 1 columna
- ✅ `areas_municipales` - **SE USA TAL CUAL** (sin cambios)
- ✅ `categorias` - Se agrega solo 1 columna
- ✅ `denuncias` - Ya tiene `area_asignada_id`, solo agregar trigger
- ✅ `evidencias` - Sin cambios
- ✅ `seguimiento` - Sin cambios
- ✅ `notificaciones` - Sin cambios

### 1.2 Script de Modificaciones Mínimas

**Archivo:** `backend/MODIFICACIONES_INCREMENTALES.sql`

```sql
-- ============================================================================
-- MODIFICACIONES INCREMENTALES - SIN MIGRACIÓN
-- Solo agrega columnas y triggers sobre la estructura existente
-- ============================================================================

USE denuncia_ciudadana;

-- ----------------------------------------------------------------------------
-- PASO 1: Agregar columna area_id a tabla usuarios
-- ----------------------------------------------------------------------------
-- Permite asignar operadores a un área específica

ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS area_id INT DEFAULT NULL AFTER rol;

-- Foreign key a areas_municipales (tabla existente)
ALTER TABLE usuarios
ADD CONSTRAINT fk_usuarios_area
    FOREIGN KEY (area_id)
    REFERENCES areas_municipales(id)
    ON DELETE SET NULL;

-- Índice para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_usuarios_area ON usuarios(area_id);

-- ----------------------------------------------------------------------------
-- PASO 2: Agregar columna area_id a tabla categorias
-- ----------------------------------------------------------------------------
-- Vincula cada categoría a un área municipal

ALTER TABLE categorias
ADD COLUMN IF NOT EXISTS area_id INT DEFAULT NULL AFTER descripcion;

-- Asignar áreas a categorías existentes (basándose en nombres)
UPDATE categorias SET area_id = 1 WHERE nombre LIKE '%Basura%' OR nombre LIKE '%Limpieza%' OR nombre LIKE '%Parques%';
UPDATE categorias SET area_id = 2 WHERE nombre LIKE '%Baches%' OR nombre LIKE '%Pistas%' OR nombre LIKE '%Edificaciones%';
UPDATE categorias SET area_id = 3 WHERE nombre LIKE '%Seguridad%';
UPDATE categorias SET area_id = 1 WHERE nombre LIKE '%Contaminación%' OR nombre LIKE '%Ruido%';
UPDATE categorias SET area_id = 4 WHERE nombre LIKE '%Agua%' OR nombre LIKE '%Desagüe%' OR nombre LIKE '%Alumbrado%';
UPDATE categorias SET area_id = 5 WHERE nombre LIKE '%Transporte%';
UPDATE categorias SET area_id = 1 WHERE area_id IS NULL; -- Default: Gerencia de Servicios

-- Ahora hacer la columna NOT NULL
ALTER TABLE categorias
MODIFY COLUMN area_id INT NOT NULL;

-- Foreign key
ALTER TABLE categorias
ADD CONSTRAINT fk_categorias_area
    FOREIGN KEY (area_id)
    REFERENCES areas_municipales(id)
    ON DELETE RESTRICT;

-- Índice
CREATE INDEX IF NOT EXISTS idx_categorias_area ON categorias(area_id);

-- ----------------------------------------------------------------------------
-- PASO 3: Verificar que denuncias.area_asignada_id existe
-- ----------------------------------------------------------------------------
-- Esta columna ya debería existir, solo verificamos

-- Si no existe, crearla
ALTER TABLE denuncias
ADD COLUMN IF NOT EXISTS area_asignada_id INT DEFAULT NULL;

-- Foreign key (por si no estaba)
ALTER TABLE denuncias
ADD CONSTRAINT fk_denuncias_area
    FOREIGN KEY (area_asignada_id)
    REFERENCES areas_municipales(id)
    ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_denuncias_area ON denuncias(area_asignada_id);

-- ----------------------------------------------------------------------------
-- PASO 4: Crear TRIGGER de asignación automática
-- ----------------------------------------------------------------------------
-- Asigna automáticamente el área basándose en la categoría seleccionada

DROP TRIGGER IF EXISTS tr_denuncias_asignar_area;

DELIMITER $$

CREATE TRIGGER tr_denuncias_asignar_area
BEFORE INSERT ON denuncias
FOR EACH ROW
BEGIN
    DECLARE area_id_var INT;

    -- Obtener área de la categoría seleccionada
    SELECT area_id INTO area_id_var
    FROM categorias
    WHERE id = NEW.categoria_id;

    -- Asignar automáticamente
    IF area_id_var IS NOT NULL THEN
        SET NEW.area_asignada_id = area_id_var;
    END IF;
END$$

DELIMITER ;

-- ----------------------------------------------------------------------------
-- PASO 5: Actualizar denuncias existentes
-- ----------------------------------------------------------------------------
-- Asignar área a denuncias que no la tienen

UPDATE denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
SET d.area_asignada_id = c.area_id
WHERE d.area_asignada_id IS NULL;

-- ----------------------------------------------------------------------------
-- PASO 6: Crear VISTA optimizada
-- ----------------------------------------------------------------------------

CREATE OR REPLACE VIEW v_denuncias_por_area AS
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at,
    d.updated_at,
    d.usuario_id,
    d.area_asignada_id,
    d.categoria_id,
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.es_anonima,

    -- Información de categoría
    c.nombre AS categoria_nombre,
    c.icono AS categoria_icono,

    -- Información del área
    a.id AS area_id,
    a.nombre AS area_nombre,
    a.responsable AS area_responsable,
    a.email_contacto AS area_email,

    -- Información del ciudadano
    CASE
        WHEN d.es_anonima = FALSE THEN CONCAT(u.nombres, ' ', u.apellidos)
        ELSE 'Anónimo'
    END AS ciudadano_nombre,

    u.email AS ciudadano_email,
    u.telefono AS ciudadano_telefono

FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
LEFT JOIN usuarios u ON d.usuario_id = u.id;

-- ----------------------------------------------------------------------------
-- PASO 7: Tabla de auditoría (opcional pero recomendado)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL,
    recurso VARCHAR(100),
    recurso_id INT,
    detalles JSON,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ============================================================================
-- VERIFICACIÓN
-- ============================================================================

-- Mostrar resultados
SELECT '✅ Columnas agregadas correctamente' AS status;

SELECT 'Usuarios con área asignada:' AS info,
       COUNT(*) AS total
FROM usuarios WHERE area_id IS NOT NULL;

SELECT 'Categorías vinculadas a áreas:' AS info,
       COUNT(*) AS total
FROM categorias WHERE area_id IS NOT NULL;

SELECT 'Denuncias con área asignada:' AS info,
       COUNT(*) AS total
FROM denuncias WHERE area_asignada_id IS NOT NULL;

-- Ver distribución de categorías por área
SELECT
    a.nombre AS area,
    GROUP_CONCAT(c.nombre SEPARATOR ', ') AS categorias
FROM areas_municipales a
LEFT JOIN categorias c ON a.id = c.area_id
GROUP BY a.id, a.nombre
ORDER BY a.nombre;

-- ============================================================================
-- FIN DE MODIFICACIONES
-- ============================================================================
```

---

## 2. ENRUTAMIENTO AUTOMÁTICO

### 2.1 Cómo Funciona con la Estructura Actual

**Flujo simplificado:**

```
1. Ciudadano crea denuncia
   ↓
2. Selecciona categoría (ej: "Basura en la calle")
   ↓
3. TRIGGER detecta que categoría tiene area_id = 1
   ↓
4. Asigna automáticamente area_asignada_id = 1
   ↓
5. Solo operadores con area_id = 1 ven la denuncia
```

### 2.2 Middleware de Filtrado

**Archivo:** `backend/middleware/filter_by_area.php`

```php
<?php
/**
 * MIDDLEWARE: Filtrado por Área Municipal
 *
 * Filtra denuncias según el área del usuario operador
 */

function filterDenunciasByArea($user_data) {
    global $db;

    $rol = $user_data->rol;
    $usuario_id = $user_data->id;

    // ADMIN y SUPERVISOR ven TODO
    if ($rol === 'admin' || $rol === 'supervisor') {
        return [
            'filter_type' => 'none',
            'where_clause' => '1=1',
            'can_edit_all' => true
        ];
    }

    // OPERADOR solo ve su área
    if ($rol === 'operador') {
        // Obtener área del operador
        $query = "SELECT area_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $area_id = $result['area_id'];

        if ($area_id === null) {
            return [
                'filter_type' => 'blocked',
                'where_clause' => '1=0',
                'error_message' => 'No tiene área asignada. Contacte al administrador.'
            ];
        }

        return [
            'filter_type' => 'area',
            'area_id' => $area_id,
            'where_clause' => "d.area_asignada_id = $area_id",
            'can_edit_own_area' => true
        ];
    }

    // CIUDADANO solo ve las suyas
    if ($rol === 'ciudadano') {
        return [
            'filter_type' => 'own',
            'where_clause' => "d.usuario_id = $usuario_id",
            'can_edit_own' => true
        ];
    }

    // DEFAULT: bloquear
    return [
        'filter_type' => 'blocked',
        'where_clause' => '1=0',
        'error_message' => 'Acceso no autorizado'
    ];
}
?>
```

---

## 3. GESTIÓN DE USUARIOS

### 3.1 Endpoint CREATE Usuario

**Archivo:** `backend/api/usuarios/create.php`

```php
<?php
/**
 * API: Crear Usuario
 * Solo accesible por Admin
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo administradores pueden crear usuarios']);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Recibir datos
$data = json_decode(file_get_contents("php://input"));

// Validaciones
$errores = [];

if (empty($data->dni)) $errores[] = 'DNI es obligatorio';
if (empty($data->nombres)) $errores[] = 'Nombres es obligatorio';
if (empty($data->apellidos)) $errores[] = 'Apellidos es obligatorio';
if (empty($data->email)) $errores[] = 'Email es obligatorio';
if (empty($data->rol)) $errores[] = 'Rol es obligatorio';
if (empty($data->password)) $errores[] = 'Password es obligatorio';

// Validar rol
$roles_validos = ['admin', 'supervisor', 'operador', 'ciudadano'];
if (!empty($data->rol) && !in_array($data->rol, $roles_validos)) {
    $errores[] = 'Rol inválido';
}

// Operador DEBE tener área
if ($data->rol === 'operador' && empty($data->area_id)) {
    $errores[] = 'Los operadores deben tener un área asignada';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode(['error' => implode(', ', $errores)]);
    exit();
}

try {
    // Verificar email duplicado
    $check_email = "SELECT id FROM usuarios WHERE email = :email";
    $stmt_check = $db->prepare($check_email);
    $stmt_check->bindParam(':email', $data->email);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El email ya está registrado']);
        exit();
    }

    // Verificar DNI duplicado
    $check_dni = "SELECT id FROM usuarios WHERE dni = :dni";
    $stmt_check_dni = $db->prepare($check_dni);
    $stmt_check_dni->bindParam(':dni', $data->dni);
    $stmt_check_dni->execute();

    if ($stmt_check_dni->rowCount() > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'El DNI ya está registrado']);
        exit();
    }

    // Hash password
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert usuario
    $query = "INSERT INTO usuarios SET
        dni = :dni,
        nombres = :nombres,
        apellidos = :apellidos,
        email = :email,
        telefono = :telefono,
        password_hash = :password_hash,
        rol = :rol,
        area_id = :area_id,
        activo = TRUE,
        verificado = FALSE";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':dni', $data->dni);
    $stmt->bindParam(':nombres', $data->nombres);
    $stmt->bindParam(':apellidos', $data->apellidos);
    $stmt->bindParam(':email', $data->email);
    $stmt->bindValue(':telefono', $data->telefono ?? null);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':rol', $data->rol);
    $stmt->bindValue(':area_id', ($data->rol === 'operador' ? $data->area_id : null));

    if ($stmt->execute()) {
        $nuevo_id = $db->lastInsertId();

        // Log de auditoría
        $log_query = "INSERT INTO logs_auditoria
            (usuario_id, accion, recurso, recurso_id, detalles, ip_address, user_agent)
            VALUES
            (:user_id, 'crear_usuario', 'usuarios', :recurso_id, :detalles, :ip, :ua)";

        $stmt_log = $db->prepare($log_query);
        $stmt_log->bindParam(':user_id', $user_data->id);
        $stmt_log->bindParam(':recurso_id', $nuevo_id);
        $stmt_log->bindValue(':detalles', json_encode([
            'email' => $data->email,
            'rol' => $data->rol,
            'area_id' => $data->area_id ?? null
        ]));
        $stmt_log->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt_log->bindValue(':ua', $_SERVER['HTTP_USER_AGENT']);
        $stmt_log->execute();

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => [
                'id' => $nuevo_id,
                'email' => $data->email,
                'rol' => $data->rol
            ]
        ]);
    } else {
        throw new Exception('Error al insertar usuario');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

### 3.2 Endpoint READ Usuarios

**Archivo:** `backend/api/usuarios/read.php`

```php
<?php
/**
 * API: Listar Usuarios
 * Solo Admin
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

$user_data = validate_jwt();

if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Construir query
$query = "SELECT
    u.id,
    u.dni,
    u.nombres,
    u.apellidos,
    u.email,
    u.telefono,
    u.rol,
    u.area_id,
    u.activo,
    u.verificado,
    u.created_at,
    a.nombre as area_nombre,
    a.responsable as area_responsable
FROM usuarios u
LEFT JOIN areas_municipales a ON u.area_id = a.id
WHERE 1=1";

// Filtros opcionales
$params = [];

if (isset($_GET['rol'])) {
    $query .= " AND u.rol = :rol";
    $params[':rol'] = $_GET['rol'];
}

if (isset($_GET['area_id'])) {
    $query .= " AND u.area_id = :area_id";
    $params[':area_id'] = $_GET['area_id'];
}

if (isset($_GET['activo'])) {
    $query .= " AND u.activo = :activo";
    $params[':activo'] = $_GET['activo'];
}

$query .= " ORDER BY u.created_at DESC";

try {
    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($usuarios),
        'data' => $usuarios
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

### 3.3 Endpoint UPDATE Usuario

**Archivo:** `backend/api/usuarios/update.php`

```php
<?php
/**
 * API: Actualizar Usuario
 * Solo Admin
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

$user_data = validate_jwt();

if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
    exit();
}

// Prevenir auto-desactivación
if ($data->id == $user_data->id && isset($data->activo) && !$data->activo) {
    http_response_code(400);
    echo json_encode(['error' => 'No puede desactivarse a sí mismo']);
    exit();
}

try {
    $updates = [];
    $params = [':id' => $data->id];

    if (isset($data->nombres)) {
        $updates[] = "nombres = :nombres";
        $params[':nombres'] = $data->nombres;
    }
    if (isset($data->apellidos)) {
        $updates[] = "apellidos = :apellidos";
        $params[':apellidos'] = $data->apellidos;
    }
    if (isset($data->email)) {
        $updates[] = "email = :email";
        $params[':email'] = $data->email;
    }
    if (isset($data->telefono)) {
        $updates[] = "telefono = :telefono";
        $params[':telefono'] = $data->telefono;
    }
    if (isset($data->rol)) {
        $updates[] = "rol = :rol";
        $params[':rol'] = $data->rol;
    }
    if (isset($data->area_id)) {
        $updates[] = "area_id = :area_id";
        $params[':area_id'] = $data->area_id;
    }
    if (isset($data->activo)) {
        $updates[] = "activo = :activo";
        $params[':activo'] = $data->activo ? 1 : 0;
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        exit();
    }

    $query = "UPDATE usuarios SET " . implode(", ", $updates) . " WHERE id = :id";
    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();

    // Log
    $log_query = "INSERT INTO logs_auditoria
        (usuario_id, accion, recurso, recurso_id, detalles, ip_address, user_agent)
        VALUES (:user_id, 'actualizar_usuario', 'usuarios', :recurso_id, :detalles, :ip, :ua)";

    $stmt_log = $db->prepare($log_query);
    $stmt_log->bindParam(':user_id', $user_data->id);
    $stmt_log->bindParam(':recurso_id', $data->id);
    $stmt_log->bindValue(':detalles', json_encode($params));
    $stmt_log->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
    $stmt_log->bindValue(':ua', $_SERVER['HTTP_USER_AGENT']);
    $stmt_log->execute();

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

### 3.4 Endpoint DELETE Usuario

**Archivo:** `backend/api/usuarios/delete.php`

```php
<?php
/**
 * API: Desactivar Usuario (Soft Delete)
 * Solo Admin
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

$user_data = validate_jwt();

if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID requerido']);
    exit();
}

if ($data->id == $user_data->id) {
    http_response_code(400);
    echo json_encode(['error' => 'No puede eliminarse a sí mismo']);
    exit();
}

try {
    // Soft delete
    $query = "UPDATE usuarios SET activo = FALSE WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Log
        $log_query = "INSERT INTO logs_auditoria
            (usuario_id, accion, recurso, recurso_id, ip_address, user_agent)
            VALUES (:user_id, 'eliminar_usuario', 'usuarios', :recurso_id, :ip, :ua)";

        $stmt_log = $db->prepare($log_query);
        $stmt_log->bindParam(':user_id', $user_data->id);
        $stmt_log->bindParam(':recurso_id', $data->id);
        $stmt_log->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
        $stmt_log->bindValue(':ua', $_SERVER['HTTP_USER_AGENT']);
        $stmt_log->execute();

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Usuario no encontrado']);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

---

## 4. GOOGLE MAPS HEATMAP

### 4.1 Componente React

**Archivo:** `frontend/src/components/GoogleHeatmap.jsx`

```jsx
import React, { useState, useEffect, useCallback } from 'react';
import { GoogleMap, LoadScript, HeatmapLayer } from '@react-google-maps/api';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';
const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const libraries = ['visualization'];

const GoogleHeatmap = () => {
  const [denuncias, setDenuncias] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  const mapCenter = {
    lat: -13.5319,
    lng: -71.9675
  };

  useEffect(() => {
    fetchLocations();
  }, []);

  const fetchLocations = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('jwt');

      const response = await axios.get(
        `${API_URL}/denuncias/locations.php`,
        { headers: { 'Authorization': `Bearer ${token}` } }
      );

      if (response.data.success) {
        setDenuncias(response.data.data);
      }
    } catch (err) {
      setError('Error al cargar mapa');
    } finally {
      setLoading(false);
    }
  };

  const getHeatmapData = useCallback(() => {
    if (!window.google || !denuncias.length) return [];

    return denuncias
      .filter(d => d.latitud && d.longitud)
      .map(d => ({
        location: new window.google.maps.LatLng(
          parseFloat(d.latitud),
          parseFloat(d.longitud)
        ),
        weight: getWeight(d.estado)
      }));
  }, [denuncias]);

  const getWeight = (estado) => {
    const weights = {
      'registrada': 1,
      'en_revision': 1.5,
      'asignada': 2,
      'en_proceso': 2.5,
      'resuelta': 0.5,
      'cerrada': 0.3,
      'rechazada': 0.2
    };
    return weights[estado] || 1;
  };

  const heatmapOptions = {
    radius: 30,
    opacity: 0.6,
    gradient: [
      'rgba(0, 255, 255, 0)',
      'rgba(0, 191, 255, 1)',
      'rgba(0, 127, 255, 1)',
      'rgba(0, 0, 255, 1)',
      'rgba(127, 0, 127, 1)',
      'rgba(255, 0, 0, 1)'
    ]
  };

  if (loading) return <div className="p-8 text-center">Cargando...</div>;
  if (error) return <div className="p-4 bg-red-100 text-red-700">{error}</div>;

  return (
    <div className="w-full">
      <div className="mb-4 bg-white p-4 rounded shadow">
        <h2 className="text-xl font-bold mb-2">Mapa de Calor de Denuncias</h2>
        <p className="text-sm text-gray-600">Total: {denuncias.length}</p>
      </div>

      <div className="bg-white rounded shadow overflow-hidden">
        <LoadScript
          googleMapsApiKey={GOOGLE_MAPS_API_KEY}
          libraries={libraries}
        >
          <GoogleMap
            mapContainerStyle={{ width: '100%', height: '600px' }}
            center={mapCenter}
            zoom={13}
          >
            {denuncias.length > 0 && (
              <HeatmapLayer
                data={getHeatmapData()}
                options={heatmapOptions}
              />
            )}
          </GoogleMap>
        </LoadScript>
      </div>
    </div>
  );
};

export default GoogleHeatmap;
```

### 4.2 Endpoint de Localizaciones

**Archivo:** `backend/api/denuncias/locations.php`

```php
<?php
/**
 * API: Obtener Coordenadas para Heatmap
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

$user_data = validate_jwt();

$database = new Database();
$db = $database->getConnection();

// Aplicar filtro por área
$filter = filterDenunciasByArea($user_data);

if ($filter['filter_type'] === 'blocked') {
    http_response_code(403);
    echo json_encode(['error' => $filter['error_message']]);
    exit();
}

$query = "SELECT
    d.id,
    d.latitud,
    d.longitud,
    d.estado,
    c.nombre as categoria,
    a.nombre as area
FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
WHERE {$filter['where_clause']}
  AND d.latitud IS NOT NULL
  AND d.longitud IS NOT NULL";

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($denuncias),
        'data' => $denuncias
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
```

---

## 5. SCRIPTS DE IMPLEMENTACIÓN

### 5.1 Script SQL para Ejecutar

```bash
# Ejecutar desde MySQL
mysql -u root -p denuncia_ciudadana < backend/MODIFICACIONES_INCREMENTALES.sql
```

### 5.2 Instalar Google Maps en Frontend

```bash
cd frontend
npm install @react-google-maps/api
```

### 5.3 Configurar .env

```env
# frontend/.env
VITE_GOOGLE_MAPS_API_KEY=TU_API_KEY_AQUI
VITE_MAP_CENTER_LAT=-13.5319
VITE_MAP_CENTER_LNG=-71.9675
```

---

## 📋 RESUMEN DE CAMBIOS

### ✅ Base de Datos (Sin migración)
- Agregar columna `area_id` a `usuarios`
- Agregar columna `area_id` a `categorias`
- Crear trigger `tr_denuncias_asignar_area`
- Crear vista `v_denuncias_por_area`
- Crear tabla `logs_auditoria`

### ✅ Backend (5 archivos nuevos)
- `backend/api/usuarios/create.php`
- `backend/api/usuarios/read.php`
- `backend/api/usuarios/update.php`
- `backend/api/usuarios/delete.php`
- `backend/middleware/filter_by_area.php`
- `backend/api/denuncias/locations.php`

### ✅ Frontend (1 componente)
- `frontend/src/components/GoogleHeatmap.jsx`

---

**TOTAL DE MODIFICACIONES:**
- 2 columnas agregadas
- 1 trigger creado
- 1 vista creada
- 1 tabla nueva (logs)
- 6 endpoints nuevos
- 1 middleware nuevo
- 1 componente React

**SIN MIGRACIÓN DE BD ✅**
