<?php
/**
 * SCRIPT DE DIAGNÓSTICO: Creación de Denuncias
 *
 * Este script prueba TODO el flujo de creación de denuncias
 * para identificar exactamente dónde está fallando
 */

header("Content-Type: text/plain; charset=UTF-8");

echo "====================================================================\n";
echo "DIAGNÓSTICO COMPLETO: Creación de Denuncias\n";
echo "====================================================================\n\n";

// Incluir archivos necesarios
include_once 'config/database.php';
include_once 'models/Denuncia.php';

// ====================================================================
// PRUEBA 1: Verificar conexión a base de datos
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 1: Conexión a Base de Datos\n";
echo "====================================================================\n";

$database = new Database();
$db = $database->getConnection();

if (!$db) {
    echo "❌ ERROR CRÍTICO: No se pudo conectar a la base de datos\n";
    echo "Verifica:\n";
    echo "  - XAMPP está corriendo\n";
    echo "  - MySQL está activo\n";
    echo "  - Las credenciales en config/database.php son correctas\n\n";
    exit();
}

echo "✅ Conexión a base de datos: OK\n\n";

// ====================================================================
// PRUEBA 2: Verificar que existen las tablas necesarias
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 2: Verificar Tablas Necesarias\n";
echo "====================================================================\n";

$tablas_requeridas = ['denuncias', 'categorias', 'usuarios'];
$tablas_ok = true;

foreach ($tablas_requeridas as $tabla) {
    try {
        $stmt = $db->query("SELECT 1 FROM $tabla LIMIT 1");
        echo "✅ Tabla '$tabla': EXISTE\n";
    } catch (PDOException $e) {
        echo "❌ Tabla '$tabla': NO EXISTE o ERROR\n";
        echo "   Error: " . $e->getMessage() . "\n";
        $tablas_ok = false;
    }
}

if (!$tablas_ok) {
    echo "\n❌ ERROR: Faltan tablas en la base de datos\n";
    echo "Ejecuta el archivo database/schema.sql\n\n";
    exit();
}

echo "\n";

// ====================================================================
// PRUEBA 3: Verificar que hay categorías
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 3: Verificar Categorías\n";
echo "====================================================================\n";

try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM categorias");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_categorias = $result['total'];

    if ($total_categorias > 0) {
        echo "✅ Hay $total_categorias categorías en la base de datos\n";

        // Mostrar las categorías
        $stmt = $db->query("SELECT id, nombre FROM categorias LIMIT 5");
        echo "\nCategorías disponibles:\n";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "  - ID: {$row['id']}, Nombre: {$row['nombre']}\n";
        }
    } else {
        echo "⚠️ No hay categorías en la base de datos\n";
        echo "Esto causará que la creación falle si categoria_id es obligatorio\n";
    }
} catch (PDOException $e) {
    echo "❌ Error al verificar categorías: " . $e->getMessage() . "\n";
}

echo "\n";

// ====================================================================
// PRUEBA 4: Verificar que hay usuarios
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 4: Verificar Usuarios\n";
echo "====================================================================\n";

try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'ciudadano'");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_usuarios = $result['total'];

    if ($total_usuarios > 0) {
        echo "✅ Hay $total_usuarios usuarios ciudadanos\n";

        // Obtener un usuario de prueba
        $stmt = $db->query("SELECT id, nombres, apellidos FROM usuarios WHERE rol = 'ciudadano' LIMIT 1");
        $usuario_prueba = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Usuario de prueba: ID {$usuario_prueba['id']} - {$usuario_prueba['nombres']} {$usuario_prueba['apellidos']}\n";
    } else {
        echo "⚠️ No hay usuarios con rol 'ciudadano'\n";
        echo "Creando usuario de prueba...\n";

        // Crear usuario de prueba
        $password_hash = password_hash('test123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO usuarios (dni, nombres, apellidos, email, password_hash, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute(['99999999', 'Usuario', 'Prueba', 'test@test.com', $password_hash, 'ciudadano']);
        $usuario_prueba = ['id' => $db->lastInsertId()];
        echo "✅ Usuario de prueba creado con ID: {$usuario_prueba['id']}\n";
    }
} catch (PDOException $e) {
    echo "❌ Error al verificar usuarios: " . $e->getMessage() . "\n";
    $usuario_prueba = ['id' => 1]; // Asumir ID 1 por defecto
}

echo "\n";

// ====================================================================
// PRUEBA 5: Inserción SQL directa (sin modelo)
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 5: Inserción SQL Directa\n";
echo "====================================================================\n";

try {
    $codigo_test = "TEST-" . date("Y") . "-" . str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT);

    $query = "INSERT INTO denuncias
                (codigo, usuario_id, categoria_id, titulo, descripcion, latitud, longitud, direccion_referencia, estado, es_anonima)
              VALUES
                (:codigo, :usuario_id, :categoria_id, :titulo, :descripcion, :latitud, :longitud, :direccion_referencia, :estado, :es_anonima)";

    $stmt = $db->prepare($query);

    $datos = [
        'codigo' => $codigo_test,
        'usuario_id' => $usuario_prueba['id'],
        'categoria_id' => 1,
        'titulo' => 'PRUEBA DIAGNÓSTICO - Inserción SQL directa',
        'descripcion' => 'Esta es una denuncia de prueba creada mediante INSERT directo',
        'latitud' => -12.0464,
        'longitud' => -77.0428,
        'direccion_referencia' => 'Dirección de prueba',
        'estado' => 'registrada',
        'es_anonima' => 0
    ];

    $stmt->execute($datos);
    $insert_id = $db->lastInsertId();

    echo "✅ Inserción SQL directa: EXITOSA\n";
    echo "   ID insertado: $insert_id\n";
    echo "   Código generado: $codigo_test\n\n";

    // Verificar que realmente se insertó
    $stmt = $db->prepare("SELECT * FROM denuncias WHERE id = ?");
    $stmt->execute([$insert_id]);
    $denuncia_insertada = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($denuncia_insertada) {
        echo "✅ Verificación: La denuncia SÍ está en la base de datos\n";
        echo "   Título: " . $denuncia_insertada['titulo'] . "\n";
        echo "   Estado: " . $denuncia_insertada['estado'] . "\n";
        echo "   Fecha: " . $denuncia_insertada['created_at'] . "\n";
    } else {
        echo "❌ ERROR: La denuncia NO se encuentra en la base de datos\n";
    }

} catch (PDOException $e) {
    echo "❌ ERROR en inserción SQL directa\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Código: " . $e->getCode() . "\n\n";

    // Si falla aquí, hay un problema con la estructura de la base de datos
    echo "⚠️ Si la inserción SQL directa falla, verifica:\n";
    echo "   1. Que la tabla 'denuncias' existe\n";
    echo "   2. Que tiene todas las columnas necesarias\n";
    echo "   3. Que las claves foráneas están correctamente configuradas\n";
}

echo "\n";

// ====================================================================
// PRUEBA 6: Creación usando el Modelo Denuncia
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 6: Creación usando Modelo Denuncia\n";
echo "====================================================================\n";

try {
    $denuncia = new Denuncia($db);

    // Establecer valores
    $denuncia->usuario_id = $usuario_prueba['id'];
    $denuncia->categoria_id = 1;
    $denuncia->titulo = "PRUEBA DIAGNÓSTICO - Usando modelo";
    $denuncia->descripcion = "Esta es una denuncia de prueba creada mediante el modelo Denuncia";
    $denuncia->latitud = -12.0464;
    $denuncia->longitud = -77.0428;
    $denuncia->direccion_referencia = "Dirección de prueba modelo";
    $denuncia->estado = "registrada";
    $denuncia->es_anonima = false;

    echo "Intentando crear denuncia...\n";

    if ($denuncia->create()) {
        echo "✅ Creación con modelo: EXITOSA\n";
        echo "   ID generado: " . $denuncia->id . "\n";
        echo "   Código generado: " . $denuncia->codigo . "\n\n";

        // Verificar en BD
        $stmt = $db->prepare("SELECT * FROM denuncias WHERE id = ?");
        $stmt->execute([$denuncia->id]);
        $denuncia_creada = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($denuncia_creada) {
            echo "✅ Verificación: La denuncia SÍ está en la base de datos\n";
            echo "   Título: " . $denuncia_creada['titulo'] . "\n";
            echo "   Código: " . $denuncia_creada['codigo'] . "\n";
        } else {
            echo "❌ ERROR EXTRAÑO: create() retornó true pero no está en BD\n";
        }

    } else {
        echo "❌ ERROR: El método create() retornó false\n";
        echo "\nPosibles causas:\n";
        echo "  1. Error en la consulta SQL del modelo\n";
        echo "  2. Problema con algún campo obligatorio\n";
        echo "  3. Error en sanitización de datos\n";

        // Intentar obtener el último error de PDO
        $errorInfo = $db->errorInfo();
        if ($errorInfo[0] !== '00000') {
            echo "\nÚltimo error de PDO:\n";
            echo "  SQLSTATE: " . $errorInfo[0] . "\n";
            echo "  Código: " . $errorInfo[1] . "\n";
            echo "  Mensaje: " . $errorInfo[2] . "\n";
        }
    }

} catch (Exception $e) {
    echo "❌ EXCEPCIÓN al crear con modelo\n";
    echo "   Mensaje: " . $e->getMessage() . "\n";
    echo "   Archivo: " . $e->getFile() . "\n";
    echo "   Línea: " . $e->getLine() . "\n";
}

echo "\n";

// ====================================================================
// PRUEBA 7: Verificar total de denuncias en BD
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 7: Total de Denuncias en Base de Datos\n";
echo "====================================================================\n";

try {
    $stmt = $db->query("SELECT COUNT(*) as total FROM denuncias");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $total_denuncias = $result['total'];

    echo "📊 Total de denuncias en BD: $total_denuncias\n";

    if ($total_denuncias > 0) {
        echo "\nÚltimas 5 denuncias creadas:\n";
        echo str_repeat("-", 80) . "\n";

        $stmt = $db->query("SELECT id, codigo, titulo, estado, created_at FROM denuncias ORDER BY created_at DESC LIMIT 5");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "ID: {$row['id']} | Código: {$row['codigo']} | Título: {$row['titulo']}\n";
            echo "Estado: {$row['estado']} | Fecha: {$row['created_at']}\n";
            echo str_repeat("-", 80) . "\n";
        }
    } else {
        echo "⚠️ No hay denuncias en la base de datos\n";
        echo "Esto confirma que el problema está en la creación\n";
    }

} catch (PDOException $e) {
    echo "❌ Error al contar denuncias: " . $e->getMessage() . "\n";
}

echo "\n";

// ====================================================================
// PRUEBA 8: Verificar logs de errores de PHP
// ====================================================================
echo "====================================================================\n";
echo "PRUEBA 8: Logs de Errores de PHP\n";
echo "====================================================================\n";

$log_file = ini_get('error_log');
if ($log_file && file_exists($log_file)) {
    echo "Ubicación del log: $log_file\n";
    echo "Últimas 10 líneas del log:\n";
    echo str_repeat("-", 80) . "\n";
    $lines = file($log_file);
    $last_lines = array_slice($lines, -10);
    echo implode("", $last_lines);
} else {
    echo "⚠️ No se pudo encontrar el archivo de log de PHP\n";
    echo "Ubicaciones comunes:\n";
    echo "  - C:\\xampp\\php\\logs\\php_error_log\n";
    echo "  - C:\\xampp\\apache\\logs\\error.log\n";
}

echo "\n";

// ====================================================================
// RESUMEN Y DIAGNÓSTICO
// ====================================================================
echo "====================================================================\n";
echo "RESUMEN DEL DIAGNÓSTICO\n";
echo "====================================================================\n\n";

echo "Si la PRUEBA 5 (SQL directo) funcionó pero la PRUEBA 6 (modelo) falló:\n";
echo "  ➜ El problema está en el modelo Denuncia.php (método create)\n";
echo "  ➜ Revisa la consulta SQL en models/Denuncia.php línea 68-104\n\n";

echo "Si ambas PRUEBA 5 y PRUEBA 6 fallaron:\n";
echo "  ➜ Hay un problema con la estructura de la base de datos\n";
echo "  ➜ Verifica las claves foráneas y constraints\n";
echo "  ➜ Revisa que categoria_id=1 existe en la tabla categorias\n\n";

echo "Si ambas pruebas funcionaron:\n";
echo "  ➜ El problema está en el endpoint create.php o en el frontend\n";
echo "  ➜ Verifica que el JWT está llegando correctamente\n";
echo "  ➜ Revisa los datos que envía el formulario\n\n";

echo "====================================================================\n";
echo "PRÓXIMO PASO:\n";
echo "====================================================================\n";
echo "Ejecuta: http://localhost/DENUNCIA%20CIUDADANA/backend/test_endpoint_create.php\n";
echo "Para probar el endpoint completo con JWT\n";
echo "====================================================================\n";
