<?php
session_start();
include '../includes/Database.php'; // Include the Database class

// Check if user is logged in and came through the login flow
if (!isset($_SESSION['user_id']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Create a database instance
$db = new Database();
$conn = $db->getConnection(); // Get the database connection

// Get the logged-in user ID from session
$user_id = $_SESSION['user_id'];

// Fetch user data from either `employee` or `TH_admin` table
$sql = "
    SELECT emp_id, full_Name, phone, email, role 
    FROM (
        SELECT emp_id, full_Name, phone, email, role FROM employee
        UNION ALL
        SELECT emp_id, full_Name, phone, email, role FROM TH_admin
    ) AS combined_tables
    WHERE emp_id = ?
";

// Prepare and execute the statement
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $user_id); // Bind the user ID
$stmt->execute();
$result = $stmt->get_result();

// Check if user exists
if ($result->num_rows > 0) {
    $user_data = $result->fetch_assoc(); // Fetch user data
} else {
    // If no user is found, destroy session and redirect to login
    session_unset();
    session_destroy();
    header('Location: ../login.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <title>Profile | TH Cinema Ticket System</title>
    
    <!-- Favicon -->
    <link rel="shortcut icon" type="image/x-icon" href="../includes/assets/img/favicon.png">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../includes/assets/css/bootstrap.min.css">
    
    <!-- Fontawesome CSS -->
    <link rel="stylesheet" href="../includes/assets/css/font-awesome.min.css">
    
    <!-- Main CSS -->
    <link rel="stylesheet" href="../includes/assets/css/style.css">
</head>
<body>
    <!-- Page Wrapper -->
    <div class="page-wrapper">
        <div class="content container-fluid">
            <!-- Page Header -->
            <div class="page-header">
                <div class="row">
                    <div class="col">
                        <h3 class="page-title">Profile</h3>
                    </div>
                </div>
            </div>
            <!-- /Page Header -->

            <div class="row">
                <div class="col-md-12">
                    <div class="profile-header">
                        <div class="row align-items-center">
                            <div class="col ml-md-n2 profile-user-info">
                                <h4 class="user-name mb-2 text-uppercase">
                                    <i class="fa fa-id-badge" aria-hidden="true"></i> 
                                    <?php echo htmlspecialchars($user_data['emp_id']); ?>
                                </h4>
                                <div class="about-text"><?php echo htmlspecialchars($user_data['role']); ?></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="tab-content profile-tab-cont">
                        <!-- Personal Details Tab -->
                        <div class="tab-pane fade show active" id="per_details_tab">
                            <div class="row">
                                <div class="col-lg-9">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Name</p>
                                                <p class="col-sm-9"><?php echo htmlspecialchars($user_data['full_Name']); ?></p>
                                            </div>
                                            <div class="row">
                                                <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Email</p>
                                                <p class="col-sm-9"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                            </div>
                                            <div class="row">
                                                <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Role</p>
                                                <p class="col-sm-9"><?php echo htmlspecialchars($user_data['role']); ?></p>
                                            </div>
                                            <div class="row">
                                                <p class="col-sm-3 text-muted text-sm-right mb-0 mb-sm-3">Phone</p>
                                                <p class="col-sm-9"><?php echo htmlspecialchars($user_data['phone']); ?></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /Personal Details Tab -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /Page Wrapper -->

    <!-- jQuery -->
    <script src="../includes/assets/js/jquery-3.2.1.min.js"></script>
    
    <!-- Bootstrap Core JS -->
    <script src="../includes/assets/js/popper.min.js"></script>
    <script src="../includes/assets/js/bootstrap.min.js"></script>
    
    <!-- Custom JS -->
    <script src="../includes/assets/js/script.js"></script>
</body>
</html>
