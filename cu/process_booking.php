<?php
session_start();

// Redirect to signin.php if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit;
}

// Include Movie class
require_once 'movies.php';

// Create Database connection
$db = new Database();
$conn = $db->getConnection();

// Create an instance of the Movie class
$movie = new Movie($conn);

// Fetch POST data from select_seat.php
$showtime_id = $_POST['showtime_id'] ?? null;
$selected_seats = $_POST['selected_seats'] ?? '';
$total_price = $_POST['total_price'] ?? 0;

// Validate inputs
if (!$showtime_id || empty($selected_seats) || !$total_price) {
    die("Invalid booking request.");
}

// Get the user ID from session
$user_id = $movie->getUserId();
if (!$user_id) {
    die("User is not logged in. Please log in to proceed.");
}

// Convert the selected seats into an array (comma separated)
$seat_ids = explode(',', $selected_seats);

// Begin transaction
$conn->begin_transaction();

try {
    // Step 1: Generate a unique booking ID
    $booking_id = $movie->generateBookingId();

    // Step 2: Insert the booking record into the database
    $booking_successful = $movie->insertBooking($booking_id, $user_id, $showtime_id, $total_price);

    if (!$booking_successful) {
        throw new Exception("Failed to insert booking record.");
    }

    // Step 3: Link selected seats to the booking and update seat statuses to 'temporarily booked'
    $seats_linked = $movie->linkSeatsToBooking($booking_id, $seat_ids);

    if (!$seats_linked) {
        throw new Exception("Failed to link seats to the booking.");
    }

    // Commit the transaction
    $conn->commit();

    // Send success response (AJAX response for the confirmation message)
    echo "Booking successful. Your seats are temporarily reserved. Please make the payment to confirm.";

} catch (Exception $e) {
    // Rollback the transaction on failure
    $conn->rollback();

    // Log the error and send the error response
    error_log($e->getMessage());
    echo "An error occurred while processing your booking. Please try again.";
}
?>
