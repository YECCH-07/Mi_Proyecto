<?php
/**
 * API Endpoint: Login
 * POST /api/auth/login.php
 *
 * Autentica usuario y retorna JWT
 */

// Activar reporte de errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 0); // No mostrar en output, solo loguear
ini_set('log_errors', 1);

// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/User.php';

// Required for JWT
require __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

// Load environment variables
function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        if(strpos($line, '=') === false) {
            continue;
        }
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

loadEnv(__DIR__ . '/../../.env');

try {

    // Get database connection
    $database = new Database();
    $db = $database->getConnection();

    // Check database connection
    if (!$db) {
        http_response_code(500);
        echo json_encode(array(
            "success" => false,
            "message" => "Database connection failed"
        ));
        exit();
    }

    // Instantiate user object
    $user = new User($db);

    // Get posted data
    $data = json_decode(file_get_contents("php://input"));

    // Validate input
    if (empty($data->email) || empty($data->password)) {
        http_response_code(400);
        echo json_encode(array(
            "success" => false,
            "message" => "Email and password are required"
        ));
        exit();
    }

    // Get email and password
    $user->email = $data->email;
    $password = $data->password;

    // Check if user is active
    $check_active_query = "SELECT activo FROM usuarios WHERE email = ? LIMIT 1";
    $stmt_active = $db->prepare($check_active_query);
    $stmt_active->bindParam(1, $user->email);
    $stmt_active->execute();

    if ($stmt_active->rowCount() > 0) {
        $row_active = $stmt_active->fetch(PDO::FETCH_ASSOC);
        if (!$row_active['activo']) {
            http_response_code(403);
            echo json_encode(array(
                "success" => false,
                "message" => "Account is deactivated. Please contact administrator."
            ));
            exit();
        }
    }

    // Check if email exists and password is correct
    if ($user->emailExists() && password_verify($password, $user->password)) {

        // The secret key MUST be defined in the environment variables
        $secret_key = getenv('JWT_SECRET_KEY');

        if (!$secret_key) {
            http_response_code(500);
            echo json_encode(array(
                "success" => false,
                "message" => "Internal server error. JWT secret key is not configured."
            ));
            exit();
        }

        $issuer_claim = "http://localhost/DENUNCIA%20CIUDADANA";
        $audience_claim = "THE_AUDIENCE";
        $issuedat_claim = time();
        $notbefore_claim = $issuedat_claim;
        $expire_claim = $issuedat_claim + 3600; // 1 hour

        $token = array(
            "iss" => $issuer_claim,
            "aud" => $audience_claim,
            "iat" => $issuedat_claim,
            "nbf" => $notbefore_claim,
            "exp" => $expire_claim,
            "data" => array(
                "id" => $user->id,
                "nombres" => $user->nombres,
                "apellidos" => $user->apellidos,
                "email" => $user->email,
                "rol" => $user->rol
            )
        );

        http_response_code(200);

        // Generate jwt
        $jwt = JWT::encode($token, $secret_key, 'HS256');

        echo json_encode(array(
            "success" => true,
            "message" => "Successful login",
            "jwt" => $jwt,
            "user" => array(
                "id" => $user->id,
                "nombres" => $user->nombres,
                "apellidos" => $user->apellidos,
                "email" => $user->email,
                "rol" => $user->rol
            )
        ));

    } else {
        http_response_code(401);
        echo json_encode(array(
            "success" => false,
            "message" => "Login failed. Invalid credentials."
        ));
    }

} catch (Exception $e) {
    // Log the error
    error_log("Login Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Internal server error",
        "error" => $e->getMessage(), // En producción, quitar esto
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ));
}
?>
