# 🏗️ ARQUITECTURA COMPLETA - PANEL DE ADMINISTRADOR CON ENRUTAMIENTO AUTOMÁTICO

**Diseñado por:** Arquitecto de Software Senior
**Fecha:** 20/12/2025
**Sistema:** Gestión de Denuncias Ciudadanas
**Versión:** 2.0 - Enrutamiento Automático por Departamentos

---

## 📋 TABLA DE CONTENIDOS

1. [Contexto y Requerimientos](#1-contexto-y-requerimientos)
2. [Arquitectura de Base de Datos](#2-arquitectura-de-base-de-datos)
3. [Lógica de Enrutamiento Automático](#3-lógica-de-enrutamiento-automático)
4. [Gestión de Usuarios (CRUD)](#4-gestión-de-usuarios-crud)
5. [Sistema de Permisos RBAC](#5-sistema-de-permisos-rbac)
6. [Google Maps Heatmap](#6-google-maps-heatmap)
7. [Diagramas de Flujo](#7-diagramas-de-flujo)
8. [Código de Implementación](#8-código-de-implementación)
9. [Seguridad y Escalabilidad](#9-seguridad-y-escalabilidad)
10. [Plan de Implementación](#10-plan-de-implementación)

---

## 1. CONTEXTO Y REQUERIMIENTOS

### 1.1 Situación Actual

**Sistema Existente:**
- ✅ 7 tablas funcionando (usuarios, denuncias, categorías, áreas, evidencias, seguimiento, notificaciones)
- ✅ 4 roles implementados (admin, supervisor, operador, ciudadano)
- ✅ Autenticación JWT
- ✅ Paneles diferenciados por rol
- ✅ Heatmap con Leaflet.js

**Limitaciones Identificadas:**
- ❌ No hay vínculo automático entre categorías y departamentos
- ❌ No hay asignación de operadores a departamentos específicos
- ❌ Admin no puede gestionar usuarios (crear/editar/eliminar)
- ❌ No hay filtrado automático de denuncias por departamento del operador
- ❌ Heatmap usa Leaflet en vez de Google Maps

### 1.2 Requerimientos Críticos

| ID | Requerimiento | Prioridad | Estado |
|----|---------------|-----------|--------|
| REQ-01 | Vincular categorías con departamentos | CRÍTICA | 🔴 Por implementar |
| REQ-02 | Asignar operadores a departamentos | CRÍTICA | 🔴 Por implementar |
| REQ-03 | Enrutamiento automático de denuncias | CRÍTICA | 🔴 Por implementar |
| REQ-04 | CRUD completo de usuarios (solo admin) | CRÍTICA | 🔴 Por implementar |
| REQ-05 | Filtrado automático por departamento | CRÍTICA | 🔴 Por implementar |
| REQ-06 | Google Maps Heatmap Layer | ALTA | 🔴 Por implementar |
| REQ-07 | Admin ve todas las denuncias | ALTA | ✅ Implementado |
| REQ-08 | Middleware de permisos estricto | ALTA | 🟡 Mejorar |

### 1.3 Casos de Uso Principales

**CASO 1: Creación de Operador con Departamento**
```
Admin → Crear Usuario → Asignar Rol "Operador" → Asignar Departamento "Medio Ambiente"
Resultado: Operador solo ve denuncias de categorías vinculadas a Medio Ambiente
```

**CASO 2: Denuncia Automáticamente Enrutada**
```
Ciudadano → Nueva Denuncia → Selecciona "Quema de Basura" (Categoría)
Sistema → Detecta que pertenece a "Medio Ambiente" (Departamento)
Sistema → Muestra solo a operadores de Medio Ambiente
```

**CASO 3: Admin Global**
```
Admin → Dashboard → Ve TODAS las denuncias de TODOS los departamentos
Admin → Heatmap → Visualiza densidad total de denuncias en la ciudad
```

---

## 2. ARQUITECTURA DE BASE DE DATOS

### 2.1 Diagrama ER Completo

```
┌──────────────────────────────────────────────────────────────────────────┐
│                    MODELO ENTIDAD-RELACIÓN MEJORADO                       │
└──────────────────────────────────────────────────────────────────────────┘

                        ┌──────────────────────┐
                        │     usuarios         │
                        │ ──────────────────── │
                        │ id (PK)              │
                        │ dni                  │
                        │ nombres              │
                        │ apellidos            │
                        │ email                │
                        │ password_hash        │
                        │ rol (ENUM)           │
                        │ departamento_id (FK) │◄────┐
                        │ activo               │     │
                        │ created_at           │     │
                        └───────────┬──────────┘     │
                                    │                │
                                    │ 1              │
                                    │                │
                                    │ N              │ 1
                        ┌───────────▼──────────┐     │
                        │    denuncias         │     │
                        │ ──────────────────── │     │
                        │ id (PK)              │     │
                        │ codigo (UNIQUE)      │     │
                        │ usuario_id (FK)      │     │
                        │ categoria_id (FK)────┼──┐  │
                        │ titulo               │  │  │
                        │ descripcion          │  │  │
                        │ latitud              │  │  │
                        │ longitud             │  │  │
                        │ estado               │  │  │
                        │ departamento_id (FK) │◄─┼──┼───┐
                        │ created_at           │  │  │   │
                        └──────────────────────┘  │  │   │
                                                  │  │   │
                ┌─────────────────────────────────┘  │   │
                │                                    │   │
                │ N                                  │   │
                │ 1                                  │   │
    ┌───────────▼──────────────┐         ┌──────────▼───▼────────────┐
    │     categorias           │    N:M  │    departamentos          │
    │ ──────────────────────── │◄────────┤ ───────────────────────── │
    │ id (PK)                  │         │ id (PK)                   │
    │ nombre                   │         │ nombre                    │
    │ descripcion              │         │ descripcion               │
    │ icono                    │         │ responsable               │
    │ departamento_id (FK)─────┼─────────┤ email_contacto            │
    │ created_at               │         │ telefono                  │
    └──────────────────────────┘         │ color (para UI)           │
                                         │ activo                    │
                                         │ created_at                │
                                         └───────────────────────────┘

    ┌──────────────────────────────────────────────────────────────┐
    │          TABLA NUEVA: categoria_departamento                  │
    │          (Relación Many-to-Many si es necesaria)             │
    ├──────────────────────────────────────────────────────────────┤
    │ id (PK)                                                      │
    │ categoria_id (FK) → categorias.id                            │
    │ departamento_id (FK) → departamentos.id                      │
    │ prioridad (INT) - orden de derivación                        │
    │ created_at                                                   │
    └──────────────────────────────────────────────────────────────┘

Nota: Decidí usar FK directa en categorias.departamento_id para simplificar.
```

### 2.2 Tabla DEPARTAMENTOS (Nueva)

**Descripción:** Reemplaza y mejora `areas_municipales`

```sql
CREATE TABLE departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Información básica
    nombre VARCHAR(150) NOT NULL UNIQUE,
    descripcion TEXT,
    codigo VARCHAR(20) UNIQUE, -- ej: "MA" para Medio Ambiente

    -- Contacto
    responsable VARCHAR(150),
    email_contacto VARCHAR(150),
    telefono VARCHAR(20),

    -- UI/UX
    color VARCHAR(7) DEFAULT '#3B82F6', -- Color hex para UI
    icono VARCHAR(50), -- Nombre del icono

    -- Estado
    activo BOOLEAN DEFAULT TRUE,

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_activo (activo),
    INDEX idx_codigo (codigo)

) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Datos Iniciales:**

```sql
INSERT INTO departamentos (nombre, descripcion, codigo, responsable, email_contacto, telefono, color, icono) VALUES
('Medio Ambiente', 'Gestión ambiental, limpieza, reciclaje, áreas verdes', 'MA', 'Biol. Luis García', 'ambiente@muni.gob.pe', '984123456', '#10B981', 'leaf'),
('Obras Públicas', 'Infraestructura, pistas, veredas, edificaciones', 'OP', 'Ing. María Rodríguez', 'obras@muni.gob.pe', '984234567', '#F59E0B', 'hammer'),
('Seguridad Ciudadana', 'Seguridad, vigilancia, emergencias', 'SC', 'Mayor Juan Pérez', 'seguridad@muni.gob.pe', '984345678', '#EF4444', 'shield'),
('Servicios Públicos', 'Agua, desagüe, electricidad, alumbrado', 'SP', 'Ing. Carlos Mendoza', 'servicios@muni.gob.pe', '984456789', '#3B82F6', 'lightbulb'),
('Transporte y Vialidad', 'Transporte público, señalización, tránsito', 'TV', 'Ing. Roberto Sánchez', 'transporte@muni.gob.pe', '984567890', '#8B5CF6', 'truck'),
('Desarrollo Urbano', 'Planificación urbana, licencias, habilitaciones', 'DU', 'Arq. Ana Torres', 'desarrollo@muni.gob.pe', '984678901', '#EC4899', 'building');
```

### 2.3 Modificación Tabla USUARIOS

**Agregar columna `departamento_id`:**

```sql
ALTER TABLE usuarios
ADD COLUMN departamento_id INT DEFAULT NULL AFTER rol,
ADD CONSTRAINT fk_usuarios_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE SET NULL;

-- Índice para optimizar búsquedas
CREATE INDEX idx_usuarios_departamento ON usuarios(departamento_id);
```

**Lógica de Asignación:**
- **Admin/Supervisor:** `departamento_id = NULL` (ven todas las denuncias)
- **Operador:** `departamento_id = [ID específico]` (solo ven su departamento)
- **Ciudadano:** `departamento_id = NULL` (no aplica)

### 2.4 Modificación Tabla CATEGORIAS

**Agregar columna `departamento_id`:**

```sql
ALTER TABLE categorias
ADD COLUMN departamento_id INT NOT NULL AFTER descripcion,
ADD CONSTRAINT fk_categorias_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE RESTRICT;

-- Índice para joins frecuentes
CREATE INDEX idx_categorias_departamento ON categorias(departamento_id);
```

**Asignación de Categorías a Departamentos:**

```sql
-- Basura y Limpieza → Medio Ambiente
UPDATE categorias SET departamento_id = 1 WHERE id = 1;

-- Baches y Pistas → Obras Públicas
UPDATE categorias SET departamento_id = 2 WHERE id = 2;

-- Alumbrado Público → Servicios Públicos
UPDATE categorias SET departamento_id = 4 WHERE id = 3;

-- Agua y Desagüe → Servicios Públicos
UPDATE categorias SET departamento_id = 4 WHERE id = 4;

-- Parques y Jardines → Medio Ambiente
UPDATE categorias SET departamento_id = 1 WHERE id = 5;

-- Seguridad Ciudadana → Seguridad Ciudadana
UPDATE categorias SET departamento_id = 3 WHERE id = 6;

-- Ruido y Contaminación → Medio Ambiente
UPDATE categorias SET departamento_id = 1 WHERE id = 7;

-- Transporte Público → Transporte y Vialidad
UPDATE categorias SET departamento_id = 5 WHERE id = 8;

-- Edificaciones Peligrosas → Desarrollo Urbano
UPDATE categorias SET departamento_id = 6 WHERE id = 9;

-- Otros → Servicios Públicos (por defecto)
UPDATE categorias SET departamento_id = 4 WHERE id = 10;
```

### 2.5 Modificación Tabla DENUNCIAS

**Reemplazar `area_asignada_id` por `departamento_id`:**

```sql
-- Opción 1: Renombrar columna existente
ALTER TABLE denuncias
CHANGE COLUMN area_asignada_id departamento_id INT DEFAULT NULL;

-- Opción 2: Agregar nueva columna (si quieres mantener ambas)
ALTER TABLE denuncias
ADD COLUMN departamento_id INT DEFAULT NULL AFTER categoria_id,
ADD CONSTRAINT fk_denuncias_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE SET NULL;

-- Índice para filtrado rápido
CREATE INDEX idx_denuncias_departamento ON denuncias(departamento_id);
```

**Trigger Automático para Asignación de Departamento:**

```sql
DELIMITER $$

CREATE TRIGGER tr_denuncias_asignar_departamento
BEFORE INSERT ON denuncias
FOR EACH ROW
BEGIN
    -- Asignar departamento basándose en la categoría seleccionada
    DECLARE dept_id INT;

    SELECT departamento_id INTO dept_id
    FROM categorias
    WHERE id = NEW.categoria_id;

    SET NEW.departamento_id = dept_id;
END$$

DELIMITER ;
```

**¿Qué hace este trigger?**
1. Cuando se inserta una nueva denuncia
2. Lee el `categoria_id` que el ciudadano seleccionó
3. Busca el `departamento_id` asociado a esa categoría
4. Asigna automáticamente el departamento a la denuncia

**Ejemplo práctico:**
```
Ciudadano selecciona: "Quema de basura" (categoria_id = 1)
→ Trigger detecta: categoria 1 → departamento_id = 1 (Medio Ambiente)
→ Denuncia queda asignada automáticamente a Medio Ambiente
```

### 2.6 Vista SQL para Operadores

**Crear vista optimizada para filtrado:**

```sql
CREATE OR REPLACE VIEW v_denuncias_por_departamento AS
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at,
    d.usuario_id,
    d.departamento_id,

    -- Información de categoría
    c.id AS categoria_id,
    c.nombre AS categoria_nombre,
    c.icono AS categoria_icono,

    -- Información de departamento
    dep.id AS departamento_asignado_id,
    dep.nombre AS departamento_nombre,
    dep.codigo AS departamento_codigo,
    dep.color AS departamento_color,

    -- Información del ciudadano (si no es anónima)
    CASE
        WHEN d.es_anonima = FALSE THEN CONCAT(u.nombres, ' ', u.apellidos)
        ELSE 'Anónimo'
    END AS ciudadano_nombre,

    u.email AS ciudadano_email,
    u.telefono AS ciudadano_telefono,

    -- Coordenadas
    d.latitud,
    d.longitud,
    d.direccion_referencia

FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
INNER JOIN departamentos dep ON d.departamento_id = dep.id
LEFT JOIN usuarios u ON d.usuario_id = u.id;
```

**Uso de la vista:**
```sql
-- Operador del departamento 1 (Medio Ambiente) solo ve sus denuncias
SELECT * FROM v_denuncias_por_departamento
WHERE departamento_id = 1;

-- Admin ve todas
SELECT * FROM v_denuncias_por_departamento;
```

### 2.7 Diagrama de Dependencias

```
departamentos (tabla maestra)
    ↓ (1:N)
    ├─→ usuarios (operadores asignados)
    ├─→ categorias (categorías del departamento)
    └─→ denuncias (denuncias asignadas automáticamente)

Flujo de Datos:
1. Admin crea Departamento
2. Admin crea Categorías vinculadas a Departamento
3. Admin crea Operadores asignados a Departamento
4. Ciudadano crea Denuncia y selecciona Categoría
5. Trigger asigna automáticamente Departamento basándose en Categoría
6. Operador ve solo denuncias de su Departamento
7. Admin ve todas las denuncias
```

---

## 3. LÓGICA DE ENRUTAMIENTO AUTOMÁTICO

### 3.1 Flujo de Enrutamiento

```
┌────────────────────────────────────────────────────────────────┐
│              FLUJO DE ENRUTAMIENTO AUTOMÁTICO                   │
└────────────────────────────────────────────────────────────────┘

PASO 1: CIUDADANO CREA DENUNCIA
├─ Frontend: Formulario "Nueva Denuncia"
├─ Ciudadano selecciona: Categoría = "Basura en la calle" (ID: 1)
└─ POST /api/denuncias/create.php

PASO 2: BACKEND RECIBE SOLICITUD
├─ Valida JWT del ciudadano
├─ Valida campos obligatorios (titulo, descripcion, categoria_id, latitud, longitud)
└─ Prepara INSERT en tabla denuncias

PASO 3: TRIGGER AUTOMÁTICO SE EJECUTA
├─ BEFORE INSERT en tabla denuncias
├─ Lee categoria_id de la nueva denuncia
├─ Busca departamento_id asociado en tabla categorias
│   └─ SELECT departamento_id FROM categorias WHERE id = NEW.categoria_id
├─ Asigna departamento_id a la denuncia
│   └─ SET NEW.departamento_id = dept_id
└─ Denuncia se inserta con departamento_id ya asignado

PASO 4: DENUNCIA GUARDADA
├─ INSERT exitoso en tabla denuncias
├─ Registro en tabla seguimiento (estado inicial: "registrada")
└─ Notificación enviada a operadores del departamento

PASO 5: OPERADORES VEN LA DENUNCIA
├─ Operador de Medio Ambiente hace login
├─ Dashboard ejecuta query:
│   └─ SELECT * FROM denuncias
│       WHERE departamento_id = [departamento_id del operador]
│       AND estado IN ('registrada', 'asignada', 'en_proceso')
├─ Solo ve denuncias de su departamento
└─ Puede actualizar estado y agregar comentarios

PASO 6: ADMIN VE TODO
├─ Admin hace login
├─ Dashboard ejecuta query:
│   └─ SELECT * FROM denuncias (sin filtro de departamento)
├─ Ve todas las denuncias de todos los departamentos
└─ Puede reasignar departamentos manualmente si es necesario
```

### 3.2 Pseudocódigo del Middleware de Filtrado

**Archivo:** `backend/middleware/filter_by_department.php`

```php
<?php
/**
 * MIDDLEWARE: Filtrado Automático por Departamento
 *
 * Este middleware se ejecuta en todos los endpoints de consulta
 * de denuncias para aplicar el filtro según el rol del usuario.
 */

function filterDenunciasByDepartment($user_data) {
    $rol = $user_data->rol;
    $usuario_id = $user_data->id;

    // CASO 1: Admin o Supervisor → VEN TODO
    if ($rol === 'admin' || $rol === 'supervisor') {
        return [
            'filter_type' => 'none',
            'where_clause' => '1=1', // No filter
            'can_edit_all' => true
        ];
    }

    // CASO 2: Operador → SOLO SU DEPARTAMENTO
    if ($rol === 'operador') {
        // Obtener departamento del operador
        $query = "SELECT departamento_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $departamento_id = $result['departamento_id'];

        if ($departamento_id === null) {
            // Operador sin departamento asignado → No ve nada
            return [
                'filter_type' => 'blocked',
                'where_clause' => '1=0', // Block all
                'error_message' => 'No tiene departamento asignado'
            ];
        }

        // Filtro por departamento
        return [
            'filter_type' => 'department',
            'department_id' => $departamento_id,
            'where_clause' => "d.departamento_id = $departamento_id",
            'can_edit_all' => false,
            'can_edit_own_department' => true
        ];
    }

    // CASO 3: Ciudadano → SOLO SUS DENUNCIAS
    if ($rol === 'ciudadano') {
        return [
            'filter_type' => 'own',
            'where_clause' => "d.usuario_id = $usuario_id",
            'can_edit_all' => false,
            'can_edit_own' => true
        ];
    }

    // DEFAULT: Bloquear acceso
    return [
        'filter_type' => 'blocked',
        'where_clause' => '1=0',
        'error_message' => 'Rol no autorizado'
    ];
}

/**
 * Ejemplo de uso en endpoint de lectura
 */
function getDenuncias($user_data) {
    global $db;

    // Aplicar filtro
    $filter = filterDenunciasByDepartment($user_data);

    if ($filter['filter_type'] === 'blocked') {
        http_response_code(403);
        echo json_encode(['error' => $filter['error_message']]);
        return;
    }

    // Construir query con filtro
    $query = "SELECT
                d.*,
                c.nombre as categoria_nombre,
                dep.nombre as departamento_nombre,
                u.nombres, u.apellidos
            FROM denuncias d
            INNER JOIN categorias c ON d.categoria_id = c.id
            INNER JOIN departamentos dep ON d.departamento_id = dep.id
            LEFT JOIN usuarios u ON d.usuario_id = u.id
            WHERE {$filter['where_clause']}
            ORDER BY d.created_at DESC";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $denuncias,
        'filter_applied' => $filter['filter_type']
    ]);
}
```

### 3.3 Lógica de Reasignación Manual (Admin)

**Solo el Admin puede reasignar denuncias a otro departamento:**

```php
<?php
/**
 * ENDPOINT: Reasignar Departamento
 * Solo accesible por Administrador
 */

// POST /api/denuncias/reasignar_departamento.php

include_once '../../middleware/validate_jwt.php';
include_once '../../config/database.php';

$user_data = validate_jwt();

// Verificar que sea admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo administradores pueden reasignar']);
    exit();
}

// Recibir datos
$data = json_decode(file_get_contents("php://input"));
$denuncia_id = $data->denuncia_id;
$nuevo_departamento_id = $data->nuevo_departamento_id;
$motivo = $data->motivo; // Razón de la reasignación

// Validaciones
if (!$denuncia_id || !$nuevo_departamento_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit();
}

try {
    $db->beginTransaction();

    // Obtener departamento anterior
    $query_old = "SELECT departamento_id FROM denuncias WHERE id = :id";
    $stmt_old = $db->prepare($query_old);
    $stmt_old->bindParam(':id', $denuncia_id);
    $stmt_old->execute();
    $old_dept = $stmt_old->fetch(PDO::FETCH_ASSOC);

    // Actualizar departamento
    $query_update = "UPDATE denuncias
                     SET departamento_id = :nuevo_dept
                     WHERE id = :id";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':nuevo_dept', $nuevo_departamento_id);
    $stmt_update->bindParam(':id', $denuncia_id);
    $stmt_update->execute();

    // Registrar en seguimiento
    $query_seguimiento = "INSERT INTO seguimiento
        (denuncia_id, usuario_id, estado_anterior, estado_nuevo, comentario)
        VALUES
        (:denuncia_id, :usuario_id, :dept_anterior, :dept_nuevo, :motivo)";

    $stmt_seg = $db->prepare($query_seguimiento);
    $stmt_seg->bindParam(':denuncia_id', $denuncia_id);
    $stmt_seg->bindParam(':usuario_id', $user_data->id);
    $stmt_seg->bindValue(':dept_anterior', "Depto: {$old_dept['departamento_id']}");
    $stmt_seg->bindValue(':dept_nuevo', "Depto: {$nuevo_departamento_id}");
    $stmt_seg->bindParam(':motivo', $motivo);
    $stmt_seg->execute();

    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Departamento reasignado correctamente'
    ]);

} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
```

---

## 4. GESTIÓN DE USUARIOS (CRUD)

### 4.1 Endpoints Requeridos

| Endpoint | Método | Descripción | Acceso |
|----------|--------|-------------|--------|
| `/api/usuarios/read.php` | GET | Listar todos los usuarios | Solo Admin |
| `/api/usuarios/read_one.php?id=X` | GET | Ver un usuario específico | Solo Admin |
| `/api/usuarios/create.php` | POST | Crear nuevo usuario | Solo Admin |
| `/api/usuarios/update.php` | PUT | Actualizar usuario | Solo Admin |
| `/api/usuarios/delete.php` | DELETE | Eliminar/desactivar usuario | Solo Admin |
| `/api/usuarios/change_password.php` | POST | Cambiar contraseña | Admin o Usuario mismo |

### 4.2 Código del Endpoint CREATE

**Archivo:** `backend/api/usuarios/create.php`

```php
<?php
/**
 * API Endpoint: Crear Usuario
 * Solo accesible por Administrador
 *
 * POST /api/usuarios/create.php
 *
 * Body JSON:
 * {
 *   "dni": "12345678",
 *   "nombres": "Juan",
 *   "apellidos": "Pérez",
 *   "email": "juan.perez@muni.gob.pe",
 *   "telefono": "987654321",
 *   "rol": "operador",
 *   "departamento_id": 1,  // Solo si rol = operador
 *   "password": "password123"
 * }
 */

// Headers
include_once '../../config/cors.php';

// Database y JWT
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../models/User.php';

// Validar JWT
$user_data = validate_jwt();

// CRÍTICO: Solo admin puede crear usuarios
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Acceso denegado. Solo administradores pueden crear usuarios.'
    ]);
    exit();
}

// Obtener datos
$database = new Database();
$db = $database->getConnection();
$data = json_decode(file_get_contents("php://input"));

// Validaciones
if (
    empty($data->dni) ||
    empty($data->nombres) ||
    empty($data->apellidos) ||
    empty($data->email) ||
    empty($data->rol) ||
    empty($data->password)
) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos. DNI, nombres, apellidos, email, rol y password son obligatorios.'
    ]);
    exit();
}

// Validar rol válido
$roles_validos = ['admin', 'supervisor', 'operador', 'ciudadano'];
if (!in_array($data->rol, $roles_validos)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Rol inválido. Roles permitidos: admin, supervisor, operador, ciudadano'
    ]);
    exit();
}

// Validar departamento para operadores
if ($data->rol === 'operador' && empty($data->departamento_id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Los operadores deben tener un departamento asignado.'
    ]);
    exit();
}

// Crear instancia User
$user = new User($db);

// Verificar si email ya existe
if ($user->emailExists($data->email)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El email ya está registrado.'
    ]);
    exit();
}

// Verificar si DNI ya existe
if ($user->dniExists($data->dni)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'El DNI ya está registrado.'
    ]);
    exit();
}

// Asignar valores
$user->dni = strip_tags($data->dni);
$user->nombres = strip_tags($data->nombres);
$user->apellidos = strip_tags($data->apellidos);
$user->email = strip_tags($data->email);
$user->telefono = isset($data->telefono) ? strip_tags($data->telefono) : null;
$user->rol = $data->rol;
$user->departamento_id = ($data->rol === 'operador') ? $data->departamento_id : null;
$user->password = $data->password; // Se hasheará en el modelo

// Crear usuario
if ($user->register()) {
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario creado exitosamente',
        'data' => [
            'id' => $user->id,
            'email' => $user->email,
            'rol' => $user->rol,
            'departamento_id' => $user->departamento_id
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al crear usuario'
    ]);
}
?>
```

### 4.3 Código del Endpoint READ (Listar Usuarios)

**Archivo:** `backend/api/usuarios/read.php`

```php
<?php
/**
 * API Endpoint: Listar Usuarios
 * Solo accesible por Administrador
 *
 * GET /api/usuarios/read.php
 *
 * Query params opcionales:
 * - rol: filtrar por rol (ej: ?rol=operador)
 * - departamento_id: filtrar por departamento (ej: ?departamento_id=1)
 * - activo: filtrar activos/inactivos (ej: ?activo=1)
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Construir query base
$query = "SELECT
    u.id,
    u.dni,
    u.nombres,
    u.apellidos,
    u.email,
    u.telefono,
    u.rol,
    u.departamento_id,
    u.activo,
    u.verificado,
    u.created_at,
    d.nombre as departamento_nombre,
    d.codigo as departamento_codigo,
    d.color as departamento_color
FROM usuarios u
LEFT JOIN departamentos d ON u.departamento_id = d.id
WHERE 1=1";

// Filtros opcionales
$params = [];

if (isset($_GET['rol']) && !empty($_GET['rol'])) {
    $query .= " AND u.rol = :rol";
    $params[':rol'] = $_GET['rol'];
}

if (isset($_GET['departamento_id']) && !empty($_GET['departamento_id'])) {
    $query .= " AND u.departamento_id = :departamento_id";
    $params[':departamento_id'] = $_GET['departamento_id'];
}

if (isset($_GET['activo'])) {
    $query .= " AND u.activo = :activo";
    $params[':activo'] = $_GET['activo'];
}

$query .= " ORDER BY u.created_at DESC";

try {
    $stmt = $db->prepare($query);

    // Bind params
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ocultar password_hash en la respuesta
    foreach ($usuarios as &$usuario) {
        unset($usuario['password_hash']);
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($usuarios),
        'data' => $usuarios
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error al obtener usuarios: ' . $e->getMessage()
    ]);
}
?>
```

### 4.4 Código del Endpoint UPDATE

**Archivo:** `backend/api/usuarios/update.php`

```php
<?php
/**
 * API Endpoint: Actualizar Usuario
 * Solo accesible por Administrador
 *
 * PUT /api/usuarios/update.php
 *
 * Body JSON:
 * {
 *   "id": 5,
 *   "nombres": "Juan Carlos",
 *   "apellidos": "Pérez López",
 *   "email": "juan.perez@muni.gob.pe",
 *   "telefono": "987654321",
 *   "rol": "operador",
 *   "departamento_id": 2,
 *   "activo": true
 * }
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Solo administradores pueden actualizar usuarios']);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

// Validar ID
if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit();
}

// Prevenir que admin se desactive a sí mismo
if ($data->id == $user_data->id && isset($data->activo) && !$data->activo) {
    http_response_code(400);
    echo json_encode(['error' => 'No puede desactivarse a sí mismo']);
    exit();
}

try {
    // Construir query dinámica solo con campos proporcionados
    $updates = [];
    $params = [':id' => $data->id];

    if (isset($data->nombres)) {
        $updates[] = "nombres = :nombres";
        $params[':nombres'] = strip_tags($data->nombres);
    }

    if (isset($data->apellidos)) {
        $updates[] = "apellidos = :apellidos";
        $params[':apellidos'] = strip_tags($data->apellidos);
    }

    if (isset($data->email)) {
        $updates[] = "email = :email";
        $params[':email'] = strip_tags($data->email);
    }

    if (isset($data->telefono)) {
        $updates[] = "telefono = :telefono";
        $params[':telefono'] = strip_tags($data->telefono);
    }

    if (isset($data->rol)) {
        $roles_validos = ['admin', 'supervisor', 'operador', 'ciudadano'];
        if (!in_array($data->rol, $roles_validos)) {
            throw new Exception('Rol inválido');
        }
        $updates[] = "rol = :rol";
        $params[':rol'] = $data->rol;
    }

    if (isset($data->departamento_id)) {
        $updates[] = "departamento_id = :departamento_id";
        $params[':departamento_id'] = $data->departamento_id;
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

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado exitosamente'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
```

### 4.5 Código del Endpoint DELETE

**Archivo:** `backend/api/usuarios/delete.php`

```php
<?php
/**
 * API Endpoint: Desactivar Usuario
 * Solo accesible por Administrador
 *
 * NO se elimina físicamente, solo se marca como inactivo
 * para mantener integridad referencial
 *
 * DELETE /api/usuarios/delete.php
 *
 * Body JSON:
 * {
 *   "id": 5
 * }
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

if (empty($data->id)) {
    http_response_code(400);
    echo json_encode(['error' => 'ID de usuario requerido']);
    exit();
}

// Prevenir auto-eliminación
if ($data->id == $user_data->id) {
    http_response_code(400);
    echo json_encode(['error' => 'No puede desactivarse a sí mismo']);
    exit();
}

try {
    // Soft delete: marcar como inactivo
    $query = "UPDATE usuarios SET activo = FALSE WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
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

## 5. SISTEMA DE PERMISOS RBAC

### 5.1 Matriz de Permisos

| Acción | Admin | Supervisor | Operador | Ciudadano |
|--------|-------|------------|----------|-----------|
| **USUARIOS** |
| Ver todos los usuarios | ✅ | ❌ | ❌ | ❌ |
| Crear usuarios | ✅ | ❌ | ❌ | ❌ |
| Editar usuarios | ✅ | ❌ | ❌ | ❌ |
| Eliminar usuarios | ✅ | ❌ | ❌ | ❌ |
| **DENUNCIAS** |
| Ver todas las denuncias | ✅ | ✅ | ❌ | ❌ |
| Ver denuncias de su departamento | ✅ | ✅ | ✅ | ❌ |
| Ver solo sus denuncias | ✅ | ✅ | ✅ | ✅ |
| Crear denuncias | ✅ | ✅ | ✅ | ✅ |
| Actualizar estado | ✅ | ✅ | ✅ | ❌ |
| Reasignar departamento | ✅ | ❌ | ❌ | ❌ |
| Eliminar denuncias | ✅ | ❌ | ❌ | ❌ |
| **DEPARTAMENTOS** |
| Ver departamentos | ✅ | ✅ | ✅ | ✅ |
| Crear departamentos | ✅ | ❌ | ❌ | ❌ |
| Editar departamentos | ✅ | ❌ | ❌ | ❌ |
| Eliminar departamentos | ✅ | ❌ | ❌ | ❌ |
| **CATEGORÍAS** |
| Ver categorías | ✅ | ✅ | ✅ | ✅ |
| Crear categorías | ✅ | ❌ | ❌ | ❌ |
| Editar categorías | ✅ | ❌ | ❌ | ❌ |
| Vincular a departamento | ✅ | ❌ | ❌ | ❌ |
| **REPORTES** |
| Ver estadísticas generales | ✅ | ✅ | ❌ | ❌ |
| Generar reportes PDF | ✅ | ✅ | ✅ | ❌ |
| Ver heatmap | ✅ | ✅ | ✅ | ❌ |
| Exportar datos | ✅ | ✅ | ❌ | ❌ |

### 5.2 Middleware de Permisos Mejorado

**Archivo:** `backend/middleware/check_permission.php`

```php
<?php
/**
 * MIDDLEWARE: Verificación de Permisos RBAC
 *
 * Uso:
 * include_once '../../middleware/check_permission.php';
 * check_permission($user_data, 'usuarios', 'create');
 */

function check_permission($user_data, $resource, $action) {
    $rol = $user_data->rol;

    // Definir matriz de permisos
    $permissions = [
        'admin' => [
            'usuarios' => ['read', 'create', 'update', 'delete'],
            'denuncias' => ['read', 'create', 'update', 'delete', 'reasignar'],
            'departamentos' => ['read', 'create', 'update', 'delete'],
            'categorias' => ['read', 'create', 'update', 'delete'],
            'reportes' => ['read', 'generate', 'export'],
            'heatmap' => ['view']
        ],
        'supervisor' => [
            'usuarios' => [],
            'denuncias' => ['read', 'create', 'update'],
            'departamentos' => ['read'],
            'categorias' => ['read'],
            'reportes' => ['read', 'generate', 'export'],
            'heatmap' => ['view']
        ],
        'operador' => [
            'usuarios' => [],
            'denuncias' => ['read', 'create', 'update'], // Solo de su departamento
            'departamentos' => ['read'],
            'categorias' => ['read'],
            'reportes' => ['generate'], // Solo de sus denuncias
            'heatmap' => ['view']
        ],
        'ciudadano' => [
            'usuarios' => [],
            'denuncias' => ['read', 'create'], // Solo las propias
            'departamentos' => ['read'],
            'categorias' => ['read'],
            'reportes' => [],
            'heatmap' => []
        ]
    ];

    // Verificar si el rol existe
    if (!isset($permissions[$rol])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'Rol no válido'
        ]);
        exit();
    }

    // Verificar si el recurso existe para ese rol
    if (!isset($permissions[$rol][$resource])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'No tiene acceso a este recurso'
        ]);
        exit();
    }

    // Verificar si tiene permiso para la acción
    if (!in_array($action, $permissions[$rol][$resource])) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => "No tiene permiso para realizar la acción: $action en $resource"
        ]);
        exit();
    }

    // Permiso concedido
    return true;
}

/**
 * Función auxiliar para verificar si puede ver todas las denuncias
 */
function can_view_all_denuncias($user_data) {
    return in_array($user_data->rol, ['admin', 'supervisor']);
}

/**
 * Función auxiliar para verificar si puede editar usuarios
 */
function can_manage_users($user_data) {
    return $user_data->rol === 'admin';
}
?>
```

**Ejemplo de uso:**

```php
// En cualquier endpoint
include_once '../../middleware/check_permission.php';
include_once '../../middleware/validate_jwt.php';

$user_data = validate_jwt();

// Verificar permiso específico
check_permission($user_data, 'usuarios', 'create');

// Si pasa la verificación, continuar con la lógica...
```

---

## 6. GOOGLE MAPS HEATMAP

### 6.1 Obtener API Key de Google Maps

**Pasos:**

1. Ir a [Google Cloud Console](https://console.cloud.google.com/)
2. Crear un nuevo proyecto o seleccionar uno existente
3. Habilitar las siguientes APIs:
   - **Maps JavaScript API**
   - **Geocoding API** (opcional, para convertir direcciones)
4. Ir a "Credenciales" → "Crear credenciales" → "Clave de API"
5. Copiar la API Key generada
6. **Restricciones recomendadas:**
   - Restricción de aplicación: Sitios web
   - Restricciones de sitios web: `http://localhost:5173`, `https://tudominio.com`
   - Restricciones de API: Solo "Maps JavaScript API"

### 6.2 Configuración en Frontend

**Archivo:** `frontend/.env`

```env
# Google Maps API Key
VITE_GOOGLE_MAPS_API_KEY=TU_API_KEY_AQUI

# Coordenadas del centro del mapa (Cusco, Perú)
VITE_MAP_CENTER_LAT=-13.5319
VITE_MAP_CENTER_LNG=-71.9675
VITE_MAP_ZOOM=13
```

### 6.3 Instalación de Dependencias

```bash
cd frontend
npm install @react-google-maps/api
```

### 6.4 Componente de Heatmap con Google Maps

**Archivo:** `frontend/src/components/GoogleHeatmap.jsx`

```jsx
import React, { useState, useEffect, useCallback } from 'react';
import { GoogleMap, LoadScript, HeatmapLayer } from '@react-google-maps/api';
import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost/DENUNCIA%20CIUDADANA/backend/api';
const GOOGLE_MAPS_API_KEY = import.meta.env.VITE_GOOGLE_MAPS_API_KEY;

const libraries = ['visualization']; // Necesario para HeatmapLayer

const GoogleHeatmap = () => {
  const [denuncias, setDenuncias] = useState([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);
  const [map, setMap] = useState(null);

  // Centro del mapa (Cusco, Perú)
  const mapCenter = {
    lat: parseFloat(import.meta.env.VITE_MAP_CENTER_LAT || -13.5319),
    lng: parseFloat(import.meta.env.VITE_MAP_CENTER_LNG || -71.9675)
  };

  // Opciones del mapa
  const mapOptions = {
    zoom: parseInt(import.meta.env.VITE_MAP_ZOOM || 13),
    center: mapCenter,
    mapTypeId: 'roadmap',
    styles: [
      {
        featureType: 'all',
        elementType: 'labels',
        stylers: [{ visibility: 'on' }]
      }
    ]
  };

  // Cargar coordenadas de denuncias
  useEffect(() => {
    fetchDenunciasLocations();
  }, []);

  const fetchDenunciasLocations = async () => {
    try {
      setLoading(true);
      const token = localStorage.getItem('jwt');

      const response = await axios.get(
        `${API_URL}/denuncias/locations.php`,
        {
          headers: {
            'Authorization': `Bearer ${token}`
          }
        }
      );

      if (response.data.success) {
        setDenuncias(response.data.data);
      } else {
        setError('No se pudieron cargar las ubicaciones');
      }
    } catch (err) {
      console.error('Error fetching locations:', err);
      setError('Error al cargar el mapa de calor');
    } finally {
      setLoading(false);
    }
  };

  // Convertir denuncias a formato de Google Maps Heatmap
  const getHeatmapData = useCallback(() => {
    if (!window.google || !denuncias.length) return [];

    return denuncias
      .filter(d => d.latitud && d.longitud)
      .map(denuncia => ({
        location: new window.google.maps.LatLng(
          parseFloat(denuncia.latitud),
          parseFloat(denuncia.longitud)
        ),
        weight: getWeight(denuncia.estado) // Peso según el estado
      }));
  }, [denuncias]);

  // Asignar peso según estado de la denuncia
  const getWeight = (estado) => {
    const weights = {
      'registrada': 1,
      'en_revision': 1.5,
      'asignada': 2,
      'en_proceso': 2.5,
      'resuelta': 0.5,  // Menos peso para resueltas
      'cerrada': 0.3,   // Muy poco peso
      'rechazada': 0.2
    };
    return weights[estado] || 1;
  };

  // Opciones del Heatmap
  const heatmapOptions = {
    radius: 30,
    opacity: 0.6,
    gradient: [
      'rgba(0, 255, 255, 0)',
      'rgba(0, 255, 255, 1)',
      'rgba(0, 191, 255, 1)',
      'rgba(0, 127, 255, 1)',
      'rgba(0, 63, 255, 1)',
      'rgba(0, 0, 255, 1)',
      'rgba(0, 0, 223, 1)',
      'rgba(0, 0, 191, 1)',
      'rgba(0, 0, 159, 1)',
      'rgba(0, 0, 127, 1)',
      'rgba(63, 0, 91, 1)',
      'rgba(127, 0, 63, 1)',
      'rgba(191, 0, 31, 1)',
      'rgba(255, 0, 0, 1)'
    ]
  };

  if (loading) {
    return (
      <div className="flex items-center justify-center h-96">
        <div className="text-lg">Cargando mapa de calor...</div>
      </div>
    );
  }

  if (error) {
    return (
      <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
        {error}
      </div>
    );
  }

  return (
    <div className="w-full h-full">
      <div className="mb-4 bg-white p-4 rounded-lg shadow">
        <h2 className="text-xl font-bold text-gray-800 mb-2">
          Mapa de Calor de Denuncias
        </h2>
        <p className="text-sm text-gray-600">
          Total de denuncias: <span className="font-semibold">{denuncias.length}</span>
        </p>
        <p className="text-xs text-gray-500 mt-1">
          Las zonas rojas indican mayor concentración de denuncias activas
        </p>
      </div>

      <div className="bg-white rounded-lg shadow overflow-hidden">
        <LoadScript
          googleMapsApiKey={GOOGLE_MAPS_API_KEY}
          libraries={libraries}
        >
          <GoogleMap
            mapContainerStyle={{
              width: '100%',
              height: '600px'
            }}
            center={mapCenter}
            zoom={mapOptions.zoom}
            onLoad={(map) => setMap(map)}
            options={mapOptions}
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

      {/* Leyenda */}
      <div className="mt-4 bg-white p-4 rounded-lg shadow">
        <h3 className="font-semibold text-gray-700 mb-2">Leyenda</h3>
        <div className="flex items-center space-x-4 text-sm">
          <div className="flex items-center">
            <div className="w-4 h-4 bg-blue-400 rounded mr-2"></div>
            <span>Baja densidad</span>
          </div>
          <div className="flex items-center">
            <div className="w-4 h-4 bg-yellow-400 rounded mr-2"></div>
            <span>Media densidad</span>
          </div>
          <div className="flex items-center">
            <div className="w-4 h-4 bg-red-600 rounded mr-2"></div>
            <span>Alta densidad</span>
          </div>
        </div>
      </div>
    </div>
  );
};

export default GoogleHeatmap;
```

### 6.5 Endpoint de Localizaciones

**Archivo:** `backend/api/denuncias/locations.php`

```php
<?php
/**
 * API Endpoint: Obtener Coordenadas para Heatmap
 *
 * GET /api/denuncias/locations.php
 *
 * Retorna array de coordenadas (lat, lng) de todas las denuncias
 * Filtrado por rol:
 * - Admin/Supervisor: Todas
 * - Operador: Solo de su departamento
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_department.php';

// Validar JWT
$user_data = validate_jwt();

// Database
$database = new Database();
$db = $database->getConnection();

// Aplicar filtro por departamento
$filter = filterDenunciasByDepartment($user_data);

if ($filter['filter_type'] === 'blocked') {
    http_response_code(403);
    echo json_encode(['error' => $filter['error_message']]);
    exit();
}

// Query con filtro
$query = "SELECT
    d.id,
    d.latitud,
    d.longitud,
    d.estado,
    d.created_at,
    c.nombre as categoria_nombre,
    dep.nombre as departamento_nombre
FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
INNER JOIN departamentos dep ON d.departamento_id = dep.id
WHERE {$filter['where_clause']}
  AND d.latitud IS NOT NULL
  AND d.longitud IS NOT NULL
ORDER BY d.created_at DESC";

try {
    $stmt = $db->prepare($query);
    $stmt->execute();
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($denuncias),
        'data' => $denuncias,
        'filter_applied' => $filter['filter_type']
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
```

### 6.6 Integración en Dashboard Admin

**Archivo:** `frontend/src/pages/admin/AdminDashboard.jsx`

```jsx
import React, { useState } from 'react';
import GoogleHeatmap from '../../components/GoogleHeatmap';
// ... otros imports

export default function AdminDashboard() {
  const [activeTab, setActiveTab] = useState('estadisticas'); // 'estadisticas' | 'usuarios' | 'heatmap'

  return (
    <div className="container mx-auto p-6">
      <h1 className="text-3xl font-bold text-primary mb-6">
        Panel de Administrador
      </h1>

      {/* Tabs */}
      <div className="mb-6 border-b border-gray-200">
        <nav className="-mb-px flex space-x-8">
          <button
            onClick={() => setActiveTab('estadisticas')}
            className={`py-4 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'estadisticas'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Estadísticas
          </button>
          <button
            onClick={() => setActiveTab('usuarios')}
            className={`py-4 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'usuarios'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Gestión de Usuarios
          </button>
          <button
            onClick={() => setActiveTab('heatmap')}
            className={`py-4 px-1 border-b-2 font-medium text-sm ${
              activeTab === 'heatmap'
                ? 'border-primary text-primary'
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            }`}
          >
            Mapa de Calor
          </button>
        </nav>
      </div>

      {/* Content */}
      {activeTab === 'estadisticas' && (
        <div>
          {/* Tu contenido actual de estadísticas */}
        </div>
      )}

      {activeTab === 'usuarios' && (
        <div>
          {/* Componente de gestión de usuarios */}
          <GestionUsuarios />
        </div>
      )}

      {activeTab === 'heatmap' && (
        <div>
          <GoogleHeatmap />
        </div>
      )}
    </div>
  );
}
```

---

## 7. DIAGRAMAS DE FLUJO

### 7.1 Flujo de Creación de Usuario por Admin

```
┌──────────────────────────────────────────────────────────────┐
│         FLUJO: Administrador Crea Operador                    │
└──────────────────────────────────────────────────────────────┘

[Admin hace login]
       │
       ├─→ JWT validado
       │
       ▼
[Dashboard Admin]
       │
       ├─→ Click en "Gestión de Usuarios"
       │
       ▼
[Formulario: Crear Usuario]
       │
       ├─→ Ingresa datos:
       │   - DNI: 12345678
       │   - Nombres: Juan
       │   - Apellidos: Pérez
       │   - Email: juan.perez@muni.gob.pe
       │   - Rol: Operador
       │   - Departamento: Medio Ambiente (ID: 1)
       │   - Password: *******
       │
       ▼
[Submit Form]
       │
       ├─→ POST /api/usuarios/create.php
       │
       ▼
[Backend: Validaciones]
       │
       ├─→ ¿JWT válido? ─────────── NO ─→ [403 Forbidden]
       │        │
       │       YES
       │        │
       ├─→ ¿Rol = admin? ────────── NO ─→ [403 Acceso Denegado]
       │        │
       │       YES
       │        │
       ├─→ ¿Datos completos? ─────── NO ─→ [400 Datos Incompletos]
       │        │
       │       YES
       │        │
       ├─→ ¿Email existe? ─────────── SÍ ─→ [400 Email Duplicado]
       │        │
       │        NO
       │        │
       ├─→ ¿DNI existe? ──────────── SÍ ─→ [400 DNI Duplicado]
       │        │
       │        NO
       │        │
       ├─→ ¿Rol = operador? ─────── SÍ ─→ ¿Departamento asignado?
       │        │                                │
       │        │                                NO
       │        │                                │
       │        │                          [400 Error: Operador
       │        │                           necesita departamento]
       │        │
       │       YES
       │        │
       ▼        ▼
[INSERT en tabla usuarios]
       │
       ├─→ Hash password (bcrypt)
       ├─→ departamento_id = 1 (Medio Ambiente)
       ├─→ rol = 'operador'
       ├─→ activo = TRUE
       │
       ▼
[Usuario Creado]
       │
       ├─→ [201 Created]
       │
       ▼
[Frontend: Mensaje de Éxito]
       │
       ├─→ "Usuario Juan Pérez creado exitosamente"
       ├─→ Actualizar lista de usuarios
       │
       ▼
[Operador ahora puede hacer login]
       │
       └─→ Solo verá denuncias de Medio Ambiente
```

### 7.2 Flujo de Enrutamiento Automático de Denuncia

```
┌──────────────────────────────────────────────────────────────┐
│       FLUJO: Enrutamiento Automático de Denuncia             │
└──────────────────────────────────────────────────────────────┘

[Ciudadano hace login]
       │
       ▼
[Formulario: Nueva Denuncia]
       │
       ├─→ Selecciona ubicación en mapa
       ├─→ Selecciona categoría: "Quema de Basura" (ID: 7)
       ├─→ Ingresa título y descripción
       ├─→ Sube evidencias (opcional)
       │
       ▼
[Submit Form]
       │
       ├─→ POST /api/denuncias/create.php
       │
       ▼
[Backend: Recibe Request]
       │
       ├─→ Validaciones:
       │   ✓ JWT válido
       │   ✓ Campos obligatorios completos
       │   ✓ Coordenadas GPS válidas
       │   ✓ Categoría existe
       │
       ▼
[Preparar INSERT]
       │
       ├─→ INSERT INTO denuncias SET
       │   - codigo = generado (DU-2025-000123)
       │   - usuario_id = [ID del ciudadano]
       │   - categoria_id = 7
       │   - titulo = "..."
       │   - descripcion = "..."
       │   - latitud = -13.5319
       │   - longitud = -71.9675
       │   - estado = 'registrada'
       │   - departamento_id = NULL ← Será llenado por trigger
       │
       ▼
[TRIGGER: tr_denuncias_asignar_departamento]
       │
       ├─→ BEFORE INSERT
       │
       ├─→ Lee: NEW.categoria_id = 7
       │
       ├─→ Query:
       │   SELECT departamento_id
       │   FROM categorias
       │   WHERE id = 7
       │
       ├─→ Resultado: departamento_id = 1 (Medio Ambiente)
       │
       ├─→ Asigna: SET NEW.departamento_id = 1
       │
       ▼
[INSERT se completa]
       │
       ├─→ Denuncia guardada con:
       │   - departamento_id = 1 (Medio Ambiente)
       │
       ▼
[Registro en tabla seguimiento]
       │
       ├─→ INSERT INTO seguimiento
       │   - estado_nuevo = 'registrada'
       │   - comentario = "Denuncia creada"
       │
       ▼
[Notificación a Operadores]
       │
       ├─→ SELECT id FROM usuarios
       │   WHERE rol = 'operador'
       │   AND departamento_id = 1
       │
       ├─→ Operadores encontrados:
       │   - Juan Pérez (ID: 5)
       │   - María López (ID: 6)
       │
       ├─→ INSERT INTO notificaciones (para cada operador)
       │
       ▼
[Response al Frontend]
       │
       ├─→ [201 Created]
       ├─→ { success: true, codigo: "DU-2025-000123" }
       │
       ▼
[Ciudadano recibe confirmación]
       │
       └─→ "Denuncia registrada: DU-2025-000123"
           "Asignada a: Medio Ambiente"


┌──────────────────────────────────────────────────────────────┐
│            VISTA DEL OPERADOR                                 │
└──────────────────────────────────────────────────────────────┘

[Operador Juan Pérez hace login]
       │
       ├─→ departamento_id = 1 (Medio Ambiente)
       │
       ▼
[Dashboard Operador]
       │
       ├─→ Query ejecutada:
       │   SELECT * FROM v_denuncias_por_departamento
       │   WHERE departamento_id = 1
       │   AND estado IN ('registrada', 'asignada', 'en_proceso')
       │
       ├─→ Resultado:
       │   - DU-2025-000123: Quema de Basura
       │   - DU-2025-000098: Basura acumulada
       │   - (solo denuncias de Medio Ambiente)
       │
       ▼
[Operador ve su bandeja filtrada]
       │
       └─→ NO ve denuncias de otros departamentos
           (ej: Seguridad, Obras Públicas)


┌──────────────────────────────────────────────────────────────┐
│            VISTA DEL ADMIN                                    │
└──────────────────────────────────────────────────────────────┘

[Admin hace login]
       │
       ▼
[Dashboard Admin]
       │
       ├─→ Query ejecutada:
       │   SELECT * FROM denuncias
       │   (sin filtro de departamento)
       │
       ├─→ Resultado:
       │   - DU-2025-000123: Medio Ambiente
       │   - DU-2025-000122: Seguridad Ciudadana
       │   - DU-2025-000121: Obras Públicas
       │   - ... (TODAS las denuncias)
       │
       ▼
[Admin ve TODO]
       │
       ├─→ Puede ver estadísticas globales
       ├─→ Puede reasignar departamentos
       └─→ Puede gestionar usuarios
```

### 7.3 Decisión de Acceso Según Rol

```
┌──────────────────────────────────────────────────────────────┐
│         ÁRBOL DE DECISIÓN: Control de Acceso                 │
└──────────────────────────────────────────────────────────────┘

Usuario hace request a /api/denuncias/read.php
                │
                ▼
        [Validar JWT]
                │
        ┌───────┴───────┐
        │               │
       FAIL           SUCCESS
        │               │
        ▼               ▼
  [401 Unauthorized] [Extraer user_data]
                         │
                         ▼
                  [Verificar ROL]
                         │
        ┌────────────────┼────────────────┐
        │                │                │
       ADMIN          SUPERVISOR       OPERADOR
        │                │                │
        ▼                ▼                ▼
    [Query SIN      [Query SIN       [Query CON filtro]
     filtro]         filtro]              │
        │                │                ▼
        │                │         [Obtener departamento_id
        │                │          del usuario]
        │                │                │
        │                │          ┌─────┴─────┐
        │                │          │           │
        │                │       dept_id     dept_id
        │                │       != NULL     = NULL
        │                │          │           │
        │                │          ▼           ▼
        │                │   [WHERE d.dept_id  [403: Sin
        │                │    = usuario.dept]   departamento]
        │                │
        ├────────────────┴────────────────┐
        │                                 │
        ▼                                 ▼
  [SELECT *            [SELECT * FROM denuncias d
   FROM denuncias]      WHERE d.departamento_id = X]
        │                                 │
        └─────────────┬───────────────────┘
                      │
                      ▼
              [Retornar JSON con
               denuncias filtradas]
```

---

## 8. CÓDIGO DE IMPLEMENTACIÓN

### 8.1 Script SQL Completo de Migración

**Archivo:** `database/migrations/002_add_departamentos.sql`

```sql
-- ============================================================================
-- MIGRACIÓN: Agregar Sistema de Departamentos
-- Versión: 2.0
-- Fecha: 20/12/2025
-- ============================================================================

-- PASO 1: Crear tabla departamentos
-- ============================================================================
CREATE TABLE IF NOT EXISTS departamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL UNIQUE,
    descripcion TEXT,
    codigo VARCHAR(20) UNIQUE,
    responsable VARCHAR(150),
    email_contacto VARCHAR(150),
    telefono VARCHAR(20),
    color VARCHAR(7) DEFAULT '#3B82F6',
    icono VARCHAR(50),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_activo (activo),
    INDEX idx_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- PASO 2: Insertar departamentos iniciales
-- ============================================================================
INSERT INTO departamentos (nombre, descripcion, codigo, responsable, email_contacto, telefono, color, icono) VALUES
('Medio Ambiente', 'Gestión ambiental, limpieza, reciclaje, áreas verdes', 'MA', 'Biol. Luis García', 'ambiente@muni.gob.pe', '984123456', '#10B981', 'leaf'),
('Obras Públicas', 'Infraestructura, pistas, veredas, edificaciones', 'OP', 'Ing. María Rodríguez', 'obras@muni.gob.pe', '984234567', '#F59E0B', 'hammer'),
('Seguridad Ciudadana', 'Seguridad, vigilancia, emergencias', 'SC', 'Mayor Juan Pérez', 'seguridad@muni.gob.pe', '984345678', '#EF4444', 'shield'),
('Servicios Públicos', 'Agua, desagüe, electricidad, alumbrado', 'SP', 'Ing. Carlos Mendoza', 'servicios@muni.gob.pe', '984456789', '#3B82F6', 'lightbulb'),
('Transporte y Vialidad', 'Transporte público, señalización, tránsito', 'TV', 'Ing. Roberto Sánchez', 'transporte@muni.gob.pe', '984567890', '#8B5CF6', 'truck'),
('Desarrollo Urbano', 'Planificación urbana, licencias, habilitaciones', 'DU', 'Arq. Ana Torres', 'desarrollo@muni.gob.pe', '984678901', '#EC4899', 'building');

-- PASO 3: Modificar tabla usuarios
-- ============================================================================
ALTER TABLE usuarios
ADD COLUMN IF NOT EXISTS departamento_id INT DEFAULT NULL AFTER rol,
ADD CONSTRAINT fk_usuarios_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_usuarios_departamento ON usuarios(departamento_id);

-- PASO 4: Modificar tabla categorias
-- ============================================================================
ALTER TABLE categorias
ADD COLUMN IF NOT EXISTS departamento_id INT DEFAULT NULL AFTER descripcion;

-- Asignar departamentos a categorías existentes
UPDATE categorias SET departamento_id = 1 WHERE nombre LIKE '%Basura%' OR nombre LIKE '%Limpieza%';
UPDATE categorias SET departamento_id = 2 WHERE nombre LIKE '%Baches%' OR nombre LIKE '%Pistas%' OR nombre LIKE '%Veredas%';
UPDATE categorias SET departamento_id = 3 WHERE nombre LIKE '%Seguridad%';
UPDATE categorias SET departamento_id = 4 WHERE nombre LIKE '%Agua%' OR nombre LIKE '%Desagüe%' OR nombre LIKE '%Alumbrado%';
UPDATE categorias SET departamento_id = 1 WHERE nombre LIKE '%Parques%' OR nombre LIKE '%Jardines%' OR nombre LIKE '%Contaminación%' OR nombre LIKE '%Ruido%';
UPDATE categorias SET departamento_id = 5 WHERE nombre LIKE '%Transporte%';
UPDATE categorias SET departamento_id = 6 WHERE nombre LIKE '%Edificaciones%';
UPDATE categorias SET departamento_id = 4 WHERE departamento_id IS NULL; -- Default: Servicios Públicos

-- Ahora hacer la columna NOT NULL
ALTER TABLE categorias
MODIFY COLUMN departamento_id INT NOT NULL;

ALTER TABLE categorias
ADD CONSTRAINT fk_categorias_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE RESTRICT;

CREATE INDEX IF NOT EXISTS idx_categorias_departamento ON categorias(departamento_id);

-- PASO 5: Modificar tabla denuncias
-- ============================================================================
-- Opción A: Renombrar area_asignada_id a departamento_id
-- ALTER TABLE denuncias
-- CHANGE COLUMN area_asignada_id departamento_id INT DEFAULT NULL;

-- Opción B: Agregar nueva columna (recomendado para no perder datos)
ALTER TABLE denuncias
ADD COLUMN IF NOT EXISTS departamento_id INT DEFAULT NULL AFTER categoria_id;

-- Migrar datos de area_asignada_id a departamento_id si existen
-- UPDATE denuncias SET departamento_id = area_asignada_id WHERE area_asignada_id IS NOT NULL;

ALTER TABLE denuncias
ADD CONSTRAINT fk_denuncias_departamento
    FOREIGN KEY (departamento_id)
    REFERENCES departamentos(id)
    ON DELETE SET NULL;

CREATE INDEX IF NOT EXISTS idx_denuncias_departamento ON denuncias(departamento_id);

-- PASO 6: Crear trigger de asignación automática
-- ============================================================================
DROP TRIGGER IF EXISTS tr_denuncias_asignar_departamento;

DELIMITER $$

CREATE TRIGGER tr_denuncias_asignar_departamento
BEFORE INSERT ON denuncias
FOR EACH ROW
BEGIN
    DECLARE dept_id INT;

    -- Obtener departamento basándose en la categoría
    SELECT departamento_id INTO dept_id
    FROM categorias
    WHERE id = NEW.categoria_id;

    -- Asignar automáticamente
    SET NEW.departamento_id = dept_id;
END$$

DELIMITER ;

-- PASO 7: Crear vista optimizada
-- ============================================================================
CREATE OR REPLACE VIEW v_denuncias_por_departamento AS
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at,
    d.updated_at,
    d.usuario_id,
    d.departamento_id,
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.es_anonima,

    -- Información de categoría
    c.id AS categoria_id,
    c.nombre AS categoria_nombre,
    c.icono AS categoria_icono,

    -- Información de departamento
    dep.id AS departamento_asignado_id,
    dep.nombre AS departamento_nombre,
    dep.codigo AS departamento_codigo,
    dep.color AS departamento_color,
    dep.responsable AS departamento_responsable,

    -- Información del ciudadano
    CASE
        WHEN d.es_anonima = FALSE THEN CONCAT(u.nombres, ' ', u.apellidos)
        ELSE 'Anónimo'
    END AS ciudadano_nombre,

    u.email AS ciudadano_email,
    u.telefono AS ciudadano_telefono

FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN departamentos dep ON d.departamento_id = dep.id
LEFT JOIN usuarios u ON d.usuario_id = u.id;

-- PASO 8: Actualizar denuncias existentes con departamento
-- ============================================================================
-- Asignar departamento a denuncias que no lo tienen
UPDATE denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
SET d.departamento_id = c.departamento_id
WHERE d.departamento_id IS NULL;

-- ============================================================================
-- FIN DE LA MIGRACIÓN
-- ============================================================================

-- Verificar resultados
SELECT 'Departamentos creados:' as status, COUNT(*) as total FROM departamentos;
SELECT 'Categorías vinculadas:' as status, COUNT(*) as total FROM categorias WHERE departamento_id IS NOT NULL;
SELECT 'Denuncias con departamento:' as status, COUNT(*) as total FROM denuncias WHERE departamento_id IS NOT NULL;
```

### 8.2 Modelo de Departamento (PHP)

**Archivo:** `backend/models/Departamento.php`

```php
<?php
class Departamento {
    private $conn;
    private $table_name = "departamentos";

    public $id;
    public $nombre;
    public $descripcion;
    public $codigo;
    public $responsable;
    public $email_contacto;
    public $telefono;
    public $color;
    public $icono;
    public $activo;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Obtener todos los departamentos activos
     */
    public function read() {
        $query = "SELECT
                    id,
                    nombre,
                    descripcion,
                    codigo,
                    responsable,
                    email_contacto,
                    telefono,
                    color,
                    icono,
                    activo,
                    created_at
                FROM " . $this->table_name . "
                WHERE activo = TRUE
                ORDER BY nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();

        return $stmt;
    }

    /**
     * Obtener departamento por ID
     */
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :id LIMIT 1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $this->nombre = $row['nombre'];
            $this->descripcion = $row['descripcion'];
            $this->codigo = $row['codigo'];
            $this->responsable = $row['responsable'];
            $this->email_contacto = $row['email_contacto'];
            $this->telefono = $row['telefono'];
            $this->color = $row['color'];
            $this->icono = $row['icono'];
            $this->activo = $row['activo'];
            return true;
        }

        return false;
    }

    /**
     * Crear departamento (solo admin)
     */
    public function create() {
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    codigo = :codigo,
                    responsable = :responsable,
                    email_contacto = :email,
                    telefono = :telefono,
                    color = :color,
                    icono = :icono,
                    activo = TRUE";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $this->codigo = htmlspecialchars(strip_tags($this->codigo));

        // Bind
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':codigo', $this->codigo);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':email', $this->email_contacto);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':icono', $this->icono);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * Actualizar departamento
     */
    public function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    nombre = :nombre,
                    descripcion = :descripcion,
                    responsable = :responsable,
                    email_contacto = :email,
                    telefono = :telefono,
                    color = :color,
                    icono = :icono,
                    activo = :activo
                WHERE id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':responsable', $this->responsable);
        $stmt->bindParam(':email', $this->email_contacto);
        $stmt->bindParam(':telefono', $this->telefono);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':icono', $this->icono);
        $stmt->bindParam(':activo', $this->activo);

        return $stmt->execute();
    }

    /**
     * Obtener estadísticas del departamento
     */
    public function getEstadisticas() {
        $query = "SELECT
                    COUNT(*) as total_denuncias,
                    SUM(CASE WHEN estado = 'registrada' THEN 1 ELSE 0 END) as registradas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                    SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas
                FROM denuncias
                WHERE departamento_id = :id";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Obtener operadores del departamento
     */
    public function getOperadores() {
        $query = "SELECT
                    id,
                    dni,
                    CONCAT(nombres, ' ', apellidos) as nombre_completo,
                    email,
                    telefono,
                    activo
                FROM usuarios
                WHERE departamento_id = :id
                  AND rol = 'operador'
                ORDER BY apellidos, nombres";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        return $stmt;
    }
}
?>
```

---

## 9. SEGURIDAD Y ESCALABILIDAD

### 9.1 Medidas de Seguridad Implementadas

| Capa | Medida | Implementación |
|------|--------|----------------|
| **Autenticación** | JWT con expiración | Token expira en 24h |
| **Contraseñas** | Bcrypt hash | Cost factor: 12 |
| **SQL Injection** | Prepared Statements | Todos los queries con PDO |
| **XSS** | Sanitización | htmlspecialchars() + strip_tags() |
| **CSRF** | Token CSRF | En formularios sensibles |
| **Autorización** | RBAC estricto | Middleware de permisos |
| **Rate Limiting** | Límite de requests | Por implementar (Nginx/Redis) |
| **HTTPS** | SSL/TLS | Obligatorio en producción |
| **Logging** | Auditoría | Logs de acciones críticas |

### 9.2 Auditoría de Acciones (Tabla de Logs)

**Crear tabla de auditoría:**

```sql
CREATE TABLE logs_auditoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    accion VARCHAR(100) NOT NULL, -- 'crear_usuario', 'eliminar_denuncia', etc.
    recurso VARCHAR(100), -- 'usuarios', 'denuncias', etc.
    recurso_id INT, -- ID del recurso afectado
    detalles JSON, -- Información adicional
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_usuario (usuario_id),
    INDEX idx_accion (accion),
    INDEX idx_fecha (created_at),

    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Función para registrar logs:**

```php
function log_auditoria($db, $usuario_id, $accion, $recurso, $recurso_id, $detalles = []) {
    $query = "INSERT INTO logs_auditoria
        (usuario_id, accion, recurso, recurso_id, detalles, ip_address, user_agent)
        VALUES
        (:usuario_id, :accion, :recurso, :recurso_id, :detalles, :ip, :ua)";

    $stmt = $db->prepare($query);

    $stmt->bindParam(':usuario_id', $usuario_id);
    $stmt->bindParam(':accion', $accion);
    $stmt->bindParam(':recurso', $recurso);
    $stmt->bindParam(':recurso_id', $recurso_id);
    $stmt->bindValue(':detalles', json_encode($detalles));
    $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR']);
    $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT']);

    $stmt->execute();
}

// Uso:
log_auditoria($db, $admin_id, 'crear_usuario', 'usuarios', $nuevo_usuario_id, [
    'email' => $nuevo_email,
    'rol' => 'operador',
    'departamento_id' => 1
]);
```

### 9.3 Escalabilidad

**Consideraciones para crecimiento:**

1. **Índices de Base de Datos:**
   - ✅ Ya implementados en columnas de búsqueda frecuente
   - Monitorear EXPLAIN de queries lentas

2. **Caché:**
   - Implementar Redis para:
     - Cache de sesiones JWT
     - Cache de consultas frecuentes (categorías, departamentos)
     - Rate limiting

3. **Paginación:**
   - Implementar LIMIT/OFFSET en listados
   - Usar cursor-based pagination para grandes volúmenes

4. **Archivos de Evidencias:**
   - Migrar a S3 / Cloud Storage
   - Implementar CDN para imágenes

5. **Base de Datos:**
   - Configurar read replicas para consultas
   - Particionar tabla denuncias por fecha si crece mucho

---

## 10. PLAN DE IMPLEMENTACIÓN

### 10.1 Fases de Desarrollo

**FASE 1: Base de Datos y Migraciones (1-2 días)**
- [ ] Ejecutar script de migración SQL
- [ ] Verificar foreign keys y triggers
- [ ] Poblar datos de prueba
- [ ] Crear respaldos de BD

**FASE 2: Backend - Endpoints de Usuario (2-3 días)**
- [ ] Crear endpoints CRUD de usuarios
- [ ] Implementar middleware de permisos
- [ ] Crear endpoint de departamentos
- [ ] Testing de endpoints con Postman

**FASE 3: Backend - Lógica de Enrutamiento (1-2 días)**
- [ ] Implementar middleware de filtrado
- [ ] Adaptar endpoints existentes de denuncias
- [ ] Crear endpoint de reasignación
- [ ] Testing de filtrado por departamento

**FASE 4: Frontend - Gestión de Usuarios (3-4 días)**
- [ ] Componente de lista de usuarios
- [ ] Formulario de creación de usuario
- [ ] Formulario de edición
- [ ] Modal de confirmación de eliminación
- [ ] Integración con API

**FASE 5: Frontend - Google Maps Heatmap (2-3 días)**
- [ ] Obtener API Key de Google Maps
- [ ] Instalar dependencias (@react-google-maps/api)
- [ ] Crear componente GoogleHeatmap
- [ ] Integrar en AdminDashboard
- [ ] Optimizar performance

**FASE 6: Testing y QA (2-3 días)**
- [ ] Testing funcional de cada rol
- [ ] Testing de permisos (intentar accesos no autorizados)
- [ ] Testing de enrutamiento automático
- [ ] Testing de filtrado por departamento
- [ ] Corrección de bugs

**FASE 7: Documentación y Deployment (1-2 días)**
- [ ] Documentar endpoints nuevos
- [ ] Actualizar README
- [ ] Guía de usuario para admins
- [ ] Deploy a staging
- [ ] Deploy a producción

**TOTAL ESTIMADO: 12-19 días hábiles (2.5 - 4 semanas)**

### 10.2 Prioridades

**CRÍTICAS (Hacer primero):**
1. ✅ Migración de base de datos
2. ✅ Trigger de asignación automática
3. ✅ Middleware de filtrado por departamento
4. ✅ CRUD de usuarios (admin)

**ALTAS (Hacer después):**
5. ✅ Google Maps Heatmap
6. ✅ Auditoría de acciones
7. ✅ Testing de permisos

**MEDIAS (Opcional pero recomendado):**
8. ⚠️ Notificaciones en tiempo real
9. ⚠️ Paginación en listas
10. ⚠️ Exportar a Excel/PDF

---

## 📝 CONCLUSIÓN

Esta arquitectura proporciona:

✅ **Enrutamiento Automático** - Las denuncias se asignan automáticamente al departamento correcto basándose en la categoría seleccionada.

✅ **RBAC Estricto** - Cada rol tiene permisos claramente definidos y validados en cada endpoint.

✅ **Gestión de Usuarios Exclusiva** - Solo el administrador puede crear, editar y eliminar usuarios.

✅ **Filtrado Automático** - Los operadores solo ven denuncias de su departamento asignado.

✅ **Vista Global para Admin** - El administrador ve todas las denuncias sin filtros.

✅ **Google Maps Heatmap** - Visualización profesional de densidad de denuncias.

✅ **Escalable y Seguro** - Diseñado con mejores prácticas de seguridad y preparado para crecer.

---

**¿Listo para implementar?** Comienza ejecutando el script de migración SQL y luego continúa con las fases en orden. 🚀
