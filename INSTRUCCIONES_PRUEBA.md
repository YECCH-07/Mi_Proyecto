# 🚀 INSTRUCCIONES PARA PROBAR LA SOLUCIÓN

## ✅ PROBLEMA SOLUCIONADO

**Error corregido:** "No se pudo cargar la denuncia"

**Causa:** El endpoint intentaba consultar una columna `nombre_original` que no existía en la tabla `evidencias`.

**Solución aplicada:** Se eliminó la referencia a la columna inexistente del archivo `backend/api/denuncias/detalle_operador.php`.

---

## 📋 PASOS PARA VERIFICAR LA SOLUCIÓN

### 1️⃣ Iniciar el Servidor Frontend

Abre una terminal y ejecuta:

```bash
cd "C:\xampp\htdocs\DENUNCIA CIUDADANA\frontend"
npm run dev
```

Deberías ver algo como:

```
  VITE v5.x.x  ready in xxx ms

  ➜  Local:   http://localhost:5173/
  ➜  Network: use --host to expose
```

---

### 2️⃣ Abrir el Navegador

Abre tu navegador y ve a:

```
http://localhost:5173
```

---

### 3️⃣ Iniciar Sesión como Operador

**Credenciales disponibles:**

- **Email:** `elena.op@muni.gob.pe`
- **Email:** `yeison@gmail.com`
- **Email:** `admin@muni.gob.pe` (si eres admin)

**Password:** La contraseña que configuraste para ese usuario

---

### 4️⃣ Navegar al Dashboard de Operador

Después de iniciar sesión, deberías estar en el Panel de Operador con una tabla de denuncias.

---

### 5️⃣ Hacer Clic en "Ver Detalle"

Busca el botón **"👁️ Ver Detalle"** en cualquier fila de la tabla y haz clic.

---

### 6️⃣ Verificar que la Vista de Detalle Carga Correctamente

**✅ Si ves la información completa de la denuncia, el problema está RESUELTO**

Deberías ver:
- Título y código de la denuncia
- Descripción completa
- Información del ciudadano
- Ubicación con botón de Google Maps
- Evidencias (si las hay)
- Historial de seguimiento
- Formulario para actualizar estado

**❌ Si todavía ves "No se pudo cargar la denuncia", revisa la sección de debugging abajo**

---

## 🔍 DEBUGGING (Si Todavía Hay Error)

### Paso 1: Abrir Consola del Navegador

Presiona **F12** y ve a la pestaña "Console"

### Paso 2: Buscar el Error Específico

Busca mensajes en rojo que empiecen con:
```
Error fetching denuncia:
```

### Paso 3: Ir a Pestaña "Network"

1. Ve a la pestaña "Network" en DevTools
2. Haz clic nuevamente en "Ver Detalle"
3. Busca la petición `detalle_operador.php`
4. Haz clic en ella
5. Ve a la pestaña "Response"
6. Copia el contenido completo de la respuesta y compártelo

---

## ✅ RESULTADO ESPERADO

Después de la corrección, el endpoint debería retornar:

```json
{
  "success": true,
  "data": {
    "denuncia": { ... },
    "ciudadano": { ... },
    "evidencias": [ ... ],
    "seguimiento": [ ... ]
  }
}
```

---

**Fecha de solución:** 20/12/2025
**Archivo corregido:** `backend/api/denuncias/detalle_operador.php`
**Líneas modificadas:** 112, 131
