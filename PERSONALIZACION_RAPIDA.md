# ⚡ Personalización Rápida - 5 Minutos

## 🎯 Acciones Inmediatas

### ✅ PASO 1: Agregar Tu Logo (1 minuto)

1. Toma el logo de tu municipalidad (PNG o JPG)
2. Renómbralo a: `logo-municipalidad.png`
3. Cópialo a: `frontend/public/logo-municipalidad.png`
4. ¡Listo! El logo aparecerá automáticamente

**Ubicaciones donde aparece:**
- ✅ Header (esquina superior izquierda)
- ✅ Footer (columna izquierda)

---

### ✅ PASO 2: Actualizar Teléfonos (2 minutos)

Editar: `frontend/src/components/Footer.jsx`

**Buscar línea 40-64** y cambiar los teléfonos:

```jsx
<li>
  <div className="text-white font-semibold">Gerencia General</div>
  <a href="tel:+51987654321" className="...">
    📱 +51 987 654 321  ← CAMBIAR POR TU NÚMERO
  </a>
</li>
```

**Cambiar:**
- Gerencia General: `+51 987 654 321` → Tu número
- Gerencia de Obras Públicas: `+51 987 654 322` → Tu número
- Gerencia de Servicios: `+51 987 654 323` → Tu número
- Gerencia de Desarrollo Social: `+51 987 654 324` → Tu número

---

### ✅ PASO 3: Actualizar Correos (1 minuto)

Editar: `frontend/src/components/Footer.jsx`

**Buscar línea 69-92** y cambiar los correos:

```jsx
<li>
  <div className="text-gray-400">Mesa de Partes:</div>
  <a href="mailto:mesadepartes@municipalidad.gob.pe" ...>
    mesadepartes@municipalidad.gob.pe  ← CAMBIAR
  </a>
</li>
```

**Cambiar:**
- Mesa de Partes: `mesadepartes@municipalidad.gob.pe` → Tu correo
- Denuncias: `denuncias@municipalidad.gob.pe` → Tu correo
- Soporte: `soporte@municipalidad.gob.pe` → Tu correo

---

### ✅ PASO 4: Actualizar Páginas Web (1 minuto)

Editar: `frontend/src/components/Footer.jsx`

**Buscar línea 97-125** y cambiar las URLs:

```jsx
<a
  href="https://www.municipalidad.gob.pe"  ← CAMBIAR
  target="_blank"
  ...
>
  www.municipalidad.gob.pe  ← Y AQUÍ TAMBIÉN
</a>
```

**Cambiar:**
- Portal Principal: `www.municipalidad.gob.pe` → Tu dominio
- Transparencia: `transparencia.municipalidad.gob.pe` → Tu dominio

---

### ✅ PASO 5: Actualizar Redes Sociales (1 minuto)

Editar: `frontend/src/components/Footer.jsx`

**Buscar línea 136-180** y cambiar las URLs:

```jsx
<a href="https://facebook.com/municipalidad" ...>  ← CAMBIAR
<a href="https://twitter.com/municipalidad" ...>   ← CAMBIAR
<a href="https://instagram.com/municipalidad" ...> ← CAMBIAR
<a href="https://youtube.com/municipalidad" ...>   ← CAMBIAR
```

---

## 🎨 Personalizar el Nombre (Opcional)

### En el Header

Editar: `frontend/src/components/Navbar.jsx` (línea 53-59)

```jsx
<h1 className="text-white text-xl font-bold leading-tight">
  Sistema de Denuncias  ← CAMBIAR
</h1>
<p className="text-white/80 text-xs">
  Municipalidad  ← CAMBIAR
</p>
```

**Ejemplo:**
```jsx
<h1 className="text-white text-xl font-bold leading-tight">
  Sistema de Denuncias Ciudadanas
</h1>
<p className="text-white/80 text-xs">
  Municipalidad Provincial de Lima
</p>
```

---

## 📝 Resumen de Archivos a Editar

| Qué Cambiar | Archivo | Líneas |
|-------------|---------|--------|
| Logo | `frontend/public/logo-municipalidad.png` | - |
| Teléfonos | `frontend/src/components/Footer.jsx` | 40-64 |
| Correos | `frontend/src/components/Footer.jsx` | 69-92 |
| Páginas Web | `frontend/src/components/Footer.jsx` | 97-125 |
| Redes Sociales | `frontend/src/components/Footer.jsx` | 136-180 |
| Nombre Municipalidad | `frontend/src/components/Navbar.jsx` | 53-59 |

---

## ⏱️ Tiempo Total: ~6 minutos

- Paso 1 (Logo): 1 minuto
- Paso 2 (Teléfonos): 2 minutos
- Paso 3 (Correos): 1 minuto
- Paso 4 (Web): 1 minuto
- Paso 5 (Redes): 1 minuto

---

## 🔍 Cómo Encontrar las Líneas Rápidamente

### En VS Code:
1. Presiona `Ctrl + G`
2. Escribe el número de línea
3. Presiona Enter

### O busca el texto:
1. Presiona `Ctrl + F`
2. Busca: `📞 Contacto de Gerencias`
3. Edita los teléfonos debajo

---

## ✅ Verificar los Cambios

### 1. Guardar los archivos
Presiona `Ctrl + S` en cada archivo editado

### 2. Ver los cambios
Si el servidor está corriendo (`npm run dev`), los cambios se verán automáticamente

### 3. Refrescar el navegador
Presiona `F5` o `Ctrl + F5`

---

## 🆘 Problemas Comunes

### El logo no aparece
- ✅ Verificar que el archivo esté en `frontend/public/`
- ✅ Verificar el nombre exacto: `logo-municipalidad.png`
- ✅ Refrescar con `Ctrl + F5`

### Los cambios no se ven
- ✅ Guardar el archivo (`Ctrl + S`)
- ✅ Esperar a que Vite recompile
- ✅ Refrescar el navegador

### Error al compilar
- ✅ Verificar que cerraste todas las etiquetas `<a>...</a>`
- ✅ Verificar que las comillas están balanceadas
- ✅ Revisar la consola de errores

---

## 📚 Documentación Completa

Para más detalles, lee: `GUIA_LOGO_Y_FOOTER.md`

---

## 🎉 ¡Listo!

Con estos 5 pasos tendrás un sistema completamente personalizado con:
- ✅ Logo de tu organización
- ✅ Teléfonos de contacto reales
- ✅ Correos electrónicos oficiales
- ✅ Enlaces a páginas web
- ✅ Redes sociales actualizadas

**Tiempo total: ~6 minutos** ⏱️
