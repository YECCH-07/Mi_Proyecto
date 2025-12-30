# Reestructuración de Navegación y Visualización de Denuncias

## Resumen de Cambios Implementados

Se ha reestructurado completamente la navegación y la lógica de visualización de denuncias para garantizar la privacidad de los ciudadanos y mejorar la experiencia de usuario.

---

## 1. Menú de Navegación Reestructurado ✅

### Para Usuarios NO Logueados (Público)

**Opciones Visibles:**
- 🔍 **Consulta tu Denuncia** → Búsqueda pública por código (sin login)
- 📝 **Registrarse** → Crear nueva cuenta
- 🚪 **Iniciar Sesión** → Acceder al sistema

**Opciones Ocultas:**
- ❌ Dashboards (no accesibles)
- ❌ Nueva Denuncia (requiere login)
- ❌ Mapa de Calor (requiere login y rol administrativo)

**Archivo modificado:** `frontend/src/components/Navbar.jsx`

### Para Usuarios Logueados

**Opciones Visibles:**
- 🏠 **Mi Panel** → Redirige al dashboard según rol
- 🔴 **Cerrar Sesión** → Logout y regreso a inicio
- 👤 **Nombre del usuario** → Muestra información del usuario

**Opciones Ocultas:**
- ❌ Registrarse (ya está logueado)
- ❌ Iniciar Sesión (ya está logueado)

**Comportamiento:**
- Al hacer clic en "Mi Panel", redirige automáticamente:
  - Admin → `/admin/dashboard`
  - Supervisor → `/supervisor/dashboard`
  - Operador → `/operador/dashboard`
  - Ciudadano → `/ciudadano/mis-denuncias`

---

## 2. Lógica de Visualización de Denuncias ✅

### Rol Ciudadano: Solo Sus Denuncias

**Filtrado Estricto en Backend:**
```php
// backend/api/denuncias/read.php (líneas 64-66)
if ($user_data->rol === 'ciudadano') {
    $stmt = $denuncia->readByUsuario($user_data->id);
}
```

**Consulta SQL con Filtro:**
```sql
SELECT d.* FROM denuncias d
WHERE d.usuario_id = :usuario_id
ORDER BY d.fecha_registro DESC
```

**Características:**
- ✅ Solo ve denuncias con `usuario_id` igual a su ID
- ✅ No puede acceder a denuncias de otros ciudadanos
- ✅ Validación adicional al consultar denuncia individual
- ✅ Error 403 si intenta ver denuncias ajenas

**Archivos modificados:**
- `backend/api/denuncias/read.php` (Validación y filtrado)
- `backend/models/Denuncia.php` (Nuevo método `readByUsuario`)
- `frontend/src/pages/ciudadano/MisDenuncias.jsx` (Interfaz)

### Roles Administrativos: Todas las Denuncias

**Sin Filtrado en Backend:**
```php
// backend/api/denuncias/read.php (líneas 67-69)
else {
    // Admin/Supervisor/Operador: All denuncias
    $stmt = $denuncia->read();
}
```

**Consulta SQL General:**
```sql
SELECT d.* FROM denuncias d
LEFT JOIN usuarios u ON d.usuario_id = u.id
ORDER BY d.fecha_registro DESC
```

**Características:**
- ✅ Admin: Ve todas las denuncias, puede eliminarlas
- ✅ Supervisor: Ve todas las denuncias, asigna áreas
- ✅ Operador: Filtra en frontend solo asignadas/en proceso
- ✅ Ordenadas cronológicamente (más recientes primero)

**Archivos modificados:**
- `frontend/src/pages/admin/AdminDashboard.jsx`
- `frontend/src/pages/supervisor/SupervisorDashboard.jsx`
- `frontend/src/pages/operador/OperadorDashboard.jsx`

---

## 3. Organización de Vistas ✅

### Landing Page Pública (Inicio)

**Nueva Página Home Profesional:**
- 🎨 Diseño limpio y atractivo
- 📋 Información sobre el sistema
- 🔍 Botón destacado: "Consultar mi Denuncia"
- 📝 Botón secundario: "Registrar Nueva Denuncia"
- ℹ️ Secciones informativas:
  - Características principales
  - Cómo funciona (4 pasos)
  - Tipos de denuncias reportables
  - CTA final para consulta por código

**Características:**
- ✅ **Totalmente pública** (sin llamadas a API)
- ✅ **Sin requerir autenticación**
- ✅ **Informativa y educativa**
- ✅ **CTAs claros y visibles**

**Archivo modificado:** `frontend/src/pages/Home.jsx`

### Dashboard Interno (Protegido)

**Características:**
- ✅ Requiere autenticación obligatoria
- ✅ Separado completamente de la landing page
- ✅ Específico por rol (4 dashboards diferentes)
- ✅ Información privada y personalizada

**Dashboards por Rol:**

**1. Admin Dashboard** (`/admin/dashboard`)
- Título: "Últimas Denuncias (Todas)"
- Subtítulo: "Denuncias de todos los ciudadanos ordenadas cronológicamente"
- Gráficos de estado y categoría
- Capacidad de eliminar denuncias
- Estadísticas completas

**2. Supervisor Dashboard** (`/supervisor/dashboard`)
- Título: "Últimas Denuncias (Todas)"
- Subtítulo: "Denuncias de todos los ciudadanos para asignar y supervisar"
- Asignación de áreas
- Cambio de estados
- Gráfico de estado

**3. Operador Dashboard** (`/operador/dashboard`)
- Título: "Denuncias Asignadas a Mí"
- Subtítulo: "Solo denuncias en proceso o asignadas que debo atender"
- Filtrado local: solo estados "asignada" y "en_proceso"
- Actualización de estados

**4. Ciudadano** (`/ciudadano/mis-denuncias`)
- Título: "Mis Denuncias"
- Solo sus propias denuncias (filtrado en backend)
- Estadísticas personales
- Solo lectura (no puede editar)
- Botón "Nueva Denuncia"

### Consulta por Código (Pública)

**Características:**
- ✅ **Acceso público SIN login** requerido
- ✅ Formulario simple con campo de código
- ✅ Cualquier persona puede consultar con código válido
- ✅ Útil para ciudadanos que perdieron acceso a su cuenta

**Archivo de ruta:** `frontend/src/App.jsx` (línea 72)
```jsx
{/* Public route - Consulta por código (sin login) */}
<Route path="/consulta" element={<ConsultaPage />} />
```

---

## Comparación: Antes vs Ahora

### Navbar (NO Logueado)

| Antes | Ahora |
|-------|-------|
| Inicio, Consulta, Heatmap, Nueva Denuncia, Login | Consulta tu Denuncia, Registrarse, Iniciar Sesión |

### Navbar (Logueado)

| Antes | Ahora |
|-------|-------|
| Inicio, Mi Panel, Nueva Denuncia, Consulta, Heatmap (según rol), Usuario, Cerrar Sesión | Mi Panel, Usuario, Cerrar Sesión |

### Landing Page

| Antes | Ahora |
|-------|-------|
| Intenta cargar denuncias (requiere auth) → Error | Página informativa pública sin llamadas API |

### Visualización de Denuncias (Ciudadano)

| Antes | Ahora |
|-------|-------|
| Ve todas las denuncias de todos | Solo ve SUS denuncias (filtrado SQL) |

### Visualización de Denuncias (Admin)

| Antes | Ahora |
|-------|-------|
| Ve todas las denuncias | Ve todas las denuncias con título claro "Todas" |

---

## Flujo de Privacidad Implementado

### Ciudadano Intenta Ver Denuncias

```
1. Ciudadano → GET /api/denuncias/read.php
   ↓
2. Backend valida JWT y obtiene rol = 'ciudadano'
   ↓
3. Backend ejecuta: readByUsuario(user_id)
   ↓
4. SQL: WHERE usuario_id = :usuario_id
   ↓
5. Retorna SOLO denuncias del ciudadano
```

### Admin Intenta Ver Denuncias

```
1. Admin → GET /api/denuncias/read.php
   ↓
2. Backend valida JWT y obtiene rol = 'admin'
   ↓
3. Backend ejecuta: read() sin filtros
   ↓
4. SQL: Sin WHERE (todas las denuncias)
   ↓
5. Retorna TODAS las denuncias
```

### Ciudadano Intenta Ver Denuncia Ajena

```
1. Ciudadano → GET /api/denuncias/read.php?id=123
   ↓
2. Backend valida JWT y obtiene rol = 'ciudadano'
   ↓
3. Backend ejecuta: readOne(id=123)
   ↓
4. Verifica: denuncia.usuario_id != user.id
   ↓
5. HTTP 403 Forbidden
   ↓
6. Error: "Access denied. You can only view your own denuncias."
```

---

## Archivos Modificados

### Frontend (4 archivos)

```
frontend/src/
├── App.jsx                                    (Ruta pública /consulta)
├── components/
│   └── Navbar.jsx                             (Navegación según auth)
└── pages/
    ├── Home.jsx                                (Landing page pública)
    ├── admin/AdminDashboard.jsx               (Título "Todas")
    ├── supervisor/SupervisorDashboard.jsx     (Título "Todas")
    ├── operador/OperadorDashboard.jsx         (Título "Asignadas")
    └── ciudadano/MisDenuncias.jsx             (Solo del usuario)
```

### Backend (2 archivos)

```
backend/
├── api/denuncias/read.php                     (Filtrado por rol)
└── models/Denuncia.php                        (Método readByUsuario)
```

---

## Seguridad y Privacidad Garantizadas

✅ **Navbar limpio para usuarios NO logueados** (solo 3 opciones)
✅ **Consulta por código es pública** (sin login requerido)
✅ **Ciudadanos solo ven SUS denuncias** (filtrado SQL estricto)
✅ **Admins/Supervisors/Operadores ven TODAS** (para gestión)
✅ **Landing page completamente pública** (sin API calls)
✅ **Dashboards separados de página pública** (protegidos)
✅ **Validación adicional en lectura individual** (previene acceso no autorizado)
✅ **Títulos claros en tablas** ("Todas" vs "Mis Denuncias")

---

## Cómo Probar

### Prueba 1: Navbar Público
```bash
1. Abre el navegador en modo incógnito
2. Ve a http://localhost:5173
3. ✅ Debes ver solo: "Consulta tu Denuncia", "Registrarse", "Iniciar Sesión"
```

### Prueba 2: Navbar Autenticado
```bash
1. Inicia sesión como cualquier rol
2. ✅ Debes ver solo: "Mi Panel", nombre usuario, "Cerrar Sesión"
3. ✅ NO debes ver "Registrarse" ni "Iniciar Sesión"
```

### Prueba 3: Privacidad de Ciudadano
```bash
1. Registra 2 usuarios ciudadanos (A y B)
2. Usuario A crea 3 denuncias
3. Usuario B crea 2 denuncias
4. Login como Usuario A
5. ✅ Solo debe ver sus 3 denuncias
6. Login como Usuario B
7. ✅ Solo debe ver sus 2 denuncias
```

### Prueba 4: Vista de Admin
```bash
1. Login como admin
2. Ve a /admin/dashboard
3. ✅ Debe ver TODAS las denuncias (5 en total del ejemplo)
4. ✅ Título debe decir "Últimas Denuncias (Todas)"
```

### Prueba 5: Consulta Pública
```bash
1. Cierra sesión (o modo incógnito)
2. Ve a http://localhost:5173/consulta
3. ✅ Debe cargar SIN redirigir a login
4. ✅ Debe mostrar formulario de búsqueda
```

### Prueba 6: Landing Page Pública
```bash
1. Cierra sesión (o modo incógnito)
2. Ve a http://localhost:5173
3. ✅ Debe cargar página informativa sin errores
4. ✅ NO debe intentar cargar denuncias
5. ✅ Debe mostrar botones "Consultar" y "Registrar"
```

### Prueba 7: Intento de Acceso No Autorizado (API)
```bash
# Como ciudadano, intenta acceder a denuncia ajena
1. Login como ciudadano A
2. Obtén el ID de una denuncia del ciudadano B (ej: id=10)
3. Intenta: GET /api/denuncias/read.php?id=10
4. ✅ Debe retornar HTTP 403 Forbidden
5. ✅ Mensaje: "Access denied. You can only view your own denuncias."
```

---

## Beneficios de la Reestructuración

### 1. Privacidad Mejorada
- Los ciudadanos no pueden espiar denuncias de otros
- Filtrado a nivel de base de datos (más seguro)
- Validación adicional en lecturas individuales

### 2. UX Mejorada
- Navbar simplificado y claro
- Landing page profesional e informativa
- Títulos descriptivos en dashboards

### 3. Separación de Responsabilidades
- Público vs Privado claramente definido
- Landing page sin dependencias de API
- Consulta por código accesible para todos

### 4. Seguridad
- Autenticación requerida para datos sensibles
- Autorización por rol implementada
- Validación en backend (no solo frontend)

---

**Fecha de Implementación:** 2025-12-18
**Estado:** ✅ Completado y Probado
**Compatibilidad:** Compatible con todas las mejoras de autenticación previas
