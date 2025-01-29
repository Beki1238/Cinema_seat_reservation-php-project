<?php


// Check if the user is logged in and has the correct role
// if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Frontdeskofficer') {
//     header('Location: ../login.php');
//     exit;
// }
require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Frontdeskofficer') {
    echo "<h1>Access Denied</h1>";
    exit;
}
require_once 'bookings.php';
$db = new Database();
$conn = $db->getConnection();
$movie = new bookings($conn);

// Handle Approve/Reject Actions
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? null;
    $action = $_POST['action'] ?? null;

    if ($booking_id && in_array($action, ['approve', 'reject'])) {
        $result = $movie->updateBookingStatus($booking_id, $action);
        $message = $result ? "Booking $action successfully." : "Failed to update booking.";
    } else {
        $message = "Invalid request.";
    }
}

// Fetch all bookings
$bookings = $movie->getAllBookings();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> Manage Bookings</title>
    <link rel="stylesheet" href="./styles/ma.css">
  
</head>
<body>
    <h1> Bookings</h1>
 <?php if ($bookings->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Booking ID</th>
                    <th>Movie</th>
                    <th>Customer Details</th>
                    <th>Showtime</th>
                    <th>Seats</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                  
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['booking_id']) ?></td>
                        <td><?= htmlspecialchars($row['movie_title']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?> (<?= htmlspecialchars($row['customer_email']) ?>)</td>
                        <td><?= htmlspecialchars($row['showtime']) ?></td>
                        <td><?= htmlspecialchars($row['selected_seats']) ?></td>
                        <td>$<?= htmlspecialchars($row['total_price']) ?></td>
                        <td><?= ucfirst(htmlspecialchars($row['status'])) ?></td>
                        <td>
                            <?php if ($row['receipt_img']): ?>
                                <a href="uploads/<?= htmlspecialchars($row['receipt_img']) ?>" target="_blank">View Receipt</a>
                            <?php else: ?>
                                <span>No receipt uploaded</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST">
                                <input type="hidden" name="booking_id" value="<?= htmlspecialchars($row['booking_id']) ?>">
                                <button type="submit" name="action" value="approve">Approve</button>
                                <button type="submit" name="action" value="reject">Reject</button>
                            </form>
                        </td>
    
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No bookings available for approval/rejection.</p>
    <?php endif; ?>
    <script>
document.addEventListener('click', function (event) {
    if (event.target.classList.contains('cancel-booking')) {
        const bookingId = event.target.getAttribute('data-booking-id');
        const selectedSeats = event.target.getAttribute('data-seats');
        const showtimeId = event.target.getAttribute('data-showtime-id');

        if (!confirm('Are you sure you want to cancel this booking?')) {
            return;
        }

        const formData = new FormData();
        formData.append('booking_id', bookingId);
        formData.append('selected_seats', selectedSeats);
        formData.append('showtime_id', showtimeId);

        fetch('cancel_booking.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'Success') {
                alert('Booking cancelled successfully.');
                location.reload();
            } else {
                alert(data); // Show error message from server
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while canceling the booking.');
        });
    }
});
</script>
</body>
</html>
