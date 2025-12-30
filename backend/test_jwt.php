<?php
// Script de depuración temporal para verificar JWT

header("Content-Type: application/json; charset=UTF-8");

// Simular lo que hace login.php
echo "=== TEST 1: Generación de Token (como login.php) ===\n";
$login_secret = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
echo "Secret en login.php: " . $login_secret . "\n\n";

// Simular lo que hace validate_jwt.php
echo "=== TEST 2: Validación de Token (como validate_jwt.php) ===\n";
$validate_secret = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
echo "Secret en validate_jwt.php: " . $validate_secret . "\n\n";

echo "=== TEST 3: ¿Coinciden? ===\n";
echo ($login_secret === $validate_secret ? "✅ SÍ COINCIDEN" : "❌ NO COINCIDEN") . "\n\n";

echo "=== TEST 4: Verificar getenv ===\n";
$env_value = getenv('JWT_SECRET_KEY');
echo "getenv('JWT_SECRET_KEY'): " . ($env_value ? $env_value : "(VACÍO - usará fallback)") . "\n\n";

echo "=== TEST 5: Crear y Validar Token de Prueba ===\n";

require_once __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

try {
    // Crear token de prueba
    $secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';

    $token_data = array(
        "iss" => "test",
        "aud" => "test",
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + 3600,
        "data" => array(
            "id" => 1,
            "nombres" => "Test",
            "apellidos" => "User",
            "email" => "test@test.com",
            "rol" => "ciudadano"
        )
    );

    $jwt = JWT::encode($token_data, $secret_key, 'HS256');
    echo "Token creado: " . substr($jwt, 0, 50) . "...\n";

    // Intentar validar el token
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));
    echo "✅ Token validado correctamente\n";
    echo "Datos decodificados: \n";
    print_r($decoded);

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
