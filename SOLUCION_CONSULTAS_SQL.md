# 🏗️ Solución Completa: Consultas SQL Corregidas

## 🔍 Diagnóstico del Problema

### ❌ Errores Identificados

1. **Error Crítico: Campo `fecha_registro` no existe**
   - **Ubicación:** `backend/models/Denuncia.php` líneas 109, 115, 125, 135
   - **Problema:** Las consultas usan `d.fecha_registro` pero en la base de datos el campo se llama `created_at`
   - **Impacto:** Las consultas SQL fallan y no devuelven datos

2. **Error de JOIN: Falta unión con tabla `categorias`**
   - **Problema:** No se obtiene el nombre de la categoría, solo el `categoria_id`
   - **Impacto:** El frontend no puede mostrar "Alumbrado Público", solo muestra el ID "3"

3. **Error de JOIN: Falta unión con tabla `areas_municipales`**
   - **Problema:** No se obtiene el nombre del área asignada
   - **Impacto:** No se puede mostrar "Obras Públicas", solo el ID

4. **Uso correcto de LEFT JOIN vs INNER JOIN**
   - **Tu diagnóstico era correcto:** Algunas tablas deben usar LEFT JOIN porque pueden tener NULL
   - `area_asignada_id` puede ser NULL → **LEFT JOIN**
   - `categoria_id` nunca es NULL → **INNER JOIN**
   - `usuario_id` puede ser NULL (denuncias anónimas) → **LEFT JOIN**

---

## ✅ Solución Implementada

### Archivos Modificados

1. ✅ `backend/models/Denuncia.php` - Completamente refactorizado
2. ✅ `backend/api/denuncias/read.php` - Actualizado para usar nuevas consultas
3. ✅ `backend/test_consultas.php` - Script de prueba creado

---

## 📊 Consultas SQL por Rol

### 1. Consulta para CIUDADANO

**Archivo:** `Denuncia.php` → `readForCiudadano($usuario_id)`

```sql
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at as fecha_registro,           -- ✅ Corregido: created_at en lugar de fecha_registro
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.categoria_id,
    d.area_asignada_id,
    -- Nombre de la categoría (INNER JOIN porque es obligatorio)
    c.nombre as categoria_nombre,              -- ✅ Nuevo campo
    c.icono as categoria_icono,                -- ✅ Nuevo campo
    -- Área asignada (LEFT JOIN porque puede ser NULL)
    a.nombre as area_nombre                    -- ✅ Nuevo campo
FROM
    denuncias d
    INNER JOIN categorias c ON d.categoria_id = c.id         -- ✅ INNER porque categoria_id nunca es NULL
    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id  -- ✅ LEFT porque puede ser NULL
WHERE
    d.usuario_id = :usuario_id
ORDER BY
    d.created_at DESC
```

**Características:**
- ✅ Solo ve sus propias denuncias
- ✅ Muestra nombre de categoría y área
- ✅ LEFT JOIN en `area_asignada_id` permite ver denuncias sin asignar
- ✅ Ordenadas por fecha descendente

**Respuesta JSON esperada:**
```json
{
  "records": [
    {
      "id": 1,
      "codigo": "DU-2025-000001",
      "titulo": "Fuga de agua en la calle principal",
      "estado": "registrada",
      "fecha_registro": "2025-12-18 10:30:00",
      "categoria_nombre": "Servicios Básicos",
      "categoria_icono": "💧",
      "area_nombre": "No asignada"         // ← NULL mapeado correctamente
    }
  ]
}
```

---

### 2. Consulta para ADMINISTRADOR

**Archivo:** `Denuncia.php` → `readForAdmin()`

```sql
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at as fecha_registro,                      -- ✅ Corregido
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.es_anonima,
    d.usuario_id,
    d.categoria_id,
    d.area_asignada_id,
    -- Datos del usuario (LEFT JOIN porque puede ser anónimo)
    CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,  -- ✅ Nombre completo
    u.email as usuario_email,                               -- ✅ Nuevo
    u.telefono as usuario_telefono,                         -- ✅ Nuevo
    -- Nombre de la categoría
    c.nombre as categoria_nombre,                           -- ✅ Nuevo
    c.icono as categoria_icono,                             -- ✅ Nuevo
    -- Área asignada (LEFT JOIN porque puede ser NULL)
    a.nombre as area_nombre,                                -- ✅ Nuevo
    a.responsable as area_responsable                       -- ✅ Nuevo
FROM
    denuncias d
    LEFT JOIN usuarios u ON d.usuario_id = u.id              -- ✅ LEFT porque puede ser anónimo
    INNER JOIN categorias c ON d.categoria_id = c.id         -- ✅ INNER porque es obligatorio
    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id  -- ✅ LEFT porque puede ser NULL
ORDER BY
    d.created_at DESC
```

**Características:**
- ✅ Ve TODAS las denuncias sin filtros
- ✅ Incluye datos completos del ciudadano
- ✅ Incluye información de área asignada y responsable
- ✅ Maneja correctamente denuncias anónimas (usuario_nombre = NULL)
- ✅ Maneja correctamente denuncias sin asignar (area_nombre = NULL)

**Respuesta JSON esperada:**
```json
{
  "records": [
    {
      "id": 1,
      "codigo": "DU-2025-000001",
      "titulo": "Fuga de agua",
      "estado": "registrada",
      "usuario_nombre": "Juan Pérez",
      "usuario_email": "juan@email.com",
      "usuario_telefono": "987654321",
      "categoria_nombre": "Servicios Básicos",
      "area_nombre": "No asignada",
      "area_responsable": null
    },
    {
      "id": 2,
      "codigo": "DU-2025-000002",
      "titulo": "Bache en avenida",
      "estado": "asignada",
      "usuario_nombre": "Anónimo",              // ← Denuncia anónima
      "categoria_nombre": "Infraestructura",
      "area_nombre": "Obras Públicas",          // ← Asignada correctamente
      "area_responsable": "Ing. Carlos López"
    }
  ]
}
```

---

### 3. Consulta para SUPERVISOR y OPERADOR

**Archivo:** `Denuncia.php` → `readForStaff($estados_permitidos)`

```sql
SELECT
    d.id,
    d.codigo,
    d.titulo,
    d.descripcion,
    d.estado,
    d.created_at as fecha_registro,                         -- ✅ Corregido
    d.latitud,
    d.longitud,
    d.direccion_referencia,
    d.usuario_id,
    d.categoria_id,
    d.area_asignada_id,
    d.es_anonima,
    -- Datos del ciudadano (para contacto)
    CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
    u.email as usuario_email,
    u.telefono as usuario_telefono,
    -- Nombre de la categoría
    c.nombre as categoria_nombre,
    c.icono as categoria_icono,
    -- Área asignada (puede ser NULL si aún no está asignada)
    a.nombre as area_nombre,                                -- ✅ LEFT JOIN permite NULL
    a.responsable as area_responsable
FROM
    denuncias d
    LEFT JOIN usuarios u ON d.usuario_id = u.id
    INNER JOIN categorias c ON d.categoria_id = c.id
    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
WHERE
    d.estado IN ('registrada', 'en_revision', 'asignada', 'en_proceso')  -- ✅ Filtro por estados
ORDER BY
    d.created_at DESC
```

**Características:**
- ✅ Solo ve denuncias en estados relevantes (no cerradas/rechazadas)
- ✅ Incluye datos de contacto del ciudadano
- ✅ **IMPORTANTE:** Muestra denuncias con estado 'registrada' AUNQUE area_asignada_id sea NULL
- ✅ Esto era tu requisito clave

**Respuesta JSON esperada:**
```json
{
  "records": [
    {
      "id": 5,
      "codigo": "DU-2025-000005",
      "titulo": "Alumbrado público dañado",
      "estado": "registrada",                 // ← Estado registrada
      "area_nombre": "No asignada",           // ← NULL, pero aparece correctamente
      "usuario_nombre": "María González",
      "usuario_email": "maria@email.com",
      "categoria_nombre": "Alumbrado Público"
    },
    {
      "id": 4,
      "codigo": "DU-2025-000004",
      "titulo": "Parque descuidado",
      "estado": "asignada",                   // ← Ya asignada
      "area_nombre": "Parques y Jardines",    // ← Con área asignada
      "area_responsable": "Lic. Ana Torres"
    }
  ]
}
```

---

## 🔧 Cambios Técnicos Implementados

### 1. Modelo `Denuncia.php`

#### Métodos nuevos creados:
```php
// Para administrador (consulta más completa)
function readForAdmin()

// Para ciudadano (solo sus denuncias)
function readForCiudadano($usuario_id)

// Para staff (filtro por estados)
function readForStaff($estados_permitidos = ['registrada', 'en_revision', 'asignada', 'en_proceso'])

// Para estadísticas de dashboard
function getEstadisticas($usuario_id = null)
```

#### Métodos existentes actualizados (compatibilidad):
```php
// Delega a readForAdmin
function read()

// Delega a readForCiudadano
function readByUsuario($usuario_id)
```

#### Corrección en `update()`:
```php
// Antes:
$stmt->bindParam(':area_asignada_id', $this->area_asignada_id);

// Ahora:
$this->area_asignada_id = $this->area_asignada_id === '' ? null : $this->area_asignada_id;
$stmt->bindParam(':area_asignada_id', $this->area_asignada_id);
```
**Razón:** Permite actualizar a NULL correctamente cuando se desasigna un área.

---

### 2. Endpoint `read.php`

#### Cambios principales:
```php
// Antes:
if ($user_data->rol === 'ciudadano') {
    $stmt = $denuncia->readByUsuario($user_data->id);
} else {
    $stmt = $denuncia->read();
}

// Ahora:
if ($user_data->rol === 'ciudadano') {
    $stmt = $denuncia->readForCiudadano($user_data->id);
} elseif ($user_data->rol === 'admin') {
    $stmt = $denuncia->readForAdmin();
} elseif ($user_data->rol === 'supervisor' || $user_data->rol === 'operador') {
    $stmt = $denuncia->readForStaff(['registrada', 'en_revision', 'asignada', 'en_proceso']);
}
```

#### Respuesta JSON enriquecida:
```php
$denuncia_item = array(
    // ... campos existentes ...

    // ✅ Nuevos campos JOIN
    "categoria_nombre" => $categoria_nombre ?? 'Sin categoría',
    "categoria_icono" => $categoria_icono ?? null,
    "area_nombre" => $area_nombre ?? 'No asignada',
    "area_responsable" => $area_responsable ?? null,
    "usuario_nombre" => $usuario_nombre ?? 'Anónimo',
    "usuario_email" => $usuario_email ?? null,
    "usuario_telefono" => $usuario_telefono ?? null,
);
```

---

## 🧪 Cómo Probar la Solución

### Paso 1: Ejecutar el script de prueba

```bash
# Abrir en el navegador:
http://localhost/DENUNCIA%20CIUDADANA/backend/test_consultas.php
```

**Qué hace este script:**
1. Prueba la consulta de administrador
2. Prueba la consulta de ciudadano
3. Prueba la consulta de staff
4. Verifica que LEFT JOIN funciona con NULL
5. Muestra estadísticas por estado

**Resultado esperado:**
```
✅ Todas las consultas SQL están corregidas
✅ Se usa LEFT JOIN para area_asignada_id (permite NULL)
✅ Se usa INNER JOIN para categoria_id (obligatorio)
✅ El campo fecha_registro se mapea correctamente a created_at
✅ Cada rol tiene su consulta específica optimizada
```

---

### Paso 2: Probar desde el frontend

#### 2.1 Dashboard de Ciudadano

1. Login como ciudadano
2. Ir a "Mis Denuncias"
3. Deberías ver:
   - ✅ Código de denuncia
   - ✅ Título
   - ✅ **Nombre de categoría** (no solo ID)
   - ✅ Estado
   - ✅ Fecha

#### 2.2 Dashboard de Administrador

1. Login como admin
2. Ir a dashboard
3. Deberías ver:
   - ✅ Todas las denuncias del sistema
   - ✅ Nombre del ciudadano
   - ✅ **Nombre de categoría**
   - ✅ **Nombre de área** (o "No asignada")

#### 2.3 Dashboard de Supervisor/Operador

1. Login como supervisor u operador
2. Ir a dashboard
3. Deberías ver:
   - ✅ Denuncias en estados: registrada, en_revision, asignada, en_proceso
   - ✅ **Incluye denuncias con estado 'registrada' aunque area_asignada_id sea NULL**
   - ✅ Nombre de categoría y área

---

## 📋 Resumen de Correcciones

| Error | Solución | Archivo | Línea |
|-------|----------|---------|-------|
| Campo `fecha_registro` no existe | Cambio a `d.created_at as fecha_registro` | `Denuncia.php` | 129, 173, 216 |
| Falta nombre de categoría | Agregado `INNER JOIN categorias` | `Denuncia.php` | 150, 186, 237 |
| Falta nombre de área | Agregado `LEFT JOIN areas_municipales` | `Denuncia.php` | 151, 187, 238 |
| INNER JOIN oculta NULL | Cambio a LEFT JOIN en área | `Denuncia.php` | 151, 187, 238 |
| Sin datos de usuario | Agregado `CONCAT(u.nombres, ' ', u.apellidos)` | `Denuncia.php` | 138, 225 |
| Sin filtro por estados para staff | Agregado WHERE IN con estados | `Denuncia.php` | 240 |
| Response sin campos JOIN | Agregados campos en array de respuesta | `read.php` | 122-129 |

---

## 🎯 Por Qué Ahora Funciona

### Antes (❌ NO FUNCIONABA):
```sql
-- Campo inexistente
SELECT d.fecha_registro FROM denuncias d

-- Sin nombres, solo IDs
SELECT d.categoria_id, d.area_asignada_id FROM denuncias d

-- INNER JOIN ocultaba registros con NULL
INNER JOIN areas_municipales a ON d.area_asignada_id = a.id
```

**Resultado:** Consulta fallaba o devolvía array vacío

---

### Ahora (✅ FUNCIONA):
```sql
-- Campo correcto
SELECT d.created_at as fecha_registro FROM denuncias d

-- Nombres de relaciones
SELECT c.nombre as categoria_nombre, a.nombre as area_nombre

-- LEFT JOIN mantiene registros con NULL
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
```

**Resultado:** Consulta exitosa con todos los datos

---

## 🚀 Próximos Pasos

### 1. Actualizar el Frontend

Si el frontend está mostrando solo IDs, actualizar para usar los nuevos campos:

**Antes:**
```javascript
<td>{denuncia.categoria_id}</td>  // Muestra: "3"
```

**Ahora:**
```javascript
<td>{denuncia.categoria_nombre}</td>  // Muestra: "Alumbrado Público"
<td>
  {denuncia.categoria_icono && <span>{denuncia.categoria_icono}</span>}
  {denuncia.categoria_nombre}
</td>
```

### 2. Usar el método getEstadisticas()

Para los dashboards con gráficos:

```php
// En read.php o en un nuevo endpoint stats.php
$stats = $denuncia->getEstadisticas($user_data->id); // O null para globales

echo json_encode($stats);
```

**Respuesta:**
```json
{
  "total": 150,
  "registradas": 45,
  "en_revision": 12,
  "asignadas": 23,
  "en_proceso": 34,
  "resueltas": 28,
  "cerradas": 6,
  "rechazadas": 2
}
```

### 3. Eliminar logging de depuración

Una vez que todo funcione:
- Quitar `console.log()` del frontend
- Eliminar `backend/test_consultas.php`

---

## 📚 Documentación de las Tablas

### Relaciones en Base de Datos

```
denuncias
├── usuario_id → usuarios.id (LEFT JOIN - puede ser NULL si es anónima)
├── categoria_id → categorias.id (INNER JOIN - siempre requerido)
└── area_asignada_id → areas_municipales.id (LEFT JOIN - puede ser NULL si no está asignada)
```

### Valores NULL Permitidos

| Campo | Puede ser NULL | Tipo de JOIN |
|-------|----------------|--------------|
| `usuario_id` | ✅ Sí (denuncias anónimas) | LEFT JOIN |
| `categoria_id` | ❌ No (siempre requerido) | INNER JOIN |
| `area_asignada_id` | ✅ Sí (hasta ser asignada) | LEFT JOIN |

---

## ✅ Checklist de Verificación

- [x] Campo `fecha_registro` mapeado a `created_at`
- [x] JOIN con tabla `categorias` para obtener nombre
- [x] JOIN con tabla `areas_municipales` para obtener nombre de área
- [x] LEFT JOIN en `area_asignada_id` (permite NULL)
- [x] LEFT JOIN en `usuario_id` (permite denuncias anónimas)
- [x] INNER JOIN en `categoria_id` (siempre obligatorio)
- [x] Consulta específica para ciudadano (solo sus denuncias)
- [x] Consulta específica para admin (todas las denuncias)
- [x] Consulta específica para staff (filtro por estados)
- [x] Endpoint `read.php` actualizado con nuevos campos
- [x] Script de prueba `test_consultas.php` creado
- [x] Documentación completa generada

---

## 🎉 Conclusión

**El problema estaba en:**
1. Campo inexistente (`fecha_registro`)
2. Falta de JOINs con tablas relacionadas
3. Uso incorrecto de INNER JOIN (debía ser LEFT)

**La solución:**
1. ✅ Mapear `created_at` como `fecha_registro`
2. ✅ Agregar JOIN con `categorias` y `areas_municipales`
3. ✅ Usar LEFT JOIN para relaciones opcionales
4. ✅ Crear consultas específicas por rol

**Resultado:**
🎯 Ahora las denuncias aparecerán en todos los dashboards con información completa.

---

**Última actualización:** 2025-12-18
**Versión:** 1.0.0
**Arquitecto:** Claude Sonnet 4.5
