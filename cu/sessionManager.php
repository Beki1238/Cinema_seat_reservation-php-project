<?php


// Check if the user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    session_unset();
    session_destroy();
    header('Location: signin.php?action=Sign%20In&error=unauthorized'); // Redirect to login page
    exit();
}

// Handle session timeout (15 minutes of inactivity)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 900)) { // 15 minutes
    session_unset();
    session_destroy();
    header('Location: signin.php?action=Sign%20In&error=session_timeout'); // Redirect to login page
    exit();
}

// Update last activity time
$_SESSION['last_activity'] = time();
?>
