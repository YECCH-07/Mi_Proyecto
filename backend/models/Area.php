<?php
class Area {
    // Database connection and table name
    private $conn;
    private $table_name = "areas_municipales";

    // Object properties
    public $id;
    public $nombre;
    public $responsable;
    public $email_contacto;
    public $created_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all areas
    function read() {
        $query = "SELECT
                    id, nombre, responsable, email_contacto
                FROM
                    " . $this->table_name . "
                ORDER BY
                    nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
