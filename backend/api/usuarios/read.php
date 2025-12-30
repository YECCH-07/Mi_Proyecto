<?php
/**
 * API: Listar Usuarios
 * Solo accesible por Administrador
 *
 * GET /api/usuarios/read.php
 *
 * Query params opcionales:
 * - rol: filtrar por rol
 * - area_id: filtrar por área
 * - activo: filtrar activos/inactivos
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Solo administradores pueden ver usuarios'
    ]);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Construir query base
$query = "SELECT
    u.id,
    u.dni,
    u.nombres,
    u.apellidos,
    u.email,
    u.telefono,
    u.rol,
    u.area_id,
    u.activo,
    u.verificado,
    u.created_at,
    a.nombre as area_nombre,
    a.responsable as area_responsable
FROM usuarios u
LEFT JOIN areas_municipales a ON u.area_id = a.id
WHERE 1=1";

// Filtros opcionales
$params = [];

if (isset($_GET['rol']) && !empty($_GET['rol'])) {
    $query .= " AND u.rol = :rol";
    $params[':rol'] = $_GET['rol'];
}

if (isset($_GET['area_id']) && !empty($_GET['area_id'])) {
    $query .= " AND u.area_id = :area_id";
    $params[':area_id'] = intval($_GET['area_id']);
}

if (isset($_GET['activo'])) {
    $query .= " AND u.activo = :activo";
    $params[':activo'] = $_GET['activo'] == '1' ? 1 : 0;
}

// Búsqueda por texto (nombre, email, DNI)
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $query .= " AND (u.nombres LIKE :search OR u.apellidos LIKE :search OR u.email LIKE :search OR u.dni LIKE :search)";
    $params[':search'] = $search;
}

$query .= " ORDER BY u.created_at DESC";

try {
    $stmt = $db->prepare($query);

    // Bind params
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    $stmt->execute();
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ocultar password_hash en la respuesta
    foreach ($usuarios as &$usuario) {
        unset($usuario['password_hash']);

        // Formatear nombre completo
        $usuario['nombre_completo'] = $usuario['nombres'] . ' ' . $usuario['apellidos'];
    }

    // Obtener estadísticas
    $stats = [
        'total' => count($usuarios),
        'por_rol' => []
    ];

    // Contar por rol
    foreach ($usuarios as $usuario) {
        $rol = $usuario['rol'];
        if (!isset($stats['por_rol'][$rol])) {
            $stats['por_rol'][$rol] = 0;
        }
        $stats['por_rol'][$rol]++;
    }

    http_response_code(200);
    echo json_encode([
        'success' => true,
        'count' => count($usuarios),
        'stats' => $stats,
        'data' => $usuarios
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener usuarios',
        'error' => $e->getMessage()
    ]);
}
?>
