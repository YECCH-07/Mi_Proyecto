<?php
/**
 * SCRIPT DE CORRECCIÓN DE BASE DE DATOS
 * Ejecutar: php backend/CORREGIR_BD.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║              CORRECCIÓN DE BASE DE DATOS                      ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

include 'config/database.php';
$db = (new Database())->getConnection();

$correcciones_aplicadas = [];
$errores = [];

// ============================================================================
// CORRECCIÓN 1: Agregar columna 'prioridad' a tabla denuncias
// ============================================================================
echo "🔧 CORRECCIÓN 1: Agregando columna 'prioridad' a tabla denuncias...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Verificar si ya existe
    $stmt = $db->query("SHOW COLUMNS FROM denuncias LIKE 'prioridad'");
    $exists = $stmt->fetch();

    if ($exists) {
        echo "⚠️ La columna 'prioridad' ya existe. Omitiendo...\n";
    } else {
        $sql = "ALTER TABLE denuncias
                ADD COLUMN prioridad ENUM('baja', 'media', 'alta', 'urgente')
                DEFAULT 'media'
                AFTER es_anonima";

        $db->exec($sql);
        echo "✅ Columna 'prioridad' agregada exitosamente\n";
        $correcciones_aplicadas[] = "Columna prioridad agregada";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Fallo al agregar columna prioridad: " . $e->getMessage();
}

echo "\n";

// ============================================================================
// CORRECCIÓN 2: Actualizar denuncias existentes con prioridad por defecto
// ============================================================================
echo "🔧 CORRECCIÓN 2: Actualizando denuncias existentes...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Verificar si la columna existe ahora
    $stmt = $db->query("SHOW COLUMNS FROM denuncias LIKE 'prioridad'");
    if ($stmt->fetch()) {
        $stmt = $db->query("UPDATE denuncias SET prioridad = 'media' WHERE prioridad IS NULL");
        $affected = $stmt->rowCount();

        if ($affected > 0) {
            echo "✅ $affected denuncias actualizadas con prioridad 'media'\n";
            $correcciones_aplicadas[] = "$affected denuncias actualizadas";
        } else {
            echo "ℹ️ No hay denuncias sin prioridad\n";
        }
    }
} catch (Exception $e) {
    echo "⚠️ ADVERTENCIA: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================================
// CORRECCIÓN 3: Limpiar códigos duplicados
// ============================================================================
echo "🔧 CORRECCIÓN 3: Verificando códigos duplicados...\n";
echo str_repeat("-", 65) . "\n";

try {
    $stmt = $db->query("
        SELECT codigo, COUNT(*) as count
        FROM denuncias
        GROUP BY codigo
        HAVING count > 1
    ");

    $duplicates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($duplicates) > 0) {
        echo "⚠️ Se encontraron " . count($duplicates) . " códigos duplicados:\n";

        foreach ($duplicates as $dup) {
            echo "   - Código: {$dup['codigo']} (aparece {$dup['count']} veces)\n";

            // Obtener todas las denuncias con este código
            $stmt2 = $db->prepare("SELECT id FROM denuncias WHERE codigo = ? ORDER BY id");
            $stmt2->execute([$dup['codigo']]);
            $ids = $stmt2->fetchAll(PDO::FETCH_COLUMN);

            // Mantener el primero, renumerar los demás
            array_shift($ids); // Quitar el primero

            foreach ($ids as $id_duplicado) {
                $nuevo_codigo = 'DU-' . date('Y') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

                $stmt3 = $db->prepare("UPDATE denuncias SET codigo = ? WHERE id = ?");
                $stmt3->execute([$nuevo_codigo, $id_duplicado]);

                echo "      ✅ ID $id_duplicado renumerado a: $nuevo_codigo\n";
            }
        }

        $correcciones_aplicadas[] = count($duplicates) . " códigos duplicados corregidos";
    } else {
        echo "✅ No se encontraron códigos duplicados\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    $errores[] = "Fallo al verificar duplicados: " . $e->getMessage();
}

echo "\n";

// ============================================================================
// CORRECCIÓN 4: Verificar índice UNIQUE en columna codigo
// ============================================================================
echo "🔧 CORRECCIÓN 4: Verificando índice UNIQUE en columna codigo...\n";
echo str_repeat("-", 65) . "\n";

try {
    $stmt = $db->query("SHOW INDEXES FROM denuncias WHERE Column_name = 'codigo'");
    $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $hasUnique = false;
    foreach ($indexes as $index) {
        if ($index['Non_unique'] == 0) {
            $hasUnique = true;
            break;
        }
    }

    if ($hasUnique) {
        echo "✅ Índice UNIQUE ya existe en columna 'codigo'\n";
    } else {
        echo "⚠️ No existe índice UNIQUE en 'codigo'\n";
        echo "   Creando índice UNIQUE...\n";

        $db->exec("ALTER TABLE denuncias ADD UNIQUE KEY codigo_unique (codigo)");
        echo "✅ Índice UNIQUE creado exitosamente\n";
        $correcciones_aplicadas[] = "Índice UNIQUE agregado";
    }
} catch (Exception $e) {
    echo "⚠️ ADVERTENCIA: " . $e->getMessage() . "\n";
    echo "   Posiblemente ya existe o hay duplicados\n";
}

echo "\n";

// ============================================================================
// RESUMEN
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                    RESUMEN DE CORRECCIONES                    ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

if (!empty($correcciones_aplicadas)) {
    echo "✅ CORRECCIONES APLICADAS (" . count($correcciones_aplicadas) . "):\n";
    foreach ($correcciones_aplicadas as $i => $correccion) {
        echo "   " . ($i + 1) . ". $correccion\n";
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

if (empty($errores)) {
    echo "🎉 ¡BASE DE DATOS CORREGIDA EXITOSAMENTE!\n";
    echo "\n";
    echo "Próximos pasos:\n";
    echo "1. Ejecutar: php backend/DIAGNOSTICO_COMPLETO.php\n";
    echo "2. Probar creación de denuncia desde el frontend\n";
    echo "3. Verificar en phpMyAdmin que se guardó\n";
} else {
    echo "⚠️ Se encontraron errores. Por favor, revísalos antes de continuar.\n";
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Correcciones completadas: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
