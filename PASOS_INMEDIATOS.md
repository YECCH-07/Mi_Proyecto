# ⚡ PASOS INMEDIATOS - Hazlo AHORA

## 🎯 Objetivo
Resolver el error 401 "Authorization header not found" en 5 minutos.

---

## 📝 Checklist (Marca cada paso)

### ☐ Paso 1: Verificar mod_rewrite (2 minutos)

1. Abrir el archivo:
   ```
   C:\xampp\apache\conf\httpd.conf
   ```

2. Presionar `Ctrl + F` y buscar:
   ```
   LoadModule rewrite_module
   ```

3. Asegurarte que la línea NO tenga `#` al inicio:
   ```apache
   LoadModule rewrite_module modules/mod_rewrite.so
   ```

   ❌ **Si tiene `#`:** Quitar el `#` y guardar
   ✅ **Si NO tiene `#`:** Continuar al siguiente paso

---

### ☐ Paso 2: Permitir .htaccess (2 minutos)

1. En el mismo archivo `httpd.conf`, presionar `Ctrl + F` y buscar:
   ```
   <Directory "C:/xampp/htdocs">
   ```

2. Buscar la línea `AllowOverride` dentro de esa sección:
   ```apache
   <Directory "C:/xampp/htdocs">
       Options Indexes FollowSymLinks Includes ExecCGI
       AllowOverride None    ← DEBE CAMBIAR
       Require all granted
   </Directory>
   ```

3. Cambiar `None` por `All`:
   ```apache
   <Directory "C:/xampp/htdocs">
       Options Indexes FollowSymLinks Includes ExecCGI
       AllowOverride All     ← CORRECTO
       Require all granted
   </Directory>
   ```

4. **GUARDAR** el archivo (Ctrl + S)

---

### ☐ Paso 3: Reiniciar Apache (30 segundos)

1. Abrir el Panel de Control de XAMPP

2. En la fila de **Apache**, hacer click en **"Stop"**

3. **Esperar 3 segundos**

4. Hacer click en **"Start"**

5. Verificar que diga "Running" en verde

---

### ☐ Paso 4: Probar el Login (1 minuto)

1. Abrir el navegador en:
   ```
   http://localhost:5173
   ```

2. Presionar `F12` para abrir la consola

3. Hacer click en **"Iniciar Sesión"**

4. Ingresar credenciales y hacer login

---

## ✅ Resultados Esperados

### Si FUNCIONÓ:
```
✅ Redirigido al dashboard
✅ Se muestran las denuncias
✅ En consola: [Interceptor Response] Success
```

**🎉 ¡ÉXITO! El problema está resuelto.**

---

### Si SIGUE FALLANDO:

#### Escenario A: Error en consola
```
❌ Failed to load resource: 401 (Unauthorized)
```

**Hacer prueba diagnóstica:**

1. En la consola del navegador (F12), pegar este código:
```javascript
const token = localStorage.getItem('jwt');

fetch('http://localhost/DENUNCIA%20CIUDADANA/backend/test_validate.php', {
    headers: {
        'Authorization': `Bearer ${token}`
    }
})
.then(r => r.text())
.then(text => {
    console.log('=== RESULTADO DEL TEST ===');
    console.log(text);
});
```

2. Copiar TODO el resultado que aparezca

3. Enviarme el resultado completo

---

#### Escenario B: Apache no inicia

**Error:** "Apache shutdown unexpectedly"

**Solución:**
1. Verificar que NO haya errores de sintaxis en `httpd.conf`
2. Revisar que la línea sea `AllowOverride All` (no `AllOverride` ni otro typo)
3. Ver el log de errores en:
   ```
   C:\xampp\apache\logs\error.log
   ```

---

## 🔍 Archivos que YA están creados

Estos archivos YA fueron creados automáticamente:

✅ `backend/.htaccess` - Configuración para pasar el header Authorization
✅ `backend/middleware/validate_jwt.php` - Mejorado con 4 métodos fallback
✅ `backend/test_validate.php` - Script de prueba
✅ `SOLUCIONES_COMPLETAS_AUTENTICACION.md` - Documentación completa

**NO necesitas crear nada más, solo:**
1. Modificar `httpd.conf`
2. Reiniciar Apache
3. Probar login

---

## 📞 Si necesitas ayuda

Después de completar TODOS los pasos, si sigue fallando:

1. Ejecutar el script de diagnóstico del Escenario A
2. Copiar el resultado completo
3. Revisar `C:\xampp\apache\logs\error.log` (últimas líneas)
4. Enviar ambos resultados

---

## ⏱️ Tiempo total estimado

- Paso 1: 2 minutos
- Paso 2: 2 minutos
- Paso 3: 30 segundos
- Paso 4: 1 minuto

**TOTAL: ~6 minutos**

---

## 🚨 IMPORTANTE

**DEBES reiniciar Apache para que los cambios surtan efecto.**

Los cambios en `httpd.conf` y `.htaccess` NO se aplican automáticamente.

---

## 💡 ¿Por qué esto funciona?

Apache bloquea el header `Authorization` por defecto. El `.htaccess` que creamos le dice a Apache:

```apache
"Hey Apache, cuando veas un header llamado 'Authorization',
por favor pásalo a PHP en vez de bloquearlo"
```

Pero para que Apache LEA el archivo `.htaccess`, necesitamos:
1. Tener `mod_rewrite` habilitado
2. Tener `AllowOverride All` para permitir .htaccess

Por eso necesitamos hacer los cambios en `httpd.conf` y reiniciar.

---

**¡Empieza ahora con el Paso 1!** ⬆️
