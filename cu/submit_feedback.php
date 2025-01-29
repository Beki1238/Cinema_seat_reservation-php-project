<?php
session_start();
require_once '../employees/includes/Database.php'; // Adjust the path if needed
Include "sessionManager.php";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ensure the user is logged in
    if (!isset($_SESSION['user'])) {
        header("Location: signin.php");
        exit();
    }

    $email = $_SESSION['user']['email']; // Get logged-in user's email
    $feedback = $_POST['feedback'] ?? ''; // Get feedback from the form

    if (!empty($feedback)) {
        $database = new Database();
        $db = $database->getConnection();

        $query = $db->prepare("INSERT INTO feedbacks (email, feedback) VALUES (?, ?)");
        $query->bind_param('ss', $email, $feedback);

        if ($query->execute()) {
            // On success, send a JavaScript alert and reload the page
            echo "<script>
                alert('Thank you for your feedback!');
                window.location.href = 'main.php'; // Redirect back to the main page
            </script>";
            exit();
        } else {
            echo "<script>
                alert('Failed to submit feedback. Please try again.');
                window.location.href = 'main.php';
            </script>";
        }
    } else {
        echo "<script>
            alert('Feedback cannot be empty.');
            window.location.href = 'main.php';
        </script>";
    }
} else {
    header("Location: main.php"); // Redirect to homepage if accessed directly
    exit();
}
?>
