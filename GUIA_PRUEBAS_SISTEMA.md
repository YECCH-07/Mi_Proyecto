# 🧪 Guía de Pruebas del Sistema - Paso a Paso

## ✅ PROBLEMAS CORREGIDOS

### 1. ❌ → ✅ Columna `prioridad` faltante
**ANTES:** Error "Unknown column 'prioridad'"
**AHORA:** Columna agregada exitosamente con valores: baja, media, alta, urgente

### 2. ❌ → ✅ Generación de códigos duplicados
**ANTES:** Error "Duplicate entry 'DU-2025-000007'"
**AHORA:** Sistema robusto que:
- Busca el número máximo
- Verifica unicidad antes de usar
- Reintenta hasta 10 veces si hay colisión
- Fallback con timestamp

### 3. ✅ Vista de Detalle con Mapa
**CREADO:** Nueva página `/ciudadano/denuncia/:id` con:
- Mapa interactivo Leaflet
- Información completa de la denuncia
- Diseño responsivo profesional

---

## 🚀 CÓMO PROBAR EL SISTEMA (10 minutos)

### PASO 1: Verificar Backend (2 minutos)

1. Abrir XAMPP Control Panel
2. Verificar que MySQL y Apache estén corriendo (luz verde)
3. Si no están corriendo, hacer clic en "Start"

**Verificar conexión a BD:**
```bash
cd "C:\xampp\htdocs\DENUNCIA CIUDADANA\backend"
php ver_estructura.php
```

**Deberías ver:**
```
ESTRUCTURA TABLA DENUNCIAS:
...
prioridad      enum(...)     YES   ← DEBE APARECER
...
```

---

### PASO 2: Iniciar Servidor Frontend (1 minuto)

1. Abrir una terminal (CMD o PowerShell)
2. Navegar a la carpeta frontend:

```bash
cd "C:\xampp\htdocs\DENUNCIA CIUDADANA\frontend"
```

3. Iniciar el servidor de desarrollo:

```bash
npm run dev
```

**Deberías ver:**
```
VITE v5.x.x  ready in xxx ms

➜  Local:   http://localhost:5173/
➜  Network: use --host to expose
➜  press h to show help
```

4. **DEJAR ESTA TERMINAL ABIERTA** - El servidor debe estar corriendo

---

### PASO 3: Abrir Aplicación en el Navegador (1 minuto)

1. Abrir navegador (Chrome, Edge, Firefox)
2. Ir a: **http://localhost:5173/**
3. Presionar **F12** para abrir DevTools
4. Ir a la pestaña **Console**

---

### PASO 4: Iniciar Sesión (2 minutos)

1. En la página principal, clic en **"Iniciar Sesión"**

2. Usar credenciales de un ciudadano (si no tienes, registrarse primero):
   ```
   Email: ciudadano1@ejemplo.com
   Password: [tu contraseña]
   ```

3. Hacer clic en **"Iniciar Sesión"**

4. **VERIFICAR en Console (F12):**
   ```javascript
   [Interceptor] Token encontrado: SÍ
   [Interceptor] Header Authorization agregado
   [Interceptor Response] Success: /usuarios/login.php
   ```

5. Deberías ser redirigido a **"Mis Denuncias"**

---

### PASO 5: Crear una Nueva Denuncia (3 minutos)

1. Clic en **"Registrar Nueva Denuncia"** (botón rojo grande)

2. Llenar el formulario:
   ```
   Título: Prueba de sistema corregido
   Descripción: Esta es una denuncia de prueba para verificar que el sistema funciona
   Categoría: [Seleccionar cualquiera]
   Latitud: -12.0464
   Longitud: -77.0428
   Dirección: Av. Principal 123, Lima
   ```

3. **DEJAR Console abierta** para ver requests

4. Hacer clic en **"Enviar Denuncia"**

5. **VERIFICAR en Console:**
   ```javascript
   [Interceptor] Token encontrado: SÍ
   [Interceptor] Header Authorization agregado
   POST http://localhost/.../denuncias/create.php
   [Interceptor Response] Success: /denuncias/create.php
   ```

6. Deberías ver un mensaje: **"✅ Denuncia creada exitosamente"**

---

### PASO 6: Verificar en Base de Datos (1 minuto)

1. Abrir phpMyAdmin: **http://localhost/phpmyadmin**

2. Seleccionar base de datos: **denuncia_ciudadana**

3. Abrir tabla: **denuncias**

4. Hacer clic en **"Examinar"** (Browse)

5. **VERIFICAR que existe tu denuncia:**
   ```
   codigo: DU-2025-XXXXXX  ← Código único generado
   titulo: Prueba de sistema corregido
   usuario_id: [tu ID]  ← NO debe ser NULL
   categoria_id: [ID válido]
   prioridad: media  ← Columna nueva
   estado: registrada
   latitud: -12.04640000
   longitud: -77.04280000
   ```

---

### PASO 7: Ver Detalle con Mapa (2 minutos)

1. Volver a la aplicación: **http://localhost:5173/ciudadano/mis-denuncias**

2. Deberías ver tu denuncia en la tabla

3. Hacer clic en **"👁️ Ver Detalles"** en la denuncia que acabas de crear

4. **DEBERÍAS VER:**
   - ✅ Header con título de la denuncia
   - ✅ Código y estado
   - ✅ Descripción completa
   - ✅ **MAPA INTERACTIVO** con un marcador rojo
   - ✅ Coordenadas mostradas
   - ✅ Sidebar con información

5. **PROBAR EL MAPA:**
   - Hacer zoom con scroll o botones +/-
   - Arrastrar el mapa
   - Hacer clic en el marcador → debe aparecer popup

---

## ✅ CHECKLIST DE VERIFICACIÓN

Marca cada item conforme lo pruebes:

### Backend:
- [ ] XAMPP MySQL y Apache corriendo
- [ ] Tabla denuncias tiene columna `prioridad`
- [ ] Script de diagnóstico muestra ✅ en PASO 5 y PASO 6

### Frontend:
- [ ] Servidor Vite corriendo en http://localhost:5173
- [ ] No hay errores de compilación en terminal
- [ ] No hay errores en Console del navegador (F12)

### Funcionalidad:
- [ ] Login funciona correctamente
- [ ] Dashboard "Mis Denuncias" carga sin errores
- [ ] Formulario "Nueva Denuncia" se muestra
- [ ] Se puede enviar denuncia SIN errores
- [ ] Console muestra "Success" en el POST
- [ ] Denuncia aparece en phpMyAdmin con `usuario_id` correcto
- [ ] Botón "Ver Detalles" aparece en cada fila
- [ ] Vista de detalle carga correctamente
- [ ] Mapa se muestra con marcador
- [ ] Coordenadas y dirección se muestran
- [ ] Se puede interactuar con el mapa

---

## 🐛 SI ALGO NO FUNCIONA

### Problema: "No se ve el mapa"

**Solución:**
1. Verificar que la denuncia tenga latitud/longitud
2. Abrir Console (F12) y buscar errores de Leaflet
3. Refrescar con Ctrl+F5

### Problema: "Error 401 al crear denuncia"

**Verificar:**
```bash
# En backend, verificar .htaccess
cat "C:\xampp\htdocs\DENUNCIA CIUDADANA\backend\.htaccess"
```

**Debe contener:**
```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

**Si falta, reiniciar Apache:**
- XAMPP → Apache → Stop
- XAMPP → Apache → Start

### Problema: "La denuncia no se guarda"

**Diagnóstico:**
```bash
cd "C:\xampp\htdocs\DENUNCIA CIUDADANA\backend"
php DIAGNOSTICO_COMPLETO.php
```

**Verificar:**
- PASO 5 debe mostrar: ✅ Inserción SQL directa: OK
- PASO 6 debe mostrar: ✅ Modelo Denuncia::create(): OK

Si alguno falla:
1. Verificar que MySQL está corriendo
2. Verificar credenciales en backend/config/database.php
3. Verificar que la tabla denuncias existe

### Problema: "Cambios del frontend no se ven"

**Soluciones:**
1. Verificar que el servidor Vite está corriendo
2. Buscar errores en la terminal de Vite
3. Limpiar caché: Ctrl+Shift+R o Ctrl+F5
4. Reiniciar servidor Vite:
   ```bash
   # Ctrl+C para detener
   npm run dev
   ```

### Problema: "Error de compilación en Vite"

**Ver errores:**
- Terminal donde corre `npm run dev` mostrará el error exacto
- Buscar línea con `ERROR` o `Failed`

**Errores comunes:**
```
Module not found: leaflet
→ Solución: npm install leaflet react-leaflet

Cannot find module './DetalleDenuncia'
→ Verificar que el archivo existe en la ruta correcta
```

---

## 📊 RESUMEN DE ARCHIVOS

### Archivos Corregidos:
```
backend/
├── models/Denuncia.php              ← Generación de códigos únicos
├── DIAGNOSTICO_COMPLETO.php         ← Script de diagnóstico
├── CORREGIR_BD.php                  ← Agregó columna prioridad
└── ver_estructura.php               ← Verificar estructura de tablas

database/
└── denuncias (tabla)                ← Agregada columna 'prioridad'
```

### Archivos Nuevos:
```
frontend/src/
├── pages/ciudadano/
│   └── DetalleDenuncia.jsx          ← Vista con mapa Leaflet
└── services/
    └── denunciaService.js           ← Agregado getDenunciaById()

Documentación:
├── GUIA_PRUEBAS_SISTEMA.md          ← Esta guía
├── DETALLE_DENUNCIA_CON_MAPA.md     ← Documentación del mapa
├── ACLARACION_STACK_Y_PROBLEMAS.md  ← Análisis técnico
└── [otros .md previos]
```

---

## 🎯 RESULTADO ESPERADO

Si todo funciona correctamente:

1. ✅ Puedes iniciar sesión como ciudadano
2. ✅ Ves dashboard "Mis Denuncias" con estadísticas
3. ✅ Puedes crear nueva denuncia
4. ✅ La denuncia se guarda en BD con `usuario_id` correcto
5. ✅ La denuncia tiene código único (DU-2025-XXXXXX)
6. ✅ La denuncia tiene prioridad asignada
7. ✅ Aparece en la tabla de "Mis Denuncias"
8. ✅ Puedes hacer clic en "Ver Detalles"
9. ✅ Se abre vista de detalle con mapa interactivo
10. ✅ El mapa muestra marcador en las coordenadas correctas

---

## 📞 SOPORTE

### Comandos Útiles:

**Ver estructura de BD:**
```bash
php backend/ver_estructura.php
```

**Diagnóstico completo:**
```bash
php backend/DIAGNOSTICO_COMPLETO.php
```

**Corregir BD (si es necesario):**
```bash
php backend/CORREGIR_BD.php
```

**Iniciar frontend:**
```bash
cd frontend && npm run dev
```

**Ver logs de Apache:**
```
C:\xampp\apache\logs\error.log
```

**Ver logs de MySQL:**
```
C:\xampp\mysql\data\[computername].err
```

---

## 🎉 ¡LISTO!

Tu sistema ahora tiene:
- ✅ Backend funcional que guarda denuncias correctamente
- ✅ Generación automática de códigos únicos
- ✅ Columna prioridad en base de datos
- ✅ Vista de detalle con mapa interactivo Leaflet
- ✅ Botón "Ver Detalles" en cada denuncia
- ✅ Sistema completo de autenticación JWT
- ✅ Privacidad (cada ciudadano ve solo sus denuncias)

**Próximos pasos sugeridos:**
1. Agregar coordenadas reales a las denuncias al crearlas (geolocalización)
2. Personalizar logo y footer con tu información
3. Agregar fotos de evidencia a las denuncias
4. Implementar notificaciones por email

---

**Desarrollado y Corregido:** 19/12/2025
**Stack:** PHP REST API + React + MySQL + Leaflet
**Tiempo de diagnóstico y corrección:** ~45 minutos
