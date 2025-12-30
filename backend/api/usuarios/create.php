<?php
/**
 * API: Crear Usuario
 * Solo accesible por Administrador
 *
 * POST /api/usuarios/create.php
 *
 * Body JSON:
 * {
 *   "dni": "12345678",
 *   "nombres": "Juan",
 *   "apellidos": "Pérez",
 *   "email": "juan.perez@muni.gob.pe",
 *   "telefono": "987654321",
 *   "rol": "operador",
 *   "area_id": 1,
 *   "password": "password123"
 * }
 */

include_once '../../config/cors.php';
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Validar JWT
$user_data = validate_jwt();

// Solo admin
if ($user_data->rol !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Solo administradores pueden crear usuarios'
    ]);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Recibir datos
$data = json_decode(file_get_contents("php://input"));

// Validaciones
$errores = [];

if (empty($data->dni)) $errores[] = 'DNI es obligatorio';
if (empty($data->nombres)) $errores[] = 'Nombres es obligatorio';
if (empty($data->apellidos)) $errores[] = 'Apellidos es obligatorio';
if (empty($data->email)) $errores[] = 'Email es obligatorio';
if (empty($data->rol)) $errores[] = 'Rol es obligatorio';
if (empty($data->password)) $errores[] = 'Password es obligatorio';

// Validar rol
$roles_validos = ['admin', 'supervisor', 'operador', 'ciudadano'];
if (!empty($data->rol) && !in_array($data->rol, $roles_validos)) {
    $errores[] = 'Rol inválido. Roles válidos: ' . implode(', ', $roles_validos);
}

// Operador DEBE tener área
if ($data->rol === 'operador' && empty($data->area_id)) {
    $errores[] = 'Los operadores deben tener un área asignada';
}

// Validar DNI (8 dígitos)
if (!empty($data->dni) && !preg_match('/^\d{8}$/', $data->dni)) {
    $errores[] = 'DNI debe tener 8 dígitos';
}

// Validar email
if (!empty($data->email) && !filter_var($data->email, FILTER_VALIDATE_EMAIL)) {
    $errores[] = 'Email inválido';
}

// Validar password (mínimo 6 caracteres)
if (!empty($data->password) && strlen($data->password) < 6) {
    $errores[] = 'Password debe tener al menos 6 caracteres';
}

if (!empty($errores)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Errores de validación',
        'errors' => $errores
    ]);
    exit();
}

try {
    // Verificar email duplicado
    $check_email = "SELECT id FROM usuarios WHERE email = :email";
    $stmt_check = $db->prepare($check_email);
    $stmt_check->bindParam(':email', $data->email);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El email ya está registrado'
        ]);
        exit();
    }

    // Verificar DNI duplicado
    $check_dni = "SELECT id FROM usuarios WHERE dni = :dni";
    $stmt_check_dni = $db->prepare($check_dni);
    $stmt_check_dni->bindParam(':dni', $data->dni);
    $stmt_check_dni->execute();

    if ($stmt_check_dni->rowCount() > 0) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'El DNI ya está registrado'
        ]);
        exit();
    }

    // Verificar que el área existe (si se proporcionó)
    if (!empty($data->area_id)) {
        $check_area = "SELECT id FROM areas_municipales WHERE id = :area_id";
        $stmt_area = $db->prepare($check_area);
        $stmt_area->bindParam(':area_id', $data->area_id);
        $stmt_area->execute();

        if ($stmt_area->rowCount() == 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El área especificada no existe'
            ]);
            exit();
        }
    }

    // Hash password
    $password_hash = password_hash($data->password, PASSWORD_BCRYPT, ['cost' => 12]);

    // Insert usuario
    $query = "INSERT INTO usuarios SET
        dni = :dni,
        nombres = :nombres,
        apellidos = :apellidos,
        email = :email,
        telefono = :telefono,
        password_hash = :password_hash,
        rol = :rol,
        area_id = :area_id,
        activo = TRUE,
        verificado = FALSE";

    $stmt = $db->prepare($query);

    // Sanitizar datos
    $dni_clean = strip_tags($data->dni);
    $nombres_clean = strip_tags($data->nombres);
    $apellidos_clean = strip_tags($data->apellidos);
    $email_clean = strip_tags($data->email);
    $telefono_clean = isset($data->telefono) ? strip_tags($data->telefono) : null;

    $stmt->bindParam(':dni', $dni_clean);
    $stmt->bindParam(':nombres', $nombres_clean);
    $stmt->bindParam(':apellidos', $apellidos_clean);
    $stmt->bindParam(':email', $email_clean);
    $stmt->bindParam(':telefono', $telefono_clean);
    $stmt->bindParam(':password_hash', $password_hash);
    $stmt->bindParam(':rol', $data->rol);
    $stmt->bindValue(':area_id', ($data->rol === 'operador' ? $data->area_id : null));

    if ($stmt->execute()) {
        $nuevo_id = $db->lastInsertId();

        // Log de auditoría
        log_auditoria($db, $user_data->id, 'crear_usuario', 'usuarios', $nuevo_id, [
            'email' => $data->email,
            'rol' => $data->rol,
            'area_id' => $data->area_id ?? null
        ]);

        // Obtener nombre del área si tiene
        $area_nombre = null;
        if ($data->rol === 'operador' && $data->area_id) {
            $query_area = "SELECT nombre FROM areas_municipales WHERE id = :area_id";
            $stmt_area_nombre = $db->prepare($query_area);
            $stmt_area_nombre->bindParam(':area_id', $data->area_id);
            $stmt_area_nombre->execute();
            $result_area = $stmt_area_nombre->fetch(PDO::FETCH_ASSOC);
            $area_nombre = $result_area['nombre'] ?? null;
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'data' => [
                'id' => $nuevo_id,
                'email' => $data->email,
                'rol' => $data->rol,
                'area_id' => $data->area_id ?? null,
                'area_nombre' => $area_nombre
            ]
        ]);
    } else {
        throw new Exception('Error al insertar usuario en la base de datos');
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor',
        'error' => $e->getMessage()
    ]);
}
?>
