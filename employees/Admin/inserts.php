<?php

class inserts {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function getAllEmployees() {
        $stmt = $this->db->prepare("SELECT * FROM employee");
        $stmt->execute();
        $result = $stmt->get_result();
        $employees = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $employees;
    }

    public function getEmployee($emp_id) {
        $stmt = $this->db->prepare("SELECT * FROM employee WHERE emp_id = ?");
        $stmt->bind_param("s", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $employee = $result->fetch_assoc();
        $stmt->close();
        return $employee;
    }

    public function saveEmployee($data) {
        // Retrieve and sanitize data
        $emp_id = isset($data['emp_id']) && !empty($data['emp_id']) ? $this->sanitizeEmpId($data['emp_id']) : null;
        $full_name = $this->sanitizeFullName($data['fullName'] ?? null);
        $phone = $this->sanitizePhone($data['phone'] ?? null);
        $email = $this->sanitizeEmail($data['email'] ?? null);
        $role = $this->sanitizeRole($data['role'] ?? null);
        $password = $data['password'] ?? null;
    
        // Validate required fields
        if (!$full_name || !$phone || !$email || !$role) {
            return "All fields are required!";
        }
    
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return "Invalid email format!";
        }
    
        // Validate phone number (should only contain digits)
        if (!preg_match("/^[0-9]{10}$/", $phone)) {
            return "Phone number must be 10 digits!";
        }
    
        // Validate password format (at least 8 characters, one uppercase, one lowercase, one special character)
        if ($password && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[\W_]).{8,}$/', $password)) {
            return "Password must be at least 8 characters long, and include at least one uppercase letter, one lowercase letter, and one special character.";
        }
    
        // Hash the password only if it's provided
        $hashed_password = $password ? password_hash($password, PASSWORD_DEFAULT) : null;
    
        if ($emp_id && $this->check_employee_exists($emp_id)) {
            // Update existing employee (exclude password if not provided)
            if ($hashed_password) {
                $query = "UPDATE employee SET full_name = ?, phone = ?, email = ?, role = ?, password = ? WHERE emp_id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) {
                    return "Prepare failed: " . $this->db->error;
                }
                $stmt->bind_param("ssssss", $full_name, $phone, $email, $role, $hashed_password, $emp_id);
            } else {
                $query = "UPDATE employee SET full_name = ?, phone = ?, email = ?, role = ? WHERE emp_id = ?";
                $stmt = $this->db->prepare($query);
                if (!$stmt) {
                    return "Prepare failed: " . $this->db->error;
                }
                $stmt->bind_param("sssss", $full_name, $phone, $email, $role, $emp_id);
            }
        } elseif (!$emp_id) {
            // Insert new employee with auto-generated emp_id
            if (!$password) {
                return "Password is required for new employees!";
            }
            $query = "INSERT INTO employee (full_name, phone, email, role, password, created_at) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return "Prepare failed: " . $this->db->error;
            }
            $stmt->bind_param("sssss", $full_name, $phone, $email, $role, $hashed_password);
        } else {
            // Insert new employee with custom emp_id
            if (!$password) {
                return "Password is required for new employees!";
            }
            $query = "INSERT INTO employee (emp_id, full_name, phone, email, role, password, created_at) 
                      VALUES (?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                return "Prepare failed: " . $this->db->error;
            }
            $stmt->bind_param("ssssss", $emp_id, $full_name, $phone, $email, $role, $hashed_password);
        }
    
        // Execute the statement and check for success
        $success = $stmt->execute();
        if (!$success) {
            return "Execute failed: " . $stmt->error;
        }
    
        $stmt->close();
        return "1";  // Indicating success
    }
    
    
    private function check_employee_exists($emp_id) {
        $query = "SELECT emp_id FROM employee WHERE emp_id = '$emp_id'";
        $result = mysqli_query($this->db, $query);
        return mysqli_num_rows($result) > 0;
    }

    public function deleteEmployee($emp_id) {
        $stmt = $this->db->prepare("DELETE FROM employee WHERE emp_id = ?");
        $stmt->bind_param("s", $emp_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success ? "Employee deleted successfully!" : "Error deleting employee.";
    }
    //-------------------------------------cinemarooms-------------------------------------------------------
    public function getAllRooms() {
    $stmt = $this->db->prepare("SELECT * FROM cinemarooms ORDER BY name ASC");
    if (!$stmt) {
        die("Preparation failed: " . $this->db->error);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $rooms = [];
    while ($row = $result->fetch_assoc()) {
        $rooms[] = $row;
    }

    $stmt->close();
    return $rooms;
}

public function getRoomById($id) {
    $stmt = $this->db->prepare("SELECT * FROM cinemarooms WHERE id = ?");
    if (!$stmt) {
        die("Preparation failed: " . $this->db->error);
    }

    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    $room = $result->fetch_assoc();
    $stmt->close();

    return $room;
}

public function saveRoom($data) {
    // Retrieve the data and sanitize the inputs
    $id = $data['id'] ?? null;
    $name = $this->sanitizeString($data['name']);
    $room_type = $this->sanitizeString($data['room_type']);
    $capacity = filter_var($data['capacity'], FILTER_SANITIZE_NUMBER_INT);
    $base_price = filter_var($data['base_price'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    // Ensure that all inputs are sanitized properly
    if (!preg_match("/^[a-zA-Z\s]+$/", $name)) {
        return "Error: Room name can only contain letters and spaces.";
    }

    if (!preg_match("/^[a-zA-Z\s]+$/", $room_type)) {
        return "Error: Room type can only contain letters and spaces.";
    }

    if ($capacity <= 0 || $base_price <= 0) {
        return "Error: Capacity and base price must be positive values.";
    }

    // Prepare the query based on whether we are updating or inserting
    if ($id) {
        // Update existing room
        $stmt = $this->db->prepare("UPDATE cinemarooms SET name = ?, room_type = ?, capacity = ?, base_price = ? WHERE id = ?");
        if (!$stmt) {
            die("Preparation failed: " . $this->db->error);
        }
        $stmt->bind_param("ssiii", $name, $room_type, $capacity, $base_price, $id);
    } else {
        // Insert new room
        $stmt = $this->db->prepare("INSERT INTO cinemarooms (name, room_type, capacity, base_price) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            die("Preparation failed: " . $this->db->error);
        }
        $stmt->bind_param("ssii", $name, $room_type, $capacity, $base_price);
    }

    // Execute the statement and handle success or failure
    $save = $stmt->execute();
    $stmt->close();

    if ($save) {
        // Automatically generate seats when adding a new room
        if (empty($id)) {
            $room_id = $this->db->insert_id; // Get the last inserted ID
            $this->generate_seats($room_id, $capacity);
        }
        return 1;
    }
    return 0;
}



public function deleteRoom($id) {
    // Ensure the ID is valid and numeric
    if (!is_numeric($id) || $id <= 0) {
        return 0; // Invalid ID
    }

    try {
        // Begin transaction for safe operation
        $this->db->begin_transaction();

        // Delete from seats table first (to avoid foreign key constraints)
        $stmtSeats = $this->db->prepare("DELETE FROM seats WHERE room_id = ?");
        $stmtSeats->bind_param("i", $id);
        $stmtSeats->execute();

        if ($stmtSeats->affected_rows === 0) {
            // If no seats were found, log it but proceed to delete room
            error_log("No seats found for room_id: $id");
        }
        $stmtSeats->close();

        // Delete from cinemarooms table
        $stmtRoom = $this->db->prepare("DELETE FROM cinemarooms WHERE id = ?");
        $stmtRoom->bind_param("i", $id);
        $stmtRoom->execute();

        if ($stmtRoom->affected_rows === 0) {
            // Rollback if no room was deleted
            $this->db->rollback();
            $stmtRoom->close();
            return 0; // Room not found
        }
        $stmtRoom->close();

        // Commit transaction
        $this->db->commit();
        return 1; // Success
    } catch (Exception $e) {
        // Rollback on error and log exception
        $this->db->rollback();
        error_log("Error deleting room: " . $e->getMessage());
        return 0; // Failure
    }
}

private function generate_seats($room_id, $capacity) {
    // Prepare the statement for seat insertion
    $stmt = $this->db->prepare("INSERT INTO seats (room_id, seat_number, status) VALUES (?, ?, 0)");
    if (!$stmt) {
        die("Preparation failed: " . $this->db->error);
    }

    // Calculate seat numbers and bind parameters for insertion
    $rows = ceil($capacity / 10);
    for ($i = 1; $i <= $rows; $i++) {
        for ($j = 1; $j <= 10; $j++) {
            if (($i - 1) * 10 + $j > $capacity) break;
            $seat_number = "R" . $i . "S" . $j;

            $stmt->bind_param("is", $room_id, $seat_number);
            $stmt->execute();
        }
    }

    $stmt->close();
}
// Function to sanitize string inputs
private function sanitizeString($string) {
    // Remove any unwanted characters and trim white space
    return filter_var(trim($string), FILTER_SANITIZE_STRING);
}

// Function to sanitize phone number (remove any non-digit characters)
private function sanitizePhone($phone) {
    return preg_replace("/[^0-9]/", "", $phone);  // Keep only numbers
}

// Function to sanitize email (ensure it has a valid email format)
private function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}
  // Function to sanitize emp_id (ensure it's a valid integer)
 private function sanitizeEmpId($emp_id) {
    // Remove special characters, keeping only letters (a-z, A-Z) and numbers (0-9)
    return preg_replace("/[^a-zA-Z0-9]/", "", $emp_id);
}


// Function to sanitize full name (allow alphabets and spaces, strip others)
private function sanitizeFullName($string) {
    return preg_replace("/[^a-zA-Z\s]/", "", trim($string));
}


// Function to sanitize role (only allow alphabets)
private function sanitizeRole($role) {
    return preg_replace("/[^a-zA-Z]/", "", trim($role));
}
}
?>
