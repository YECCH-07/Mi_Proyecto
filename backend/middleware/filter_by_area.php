<?php
/**
 * MIDDLEWARE: Filtrado por Área Municipal
 *
 * Filtra denuncias según el área del usuario operador
 * Uso: include_once '../../middleware/filter_by_area.php';
 *       $filter = filterDenunciasByArea($user_data);
 */

function filterDenunciasByArea($user_data) {
    global $db;

    $rol = $user_data->rol;
    $usuario_id = $user_data->id;

    // ADMIN y SUPERVISOR ven TODO
    if ($rol === 'admin' || $rol === 'supervisor') {
        return [
            'filter_type' => 'none',
            'where_clause' => '1=1',
            'can_edit_all' => true,
            'area_id' => null
        ];
    }

    // OPERADOR solo ve su área
    if ($rol === 'operador') {
        // Obtener área del operador
        $query = "SELECT area_id FROM usuarios WHERE id = :usuario_id";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $area_id = $result['area_id'];

        if ($area_id === null) {
            return [
                'filter_type' => 'blocked',
                'where_clause' => '1=0',
                'error_message' => 'No tiene área asignada. Contacte al administrador.',
                'area_id' => null
            ];
        }

        return [
            'filter_type' => 'area',
            'area_id' => $area_id,
            'where_clause' => "d.area_asignada_id = $area_id",
            'can_edit_own_area' => true
        ];
    }

    // CIUDADANO solo ve las suyas
    if ($rol === 'ciudadano') {
        return [
            'filter_type' => 'own',
            'where_clause' => "d.usuario_id = $usuario_id",
            'can_edit_own' => true,
            'area_id' => null
        ];
    }

    // DEFAULT: bloquear
    return [
        'filter_type' => 'blocked',
        'where_clause' => '1=0',
        'error_message' => 'Acceso no autorizado',
        'area_id' => null
    ];
}

/**
 * Verificar si puede ver todas las denuncias
 */
function can_view_all_denuncias($user_data) {
    return in_array($user_data->rol, ['admin', 'supervisor']);
}

/**
 * Verificar si puede gestionar usuarios
 */
function can_manage_users($user_data) {
    return $user_data->rol === 'admin';
}

/**
 * Registrar acción en logs de auditoría
 */
function log_auditoria($db, $usuario_id, $accion, $recurso, $recurso_id = null, $detalles = []) {
    try {
        $query = "INSERT INTO logs_auditoria
            (usuario_id, accion, recurso, recurso_id, detalles, ip_address, user_agent)
            VALUES
            (:usuario_id, :accion, :recurso, :recurso_id, :detalles, :ip, :ua)";

        $stmt = $db->prepare($query);

        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':accion', $accion);
        $stmt->bindParam(':recurso', $recurso);
        $stmt->bindParam(':recurso_id', $recurso_id);
        $stmt->bindValue(':detalles', json_encode($detalles));
        $stmt->bindValue(':ip', $_SERVER['REMOTE_ADDR'] ?? 'unknown');
        $stmt->bindValue(':ua', $_SERVER['HTTP_USER_AGENT'] ?? 'unknown');

        $stmt->execute();
    } catch (Exception $e) {
        // Silently fail para no interrumpir la operación principal
        error_log("Error logging auditoria: " . $e->getMessage());
    }
}
?>
