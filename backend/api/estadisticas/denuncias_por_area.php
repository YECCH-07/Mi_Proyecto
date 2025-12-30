<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';

// Validate JWT and user role
$user_data = validate_jwt(['admin', 'supervisor', 'operador']); // Allow operators to view stats

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Query to get denuncias count by area
$query = "SELECT
            a.nombre as area_nombre, COUNT(d.id) as count
        FROM
            denuncias d
            LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
        WHERE
            d.area_asignada_id IS NOT NULL
        GROUP BY
            a.nombre
        ORDER BY
            count DESC";

$stmt = $db->prepare($query);
$stmt->execute();

$num = $stmt->rowCount();

if ($num > 0) {
    $estadisticas_arr = array();
    $estadisticas_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $estadistica_item = array(
            "area_nombre" => $area_nombre,
            "count" => $count
        );
        array_push($estadisticas_arr["records"], $estadistica_item);
    }
    http_response_code(200);
    echo json_encode($estadisticas_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No statistics found for denuncias by area."));
}
