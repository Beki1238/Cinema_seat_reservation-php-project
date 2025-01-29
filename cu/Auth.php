<?php

require_once '../employees/includes/Database.php';

class Auth {
    private $db;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
    }

    public function login($email, $password) {
        $query = $this->db->prepare("SELECT * FROM customers WHERE email = ?");
        $query->bind_param('s', $email);
        $query->execute();
        $result = $query->get_result();
    
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                // Password matches
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                ];
                $_SESSION['logged_in'] = true; // Mark session as logged in
                $_SESSION['last_activity'] = time(); // Set activity timestamp
                return true;
            }
        }
        return false; // Invalid credentials
    }
    

    public function signup($name, $email, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT); // Hash the password
        $query = $this->db->prepare("INSERT INTO customers (name, email, password) VALUES (?, ?, ?)");
        $query->bind_param('sss', $name, $email, $hashedPassword);

        if ($query->execute()) {
            $_SESSION['user'] = [
                'id' => $query->insert_id,
                'name' => $name,
                'email' => $email,
            ];
            return true;
        } else {
            return false;
        }
    }
}

?>
