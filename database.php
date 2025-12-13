<?php
class Database{
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "quiz_system";

    protected $conn;

    public function connect() {
        if ($this->conn) {
            return $this->conn;
        }

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, $options);
            return $this->conn;
        } catch (PDOException $e) {
            // In real apps, don't echo the error. Log it and return/throw a generic message.
            throw $e;
        }
    }
}

// Create a global $pdo for compatibility with older code that expects $pdo
if (!isset($pdo)) {
    try {
        $pdo = (new Database())->connect();
    } catch (Exception $e) {
        error_log('Database connect failed: ' . $e->getMessage());
        // don't throw here to avoid breaking pages that can handle missing DB
    }
}

