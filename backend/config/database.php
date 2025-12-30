<?php

class Database {
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    public function __construct() {
        // Load environment variables if not already loaded
        if (!getenv('DB_HOST')) {
            $this->loadEnv('../.env'); // Adjust path as necessary
        }

        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->db_name = getenv('DB_NAME') ?: 'denuncia_ciudadana';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    // Function to load .env file
    private function loadEnv($path) {
        if (!file_exists($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue; // Skip comments
            }
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv(sprintf('%s=%s', $name, $value));
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException $exception) {
            // Important: Don't echo here, as it will break CORS headers.
            // Instead, we'll send a proper JSON response.
            http_response_code(500); // Internal Server Error
            // Clear any previously buffered output
            if (ob_get_length()) {
                ob_end_clean();
            }
            // Ensure headers are sent as JSON
            header('Content-Type: application/json');
            die(json_encode(array(
                "status" => "error",
                "message" => "Database connection failed.",
                // For debugging, you might want to include the original error message.
                // Be cautious about exposing detailed errors in production.
                "error_details" => $exception->getMessage() 
            )));
        }

        return $this->conn;
    }
}
