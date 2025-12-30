<?php
/**
 * API Endpoint: Read Denuncias
 *
 * Optimizado para devolver los datos completos con LEFT JOIN
 * Filtrado por rol de usuario
 */

// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate denuncia object
$denuncia = new Denuncia($db);

// Validate JWT and get user data
$user_data = validate_jwt(); // This will exit if token is invalid

// Check if ID or Codigo is set for reading a single record
$denuncia->id = isset($_GET['id']) ? $_GET['id'] : null;
$denuncia->codigo = isset($_GET['codigo']) ? $_GET['codigo'] : null;

if ($denuncia->id || $denuncia->codigo) {
    // ====================================================================
    // LEER UNA SOLA DENUNCIA
    // ====================================================================

    if ($denuncia->id) {
        $denuncia->readOne(true);
    } else {
        $denuncia->readByCodigo(true);
    }

    if ($denuncia->titulo != null) {
        // If user is ciudadano, verify they own this denuncia
        if ($user_data->rol === 'ciudadano' && $denuncia->usuario_id != $user_data->id) {
            http_response_code(403);
            echo json_encode(array("message" => "Access denied. You can only view your own denuncias."));
            exit();
        }

        // Create array from object properties
        $denuncia_arr = array(
            "id" => $denuncia->id,
            "codigo" => $denuncia->codigo,
            "titulo" => $denuncia->titulo,
            "descripcion" => $denuncia->descripcion,
            "latitud" => $denuncia->latitud,
            "longitud" => $denuncia->longitud,
            "direccion_referencia" => $denuncia->direccion_referencia,
            "estado" => $denuncia->estado,
            "created_at" => $denuncia->created_at,
            "usuario_id" => $denuncia->usuario_id,
            "categoria_id" => $denuncia->categoria_id,
            "area_asignada_id" => $denuncia->area_asignada_id,
            "es_anonima" => $denuncia->es_anonima,
            "usuario_nombre" => $denuncia->usuario_nombre ?? 'Anónimo',
        );
        http_response_code(200);
        echo json_encode($denuncia_arr);
    } else {
        http_response_code(404);
        echo json_encode(array("message" => "Denuncia not found."));
    }

} else {
    // ====================================================================
    // LEER MÚLTIPLES DENUNCIAS (según rol)
    // ====================================================================

    // Ejecutar la consulta según el rol del usuario
    if ($user_data->rol === 'ciudadano') {
        // CIUDADANO: Solo sus propias denuncias
        $stmt = $denuncia->readForCiudadano($user_data->id);
    } elseif ($user_data->rol === 'admin') {
        // ADMINISTRADOR: Todas las denuncias
        $stmt = $denuncia->readForAdmin();
    } elseif ($user_data->rol === 'supervisor') {
        // SUPERVISOR: Todas las denuncias en estados específicos
        $stmt = $denuncia->readForStaff(['registrada', 'en_revision', 'asignada', 'en_proceso']);
    } elseif ($user_data->rol === 'operador') {
        // OPERADOR: Solo denuncias de su área
        $filter = filterDenunciasByArea($user_data);

        if ($filter['filter_type'] === 'blocked') {
            http_response_code(403);
            echo json_encode(array("message" => $filter['error_message']));
            exit();
        }

        // Query manual con filtro de área
        $query = "SELECT
            d.id,
            d.codigo,
            d.titulo,
            d.descripcion,
            d.estado,
            d.created_at as fecha_registro,
            d.latitud,
            d.longitud,
            d.direccion_referencia,
            d.usuario_id,
            d.categoria_id,
            d.area_asignada_id,
            d.es_anonima,
            CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
            u.email as usuario_email,
            u.telefono as usuario_telefono,
            c.nombre as categoria_nombre,
            c.icono as categoria_icono,
            a.nombre as area_nombre,
            a.responsable as area_responsable
        FROM denuncias d
        LEFT JOIN usuarios u ON d.usuario_id = u.id
        LEFT JOIN categorias c ON d.categoria_id = c.id
        LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
        WHERE {$filter['where_clause']}
          AND d.estado IN ('registrada', 'en_revision', 'asignada', 'en_proceso')
        ORDER BY d.created_at DESC";

        $stmt = $db->prepare($query);
        $stmt->execute();
    } else {
        // Rol no reconocido
        http_response_code(403);
        echo json_encode(array("message" => "Access denied. Unknown role."));
        exit();
    }

    $num = $stmt->rowCount();

    if ($num > 0) {
        $denuncias_arr = array();
        $denuncias_arr["records"] = array();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // Extraer datos del row
            extract($row);

            // Construir el objeto denuncia
            $denuncia_item = array(
                "id" => $id,
                "codigo" => $codigo ?? null,
                "titulo" => $titulo ?? null,
                "descripcion" => $descripcion ?? null,
                "estado" => $estado ?? null,
                "fecha_registro" => $fecha_registro ?? null, // Alias de created_at
                "latitud" => $latitud ?? null,
                "longitud" => $longitud ?? null,
                "direccion_referencia" => $direccion_referencia ?? null,
                "usuario_id" => $usuario_id ?? null,
                "categoria_id" => $categoria_id ?? null,
                "area_asignada_id" => $area_asignada_id ?? null,
                "es_anonima" => isset($es_anonima) ? (bool)$es_anonima : false,

                // Campos JOIN - Información relacionada
                "usuario_nombre" => $usuario_nombre ?? 'Anónimo',
                "usuario_email" => $usuario_email ?? null,
                "usuario_telefono" => $usuario_telefono ?? null,
                "categoria_nombre" => $categoria_nombre ?? 'Sin categoría',
                "categoria_icono" => $categoria_icono ?? null,
                "area_nombre" => $area_nombre ?? 'No asignada',
                "area_responsable" => $area_responsable ?? null,
            );

            array_push($denuncias_arr["records"], $denuncia_item);
        }

        http_response_code(200);
        echo json_encode($denuncias_arr);
    } else {
        // No se encontraron denuncias
        http_response_code(200);
        echo json_encode(array(
            "records" => array(),
            "message" => "No denuncias found."
        ));
    }
}
