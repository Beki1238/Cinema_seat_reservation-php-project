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

// Get the showtime ID from the URL
$showtime_id = $_GET['showtime_id'] ?? null;
if (!$showtime_id) {
    die("Invalid showtime. Please try again.");
}

// Instantiate Movie class
$movie = new Movie($conn);

// Fetch room and showtime details
$room = $movie->getShowtimeDetails($showtime_id);
if (!$room) {
    die("Room or Showtime not found. Please check the database.");
}

// Fetch seats for the room
$seats = $movie->getSeatsByRoomId($room['room_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Seats</title>
    <link rel="stylesheet" href="./styles/seat.css">
  
</head>
<body>
    <h1>Room: <?= htmlspecialchars($room['room_name']) ?></h1>
    <h2>Select Your Seats</h2>

    <!-- Information Section for Seat Colors -->
    <div class="info-section">
        <h3>Seat Color Definitions:</h3>
        <ul>
            <li><span style="background-color: #4CAF50; color: white; padding: 5px;">Green:</span> Available for selection</li>
            <li><span style="background-color: #9E9E9E; color: white; padding: 5px;">Gray:</span> Temporarily booked (awaiting confirmation)</li>
            <li><span style="background-color: #d32f2f; color: white; padding: 5px;">Red:</span> Fully booked (cannot be selected)</li>
            <li><span style="background-color: #FFD700; color: black; padding: 5px;">Yellow:</span> Selected by you (not confirmed yet)</li>
        </ul>
    </div>

    <p>Base Price per Seat: $<?= htmlspecialchars($room['base_price']) ?></p>
    <p>Showtime Additional Price: $<?= htmlspecialchars($room['showtime_price']) ?></p>
    <p>Showtime Start: <?= htmlspecialchars($room['start_time']) ?></p>

    <form method="POST" action="process_booking.php">
    <div class="cinema-room">
        <!-- Loop through seats -->
        <?php while ($seat = $seats->fetch_assoc()): ?>
            <div class="seat 
                <?php 
                    if ($seat['is_booked'] == 1 && $seat['status'] == 1) {
                        echo 'booked'; // Fully booked seat
                    } elseif ($seat['status'] == 1) {
                        echo 'temporarily-booked'; // Temporarily booked seat
                    }
                ?>" 
                data-seat-id="<?= $seat['id'] ?>" 
                data-base-price="<?= $room['base_price'] ?>" 
                data-showtime-price="<?= $room['showtime_price'] ?>" 
                onclick="selectSeat(this)">
                <?= htmlspecialchars($seat['seat_number']) ?>
            </div>
        <?php endwhile; ?>
    </div>
    <input type="hidden" name="showtime_id" value="<?= htmlspecialchars($showtime_id) ?>">
    <input type="hidden" name="selected_seats" id="selected_seats">
    <input type="hidden" name="total_price" id="total_price">
    <p>Total Price: $<span id="total_price_display">0.00</span></p>
    <button type="submit" class="btn">Book Selected Seats</button>
</form>

    <div id="booking_message" style="display: none; background-color: #222; color: #FFD700; padding: 20px; border-radius: 8px; max-width: 600px; margin: 20px auto; text-align: center;"></div>

<script>
    const selectedSeats = [];
    let totalPrice = 0;

    function selectSeat(seatElement) {
        if (seatElement.classList.contains('booked') || seatElement.classList.contains('temporarily-booked')) return;

        const seatId = seatElement.getAttribute('data-seat-id');
        const basePrice = parseFloat(seatElement.getAttribute('data-base-price'));
        const showtimePrice = parseFloat(seatElement.getAttribute('data-showtime-price'));
        const seatPrice = basePrice + showtimePrice;

        if (selectedSeats.includes(seatId)) {
            // Deselect seat
            selectedSeats.splice(selectedSeats.indexOf(seatId), 1);
            seatElement.classList.remove('selected');
            totalPrice -= seatPrice;
        } else {
            // Select seat
            selectedSeats.push(seatId);
            seatElement.classList.add('selected');
            totalPrice += seatPrice;
        }

        // Update total price display
        document.getElementById('total_price_display').textContent = totalPrice.toFixed(2);
    }

    document.querySelector('form').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent traditional form submission

    // Disable the submit button to prevent multiple submissions
    const submitButton = document.querySelector('button[type="submit"]');
    submitButton.disabled = true;

    // Prepare data for submission
    const showtimeId = <?= htmlspecialchars($showtime_id) ?>;
    const messageContainer = document.getElementById('booking_message');
    
    const formData = new FormData();
    formData.append('showtime_id', showtimeId);
    formData.append('selected_seats', selectedSeats.join(',')); // seats joined as a comma-separated string
    formData.append('total_price', totalPrice.toFixed(2));

    // Send data via AJAX
    fetch('process_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        // Show booking confirmation message
        messageContainer.style.display = 'block';
        messageContainer.innerHTML = `
            <div style='background-color: #222; color: #FFD700; padding: 20px; border-radius: 8px; max-width: 600px; margin: auto; text-align: center;'>
                <h2>Booking Successful!</h2>
                <p>Your seat(s) have been temporarily reserved. To confirm your booking, please make the payment using one of the following methods and send the receipt photo in the provided form in the my bookings page:</p>
                <ul style='text-align: left; margin: 20px auto; display: inline-block;'>
                    <li><strong>CBE:</strong> 100030406177</li>
                    <li><strong>Telebirr:</strong> 0915555555</li>
                    <li><strong>Ebirr:</strong> 0912555555</li>
                </ul>
                <p>Once payment is completed and the receipt is sent, go to the <strong>My Bookings</strong> page (accessible by clicking your name on the header of the homepage) to check the status and await confirmation.</p>
                <a href='main.php' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #FFD700; color: #000; border-radius: 5px; text-decoration: none;'>Return to Home</a>
            </div>
        `;

        // Reset seat selection
        selectedSeats.forEach(seatId => {
            const seatElement = document.querySelector(`[data-seat-id="${seatId}"]`);
            seatElement.classList.remove('selected');
        });
        selectedSeats.length = 0;
        totalPrice = 0;
        document.getElementById('total_price_display').textContent = totalPrice.toFixed(2);
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing your booking. Please try again.');
    })
    .finally(() => {
        // Re-enable the submit button after the request is complete
        submitButton.disabled = false;
    });
});


</script>

</body>
</html>
