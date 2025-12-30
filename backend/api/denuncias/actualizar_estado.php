<?php
/**
 * API Endpoint: Actualizar Estado de Denuncia y Enviar Notificación
 *
 * Funciones:
 * 1. Actualizar el estado de la denuncia
 * 2. Insertar registro en tabla seguimiento
 * 3. Enviar correo electrónico al ciudadano
 *
 * Requiere: Rol operador, supervisor o admin
 */

// Handle CORS and Content-Type
include_once '../../config/cors.php';

// Include database and model files
include_once '../../config/database.php';
include_once '../../middleware/validate_jwt.php';
include_once '../../middleware/filter_by_area.php';

// Get database connection
$database = new Database();
$db = $database->getConnection();

// Validate JWT and get user data
$user_data = validate_jwt();

// Verificar que el usuario sea operador, supervisor o admin
$allowed_roles = ['operador', 'supervisor', 'admin'];
if (!in_array($user_data->rol, $allowed_roles)) {
    http_response_code(403);
    echo json_encode(array("message" => "Access denied. Only operators, supervisors and admins can update denuncias."));
    exit();
}

// Get posted data
$data = json_decode(file_get_contents("php://input"));

// Validar datos requeridos
if (empty($data->denuncia_id) || empty($data->nuevo_estado) || empty($data->comentario)) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Missing required fields",
        "required" => ["denuncia_id", "nuevo_estado", "comentario"]
    ));
    exit();
}

$denuncia_id = intval($data->denuncia_id);
$nuevo_estado = $data->nuevo_estado;
$comentario = $data->comentario;

// Validar que el nuevo estado sea válido
$estados_validos = ['registrada', 'en_revision', 'asignada', 'en_proceso', 'resuelta', 'cerrada', 'rechazada'];
if (!in_array($nuevo_estado, $estados_validos)) {
    http_response_code(400);
    echo json_encode(array(
        "message" => "Invalid estado",
        "valid_estados" => $estados_validos
    ));
    exit();
}

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
    $query_check_area = "SELECT area_asignada_id FROM denuncias WHERE id = :denuncia_id";
    $stmt_check = $db->prepare($query_check_area);
    $stmt_check->bindParam(':denuncia_id', $denuncia_id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() == 0) {
        http_response_code(404);
        echo json_encode(array("message" => "Denuncia not found"));
        exit();
    }

    $check_result = $stmt_check->fetch(PDO::FETCH_ASSOC);
    $denuncia_area_id = $check_result['area_asignada_id'];

    // Comparar con el área del operador
    if ($denuncia_area_id != $filter['area_id']) {
        http_response_code(403);
        echo json_encode(array(
            "message" => "Access denied. You can only update denuncias from your assigned area.",
            "your_area_id" => $filter['area_id'],
            "denuncia_area_id" => $denuncia_area_id
        ));
        exit();
    }
}

try {
    // Iniciar transacción
    $db->beginTransaction();

    // ========================================================================
    // PASO 1: Obtener datos actuales de la denuncia y del ciudadano
    // ========================================================================
    $query_denuncia = "SELECT
                        d.id,
                        d.codigo,
                        d.titulo,
                        d.estado as estado_actual,
                        d.usuario_id,
                        u.nombres,
                        u.apellidos,
                        u.email
                    FROM
                        denuncias d
                        LEFT JOIN usuarios u ON d.usuario_id = u.id
                    WHERE
                        d.id = :denuncia_id
                    LIMIT 1";

    $stmt = $db->prepare($query_denuncia);
    $stmt->bindParam(':denuncia_id', $denuncia_id);
    $stmt->execute();

    if ($stmt->rowCount() == 0) {
        $db->rollBack();
        http_response_code(404);
        echo json_encode(array("message" => "Denuncia not found"));
        exit();
    }

    $denuncia = $stmt->fetch(PDO::FETCH_ASSOC);
    $estado_anterior = $denuncia['estado_actual'];
    $codigo_denuncia = $denuncia['codigo'];
    $titulo_denuncia = $denuncia['titulo'];

    // Datos del ciudadano
    $ciudadano_nombres = $denuncia['nombres'];
    $ciudadano_apellidos = $denuncia['apellidos'];
    $ciudadano_email = $denuncia['email'];

    // ========================================================================
    // PASO 2: Actualizar el estado de la denuncia
    // ========================================================================
    $query_update = "UPDATE denuncias
                    SET
                        estado = :nuevo_estado,
                        updated_at = NOW()
                    WHERE
                        id = :denuncia_id";

    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':nuevo_estado', $nuevo_estado);
    $stmt_update->bindParam(':denuncia_id', $denuncia_id);

    if (!$stmt_update->execute()) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Failed to update denuncia estado"));
        exit();
    }

    // ========================================================================
    // PASO 3: Insertar registro en tabla seguimiento
    // ========================================================================
    $query_seguimiento = "INSERT INTO seguimiento
                        SET
                            denuncia_id = :denuncia_id,
                            usuario_id = :usuario_id,
                            estado_anterior = :estado_anterior,
                            estado_nuevo = :estado_nuevo,
                            comentario = :comentario,
                            created_at = NOW()";

    $stmt_seguimiento = $db->prepare($query_seguimiento);
    $stmt_seguimiento->bindParam(':denuncia_id', $denuncia_id);
    $stmt_seguimiento->bindParam(':usuario_id', $user_data->id);
    $stmt_seguimiento->bindParam(':estado_anterior', $estado_anterior);
    $stmt_seguimiento->bindParam(':estado_nuevo', $nuevo_estado);
    $stmt_seguimiento->bindParam(':comentario', $comentario);

    if (!$stmt_seguimiento->execute()) {
        $db->rollBack();
        http_response_code(500);
        echo json_encode(array("message" => "Failed to insert seguimiento record"));
        exit();
    }

    // Confirmar transacción
    $db->commit();

    // ========================================================================
    // PASO 4: Enviar correo electrónico al ciudadano (si tiene email)
    // ========================================================================
    $email_enviado = false;
    $email_error = null;

    if ($ciudadano_email && filter_var($ciudadano_email, FILTER_VALIDATE_EMAIL)) {
        // Preparar contenido del correo
        $nombre_ciudadano = $ciudadano_nombres . ' ' . $ciudadano_apellidos;

        // Mapeo de estados a texto legible en español
        $estados_texto = array(
            'registrada' => 'Registrada',
            'en_revision' => 'En Revisión',
            'asignada' => 'Asignada',
            'en_proceso' => 'En Proceso',
            'resuelta' => 'Resuelta',
            'cerrada' => 'Cerrada',
            'rechazada' => 'Rechazada'
        );

        $estado_texto = $estados_texto[$nuevo_estado] ?? ucfirst($nuevo_estado);

        // Asunto del correo
        $asunto = "Actualización de su Denuncia $codigo_denuncia - Municipalidad";

        // Cuerpo del correo en HTML
        $mensaje_html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background-color: #9C221C; color: white; padding: 20px; text-align: center; }
                .content { background-color: #f9f9f9; padding: 20px; border: 1px solid #ddd; }
                .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
                .estado { background-color: #4CAF50; color: white; padding: 10px; border-radius: 5px; display: inline-block; }
                .detalle { background-color: white; padding: 15px; margin-top: 15px; border-left: 4px solid #9C221C; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>🏛️ Sistema de Denuncias Ciudadanas</h2>
                    <p>Municipalidad</p>
                </div>

                <div class='content'>
                    <h3>Estimado/a $nombre_ciudadano,</h3>

                    <p>Le informamos que el estado de su denuncia ha sido actualizado:</p>

                    <p><strong>Código de Denuncia:</strong> $codigo_denuncia</p>
                    <p><strong>Título:</strong> $titulo_denuncia</p>

                    <p><strong>Nuevo Estado:</strong> <span class='estado'>$estado_texto</span></p>

                    <div class='detalle'>
                        <h4>📝 Comentario del Operador:</h4>
                        <p>" . nl2br(htmlspecialchars($comentario)) . "</p>
                    </div>

                    <p style='margin-top: 20px;'>
                        Puede consultar el estado de su denuncia en cualquier momento ingresando
                        a nuestro portal con el código <strong>$codigo_denuncia</strong>.
                    </p>

                    <p>Gracias por contribuir al mejoramiento de nuestra comunidad.</p>
                </div>

                <div class='footer'>
                    <p>Este es un correo automático, por favor no responder.</p>
                    <p>&copy; " . date('Y') . " Municipalidad. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>
        ";

        // Cuerpo alternativo en texto plano
        $mensaje_texto = "
Estimado/a $nombre_ciudadano,

Le informamos que el estado de su denuncia ha sido actualizado:

Código de Denuncia: $codigo_denuncia
Título: $titulo_denuncia
Nuevo Estado: $estado_texto

Comentario del Operador:
$comentario

Puede consultar el estado de su denuncia en cualquier momento ingresando a nuestro portal con el código $codigo_denuncia.

Gracias por contribuir al mejoramiento de nuestra comunidad.

---
Este es un correo automático, por favor no responder.
© " . date('Y') . " Municipalidad. Todos los derechos reservados.
        ";

        // Headers del correo
        $headers = "From: Sistema de Denuncias <noreply@municipalidad.gob.pe>\r\n";
        $headers .= "Reply-To: soporte@municipalidad.gob.pe\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/alternative; boundary=\"boundary_mail\"\r\n";

        // Construir mensaje multipart
        $mensaje_completo = "--boundary_mail\r\n";
        $mensaje_completo .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
        $mensaje_completo .= $mensaje_texto . "\r\n";
        $mensaje_completo .= "--boundary_mail\r\n";
        $mensaje_completo .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
        $mensaje_completo .= $mensaje_html . "\r\n";
        $mensaje_completo .= "--boundary_mail--";

        // Enviar correo usando la función mail() de PHP
        $email_enviado = @mail($ciudadano_email, $asunto, $mensaje_completo, $headers);

        if (!$email_enviado) {
            $email_error = "Failed to send email to $ciudadano_email";
        }
    } else {
        $email_error = "No valid email address for this citizen";
    }

    // ========================================================================
    // RESPUESTA EXITOSA
    // ========================================================================
    http_response_code(200);
    echo json_encode(array(
        "success" => true,
        "message" => "Denuncia updated successfully",
        "data" => array(
            "denuncia_id" => $denuncia_id,
            "codigo" => $codigo_denuncia,
            "estado_anterior" => $estado_anterior,
            "estado_nuevo" => $nuevo_estado,
            "comentario" => $comentario,
            "email_enviado" => $email_enviado,
            "email_destinatario" => $ciudadano_email,
            "email_error" => $email_error
        )
    ));

} catch (Exception $e) {
    // Revertir transacción en caso de error
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    http_response_code(500);
    echo json_encode(array(
        "success" => false,
        "message" => "Error updating denuncia",
        "error" => $e->getMessage()
    ));
}
