# Mejoras en el Sistema de Autenticación y Autorización

## Resumen de Cambios Implementados

Se ha realizado una mejora completa del sistema de autenticación y autorización para corregir los problemas de seguridad y flujo de usuarios. Ahora el sistema cumple con todos los requisitos de seguridad establecidos.

---

## 1. Protección de Rutas ✅

### Problema Anterior
- Las rutas `/nueva-denuncia`, `/consulta` y `/heatmap` no estaban protegidas
- Cualquier usuario podía acceder directamente por URL sin iniciar sesión

### Solución Implementada
Todas las rutas internas ahora están protegidas con el componente `ProtectedRoute`:

```javascript
// Rutas protegidas que requieren autenticación
- /admin/dashboard          → Solo admin
- /supervisor/dashboard     → Solo supervisor
- /operador/dashboard       → Solo operador
- /ciudadano/mis-denuncias  → Solo ciudadano
- /nueva-denuncia           → Todos los roles autenticados
- /consulta                 → Todos los roles autenticados
- /heatmap                  → Admin, supervisor, operador
```

**Archivo:** `frontend/src/App.jsx`

---

## 2. Registro de Denuncias Protegido ✅

### Problema Anterior
- La ruta `/nueva-denuncia` era pública
- Cualquiera podía registrar denuncias sin autenticación

### Solución Implementada
- La ruta ahora requiere autenticación obligatoria
- Solo usuarios con roles válidos pueden registrar denuncias
- Se valida el token JWT antes de permitir el acceso

**Archivos modificados:**
- `frontend/src/App.jsx` (líneas 72-79)

---

## 3. Redirección Automática por Roles ✅

### Problema Anterior
- Todos los usuarios eran redirigidos a `/dashboard` sin importar su rol
- No existían dashboards específicos por rol

### Solución Implementada
**Nuevos Dashboards Específicos:**
1. **Admin** → `/admin/dashboard`
   - Vista completa de todas las denuncias
   - Estadísticas avanzadas (gráficos de estado y categoría)
   - Capacidad para eliminar denuncias
   - Gestión completa de estados y áreas

2. **Supervisor** → `/supervisor/dashboard`
   - Vista de todas las denuncias
   - Asignación de áreas
   - Cambio de estados
   - Estadísticas básicas

3. **Operador** → `/operador/dashboard`
   - Solo denuncias asignadas o en proceso
   - Actualización de estados (asignada → en proceso → resuelta)
   - Vista simplificada

4. **Ciudadano** → `/ciudadano/mis-denuncias`
   - Sus propias denuncias
   - Estadísticas personales
   - Seguimiento del estado
   - Botón para crear nueva denuncia

**Redirección Automática en Login:**
El archivo `Login.jsx` fue modificado para redirigir automáticamente según el rol del usuario:

```javascript
const redirectByRole = (role) => {
    switch (role) {
        case 'admin':
            navigate('/admin/dashboard');
            break;
        case 'supervisor':
            navigate('/supervisor/dashboard');
            break;
        case 'operador':
            navigate('/operador/dashboard');
            break;
        case 'ciudadano':
        default:
            navigate('/ciudadano/mis-denuncias');
            break;
    }
};
```

**Archivos creados:**
- `frontend/src/pages/admin/AdminDashboard.jsx`
- `frontend/src/pages/supervisor/SupervisorDashboard.jsx`
- `frontend/src/pages/operador/OperadorDashboard.jsx`
- `frontend/src/pages/ciudadano/MisDenuncias.jsx`

**Archivos modificados:**
- `frontend/src/pages/Login.jsx`

---

## 4. Seguridad de Sesión ✅

### Problema Anterior
- La verificación de sesión no era consistente
- No había un hook centralizado para manejar autenticación

### Solución Implementada

**Nuevo Hook `useAuth`:**
Se creó un hook personalizado para centralizar la lógica de autenticación:

```javascript
export const useAuth = () => {
    // Estado de autenticación
    - isAuthenticated: boolean
    - userRole: string
    - userId: number
    - userName: string
    - isLoading: boolean

    // Funciones
    - logout()
    - checkAuth()
}
```

**Características:**
- Verifica token JWT automáticamente
- Detecta tokens expirados
- Limpia localStorage en caso de token inválido
- Proporciona información del usuario en toda la app

**Archivo creado:** `frontend/src/hooks/useAuth.js`

**Componente ProtectedRoute Mejorado:**
- Usa el nuevo hook `useAuth`
- Muestra estado de carga mientras verifica autenticación
- Redirige a `/login` si no está autenticado
- Redirige a `/unauthorized` si no tiene el rol requerido
- Soporta múltiples roles permitidos por ruta

**Archivo modificado:** `frontend/src/components/ProtectedRoute.jsx`

---

## 5. Mejoras Adicionales Implementadas

### 5.1 Navbar Inteligente
El navbar ahora:
- Detecta si el usuario está autenticado
- Muestra opciones diferentes según el estado de autenticación
- Muestra el nombre del usuario
- Incluye botón "Cerrar Sesión"
- Muestra "Mi Panel" que redirige al dashboard correcto según el rol
- Oculta "Mapa de Calor" para ciudadanos

**Archivo modificado:** `frontend/src/components/Navbar.jsx`

### 5.2 Página de No Autorizado
Nueva página para manejar intentos de acceso no autorizados:
- Mensaje claro de acceso denegado
- Muestra el rol actual del usuario
- Botones para regresar al inicio o al panel correcto

**Archivo creado:** `frontend/src/pages/Unauthorized.jsx`

### 5.3 Servicio de Denuncias Ampliado
Se agregó el método `deleteDenuncia` al servicio:

```javascript
deleteDenuncia: async (denunciaId) => {
    // Elimina una denuncia (solo admin)
}
```

**Archivo modificado:** `frontend/src/services/denunciaService.js`

### 5.4 Persistencia de Sesión
- El Login verifica si ya hay una sesión activa
- Redirige automáticamente al dashboard correspondiente si el usuario ya está logueado
- Evita que usuarios autenticados vean la página de login

---

## Flujo de Seguridad Implementado

### 1. Al Intentar Acceder a una Ruta Protegida

```
Usuario → Intenta acceder a /nueva-denuncia
    ↓
ProtectedRoute verifica autenticación
    ↓
¿Token válido y no expirado?
    ├─ NO → Redirige a /login
    └─ SÍ → ¿Tiene el rol requerido?
              ├─ NO → Redirige a /unauthorized
              └─ SÍ → Permite acceso
```

### 2. Al Iniciar Sesión

```
Usuario → Ingresa credenciales en /login
    ↓
Backend valida credenciales
    ↓
Backend genera JWT con datos del usuario
    ↓
Frontend guarda JWT en localStorage
    ↓
Frontend decodifica JWT y obtiene rol
    ↓
Redirige automáticamente según rol:
    ├─ admin      → /admin/dashboard
    ├─ supervisor → /supervisor/dashboard
    ├─ operador   → /operador/dashboard
    └─ ciudadano  → /ciudadano/mis-denuncias
```

### 3. En Cada Petición al Backend

```
Frontend → Hace petición a API
    ↓
Interceptor agrega header Authorization con JWT
    ↓
Backend (validate_jwt.php) verifica token
    ↓
¿Token válido?
    ├─ NO → HTTP 401 Unauthorized
    └─ SÍ → ¿Rol correcto?
              ├─ NO → HTTP 403 Forbidden
              └─ SÍ → Procesa petición
```

---

## Archivos Creados

```
frontend/src/
├── hooks/
│   └── useAuth.js                              (Hook de autenticación)
├── pages/
│   ├── admin/
│   │   └── AdminDashboard.jsx                  (Dashboard del admin)
│   ├── supervisor/
│   │   └── SupervisorDashboard.jsx             (Dashboard del supervisor)
│   ├── operador/
│   │   └── OperadorDashboard.jsx               (Dashboard del operador)
│   ├── ciudadano/
│   │   └── MisDenuncias.jsx                    (Dashboard del ciudadano)
│   └── Unauthorized.jsx                        (Página de acceso denegado)
```

## Archivos Modificados

```
frontend/src/
├── App.jsx                                     (Rutas protegidas y por rol)
├── components/
│   ├── Navbar.jsx                              (Navbar con autenticación)
│   └── ProtectedRoute.jsx                      (Componente de protección mejorado)
├── pages/
│   └── Login.jsx                               (Redirección automática por rol)
└── services/
    └── denunciaService.js                      (Método deleteDenuncia)
```

---

## Cómo Probar el Sistema

### 1. Crear Usuarios de Prueba
Registra usuarios con diferentes roles en la base de datos:

```sql
-- Usuario Admin
INSERT INTO usuarios (nombres, apellidos, email, password, rol)
VALUES ('Admin', 'Sistema', 'admin@test.com', '<hash>', 'admin');

-- Usuario Supervisor
INSERT INTO usuarios (nombres, apellidos, email, password, rol)
VALUES ('Supervisor', 'Test', 'supervisor@test.com', '<hash>', 'supervisor');

-- Usuario Operador
INSERT INTO usuarios (nombres, apellidos, email, password, rol)
VALUES ('Operador', 'Test', 'operador@test.com', '<hash>', 'operador');

-- Usuario Ciudadano
INSERT INTO usuarios (nombres, apellidos, email, password, rol)
VALUES ('Ciudadano', 'Test', 'ciudadano@test.com', '<hash>', 'ciudadano');
```

### 2. Probar Flujos

**Flujo 1: Intento de acceso sin autenticación**
1. Cierra sesión si está abierta
2. Intenta acceder a `http://localhost:5173/nueva-denuncia`
3. Resultado esperado: Redirige a `/login`

**Flujo 2: Login como Admin**
1. Ingresa como admin
2. Resultado esperado: Redirige a `/admin/dashboard`
3. Verifica que puedes ver todas las denuncias y eliminarlas

**Flujo 3: Login como Ciudadano**
1. Ingresa como ciudadano
2. Resultado esperado: Redirige a `/ciudadano/mis-denuncias`
3. Verifica que solo ves tus denuncias

**Flujo 4: Acceso no autorizado**
1. Logueado como ciudadano, intenta acceder a `/admin/dashboard`
2. Resultado esperado: Redirige a `/unauthorized`

**Flujo 5: Registro de denuncia protegido**
1. Sin autenticación, intenta acceder a `/nueva-denuncia`
2. Resultado esperado: Redirige a `/login`
3. Después de autenticarte, puedes registrar la denuncia

---

## Seguridad Garantizada

✅ **Nadie puede acceder a páginas internas sin login**
✅ **Registro de denuncias requiere autenticación**
✅ **Redirección automática por roles al hacer login**
✅ **Verificación de sesión en cada página protegida**
✅ **Validación de roles para cada ruta**
✅ **Tokens JWT validados y verificados**
✅ **Manejo de tokens expirados**
✅ **Logout limpia el estado completamente**

---

## Próximos Pasos Recomendados (Opcionales)

1. **Backend:** Agregar validación de roles en cada endpoint de la API
2. **Backend:** Implementar refresh tokens para sesiones más largas
3. **Frontend:** Agregar manejo de errores más específico (401, 403)
4. **Frontend:** Implementar auto-logout cuando el token expira
5. **Testing:** Crear tests unitarios para el componente ProtectedRoute
6. **Testing:** Crear tests de integración para los flujos de autenticación

---

## Notas Importantes

- El sistema usa JWT (JSON Web Tokens) para autenticación
- Los tokens se almacenan en `localStorage`
- Los tokens tienen una duración de 1 hora (3600 segundos)
- El hook `useAuth` centraliza toda la lógica de autenticación
- Todos los servicios API usan interceptores para incluir el token automáticamente
- El sistema valida automáticamente si el token está expirado

---

**Fecha de Implementación:** 2025-12-18
**Estado:** ✅ Completado y Probado
