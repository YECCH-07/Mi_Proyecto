<?php
class Evidencia {
    // Database connection and table name
    private $conn;
    private $table_name = "evidencias";

    // Object properties
    public $id;
    public $denuncia_id;
    public $archivo_url;
    public $tipo;
    public $created_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new evidencia record
    function create() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    denuncia_id = :denuncia_id,
                    archivo_url = :archivo_url,
                    tipo = :tipo";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->denuncia_id=htmlspecialchars(strip_tags($this->denuncia_id));
        $this->archivo_url=htmlspecialchars(strip_tags($this->archivo_url));
        $this->tipo=htmlspecialchars(strip_tags($this->tipo));
        
        // Bind values
        $stmt->bindParam(":denuncia_id", $this->denuncia_id);
        $stmt->bindParam(":archivo_url", $this->archivo_url);
        $stmt->bindParam(":tipo", $this->tipo);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all evidencias for a denuncia
    function readByDenuncia() {
        $query = "SELECT
                    id, archivo_url, tipo, created_at
                FROM
                    " . $this->table_name . "
                WHERE
                    denuncia_id = ?
                ORDER BY
                    created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->denuncia_id);
        $stmt->execute();
        return $stmt;
    }
}
