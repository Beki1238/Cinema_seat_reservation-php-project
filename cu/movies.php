<?php
include '../employees/includes/Database.php';

class Movie
{
    private $db;
    private $table = 'movies';

    // Constructor accepts the Database dbection object
    public function __construct($db)
    {
        $this->db = $db;
    }

    // Get all movies based on status ('active' or 'inactive')
    public function getMoviesByStatus($status)
    {
        // Prepare the SQL query
        $query = "SELECT * FROM " . $this->table . " WHERE status = ?";
        
        // Prepare the statement
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("s", $status); // "s" means it's a string (active/inactive)
        
        // Execute the query
        $stmt->execute();
        
        // Get the result
        $result = $stmt->get_result();
        
        // Check if there are any movies
        if ($result->num_rows > 0) {
            // Store all the movies in an associative array
            $movies = [];
            while ($row = $result->fetch_assoc()) {
                $movies[] = $row;
            }
            return $movies;
        } else {
            return [];
        }
    }

    // Get all active movies
    public function getActiveMovies()
    {
        return $this->getMoviesByStatus('active');
    }

    // Get all upcoming movies (inactive)
    public function getInactiveMovies()
    {
        return $this->getMoviesByStatus('inactive');
    }

    // Get a single movie by its ID
    public function getMovieById($id)
    {
        $query = "SELECT * FROM " . $this->table . " WHERE id = ? LIMIT 1";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id); // "i" means it's an integer
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            return $result->fetch_assoc(); // Return movie data as an associative array
        }
        
        return null; // If no movie found, return null
    }

    // Get showtimes for a specific movie
    public function getShowtimesByMovieId($movie_id)
    {
        $query = "SELECT showtime.id AS showtime_id, cinemarooms.name AS room_name, 
                         showtime.start_time, cinemarooms.base_price, showtime.price AS showtime_price
                  FROM showtime
                  JOIN cinemarooms ON showtime.room_id = cinemarooms.id
                  WHERE showtime.movie_id = ?
                  ORDER BY showtime.start_time ASC";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $movie_id);
        $stmt->execute();
        $result = $stmt->get_result();

        return $result; // Return the result set for showtimes
    }
    public function getAllRooms()
    {
        $query = "SELECT * FROM cinemarooms";  // Query to fetch all cinema rooms
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if there are rooms available
        if ($result->num_rows > 0) {
            $rooms = [];
            while ($row = $result->fetch_assoc()) {
                $rooms[] = $row;
            }
            return $rooms;
        }
        return [];  // Return an empty array if no rooms are found
    }
    public function getShowtimeDetails($showtime_id) {
        global $conn;
        $query = "SELECT cinemarooms.id AS room_id, cinemarooms.name AS room_name, 
                         cinemarooms.base_price, showtime.start_time, showtime.price AS showtime_price
                  FROM showtime
                  JOIN cinemarooms ON showtime.room_id = cinemarooms.id
                  WHERE showtime.id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $showtime_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Method to fetch seats for a given room
    public function getSeatsByRoomId($room_id) {
        global $conn;
        $seat_query = "SELECT * FROM seats WHERE room_id = ?";
        $seat_stmt = $conn->prepare($seat_query);
        $seat_stmt->bind_param("i", $room_id);
        $seat_stmt->execute();
        return $seat_stmt->get_result();
    }
    
// Method to generate unique booking ID
    public function generateBookingId() {
        do {
            $prefix = "BK"; // Prefix for booking ID
            $uniqueId = uniqid($prefix, false); // Generate a unique ID
            $query = "SELECT COUNT(*) AS count FROM bookings WHERE id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("s", $uniqueId);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $count = $row['count'] ?? 0; // Fetch the count value
            $stmt->close();
        } while ($count > 0); // Ensure ID is unique

        return strtoupper($uniqueId); // Return ID in uppercase
    }

    // Method to insert booking record
    public function insertBooking($booking_id, $customer_id, $showtime_id, $total_price) {
        $query = "INSERT INTO bookings (id, customer_id, showtime_id, total_price, status) 
                  VALUES (?, ?, ?, ?, 'pending')";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("siis", $booking_id, $customer_id, $showtime_id, $total_price);
        if ($stmt->execute()) {
            return true;
        } else {
            error_log("Error inserting booking: " . $stmt->error);
            return false;
        }
    }

    // Method to link seats to the booking and update seat status
    public function linkSeatsToBooking($booking_id, $selected_seats) {
        foreach ($selected_seats as $seat_id) {
            // Link the seat to the booking
            $link_query = "INSERT INTO booked_seats (booking_id, seat_id) VALUES (?, ?)";
            $link_stmt = $this->db->prepare($link_query);
            $link_stmt->bind_param("si", $booking_id, $seat_id);
            if (!$link_stmt->execute()) {
                error_log("Error linking seat to booking: " . $link_stmt->error);
                return false;
            }

            // Update the seat status to 'temporarily booked' (status = 1)
            $update_seat_query = "UPDATE seats SET status = 1 WHERE id = ?";
            $update_seat_stmt = $this->db->prepare($update_seat_query);
            $update_seat_stmt->bind_param("i", $seat_id);
            if (!$update_seat_stmt->execute()) {
                error_log("Error updating seat status: " . $update_seat_stmt->error);
                return false;
            }
        }

        return true;
    }

    // Get the user ID from the session
    public function getUserId() {
        return $_SESSION['user']['id'] ?? null;
    }
    // Fetch user details
    public function getUserDetails($customer_id) {
        $query = "SELECT name, email FROM customers WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Fetch user bookings
    public function getUserBookings($customer_id) {
        $query = "
   SELECT 
    bookings.id AS booking_id, 
    bookings.total_price, 
    bookings.status, 
    bookings.payment_status, 
    bookings.receipt_img, 
   
    GROUP_CONCAT(seats.seat_number SEPARATOR ', ') AS selected_seats, 
    showtime.start_time AS showtime, 
    showtime.id AS booking_showtime_id, -- Alias added
    movies.title AS movie_title, 
    customers.name AS customer_name, 
    customers.email AS customer_email 
FROM bookings
JOIN booked_seats ON bookings.id = booked_seats.booking_id
JOIN seats ON booked_seats.seat_id = seats.id
JOIN showtime ON bookings.showtime_id = showtime.id
JOIN movies ON showtime.movie_id = movies.id
JOIN customers ON bookings.customer_id = customers.id
WHERE bookings.customer_id = ?
GROUP BY 
    bookings.id, 
    bookings.total_price, 
    bookings.status, 
    bookings.payment_status, 
    bookings.receipt_img, 
  
    showtime.start_time, 
    showtime.id, 
    movies.title, 
    customers.name, 
    customers.email;

        ";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();
        return $stmt->get_result();
    }
   // Method to handle receipt upload
   public function uploadReceipt($booking_id, $customer_id, $file) {
    // Validate file
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception("Error uploading receipt. Please try again.");
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception("Invalid file type. Only JPG, PNG, and GIF files are allowed.");
    }
// include "../employees/Front_desk_Officer/";
    // Generate a unique file name
    $file_name = 'receipt_' . $booking_id . '_' . time() . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
    $upload_dir = './employees/Front_desk_Officer/uploads/';
    $upload_path = $upload_dir . $file_name;

    // Create upload directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Move the uploaded file
    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        throw new Exception("Failed to save the uploaded file. Please try again.");
    }
    

    // Update the database with the file name
    $query = "UPDATE bookings SET receipt_img = ? WHERE id = ? AND customer_id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("sii", $file_name, $booking_id, $customer_id);

    if (!$stmt->execute()) {
        throw new Exception("Error updating the database: " . $stmt->error);
    }

    return $file_name;
}
public function saveReceipt($booking_id, $customer_id, $file_name) {
    // Query to update the receipt for the specific booking ID
    $query = "UPDATE bookings SET receipt_img = ? WHERE id = ? AND customer_id = ?";
    $stmt = $this->db->prepare($query);
    $stmt->bind_param("ssi", $file_name, $booking_id, $customer_id);

    if (!$stmt->execute()) {
        throw new Exception("Failed to update receipt image in the database: " . $stmt->error);
    }

    $stmt->close();
}

public function cancelBooking($bookingId, $seats, $showtimeId) {
    // Begin transaction
    $this->db->begin_transaction();

    try {
        // Update booking status to 'cancelled'
        $stmt = $this->db->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
        $stmt->bind_param('i', $bookingId);
        $stmt->execute();

        // Release the seats
        $seatIds = explode(',', $seats);
        $placeholders = implode(',', array_fill(0, count($seatIds), '?'));
        $seatQuery = "UPDATE seats SET is_booked = 0, status = 0 WHERE id IN ($placeholders)";
        $stmt = $this->db->prepare($seatQuery);
        $stmt->bind_param(str_repeat('i', count($seatIds)), ...$seatIds);
        $stmt->execute();

        // Commit transaction
        $this->db->commit();

        return true;
    } catch (Exception $e) {
        // Rollback on error
        $this->db->rollback();
        throw $e;
    }
}

public function submitFeedback($customerEmail, $feedbackText)
    {
        $query = "INSERT INTO feedback (customer_email, feedback_text) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        if (!$stmt) {
            return ["success" => false, "message" => "Failed to prepare statement: " . $this->db->error];
        }

        $stmt->bind_param("ss", $customerEmail, $feedbackText);

        if ($stmt->execute()) {
            $stmt->close();
            return ["success" => true, "message" => "Feedback submitted successfully!"];
        } else {
            $stmt->close();
            return ["success" => false, "message" => "Error: " . $this->db->error];
        }
    }
}
?>
