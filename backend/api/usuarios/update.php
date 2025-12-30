<?php
/**
 * API: Actualizar Usuario
 * Solo accesible por Administrador
 *
 * PUT /api/usuarios/update.php
 *
 * Body JSON:
 * {
 *   "id": 5,
 *   "nombres": "Juan Carlos",
 *   "apellidos": "Pérez López",
 *   "email": "juan.perez@muni.gob.pe",
 *   "telefono": "987654321",
 *   "rol": "operador",
 *   "area_id": 2,
 *   "activo": true
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
        'message' => 'Solo administradores pueden actualizar usuarios'
    ]);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

// Validar ID
if (empty($data->id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario requerido'
    ]);
    exit();
}

// Prevenir que admin se desactive a sí mismo
if ($data->id == $user_data->id && isset($data->activo) && !$data->activo) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No puede desactivarse a sí mismo'
    ]);
    exit();
}

// Prevenir que admin cambie su propio rol
if ($data->id == $user_data->id && isset($data->rol) && $data->rol !== 'admin') {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No puede cambiar su propio rol'
    ]);
    exit();
}

try {
    // Construir query dinámica solo con campos proporcionados
    $updates = [];
    $params = [':id' => $data->id];

    if (isset($data->nombres)) {
        $updates[] = "nombres = :nombres";
        $params[':nombres'] = strip_tags($data->nombres);
    }

    if (isset($data->apellidos)) {
        $updates[] = "apellidos = :apellidos";
        $params[':apellidos'] = strip_tags($data->apellidos);
    }

    if (isset($data->email)) {
        // Verificar que email no esté en uso por otro usuario
        $check_email = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
        $stmt_check = $db->prepare($check_email);
        $stmt_check->bindParam(':email', $data->email);
        $stmt_check->bindParam(':id', $data->id);
        $stmt_check->execute();

        if ($stmt_check->rowCount() > 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'El email ya está en uso por otro usuario'
            ]);
            exit();
        }

        $updates[] = "email = :email";
        $params[':email'] = strip_tags($data->email);
    }

    if (isset($data->telefono)) {
        $updates[] = "telefono = :telefono";
        $params[':telefono'] = strip_tags($data->telefono);
    }

    if (isset($data->rol)) {
        $roles_validos = ['admin', 'supervisor', 'operador', 'ciudadano'];
        if (!in_array($data->rol, $roles_validos)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Rol inválido'
            ]);
            exit();
        }
        $updates[] = "rol = :rol";
        $params[':rol'] = $data->rol;
    }

    if (isset($data->area_id)) {
        // Si es operador, el área es obligatoria
        if (isset($data->rol) && $data->rol === 'operador' && empty($data->area_id)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Los operadores deben tener un área asignada'
            ]);
            exit();
        }

        $updates[] = "area_id = :area_id";
        $params[':area_id'] = $data->area_id;
    }

    if (isset($data->activo)) {
        $updates[] = "activo = :activo";
        $params[':activo'] = $data->activo ? 1 : 0;
    }

    if (empty($updates)) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'No hay campos para actualizar'
        ]);
        exit();
    }

    $query = "UPDATE usuarios SET " . implode(", ", $updates) . " WHERE id = :id";

    $stmt = $db->prepare($query);

    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }

    if ($stmt->execute()) {
        // Log de auditoría
        log_auditoria($db, $user_data->id, 'actualizar_usuario', 'usuarios', $data->id, [
            'campos_actualizados' => array_keys($params)
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente'
        ]);
    } else {
        throw new Exception('Error al actualizar usuario');
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
