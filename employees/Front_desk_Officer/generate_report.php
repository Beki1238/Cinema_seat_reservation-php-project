<?php
session_start();
require_once 'bookings.php';
require 'vendor/autoload.php';

// Ensure the user is logged in and authorized
// if (!isset($_SESSION['user']) || !in_array($_SESSION['user']['role'], ['Frontdeskofficer', 'admin'])) {
//     header("Location: ../login.php");
//     exit;
// }

// Initialize database and movie class
$db = new Database();
$conn = $db->getConnection();
$movie = new bookings($conn);

// Fetch report data
$reportData = $movie->getReportData();

// Create the report name
$report_name = 'Booking_Report_' . time() . '.pdf';

// Create PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Booking Report', 0, 1, 'C');
$pdf->Ln(10);

// Table header
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(40, 10, 'Showtime', 1);
$pdf->Cell(60, 10, 'Movie', 1);
$pdf->Cell(40, 10, 'Room', 1);
$pdf->Cell(30, 10, 'Seats', 1);
$pdf->Cell(20, 10, 'Revenue', 1);
$pdf->Ln();

// Table data
$pdf->SetFont('Arial', '', 12);
while ($row = $reportData->fetch_assoc()) {
    $pdf->Cell(40, 10, $row['showtime'], 1);
    $pdf->Cell(60, 10, $row['movie_title'], 1);
    $pdf->Cell(40, 10, $row['room_name'], 1);
    $pdf->Cell(30, 10, $row['booked_seats'], 1);
    $pdf->Cell(20, 10, '$' . $row['total_revenue'], 1);
    $pdf->Ln();
}

// Save the file to the reports directory
$report_path = 'reports/' . $report_name;
$pdf->Output('F', $report_path);  // Save the file to the server

// Return the path of the saved file for confirmation
echo json_encode(['success' => true, 'reportPath' => $report_path]);
exit;
