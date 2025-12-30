# ⚡ Guía Rápida de Pruebas

## 🎯 Pasos para Verificar que Todo Funciona

### ✅ Paso 1: Probar las Consultas SQL (2 minutos)

Abre en tu navegador:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_consultas.php
```

**Resultado esperado:**
```
✅ Conexión a base de datos: OK
✅ Consulta ejecutada correctamente
📊 Total de denuncias: X
```

Si ves esto, las consultas SQL están funcionando correctamente.

---

### ✅ Paso 2: Probar el Dashboard de Ciudadano (2 minutos)

1. **Login como ciudadano:**
   - Email: (tu usuario ciudadano)
   - Password: (tu contraseña)

2. **Ir a "Mis Denuncias"**

3. **Verificar que aparecen:**
   - ✅ Código de denuncia (DU-2025-000001)
   - ✅ Título
   - ✅ **Nombre de categoría** (ej: "Alumbrado Público")
   - ✅ Estado
   - ✅ Fecha
   - ✅ **Nombre de área** (ej: "Obras Públicas" o "No asignada")

**Antes veías:**
```
❌ Array vacío
❌ Sin datos
❌ Solo IDs (categoria_id: 3)
```

**Ahora debes ver:**
```
✅ Tabla con denuncias
✅ Nombres completos (categoria_nombre: "Servicios Básicos")
✅ Área asignada (area_nombre: "No asignada" si es NULL)
```

---

### ✅ Paso 3: Probar el Dashboard de Admin (2 minutos)

1. **Login como admin:**
   - Email: admin@municipio.gob.pe (o tu admin)
   - Password: (tu contraseña admin)

2. **Ir al Dashboard de Admin**

3. **Verificar que aparecen:**
   - ✅ TODAS las denuncias del sistema
   - ✅ Nombre del ciudadano que reportó
   - ✅ Email y teléfono del ciudadano
   - ✅ Nombre de categoría
   - ✅ Nombre de área (o "No asignada")
   - ✅ Responsable del área

**Importante:**
- Debe mostrar denuncias AUNQUE `area_asignada_id` sea NULL
- Debe mostrar denuncias de usuarios anónimos (usuario_nombre: "Anónimo")

---

### ✅ Paso 4: Probar el Dashboard de Supervisor/Operador (2 minutos)

1. **Login como supervisor u operador**

2. **Ir al Dashboard**

3. **Verificar:**
   - ✅ Solo muestra denuncias en estados: registrada, en_revision, asignada, en_proceso
   - ✅ **Muestra denuncias con estado 'registrada' AUNQUE area_asignada_id sea NULL**
   - ✅ Muestra nombre de categoría
   - ✅ Muestra datos de contacto del ciudadano

**Esto era crítico:**
- Antes: Denuncias con `area_asignada_id = NULL` NO aparecían (INNER JOIN las ocultaba)
- Ahora: Sí aparecen gracias a LEFT JOIN

---

## 🔍 Cómo Verificar los Datos en la Base de Datos

### Opción 1: phpMyAdmin

1. Ir a http://localhost/phpmyadmin
2. Seleccionar base de datos `denuncia_ciudadana`
3. Click en tabla `denuncias`
4. Ejecutar esta consulta:

```sql
SELECT
    d.id,
    d.codigo,
    d.estado,
    d.area_asignada_id,
    c.nombre as categoria_nombre,
    a.nombre as area_nombre
FROM denuncias d
LEFT JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
ORDER BY d.created_at DESC
LIMIT 10;
```

**Debes ver:**
- Columna `categoria_nombre` con texto (no NULL)
- Columna `area_nombre` puede ser NULL (es normal si no está asignada)

---

### Opción 2: Desde el código PHP

Crear archivo `backend/check.php`:

```php
<?php
include_once 'config/database.php';
include_once 'models/Denuncia.php';

$database = new Database();
$db = $database->getConnection();
$denuncia = new Denuncia($db);

// Probar consulta de admin
$stmt = $denuncia->readForAdmin();
echo "Total denuncias: " . $stmt->rowCount() . "\n\n";

// Mostrar primera denuncia
$row = $stmt->fetch(PDO::FETCH_ASSOC);
print_r($row);
```

Ejecutar: `http://localhost/DENUNCIA%20CIUDADANA/backend/check.php`

---

## 🐛 Troubleshooting

### Problema 1: Sigo viendo arrays vacíos

**Posibles causas:**
1. No hay denuncias en la base de datos
2. El usuario no tiene denuncias asignadas
3. Error de autenticación (JWT no válido)

**Solución:**
```bash
# Verificar que hay denuncias
http://localhost/DENUNCIA%20CIUDADANA/backend/test_consultas.php

# Si dice "Total: 0", necesitas crear denuncias de prueba
```

---

### Problema 2: Aparece "categoria_nombre: null"

**Causa:** No hay categorías en la tabla `categorias`

**Solución:**
```sql
-- Ejecutar en phpMyAdmin
INSERT INTO categorias (nombre, descripcion, icono) VALUES
('Servicios Básicos', 'Agua, luz, desagüe', '💧'),
('Alumbrado Público', 'Postes y luminarias', '💡'),
('Infraestructura', 'Pistas, veredas', '🏗️'),
('Seguridad', 'Robos, delincuencia', '🚨'),
('Limpieza Pública', 'Basura, residuos', '🗑️');
```

---

### Problema 3: Error "Call to undefined method readForAdmin()"

**Causa:** El archivo `Denuncia.php` no se actualizó correctamente

**Solución:**
1. Verificar que `backend/models/Denuncia.php` tiene los métodos:
   - `readForAdmin()`
   - `readForCiudadano()`
   - `readForStaff()`

2. Si no los tiene, copiar nuevamente el archivo corregido

---

### Problema 4: Error SQL "Unknown column 'fecha_registro'"

**Causa:** Todavía está usando el campo antiguo

**Solución:**
- Verificar línea 129, 173, 216 de `Denuncia.php`
- Debe decir: `d.created_at as fecha_registro`
- NO debe decir: `d.fecha_registro`

---

## 📊 Campos que Ahora Devuelve el API

### Respuesta JSON de `/api/denuncias/read.php`

```json
{
  "records": [
    {
      "id": 1,
      "codigo": "DU-2025-000001",
      "titulo": "Fuga de agua en calle principal",
      "descripcion": "...",
      "estado": "registrada",
      "fecha_registro": "2025-12-18 10:30:00",
      "latitud": -12.0464,
      "longitud": -77.0428,
      "direccion_referencia": "Av. Principal 123",
      "usuario_id": 5,
      "categoria_id": 1,
      "area_asignada_id": null,
      "es_anonima": false,

      // ✅ NUEVOS CAMPOS (antes no existían):
      "usuario_nombre": "Juan Pérez López",
      "usuario_email": "juan@email.com",
      "usuario_telefono": "987654321",
      "categoria_nombre": "Servicios Básicos",
      "categoria_icono": "💧",
      "area_nombre": "No asignada",
      "area_responsable": null
    }
  ]
}
```

### Actualizar Frontend para Usar Nuevos Campos

**Antes (mostraba solo IDs):**
```javascript
<td>{denuncia.categoria_id}</td>  // Mostraba: "1"
<td>{denuncia.area_asignada_id}</td>  // Mostraba: "null"
```

**Ahora (mostrar nombres):**
```javascript
<td>
  {denuncia.categoria_icono && <span>{denuncia.categoria_icono}</span>}
  {denuncia.categoria_nombre}
</td>  // Muestra: "💧 Servicios Básicos"

<td>{denuncia.area_nombre || 'No asignada'}</td>  // Muestra: "No asignada"
```

---

## ✅ Checklist Final

Marca cada uno cuando funcione:

### Backend
- [ ] Script `test_consultas.php` ejecuta sin errores
- [ ] Consulta de admin devuelve denuncias
- [ ] Consulta de ciudadano devuelve denuncias filtradas
- [ ] Consulta de staff devuelve denuncias con estados específicos
- [ ] LEFT JOIN funciona (denuncias con `area_asignada_id = NULL` aparecen)

### Frontend
- [ ] Dashboard de ciudadano muestra tabla con denuncias
- [ ] Se ve el nombre de categoría (no solo ID)
- [ ] Se ve "No asignada" cuando área es NULL
- [ ] Dashboard de admin muestra todas las denuncias
- [ ] Dashboard de supervisor/operador muestra denuncias filtradas

### Datos
- [ ] Hay denuncias en la tabla `denuncias`
- [ ] Hay categorías en la tabla `categorias`
- [ ] Hay usuarios con diferentes roles
- [ ] Al menos una denuncia tiene `area_asignada_id = NULL`

---

## 🎯 Resumen Ejecutivo

### ¿Qué se corrigió?
1. ✅ Campo inexistente `fecha_registro` → Mapeado a `created_at`
2. ✅ Falta de JOINs → Agregados con `categorias` y `areas_municipales`
3. ✅ INNER JOIN ocultaba NULL → Cambiado a LEFT JOIN

### ¿Por qué ahora funciona?
- Las consultas SQL ahora se ejecutan correctamente
- LEFT JOIN permite que aparezcan denuncias sin área asignada
- Se devuelven nombres legibles en lugar de solo IDs

### ¿Qué hacer si sigue sin funcionar?
1. Ejecutar `test_consultas.php` para diagnóstico
2. Revisar logs de errores PHP en XAMPP
3. Verificar que hay datos en las tablas
4. Comprobar que el JWT está funcionando

---

**¡Con estas pruebas deberías ver tus denuncias en todos los dashboards!** 🎉
