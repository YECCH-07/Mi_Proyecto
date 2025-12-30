<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Area.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate area object
$area = new Area($db);

// Read all areas
$stmt = $area->read();
$num = $stmt->rowCount();

if ($num > 0) {
    $areas_arr = array();
    $areas_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $area_item = array(
            "id" => $id,
            "nombre" => $nombre,
            "responsable" => $responsable,
            "email_contacto" => $email_contacto
        );
        array_push($areas_arr["records"], $area_item);
    }
    http_response_code(200);
    echo json_encode($areas_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No areas found."));
}
