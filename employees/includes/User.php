<?php
class User {
    private $conn;

    public function __construct($dbConnection) {
        $this->conn = $dbConnection;
    }

    // Method to authenticate user
    public function authenticate($emp_id, $password) {
        // Validate input
        $emp_id = htmlspecialchars($emp_id);

        // Prepare SQL query to search in both employee and TH_admin tables
        $stmt = $this->conn->prepare("
            SELECT emp_id, password, role FROM (
                SELECT emp_id, password, role FROM employee
                UNION
                SELECT emp_id, password, role FROM TH_admin
            ) AS combined_tables
            WHERE emp_id = ?
        ");
        $stmt->bind_param("s", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if user exists
        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Verify password
            if (password_verify($password, $user['password'])) {
                return $user; // Return user data if authentication succeeds
            }
        }
        return false; // Return false if authentication fails
    }

    // Method to start a secure session
    public function startSession($user) {
        session_start();
        session_regenerate_id(true); // Prevent session fixation

        $_SESSION['user_id'] = htmlspecialchars($user['emp_id']);
        $_SESSION['user_role'] = ucfirst(htmlspecialchars($user['role'])); // Store user's role
    }

    // Method to sanitize user input
    public function sanitize($input) {
        return htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    }
}
?>
