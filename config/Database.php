<?php

class Database {
    private string $host = "localhost";
    private string $dbName = "absence_system";
    private string $username = "root";
    private string $password = ""; // XAMPP's default MySQL root has no password
    private ?PDO $connection = null;

    public function connect(): PDO {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";
                $this->connection = new PDO($dsn, $this->username, $this->password);
                $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }
        return $this->connection;
    }
}