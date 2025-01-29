<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to check for logged-in status
session_start();
Include "sessionManager.php";
// Include the Database and Movie class files

include 'movies.php';

// Redirect to signin.php if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}

// Create Database connection
$db = new Database();
$conn = $db->getConnection();

// Get the movie ID from the URL
$movie_id = $_GET['id'] ?? null;
if (!$movie_id) {
    die("Invalid movie selection. Please try again.");
}

// Create Movie object
$movie = new Movie($conn);

// Fetch the selected movie details
$movieDetails = $movie->getMovieById($movie_id);

if (!$movieDetails) {
    die("Movie not found.");
}

// Fetch available showtimes for the selected movie
$showtimes = $movie->getShowtimesByMovieId($movie_id);

$showtime_message = null;
if ($showtimes->num_rows === 0) {
    $showtime_message = "No showtimes available for this movie yet. Please check back later.";
}

// Fetch all rooms available in the cinema
$rooms = $movie->getAllRooms();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Room - Triangle Cinema</title>
    <link rel="stylesheet" href="../Cu/styles/style.css"> <!-- Your CSS file -->
    <link rel="stylesheet" href="../Cu/styles/ro.css"> <!-- Your CSS file -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"> <!-- Swiper CSS -->
    <style>
        
        .container {
            max-width: 100%;
            margin: 3rem auto;
            padding: 1rem;
            background: url(../../employees/Manager/assets/img/theater-bg1.jpg) no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }
    </style>
</head>
<body>
  

    <!-- Room Selection Section -->
    <div class="container">
        <h1>Rooms Showing Movie: <?= htmlspecialchars($movieDetails['title']); ?>, please select the room  to proceed</h1>

        <?php if ($showtime_message): ?>
            <div class="no-showtime-message">
                <?= htmlspecialchars($showtime_message); ?>
            </div>
        <?php else: ?>
            <div class="showtime-list">
                <?php while ($row = $showtimes->fetch_assoc()): ?>
                    <div class="showtime-item">
                        <div class="showtime-details">
                            <h3><?= htmlspecialchars($row['room_name']) ?></h3>
                            <p>Showtime: <?= htmlspecialchars($row['start_time']) ?></p>
                            <p>Base Price per Seat: $<?= htmlspecialchars($row['base_price']) ?></p>
                            <p>Additional Showtime Price: $<?= htmlspecialchars($row['showtime_price']) ?></p>
                        </div>
                        <a href="select_seats.php?showtime_id=<?= htmlspecialchars($row['showtime_id']) ?>" class="btn">Select</a>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>

   

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <!-- Your custom JS -->
    <script src="../Cu/scripts/script.js"></script>
</body>
</html>
