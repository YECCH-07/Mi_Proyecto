<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/User.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Instantiate user object
$user = new User($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Set user property values
$user->dni = $data->dni ?? '';
$user->nombres = $data->nombres ?? '';
$user->apellidos = $data->apellidos ?? '';
$user->email = $data->email ?? '';
$user->password = $data->password ?? ''; // Plain password
$user->telefono = $data->telefono ?? null;

// Validate input
if (
    empty($user->dni) ||
    empty($user->nombres) ||
    empty($user->apellidos) ||
    empty($user->email) ||
    empty($user->password)
) {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to register user. Data is incomplete."));
    exit();
}

// Basic email validation
if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(array("message" => "Invalid email format."));
    exit();
}

// Check if email already exists
if ($user->emailExists()) {
    http_response_code(409); // Conflict
    echo json_encode(array("message" => "The email is already registered."));
    exit();
}

// Check if DNI already exists
if ($user->dniExists()) {
    http_response_code(409); // Conflict
    echo json_encode(array("message" => "The DNI is already registered."));
    exit();
}

// Create the user
if($user->register()) {
    http_response_code(201);
    echo json_encode(array("message" => "User was successfully registered."));
} else {
    http_response_code(500);
    echo json_encode(array("message" => "Unable to register user."));
}
