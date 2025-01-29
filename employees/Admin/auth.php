<?php
// auth.php


// Redirect if the user is not logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: ../login.php');
    exit;
}

// Store the user role for use in the file
$userRole = $_SESSION['user_role'];
?>
