<?php
/**
 * API: Desactivar Usuario (Soft Delete)
 * Solo accesible por Administrador
 *
 * DELETE /api/usuarios/delete.php
 *
 * Body JSON:
 * {
 *   "id": 5
 * }
 *
 * NOTA: No se elimina físicamente, solo se marca como inactivo
 * para mantener integridad referencial con denuncias y seguimiento
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
        'message' => 'Solo administradores pueden eliminar usuarios'
    ]);
    exit();
}

// Database
$database = new Database();
$db = $database->getConnection();

// Obtener datos
$data = json_decode(file_get_contents("php://input"));

if (empty($data->id)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario requerido'
    ]);
    exit();
}

// Prevenir auto-eliminación
if ($data->id == $user_data->id) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'No puede eliminarse a sí mismo'
    ]);
    exit();
}

try {
    // Obtener información del usuario antes de eliminarlo (para log)
    $query_info = "SELECT nombres, apellidos, email, rol FROM usuarios WHERE id = :id";
    $stmt_info = $db->prepare($query_info);
    $stmt_info->bindParam(':id', $data->id);
    $stmt_info->execute();
    $usuario_info = $stmt_info->fetch(PDO::FETCH_ASSOC);

    if (!$usuario_info) {
        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
        exit();
    }

    // Soft delete: marcar como inactivo
    $query = "UPDATE usuarios SET activo = FALSE WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $data->id);

    if ($stmt->execute() && $stmt->rowCount() > 0) {
        // Log de auditoría
        log_auditoria($db, $user_data->id, 'eliminar_usuario', 'usuarios', $data->id, [
            'nombre' => $usuario_info['nombres'] . ' ' . $usuario_info['apellidos'],
            'email' => $usuario_info['email'],
            'rol' => $usuario_info['rol']
        ]);

        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente',
            'data' => [
                'id' => $data->id,
                'nombre' => $usuario_info['nombres'] . ' ' . $usuario_info['apellidos']
            ]
        ]);
    } else {
        throw new Exception('No se pudo desactivar el usuario');
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
