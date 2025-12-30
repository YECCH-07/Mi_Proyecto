<?php
/**
 * PRUEBA DEL ENDPOINT /api/denuncias/create.php
 *
 * Simula una petición completa desde el frontend
 */

header("Content-Type: text/plain; charset=UTF-8");

echo "====================================================================\n";
echo "PRUEBA DEL ENDPOINT: /api/denuncias/create.php\n";
echo "====================================================================\n\n";

// ====================================================================
// PASO 1: Generar un JWT válido para la prueba
// ====================================================================
echo "PASO 1: Generando JWT de prueba...\n";
echo str_repeat("-", 80) . "\n";

include_once 'config/database.php';
require_once __DIR__ . '/vendor/autoload.php';
use \Firebase\JWT\JWT;

$database = new Database();
$db = $database->getConnection();

// Obtener un usuario ciudadano
$stmt = $db->query("SELECT id, nombres, apellidos, email, rol FROM usuarios WHERE rol = 'ciudadano' LIMIT 1");
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "❌ No hay usuarios ciudadanos. Creando uno...\n";
    $password_hash = password_hash('test123', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['88888888', 'Test', 'Usuario', 'testuser@test.com', $password_hash, 'ciudadano']);
    $usuario_id = $db->lastInsertId();

    $usuario = [
        'id' => $usuario_id,
        'nombres' => 'Test',
        'apellidos' => 'Usuario',
        'email' => 'testuser@test.com',
        'rol' => 'ciudadano'
    ];
}

echo "✅ Usuario de prueba: {$usuario['nombres']} {$usuario['apellidos']} (ID: {$usuario['id']})\n";

// Generar JWT
$secret_key = getenv('JWT_SECRET_KEY') ?: 'denuncia_ciudadana_secret_key_2025';
$issued_at = time();
$expiration_time = $issued_at + (60 * 60); // 1 hora

$token_data = array(
    "iss" => "http://localhost",
    "aud" => "http://localhost",
    "iat" => $issued_at,
    "nbf" => $issued_at,
    "exp" => $expiration_time,
    "data" => array(
        "id" => $usuario['id'],
        "nombres" => $usuario['nombres'],
        "apellidos" => $usuario['apellidos'],
        "email" => $usuario['email'],
        "rol" => $usuario['rol']
    )
);

$jwt = JWT::encode($token_data, $secret_key, 'HS256');
echo "✅ JWT generado exitosamente\n";
echo "Token (primeros 50 caracteres): " . substr($jwt, 0, 50) . "...\n\n";

// ====================================================================
// PASO 2: Preparar datos de la denuncia
// ====================================================================
echo "PASO 2: Preparando datos de la denuncia...\n";
echo str_repeat("-", 80) . "\n";

$denuncia_data = array(
    'titulo' => 'PRUEBA ENDPOINT - ' . date('Y-m-d H:i:s'),
    'descripcion' => 'Esta es una denuncia de prueba creada para verificar el endpoint',
    'categoria_id' => 1,
    'latitud' => -12.0464,
    'longitud' => -77.0428,
    'direccion_referencia' => 'Av. Test 123, Lima',
    'es_anonima' => false
);

echo "Datos a enviar:\n";
foreach ($denuncia_data as $key => $value) {
    echo "  - $key: $value\n";
}
echo "\n";

// ====================================================================
// PASO 3: Simular la petición al endpoint
// ====================================================================
echo "PASO 3: Simulando petición POST al endpoint...\n";
echo str_repeat("-", 80) . "\n";

// Simular el entorno de la petición
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['HTTP_AUTHORIZATION'] = "Bearer $jwt";

// Simular el cuerpo de la petición
$GLOBALS['HTTP_RAW_POST_DATA'] = json_encode($denuncia_data);

// Capturar la salida del endpoint
ob_start();

try {
    // Incluir el endpoint
    include 'api/denuncias/create.php';
    $output = ob_get_clean();

    echo "Respuesta del endpoint:\n";
    echo str_repeat("-", 80) . "\n";
    echo $output . "\n";
    echo str_repeat("-", 80) . "\n\n";

    // Intentar decodificar la respuesta
    $response = json_decode($output, true);

    if ($response && isset($response['codigo'])) {
        echo "✅ ÉXITO: Denuncia creada\n";
        echo "   Código: " . $response['codigo'] . "\n";
        echo "   ID: " . $response['id'] . "\n\n";

        // Verificar en la base de datos
        $stmt = $db->prepare("SELECT * FROM denuncias WHERE codigo = ?");
        $stmt->execute([$response['codigo']]);
        $denuncia_bd = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($denuncia_bd) {
            echo "✅ VERIFICACIÓN: La denuncia SÍ está en la base de datos\n";
            echo "   ID: " . $denuncia_bd['id'] . "\n";
            echo "   Código: " . $denuncia_bd['codigo'] . "\n";
            echo "   Título: " . $denuncia_bd['titulo'] . "\n";
            echo "   Usuario ID: " . $denuncia_bd['usuario_id'] . "\n";
            echo "   Estado: " . $denuncia_bd['estado'] . "\n";
            echo "   Fecha: " . $denuncia_bd['created_at'] . "\n";
        } else {
            echo "❌ ERROR: El endpoint dice que creó la denuncia pero NO está en BD\n";
        }

    } else {
        echo "❌ ERROR: La respuesta no contiene código de denuncia\n";
        if ($response && isset($response['message'])) {
            echo "   Mensaje: " . $response['message'] . "\n";
        }
    }

} catch (Exception $e) {
    ob_end_clean();
    echo "❌ EXCEPCIÓN al ejecutar el endpoint\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n";

// ====================================================================
// PASO 4: Verificar que aparece en las consultas de lectura
// ====================================================================
echo "PASO 4: Verificando que aparece en las consultas...\n";
echo str_repeat("-", 80) . "\n";

include_once 'models/Denuncia.php';

$denuncia_model = new Denuncia($db);

// Probar lectura por usuario
echo "Consultando denuncias del usuario {$usuario['id']}...\n";
$stmt = $denuncia_model->readForCiudadano($usuario['id']);
$count = $stmt->rowCount();

echo "Total de denuncias del usuario: $count\n";

if ($count > 0) {
    echo "\nDenuncias encontradas:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "  - {$row['codigo']}: {$row['titulo']}\n";
    }
} else {
    echo "⚠️ No se encontraron denuncias para este usuario\n";
    echo "Esto podría indicar un problema en las consultas SQL\n";
}

echo "\n";

// ====================================================================
// RESUMEN FINAL
// ====================================================================
echo "====================================================================\n";
echo "RESUMEN DEL DIAGNÓSTICO\n";
echo "====================================================================\n\n";

$stmt = $db->query("SELECT COUNT(*) as total FROM denuncias");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "📊 Total de denuncias en la base de datos: {$result['total']}\n\n";

// Contar denuncias de prueba
$stmt = $db->query("SELECT COUNT(*) as total FROM denuncias WHERE titulo LIKE 'PRUEBA%'");
$result = $stmt->fetch(PDO::FETCH_ASSOC);
echo "🧪 Denuncias de prueba creadas: {$result['total']}\n\n";

echo "====================================================================\n";
echo "ANÁLISIS:\n";
echo "====================================================================\n\n";

if ($result['total'] > 0) {
    echo "✅ El endpoint create.php ESTÁ FUNCIONANDO\n";
    echo "✅ Las denuncias SÍ se están guardando en la base de datos\n\n";
    echo "Si no aparecen en el frontend, el problema podría ser:\n";
    echo "  1. El JWT no está llegando correctamente desde el navegador\n";
    echo "  2. El frontend no está enviando todos los datos requeridos\n";
    echo "  3. Hay un error de CORS bloqueando la petición\n";
    echo "  4. Las denuncias se crean pero el endpoint read.php no las muestra\n\n";
    echo "SIGUIENTE PASO:\n";
    echo "  - Abre la consola del navegador (F12)\n";
    echo "  - Intenta crear una denuncia desde el formulario\n";
    echo "  - Copia y pega TODOS los mensajes de consola aquí\n";
} else {
    echo "❌ El endpoint NO está creando denuncias\n\n";
    echo "Revisa:\n";
    echo "  1. Que el método Denuncia::create() funciona (ver test_crear_denuncia.php)\n";
    echo "  2. Que el JWT está siendo validado correctamente\n";
    echo "  3. Los logs de error de Apache/PHP\n";
}

echo "\n====================================================================\n";
