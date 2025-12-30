<?php
/**
 * SCRIPT: Verificar y Crear Tablas para Funcionalidad de Operador
 *
 * Este script verifica si las tablas 'evidencias' y 'seguimiento' existen.
 * Si no existen, las crea automáticamente.
 *
 * Ejecutar: php backend/CREAR_TABLAS_OPERADOR.php
 */

echo "\n";
echo "╔═══════════════════════════════════════════════════════════════╗\n";
echo "║     VERIFICACIÓN Y CREACIÓN DE TABLAS PARA OPERADOR          ║\n";
echo "╚═══════════════════════════════════════════════════════════════╝\n";
echo "\n";

include_once 'config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    echo "✅ Conexión a base de datos exitosa\n";
    echo "\n";

    // ========================================================================
    // TABLA 1: evidencias
    // ========================================================================
    echo "📋 Verificando tabla 'evidencias'...\n";
    echo str_repeat("-", 65) . "\n";

    $stmt = $db->query("SHOW TABLES LIKE 'evidencias'");

    if ($stmt->rowCount() > 0) {
        echo "✅ La tabla 'evidencias' ya existe\n";

        // Mostrar estructura
        $stmt = $db->query("DESCRIBE evidencias");
        echo "\nEstructura actual:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "⚠️ La tabla 'evidencias' NO existe. Creando...\n";

        $sql = "CREATE TABLE evidencias (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            denuncia_id INT(11) NOT NULL,
            archivo_url VARCHAR(500) NOT NULL,
            tipo ENUM('imagen', 'video') DEFAULT 'imagen',
            nombre_original VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_denuncia (denuncia_id),
            FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $db->exec($sql);

        echo "✅ Tabla 'evidencias' creada exitosamente\n";
        echo "\nColumnas creadas:\n";
        echo "   - id (INT, AUTO_INCREMENT, PRIMARY KEY)\n";
        echo "   - denuncia_id (INT, FK a denuncias)\n";
        echo "   - archivo_url (VARCHAR 500)\n";
        echo "   - tipo (ENUM: 'imagen', 'video')\n";
        echo "   - nombre_original (VARCHAR 255)\n";
        echo "   - created_at (TIMESTAMP)\n";
    }

    echo "\n";

    // ========================================================================
    // TABLA 2: seguimiento
    // ========================================================================
    echo "📋 Verificando tabla 'seguimiento'...\n";
    echo str_repeat("-", 65) . "\n";

    $stmt = $db->query("SHOW TABLES LIKE 'seguimiento'");

    if ($stmt->rowCount() > 0) {
        echo "✅ La tabla 'seguimiento' ya existe\n";

        // Mostrar estructura
        $stmt = $db->query("DESCRIBE seguimiento");
        echo "\nEstructura actual:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "   - {$row['Field']} ({$row['Type']})\n";
        }
    } else {
        echo "⚠️ La tabla 'seguimiento' NO existe. Creando...\n";

        $sql = "CREATE TABLE seguimiento (
            id INT(11) AUTO_INCREMENT PRIMARY KEY,
            denuncia_id INT(11) NOT NULL,
            usuario_id INT(11),
            estado_anterior VARCHAR(50),
            estado_nuevo VARCHAR(50) NOT NULL,
            comentario TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_denuncia (denuncia_id),
            INDEX idx_fecha (created_at),
            FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

        $db->exec($sql);

        echo "✅ Tabla 'seguimiento' creada exitosamente\n";
        echo "\nColumnas creadas:\n";
        echo "   - id (INT, AUTO_INCREMENT, PRIMARY KEY)\n";
        echo "   - denuncia_id (INT, FK a denuncias)\n";
        echo "   - usuario_id (INT, FK a usuarios)\n";
        echo "   - estado_anterior (VARCHAR 50)\n";
        echo "   - estado_nuevo (VARCHAR 50)\n";
        echo "   - comentario (TEXT)\n";
        echo "   - created_at (TIMESTAMP)\n";
    }

    echo "\n";

    // ========================================================================
    // VERIFICAR DATOS DE PRUEBA
    // ========================================================================
    echo "🧪 Verificando datos existentes...\n";
    echo str_repeat("-", 65) . "\n";

    // Contar evidencias
    $stmt = $db->query("SELECT COUNT(*) as total FROM evidencias");
    $evidencias_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   Evidencias registradas: $evidencias_count\n";

    // Contar seguimientos
    $stmt = $db->query("SELECT COUNT(*) as total FROM seguimiento");
    $seguimiento_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    echo "   Seguimientos registrados: $seguimiento_count\n";

    echo "\n";

    // ========================================================================
    // INSERTAR DATOS DE PRUEBA (OPCIONAL)
    // ========================================================================
    if ($evidencias_count == 0) {
        echo "📸 ¿Deseas insertar evidencias de prueba?\n";
        echo "   (Esta opción agregará imágenes de ejemplo para testing)\n";
        echo "\n";
        echo "   Para insertar manualmente, ejecuta:\n";
        echo "   INSERT INTO evidencias (denuncia_id, archivo_url, tipo, nombre_original)\n";
        echo "   VALUES (1, 'https://via.placeholder.com/600x400.png?text=Evidencia+1', 'imagen', 'evidencia_1.png');\n";
        echo "\n";
    }

    // ========================================================================
    // RESUMEN FINAL
    // ========================================================================
    echo "╔═══════════════════════════════════════════════════════════════╗\n";
    echo "║                    VERIFICACIÓN COMPLETADA                    ║\n";
    echo "╚═══════════════════════════════════════════════════════════════╝\n";
    echo "\n";

    $stmt = $db->query("SHOW TABLES LIKE 'evidencias'");
    $tabla_evidencias = $stmt->rowCount() > 0 ? '✅' : '❌';

    $stmt = $db->query("SHOW TABLES LIKE 'seguimiento'");
    $tabla_seguimiento = $stmt->rowCount() > 0 ? '✅' : '❌';

    echo "Estado de las tablas:\n";
    echo "   $tabla_evidencias Tabla 'evidencias'\n";
    echo "   $tabla_seguimiento Tabla 'seguimiento'\n";
    echo "\n";

    if ($tabla_evidencias == '✅' && $tabla_seguimiento == '✅') {
        echo "🎉 ¡Todas las tablas necesarias están creadas!\n";
        echo "\n";
        echo "Próximos pasos:\n";
        echo "1. Iniciar servidor frontend: cd frontend && npm run dev\n";
        echo "2. Iniciar sesión como operador\n";
        echo "3. Hacer clic en 'Ver Detalle' de una denuncia\n";
        echo "4. Actualizar estado y verificar email\n";
    } else {
        echo "⚠️ Algunas tablas no se pudieron crear.\n";
        echo "   Verifica los permisos de la base de datos.\n";
    }

    echo "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "Script ejecutado: " . date('Y-m-d H:i:s') . "\n";
    echo "═══════════════════════════════════════════════════════════════\n";
    echo "\n";

} catch (Exception $e) {
    echo "\n";
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\n";
    echo "Posibles causas:\n";
    echo "1. No hay conexión a la base de datos\n";
    echo "2. Credenciales incorrectas en config/database.php\n";
    echo "3. La tabla 'denuncias' o 'usuarios' no existe (requeridas para FK)\n";
    echo "\n";
    exit(1);
}
