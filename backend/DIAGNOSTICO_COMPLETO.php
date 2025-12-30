<?php
/**
 * DIAGNÓSTICO COMPLETO DEL SISTEMA
 * Ejecutar: php backend/DIAGNOSTICO_COMPLETO.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          DIAGNÓSTICO COMPLETO DEL SISTEMA                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

$errores = [];
$advertencias = [];

// ============================================================================
// PASO 1: VERIFICAR CONEXIÓN A BASE DE DATOS
// ============================================================================
echo "📊 PASO 1: Verificando conexión a base de datos...\n";
echo str_repeat("-", 65) . "\n";

try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "✅ Conexión a base de datos: OK\n";

        // Verificar nombre de la base de datos
        $stmt = $db->query("SELECT DATABASE()");
        $dbName = $stmt->fetchColumn();
        echo "   Base de datos: $dbName\n";
    } else {
        echo "❌ ERROR: No se pudo conectar a la base de datos\n";
        $errores[] = "Conexión a base de datos falló";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Excepción en conexión: " . $e->getMessage();
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 2: VERIFICAR ESTRUCTURA DE TABLAS
// ============================================================================
echo "📋 PASO 2: Verificando estructura de tablas...\n";
echo str_repeat("-", 65) . "\n";

// Verificar tabla denuncias
try {
    $stmt = $db->query("DESCRIBE denuncias");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $required_columns = [
        'id', 'codigo', 'titulo', 'descripcion', 'latitud', 'longitud',
        'direccion_referencia', 'estado', 'usuario_id', 'categoria_id',
        'area_asignada_id', 'es_anonima', 'prioridad', 'created_at', 'updated_at'
    ];

    echo "Columnas requeridas en tabla 'denuncias':\n";
    $missing_columns = [];
    foreach ($required_columns as $col) {
        if (in_array($col, $columns)) {
            echo "   ✅ $col\n";
        } else {
            echo "   ❌ $col - FALTA\n";
            $missing_columns[] = $col;
        }
    }

    if (!empty($missing_columns)) {
        $errores[] = "Faltan columnas: " . implode(', ', $missing_columns);
    }

} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Tabla denuncias no existe o error en estructura";
}

echo "\n";

// ============================================================================
// PASO 3: VERIFICAR CATEGORÍAS
// ============================================================================
echo "📂 PASO 3: Verificando categorías...\n";
echo str_repeat("-", 65) . "\n";

try {
    $stmt = $db->query("SELECT COUNT(*) FROM categorias");
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "✅ Categorías encontradas: $count\n";

        // Mostrar las categorías
        $stmt = $db->query("SELECT id, nombre FROM categorias LIMIT 5");
        echo "   Primeras categorías:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - ID {$row['id']}: {$row['nombre']}\n";
        }
    } else {
        echo "⚠️ ADVERTENCIA: No hay categorías en la base de datos\n";
        $advertencias[] = "No hay categorías";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Error al verificar categorías";
}

echo "\n";

// ============================================================================
// PASO 4: VERIFICAR USUARIOS
// ============================================================================
echo "👥 PASO 4: Verificando usuarios...\n";
echo str_repeat("-", 65) . "\n";

try {
    $stmt = $db->query("SELECT COUNT(*) FROM usuarios");
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        echo "✅ Usuarios encontrados: $count\n";

        // Verificar usuarios ciudadanos
        $stmt = $db->query("SELECT COUNT(*) FROM usuarios WHERE rol = 'ciudadano'");
        $ciudadanos = $stmt->fetchColumn();
        echo "   Ciudadanos: $ciudadanos\n";

        // Mostrar primer usuario ciudadano
        $stmt = $db->query("SELECT id, nombre, email FROM usuarios WHERE rol = 'ciudadano' LIMIT 1");
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            echo "   Usuario de prueba disponible:\n";
            echo "   - ID: {$user['id']}\n";
            echo "   - Nombre: {$user['nombre']}\n";
            echo "   - Email: {$user['email']}\n";
        }
    } else {
        echo "⚠️ ADVERTENCIA: No hay usuarios en la base de datos\n";
        $advertencias[] = "No hay usuarios";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Error al verificar usuarios";
}

echo "\n";

// ============================================================================
// PASO 5: PROBAR INSERCIÓN DIRECTA CON SQL
// ============================================================================
echo "💾 PASO 5: Probando inserción DIRECTA con SQL...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Obtener un usuario_id válido
    $stmt = $db->query("SELECT id FROM usuarios WHERE rol = 'ciudadano' LIMIT 1");
    $usuario_id = $stmt->fetchColumn();

    // Obtener una categoría válida
    $stmt = $db->query("SELECT id FROM categorias LIMIT 1");
    $categoria_id = $stmt->fetchColumn();

    if (!$usuario_id || !$categoria_id) {
        echo "⚠️ ADVERTENCIA: No hay usuario o categoría para probar\n";
        $advertencias[] = "Faltan datos para prueba de inserción";
    } else {
        $test_codigo = 'TEST-' . time();

        $query = "INSERT INTO denuncias SET
            codigo = :codigo,
            titulo = :titulo,
            descripcion = :descripcion,
            latitud = :latitud,
            longitud = :longitud,
            direccion_referencia = :direccion,
            estado = 'registrada',
            usuario_id = :usuario_id,
            categoria_id = :categoria_id,
            es_anonima = 0,
            prioridad = 'media',
            created_at = NOW(),
            updated_at = NOW()";

        $stmt = $db->prepare($query);

        $stmt->bindParam(':codigo', $test_codigo);
        $titulo = 'PRUEBA DIAGNÓSTICO';
        $stmt->bindParam(':titulo', $titulo);
        $descripcion = 'Esta es una denuncia de prueba creada por el script de diagnóstico';
        $stmt->bindParam(':descripcion', $descripcion);
        $latitud = -12.0464;
        $stmt->bindParam(':latitud', $latitud);
        $longitud = -77.0428;
        $stmt->bindParam(':longitud', $longitud);
        $direccion = 'Dirección de prueba';
        $stmt->bindParam(':direccion', $direccion);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':categoria_id', $categoria_id);

        if ($stmt->execute()) {
            $last_id = $db->lastInsertId();
            echo "✅ Inserción SQL directa: OK\n";
            echo "   ID insertado: $last_id\n";
            echo "   Código: $test_codigo\n";

            // Verificar que se insertó
            $stmt = $db->prepare("SELECT * FROM denuncias WHERE id = ?");
            $stmt->execute([$last_id]);
            $inserted = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($inserted) {
                echo "   ✅ Verificación: Registro encontrado en BD\n";
                echo "   Datos insertados:\n";
                echo "   - Título: {$inserted['titulo']}\n";
                echo "   - Usuario ID: {$inserted['usuario_id']}\n";
                echo "   - Categoría ID: {$inserted['categoria_id']}\n";
                echo "   - Estado: {$inserted['estado']}\n";

                // Limpiar registro de prueba
                $db->exec("DELETE FROM denuncias WHERE id = $last_id");
                echo "   🧹 Registro de prueba eliminado\n";
            }
        } else {
            echo "❌ ERROR: Fallo en inserción SQL directa\n";
            $error_info = $stmt->errorInfo();
            echo "   Código error: {$error_info[0]}\n";
            echo "   Mensaje: {$error_info[2]}\n";
            $errores[] = "Fallo inserción SQL: " . $error_info[2];
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Excepción en inserción SQL: " . $e->getMessage();
}

echo "\n";

// ============================================================================
// PASO 6: PROBAR MODELO Denuncia::create()
// ============================================================================
echo "🔧 PASO 6: Probando modelo Denuncia::create()...\n";
echo str_repeat("-", 65) . "\n";

try {
    include_once 'models/Denuncia.php';

    $denuncia = new Denuncia($db);

    // Obtener IDs válidos
    $stmt = $db->query("SELECT id FROM usuarios WHERE rol = 'ciudadano' LIMIT 1");
    $usuario_id = $stmt->fetchColumn();

    $stmt = $db->query("SELECT id FROM categorias LIMIT 1");
    $categoria_id = $stmt->fetchColumn();

    if (!$usuario_id || !$categoria_id) {
        echo "⚠️ ADVERTENCIA: No hay datos para probar modelo\n";
    } else {
        $denuncia->titulo = 'PRUEBA MODELO';
        $denuncia->descripcion = 'Prueba del modelo Denuncia::create()';
        $denuncia->latitud = -12.0464;
        $denuncia->longitud = -77.0428;
        $denuncia->direccion_referencia = 'Prueba dirección';
        $denuncia->estado = 'registrada';
        $denuncia->usuario_id = $usuario_id;
        $denuncia->categoria_id = $categoria_id;
        $denuncia->es_anonima = false;
        $denuncia->prioridad = 'media';

        if ($denuncia->create()) {
            echo "✅ Modelo Denuncia::create(): OK\n";
            echo "   Código generado: {$denuncia->codigo}\n";

            // Verificar en BD
            $stmt = $db->prepare("SELECT * FROM denuncias WHERE codigo = ?");
            $stmt->execute([$denuncia->codigo]);
            $inserted = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($inserted) {
                echo "   ✅ Verificación: Registro encontrado en BD\n";
                echo "   ID: {$inserted['id']}\n";

                // Limpiar
                $db->exec("DELETE FROM denuncias WHERE id = {$inserted['id']}");
                echo "   🧹 Registro de prueba eliminado\n";
            } else {
                echo "   ❌ ERROR: Modelo dice OK pero no se encuentra en BD\n";
                $errores[] = "Modelo create() no insertó en BD";
            }
        } else {
            echo "❌ ERROR: Modelo Denuncia::create() falló\n";
            $errores[] = "Fallo en modelo create()";
        }
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Excepción en modelo: " . $e->getMessage();
}

echo "\n";

// ============================================================================
// PASO 7: VERIFICAR ENDPOINT create.php
// ============================================================================
echo "🌐 PASO 7: Analizando endpoint api/denuncias/create.php...\n";
echo str_repeat("-", 65) . "\n";

$create_file = 'api/denuncias/create.php';
if (file_exists($create_file)) {
    echo "✅ Archivo existe: $create_file\n";

    $content = file_get_contents($create_file);

    // Verificar elementos clave
    $checks = [
        'include.*cors.php' => 'CORS configurado',
        'validate_jwt\(\)' => 'Validación JWT',
        'usuario_id.*user_data' => 'Asignación de usuario_id',
        '\$denuncia->create\(\)' => 'Llamada a create()',
        'json_encode' => 'Respuesta JSON'
    ];

    foreach ($checks as $pattern => $description) {
        if (preg_match("/$pattern/", $content)) {
            echo "   ✅ $description\n";
        } else {
            echo "   ⚠️ $description - NO ENCONTRADO\n";
            $advertencias[] = "Endpoint: falta $description";
        }
    }
} else {
    echo "❌ ERROR: Archivo no existe: $create_file\n";
    $errores[] = "Endpoint create.php no existe";
}

echo "\n";

// ============================================================================
// PASO 8: VERIFICAR JWT
// ============================================================================
echo "🔐 PASO 8: Verificando sistema JWT...\n";
echo str_repeat("-", 65) . "\n";

if (file_exists('middleware/validate_jwt.php')) {
    echo "✅ Middleware JWT existe\n";

    if (file_exists('vendor/autoload.php')) {
        echo "✅ Firebase JWT instalado\n";
    } else {
        echo "❌ ERROR: Firebase JWT no instalado\n";
        echo "   Ejecutar: cd backend && composer install\n";
        $errores[] = "JWT library no instalada";
    }
} else {
    echo "❌ ERROR: Middleware JWT no existe\n";
    $errores[] = "Falta validate_jwt.php";
}

echo "\n";

// ============================================================================
// PASO 9: VERIFICAR CONFIGURACIÓN APACHE (.htaccess)
// ============================================================================
echo "⚙️ PASO 9: Verificando configuración Apache...\n";
echo str_repeat("-", 65) . "\n";

if (file_exists('.htaccess')) {
    echo "✅ Archivo .htaccess existe en backend/\n";

    $htaccess = file_get_contents('.htaccess');

    if (strpos($htaccess, 'HTTP_AUTHORIZATION') !== false) {
        echo "   ✅ Configuración de Authorization header presente\n";
    } else {
        echo "   ⚠️ Falta configuración de Authorization header\n";
        echo "   Esto puede causar error 401\n";
        $advertencias[] = ".htaccess sin configuración de Authorization";
    }
} else {
    echo "⚠️ ADVERTENCIA: No existe .htaccess en backend/\n";
    $advertencias[] = "Falta .htaccess en backend";
}

echo "\n";

// ============================================================================
// RESUMEN FINAL
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN DEL DIAGNÓSTICO                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

if (empty($errores) && empty($advertencias)) {
    echo "🎉 TODO ESTÁ FUNCIONANDO CORRECTAMENTE\n";
    echo "\n";
    echo "El sistema está configurado correctamente.\n";
    echo "Si las denuncias no se guardan desde el frontend:\n";
    echo "1. Verificar consola del navegador (F12)\n";
    echo "2. Verificar Network tab para ver requests\n";
    echo "3. Verificar que el token JWT esté presente\n";
    echo "4. Ejecutar: php backend/test_endpoint_create.php\n";
} else {
    if (!empty($errores)) {
        echo "❌ ERRORES ENCONTRADOS (" . count($errores) . "):\n";
        foreach ($errores as $i => $error) {
            echo "   " . ($i + 1) . ". $error\n";
        }
        echo "\n";
    }

    if (!empty($advertencias)) {
        echo "⚠️ ADVERTENCIAS (" . count($advertencias) . "):\n";
        foreach ($advertencias as $i => $adv) {
            echo "   " . ($i + 1) . ". $adv\n";
        }
        echo "\n";
    }

    echo "🔧 RECOMENDACIONES:\n";
    echo "1. Corregir los errores listados arriba\n";
    echo "2. Ejecutar este script nuevamente\n";
    echo "3. Si los errores persisten, revisar logs de Apache/PHP\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Diagnóstico completado: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
