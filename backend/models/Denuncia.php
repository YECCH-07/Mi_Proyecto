<?php
/**
 * Modelo Denuncia - CORREGIDO Y OPTIMIZADO
 *
 * Correcciones aplicadas:
 * 1. Cambio de fecha_registro a created_at (campo correcto en BD)
 * 2. LEFT JOIN con categorias para obtener nombre de categoría
 * 3. LEFT JOIN con areas_municipales para obtener nombre de área
 * 4. Consultas optimizadas para cada rol
 */

class Denuncia {
    // Database connection and table name
    private $conn;
    private $table_name = "denuncias";

    // Object properties
    public $id;
    public $codigo;
    public $usuario_id;
    public $categoria_id;
    public $titulo;
    public $descripcion;
    public $latitud;
    public $longitud;
    public $direccion_referencia;
    public $estado;
    public $area_asignada_id;
    public $es_anonima;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Function to generate a unique complaint code
    private function generateUniqueCode() {
        // Format: DU-YYYY-NNNNNN (e.g., DU-2025-000001)
        $year = date("Y");
        $prefix = "DU-" . $year . "-";

        $max_attempts = 10;
        for ($attempt = 0; $attempt < $max_attempts; $attempt++) {
            // Find the highest number for the current year
            $query = "SELECT MAX(CAST(SUBSTRING(codigo, 9) AS UNSIGNED)) as max_num
                      FROM " . $this->table_name . "
                      WHERE codigo LIKE ?";
            $stmt = $this->conn->prepare($query);
            $search_prefix = $prefix . "%";
            $stmt->bindParam(1, $search_prefix);
            $stmt->execute();

            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $max_num = $row['max_num'] ? intval($row['max_num']) : 0;
            $new_number = $max_num + 1;

            $new_code = $prefix . str_pad($new_number, 6, "0", STR_PAD_LEFT);

            // Verify the code doesn't exist
            $check_query = "SELECT COUNT(*) as count FROM " . $this->table_name . " WHERE codigo = ?";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(1, $new_code);
            $check_stmt->execute();

            $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
            if ($result['count'] == 0) {
                return $new_code;
            }

            // If code exists, wait a tiny bit and try again
            usleep(100000); // 100ms
        }

        // Fallback: use timestamp if all else fails
        return $prefix . str_pad(time() % 1000000, 6, "0", STR_PAD_LEFT);
    }

    // Create new denuncia record
    function create() {
        // Generate a unique code
        $this->codigo = $this->generateUniqueCode();

        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    codigo = :codigo,
                    usuario_id = :usuario_id,
                    categoria_id = :categoria_id,
                    titulo = :titulo,
                    descripcion = :descripcion,
                    latitud = :latitud,
                    longitud = :longitud,
                    direccion_referencia = :direccion_referencia,
                    estado = :estado,
                    es_anonima = :es_anonima";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->usuario_id=htmlspecialchars(strip_tags($this->usuario_id));
        $this->categoria_id=htmlspecialchars(strip_tags($this->categoria_id));
        $this->titulo=htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion=htmlspecialchars(strip_tags($this->descripcion));
        $this->latitud=htmlspecialchars(strip_tags($this->latitud));
        $this->longitud=htmlspecialchars(strip_tags($this->longitud));
        $this->direccion_referencia=htmlspecialchars(strip_tags($this->direccion_referencia));
        $this->estado = $this->estado ?: 'registrada'; // Default state
        $this->es_anonima = $this->es_anonima ? 1 : 0; // Ensure boolean

        // Bind values
        $stmt->bindParam(":codigo", $this->codigo);
        $stmt->bindParam(":usuario_id", $this->usuario_id);
        $stmt->bindParam(":categoria_id", $this->categoria_id);
        $stmt->bindParam(":titulo", $this->titulo);
        $stmt->bindParam(":descripcion", $this->descripcion);
        $stmt->bindParam(":latitud", $this->latitud);
        $stmt->bindParam(":longitud", $this->longitud);
        $stmt->bindParam(":direccion_referencia", $this->direccion_referencia);
        $stmt->bindParam(":estado", $this->estado);
        $stmt->bindParam(":es_anonima", $this->es_anonima);

        // Execute query
        if($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }

        return false;
    }

    /**
     * ====================================================================
     * CONSULTA PARA ADMINISTRADOR
     * Debe ver TODAS las denuncias con información completa
     * ====================================================================
     */
    function readForAdmin() {
        $query = "SELECT
                    d.id,
                    d.codigo,
                    d.titulo,
                    d.descripcion,
                    d.estado,
                    d.created_at as fecha_registro,
                    d.latitud,
                    d.longitud,
                    d.direccion_referencia,
                    d.es_anonima,
                    d.usuario_id,
                    d.categoria_id,
                    d.area_asignada_id,
                    -- Datos del usuario (LEFT JOIN porque puede ser anónimo)
                    CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                    u.email as usuario_email,
                    u.telefono as usuario_telefono,
                    -- Nombre de la categoría (INNER JOIN porque es obligatorio)
                    c.nombre as categoria_nombre,
                    c.icono as categoria_icono,
                    -- Área asignada (LEFT JOIN porque puede ser NULL)
                    a.nombre as area_nombre,
                    a.responsable as area_responsable
                FROM
                    " . $this->table_name . " d
                    LEFT JOIN usuarios u ON d.usuario_id = u.id
                    INNER JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                ORDER BY
                    d.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    /**
     * ====================================================================
     * CONSULTA PARA CIUDADANO
     * Solo sus propias denuncias con información de categoría y estado
     * ====================================================================
     */
    function readForCiudadano($usuario_id) {
        $query = "SELECT
                    d.id,
                    d.codigo,
                    d.titulo,
                    d.descripcion,
                    d.estado,
                    d.created_at as fecha_registro,
                    d.latitud,
                    d.longitud,
                    d.direccion_referencia,
                    d.categoria_id,
                    d.area_asignada_id,
                    -- Nombre de la categoría
                    c.nombre as categoria_nombre,
                    c.icono as categoria_icono,
                    -- Área asignada (si ya fue asignada)
                    a.nombre as area_nombre
                FROM
                    " . $this->table_name . " d
                    INNER JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                WHERE
                    d.usuario_id = :usuario_id
                ORDER BY
                    d.created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    /**
     * ====================================================================
     * CONSULTA PARA SUPERVISOR Y OPERADOR
     * Todas las denuncias relevantes (registradas, asignadas, en proceso)
     * Con información completa para gestión
     * ====================================================================
     */
    function readForStaff($estados_permitidos = ['registrada', 'en_revision', 'asignada', 'en_proceso']) {
        // Crear placeholders para los estados
        $placeholders = implode(',', array_fill(0, count($estados_permitidos), '?'));

        $query = "SELECT
                    d.id,
                    d.codigo,
                    d.titulo,
                    d.descripcion,
                    d.estado,
                    d.created_at as fecha_registro,
                    d.latitud,
                    d.longitud,
                    d.direccion_referencia,
                    d.usuario_id,
                    d.categoria_id,
                    d.area_asignada_id,
                    d.es_anonima,
                    -- Datos del ciudadano (para contacto)
                    CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre,
                    u.email as usuario_email,
                    u.telefono as usuario_telefono,
                    -- Nombre de la categoría
                    c.nombre as categoria_nombre,
                    c.icono as categoria_icono,
                    -- Área asignada (puede ser NULL si aún no está asignada)
                    a.nombre as area_nombre,
                    a.responsable as area_responsable
                FROM
                    " . $this->table_name . " d
                    LEFT JOIN usuarios u ON d.usuario_id = u.id
                    INNER JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                WHERE
                    d.estado IN ($placeholders)
                ORDER BY
                    d.created_at DESC";

        $stmt = $this->conn->prepare($query);

        // Bind estados dinámicamente
        foreach ($estados_permitidos as $index => $estado) {
            $stmt->bindValue($index + 1, $estado);
        }

        $stmt->execute();
        return $stmt;
    }

    /**
     * ====================================================================
     * CONSULTA GENÉRICA (Para compatibilidad con código existente)
     * Mantiene la firma original pero con consulta corregida
     * ====================================================================
     */
    function read() {
        // Delega a readForAdmin (es la consulta más completa)
        return $this->readForAdmin();
    }

    /**
     * ====================================================================
     * CONSULTA POR USUARIO (Para compatibilidad con código existente)
     * Mantiene la firma original pero con consulta corregida
     * ====================================================================
     */
    function readByUsuario($usuario_id) {
        // Delega a readForCiudadano
        return $this->readForCiudadano($usuario_id);
    }

    /**
     * ====================================================================
     * LEER UNA SOLA DENUNCIA POR ID
     * ====================================================================
     */
    function readOne($all_fields = false) {
        $fields = $all_fields
            ? "d.*, CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre, u.email as usuario_email, c.nombre as categoria_nombre, a.nombre as area_nombre"
            : "d.id, d.codigo, d.titulo, d.descripcion, d.latitud, d.longitud, d.direccion_referencia, d.estado, d.created_at, CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre";

         $query = "SELECT " . $fields . "
                FROM
                    " . $this->table_name . " d
                    LEFT JOIN usuarios u ON d.usuario_id = u.id
                    LEFT JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                WHERE
                    d.id = ?
                LIMIT
                    0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            foreach ($row as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * ====================================================================
     * LEER UNA SOLA DENUNCIA POR CÓDIGO
     * ====================================================================
     */
    function readByCodigo($all_fields = false) {
        $fields = $all_fields
            ? "d.*, CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre, u.email as usuario_email, c.nombre as categoria_nombre, a.nombre as area_nombre"
            : "d.id, d.codigo, d.titulo, d.descripcion, d.latitud, d.longitud, d.direccion_referencia, d.estado, d.created_at, CONCAT(u.nombres, ' ', u.apellidos) as usuario_nombre";

         $query = "SELECT " . $fields . "
                FROM
                    " . $this->table_name . " d
                    LEFT JOIN usuarios u ON d.usuario_id = u.id
                    LEFT JOIN categorias c ON d.categoria_id = c.id
                    LEFT JOIN areas_municipales a ON d.area_asignada_id = a.id
                WHERE
                    d.codigo = ?
                LIMIT
                    0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->codigo);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            foreach ($row as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
        }
    }

    /**
     * ====================================================================
     * ACTUALIZAR DENUNCIA
     * ====================================================================
     */
    function update() {
        $query = "UPDATE " . $this->table_name . "
                SET
                    titulo = :titulo,
                    descripcion = :descripcion,
                    categoria_id = :categoria_id,
                    estado = :estado,
                    area_asignada_id = :area_asignada_id
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->titulo=htmlspecialchars(strip_tags($this->titulo));
        $this->descripcion=htmlspecialchars(strip_tags($this->descripcion));
        $this->categoria_id=htmlspecialchars(strip_tags($this->categoria_id));
        $this->estado=htmlspecialchars(strip_tags($this->estado));
        $this->area_asignada_id = $this->area_asignada_id === '' ? null : $this->area_asignada_id;
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind new values
        $stmt->bindParam(':titulo', $this->titulo);
        $stmt->bindParam(':descripcion', $this->descripcion);
        $stmt->bindParam(':categoria_id', $this->categoria_id);
        $stmt->bindParam(':estado', $this->estado);
        $stmt->bindParam(':area_asignada_id', $this->area_asignada_id);
        $stmt->bindParam(':id', $this->id);

        // Execute the query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * ====================================================================
     * ELIMINAR DENUNCIA
     * ====================================================================
     */
    function delete() {
        // Hard delete: permanently remove the record
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->id=htmlspecialchars(strip_tags($this->id));

        // Bind id of record to delete
        $stmt->bindParam(1, $this->id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    /**
     * ====================================================================
     * OBTENER ESTADÍSTICAS (útil para dashboards)
     * ====================================================================
     */
    function getEstadisticas($usuario_id = null) {
        $where_conditions = [];
        if ($usuario_id) {
            $where_conditions[] = "usuario_id = :usuario_id";
        }
        $where_clause = count($where_conditions) > 0 ? "WHERE " . implode(" AND ", $where_conditions) : "";

        $query = "SELECT
                    COUNT(*) as total,
                    SUM(CASE WHEN estado = 'registrada' THEN 1 ELSE 0 END) as registradas,
                    SUM(CASE WHEN estado = 'en_revision' THEN 1 ELSE 0 END) as en_revision,
                    SUM(CASE WHEN estado = 'asignada' THEN 1 ELSE 0 END) as asignadas,
                    SUM(CASE WHEN estado = 'en_proceso' THEN 1 ELSE 0 END) as en_proceso,
                    SUM(CASE WHEN estado = 'resuelta' THEN 1 ELSE 0 END) as resueltas,
                    SUM(CASE WHEN estado = 'cerrada' THEN 1 ELSE 0 END) as cerradas,
                    SUM(CASE WHEN estado = 'rechazada' THEN 1 ELSE 0 END) as rechazadas
                FROM
                    " . $this->table_name . "
                $where_clause";

        $stmt = $this->conn->prepare($query);

        if ($usuario_id) {
            $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        }

        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
