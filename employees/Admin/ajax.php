<?php 
include '../includes/Database.php';
include 'inserts.php';
 
$db = (new Database())->getConnection();
$employee = new inserts($db);
$cinema = new inserts($db);

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action == 'save_employee') {
        error_log('Save Employee POST data: ' . print_r($_POST, true));
        $result = $employee->saveEmployee($_POST);
        error_log('Save Employee Result: ' . $result);
        echo $result;
    }
     elseif ($action == 'delete_employee') {
        echo $employee->deleteEmployee($_POST['emp_id']);
    }
}

if ($_GET['action'] == 'save_theater') {
    echo $cinema->saveRoom($_POST);
}

if ($_GET['action'] == 'delete_theater') {
    echo $cinema->deleteRoom($_POST['id']);
}
?>
