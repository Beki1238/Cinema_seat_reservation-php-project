<?php
session_start();
require_once "./includes/Database.php";
require_once './includes/User.php';

$error = "";

// Redirect users based on their roles if already logged in
if (isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'Admin':
            header('Location: ./Admin/Aheader.php');
            exit;
        case 'Manager':
            header('Location: ./manager/mheader.php');
            exit;
        case 'Frontdeskofficer':
            header('Location: ./Front_desk_Officer/fheader.php');
            exit;
        default:
            session_unset();
            session_destroy();
            header("Location: ./login.php");
            exit;
    }
}

// Instantiate the Database and User classes
$db = new Database();
$conn = $db->getConnection();
$user = new User($conn);

// Handle POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emp_id = $_POST['emp_id'] ?? '';
    $password = $_POST['password'] ?? '';

    // Validate and sanitize inputs
    $emp_id = $user->sanitize($emp_id);

    // Check password format
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z]).{8,}$/', $password)) {
        $error = "Password must be at least 8 characters long, and include at least one uppercase and one lowercase letter.";
    } else {
        // Attempt authentication
        $authenticatedUser = $user->authenticate($emp_id, $password);

        if ($authenticatedUser) {
            // Start the session and set variables
            $user->startSession($authenticatedUser);
            $_SESSION['logged_in'] = true;

            switch ($_SESSION['user_role']) {
                case 'Admin':
                    header('Location: ./Admin/Aheader.php');
                    break;
                case 'Manager':
                    header('Location: ./manager/mheader.php');
                    break;
                case 'Frontdeskofficer':
                    header('Location: ./Front_desk_Officer/fheader.php');
                    break;
            }
            exit;
        } else {
            $error = "Invalid Employee ID or password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Staff Login</title>
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="log.css">
</head>
<body>
    <div class="background">
        <div class="shape"></div>
        <div class="shape"></div>
    </div>
    <form method="POST" action="login.php">
        <h3>Staff Login</h3>

        <label for="emp_id">Employee ID</label>
        <input type="text" id="emp_id" name="emp_id" required placeholder="id">

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="password" required 
               minlength="8" pattern="(?=.*[a-z])(?=.*[A-Z]).{8,}" 
               title="Password must be at least 8 characters long, and include at least one uppercase and one lowercase letter."> 

        <button type="submit">Log In</button>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
