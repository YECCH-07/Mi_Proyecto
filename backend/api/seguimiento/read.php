<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Seguimiento.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate seguimiento object
$seguimiento = new Seguimiento($db);

// Get denuncia_id from query string
$seguimiento->denuncia_id = isset($_GET['denuncia_id']) ? $_GET['denuncia_id'] : die();

// Read all seguimiento records for the denuncia
$stmt = $seguimiento->readByDenuncia();
$num = $stmt->rowCount();

if ($num > 0) {
    $seguimientos_arr = array();
    $seguimientos_arr["records"] = array();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        $seguimiento_item = array(
            "id" => $id,
            "estado_anterior" => $estado_anterior,
            "estado_nuevo" => $estado_nuevo,
            "comentario" => $comentario,
            "created_at" => $created_at,
            "usuario_nombre" => $nombres . " " . $apellidos
        );
        array_push($seguimientos_arr["records"], $seguimiento_item);
    }
    http_response_code(200);
    echo json_encode($seguimientos_arr);
} else {
    http_response_code(404);
    echo json_encode(array("message" => "No tracking history found for this denuncia."));
}
