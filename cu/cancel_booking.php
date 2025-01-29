<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session to check for logged-in status
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

// Include the database and Movie class
require_once 'movies.php';
$db = new Database();
$conn = $db->getConnection();
$movie = new Movie($conn);

// Get data from the POST request
$booking_id = $_POST['booking_id'] ?? null;
$showtime_id = $_POST['showtime_id'] ?? null;
$selected_seats = $_POST['selected_seats'] ?? null;

// Validate required parameters
if (!$booking_id || !$showtime_id || !$selected_seats) {
    echo json_encode(['success' => false, 'message' => 'Invalid input.']);
    exit();
}

try {
    // Begin a transaction to ensure data consistency
    $conn->begin_transaction();

    // Step 1: Delete the `booked_seats` entries for the given booking
    $deleteBookedSeatsSQL = "DELETE FROM booked_seats WHERE booking_id = ?";
    $deleteStmt = $conn->prepare($deleteBookedSeatsSQL);
    $deleteStmt->bind_param('i', $booking_id);
    $deleteStmt->execute();

    if ($deleteStmt->affected_rows <= 0) {
        throw new Exception('Failed to delete booked seats.');
    }

    // Step 2: Update the `seats` table to mark the seats as available
    $seatNumbers = explode(',', $selected_seats); // Split seat numbers into an array
    $updateSeatSQL = "UPDATE seats SET status = 0 WHERE seat_number = ?";
    $updateStmt = $conn->prepare($updateSeatSQL);

    foreach ($seatNumbers as $seatNumber) {
        $seatNumber = trim($seatNumber); // Trim any extra whitespace
        $updateStmt->bind_param('s', $seatNumber);
        $updateStmt->execute();

        if ($updateStmt->affected_rows <= 0) {
            throw new Exception("Failed to update seat status for seat: $seatNumber.");
        }
    }

    // Step 3: Delete the booking itself
    $deleteBookingSQL = "DELETE FROM bookings WHERE id = ?";
    $deleteBookingStmt = $conn->prepare($deleteBookingSQL);
    $deleteBookingStmt->bind_param('i', $booking_id);
    $deleteBookingStmt->execute();

    if ($deleteBookingStmt->affected_rows <= 0) {
        throw new Exception('Failed to delete booking.');
    }

    // Commit the transaction
    $conn->commit();

    // Send success response
    echo json_encode(['success' => true, 'message' => 'Booking canceled successfully.']);
  
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();

    // Send error response
    echo json_encode(['success' => false, 'message' => 'Failed to cancel booking: ' . $e->getMessage()]);
} finally {
    // Close all prepared statements if defined
    if (isset($deleteStmt)) $deleteStmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($deleteBookingStmt)) $deleteBookingStmt->close();
    $conn->close();
}
