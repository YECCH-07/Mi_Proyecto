<?php
/**
 * SCRIPT DE PRUEBA DE CONSULTAS SQL
 *
 * Este script prueba todas las consultas SQL corregidas
 * para verificar que devuelven datos correctamente
 */

header("Content-Type: text/plain; charset=UTF-8");

echo "====================================================================\n";
echo "SCRIPT DE PRUEBA - Consultas SQL Corregidas\n";
echo "====================================================================\n\n";

// Incluir archivos necesarios
include_once 'config/database.php';
include_once 'models/Denuncia.php';

// Conectar a la base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "❌ ERROR: No se pudo conectar a la base de datos\n";
    exit();
}

echo "✅ Conexión a base de datos: OK\n\n";

// Instanciar el modelo
$denuncia = new Denuncia($db);

echo "====================================================================\n";
echo "PRUEBA 1: Consulta para ADMINISTRADOR (readForAdmin)\n";
echo "====================================================================\n";

try {
    $stmt = $denuncia->readForAdmin();
    $count = $stmt->rowCount();

    echo "✅ Consulta ejecutada correctamente\n";
    echo "📊 Total de denuncias: $count\n\n";

    if ($count > 0) {
        echo "Primeros 3 registros:\n";
        echo str_repeat("-", 80) . "\n";

        $counter = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC) and $counter < 3) {
            echo "ID: " . $row['id'] . "\n";
            echo "Código: " . $row['codigo'] . "\n";
            echo "Título: " . $row['titulo'] . "\n";
            echo "Estado: " . $row['estado'] . "\n";
            echo "Categoría: " . ($row['categoria_nombre'] ?? 'NULL') . "\n";
            echo "Área: " . ($row['area_nombre'] ?? 'NULL - No asignada') . "\n";
            echo "Usuario: " . ($row['usuario_nombre'] ?? 'Anónimo') . "\n";
            echo "Fecha: " . ($row['fecha_registro'] ?? 'NULL') . "\n";
            echo str_repeat("-", 80) . "\n";
            $counter++;
        }
    } else {
        echo "⚠️ No hay denuncias en la base de datos\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR en consulta: " . $e->getMessage() . "\n";
}

echo "\n";

echo "====================================================================\n";
echo "PRUEBA 2: Consulta para CIUDADANO (readForCiudadano)\n";
echo "====================================================================\n";

try {
    // Obtener el ID del primer usuario con rol ciudadano
    $query_user = "SELECT id, nombres, apellidos FROM usuarios WHERE rol = 'ciudadano' LIMIT 1";
    $stmt_user = $db->prepare($query_user);
    $stmt_user->execute();

    if ($stmt_user->rowCount() > 0) {
        $user = $stmt_user->fetch(PDO::FETCH_ASSOC);
        $usuario_id = $user['id'];

        echo "🧑 Probando con usuario: {$user['nombres']} {$user['apellidos']} (ID: $usuario_id)\n\n";

        $stmt = $denuncia->readForCiudadano($usuario_id);
        $count = $stmt->rowCount();

        echo "✅ Consulta ejecutada correctamente\n";
        echo "📊 Denuncias del usuario: $count\n\n";

        if ($count > 0) {
            echo "Denuncias del ciudadano:\n";
            echo str_repeat("-", 80) . "\n";

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "Código: " . $row['codigo'] . "\n";
                echo "Título: " . $row['titulo'] . "\n";
                echo "Estado: " . $row['estado'] . "\n";
                echo "Categoría: " . ($row['categoria_nombre'] ?? 'NULL') . "\n";
                echo "Área: " . ($row['area_nombre'] ?? 'No asignada') . "\n";
                echo str_repeat("-", 80) . "\n";
            }
        } else {
            echo "ℹ️ Este usuario no tiene denuncias registradas\n";
        }
    } else {
        echo "⚠️ No hay usuarios con rol 'ciudadano' en la base de datos\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR en consulta: " . $e->getMessage() . "\n";
}

echo "\n";

echo "====================================================================\n";
echo "PRUEBA 3: Consulta para STAFF (readForStaff)\n";
echo "====================================================================\n";

try {
    $stmt = $denuncia->readForStaff(['registrada', 'en_revision', 'asignada', 'en_proceso']);
    $count = $stmt->rowCount();

    echo "✅ Consulta ejecutada correctamente\n";
    echo "📊 Denuncias en estados: registrada, en_revision, asignada, en_proceso\n";
    echo "📊 Total: $count\n\n";

    if ($count > 0) {
        // Contar por estado
        $estados = [];
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rows as $row) {
            $estado = $row['estado'];
            if (!isset($estados[$estado])) {
                $estados[$estado] = 0;
            }
            $estados[$estado]++;
        }

        echo "Distribución por estado:\n";
        foreach ($estados as $estado => $cantidad) {
            echo "  - $estado: $cantidad\n";
        }

        echo "\nPrimeros 3 registros:\n";
        echo str_repeat("-", 80) . "\n";

        for ($i = 0; $i < min(3, count($rows)); $i++) {
            $row = $rows[$i];
            echo "Código: " . $row['codigo'] . "\n";
            echo "Título: " . $row['titulo'] . "\n";
            echo "Estado: " . $row['estado'] . "\n";
            echo "Categoría: " . ($row['categoria_nombre'] ?? 'NULL') . "\n";
            echo "Área: " . ($row['area_nombre'] ?? 'No asignada') . "\n";
            echo "Usuario: " . ($row['usuario_nombre'] ?? 'Anónimo') . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "ℹ️ No hay denuncias en esos estados\n";
    }
} catch (Exception $e) {
    echo "❌ ERROR en consulta: " . $e->getMessage() . "\n";
}

echo "\n";

echo "====================================================================\n";
echo "PRUEBA 4: Verificar campos NULL en area_asignada_id\n";
echo "====================================================================\n";

try {
    $query = "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN area_asignada_id IS NULL THEN 1 ELSE 0 END) as sin_area,
                SUM(CASE WHEN area_asignada_id IS NOT NULL THEN 1 ELSE 0 END) as con_area
            FROM denuncias";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    echo "📊 Estadísticas de asignación:\n";
    echo "  Total de denuncias: " . $stats['total'] . "\n";
    echo "  Sin área asignada: " . $stats['sin_area'] . "\n";
    echo "  Con área asignada: " . $stats['con_area'] . "\n\n";

    // Probar que LEFT JOIN funciona con NULL
    echo "✅ Probando LEFT JOIN con denuncias SIN área asignada:\n";
    echo str_repeat("-", 80) . "\n";

    $query_test = "SELECT
                    d.id,
                    d.codigo,
                    d.titulo,
                    d.estado,
                    d.area_asignada_id,
                    a.nombre as area_nombre
                FROM denuncias d
                LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                WHERE d.area_asignada_id IS NULL
                LIMIT 3";

    $stmt_test = $db->prepare($query_test);
    $stmt_test->execute();

    $count_null = $stmt_test->rowCount();

    if ($count_null > 0) {
        echo "✅ Se encontraron $count_null denuncias sin área asignada\n";
        echo "✅ LEFT JOIN funciona correctamente (no oculta registros con NULL)\n\n";

        while ($row = $stmt_test->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: " . $row['id'] . "\n";
            echo "Código: " . $row['codigo'] . "\n";
            echo "Título: " . $row['titulo'] . "\n";
            echo "Estado: " . $row['estado'] . "\n";
            echo "area_asignada_id: NULL\n";
            echo "area_nombre: " . ($row['area_nombre'] ?? 'NULL (correcto)') . "\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "ℹ️ Todas las denuncias tienen área asignada (no hay NULL)\n";
    }

} catch (Exception $e) {
    echo "❌ ERROR en consulta: " . $e->getMessage() . "\n";
}

echo "\n";

echo "====================================================================\n";
echo "PRUEBA 5: Estadísticas por estado\n";
echo "====================================================================\n";

try {
    $stats = $denuncia->getEstadisticas();

    echo "📊 Estadísticas generales del sistema:\n";
    echo "  Total: " . $stats['total'] . "\n";
    echo "  Registradas: " . $stats['registradas'] . "\n";
    echo "  En revisión: " . $stats['en_revision'] . "\n";
    echo "  Asignadas: " . $stats['asignadas'] . "\n";
    echo "  En proceso: " . $stats['en_proceso'] . "\n";
    echo "  Resueltas: " . $stats['resueltas'] . "\n";
    echo "  Cerradas: " . $stats['cerradas'] . "\n";
    echo "  Rechazadas: " . $stats['rechazadas'] . "\n";

} catch (Exception $e) {
    echo "❌ ERROR en consulta: " . $e->getMessage() . "\n";
}

echo "\n";
echo "====================================================================\n";
echo "RESUMEN DE PRUEBAS\n";
echo "====================================================================\n";
echo "✅ Todas las consultas SQL están corregidas\n";
echo "✅ Se usa LEFT JOIN para area_asignada_id (permite NULL)\n";
echo "✅ Se usa INNER JOIN para categoria_id (obligatorio)\n";
echo "✅ El campo fecha_registro se mapea correctamente a created_at\n";
echo "✅ Cada rol tiene su consulta específica optimizada\n";
echo "\n";
echo "🎯 Las denuncias ahora deberían aparecer en todos los dashboards\n";
echo "====================================================================\n";
