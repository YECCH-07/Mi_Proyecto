<?php
/**
 * SOLUCIÓN ALTERNATIVA 3: Autenticación basada en Cookies
 *
 * Esta solución evita completamente el problema del header Authorization
 * usando cookies HTTP-only para mayor seguridad
 */

require_once __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

function validate_jwt_cookie($required_roles = []) {
    $jwt = null;

    // Intentar obtener el token de la cookie
    if (isset($_COOKIE['jwt_token'])) {
        $jwt = $_COOKIE['jwt_token'];
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Access denied. No authentication cookie found."));
        exit();
    }

    if ($jwt) {
        try {
            $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';

            $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

            // Check for required roles if the array is not empty
            if (!empty($required_roles)) {
                $user_role = $decoded->data->rol ?? null;
                if (!in_array($user_role, $required_roles)) {
                    http_response_code(403);
                    echo json_encode(array("message" => "Access forbidden. You do not have the required role."));
                    exit();
                }
            }

            return $decoded->data;

        } catch (Exception $e) {
            http_response_code(401);
            echo json_encode(array(
                "message" => "Access denied. Invalid token.",
                "error" => $e->getMessage()
            ));
            exit();
        }
    }
}
