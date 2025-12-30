<?php
/**
 * API: Obtener Coordenadas para Heatmap
 * GET /api/denuncias/locations.php
 *
 * Retorna coordenadas de denuncias para Google Maps Heatmap
 * Filtrado por rol:
 * - Admin/Supervisor: Todas las denuncias
 * - Operador: Solo de su área
 * - Ciudadano: Solo las suyas
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Validar JWT
$user_data = validate_jwt();

// Database
$database = new Database();
$db = $database->getConnection();

// Aplicar filtro por área
$filter = filterDenunciasByArea($user_data);

if ($filter['filter_type'] === 'blocked') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => $filter['error_message']
    ]);
    exit();
}

// Query con filtro
$query = "SELECT
    d.id,
    d.codigo,
    d.latitud,
    d.longitud,
    d.estado,
    d.created_at,
    c.nombre as categoria,
    c.icono as categoria_icono,
    a.nombre as area
FROM denuncias d
INNER JOIN categorias c ON d.categoria_id = c.id
LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
WHERE {$filter['where_clause']}
  AND d.latitud IS NOT NULL
  AND d.longitud IS NOT NULL";

// Filtro opcional por estado
if (isset($_GET['estado']) && !empty($_GET['estado'])) {
    $query .= " AND d.estado = :estado";
}

// Filtro opcional por fecha
if (isset($_GET['fecha_desde'])) {
    $query .= " AND d.created_at >= :fecha_desde";
}
if (isset($_GET['fecha_hasta'])) {
    $query .= " AND d.created_at <= :fecha_hasta";
}

$query .= " ORDER BY d.created_at DESC";

try {
    $stmt = $db->prepare($query);

    // Bind parameters opcionales
    if (isset($_GET['estado']) && !empty($_GET['estado'])) {
        $stmt->bindValue(':estado', $_GET['estado']);
    }
    if (isset($_GET['fecha_desde'])) {
        $stmt->bindValue(':fecha_desde', $_GET['fecha_desde']);
    }
    if (isset($_GET['fecha_hasta'])) {
        $stmt->bindValue(':fecha_hasta', $_GET['fecha_hasta']);
    }

    $stmt->execute();
    $denuncias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Formatear coordenadas para Google Maps
    $locations = [];
    foreach ($denuncias as $d) {
        $locations[] = [
            'id' => $d['id'],
            'codigo' => $d['codigo'],
            'lat' => floatval($d['latitud']),
            'lng' => floatval($d['longitud']),
            'estado' => $d['estado'],
            'categoria' => $d['categoria'],
            'categoria_icono' => $d['categoria_icono'],
            'area' => $d['area'],
            'fecha' => $d['created_at'],
            // Peso para el heatmap (denuncias más recientes pesan más)
            'weight' => calculateWeight($d['estado'], $d['created_at'])
        ];
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($locations),
        'data' => $locations,
        'filter_applied' => $filter['filter_type'],
        'area_id' => $filter['area_id'] ?? null
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener ubicaciones',
        'error' => $e->getMessage()
    ]);
}

/**
 * Calcular peso para el heatmap
 * Estados más críticos y denuncias más recientes tienen mayor peso
 */
function calculateWeight($estado, $fecha_creacion) {
    // Peso base según estado
    $pesos_estado = [
        'registrada' => 1.0,
        'en_revision' => 1.5,
        'asignada' => 2.0,
        'en_proceso' => 2.5,
        'resuelta' => 0.5,
        'cerrada' => 0.3,
        'rechazada' => 0.2
    ];

    $peso_base = $pesos_estado[$estado] ?? 1.0;

    // Factor de tiempo (denuncias más recientes pesan más)
    $dias_transcurridos = (time() - strtotime($fecha_creacion)) / 86400;

    if ($dias_transcurridos < 7) {
        $factor_tiempo = 1.5; // Última semana
    } elseif ($dias_transcurridos < 30) {
        $factor_tiempo = 1.2; // Último mes
    } elseif ($dias_transcurridos < 90) {
        $factor_tiempo = 1.0; // Últimos 3 meses
    } else {
        $factor_tiempo = 0.7; // Más antiguas
    }

    return $peso_base * $factor_tiempo;
}
?>
