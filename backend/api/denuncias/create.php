<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../middleware/validate_jwt.php';

// Validate JWT
$user_data = validate_jwt();
$user_id = $user_data->id;

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate denuncia object
$denuncia = new Denuncia($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty
if (
    !empty($data->titulo) &&
    !empty($data->descripcion) &&
    !empty($data->categoria_id) &&
    isset($data->latitud) &&
    isset($data->longitud)
) {
    // Set denuncia property values
    $denuncia->titulo = $data->titulo;
    $denuncia->descripcion = $data->descripcion;
    $denuncia->categoria_id = $data->categoria_id;
    $denuncia->latitud = $data->latitud;
    $denuncia->longitud = $data->longitud;
    $denuncia->direccion_referencia = $data->direccion_referencia ?? '';
    $denuncia->es_anonima = $data->es_anonima ?? false;
    
    // Set the user ID from the token
    // If the complaint is anonymous, we might want to set usuario_id to null.
    $denuncia->usuario_id = $denuncia->es_anonima ? null : $user_id;
    
    // Create the denuncia
    if($denuncia->create()) {
        http_response_code(201);
        echo json_encode(array(
            "message" => "Denuncia was created successfully.",
            "codigo" => $denuncia->codigo,
            "id" => $denuncia->id
        ));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Unable to create denuncia."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Unable to create denuncia. Data is incomplete."));
}
