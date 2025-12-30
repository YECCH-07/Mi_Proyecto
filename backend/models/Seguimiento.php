<?php
class Seguimiento {
    // Database connection and table name
    private $conn;
    private $table_name = "seguimiento";

    // Object properties
    public $id;
    public $denuncia_id;
    public $estado_anterior;
    public $estado_nuevo;
    public $comentario;
    public $usuario_id;
    public $created_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new seguimiento record
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    denuncia_id = :denuncia_id,
                    estado_anterior = :estado_anterior,
                    estado_nuevo = :estado_nuevo,
                    comentario = :comentario,
                    usuario_id = :usuario_id";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->denuncia_id=htmlspecialchars(strip_tags($this->denuncia_id));
        $this->estado_anterior=htmlspecialchars(strip_tags($this->estado_anterior));
        $this->estado_nuevo=htmlspecialchars(strip_tags($this->estado_nuevo));
        $this->comentario=htmlspecialchars(strip_tags($this->comentario));
        $this->usuario_id=htmlspecialchars(strip_tags($this->usuario_id));
        
        // Bind values
        $stmt->bindParam(":denuncia_id", $this->denuncia_id);
        $stmt->bindParam(":estado_anterior", $this->estado_anterior);
        $stmt->bindParam(":estado_nuevo", $this->estado_nuevo);
        $stmt->bindParam(":comentario", $this->comentario);
        $stmt->bindParam(":usuario_id", $this->usuario_id);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all seguimiento records for a denuncia
    function readByDenuncia() {
        $query = "SELECT
                    s.id, s.estado_anterior, s.estado_nuevo, s.comentario, s.created_at, u.nombres, u.apellidos
                FROM
                    " . $this->table_name . " s
                    LEFT JOIN usuarios u ON s.usuario_id = u.id
                WHERE
                    s.denuncia_id = ?
                ORDER BY
                    s.created_at ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->denuncia_id);
        $stmt->execute();
        return $stmt;
    }
}
