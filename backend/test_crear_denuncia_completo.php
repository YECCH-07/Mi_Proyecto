<?php
echo "=== PRUEBA COMPLETA DE CREACIÓN DE DENUNCIAS ===\n\n";

// 1. Obtener JWT
echo "1. Obteniendo JWT...\n";
$loginUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/auth/login.php';
$loginData = json_encode([
    'email' => 'admin@muni.gob.pe',
    'password' => 'admin123'
]);

$ch = curl_init($loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if($httpCode !== 200) {
    echo "❌ Error obteniendo JWT: HTTP $httpCode\n";
    echo "Response: $response\n";
    exit;
}

$loginResult = json_decode($response, true);
$jwt = $loginResult['jwt'] ?? null;
$userId = $loginResult['user']['id'] ?? null;

if(!$jwt) {
    echo "❌ No se recibió JWT\n";
    exit;
}

echo "✓ JWT obtenido\n";
echo "✓ User ID: $userId\n\n";

// 2. Obtener categoría disponible
echo "2. Obteniendo categoría...\n";
$conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
$stmt = $conn->query("SELECT id, nombre FROM categorias LIMIT 1");
$categoria = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$categoria) {
    echo "❌ No hay categorías en la BD\n";
    exit;
}

echo "✓ Categoría: {$categoria['nombre']} (ID: {$categoria['id']})\n\n";

// 3. Crear denuncia via API
echo "3. Creando denuncia via API endpoint...\n";
$createUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/create.php';

$denunciaData = json_encode([
    'titulo' => 'Test Denuncia - ' . date('Y-m-d H:i:s'),
    'descripcion' => 'Esta es una denuncia de prueba para verificar que el sistema funciona correctamente.',
    'categoria_id' => $categoria['id'],
    'latitud' => -12.046374,
    'longitud' => -77.042793,
    'direccion_referencia' => 'Av. Principal 123, Lima',
    'es_anonima' => false
]);

$ch = curl_init($createUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $denunciaData);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $jwt
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   HTTP Code: $httpCode\n";
echo "   Response: $response\n";

if($httpCode === 201) {
    $result = json_decode($response, true);
    $denunciaId = $result['id'] ?? null;
    $codigo = $result['codigo'] ?? null;

    echo "✓ Denuncia creada exitosamente\n";
    echo "   ID: $denunciaId\n";
    echo "   Código: $codigo\n\n";

    // 4. Verificar en base de datos
    echo "4. Verificando en base de datos...\n";
    $stmt = $conn->prepare("SELECT * FROM denuncias WHERE id = ?");
    $stmt->execute([$denunciaId]);
    $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

    if($denuncia) {
        echo "✓ Denuncia encontrada en BD\n";
        echo "   Título: {$denuncia['titulo']}\n";
        echo "   Estado: {$denuncia['estado']}\n";
        echo "   Código: {$denuncia['codigo']}\n";
        echo "   Usuario ID: {$denuncia['usuario_id']}\n";
        echo "   Categoría ID: {$denuncia['categoria_id']}\n\n";
    } else {
        echo "❌ Denuncia NO encontrada en BD\n\n";
    }

    // 5. Verificar que aparece en el endpoint de lectura
    echo "5. Verificando que aparece en endpoint de lectura...\n";
    $readUrl = 'http://localhost/DENUNCIA%20CIUDADANA/backend/api/denuncias/read.php';

    $ch = curl_init($readUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $jwt
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if($httpCode === 200) {
        $result = json_decode($response, true);
        $denuncias = $result['records'] ?? [];
        $encontrada = false;

        foreach($denuncias as $d) {
            if($d['id'] == $denunciaId) {
                $encontrada = true;
                break;
            }
        }

        if($encontrada) {
            echo "✓ Denuncia aparece en el listado\n";
            echo "   Total denuncias: " . count($denuncias) . "\n";
        } else {
            echo "❌ Denuncia NO aparece en el listado\n";
            echo "   Total denuncias: " . count($denuncias) . "\n";
        }
    } else {
        echo "❌ Error al obtener denuncias: HTTP $httpCode\n";
    }

} else {
    echo "❌ Error al crear denuncia\n";
}

echo "\n=== FIN DE PRUEBA ===\n";
