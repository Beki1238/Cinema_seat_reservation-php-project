<?php
include '../includes/Database.php';
include 'inserts.php';

 
$db = (new Database())->getConnection();
$movieManager = new inserts($db);

$showManager = new inserts($db);


$action = $_GET['action'] ?? '';

if ($action === 'save_movie') {
    echo $movieManager->saveMovie($_POST, $_FILES);
} elseif ($action === 'delete_movie') {
    echo $movieManager->deleteMovie($_POST['id']);
} else {
    echo 'Invalid action.';
}

if ($action === 'save_showtime') {
    $response = $showManager ->saveShowtime($_POST);
    if ($response === 1) {
        echo '1'; // Success
    } else {
        echo $response; // Error message
    }
} elseif ($action === 'delete_showtime') {
    echo $showManager ->deleteShowtime($_POST['id']);
} else {
    echo 'Invalid action.';
}

?>
