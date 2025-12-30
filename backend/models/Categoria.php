<?php
class Categoria {
    // Database connection and table name
    private $conn;
    private $table_name = "categorias";

    // Object properties
    public $id;
    public $nombre;
    public $descripcion;
    public $icono;
    public $created_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Read all categories
    function read() {
        $query = "SELECT
                    id, nombre, descripcion, icono
                FROM
                    " . $this->table_name . "
                ORDER BY
                    nombre ASC";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
