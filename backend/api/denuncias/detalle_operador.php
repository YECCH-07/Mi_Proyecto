<?php
/**
 * API Endpoint: Detalle de Denuncia para Operador
 *
 * Retorna información completa de la denuncia incluyendo:
 * - Datos de la denuncia
 * - Información del ciudadano (nombre, DNI, email, teléfono)
 * - Categoría
 * - Área asignada
 * - Evidencias (imágenes/videos)
 * - Historial de seguimiento
 * - Coordenadas GPS para Google Maps
 */

// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and model files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Validate JWT and get user data
$user_data = validate_jwt();

// Verificar que el usuario sea operador, supervisor o admin
$allowed_roles = ['operador', 'supervisor', 'admin'];
if (!in_array($user_data->rol, $allowed_roles)) {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Only operators, supervisors and admins can access this."));
    exit();
}

// Obtener ID de la denuncia
$denuncia_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($denuncia_id <= 0) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid denuncia ID"));
    exit();
}

// ============================================================================
// VALIDACIÓN DE ÁREA PARA OPERADORES
// ============================================================================
if ($user_data->rol === 'operador') {
    // Obtener filtro por área del operador
    $filter = filterDenunciasByArea($user_data);

    // Si el operador no tiene área asignada, bloquear
    if ($filter['filter_type'] === 'blocked') {
        http_response_code(403);
        echo json_encode(array(
            "message" => $filter['error_message']
        ));
        exit();
    }

    // Verificar que la denuncia pertenece al área del operador
    $query_check_area = "SELECT area_asignada_id FROM denuncias WHERE id = :denuncia_id";
    $stmt_check = $db->prepare($query_check_area);
    $stmt_check->bindParam(':denuncia_id', $denuncia_id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Denuncia not found"));
        exit();
    }

    $check_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    $denuncia_area_id = $check_result['area_asignada_id'];

    // Comparar con el área del operador
    if ($denuncia_area_id != $filter['area_id']) {
        http_response_code(403);
        echo json_encode(array(
            "message" => "Access denied. You can only view denuncias from your assigned area.",
            "your_area_id" => $filter['area_id'],
            "denuncia_area_id" => $denuncia_area_id
        ));
        exit();
    }
}

// ============================================================================
// CONSULTA PRINCIPAL: Obtener datos completos de la denuncia
// ============================================================================
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

            -- Categoría
            c.id as categoria_id,
            c.nombre as categoria_nombre,
            c.descripcion as categoria_descripcion,
            c.icono as categoria_icono,

            -- Área asignada
            a.id as area_id,
            a.nombre as area_nombre,
            a.responsable as area_responsable,

            -- Datos del ciudadano (puede ser NULL si es anónima)
            u.id as ciudadano_id,
            u.dni as ciudadano_dni,
            CONCAT(u.nombres, ' ', u.apellidos) as ciudadano_nombre,
            u.nombres as ciudadano_nombres,
            u.apellidos as ciudadano_apellidos,
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
$stmt->bindParam(':denuncia_id', $denuncia_id);
$stmt->execute();

if ($stmt->rowCount() == 0) {
    http_response_code(404);
    echo json_encode(array("message" => "Denuncia not found"));
    exit();
}

$denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

// ============================================================================
// CONSULTA 2: Obtener evidencias (imágenes/videos)
// ============================================================================
$query_evidencias = "SELECT
                        id,
                        denuncia_id,
                        archivo_url,
                        tipo,
                        created_at
                    FROM
                        evidencias
                    WHERE
                        denuncia_id = :denuncia_id
                    ORDER BY
                        created_at ASC";

$stmt_evidencias = $db->prepare($query_evidencias);
$stmt_evidencias->bindParam(':denuncia_id', $denuncia_id);
$stmt_evidencias->execute();

$evidencias = array();
while ($row = $stmt_evidencias->fetch(PDO::FETCH_ASSOC)) {
    array_push($evidencias, array(
        "id" => $row['id'],
        "archivo_url" => $row['archivo_url'],
        "tipo" => $row['tipo'], // 'imagen' o 'video'
        "created_at" => $row['created_at']
    ));
}

// ============================================================================
// CONSULTA 3: Obtener historial de seguimiento
// ============================================================================
$query_seguimiento = "SELECT
                        s.id,
                        s.comentario,
                        s.estado_anterior,
                        s.estado_nuevo,
                        s.created_at,
                        CONCAT(u.nombres, ' ', u.apellidos) as responsable_nombre,
                        u.rol as responsable_rol
                    FROM
                        seguimiento s
                        LEFT JOIN usuarios u ON s.usuario_id = u.id
                    WHERE
                        s.denuncia_id = :denuncia_id
                    ORDER BY
                        s.created_at DESC";

$stmt_seguimiento = $db->prepare($query_seguimiento);
$stmt_seguimiento->bindParam(':denuncia_id', $denuncia_id);
$stmt_seguimiento->execute();

$seguimiento = array();
while ($row = $stmt_seguimiento->fetch(PDO::FETCH_ASSOC)) {
    array_push($seguimiento, array(
        "id" => $row['id'],
        "comentario" => $row['comentario'],
        "estado_anterior" => $row['estado_anterior'],
        "estado_nuevo" => $row['estado_nuevo'],
        "created_at" => $row['created_at'],
        "responsable_nombre" => $row['responsable_nombre'] ?? 'Sistema',
        "responsable_rol" => $row['responsable_rol']
    ));
}

// ============================================================================
// GENERAR ENLACE DE GOOGLE MAPS
// ============================================================================
$google_maps_url = null;
if ($denuncia['latitud'] && $denuncia['longitud']) {
    $google_maps_url = "https://www.google.com/maps?q=" . $denuncia['latitud'] . "," . $denuncia['longitud'];
}

// ============================================================================
// PREPARAR RESPUESTA COMPLETA
// ============================================================================
$response = array(
    "success" => true,
    "data" => array(
        // Datos de la denuncia
        "denuncia" => array(
            "id" => $denuncia['id'],
            "codigo" => $denuncia['codigo'],
            "titulo" => $denuncia['titulo'],
            "descripcion" => $denuncia['descripcion'],
            "estado" => $denuncia['estado'],
            "prioridad" => $denuncia['prioridad'],
            "es_anonima" => (bool)$denuncia['es_anonima'],
            "created_at" => $denuncia['created_at'],
            "updated_at" => $denuncia['updated_at']
        ),

        // Ubicación
        "ubicacion" => array(
            "latitud" => $denuncia['latitud'],
            "longitud" => $denuncia['longitud'],
            "direccion_referencia" => $denuncia['direccion_referencia'],
            "google_maps_url" => $google_maps_url
        ),

        // Categoría
        "categoria" => array(
            "id" => $denuncia['categoria_id'],
            "nombre" => $denuncia['categoria_nombre'],
            "descripcion" => $denuncia['categoria_descripcion'],
            "icono" => $denuncia['categoria_icono']
        ),

        // Área asignada
        "area" => $denuncia['area_id'] ? array(
            "id" => $denuncia['area_id'],
            "nombre" => $denuncia['area_nombre'],
            "responsable" => $denuncia['area_responsable']
        ) : null,

        // Datos del ciudadano
        "ciudadano" => !$denuncia['es_anonima'] && $denuncia['ciudadano_id'] ? array(
            "id" => $denuncia['ciudadano_id'],
            "dni" => $denuncia['ciudadano_dni'],
            "nombre_completo" => $denuncia['ciudadano_nombre'],
            "nombres" => $denuncia['ciudadano_nombres'],
            "apellidos" => $denuncia['ciudadano_apellidos'],
            "email" => $denuncia['ciudadano_email'],
            "telefono" => $denuncia['ciudadano_telefono']
        ) : array(
            "nombre_completo" => "Anónimo",
            "dni" => "N/A",
            "email" => null,
            "telefono" => null
        ),

        // Evidencias
        "evidencias" => $evidencias,

        // Historial de seguimiento
        "seguimiento" => $seguimiento
    )
);

// Retornar respuesta
http_response_code(200);
echo json_encode($response);
