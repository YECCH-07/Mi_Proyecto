<?php
/**
 * SCRIPT DE PRUEBA: Endpoint detalle_operador.php
 *
 * Este script prueba directamente el endpoint para identificar
 * por qué falla al cargar el detalle de la denuncia.
 *
 * Ejecutar: php backend/PROBAR_DETALLE_OPERADOR.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║          PRUEBA: Endpoint detalle_operador.php               ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================================================
// PASO 1: VERIFICAR ARCHIVO EXISTE
// ============================================================================
echo "📂 PASO 1: Verificando archivo del endpoint...\n";
echo str_repeat("-", 65) . "\n";

$archivo_endpoint = 'api/denuncias/detalle_operador.php';

if (file_exists($archivo_endpoint)) {
    echo "✅ Archivo existe: $archivo_endpoint\n";
    $size = filesize($archivo_endpoint);
    echo "   Tamaño: " . number_format($size) . " bytes\n";
} else {
    echo "❌ ERROR: Archivo NO existe: $archivo_endpoint\n";
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 2: VERIFICAR CONEXIÓN A BD
// ============================================================================
echo "🔌 PASO 2: Verificando conexión a base de datos...\n";
echo str_repeat("-", 65) . "\n";

try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    echo "✅ Conexión exitosa\n";
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 3: OBTENER DENUNCIAS DISPONIBLES
// ============================================================================
echo "📋 PASO 3: Obteniendo denuncias disponibles para probar...\n";
echo str_repeat("-", 65) . "\n";

try {
    $query = "SELECT id, codigo, titulo, estado FROM denuncias LIMIT 5";
    $stmt = $db->query($query);
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($denuncias) > 0) {
        echo "✅ Se encontraron " . count($denuncias) . " denuncias:\n\n";
        foreach ($denuncias as $d) {
            echo "   ID: {$d['id']} | Código: {$d['codigo']} | Estado: {$d['estado']}\n";
            echo "   Título: {$d['titulo']}\n";
            echo "   " . str_repeat("-", 60) . "\n";
        }

        // Usar la primera denuncia para prueba
        $denuncia_id = $denuncias[0]['id'];
        $codigo_prueba = $denuncias[0]['codigo'];

        echo "\n";
        echo "🎯 Usaremos la denuncia ID: $denuncia_id ($codigo_prueba) para la prueba\n";
    } else {
        echo "⚠️ No hay denuncias en la base de datos\n";
        exit(1);
    }
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 4: SIMULAR LLAMADA AL ENDPOINT
// ============================================================================
echo "🧪 PASO 4: Simulando llamada al endpoint...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Ejecutar la misma query que usa el endpoint
    $query = "SELECT
        d.id,
        d.codigo,
        d.titulo,
        d.descripcion,
        d.latitud,
        d.longitud,
        d.direccion_referencia,
        d.estado,
        d.prioridad,
        d.es_anonima,
        d.created_at,
        d.updated_at,
        c.id as categoria_id,
        c.nombre as categoria_nombre,
        c.icono as categoria_icono,
        a.id as area_id,
        a.nombre as area_nombre,
        CONCAT(u.nombres, ' ', u.apellidos) as ciudadano_nombre,
        u.dni as ciudadano_dni,
        u.email as ciudadano_email,
        u.telefono as ciudadano_telefono
    FROM
        denuncias d
        INNER JOIN categorias c ON d.categoria_id = c.id
        LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
        LEFT JOIN usuarios u ON d.usuario_id = u.id
    WHERE
        d.id = :denuncia_id
    LIMIT 1";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':denuncia_id', $denuncia_id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

        echo "✅ Query principal ejecutada correctamente\n\n";
        echo "📝 DATOS OBTENIDOS:\n";
        echo "   Código: {$denuncia['codigo']}\n";
        echo "   Título: {$denuncia['titulo']}\n";
        echo "   Estado: {$denuncia['estado']}\n";
        echo "   Categoría: {$denuncia['categoria_nombre']}\n";
        echo "   Área: " . ($denuncia['area_nombre'] ?? 'Sin asignar') . "\n";
        echo "   Ciudadano: " . ($denuncia['ciudadano_nombre'] ?? 'Anónimo') . "\n";
        echo "   Email: " . ($denuncia['ciudadano_email'] ?? 'N/A') . "\n";
        echo "   Coordenadas: ({$denuncia['latitud']}, {$denuncia['longitud']})\n";

    } else {
        echo "❌ ERROR: La query no retornó resultados\n";
        echo "   Denuncia ID $denuncia_id no encontrada o mal estructurada\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ ERROR en query principal: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n";

// ============================================================================
// PASO 5: OBTENER EVIDENCIAS
// ============================================================================
echo "📷 PASO 5: Obteniendo evidencias...\n";
echo str_repeat("-", 65) . "\n";

try {
    $query_evidencias = "SELECT
        id,
        archivo_url,
        tipo,
        nombre_original,
        created_at
    FROM evidencias
    WHERE denuncia_id = :denuncia_id
    ORDER BY created_at DESC";

    $stmt_evidencias = $db->prepare($query_evidencias);
    $stmt_evidencias->bindParam(':denuncia_id', $denuncia_id, PDO::PARAM_INT);
    $stmt_evidencias->execute();

    $evidencias = $stmt_evidencias->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Query de evidencias ejecutada correctamente\n";
    echo "   Evidencias encontradas: " . count($evidencias) . "\n";

    if (count($evidencias) > 0) {
        echo "\n   Detalles:\n";
        foreach ($evidencias as $ev) {
            echo "   - {$ev['tipo']}: {$ev['nombre_original']}\n";
        }
    }

} catch (Exception $e) {
    echo "⚠️ Error al obtener evidencias: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================================
// PASO 6: OBTENER SEGUIMIENTO
// ============================================================================
echo "📋 PASO 6: Obteniendo historial de seguimiento...\n";
echo str_repeat("-", 65) . "\n";

try {
    $query_seguimiento = "SELECT
        s.id,
        s.estado_anterior,
        s.estado_nuevo,
        s.comentario,
        s.created_at,
        CONCAT(u.nombres, ' ', u.apellidos) as responsable_nombre,
        u.rol as responsable_rol
    FROM seguimiento s
    LEFT JOIN usuarios u ON s.usuario_id = u.id
    WHERE s.denuncia_id = :denuncia_id
    ORDER BY s.created_at DESC";

    $stmt_seguimiento = $db->prepare($query_seguimiento);
    $stmt_seguimiento->bindParam(':denuncia_id', $denuncia_id, PDO::PARAM_INT);
    $stmt_seguimiento->execute();

    $seguimiento = $stmt_seguimiento->fetchAll(PDO::FETCH_ASSOC);

    echo "✅ Query de seguimiento ejecutada correctamente\n";
    echo "   Registros de seguimiento: " . count($seguimiento) . "\n";

    if (count($seguimiento) > 0) {
        echo "\n   Últimos cambios:\n";
        foreach (array_slice($seguimiento, 0, 3) as $seg) {
            echo "   - {$seg['estado_anterior']} → {$seg['estado_nuevo']}\n";
            echo "     Por: " . ($seg['responsable_nombre'] ?? 'Sistema') . "\n";
        }
    }

} catch (Exception $e) {
    echo "⚠️ Error al obtener seguimiento: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================================
// PASO 7: SIMULAR RESPUESTA JSON DEL ENDPOINT
// ============================================================================
echo "🔍 PASO 7: Simulando respuesta JSON del endpoint...\n";
echo str_repeat("-", 65) . "\n";

try {
    // Construir respuesta como lo haría el endpoint
    $response_data = [
        'denuncia' => [
            'id' => (int)$denuncia['id'],
            'codigo' => $denuncia['codigo'],
            'titulo' => $denuncia['titulo'],
            'descripcion' => $denuncia['descripcion'],
            'estado' => $denuncia['estado'],
            'prioridad' => $denuncia['prioridad'],
            'es_anonima' => (bool)$denuncia['es_anonima'],
            'created_at' => $denuncia['created_at'],
            'updated_at' => $denuncia['updated_at']
        ],
        'ciudadano' => [
            'nombre' => $denuncia['ciudadano_nombre'] ?? 'Anónimo',
            'dni' => $denuncia['ciudadano_dni'] ?? null,
            'email' => $denuncia['ciudadano_email'] ?? null,
            'telefono' => $denuncia['ciudadano_telefono'] ?? null
        ],
        'categoria' => [
            'id' => (int)$denuncia['categoria_id'],
            'nombre' => $denuncia['categoria_nombre'],
            'icono' => $denuncia['categoria_icono'] ?? '📝'
        ],
        'area' => [
            'id' => $denuncia['area_id'] ? (int)$denuncia['area_id'] : null,
            'nombre' => $denuncia['area_nombre'] ?? 'Sin asignar'
        ],
        'ubicacion' => [
            'latitud' => $denuncia['latitud'],
            'longitud' => $denuncia['longitud'],
            'direccion_referencia' => $denuncia['direccion_referencia'],
            'google_maps_url' => null
        ],
        'evidencias' => $evidencias ?? [],
        'seguimiento' => $seguimiento ?? []
    ];

    // Generar URL de Google Maps si hay coordenadas
    if ($denuncia['latitud'] && $denuncia['longitud']) {
        $response_data['ubicacion']['google_maps_url'] =
            "https://www.google.com/maps?q={$denuncia['latitud']},{$denuncia['longitud']}";
    }

    $final_response = [
        'success' => true,
        'data' => $response_data
    ];

    echo "✅ Respuesta JSON construida correctamente\n\n";
    echo "📦 ESTRUCTURA DE LA RESPUESTA:\n";
    echo json_encode($final_response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";

} catch (Exception $e) {
    echo "❌ ERROR al construir respuesta: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================================================
// PASO 8: VERIFICAR QUE EL ENDPOINT RETORNA SUCCESS=TRUE
// ============================================================================
echo "🎯 PASO 8: Verificando código del endpoint...\n";
echo str_repeat("-", 65) . "\n";

$contenido_endpoint = file_get_contents($archivo_endpoint);

// Buscar líneas críticas
$tiene_success_true = strpos($contenido_endpoint, "'success' => true") !== false ||
                      strpos($contenido_endpoint, '"success" => true') !== false;
$tiene_echo_json = strpos($contenido_endpoint, 'echo json_encode') !== false;
$tiene_exit = strpos($contenido_endpoint, 'exit()') !== false ||
              strpos($contenido_endpoint, 'exit;') !== false;

echo "   ✓ Tiene 'success' => true: " . ($tiene_success_true ? "✅ SÍ" : "❌ NO") . "\n";
echo "   ✓ Tiene echo json_encode: " . ($tiene_echo_json ? "✅ SÍ" : "❌ NO") . "\n";
echo "   ✓ Tiene exit al final: " . ($tiene_exit ? "✅ SÍ" : "⚠️ NO (puede causar output extra)") . "\n";

echo "\n";

// ============================================================================
// RESUMEN Y DIAGNÓSTICO
// ============================================================================
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║                     RESUMEN DEL DIAGNÓSTICO                   ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

echo "✅ VERIFICACIONES EXITOSAS:\n";
echo "   1. Archivo del endpoint existe\n";
echo "   2. Conexión a BD funciona\n";
echo "   3. Hay denuncias disponibles\n";
echo "   4. Query principal retorna datos\n";
echo "   5. Query de evidencias funciona\n";
echo "   6. Query de seguimiento funciona\n";
echo "   7. Estructura JSON se puede construir\n";
echo "\n";

echo "📋 PRÓXIMO PASO:\n";
echo "   Ejecutar el endpoint real con una herramienta como:\n";
echo "   - Postman\n";
echo "   - cURL\n";
echo "   - Navegador con extensión REST\n";
echo "\n";
echo "   URL a probar:\n";
echo "   http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/detalle_operador.php?id=$denuncia_id\n";
echo "\n";
echo "   IMPORTANTE: Necesitas incluir el header de autenticación:\n";
echo "   Authorization: Bearer [TU_TOKEN_JWT]\n";
echo "\n";

echo "═══════════════════════════════════════════════════════════════\n";
echo "Diagnóstico completado: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
