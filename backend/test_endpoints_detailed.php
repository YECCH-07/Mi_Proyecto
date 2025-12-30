<?php
// Test endpoints que están fallando
echo "=== PRUEBA DETALLADA DE ENDPOINTS CON ERROR 500 ===\n\n";

// Primero hacer login para obtener JWT
$apiUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php';

$postData = json_encode([
    'email' => 'admin@muni.gob.pe',
    'password' => 'admin123'
]);

echo "1. Obteniendo JWT con login...\n";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpCode !== 200) {
    echo "❌ Error obteniendo JWT: HTTP $httpCode\n";
    echo "Response: $response\n";
    exit;
}

$data = json_decode($response, true);
$jwt = $data['jwt'] ?? null;

if(!$jwt) {
    echo "❌ No se recibió JWT\n";
    exit;
}

echo "✓ JWT obtenido: " . substr($jwt, 0, 30) . "...\n\n";

// Ahora probar los endpoints que fallan
$endpoints = [
    '/denuncias/read.php',
    '/estadisticas/denuncias_por_categoria.php',
    '/estadisticas/denuncias_por_estado.php'
];

foreach($endpoints as $endpoint) {
    echo "2. Probando endpoint: $endpoint\n";
    $url = "http://localhost/DENUNCIA%20CIUDADANA/backend/api" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $jwt
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   HTTP Code: $httpCode\n";

    if($httpCode === 200) {
        $data = json_decode($response, true);
        echo "   ✓ Success\n";
        if(isset($data['records'])) {
            echo "   Records: " . count($data['records']) . "\n";
        }
    } else {
        echo "   ❌ Error\n";
        echo "   Response: " . substr($response, 0, 500) . "\n";
    }
    echo "\n";
}

echo "=== FIN DE PRUEBAS ===\n";
