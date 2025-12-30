# Guía de Diagnóstico - Problemas de Login y Dashboards

## Cambios Implementados para Diagnóstico

Se han agregado logs detallados en todo el flujo de autenticación para identificar exactamente dónde está fallando el proceso.

---

## 📋 Paso a Paso para Diagnosticar

### Paso 1: Limpia Completamente el Navegador

**IMPORTANTE:** Haz esto ANTES de cualquier prueba:

```bash
1. Abre DevTools (F12)
2. Ve a Application → Storage
3. Clic en "Clear site data"
4. Marca todas las opciones
5. Clic en "Clear site data"
6. Cierra DevTools
7. Cierra el navegador completamente
8. Vuelve a abrir el navegador
```

**Alternativa:**
- Usa modo incógnito: `Ctrl + Shift + N`

---

### Paso 2: Abre la Consola del Navegador

1. Presiona `F12`
2. Ve a la pestaña "Console"
3. Mantén la consola abierta durante TODO el proceso

---

### Paso 3: Intenta Iniciar Sesión

1. Ve a `http://localhost:5173/login`
2. Ingresa credenciales válidas
3. Haz clic en "Iniciar Sesión"
4. **OBSERVA LA CONSOLA**

---

## 🔍 Qué Buscar en la Consola

### Escenario 1: Login Exitoso (Ideal)

```
[Login] Intentando login...
[Login] Token recibido: SÍ
[Login] Token guardado en localStorage: SÍ
[Login] Usuario: Juan Pérez Rol: ciudadano
[Login] Redirigiendo según rol...
[Interceptor] Token encontrado: SÍ
[Interceptor] Header Authorization agregado
[Interceptor Response] Success: /denuncias/read.php
[Interceptor] Token encontrado: SÍ
[Interceptor] Header Authorization agregado
[Interceptor Response] Success: /categorias/read.php
```

**✅ Si ves esto:** Todo funciona correctamente.

---

### Escenario 2: Token NO se Guarda

```
[Login] Intentando login...
[Login] Token recibido: SÍ
[Login] Token guardado en localStorage: NO  ❌
```

**❌ Problema:** localStorage bloqueado o no funcional

**Solución:**
1. Verifica que no estés en modo privado/incógnito con restricciones
2. Verifica configuración del navegador
3. Intenta en otro navegador

---

### Escenario 3: Token NO Llega del Servidor

```
[Login] Intentando login...
[Login] Error: {message: "Login failed. Invalid credentials."}
```

**❌ Problema:** Credenciales incorrectas o error en el backend

**Solución:**
1. Verifica que las credenciales sean correctas
2. Verifica que el usuario exista en la base de datos
3. Revisa los logs de PHP en `xampp/apache/logs/error.log`

---

### Escenario 4: Token NO se Envía en Peticiones

```
[Login] Token guardado en localStorage: SÍ
[Login] Redirigiendo según rol...
[Interceptor] Token encontrado: NO  ❌
[Interceptor Response Error] {
  status: 401,
  message: "Access denied. Authorization header not found."
}
```

**❌ Problema:** El interceptor no encuentra el token

**Solución:**
```javascript
// En la consola, ejecuta:
localStorage.getItem('jwt')

// Si devuelve null:
// El token se perdió después de guardarse
// Intenta reiniciar el servidor de desarrollo
```

---

### Escenario 5: Error 401 a Pesar de Enviar Token

```
[Interceptor] Token encontrado: SÍ
[Interceptor] Header Authorization agregado
[Interceptor Response Error] {
  status: 401,
  message: "Access denied. Invalid token."
}
```

**❌ Problema:** Token inválido o secret_key no coincide

**Solución:**
1. Verifica que `backend/middleware/validate_jwt.php` (línea 23) tenga:
   ```php
   $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
   ```

2. Verifica que `backend/api/auth/login.php` (línea 31) tenga el MISMO secret:
   ```php
   $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
   ```

---

## 🧪 Pruebas Manuales en la Consola

### Prueba 1: Verificar Token

```javascript
// Después de hacer login, ejecuta:
const token = localStorage.getItem('jwt');
console.log('Token:', token);

// Resultado esperado: String largo que empieza con "eyJ..."
```

### Prueba 2: Decodificar Token

```javascript
// Copia el token de arriba y ejecuta:
import { jwtDecode } from 'jwt-decode';

const token = localStorage.getItem('jwt');
const decoded = jwtDecode(token);
console.log('Datos del token:', decoded);

// Debe mostrar: { data: { id, nombres, apellidos, email, rol }, exp, ... }
```

### Prueba 3: Probar Petición Manual

```javascript
// Ejecuta:
const token = localStorage.getItem('jwt');

fetch('http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php', {
    headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
    }
})
.then(r => {
    console.log('Status:', r.status);
    return r.json();
})
.then(data => console.log('Datos:', data))
.catch(err => console.error('Error:', err));

// Si status es 200: ✅ El backend funciona
// Si status es 401: ❌ Problema de autenticación
```

---

## 🔧 Soluciones por Rol

### Para TODOS los Roles

1. **Verifica que el usuario esté en la base de datos:**
   ```sql
   SELECT * FROM usuarios WHERE email = 'tu@email.com';
   ```

2. **Verifica el rol del usuario:**
   ```sql
   SELECT rol FROM usuarios WHERE email = 'tu@email.com';
   -- Debe ser: 'admin', 'supervisor', 'operador', o 'ciudadano'
   ```

### Para Rol: Ciudadano

**Dashboard:** `/ciudadano/mis-denuncias`

**Si aparece error 401:**
1. Verifica en consola: `[Interceptor] Token encontrado:`
2. Si dice "NO": El token no se guardó
3. Si dice "SÍ": Verifica `backend/middleware/validate_jwt.php`

**Si aparece pantalla en blanco:**
1. Verifica en consola si hay errores
2. Verifica que `backend/api/denuncias/read.php` tenga el código corregido
3. Prueba manualmente la petición (Prueba 3 arriba)

### Para Rol: Admin/Supervisor/Operador

**Dashboards:**
- Admin: `/admin/dashboard`
- Supervisor: `/supervisor/dashboard`
- Operador: `/operador/dashboard`

**Mismo diagnóstico que Ciudadano**, pero verifica también:

1. Que el componente del dashboard exista:
   ```bash
   frontend/src/pages/admin/AdminDashboard.jsx
   frontend/src/pages/supervisor/SupervisorDashboard.jsx
   frontend/src/pages/operador/OperadorDashboard.jsx
   ```

2. Que las rutas estén configuradas en `App.jsx`

---

## 📊 Checklist de Verificación

Usa este checklist para verificar cada punto:

### Backend

- [ ] ✅ `backend/middleware/validate_jwt.php` tiene el fallback del secret_key
- [ ] ✅ `backend/api/auth/login.php` tiene el MISMO secret_key
- [ ] ✅ `backend/config/cors.php` permite header Authorization
- [ ] ✅ `backend/api/denuncias/read.php` filtra por rol
- [ ] ✅ `backend/models/Denuncia.php` tiene método `readByUsuario`
- [ ] ✅ XAMPP/Apache está corriendo
- [ ] ✅ Base de datos tiene usuarios

### Frontend

- [ ] ✅ `frontend/src/services/denunciaService.js` tiene interceptores
- [ ] ✅ `frontend/src/pages/Login.jsx` guarda el token
- [ ] ✅ `frontend/src/components/ProtectedRoute.jsx` valida auth
- [ ] ✅ `frontend/src/App.jsx` tiene rutas protegidas
- [ ] ✅ Todos los dashboards existen (admin, supervisor, operador, ciudadano)
- [ ] ✅ Server de desarrollo corriendo: `npm run dev`

### Navegador

- [ ] ✅ localStorage limpio antes de probar
- [ ] ✅ Consola abierta para ver logs
- [ ] ✅ Sin extensiones que bloqueen cookies/storage
- [ ] ✅ JavaScript habilitado

---

## 🚨 Errores Comunes y Soluciones

### Error: "Access denied. Authorization header not found"

**Causa:** Header no llega al servidor

**Soluciones:**
1. Verifica que CORS permita Authorization (ya corregido)
2. Verifica que el interceptor funcione (logs en consola)
3. Verifica que el token exista en localStorage

### Error: "Access denied. Invalid token"

**Causa:** Secret key no coincide

**Soluciones:**
1. Verifica que `login.php` y `validate_jwt.php` usen el MISMO secret
2. Reinicia Apache
3. Prueba con un nuevo login

### Pantalla en Blanco

**Causa:** Error de JavaScript no capturado

**Soluciones:**
1. Abre la consola y busca errores en rojo
2. Verifica que el componente del dashboard exista
3. Verifica las rutas en `App.jsx`

### Redirección Infinita

**Causa:** ProtectedRoute redirige a una ruta que también redirige

**Soluciones:**
1. Verifica que las rutas públicas no estén protegidas
2. Verifica que `/login` NO esté protegido
3. Limpia localStorage y vuelve a intentar

---

## 📝 Comandos Útiles

### Reiniciar Todo

```bash
# Frontend
cd frontend
# Ctrl+C para detener
npm run dev

# Backend (XAMPP)
# Detén y vuelve a iniciar Apache desde el panel de XAMPP

# Limpiar npm cache (si es necesario)
npm cache clean --force
rm -rf node_modules
npm install
```

### Ver Logs en Tiempo Real

```bash
# Logs de Apache (XAMPP)
tail -f C:/xampp/apache/logs/error.log

# Consola del navegador
# F12 → Console → Filter: [Login] o [Interceptor]
```

---

## 📞 Si Nada Funciona

1. **Copia TODO el output de la consola** cuando intentas login
2. **Toma screenshot** del error
3. **Verifica:**
   - ¿Qué rol está intentando entrar?
   - ¿Qué URL está viendo?
   - ¿Qué dice la consola exactamente?

---

**Última Actualización:** 2025-12-18
**Archivos con Logging:** `Login.jsx`, `denunciaService.js`
**Estado:** ✅ Listo para diagnóstico detallado
