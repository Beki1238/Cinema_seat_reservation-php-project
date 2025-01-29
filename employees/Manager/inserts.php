<?php

class inserts {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }
    public function getAllMovies() {
        $stmt = $this->db->prepare("SELECT * FROM movies ORDER BY title ASC");
    if (!$stmt) {
        die("Preparation failed: " . $this->db->error);
    }
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    

public function getMovieById($id) {
        $stmt = $this->db->prepare("SELECT * FROM movies WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function saveMovie($data, $files) {
        $title = htmlspecialchars($data['title']);
        $description = htmlspecialchars($data['description']);
        $release_date = $data['release_date']; // Make sure the form field has the correct name

        $duration = (int)$data['duration_hour'] . '.' . round(((int)$data['duration_min'] / 60) * 100, 2);
        $youtube_link = htmlspecialchars($_POST['youtube_link']);
        $cover_img = null;
        if (isset($files['cover']) && $files['cover']['tmp_name'] !== '') {
            $cover_img = time() . '_' . basename($files['cover']['name']);
            move_uploaded_file($files['cover']['tmp_name'], './assets/img/' . $cover_img);
        }
        
        // Get the status (active or inactive)
        $status = isset($data['status']) ? $data['status'] : 'inactive';
    
        if (!empty($data['id'])) {
           // Update query
$stmt = $this->db->prepare("UPDATE movies SET title = ?, description = ?,  duration = ?,release_date = ?, youtube_link = ?, cover_img = IFNULL(?, cover_img), status = ? WHERE id = ?");
$stmt->bind_param("sssssssi", $title, $description, $duration,$release_date, $youtube_link, $cover_img, $status, $data['id']);

        } else {
            // Insert new movie with the status field
            $stmt = $this->db->prepare("INSERT INTO movies (title, description, release_date,  duration, youtube_link, cover_img, status) VALUES ( ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $title, $description,  $duration, $release_date, $youtube_link, $cover_img, $status);
        }
    
        if ($stmt->execute()) {
            return '1'; // Successfully executed
        }
        return $stmt->error; // Return error message if failed
    }
    
   
    public function deleteMovie($id) {
        $stmt = $this->db->prepare("DELETE FROM movies WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute() ? '1' : $stmt->error;
    }
    
     //-------------------------------------showtimes-------------------------------------------------------
    // Get all cinema rooms
    public function getAllactiveMovies() {
        $stmt = $this->db->prepare("SELECT * FROM movies WHERE status = 'active'");
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getAllRooms() {
        $result = $this->db->query("SELECT id, name FROM CinemaRooms");

        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Get a specific room by ID
    public function getRoomById($id) {
        $stmt = $this->db->prepare("SELECT id, name FROM CinemaRooms WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    // Get all showtimes (including movie and room information)
    public function getAllShows() {
        $result = $this->db->query("
            SELECT s.id, m.title AS movie, r.name AS room, s.start_time,s.price
            FROM Showtime s
            JOIN Movies m ON s.movie_id = m.id
            JOIN CinemaRooms r ON s.room_id = r.id
        ");

        $data = [];
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
        }
        return $data;
    }

    // Get a showtime by ID
    public function getShowById($id) {
        $stmt = $this->db->prepare("
            SELECT id, movie_id, room_id, start_time
            FROM Showtime WHERE id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public function saveShowtime($data) {
        if (!isset($data['movie_id']) || !isset($data['room_id']) || !isset($data['start_time']) || !isset($data['price'])) {
            return 'Error: Missing required fields (movie_id, room_id, start_time, price)';
        }
    
        $id = $data['id'] ?? null;
        $movie_id = $data['movie_id'];
        $room_id = $data['room_id'];
        $start_time = $data['start_time'];
        $price = $data['price'];
    
        // Convert start_time to MySQL-compatible datetime format
        $start_time = date('Y-m-d H:i:s', strtotime($start_time));
    
        // Check for conflicts in the same room and time
        if ($this->isShowtimeExists($room_id, $start_time, $id)) {
            return 'Error: Showtime conflict in the same room and time.';
        }
    
        // Check if the room is already occupied by a different movie or showtime
        if ($this->isRoomOccupied($room_id, $movie_id, $id)) {
            return 'Error: The room is occupied by another movie or showtime.';
        }
    
        // Insert or update logic
        if ($id) {
            // Update existing showtime
            $stmt = $this->db->prepare("UPDATE Showtime SET movie_id = ?, room_id = ?, start_time = ?, price = ? WHERE id = ?");
            $stmt->bind_param('iisdi', $movie_id, $room_id, $start_time, $price, $id);
        } else {
            // Insert new showtime
            $stmt = $this->db->prepare("INSERT INTO Showtime (movie_id, room_id, start_time, price) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('iisd', $movie_id, $room_id, $start_time, $price);
        }
    
        if ($stmt->execute()) {
            return 1;
        } else {
            return 'Error: ' . $stmt->error;
        }
    }
    
    // Check if a showtime already exists in the same room at the same time
    public function isShowtimeExists($room_id, $start_time, $exclude_id = null) {
        $query = "SELECT id FROM Showtime WHERE room_id = ? AND start_time = ?";
        $params = [$room_id, $start_time];
        $types = 'is';
    
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }
    
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0;
    }
    
    // Check if a room is occupied by another movie or showtime
    public function isRoomOccupied($room_id, $movie_id, $exclude_id = null) {
        $query = "
            SELECT id 
            FROM Showtime 
            WHERE room_id = ? 
            AND movie_id != ?
        ";
        $params = [$room_id, $movie_id];
        $types = 'ii';
    
        if ($exclude_id) {
            $query .= " AND id != ?";
            $params[] = $exclude_id;
            $types .= 'i';
        }
    
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
    
        return $result->num_rows > 0;
    }
    
    
  
    // Delete a showtime by ID
public function deleteShowtime($id) {
    $stmt = $this->db->prepare("DELETE FROM Showtime WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Execute the delete query
    if ($stmt->execute()) {
        return 1; // Success
    }

 
}

    // Get all movies (needed for the select dropdown in manage_showtime.php)
 

}