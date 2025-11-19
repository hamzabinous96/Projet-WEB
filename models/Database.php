<?php
require_once 'config/database.php';

class DatabaseConnection {
    private $database;
    public $conn;

    public function __construct() {
        $this->database = new Database();
        $this->conn = $this->database->getConnection();
    }
}
?>