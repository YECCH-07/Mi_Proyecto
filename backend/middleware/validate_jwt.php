<?php
// Required for JWT
require_once __DIR__ . '/../vendor/autoload.php';
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

// Load .env file once when this middleware is included
loadEnv(__DIR__ . '/../.env');

function validate_jwt($required_roles = []) {
    $jwt = null;

    // Intentar obtener el header Authorization de múltiples fuentes
    $authHeader = null;

    // Método 1: HTTP_AUTHORIZATION (después de .htaccess)
    if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    }
    // Método 2: REDIRECT_HTTP_AUTHORIZATION (algunos servidores)
    elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    }
    // Método 3: apache_request_headers (si está disponible)
    elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }
    // Método 4: getallheaders (alternativa)
    elseif (function_exists('getallheaders')) {
        $headers = getallheaders();
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($headers['authorization'])) {
            $authHeader = $headers['authorization'];
        }
    }

    if (!$authHeader) {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Authorization header not found."));
        exit();
    }

    $arr = explode(" ", $authHeader);
    $jwt = $arr[1] ?? null;

    if ($jwt) {
        try {
            // The secret key MUST be defined in the environment variables
            $secret_key = getenv('JWT_SECRET_KEY');

            if (!$secret_key) {
                http_response_code(500); // Internal Server Error
                echo json_encode(array("message" => "Internal server error. JWT secret key is not configured."));
                exit();
            }

            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            // Check for required roles if the array is not empty
            if (!empty($required_roles)) {
                $user_role = $decoded->data->rol ?? null;
                if (!in_array($user_role, $required_roles)) {
                    http_response_code(403); // Forbidden
                    echo json_encode(array("message" => "Access forbidden. You do not have the required role."));
                    exit();
                }
            }

            // The decoded token is now available, you can return it or a specific part of it
            return $decoded->data;

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array(
                "message" => "Access denied. Invalid token.",
                "error" => $e->getMessage()
            ));
            exit();
        }
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. Token not found."));
        exit();
    }
}
