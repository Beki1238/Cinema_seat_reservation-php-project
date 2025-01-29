<?php
session_start();

// Redirect to signin.php if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit;
}

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the Movie class and initialize it
require_once 'movies.php';
$db = new Database();
$conn = $db->getConnection();
$movie = new Movie($conn);

// Get the logged-in user's ID
$customer_id = $_SESSION['user']['id'];

// Fetch user details
$user = $movie->getUserDetails($customer_id);

// Fetch the bookings for the logged-in user
$bookings = $movie->getUserBookings($customer_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link rel="stylesheet" href="../cu/styles/my.css">
</head>
<body>
    <a href="index.php" class="logo">
        <i class='bx bxs-movie'></i>Movies
    </a>
    <div class="bx bx-menu" id="menu-icon"></div>
    <ul class="navbar">
        <li><a href="main.php" class="home-active"> &lt;= Back Home</a></li>
    </ul>
    <h1>My Bookings</h1>

    <?php if ($bookings->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Movie</th>
                    <th>Name</th>
                    <th>booking number</th>
                    <th>Showtime</th>
                    <th>Seats</th>
                    <th>Total Price</th>
                    <th>Booking Status</th>
                    <th>Receipt</th>
                    <th>Cancel Booking</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $bookings->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['movie_title']) ?></td>
                        <td><?= htmlspecialchars($row['customer_name']) ?></td>
                        <td><?= htmlspecialchars($row['booking_id']) ?></td>
                        <td><?= htmlspecialchars($row['showtime']) ?></td>
                        <td><?= htmlspecialchars($row['selected_seats']) ?></td>
                        <td>$<?= htmlspecialchars($row['total_price']) ?></td>
                        <td class="status-<?= strtolower($row['status']) ?>">
                            <?= ucfirst($row['status']) ?>
                        </td>
                        <td>
    <?php if (isset($row['receipt_img']) && $row['receipt_img']): ?>
        <?php 
        $receipt_path = 'uploads/' . $row['receipt_img'];
        if (file_exists($receipt_path)): ?>
            <a href="<?= htmlspecialchars($receipt_path) ?>" target="_blank">
                <img src="<?= htmlspecialchars($receipt_path) ?>" alt="Receipt Image" style="width: 100px; height: auto;">
            </a>
        <?php else: ?>
            <span>Receipt image not available.</span>
        <?php endif; ?>
    <?php else: ?>
        <form 
            method="POST" 
            action="upload_receipt.php" 
            enctype="multipart/form-data" 
            class="upload-form">
            <input type="hidden" name="booking_id" value="<?= $row['booking_id'] ?>">
            <input type="file" name="receipt" accept="image/*" required>
            <button type="submit">Upload</button>
        </form>
    <?php endif; ?>
</td>
<td>
     <!-- Disable the cancel button if the booking status is 'confirmed' -->
     <?php if (strtolower($row['status']) === 'confirmed'): ?>
                    <button class="cancel-button" disabled>Cancel</button>
                <?php else: ?>
<button 
        class="cancel-booking" 
        data-booking-id="<?= htmlspecialchars($row['booking_id'] ?? '') ?>" 
        data-seats="<?= htmlspecialchars($row['selected_seats'] ?? '') ?>" 
        data-showtime-id="<?= htmlspecialchars($row['booking_showtime_id'] ?? '') ?>">
        Cancel
    </button>
    <?php
// Debugging output
//var_dump($row['booking_id'], $row['selected_seats'], $row['booking_showtime_id']);
?>

<?php endif; ?>

</td>


                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>You have no bookings yet.</p>
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
    //     <button 
    //     class="cancel-booking" 
    //     data-booking-id="<?= htmlspecialchars($row['booking_id'] ?? '') ?>" 
    //     data-seats="<?= htmlspecialchars($row['selected_seats'] ?? '') ?>" 
    //     data-showtime-id="<?= htmlspecialchars($row['booking_showtime_id'] ?? '') ?>">
    //     Cancel
    // </button>

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
