<?php

namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private $host = 'localhost';
    private $db_name = 'iams_arms';
    private $username = 'root';
    private $password = '';
    private $conn;

    public function getConnection()
    {
        $this->conn = null;

        // Get environment variables or fallback to defaults
        $db_host = getenv('DB_HOST') ?: ($_SERVER['DB_HOST'] ?? 'localhost');
        $db_port = getenv('DB_PORT') ?: ($_SERVER['DB_PORT'] ?? '5432');
        $db_name = getenv('DB_NAME') ?: ($_SERVER['DB_NAME'] ?? 'postgres');
        $db_user = getenv('DB_USER') ?: ($_SERVER['DB_USER'] ?? 'postgres');
        $db_pass = getenv('DB_PASS') ?: ($_SERVER['DB_PASS'] ?? '');

        try {
            // PostgreSQL DSN
            $dsn = "pgsql:host=" . $db_host . ";port=" . $db_port . ";dbname=" . $db_name;
            
            $this->conn = new PDO($dsn, $db_user, $db_pass);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Set default fetch mode to object
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
        } catch(PDOException $exception) {
            echo "Connection error: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
