<?php
echo "=== PRUEBA DE readForAdmin() ===\n\n";

// Cargar config
include_once 'config/database.php';
include_once 'models/Denuncia.php';

// Conectar a BD
$database = new Database();
$db = $database->getConnection();

if(!$db) {
    echo "❌ No se pudo conectar a la BD\n";
    exit;
}

echo "✓ Conectado a BD\n\n";

// Crear objeto Denuncia
$denuncia = new Denuncia($db);

// Llamar a readForAdmin()
echo "Ejecutando readForAdmin()...\n";
try {
    $stmt = $denuncia->readForAdmin();
    $num = $stmt->rowCount();

    echo "✓ Consulta ejecutada exitosamente\n";
    echo "✓ Total de denuncias: $num\n\n";

    if($num > 0) {
        echo "Primeras 5 denuncias:\n";
        $count = 0;
        while($row = $stmt->fetch(PDO::FETCH_ASSOC) && $count < 5) {
            echo "  - ID: {$row['id']} | Código: {$row['codigo']} | {$row['titulo']}\n";
            $count++;
        }
    } else {
        echo "⚠️  No hay denuncias en la base de datos\n";
    }

} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== FIN DE PRUEBA ===\n";
