# Instrucciones para Ejecutar el Sistema

## Estado del Sistema
✅ Base de datos configurada y funcionando
✅ Backend API corregido y funcionando
✅ Usuarios de prueba creados
✅ Frontend configurado y listo

## Credenciales de Prueba

### Administrador
- Email: `admin@muni.gob.pe`
- Password: `admin123`
- Rol: admin

### Supervisor
- Email: `carlos.sup@muni.gob.pe`
- Password: `carlos123`
- Rol: supervisor

### Operador
- Email: `elena.op@muni.gob.pe`
- Password: `elena123`
- Rol: operador

### Ciudadano
- Email: `juan.perez@mail.com`
- Password: `juan123`
- Rol: ciudadano

## Pasos para Ejecutar

### 1. Iniciar XAMPP
- Asegúrate de que Apache y MySQL estén corriendo

### 2. Iniciar el Frontend
```bash
cd "C:\xampp\htdocs\DENUNCIA CIUDADANA\frontend"
npm run dev
```

El frontend estará disponible en: `http://localhost:5173`

### 3. Acceder al Sistema
- Abre tu navegador en `http://localhost:5173`
- Inicia sesión con cualquiera de las credenciales de arriba
- Serás redirigido al dashboard según tu rol

## URLs del Sistema

### Frontend
- Home: `http://localhost:5173`
- Login: `http://localhost:5173/login`
- Registro: `http://localhost:5173/register`
- Consulta pública: `http://localhost:5173/consulta`

### Backend API
- Base URL: `http://localhost/DENUNCIA%20CIUDADANA/backend/api`
- Login: `http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php`
- Denuncias: `http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php`

## Funcionalidades por Rol

### Administrador
- Dashboard completo con estadísticas
- Gestión de usuarios
- Gestión de áreas y categorías
- Ver todas las denuncias
- Generar reportes

### Supervisor
- Dashboard con estadísticas de su área
- Ver denuncias asignadas a su área
- Actualizar estado de denuncias
- Asignar denuncias a operadores

### Operador
- Ver denuncias asignadas
- Actualizar estado y seguimiento
- Gestionar evidencias

### Ciudadano
- Crear nuevas denuncias
- Ver mis denuncias
- Consultar estado
- Seguimiento de denuncias

## Correcciones Aplicadas

1. ✅ Error de sintaxis en `login.php` - Movido `use` statements fuera del bloque try
2. ✅ Variables de entorno no cargadas - Agregada función `loadEnv()` en login.php
3. ✅ Contraseñas de usuarios actualizadas - Creados usuarios con contraseñas conocidas
4. ✅ Estructura de base de datos verificada - Todas las tablas existen y tienen datos

## Notas Importantes

- El sistema usa JWT para autenticación
- Los tokens expiran en 1 hora (3600 segundos)
- Las evidencias se suben a `backend/uploads/`
- Los logs se guardan en `backend/logs/`
- CORS está configurado para permitir localhost:5173

## Troubleshooting

### Si el login no funciona:
1. Verifica que MySQL esté corriendo en XAMPP
2. Verifica que la base de datos `denuncia_ciudadana` exista
3. Verifica que el archivo `.env` exista en `backend/`

### Si el frontend no carga:
1. Asegúrate de haber ejecutado `npm install` en la carpeta frontend
2. Verifica que el puerto 5173 no esté en uso
3. Revisa la consola del navegador para errores

### Si hay errores de CORS:
1. Verifica que el frontend esté corriendo en el puerto correcto (5173)
2. Verifica que el archivo `backend/config/cors.php` exista
