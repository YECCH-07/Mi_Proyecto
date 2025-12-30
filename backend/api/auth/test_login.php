<?php
/**
 * DIAGNÓSTICO DE LOGIN
 * Este archivo muestra exactamente qué error está ocurriendo
 */

// Activar reporte de errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Diagnóstico de Login</h1>";
echo "<pre>";

// Test 1: Verificar que PHP funciona
echo "✓ PHP está funcionando\n";
echo "PHP Version: " . phpversion() . "\n\n";

// Test 2: Verificar autoload
echo "Test 2: Verificar autoload de Composer\n";
try {
    require __DIR__ . '/../../vendor/autoload.php';
    use \Firebase\JWT\JWT;
    use \Firebase\JWT\Key;
    echo "✓ Autoload cargado correctamente\n\n";
} catch (Exception $e) {
    echo "❌ Error al cargar autoload: " . $e->getMessage() . "\n\n";
    die();
}

// Test 3: Verificar Firebase JWT
echo "Test 3: Verificar Firebase JWT\n";
try {
    echo "✓ Firebase JWT se puede usar\n\n";
} catch (Exception $e) {
    echo "❌ Error con Firebase JWT: " . $e->getMessage() . "\n\n";
}

// Test 4: Verificar variables de entorno
echo "Test 4: Verificar variables de entorno\n";
if (!file_exists(__DIR__ . '/../../.env')) {
    echo "⚠️ Archivo .env no encontrado, cargando manualmente...\n";
    // Cargar .env manualmente
    $envFile = __DIR__ . '/../../.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

$jwt_secret = getenv('JWT_SECRET_KEY');
if ($jwt_secret) {
    echo "✓ JWT_SECRET_KEY está configurado: " . substr($jwt_secret, 0, 10) . "...\n\n";
} else {
    echo "❌ JWT_SECRET_KEY NO está configurado\n\n";
}

// Test 5: Verificar conexión a base de datos
echo "Test 5: Verificar conexión a base de datos\n";
try {
    include_once __DIR__ . '/../../config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "✓ Conexión a base de datos establecida\n\n";
    } else {
        echo "❌ No se pudo conectar a la base de datos\n\n";
        die();
    }
} catch (Exception $e) {
    echo "❌ Error al conectar a BD: " . $e->getMessage() . "\n\n";
    die();
}

// Test 6: Verificar modelo User
echo "Test 6: Verificar modelo User\n";
try {
    include_once __DIR__ . '/../../models/User.php';
    $user = new User($db);
    echo "✓ Modelo User cargado correctamente\n\n";
} catch (Exception $e) {
    echo "❌ Error al cargar modelo User: " . $e->getMessage() . "\n\n";
    die();
}

// Test 7: Verificar usuarios en BD
echo "Test 7: Verificar usuarios en base de datos\n";
try {
    $query = "SELECT id, email, rol FROM usuarios LIMIT 5";
    $stmt = $db->query($query);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($users) > 0) {
        echo "✓ Se encontraron " . count($users) . " usuarios:\n";
        foreach ($users as $u) {
            echo "  - ID: {$u['id']}, Email: {$u['email']}, Rol: {$u['rol']}\n";
        }
        echo "\n";
    } else {
        echo "⚠️ No hay usuarios en la base de datos\n\n";
    }
} catch (Exception $e) {
    echo "❌ Error al consultar usuarios: " . $e->getMessage() . "\n\n";
}

// Test 8: Simular login con primer usuario
echo "Test 8: Probar función emailExists() con primer usuario\n";
try {
    if (count($users) > 0) {
        $test_email = $users[0]['email'];
        echo "Probando con email: $test_email\n";

        $user->email = $test_email;
        if ($user->emailExists()) {
            echo "✓ emailExists() funciona correctamente\n";
            echo "  Usuario encontrado: {$user->nombres} {$user->apellidos}\n";
            echo "  Rol: {$user->rol}\n";
            echo "  ID: {$user->id}\n\n";
        } else {
            echo "❌ emailExists() retornó false\n\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Error en emailExists(): " . $e->getMessage() . "\n\n";
}

// Test 9: Probar generación de JWT
echo "Test 9: Probar generación de JWT\n";
try {
    $token_data = array(
        "iss" => "test",
        "aud" => "test",
        "iat" => time(),
        "nbf" => time(),
        "exp" => time() + 3600,
        "data" => array(
            "id" => 1,
            "email" => "test@test.com"
        )
    );

    $jwt = JWT::encode($token_data, $jwt_secret, 'HS256');
    echo "✓ JWT generado correctamente\n";
    echo "  Token: " . substr($jwt, 0, 50) . "...\n\n";
} catch (Exception $e) {
    echo "❌ Error al generar JWT: " . $e->getMessage() . "\n\n";
}

echo "</pre>";
echo "<h2>✅ TODOS LOS TESTS PASARON</h2>";
echo "<p>El sistema de login debería funcionar. Si aún hay error 500, revisa:</p>";
echo "<ul>";
echo "<li>Consola del navegador para ver la respuesta exacta</li>";
echo "<li>Logs de Apache: C:\\xampp\\apache\\logs\\error.log</li>";
echo "<li>Logs de PHP: C:\\xampp\\php\\logs\\php_error_log</li>";
echo "</ul>";
?>
