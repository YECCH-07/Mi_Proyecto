<?php
/**
 * Database Setup and Verification Script - Version 2
 * Este script crea la base de datos, tablas y datos iniciales
 * Retorna UN SOLO objeto JSON válido
 */

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Array to store all messages
$messages = [];
$errors = [];

// Database connection parameters
$host = 'localhost';
$db_name = 'denuncia_ciudadana';
$username = 'root';
$password = '';

try {
    // First, connect without selecting a database to create it
    $conn = new PDO("mysql:host=" . $host, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = "✓ Base de datos '$db_name' creada/verificada";

    // Now connect to the specific database
    $conn = new PDO("mysql:host=" . $host . ";dbname=" . $db_name, $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables
    $tables_sql = [
        "CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            dni VARCHAR(8) UNIQUE NOT NULL,
            nombres VARCHAR(100) NOT NULL,
            apellidos VARCHAR(100) NOT NULL,
            email VARCHAR(150) UNIQUE NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            telefono VARCHAR(15),
            rol ENUM('ciudadano', 'operador', 'supervisor', 'admin') DEFAULT 'ciudadano',
            verificado BOOLEAN DEFAULT FALSE,
            activo BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_email (email),
            INDEX idx_dni (dni)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS categorias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            icono VARCHAR(50),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS areas_municipales (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(150) NOT NULL,
            responsable VARCHAR(150),
            email_contacto VARCHAR(150),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS denuncias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            codigo VARCHAR(20) UNIQUE NOT NULL,
            usuario_id INT,
            categoria_id INT NOT NULL,
            titulo VARCHAR(200) NOT NULL,
            descripcion TEXT NOT NULL,
            latitud DECIMAL(10, 8) NOT NULL,
            longitud DECIMAL(11, 8) NOT NULL,
            direccion_referencia TEXT,
            estado ENUM('registrada', 'en_revision', 'asignada', 'en_proceso', 'resuelta', 'cerrada', 'rechazada') DEFAULT 'registrada',
            area_asignada_id INT,
            es_anonima BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
            FOREIGN KEY (categoria_id) REFERENCES categorias(id),
            FOREIGN KEY (area_asignada_id) REFERENCES areas_municipales(id),
            INDEX idx_estado (estado),
            INDEX idx_codigo (codigo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS evidencias (
            id INT PRIMARY KEY AUTO_INCREMENT,
            denuncia_id INT NOT NULL,
            archivo_url VARCHAR(255) NOT NULL,
            tipo ENUM('imagen', 'video', 'documento') NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS seguimiento (
            id INT PRIMARY KEY AUTO_INCREMENT,
            denuncia_id INT NOT NULL,
            estado_anterior VARCHAR(50),
            estado_nuevo VARCHAR(50) NOT NULL,
            comentario TEXT,
            usuario_id INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

        "CREATE TABLE IF NOT EXISTS notificaciones (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT NOT NULL,
            denuncia_id INT,
            mensaje TEXT NOT NULL,
            leida BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            FOREIGN KEY (denuncia_id) REFERENCES denuncias(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
    ];

    foreach ($tables_sql as $table_sql) {
        $conn->exec($table_sql);
    }

    $messages[] = "✓ Todas las tablas creadas/verificadas (7 tablas)";

    // Check if categories exist, if not insert them
    $stmt = $conn->query("SELECT COUNT(*) FROM categorias");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $categorias_sql = "INSERT INTO categorias (nombre, descripcion, icono) VALUES
            ('Baches', 'Problemas con baches en las vías', '🕳️'),
            ('Alumbrado Público', 'Fallas en el alumbrado público', '💡'),
            ('Basura', 'Acumulación de basura o residuos', '🗑️'),
            ('Agua y Desagüe', 'Fugas de agua o problemas de desagüe', '💧'),
            ('Infraestructura', 'Problemas con infraestructura pública', '🏗️'),
            ('Seguridad', 'Temas relacionados con seguridad ciudadana', '🚨'),
            ('Parques y Jardines', 'Mantenimiento de áreas verdes', '🌳'),
            ('Tránsito', 'Problemas de tránsito y señalización', '🚦')";

        $conn->exec($categorias_sql);
        $messages[] = "✓ Categorías iniciales insertadas (8 categorías)";
    } else {
        $messages[] = "✓ Categorías ya existen ($count categorías)";
    }

    // Check if areas exist, if not insert them
    $stmt = $conn->query("SELECT COUNT(*) FROM areas_municipales");
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $areas_sql = "INSERT INTO areas_municipales (nombre, responsable, email_contacto) VALUES
            ('Gerencia de Infraestructura', 'Ing. Juan Pérez', 'infraestructura@municusco.gob.pe'),
            ('Gerencia de Servicios Públicos', 'Lic. María González', 'servicios@municusco.gob.pe'),
            ('Gerencia de Transporte', 'Ing. Carlos Ramírez', 'transporte@municusco.gob.pe'),
            ('Gerencia de Seguridad Ciudadana', 'Cnel. Pedro Martínez', 'seguridad@municusco.gob.pe'),
            ('Gerencia de Medio Ambiente', 'Biol. Ana Torres', 'ambiente@municusco.gob.pe')";

        $conn->exec($areas_sql);
        $messages[] = "✓ Áreas municipales iniciales insertadas (5 áreas)";
    } else {
        $messages[] = "✓ Áreas municipales ya existen ($count áreas)";
    }

    // Create admin user if it doesn't exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
    $stmt->execute(['admin@municusco.gob.pe']);
    $count = $stmt->fetchColumn();

    if ($count == 0) {
        $password_hash = password_hash('admin123', PASSWORD_BCRYPT, ['cost' => 12]);
        $admin_sql = "INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol, verificado, activo)
                      VALUES ('12345678', 'Admin', 'Sistema', 'admin@municusco.gob.pe', ?, 'admin', 1, 1)";
        $stmt = $conn->prepare($admin_sql);
        $stmt->execute([$password_hash]);
        $messages[] = "✓ Usuario administrador creado (admin@municusco.gob.pe / admin123)";
    } else {
        $messages[] = "✓ Usuario administrador ya existe";
    }

    // Get summary
    $summary = [];
    $tables = ['usuarios', 'categorias', 'areas_municipales', 'denuncias', 'evidencias', 'seguimiento', 'notificaciones'];

    foreach ($tables as $table) {
        $stmt = $conn->query("SELECT COUNT(*) FROM $table");
        $count = $stmt->fetchColumn();
        $summary[$table] = intval($count);
    }

    // Return single JSON response
    http_response_code(200);
    echo json_encode([
        "status" => "success",
        "message" => "Base de datos configurada correctamente",
        "messages" => $messages,
        "summary" => $summary
    ], JSON_PRETTY_PRINT);

} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Error de base de datos",
        "error" => $e->getMessage()
    ], JSON_PRETTY_PRINT);
}
?>
