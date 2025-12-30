<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Validate JWT
$user_data = validate_jwt();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// ============================================================================
// VALIDACIÓN DE ROL: Solo admin puede eliminar denuncias
// ============================================================================
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode(array(
        "message" => "Access denied. Only administrators can delete denuncias."
    ));
    exit();
}

// Instantiate denuncia object
$denuncia = new Denuncia($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty and ID is present
if (!empty($data->id)) {
    $denuncia->id = $data->id;

    // Here you could add a check to ensure the user has permission to delete.
    // For example, check if the user is an 'admin' or if they own the denuncia.

    // Delete the denuncia
    if($denuncia->delete()) {
        http_response_code(200);
        echo json_encode(array("message" => "Denuncia was deleted successfully."));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Unable to delete denuncia."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Unable to delete denuncia. ID is missing."));
}
