<?php
// Test para simular exactamente lo que hace validate_jwt.php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

echo "=== DEBUG: Verificación de Headers ===\n\n";

// Ver todos los headers
echo "1. HTTP_AUTHORIZATION:\n";
$authHeader1 = $_SERVER['HTTP_AUTHORIZATION'] ?? null;
echo "   Valor: " . ($authHeader1 ?? "(NO ENCONTRADO)") . "\n\n";

// Alternativa REDIRECT
echo "2. REDIRECT_HTTP_AUTHORIZATION:\n";
$authHeader2 = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
echo "   Valor: " . ($authHeader2 ?? "(NO ENCONTRADO)") . "\n\n";

// Intentar con apache_request_headers
if (function_exists('apache_request_headers')) {
    echo "3. apache_request_headers():\n";
    $headers = apache_request_headers();
    echo "   Authorization: " . ($headers['Authorization'] ?? "(NO ENCONTRADO)") . "\n\n";
}

// Usar el mismo header que validate_jwt.php
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

if (!$authHeader) {
    echo "❌ ERROR: No se encontró el header Authorization\n";
    echo "Esto es exactamente el error que estás viendo\n\n";

    // Mostrar todos los headers disponibles
    echo "Headers disponibles:\n";
    foreach ($_SERVER as $key => $value) {
        if (strpos($key, 'HTTP_') === 0) {
            echo "  - $key: $value\n";
        }
    }
    exit();
}

echo "✅ Header Authorization encontrado\n\n";

// Extraer el token
$arr = explode(" ", $authHeader);
$jwt = $arr[1] ?? null;

if (!$jwt) {
    echo "❌ ERROR: No se pudo extraer el token del header\n";
    echo "Header completo: $authHeader\n";
    exit();
}

echo "Token extraído (primeros 50 caracteres): " . substr($jwt, 0, 50) . "...\n\n";

// Intentar validar
try {
    $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
    echo "Secret key usado: $secret_key\n\n";

    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    echo "✅ TOKEN VÁLIDO\n\n";
    echo "Datos del usuario:\n";
    echo "  - ID: " . $decoded->data->id . "\n";
    echo "  - Nombre: " . $decoded->data->nombres . " " . $decoded->data->apellidos . "\n";
    echo "  - Rol: " . $decoded->data->rol . "\n";
    echo "  - Email: " . $decoded->data->email . "\n";

} catch (Exception $e) {
    echo "❌ ERROR AL VALIDAR TOKEN:\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
}
