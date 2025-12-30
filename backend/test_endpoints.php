<?php
// Test critical endpoints
echo "=== PRUEBA DE ENDPOINTS CRÍTICOS ===\n\n";

// 1. Test database connection
echo "1. Probando conexión a base de datos...\n";
try {
    $conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "   ✓ Conexión exitosa\n\n";
} catch(PDOException $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n\n";
    exit;
}

// 2. Check if users exist
echo "2. Verificando usuarios en la base de datos...\n";
$stmt = $conn->query("SELECT COUNT(*) as count FROM usuarios");
$userCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   Total de usuarios: $userCount\n";

if($userCount > 0) {
    $stmt = $conn->query("SELECT id, nombres, apellidos, email, rol, activo FROM usuarios LIMIT 5");
    echo "   Usuarios encontrados:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $status = $row['activo'] ? 'Activo' : 'Inactivo';
        echo "   - ID: {$row['id']} | {$row['nombres']} {$row['apellidos']} | {$row['email']} | Rol: {$row['rol']} | Estado: $status\n";
    }
} else {
    echo "   ⚠️  No hay usuarios en la base de datos. Necesita crear al menos un usuario.\n";
}
echo "\n";

// 3. Check areas
echo "3. Verificando áreas municipales...\n";
$stmt = $conn->query("SELECT COUNT(*) as count FROM areas_municipales");
$areaCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   Total de áreas: $areaCount\n";
if($areaCount > 0) {
    $stmt = $conn->query("SELECT nombre FROM areas_municipales LIMIT 5");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['nombre']}\n";
    }
}
echo "\n";

// 4. Check categories
echo "4. Verificando categorías...\n";
$stmt = $conn->query("SELECT COUNT(*) as count FROM categorias");
$catCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   Total de categorías: $catCount\n";
if($catCount > 0) {
    $stmt = $conn->query("SELECT nombre FROM categorias LIMIT 5");
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - {$row['nombre']}\n";
    }
}
echo "\n";

// 5. Check denuncias
echo "5. Verificando denuncias...\n";
$stmt = $conn->query("SELECT COUNT(*) as count FROM denuncias");
$denCount = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "   Total de denuncias: $denCount\n";
if($denCount > 0) {
    $stmt = $conn->query("SELECT id, codigo, titulo, estado FROM denuncias LIMIT 5");
    echo "   Denuncias recientes:\n";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "   - ID: {$row['id']} | Código: {$row['codigo']} | {$row['titulo']} | Estado: {$row['estado']}\n";
    }
}
echo "\n";

echo "=== FIN DE PRUEBAS ===\n";
