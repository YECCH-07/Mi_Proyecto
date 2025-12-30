<?php

class User {
    // Database connection and table name
    private $conn;
    private $table_name = "usuarios";

    // Object properties
    public $id;
    public $dni;
    public $nombres;
    public $apellidos;
    public $email;
    public $password; // This will be the plain password for input, not password_hash
    public $telefono;
    public $rol;
    public $verificado;
    public $activo;
    public $created_at;
    public $updated_at;

    // Constructor with $db as database connection
    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new user record
    function register() {
        // Query to insert record
        $query = "INSERT INTO " . $this->table_name . "
                SET
                    dni = :dni,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    email = :email,
                    password_hash = :password_hash,
                    telefono = :telefono,
                    rol = :rol,
                    verificado = :verificado,
                    activo = :activo";

        // Prepare query
        $stmt = $this->conn->prepare($query);

        // Sanitize
        $this->dni=htmlspecialchars(strip_tags($this->dni));
        $this->nombres=htmlspecialchars(strip_tags($this->nombres));
        $this->apellidos=htmlspecialchars(strip_tags($this->apellidos));
        $this->email=htmlspecialchars(strip_tags($this->email));
        $this->telefono=htmlspecialchars(strip_tags($this->telefono));
        // rol is an ENUM, ensure it's a valid value or use default
        $this->rol = $this->rol ?: 'ciudadano'; // Default to ciudadano if not set
        $this->verificado = $this->verificado ?: 0; // Default to false
        $this->activo = $this->activo ?: 1; // Default to true

        // Bind values
        $stmt->bindParam(":dni", $this->dni);
        $stmt->bindParam(":nombres", $this->nombres);
        $stmt->bindParam(":apellidos", $this->apellidos);
        $stmt->bindParam(":email", $this->email);

        // Hash the password before saving to database
        $password_hash = password_hash($this->password, PASSWORD_BCRYPT, ['cost' => 12]);
        $stmt->bindParam(":password_hash", $password_hash);

        $stmt->bindParam(":telefono", $this->telefono);
        $stmt->bindParam(":rol", $this->rol);
        $stmt->bindParam(":verificado", $this->verificado);
        $stmt->bindParam(":activo", $this->activo);

        // Execute query
        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Check if email exists
    function emailExists() {
        // Query to check if email exists
        $query = "SELECT id, dni, nombres, apellidos, password_hash, rol, verificado, activo
                FROM " . $this->table_name . "
                WHERE email = ?
                LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare( $query );

        // Sanitize
        $this->email=htmlspecialchars(strip_tags($this->email));

        // Bind email value
        $stmt->bindParam(1, $this->email);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If email exists, assign values to object properties for easy access and use
        if($num > 0) {
            // Get record details
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Assign property values
            $this->id = $row['id'];
            $this->dni = $row['dni'];
            $this->nombres = $row['nombres'];
            $this->apellidos = $row['apellidos'];
            $this->password = $row['password_hash']; // Storing hashed password for verification
            $this->rol = $row['rol'];
            $this->verificado = $row['verificado'];
            $this->activo = $row['activo'];

            return true;
        }

        return false;
    }

    // Check if DNI exists
    function dniExists() {
        // Query to check if DNI exists
        $query = "SELECT id
                FROM " . $this->table_name . "
                WHERE dni = ?
                LIMIT 0,1";

        // Prepare query statement
        $stmt = $this->conn->prepare( $query );

        // Sanitize
        $this->dni=htmlspecialchars(strip_tags($this->dni));

        // Bind DNI value
        $stmt->bindParam(1, $this->dni);

        // Execute the query
        $stmt->execute();

        // Get number of rows
        $num = $stmt->rowCount();

        // If DNI exists
        if($num > 0) {
            return true;
        }

        return false;
    }

    // Verify password
    public function verifyPassword($password) {
        return password_verify($password, $this->password); // this->password holds the hashed password from emailExists()
    }
}
