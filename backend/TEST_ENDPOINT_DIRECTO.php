<?php
/**
 * TEST DIRECTO: Llamar al endpoint detalle_operador.php
 *
 * Este script simula una llamada HTTP al endpoint para verificar
 * que ahora funciona correctamente después de la corrección.
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║       TEST DIRECTO: detalle_operador.php                     ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

// ============================================================================
// PASO 1: Obtener un JWT válido para la prueba
// ============================================================================
echo "🔑 PASO 1: Generando JWT de prueba...\n";
echo str_repeat("-", 65) . "\n";

include_once 'config/database.php';

// Obtener un usuario operador
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, email, rol FROM usuarios WHERE rol IN ('operador', 'admin') LIMIT 1";
$stmt = $db->query($query);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "❌ No hay usuarios operador/admin en la BD\n";
    exit(1);
}

echo "✅ Usuario encontrado: {$user['email']} (rol: {$user['rol']})\n";

// Generar JWT (simplificado para testing)
include_once 'vendor/firebase/php-jwt/src/JWT.php';
include_once 'vendor/firebase/php-jwt/src/Key.php';

use Firebase\JWT\JWT;

$secret_key = "tu_clave_secreta_super_segura_2024";
$issuer_claim = "localhost";
$audience_claim = "localhost";
$issuedat_claim = time();
$expire_claim = $issuedat_claim + 3600;

$token_payload = array(
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "exp" => $expire_claim,
    "data" => array(
        "id" => $user['id'],
        "email" => $user['email'],
        "rol" => $user['rol']
    )
);

$jwt = JWT::encode($token_payload, $secret_key, 'HS256');
echo "✅ JWT generado exitosamente\n";
echo "\n";

// ============================================================================
// PASO 2: Obtener una denuncia de prueba
// ============================================================================
echo "📋 PASO 2: Obteniendo denuncia de prueba...\n";
echo str_repeat("-", 65) . "\n";

$query = "SELECT id, codigo, titulo FROM denuncias LIMIT 1";
$stmt = $db->query($query);
$denuncia = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$denuncia) {
    echo "❌ No hay denuncias en la BD\n";
    exit(1);
}

$denuncia_id = $denuncia['id'];
echo "✅ Denuncia encontrada:\n";
echo "   ID: {$denuncia['id']}\n";
echo "   Código: {$denuncia['codigo']}\n";
echo "   Título: {$denuncia['titulo']}\n";
echo "\n";

// ============================================================================
// PASO 3: Simular llamada al endpoint
// ============================================================================
echo "🧪 PASO 3: Ejecutando endpoint con los datos...\n";
echo str_repeat("-", 65) . "\n";

// Configurar variables de entorno como si fuera una petición HTTP
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ' . $jwt;
$_GET['id'] = $denuncia_id;

// Capturar output del endpoint
ob_start();

try {
    // Incluir el endpoint (esto lo ejecutará)
    include 'api/denuncias/detalle_operador.php';

    $output = ob_get_clean();

    // Intentar decodificar JSON
    $response = json_decode($output, true);

    if ($response === null) {
        echo "❌ ERROR: El endpoint no retornó JSON válido\n";
        echo "Output recibido:\n";
        echo $output . "\n";
        exit(1);
    }

    // Verificar estructura de respuesta
    if (isset($response['success']) && $response['success'] === true) {
        echo "✅ Endpoint ejecutado EXITOSAMENTE\n\n";

        echo "📊 RESPUESTA RECIBIDA:\n";
        echo "   ✓ success: " . ($response['success'] ? 'true' : 'false') . "\n";

        if (isset($response['data'])) {
            echo "   ✓ data existe\n";

            if (isset($response['data']['denuncia'])) {
                $d = $response['data']['denuncia'];
                echo "   ✓ denuncia:\n";
                echo "      - ID: {$d['id']}\n";
                echo "      - Código: {$d['codigo']}\n";
                echo "      - Título: {$d['titulo']}\n";
                echo "      - Estado: {$d['estado']}\n";
            }

            if (isset($response['data']['ciudadano'])) {
                $c = $response['data']['ciudadano'];
                echo "   ✓ ciudadano:\n";
                echo "      - Nombre: {$c['nombre_completo']}\n";
                echo "      - Email: " . ($c['email'] ?? 'N/A') . "\n";
            }

            if (isset($response['data']['evidencias'])) {
                $count = count($response['data']['evidencias']);
                echo "   ✓ evidencias: $count registros\n";
            }

            if (isset($response['data']['seguimiento'])) {
                $count = count($response['data']['seguimiento']);
                echo "   ✓ seguimiento: $count registros\n";
            }

            if (isset($response['data']['ubicacion'])) {
                $u = $response['data']['ubicacion'];
                echo "   ✓ ubicacion:\n";
                echo "      - Coordenadas: ({$u['latitud']}, {$u['longitud']})\n";
                if ($u['google_maps_url']) {
                    echo "      - Google Maps: ✅ URL generada\n";
                }
            }
        }

        echo "\n";
        echo "🎉 ¡EL ENDPOINT ESTÁ FUNCIONANDO CORRECTAMENTE!\n";

    } else {
        echo "❌ ERROR: El endpoint retornó success = false\n";
        echo "Mensaje: " . ($response['message'] ?? 'Sin mensaje') . "\n";
    }

} catch (Exception $e) {
    ob_end_clean();
    echo "❌ EXCEPCIÓN CAPTURADA: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "Test completado: " . date('Y-m-d H:i:s') . "\n";
echo "═══════════════════════════════════════════════════════════════\n";
echo "\n";
