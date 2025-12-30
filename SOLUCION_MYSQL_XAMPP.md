# 🔧 SOLUCIÓN: MySQL Shutdown Unexpectedly - XAMPP

## 🔴 PROBLEMA REPORTADO
```
Error: MySQL shutdown unexpectedly.
This may be due to a blocked port, missing dependencies,
improper privileges, a crash, or a shutdown by another method.
```

## 📋 DIAGNÓSTICO PASO A PASO

### 1. VERIFICAR PUERTO 3306 (CAUSA MÁS COMÚN)

**Problema:** Otro proceso está usando el puerto 3306

**Solución:**

```cmd
# Abrir CMD como Administrador y ejecutar:
netstat -ano | findstr :3306
```

Si aparece un proceso usando el puerto 3306:
- Anota el PID (último número)
- Abre el Administrador de Tareas
- Ve a la pestaña "Detalles"
- Busca el PID y finaliza ese proceso

**Procesos comunes que bloquean MySQL:**
- `mysqld.exe` (otra instancia de MySQL)
- `vmware-hostd.exe` (VMware)
- Otros servicios de bases de datos

**Cambiar puerto si es necesario:**
1. Abre: `C:\xampp\mysql\bin\my.ini`
2. Busca: `port=3306`
3. Cambia a: `port=3307`
4. Guarda y reinicia MySQL desde XAMPP Control Panel

---

### 2. VERIFICAR LOGS DE ERROR

**Ubicación del log:**
```
C:\xampp\mysql\data\mysql_error.log
```

**Buscar en el log:**
- `[ERROR]` - Errores críticos
- `InnoDB` - Problemas con el motor de almacenamiento
- `crashed` - Tablas corruptas
- `port` - Conflictos de puerto

---

### 3. ARCHIVOS CORRUPTOS (ibdata1, ib_logfile)

**Síntomas:**
- MySQL no inicia después de un apagado forzado
- Errores de InnoDB en el log

**Solución - BACKUP PRIMERO:**

```cmd
# 1. HACER BACKUP de la carpeta data
xcopy /E /I C:\xampp\mysql\data C:\xampp\mysql\data_backup
```

**Luego eliminar archivos temporales:**
```cmd
cd C:\xampp\mysql\data
# Eliminar estos archivos (SOLO si tienes backup):
del ib_logfile0
del ib_logfile1
del ibdata1
```

⚠️ **IMPORTANTE:** Solo haz esto si tienes backup. MySQL los regenerará al iniciar.

---

### 4. REPARAR TABLAS CORRUPTAS

Si tienes bases de datos corruptas:

```cmd
# Desde XAMPP Shell o CMD:
cd C:\xampp\mysql\bin

# Reparar base de datos específica:
mysqlcheck -u root -p --auto-repair denuncia_ciudadana

# O reparar TODAS las bases de datos:
mysqlcheck -u root -p --auto-repair --all-databases
```

---

### 5. PERMISOS DE WINDOWS

**Problema:** XAMPP no tiene permisos para escribir en la carpeta data

**Solución:**

1. Click derecho en: `C:\xampp\mysql\data`
2. Propiedades → Seguridad
3. Editar → Agregar
4. Escribe: `Todos`
5. Dar "Control total"
6. Aplicar y Aceptar

---

### 6. ANTIVIRUS / FIREWALL

**Problema:** El antivirus bloquea mysqld.exe

**Solución:**

Agregar excepciones en tu antivirus para:
- `C:\xampp\mysql\bin\mysqld.exe`
- `C:\xampp\mysql\data\` (carpeta completa)

**Windows Defender:**
1. Configuración → Actualización y Seguridad
2. Seguridad de Windows → Protección contra virus
3. Administrar configuración → Exclusiones
4. Agregar las rutas mencionadas

---

### 7. REINSTALAR MYSQL (ÚLTIMO RECURSO)

**Si nada funciona, reinstalar MySQL conservando los datos:**

```cmd
# 1. BACKUP de las bases de datos
xcopy /E /I C:\xampp\mysql\data C:\backup_mysql_data

# 2. Desde XAMPP Control Panel:
#    - Detener MySQL
#    - Desinstalar MySQL (botón Config → Uninstall)

# 3. Reinstalar:
#    - Descargar XAMPP actualizado
#    - Instalar solo el componente MySQL

# 4. Restaurar bases de datos desde backup
```

---

## 🚀 SOLUCIÓN RÁPIDA (Más Común)

**99% de las veces es el puerto bloqueado:**

```cmd
# 1. Abrir CMD como Administrador
netstat -ano | findstr :3306

# 2. Si aparece un PID, finalízalo en Administrador de Tareas

# 3. O cambia el puerto en my.ini:
notepad C:\xampp\mysql\bin\my.ini
# Busca: port=3306
# Cambia a: port=3307
# Guarda

# 4. También cambia en PHP:
notepad C:\xampp\php\php.ini
# Busca: mysqli.default_port
# Cambia a: 3307

# 5. También en tu config de database:
notepad C:\xampp\htdocs\DENUNCIA CIUDADANA\backend\config\database.php
# Cambia: define('DB_HOST', 'localhost:3307');
```

---

## 📊 VERIFICAR QUE MYSQL FUNCIONE

Después de aplicar la solución:

```cmd
# 1. Iniciar MySQL desde XAMPP Control Panel

# 2. Verificar conexión:
cd C:\xampp\mysql\bin
mysql -u root -p

# 3. Una vez dentro de MySQL:
SHOW DATABASES;
USE denuncia_ciudadana;
SHOW TABLES;
SELECT COUNT(*) FROM denuncias;

# Si todo funciona, MySQL está OK
```

---

## 🔍 CHECKLIST DE VERIFICACIÓN

- [ ] Verificar puerto 3306 con `netstat`
- [ ] Revisar `mysql_error.log`
- [ ] Comprobar permisos en carpeta `data`
- [ ] Verificar que no haya otro MySQL corriendo
- [ ] Revisar configuración de antivirus
- [ ] Hacer backup antes de cualquier cambio
- [ ] Probar conexión después de la solución

---

## 📞 SI NADA FUNCIONA

**Envíame el contenido de:**
```
C:\xampp\mysql\data\mysql_error.log
```

Para diagnosticar el problema específico.
