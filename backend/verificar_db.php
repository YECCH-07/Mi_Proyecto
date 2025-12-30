<?php
// Verificar conexión a MySQL y existencia de base de datos
try {
    $conn = new PDO('mysql:host=localhost', 'root', '');
    echo "✓ MySQL está corriendo y accesible\n";

    $stmt = $conn->query('SHOW DATABASES LIKE "denuncia_ciudadana"');
    $dbs = $stmt->fetchAll();

    if(count($dbs) > 0) {
        echo "✓ Base de datos 'denuncia_ciudadana' existe\n";

        // Conectar a la base de datos y verificar tablas
        $conn = new PDO('mysql:host=localhost;dbname=denuncia_ciudadana', 'root', '');
        $tables = $conn->query('SHOW TABLES')->fetchAll();
        echo "✓ Tablas encontradas: " . count($tables) . "\n";

        foreach($tables as $table) {
            echo "  - " . $table[0] . "\n";
        }
    } else {
        echo "❌ Base de datos 'denuncia_ciudadana' NO existe\n";
        echo "Necesita ejecutar el script de creación de base de datos\n";
    }
} catch(Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
