<?php
// Simular una petición POST al endpoint de login
echo "=== PRUEBA DE ENDPOINT LOGIN ===\n\n";

$apiUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php';

// Credenciales de prueba
$testUsers = [
    ['email' => 'admin@muni.gob.pe', 'password' => 'admin123', 'rol' => 'admin'],
    ['email' => 'carlos.sup@muni.gob.pe', 'password' => 'carlos123', 'rol' => 'supervisor'],
    ['email' => 'elena.op@muni.gob.pe', 'password' => 'elena123', 'rol' => 'operador'],
    ['email' => 'juan.perez@mail.com', 'password' => 'juan123', 'rol' => 'ciudadano']
];

foreach($testUsers as $index => $testUser) {
    echo ($index + 1) . ". Probando login con: {$testUser['email']} (Rol esperado: {$testUser['rol']})\n";

    // Preparar datos
    $postData = json_encode([
        'email' => $testUser['email'],
        'password' => $testUser['password']
    ]);

    // Inicializar cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($postData)
    ]);

    // Ejecutar
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "   HTTP Code: $httpCode\n";
    echo "   Raw Response: " . substr($response, 0, 200) . "...\n";

    if($httpCode === 200) {
        $data = json_decode($response, true);
        if($data === null) {
            echo "   ❌ Error: Response is not valid JSON\n";
            echo "   Full response: $response\n";
        } elseif(isset($data['success']) && $data['success']) {
            echo "   ✓ Login exitoso\n";
            echo "   Usuario: {$data['user']['nombres']} {$data['user']['apellidos']}\n";
            echo "   Rol: {$data['user']['rol']}\n";
            echo "   JWT: " . substr($data['jwt'], 0, 50) . "...\n";
        } else {
            echo "   ❌ Error: " . ($data['message'] ?? 'Unknown error') . "\n";
        }
    } else {
        echo "   ❌ Error HTTP $httpCode\n";
        echo "   Response: $response\n";
    }
    echo "\n";
}

echo "=== FIN DE PRUEBAS ===\n";
