<?php
// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and object files
include_once '../../config/database.php';
include_once '../../models/Denuncia.php';
include_once '../../models/Seguimiento.php';
include_once '../../models/User.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';
include_once '../../services/NotificationService.php';

// Validate JWT
$user_data = validate_jwt();

// Get database connection
$database = new Database();
$db = $database->getConnection();

// ============================================================================
// VALIDACIÓN DE ROL
// ============================================================================
$allowed_roles = ['admin', 'supervisor', 'operador'];
if (!in_array($user_data->rol, $allowed_roles)) {
    http_response_code(403);
    echo json_encode(array(
        "message" => "Access denied. Only administrators, supervisors and operators can update denuncias."
    ));
    exit();
}

// Instantiate objects
$denuncia = new Denuncia($db);
$seguimiento = new Seguimiento($db);

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Make sure data is not empty and ID is present
if (!empty($data->id) && !empty($data->estado)) {
    
    // Get original denuncia data before updating
    $denuncia_anterior = new Denuncia($db);
    $denuncia_anterior->id = $data->id;
    $denuncia_anterior->readOne(true); // true to fetch all fields

    if (!$denuncia_anterior->titulo) {
        http_response_code(404);
        echo json_encode(array("message" => "Denuncia not found."));
        exit();
    }
    $estado_anterior = $denuncia_anterior->estado;

    // ========================================================================
    // VALIDACIÓN DE ÁREA PARA OPERADORES
    // ========================================================================
    if ($user_data->rol === 'operador') {
        // Obtener filtro por área del operador
        $filter = filterDenunciasByArea($user_data);

        // Si el operador no tiene área asignada, bloquear
        if ($filter['filter_type'] === 'blocked') {
            http_response_code(403);
            echo json_encode(array(
                "message" => $filter['error_message']
            ));
            exit();
        }

        // Verificar que la denuncia pertenece al área del operador
        if ($denuncia_anterior->area_asignada_id != $filter['area_id']) {
            http_response_code(403);
            echo json_encode(array(
                "message" => "Access denied. You can only update denuncias from your assigned area.",
                "your_area_id" => $filter['area_id'],
                "denuncia_area_id" => $denuncia_anterior->area_asignada_id
            ));
            exit();
        }
    }

    // Set denuncia property values from the request
    $denuncia->id = $data->id;
    $denuncia->titulo = $data->titulo ?? $denuncia_anterior->titulo;
    $denuncia->descripcion = $data->descripcion ?? $denuncia_anterior->descripcion;
    $denuncia->categoria_id = $data->categoria_id ?? $denuncia_anterior->categoria_id;
    $denuncia->estado = $data->estado;
    $denuncia->area_asignada_id = $data->area_asignada_id ?? $denuncia_anterior->area_asignada_id;
    
    // Update the denuncia
    if($denuncia->update()) {
        $notification_sent = false;
        // If the status has changed, create a seguimiento record and send notification
        if ($estado_anterior !== $denuncia->estado) {
            $seguimiento->denuncia_id = $denuncia->id;
            $seguimiento->estado_anterior = $estado_anterior;
            $seguimiento->estado_nuevo = $denuncia->estado;
            $seguimiento->comentario = $data->comentario ?? 'Cambio de estado automático.';
            $seguimiento->usuario_id = $user_data->id;
            $seguimiento->create();

            // Send notification email if the denuncia is not anonymous
            if ($denuncia_anterior->usuario_id) {
                $citizen = new User($db);
                $citizen->id = $denuncia_anterior->usuario_id;
                // A method to read user by ID would be useful here. For now, let's assume we can get the email.
                // This is a simplified stand-in. In a real app, you'd have a proper user fetch method.
                $user_stmt = $db->prepare("SELECT email, nombres FROM usuarios WHERE id = ?");
                $user_stmt->bindParam(1, $citizen->id);
                $user_stmt->execute();
                $user_row = $user_stmt->fetch(PDO::FETCH_ASSOC);

                if ($user_row) {
                    $notification_service = new NotificationService();
                    $notification_sent = $notification_service->sendDenunciaStatusUpdate(
                        $user_row['email'],
                        $user_row['nombres'],
                        $denuncia_anterior->codigo,
                        $denuncia->titulo,
                        $denuncia->estado
                    );
                }
            }
        }

        http_response_code(200);
        echo json_encode(array(
            "message" => "Denuncia was updated successfully." . ($notification_sent ? " Notification sent." : "")
        ));
    } else {
        http_response_code(503); // Service Unavailable
        echo json_encode(array("message" => "Unable to update denuncia."));
    }
} else {
    http_response_code(400); // Bad Request
    echo json_encode(array("message" => "Unable to update denuncia. Data is incomplete (ID and estado are required)."));
}