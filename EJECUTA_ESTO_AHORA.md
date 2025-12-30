# ⚡ EJECUTA ESTO AHORA - Diagnóstico en 3 Pasos

## 🎯 Objetivo
Identificar en 5 minutos EXACTAMENTE dónde está fallando la creación de denuncias.

---

## ✅ PASO 1: Prueba la Base de Datos (2 minutos)

### Abre esta URL en tu navegador:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_crear_denuncia.php
```

### ¿Qué debes ver?

#### ✅ CASO BUENO:
```
✅ Conexión a base de datos: OK
✅ Tabla 'denuncias': EXISTE
✅ Inserción SQL directa: EXITOSA
   ID insertado: 123
✅ Creación con modelo: EXITOSA
   ID generado: 124
📊 Total de denuncias en BD: 2
```

**➜ Si ves esto, la base de datos funciona correctamente. Pasa al PASO 2.**

---

#### ❌ CASO MALO A:
```
❌ ERROR CRÍTICO: No se pudo conectar a la base de datos
```

**SOLUCIÓN:**
1. Abrir XAMPP Control Panel
2. Verificar que MySQL está en verde (Running)
3. Si está apagado, hacer click en "Start"
4. Volver a ejecutar el script

---

#### ❌ CASO MALO B:
```
❌ Tabla 'denuncias': NO EXISTE o ERROR
```

**SOLUCIÓN:**
1. Abrir phpMyAdmin: http://localhost/phpmyadmin
2. Crear base de datos `denuncia_ciudadana` si no existe
3. Seleccionar la base de datos
4. Click en pestaña "SQL"
5. Ejecutar el contenido de: `database/schema.sql`
6. Volver a ejecutar el script

---

#### ❌ CASO MALO C:
```
✅ Inserción SQL directa: EXITOSA
❌ ERROR: El método create() retornó false
```

**PROBLEMA IDENTIFICADO:** El modelo `Denuncia.php` tiene un error.

**SOLUCIÓN:**
Lee la sección "Si la PRUEBA 6 (Modelo) FALLA" en `SOLUCION_DENUNCIAS_NO_SE_CREAN.md`

---

## ✅ PASO 2: Prueba el Endpoint (2 minutos)

### Abre esta URL:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_endpoint_create.php
```

### ¿Qué debes ver?

#### ✅ CASO BUENO:
```
✅ Usuario de prueba: Test Usuario (ID: 5)
✅ JWT generado exitosamente
✅ ÉXITO: Denuncia creada
   Código: DU-2025-000125
   ID: 125
✅ VERIFICACIÓN: La denuncia SÍ está en la base de datos
```

**➜ Si ves esto, el endpoint funciona. Pasa al PASO 3.**

---

#### ❌ CASO MALO:
```
❌ ERROR: La respuesta no contiene código de denuncia
   Mensaje: Access denied. Authorization header not found.
```

**PROBLEMA IDENTIFICADO:** Problema con JWT o header Authorization.

**SOLUCIÓN:**
Leer `SOLUCIONES_COMPLETAS_AUTENTICACION.md` y verificar:
1. Que `.htaccess` existe en `/backend`
2. Que Apache tiene mod_rewrite habilitado
3. Reiniciar Apache

---

## ✅ PASO 3: Prueba desde el Navegador (3 minutos)

### Abre esta URL:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/test_frontend.html
```

### Sigue estos pasos:

#### 1. Iniciar Sesión
- Email: `juan@email.com` (o tu usuario)
- Password: `123456` (o tu contraseña)
- Click en **"Iniciar Sesión y Obtener JWT"**

**Debes ver:**
```
✅ LOGIN EXITOSO
✅ JWT obtenido: eyJ0eXAiOiJKV1Qi...
✅ Usuario: Juan Pérez
✅ Rol: ciudadano
```

**Si falla:**
```
❌ ERROR en login: Login failed. User not found.
```

**SOLUCIÓN:**
El usuario no existe. Usa phpMyAdmin para crear uno:
```sql
INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol)
VALUES (
  '12345678',
  'Juan',
  'Pérez',
  'juan@email.com',
  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  -- Password: 123456
  'ciudadano'
);
```

---

#### 2. Crear Denuncia

- Dejar los datos pre-llenados o modificarlos
- Click en **"Crear Denuncia"**

**Debes ver:**
```
✅ ¡DENUNCIA CREADA EXITOSAMENTE!
✅ Código: DU-2025-000126
✅ ID: 126
```

**Si falla con Error 401:**
```
❌ Error 401: Token inválido o expirado
```

**SOLUCIÓN:**
- Haz login nuevamente
- Si persiste, revisar `SOLUCIONES_COMPLETAS_AUTENTICACION.md`

**Si falla con Error 400:**
```
❌ Error 400: Datos incompletos
```

**SOLUCIÓN:**
- Verificar que todos los campos están llenos
- Especialmente `categoria_id`, `latitud`, `longitud`

**Si falla con Error 503:**
```
❌ Error 503: No se pudo crear en la base de datos
```

**SOLUCIÓN:**
- Verificar que `categoria_id = 1` existe en tabla `categorias`
- Ejecutar:
  ```sql
  INSERT INTO categorias (nombre, descripcion) VALUES ('Servicios Básicos', 'Agua, luz, desagüe');
  ```

---

#### 3. Verificar Denuncias

- Click en **"Obtener Mis Denuncias"**

**Debes ver:**
```
✅ Denuncias obtenidas: 5
📋 LISTA DE DENUNCIAS:
1. Código: DU-2025-000126
   Título: Prueba desde navegador - Bache en la calle
   Estado: registrada
```

**Si dice:**
```
⚠️ No tienes denuncias registradas
```

**Pero acabas de crear una:**

**PROBLEMA IDENTIFICADO:** Las denuncias se crean pero las consultas no las muestran.

**SOLUCIÓN:**
Leer `SOLUCION_CONSULTAS_SQL.md` - Problema con los JOINs.

---

## 📊 Resumen de Diagnóstico

### Si los 3 pasos funcionan:

```
✅ PASO 1: Base de datos OK
✅ PASO 2: Endpoint OK
✅ PASO 3: Navegador OK
```

**➜ El problema está en el FRONTEND de React, no en el backend.**

**Siguiente acción:**
1. Abrir el frontend en el navegador
2. Presionar F12 (abrir consola)
3. Ir a la página de "Registrar Denuncia"
4. Llenar el formulario
5. Click en "Registrar"
6. **Copiar TODOS los mensajes de la consola**
7. Enviarlos para análisis

---

### Si algún paso falla:

| Paso | Estado | Problema |
|------|--------|----------|
| 1 | ❌ | Base de datos o modelo |
| 2 | ❌ | Endpoint o JWT |
| 3 | ❌ | Frontend o CORS |

**Siguiente acción:**
- Copiar la salida COMPLETA del paso que falló
- Enviarla para análisis detallado
- Aplicar la solución correspondiente

---

## 🎯 ¿Qué hacer después?

### Escenario A: Todo funcionó ✅

Si los 3 pasos dieron verde:
1. Ir al frontend real de React
2. Intentar crear una denuncia
3. Si falla, copiar errores de consola (F12)

### Escenario B: Algún paso falló ❌

1. Identificar QUÉ paso falló (1, 2 o 3)
2. Copiar la salida COMPLETA del script
3. Leer la solución específica en `SOLUCION_DENUNCIAS_NO_SE_CREAN.md`
4. Aplicar la corrección
5. Volver a ejecutar el script

---

## 📝 Formato de Reporte

Si necesitas ayuda, envía esto:

```
=== PASO 1 ===
[Copiar toda la salida de test_crear_denuncia.php]

=== PASO 2 ===
[Copiar toda la salida de test_endpoint_create.php]

=== PASO 3 ===
[Copiar todo el log del área negra de test_frontend.html]

=== CONSOLA DEL NAVEGADOR ===
[F12 → Console → Copiar todos los errores]
```

---

## ⏱️ Tiempo Total Estimado

- Paso 1: 2 minutos
- Paso 2: 2 minutos
- Paso 3: 3 minutos

**TOTAL: ~7 minutos**

---

## 🚀 ¡EMPIEZA AHORA!

1. Abre: `http://localhost/DENUNCIA%20CIUDADANA/backend/test_crear_denuncia.php`
2. Lee el resultado
3. Continúa con los siguientes pasos según corresponda

---

**¡Con estos 3 scripts identificaremos el problema en menos de 10 minutos!** 🎯
