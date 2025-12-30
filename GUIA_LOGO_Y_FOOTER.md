# 🎨 Guía de Personalización: Logo y Footer

## 📋 Resumen de Cambios Implementados

Se han agregado **2 componentes profesionales** al sistema:

### ✅ 1. Logo en el Header (Navbar)
- Ubicación: **Esquina superior izquierda**
- Tamaño: 48x48 píxeles
- Con fondo blanco redondeado
- Fallback a icono 🏛️ si no existe la imagen

### ✅ 2. Footer Profesional
- **Columna 1:** Información de la municipalidad
- **Columna 2:** Teléfonos de contacto de gerencias
- **Columna 3:** Correos electrónicos y páginas web
- **Sección inferior:** Redes sociales y copyright

---

## 🖼️ Cómo Agregar el Logo Real

### Opción 1: Usar el Logo en /public (Recomendado)

1. **Obtén tu logo** en formato PNG o JPG
   - Tamaño recomendado: **512x512 píxeles** mínimo
   - Fondo transparente (PNG preferible)
   - Formato cuadrado o cercano

2. **Renombra el archivo:**
   ```
   logo-municipalidad.png
   ```

3. **Coloca el archivo en:**
   ```
   frontend/public/logo-municipalidad.png
   ```

4. **¡Listo!** El logo aparecerá automáticamente

### Opción 2: Cambiar el Nombre del Archivo

Si tu logo tiene otro nombre, edita `Navbar.jsx` y `Footer.jsx`:

**Archivo:** `frontend/src/components/Navbar.jsx` (línea 37)
```jsx
// Cambiar esto:
src="/logo-municipalidad.png"

// Por tu archivo:
src="/mi-logo-custom.png"
```

**Archivo:** `frontend/src/components/Footer.jsx` (línea 13)
```jsx
// Cambiar esto:
src="/logo-municipalidad.png"

// Por tu archivo:
src="/mi-logo-custom.png"
```

### Opción 3: Logo desde URL Externa

Si el logo está en otro servidor:

```jsx
src="https://www.municipalidad.gob.pe/logo.png"
```

---

## 📝 Personalizar Información de Contacto

### 1. Editar Teléfonos de las Gerencias

**Archivo:** `frontend/src/components/Footer.jsx` (líneas 40-64)

```jsx
<li>
  <div className="text-white font-semibold">Gerencia General</div>
  <a href="tel:+51987654321" className="text-blue-400...">
    📱 +51 987 654 321  {/* ← CAMBIAR AQUÍ */}
  </a>
</li>
```

**Pasos:**
1. Localizar la sección "📞 Contacto de Gerencias"
2. Cambiar los nombres de las gerencias
3. Cambiar los números de teléfono
4. Agregar o quitar gerencias según necesites

**Ejemplo para agregar una nueva gerencia:**
```jsx
<li>
  <div className="text-white font-semibold">Gerencia de Seguridad Ciudadana</div>
  <a href="tel:+51987654325" className="text-blue-400 hover:text-blue-300 transition">
    📱 +51 987 654 325
  </a>
</li>
```

---

### 2. Editar Correos Electrónicos

**Archivo:** `frontend/src/components/Footer.jsx` (líneas 69-92)

```jsx
<li>
  <div className="text-gray-400">Mesa de Partes:</div>
  <a href="mailto:mesadepartes@municipalidad.gob.pe" ...>
    mesadepartes@municipalidad.gob.pe  {/* ← CAMBIAR AQUÍ */}
  </a>
</li>
```

**Cambiar por tus correos reales:**
```jsx
<li>
  <div className="text-gray-400">Mesa de Partes:</div>
  <a href="mailto:mesadepartes@tumunicipio.gob.pe" ...>
    mesadepartes@tumunicipio.gob.pe
  </a>
</li>
```

---

### 3. Editar Páginas Web

**Archivo:** `frontend/src/components/Footer.jsx` (líneas 97-125)

```jsx
<li>
  <div className="text-gray-400">Portal Principal:</div>
  <a
    href="https://www.municipalidad.gob.pe"  {/* ← CAMBIAR AQUÍ */}
    target="_blank"
    rel="noopener noreferrer"
    className="text-blue-400..."
  >
    www.municipalidad.gob.pe  {/* ← Y AQUÍ */}
    ...
  </a>
</li>
```

**Cambiar por tu dominio:**
```jsx
<a
  href="https://www.tumunicipio.gob.pe"
  target="_blank"
  rel="noopener noreferrer"
  ...
>
  www.tumunicipio.gob.pe
  ...
</a>
```

---

### 4. Editar Redes Sociales

**Archivo:** `frontend/src/components/Footer.jsx` (líneas 136-180)

**Cambiar URLs de redes sociales:**
```jsx
{/* Facebook */}
<a
  href="https://facebook.com/municipalidad"  {/* ← CAMBIAR */}
  target="_blank"
  ...
>

{/* Twitter */}
<a
  href="https://twitter.com/municipalidad"  {/* ← CAMBIAR */}
  target="_blank"
  ...
>

{/* Instagram */}
<a
  href="https://instagram.com/municipalidad"  {/* ← CAMBIAR */}
  target="_blank"
  ...
>

{/* YouTube */}
<a
  href="https://youtube.com/municipalidad"  {/* ← CAMBIAR */}
  target="_blank"
  ...
>
```

**Para quitar una red social:**
Comentar o eliminar el bloque completo:
```jsx
{/* Eliminar esta sección si no tienes YouTube
<a
  href="https://youtube.com/municipalidad"
  ...
>
  ...
</a>
*/}
```

---

### 5. Personalizar el Nombre de la Municipalidad

**En el Navbar:** `frontend/src/components/Navbar.jsx` (líneas 53-59)

```jsx
<div className="hidden md:block">
  <h1 className="text-white text-xl font-bold leading-tight">
    Sistema de Denuncias  {/* ← CAMBIAR */}
  </h1>
  <p className="text-white/80 text-xs">
    Municipalidad  {/* ← CAMBIAR */}
  </p>
</div>
```

**Ejemplo personalizado:**
```jsx
<h1 className="text-white text-xl font-bold leading-tight">
  Sistema de Denuncias Ciudadanas
</h1>
<p className="text-white/80 text-xs">
  Municipalidad Provincial de Lima
</p>
```

**En el Footer:** `frontend/src/components/Footer.jsx` (líneas 15-25)

```jsx
<h3 className="text-white font-bold text-lg">
  Municipalidad  {/* ← CAMBIAR */}
</h3>
<p className="text-gray-400 text-sm">
  Sistema de Denuncias Ciudadanas  {/* ← CAMBIAR */}
</p>
```

---

## 🎨 Personalizar Colores (Opcional)

### Cambiar Color del Header

**Archivo:** `frontend/src/components/Navbar.jsx` (línea 28)

```jsx
// Color actual: bg-primary
<nav className="bg-primary shadow-lg">

// Cambiar a otro color:
<nav className="bg-blue-600 shadow-lg">  // Azul
<nav className="bg-green-700 shadow-lg"> // Verde
<nav className="bg-red-600 shadow-lg">   // Rojo
```

### Cambiar Color del Footer

**Archivo:** `frontend/src/components/Footer.jsx`

```jsx
// Sección principal (línea 6)
<div className="container mx-auto px-4 py-12">  {/* Mantener */}

// Sección inferior (línea 136)
<div className="bg-gray-950 border-t border-gray-800">  {/* Puedes cambiar */}
```

---

## 📁 Estructura de Archivos

```
frontend/
├── public/
│   └── logo-municipalidad.png  ← AGREGAR TU LOGO AQUÍ
│
├── src/
│   ├── components/
│   │   ├── Navbar.jsx  ← Logo y navegación
│   │   └── Footer.jsx  ← Información de contacto
│   │
│   └── App.jsx  ← Incluye Navbar y Footer
```

---

## 🚀 Aplicar los Cambios

### 1. Agregar el Logo

```bash
# Copiar tu logo a la carpeta public
frontend/public/logo-municipalidad.png
```

### 2. Editar la Información de Contacto

```bash
# Editar Footer.jsx
frontend/src/components/Footer.jsx
```

Cambiar:
- Teléfonos (líneas 40-64)
- Correos (líneas 69-92)
- Páginas web (líneas 97-125)
- Redes sociales (líneas 136-180)

### 3. Ver los Cambios

```bash
# Si el servidor está corriendo, los cambios se verán automáticamente
# Si no, ejecutar:
cd frontend
npm run dev
```

---

## ✅ Checklist de Personalización

### Logo
- [ ] Logo preparado (512x512px, PNG transparente)
- [ ] Logo copiado a `frontend/public/logo-municipalidad.png`
- [ ] Logo visible en el header
- [ ] Logo visible en el footer

### Información de Contacto
- [ ] Nombre de la municipalidad actualizado
- [ ] Teléfonos de gerencias actualizados
- [ ] Correos electrónicos actualizados
- [ ] Páginas web actualizadas
- [ ] Redes sociales actualizadas

### Verificación
- [ ] El header se ve correctamente
- [ ] El footer se ve correctamente
- [ ] Todos los links funcionan
- [ ] Los teléfonos abren el marcador
- [ ] Los correos abren el cliente de email

---

## 📸 Vista Previa

### Header con Logo
```
┌─────────────────────────────────────────────────────────────────┐
│  🏛️  Sistema de Denuncias              Mi Panel | Cerrar Sesión │
│      Municipalidad                                               │
└─────────────────────────────────────────────────────────────────┘
```

### Footer
```
┌─────────────────────────────────────────────────────────────────┐
│                                                                 │
│  🏛️ Municipalidad    📞 Contacto Gerencias    📧 Info Digital  │
│  Sistema de          • Gerencia General       • Mesa de Partes │
│  Denuncias           • Obras Públicas         • Denuncias      │
│                      • Servicios              • Soporte         │
│                                                                 │
├─────────────────────────────────────────────────────────────────┤
│  🌐 Redes Sociales          © 2025 Municipalidad               │
│  📘 Facebook  🐦 Twitter  📷 Instagram  ▶️ YouTube              │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🆘 Solución de Problemas

### El logo no aparece

**Problema:** Se ve el icono 🏛️ en lugar del logo

**Soluciones:**
1. Verificar que el archivo existe en `frontend/public/logo-municipalidad.png`
2. Verificar el nombre exacto del archivo (mayúsculas/minúsculas)
3. Refrescar el navegador con Ctrl+F5
4. Verificar la consola del navegador (F12) para errores

### Los cambios no se ven

**Problema:** Edité el Footer pero no veo cambios

**Soluciones:**
1. Guardar el archivo (Ctrl+S)
2. Esperar a que Vite recompile (verás en la terminal)
3. Refrescar el navegador
4. Verificar que editaste el archivo correcto

### El footer no se queda abajo

**Problema:** El footer flota en medio de la página

**Solución:**
Verificar que `App.jsx` tiene:
```jsx
<div className="flex flex-col min-h-screen bg-gray-50">
  <Navbar />
  <main className="flex-grow">  {/* ← IMPORTANTE */}
    <Routes>...</Routes>
  </main>
  <Footer />
</div>
```

---

## 💡 Consejos Profesionales

### Para el Logo:
- ✅ Usa PNG con fondo transparente
- ✅ Tamaño cuadrado (512x512px o 1024x1024px)
- ✅ Colores que contrasten con el fondo del header
- ✅ Optimiza el tamaño del archivo (<100KB)

### Para los Teléfonos:
- ✅ Usa formato internacional: `+51 987 654 321`
- ✅ El `href="tel:+51987654321"` NO lleva espacios
- ✅ Verifica que los números son correctos

### Para los Correos:
- ✅ Usa direcciones institucionales (@municipalidad.gob.pe)
- ✅ Evita correos personales (gmail, hotmail, etc.)

### Para las Redes Sociales:
- ✅ Usa las URLs oficiales de la municipalidad
- ✅ Quita las redes que no uses
- ✅ Verifica que los links funcionan

---

## 📞 Soporte

Si necesitas ayuda adicional:
- Revisa los archivos `Navbar.jsx` y `Footer.jsx`
- Todos los textos están en español y son fáciles de identificar
- Busca los comentarios `{/* ← CAMBIAR AQUÍ */}`

---

**¡Tu sistema ahora tiene un aspecto profesional con logo y footer completo!** 🎉
