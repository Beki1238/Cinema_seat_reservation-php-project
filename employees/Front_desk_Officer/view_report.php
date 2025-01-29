<?php
session_start();
require_once 'bookings.php'; // Assuming your class is named 'bookings'
require('vendor/autoload.php'); // Assuming you're using Composer for FPDF autoload

require_once 'auth.php'; // Validate session

// Check if the user has the correct role
if ($userRole !== 'Frontdeskofficer') {
    echo "<h1>Access Denied</h1>";
    exit;
}
// Initialize database and bookings class
$db = new Database();
$conn = $db->getConnection();
$booking = new bookings($conn); // 'bookings' class for handling bookings data

// Fetch report data
$reportData = $booking->getReportData(); // Use the class method to get the report data

// Create PDF instance
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Booking Report', 0, 1, 'C');
$pdf->Ln(10);

// Table Header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Showtime', 1);
$pdf->Cell(60, 10, 'Movie', 1);
$pdf->Cell(40, 10, 'Room', 1);
$pdf->Cell(30, 10, 'Seats', 1);
$pdf->Cell(20, 10, 'Revenue', 1);
$pdf->Ln();

// Table Body - Filling in the booking details
$pdf->SetFont('Arial', '', 12);
if ($reportData->num_rows > 0) {
    while ($row = $reportData->fetch_assoc()) {
        // Filling in the data for each booking row
        $pdf->Cell(40, 10, $row['showtime'], 1);
        $pdf->Cell(60, 10, $row['movie_title'], 1);
        $pdf->Cell(40, 10, $row['room_name'], 1);
        $pdf->Cell(30, 10, $row['booked_seats'], 1);
        $pdf->Cell(20, 10, '$' . number_format($row['total_revenue'], 2), 1); // Format the revenue
        $pdf->Ln();
    }
} else {
    $pdf->Cell(190, 10, 'No bookings available for the report.', 1, 1, 'C');
}

// Output the PDF to the browser (inline)
$pdf->Output('I', 'Booking_Report_' . date('Y-m-d') . '.pdf'); // Inline view with dynamic name
exit;
?>
