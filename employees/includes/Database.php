<?php
class Database {
    private $host = "localhost";
    private $user = "root";
    private $password = "";
    private $dbname = "th_ctms"; // Replace with your database name
    private $conn;

    public function __construct() {
        $this->conn = new mysqli($this->host, $this->user, $this->password, $this->dbname);

        if ($this->conn->connect_error) {
            die("Connection failed: " . htmlspecialchars($this->conn->connect_error));
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?>
