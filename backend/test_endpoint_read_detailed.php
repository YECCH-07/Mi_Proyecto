<?php
echo "=== PRUEBA DETALLADA DEL ENDPOINT READ ===\n\n";

// 1. Login
$loginUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php';
$loginData = json_encode(['email' => 'admin@muni.gob.pe', 'password' => 'admin123']);

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
curl_close($ch);

$loginResult = json_decode($response, true);
$jwt = $loginResult['jwt'] ?? null;

if(!$jwt) {
    echo "❌ No se recibió JWT\n";
    exit;
}

echo "✓ JWT obtenido\n";
echo "✓ User: {$loginResult['user']['nombres']} ({$loginResult['user']['rol']})\n\n";

// 2. Probar endpoint read
echo "Llamando a /denuncias/read.php...\n";
$readUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php';

$ch = curl_init($readUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);

// Habilitar verbose para ver qué está pasando
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

// Ver verbose output
rewind($verbose);
$verboseLog = stream_get_contents($verbose);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n\n";

if($httpCode !== 200) {
    echo "Verbose Log:\n$verboseLog\n";
}

echo "\n=== FIN ===\n";
