<?php
/**
 * SOLUCIÓN ALTERNATIVA 3: Login con Cookie
 *
 * En lugar de enviar el JWT al frontend para que lo almacene en localStorage,
 * lo enviamos como una cookie HTTP-only, lo que evita:
 * 1. El problema del header Authorization en Apache
 * 2. Vulnerabilidades XSS (JavaScript no puede acceder a la cookie)
 */

header("Access-Control-Allow-Origin: http://localhost:5173");
header("Access-Control-Allow-Credentials: true");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

include_once '../../config/database.php';
include_once '../../models/Usuario.php';

require_once __DIR__ . '/../../vendor/autoload.php';
use \Firebase\JWT\JWT;

$database = new Database();
$db = $database->getConnection();

$usuario = new Usuario($db);

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {
    $usuario->email = $data->email;

    if ($usuario->login()) {
        if (password_verify($data->password, $usuario->password)) {
            $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
            $issuer = "http://localhost";
            $audience = "http://localhost";
            $issued_at = time();
            $expiration_time = $issued_at + (60 * 60 * 24); // 24 horas

            $token = array(
                "iss" => $issuer,
                "aud" => $audience,
                "iat" => $issued_at,
                "nbf" => $issued_at,
                "exp" => $expiration_time,
                "data" => array(
                    "id" => $usuario->id,
                    "nombres" => $usuario->nombres,
                    "apellidos" => $usuario->apellidos,
                    "email" => $usuario->email,
                    "rol" => $usuario->rol
                )
            );

            $jwt = JWT::encode($token, $secret_key, 'HS256');

            // Establecer cookie HTTP-only
            setcookie(
                'jwt_token',           // Nombre
                $jwt,                  // Valor
                [
                    'expires' => $expiration_time,
                    'path' => '/',
                    'domain' => 'localhost',
                    'secure' => false,  // true solo para HTTPS
                    'httponly' => true, // No accesible desde JavaScript
                    'samesite' => 'Lax' // Protección CSRF
                ]
            );

            http_response_code(200);
            echo json_encode(array(
                "message" => "Login successful",
                "user" => array(
                    "id" => $usuario->id,
                    "nombres" => $usuario->nombres,
                    "apellidos" => $usuario->apellidos,
                    "email" => $usuario->email,
                    "rol" => $usuario->rol
                )
                // Nota: NO enviamos el JWT en el body, está en la cookie
            ));
        } else {
            http_response_code(401);
            echo json_encode(array("message" => "Login failed. Incorrect password."));
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login failed. User not found."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Unable to login. Data is incomplete."));
}
