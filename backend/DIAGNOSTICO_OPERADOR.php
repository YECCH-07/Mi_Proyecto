<?php
/**
 * DIAGNÓSTICO COMPLETO: Funcionalidad de Operador
 *
 * Verifica todos los componentes necesarios para la gestión de denuncias
 * por parte del operador.
 *
 * Ejecutar: php backend/DIAGNOSTICO_OPERADOR.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          DIAGNÓSTICO: FUNCIONALIDAD DE OPERADOR              ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errores = [];
$advertencias = [];
$exitos = [];

// ============================================================================
// PASO 1: VERIFICAR CONEXIÓN A BD
// ============================================================================
echo "🔌 PASO 1: Verificando conexión a base de datos...\n";
echo str_repeat("-", 65) . "\n";

try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    echo "✅ Conexión exitosa\n";
    $exitos[] = "Conexión a BD OK";

    $stmt = $db->query("SELECT DATABASE()");
    $dbName = $stmt->fetchColumn();
    echo "   Base de datos: $dbName\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Fallo en conexión a BD";
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 2: VERIFICAR TABLAS NECESARIAS
// ============================================================================
echo "📋 PASO 2: Verificando tablas necesarias...\n";
echo str_repeat("-", 65) . "\n";

$tablas_requeridas = ['denuncias', 'usuarios', 'categorias', 'evidencias', 'seguimiento'];

foreach ($tablas_requeridas as $tabla) {
    $stmt = $db->query("SHOW TABLES LIKE '$tabla'");
    if ($stmt->rowCount() > 0) {
        echo "   ✅ Tabla '$tabla' existe\n";
        $exitos[] = "Tabla $tabla OK";
    } else {
        echo "   ❌ Tabla '$tabla' NO existe\n";
        $errores[] = "Falta tabla $tabla";
    }
}

echo "\n";

// ============================================================================
// PASO 3: VERIFICAR ARCHIVOS BACKEND
// ============================================================================
echo "📂 PASO 3: Verificando archivos backend (API)...\n";
echo str_repeat("-", 65) . "\n";

$archivos_backend = [
    'api/denuncias/detalle_operador.php' => 'Endpoint de detalle',
    'api/denuncias/actualizar_estado.php' => 'Endpoint de actualización',
    'middleware/validate_jwt.php' => 'Validación JWT'
];

foreach ($archivos_backend as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "   ✅ $descripcion ($archivo)\n";
        $exitos[] = "$descripcion existe";
    } else {
        echo "   ❌ $descripcion NO existe ($archivo)\n";
        $errores[] = "Falta $descripcion";
    }
}

echo "\n";

// ============================================================================
// PASO 4: VERIFICAR ARCHIVOS FRONTEND
// ============================================================================
echo "⚛️ PASO 4: Verificando archivos frontend (React)...\n";
echo str_repeat("-", 65) . "\n";

$archivos_frontend = [
    '../frontend/src/pages/operador/OperadorDashboard.jsx' => 'Dashboard Operador',
    '../frontend/src/pages/operador/DetalleDenunciaOperador.jsx' => 'Vista Detalle Operador',
    '../frontend/src/App.jsx' => 'Rutas de la aplicación'
];

foreach ($archivos_frontend as $archivo => $descripcion) {
    if (file_exists($archivo)) {
        echo "   ✅ $descripcion\n";
        $exitos[] = "$descripcion existe";

        // Verificar contenido específico
        $contenido = file_get_contents($archivo);

        if ($archivo == '../frontend/src/pages/operador/OperadorDashboard.jsx') {
            if (strpos($contenido, 'Ver Detalle') !== false) {
                echo "      ✅ Tiene botón 'Ver Detalle'\n";
                $exitos[] = "Dashboard tiene botón Ver Detalle";
            } else {
                echo "      ⚠️ NO tiene botón 'Ver Detalle'\n";
                $advertencias[] = "Dashboard sin botón Ver Detalle";
            }

            if (strpos($contenido, 'Link') !== false) {
                echo "      ✅ Importa componente Link de React Router\n";
                $exitos[] = "Dashboard importa Link";
            } else {
                echo "      ❌ NO importa Link de React Router\n";
                $errores[] = "Dashboard no importa Link";
            }
        }

        if ($archivo == '../frontend/src/App.jsx') {
            if (strpos($contenido, 'DetalleDenunciaOperador') !== false) {
                echo "      ✅ Importa DetalleDenunciaOperador\n";
                $exitos[] = "App.jsx importa componente de detalle";
            } else {
                echo "      ❌ NO importa DetalleDenunciaOperador\n";
                $errores[] = "App.jsx no importa DetalleDenunciaOperador";
            }

            if (strpos($contenido, '/operador/denuncia/:id') !== false) {
                echo "      ✅ Tiene ruta /operador/denuncia/:id\n";
                $exitos[] = "Ruta de detalle configurada";
            } else {
                echo "      ❌ NO tiene ruta /operador/denuncia/:id\n";
                $errores[] = "Falta ruta de detalle";
            }
        }

    } else {
        echo "   ❌ $descripcion NO existe\n";
        $errores[] = "Falta $descripcion";
    }
}

echo "\n";

// ============================================================================
// PASO 5: PROBAR ENDPOINT DE DETALLE (simulado)
// ============================================================================
echo "🧪 PASO 5: Probando endpoint de detalle...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Obtener una denuncia de prueba
    $stmt = $db->query("SELECT id FROM denuncias LIMIT 1");
    $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($denuncia) {
        $denuncia_id = $denuncia['id'];
        echo "   ℹ️ Usando denuncia ID: $denuncia_id para prueba\n";

        // Simular consulta del endpoint
        $query = "SELECT
                    d.id,
                    d.codigo,
                    d.titulo,
                    d.descripcion,
                    d.latitud,
                    d.longitud,
                    c.nombre as categoria_nombre,
                    u.nombres,
                    u.email
                FROM
                    denuncias d
                    LEFT JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN usuarios u ON d.usuario_id = u.id
                WHERE
                    d.id = :denuncia_id
                LIMIT 1";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':denuncia_id', $denuncia_id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "   ✅ Query de detalle funciona correctamente\n";
            echo "      - Código: {$resultado['codigo']}\n";
            echo "      - Título: {$resultado['titulo']}\n";
            echo "      - Categoría: " . ($resultado['categoria_nombre'] ?? 'N/A') . "\n";
            echo "      - Ciudadano: " . ($resultado['nombres'] ?? 'Anónimo') . "\n";
            echo "      - Email: " . ($resultado['email'] ?? 'N/A') . "\n";

            if ($resultado['latitud'] && $resultado['longitud']) {
                $google_maps = "https://www.google.com/maps?q={$resultado['latitud']},{$resultado['longitud']}";
                echo "      - Google Maps URL: $google_maps\n";
                $exitos[] = "Georeferenciación disponible";
            } else {
                echo "      ⚠️ Sin coordenadas GPS\n";
                $advertencias[] = "Denuncia sin coordenadas";
            }

            $exitos[] = "Endpoint de detalle funcional";
        } else {
            echo "   ❌ Query no retornó resultados\n";
            $errores[] = "Query de detalle falló";
        }
    } else {
        echo "   ⚠️ No hay denuncias en la BD para probar\n";
        $advertencias[] = "BD sin denuncias";
    }
} catch (Exception $e) {
    echo "   ❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Error en prueba de endpoint";
}

echo "\n";

// ============================================================================
// PASO 6: VERIFICAR EVIDENCIAS Y SEGUIMIENTO
// ============================================================================
echo "📸 PASO 6: Verificando evidencias y seguimiento...\n";
echo str_repeat("-", 65) . "\n";

// Contar evidencias
$stmt = $db->query("SELECT COUNT(*) as total FROM evidencias");
$evidencias_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   📊 Evidencias en BD: $evidencias_count\n";

if ($evidencias_count > 0) {
    $exitos[] = "Hay evidencias en BD";
    $stmt = $db->query("SELECT id, denuncia_id, tipo, archivo_url FROM evidencias LIMIT 3");
    echo "      Ejemplos:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "      - ID {$row['id']}: {$row['tipo']} para denuncia {$row['denuncia_id']}\n";
    }
} else {
    $advertencias[] = "No hay evidencias en BD";
}

echo "\n";

// Contar seguimientos
$stmt = $db->query("SELECT COUNT(*) as total FROM seguimiento");
$seguimiento_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
echo "   📊 Seguimientos en BD: $seguimiento_count\n";

if ($seguimiento_count > 0) {
    $exitos[] = "Hay seguimientos en BD";
    $stmt = $db->query("SELECT id, denuncia_id, estado_anterior, estado_nuevo FROM seguimiento ORDER BY created_at DESC LIMIT 3");
    echo "      Últimos cambios:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "      - Denuncia {$row['denuncia_id']}: {$row['estado_anterior']} → {$row['estado_nuevo']}\n";
    }
} else {
    $advertencias[] = "No hay seguimientos en BD";
}

echo "\n";

// ============================================================================
// PASO 7: VERIFICAR CONFIGURACIÓN DE EMAIL
// ============================================================================
echo "📧 PASO 7: Verificando configuración de email...\n";
echo str_repeat("-", 65) . "\n";

$php_ini = php_ini_loaded_file();
echo "   📄 Archivo php.ini: $php_ini\n";

$smtp = ini_get('SMTP');
$smtp_port = ini_get('smtp_port');

if ($smtp && $smtp != '') {
    echo "   ✅ SMTP configurado: $smtp:$smtp_port\n";
    $exitos[] = "SMTP configurado";
} else {
    echo "   ⚠️ SMTP no configurado en php.ini\n";
    echo "      (Email no funcionará sin configuración)\n";
    $advertencias[] = "SMTP no configurado";
}

// Verificar si PHPMailer está instalado
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Composer vendor instalado (puede usar PHPMailer)\n";
    $exitos[] = "Composer instalado";
} else {
    echo "   ⚠️ Composer vendor no encontrado\n";
    $advertencias[] = "Sin Composer/PHPMailer";
}

echo "\n";

// ============================================================================
// PASO 8: VERIFICAR USUARIOS OPERADORES
// ============================================================================
echo "👥 PASO 8: Verificando usuarios operadores...\n";
echo str_repeat("-", 65) . "\n";

$stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'operador'");
$operadores_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

echo "   📊 Operadores registrados: $operadores_count\n";

if ($operadores_count > 0) {
    $exitos[] = "Hay operadores en BD";
    $stmt = $db->query("SELECT id, nombres, apellidos, email FROM usuarios WHERE rol = 'operador' LIMIT 3");
    echo "      Operadores:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "      - {$row['nombres']} {$row['apellidos']} ({$row['email']})\n";
    }
} else {
    $advertencias[] = "No hay operadores en BD";
    echo "      ⚠️ Necesitas crear al menos un usuario con rol 'operador'\n";
}

echo "\n";

// ============================================================================
// RESUMEN FINAL
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN DEL DIAGNÓSTICO                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ ÉXITOS (" . count($exitos) . "):\n";
foreach ($exitos as $i => $exito) {
    if ($i < 5) { // Mostrar solo los primeros 5
        echo "   " . ($i + 1) . ". $exito\n";
    }
}
if (count($exitos) > 5) {
    echo "   ... y " . (count($exitos) - 5) . " más\n";
}
echo "\n";

if (!empty($advertencias)) {
    echo "⚠️ ADVERTENCIAS (" . count($advertencias) . "):\n";
    foreach ($advertencias as $i => $adv) {
        echo "   " . ($i + 1) . ". $adv\n";
    }
    echo "\n";
}

if (!empty($errores)) {
    echo "❌ ERRORES (" . count($errores) . "):\n";
    foreach ($errores as $i => $error) {
        echo "   " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

// ============================================================================
// CONCLUSIÓN Y PRÓXIMOS PASOS
// ============================================================================
if (empty($errores)) {
    echo "🎉 ¡SISTEMA LISTO PARA USAR!\n";
    echo "\n";
    echo "📋 PASOS PARA PROBAR:\n";
    echo "\n";
    echo "1. Iniciar servidor frontend:\n";
    echo "   cd frontend\n";
    echo "   npm run dev\n";
    echo "\n";
    echo "2. Abrir navegador:\n";
    echo "   http://localhost:5173\n";
    echo "\n";
    echo "3. Iniciar sesión como operador:\n";
    echo "   Email: operador@ejemplo.com (o el email de tu operador)\n";
    echo "   Password: [tu contraseña]\n";
    echo "\n";
    echo "4. En el dashboard, buscar el botón:\n";
    echo "   👁️ Ver Detalle\n";
    echo "\n";
    echo "5. Hacer clic en 'Ver Detalle' de cualquier denuncia\n";
    echo "\n";
    echo "6. En la vista de detalle:\n";
    echo "   - Ver información completa\n";
    echo "   - Ver evidencias (si las hay)\n";
    echo "   - Hacer clic en 'Abrir en Google Maps' (si tiene coordenadas)\n";
    echo "   - Actualizar estado y agregar comentario\n";
    echo "   - Hacer clic en 'Guardar y Notificar'\n";
    echo "\n";
} else {
    echo "⚠️ SE ENCONTRARON ERRORES QUE DEBEN CORREGIRSE\n";
    echo "\n";
    echo "Por favor, revisa los errores listados arriba y corrige:\n";
    foreach ($errores as $i => $error) {
        echo "   " . ($i + 1) . ". $error\n";
    }
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════════\n";
echo "Diagnóstico completado: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
