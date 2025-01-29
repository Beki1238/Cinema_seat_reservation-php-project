<?php
include '../includes/Database.php';

class bookings {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function getAllBookings() {
        $query = "
            SELECT 
                bookings.id AS booking_id, 
                bookings.total_price, 
                bookings.status, 
                bookings.payment_status, 
                bookings.receipt_img, 
                GROUP_CONCAT(seats.seat_number SEPARATOR ', ') AS selected_seats, 
                showtime.start_time AS showtime, 
                movies.title AS movie_title, 
                customers.name AS customer_name, 
                customers.email AS customer_email 
            FROM bookings
            JOIN booked_seats ON bookings.id = booked_seats.booking_id
            JOIN seats ON booked_seats.seat_id = seats.id
            JOIN showtime ON bookings.showtime_id = showtime.id
            JOIN movies ON showtime.movie_id = movies.id
            JOIN customers ON bookings.customer_id = customers.id
            GROUP BY bookings.id
        ";

        return $this->conn->query($query);
    }

    public function updateBookingStatus($booking_id, $action) {
        try {
            if ($action === 'approve') {
                // Approve the booking
                $update_booking_query = "
                    UPDATE bookings 
                    SET status='confirmed', payment_status='paid' 
                    WHERE id=?
                ";
                $stmt = $this->conn->prepare($update_booking_query);
                $stmt->bind_param("s", $booking_id);
                $stmt->execute();

                // Update the seats to mark as booked
                $update_seats_query = "
                    UPDATE seats 
                    JOIN booked_seats ON seats.id = booked_seats.seat_id 
                    SET seats.is_booked=1 
                    WHERE booked_seats.booking_id=?
                ";
                $stmt = $this->conn->prepare($update_seats_query);
                $stmt->bind_param("s", $booking_id);
                $stmt->execute();
            } elseif ($action === 'reject') {
                // Reject the booking
                $update_booking_query = "
                    UPDATE bookings 
                    SET status='cancelled', payment_status='failed' 
                    WHERE id=?
                ";
                $stmt = $this->conn->prepare($update_booking_query);
                $stmt->bind_param("s", $booking_id);
                $stmt->execute();

                // Reset the seats to not booked
                $reset_seats_query = "
                    UPDATE seats 
                    JOIN booked_seats ON seats.id = booked_seats.seat_id 
                    SET seats.is_booked=0 
                    WHERE booked_seats.booking_id=?
                ";
                $stmt = $this->conn->prepare($reset_seats_query);
                $stmt->bind_param("s", $booking_id);
                $stmt->execute();
            }
            return true;
        } catch (Exception $e) {
            error_log("Error updating booking status: " . $e->getMessage());
            return false;
        }
    }

    // Fetch report data
    public function getReportData() {
        $query = "
           SELECT 
    showtime.start_time AS showtime, 
    movies.title AS movie_title, 
    cinemarooms.name AS room_name,
    GROUP_CONCAT(seats.seat_number SEPARATOR ', ') AS booked_seats,
    SUM(bookings.total_price) AS total_revenue
FROM bookings
JOIN booked_seats ON bookings.id = booked_seats.booking_id
JOIN seats ON booked_seats.seat_id = seats.id
JOIN showtime ON bookings.showtime_id = showtime.id
JOIN movies ON showtime.movie_id = movies.id
JOIN cinemarooms ON showtime.room_id = cinemarooms.id
GROUP BY bookings.showtime_id
ORDER BY showtime.start_time;

        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->get_result();
    }
}
