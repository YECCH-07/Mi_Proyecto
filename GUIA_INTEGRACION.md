# 🔧 Guía de Integración - Sistema de Denuncia Ciudadana

## ✅ Estado Actual del Sistema

### Base de Datos
- ✓ Base de datos `denuncia_ciudadana` creada
- ✓ 7 tablas configuradas (usuarios, denuncias, categorías, áreas, evidencias, seguimiento, notificaciones)
- ✓ 8 categorías predefinidas
- ✓ 5 áreas municipales configuradas
- ✓ Usuario administrador creado

### Backend (PHP + MySQL)
- ✓ Modelos PHP funcionando
- ✓ API REST configurada
- ✓ CORS habilitado
- ✓ JWT para autenticación

### Frontend (React + Vite)
- ✓ Componentes React configurados
- ✓ Rutas definidas
- ✓ Servicios API conectados
- ✓ Tailwind CSS implementado

---

## 🚀 Pasos para Probar la Integración

### 1. Verificar que XAMPP esté corriendo

Abre el Panel de Control de XAMPP y asegúrate de que:
- ✅ Apache esté en estado "Running"
- ✅ MySQL esté en estado "Running"

### 2. Verificar la Base de Datos

Accede a: http://localhost/phpmyadmin

Deberías ver:
- Base de datos: `denuncia_ciudadana`
- Tablas: 7 tablas (usuarios, denuncias, categorias, areas_municipales, evidencias, seguimiento, notificaciones)

### 3. Probar el Backend

#### Endpoint: Categorías
```bash
curl http://localhost/DENUNCIA%20CIUDADANA/backend/api/categorias/read.php
```
**Respuesta esperada:** JSON con 8 categorías

#### Endpoint: Áreas
```bash
curl http://localhost/DENUNCIA%20CIUDADANA/backend/api/areas/read.php
```
**Respuesta esperada:** JSON con 5 áreas municipales

#### Endpoint: Denuncias
```bash
curl http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php
```
**Respuesta esperada:** `{"message":"No denuncias found."}` (normal, porque no hay denuncias todavía)

### 4. Iniciar el Frontend

Abre una terminal en la carpeta del proyecto y ejecuta:

```bash
cd frontend
npm install     # Solo la primera vez
npm run dev
```

El servidor de desarrollo debería iniciar en: http://localhost:5173

### 5. Probar el Frontend

#### 5.1 Página de Inicio
Accede a: http://localhost:5173

Deberías ver la página principal del sistema.

#### 5.2 Registro de Usuario
1. Ve a: http://localhost:5173/register
2. Completa el formulario:
   - DNI: 87654321
   - Nombres: Tu Nombre
   - Apellidos: Tu Apellido
   - Email: tucorreo@ejemplo.com
   - Password: tu_password
   - Teléfono: 987654321 (opcional)
3. Haz clic en "Registrar"

**Resultado esperado:** Mensaje de éxito y redirección al login

#### 5.3 Login de Usuario
1. Ve a: http://localhost:5173/login
2. Ingresa las credenciales que acabas de crear
3. Haz clic en "Iniciar Sesión"

**Resultado esperado:** Redirección al dashboard

#### 5.4 Login como Administrador
Credenciales del admin:
- Email: `admin@municusco.gob.pe`
- Password: `admin123`

#### 5.5 Crear una Denuncia
1. Ve a: http://localhost:5173/nueva-denuncia
2. Completa el formulario:
   - Título: "Bache en Av. El Sol"
   - Descripción: "Gran bache que causa problemas de tránsito"
   - Categoría: Selecciona "Baches"
   - Ubicación: Haz clic en el mapa (centro de Cusco: -13.5319, -71.9675)
   - Dirección: "Av. El Sol, Cusco"
3. (Opcional) Sube una foto
4. Haz clic en "Enviar Denuncia"

**Resultado esperado:**
- Código de denuncia generado (ej: DU-2025-000001)
- Mensaje de éxito

#### 5.6 Consultar Denuncias
1. Ve a: http://localhost:5173/consulta
2. Ingresa el código de la denuncia (ej: DU-2025-000001)
3. Haz clic en "Buscar"

**Resultado esperado:** Detalles de la denuncia

#### 5.7 Dashboard (Solo Admin/Operadores)
1. Inicia sesión como admin
2. Ve a: http://localhost:5173/dashboard

**Resultado esperado:** Panel con estadísticas y lista de denuncias

---

## 🔍 Endpoints del Backend Disponibles

### Autenticación
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/auth/register.php` | POST | Registrar nuevo usuario |
| `/api/auth/login.php` | POST | Login y generación de JWT |
| `/api/auth/verify.php` | GET | Verificar token JWT |

### Denuncias
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/denuncias/create.php` | POST | Crear denuncia |
| `/api/denuncias/read.php` | GET | Listar todas las denuncias |
| `/api/denuncias/read.php?id={id}` | GET | Obtener denuncia por ID |
| `/api/denuncias/read.php?codigo={codigo}` | GET | Obtener denuncia por código |
| `/api/denuncias/update.php` | PUT | Actualizar denuncia |
| `/api/denuncias/delete.php` | DELETE | Eliminar denuncia |
| `/api/denuncias/locations.php` | GET | Obtener ubicaciones para mapa |

### Categorías y Áreas
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/categorias/read.php` | GET | Listar categorías |
| `/api/areas/read.php` | GET | Listar áreas municipales |

### Archivos
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/archivos/upload.php` | POST | Subir evidencias |

### Seguimiento
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/seguimiento/read.php?denuncia_id={id}` | GET | Historial de seguimiento |

### Estadísticas
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/estadisticas/denuncias_por_area.php` | GET | Estadísticas por área |
| `/api/estadisticas/denuncias_por_categoria.php` | GET | Estadísticas por categoría |
| `/api/estadisticas/denuncias_por_estado.php` | GET | Estadísticas por estado |

### Reportes
| Endpoint | Método | Descripción |
|----------|--------|-------------|
| `/api/reportes/generate_pdf.php` | POST | Generar reporte PDF |

---

## 🐛 Solución de Problemas Comunes

### Error: CORS policy blocking
**Solución:** El archivo `backend/config/cors.php` ya está configurado. Asegúrate de que XAMPP esté corriendo.

### Error: 404 Not Found en API
**Solución:** Verifica que la URL sea correcta:
```
http://localhost/DENUNCIA%20CIUDADANA/backend/api/...
```
Nota el espacio codificado como `%20`.

### Error: No se conecta a la base de datos
**Solución:**
1. Verifica que MySQL esté corriendo en XAMPP
2. Ejecuta el script de setup: http://localhost/DENUNCIA%20CIUDADANA/backend/setup_database.php

### Error: Frontend no carga datos
**Solución:**
1. Abre DevTools (F12) → Network
2. Verifica que las peticiones lleguen a la URL correcta
3. Verifica que el backend responda con status 200
4. Limpia la caché del navegador (Ctrl+Shift+R)

### Error: "Failed to compile" en Vite
**Solución:**
```bash
cd frontend
rm -rf node_modules
npm install
npm run dev
```

---

## 📊 Datos de Prueba

### Categorías (ya insertadas)
1. Baches
2. Alumbrado Público
3. Basura
4. Agua y Desagüe
5. Infraestructura
6. Seguridad
7. Parques y Jardines
8. Tránsito

### Áreas Municipales (ya insertadas)
1. Gerencia de Infraestructura
2. Gerencia de Servicios Públicos
3. Gerencia de Transporte
4. Gerencia de Seguridad Ciudadana
5. Gerencia de Medio Ambiente

### Usuario Admin (ya creado)
- Email: `admin@municusco.gob.pe`
- Password: `admin123`
- Rol: admin

---

## 🎯 Checklist de Integración

- [ ] XAMPP Apache corriendo
- [ ] XAMPP MySQL corriendo
- [ ] Base de datos creada
- [ ] Tablas populadas
- [ ] Backend responde correctamente
- [ ] Frontend inicia sin errores
- [ ] Registro de usuario funciona
- [ ] Login funciona
- [ ] Creación de denuncia funciona
- [ ] Consulta de denuncia funciona
- [ ] Dashboard carga correctamente

---

## 📝 Notas Importantes

1. **URL del Proyecto:** El proyecto está en `C:\xampp\htdocs\DENUNCIA CIUDADANA\`
2. **URL del Backend:** `http://localhost/DENUNCIA%20CIUDADANA/backend/api/`
3. **URL del Frontend:** `http://localhost:5173`
4. **Base de Datos:** `denuncia_ciudadana` en localhost
5. **Usuario Root MySQL:** Sin contraseña (configuración por defecto de XAMPP)

---

## 🔐 Seguridad

- ✓ Contraseñas hasheadas con bcrypt (cost factor 12)
- ✓ Prepared statements para prevenir SQL injection
- ✓ Sanitización de inputs con htmlspecialchars
- ✓ CORS configurado
- ✓ JWT para autenticación stateless

---

## 📞 Soporte

Si encuentras algún problema:
1. Verifica los logs de Apache en: `C:\xampp\apache\logs\error.log`
2. Verifica los logs de MySQL en: `C:\xampp\mysql\data\mysql_error.log`
3. Revisa la consola del navegador (F12) para errores de JavaScript
4. Verifica la pestaña Network en DevTools para errores de API

---

**Última actualización:** 2025-12-18
