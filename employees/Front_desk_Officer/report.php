<?php

require_once 'bookings.php';
require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Frontdeskofficer') {
    echo "<h1>Access Denied</h1>";
    exit;
}

// // Start the session at the top of the page

// // Debugging the session role
// if (isset($_SESSION['user']['role'])) {
//     echo "User role: " . htmlspecialchars($_SESSION['user']['role']);
// } else {
//     echo "Role not set in session.";
//     exit;
// }

// // Check if the user has an authorized role
// if (!in_array($_SESSION['user']['role'], ['Frontdeskofficer', 'admin'])) {
//     echo "Unauthorized role: " . htmlspecialchars($_SESSION['user']['role']);
//     header("Location: ../login.php");
//     exit;
// }






// Initialize database and bookings class
$db = new Database();
$conn = $db->getConnection();
$movie = new bookings($conn);

// Fetch report data
$reportData = $movie->getReportData();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report</title>
    <link rel="stylesheet" href="./styles/report.css"> <!-- Update the CSS file path -->
    
</head>
<body>
    <div class="report-container">
        <h1>Booking Report</h1>
        <p><strong>Generated On:</strong> <?= date('Y-m-d H:i:s') ?></p>
        
        <table class="report-table">
            <thead>
                <tr>
                    <th>Showtime</th>
                    <th>Movie</th>
                    <th>Room</th>
                    <th>booked Seats</th>
                    <th>Total Revenue</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $reportData->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['showtime']) ?></td>
                        <td><?= htmlspecialchars($row['movie_title']) ?></td>
                        <td><?= htmlspecialchars($row['room_name']) ?></td>
                        <td><?= htmlspecialchars($row['booked_seats']) ?></td>
                        <td>$<?= htmlspecialchars($row['total_revenue']) ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <div class="report-actions">
              <button id="generateReportBtn" class="btn btn-secondary">Generate Report</button>

<!-- Loading indicator (hidden initially) -->
<div id="loading" style="display: none;">Generating report... Please wait.</div>

            <a href="view_report.php" class="btn btn-secondary">View PDF</a>
        </div>
    </div>
     <!-- Button to trigger PDF generation -->
  
<script>
// jQuery for handling the click and AJAX request
$(document).ready(function() {
    $('#generateReportBtn').on('click', function() {
        // Show loading indicator
        $('#loading').show();

        // Send AJAX request to generate the PDF
        $.ajax({
            url: 'generate_report.php', // File that generates the PDF
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    // Successfully generated the report, reload the page
                    location.reload(); // Reload the page to show the new report
                } else {
                    alert('Error generating the report.');
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            },
            complete: function() {
                // Hide loading indicator
                $('#loading').hide();
            }
        });
    });
});
</script>

</body>
</html>
</body>
</html>
