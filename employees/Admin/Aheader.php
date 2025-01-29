<?php
session_start();


// Prevent browser caching of protected pages
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirect if the user is not logged in or bypassed the login flow
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || !isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: ../login.php');
    exit;
}

// Check for inactivity timeout
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $_SESSION['timeout']) {
  // Destroy the session and redirect to login
  session_unset();
  session_destroy();
  header('Location: ../login.php?error=session_timeout');
  exit;
}

// Update the last activity time
$_SESSION['last_activity'] = time();
// Get the user's role
$userRole = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Dashboard - Movie Ticket Reservation System</title>
  
  <!-- Link to Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <link rel="stylesheet" href="../includes/head.css">
  <!-- AdminLTE and Bootstrap CSS -->
  <link rel="stylesheet" href="https://adminlte.io/themes/v3/dist/css/adminlte.min.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://adminlte.io/themes/v3/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  
  <style>
     
  </style>
</head>
<body class="hold-transition sidebar-mini">
  <div class="wrapper">

    <!-- Navbar -->
    <nav class="main-header navbar navbar-expand navbar-white navbar-light">
      <ul class="navbar-nav">
        <li class="nav-item">
          <!-- Sidebar Toggle Button -->
          <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
        </li>
      </ul>

      <!-- User Role Display -->
      <div class="navbar-nav ml-auto">
        <span class="navbar-text">
          <?php echo htmlspecialchars($userRole); ?>
        </span>
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="userDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          </button>
          <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
    <a id="profileLink" class="dropdown-item" href="#">Profile</a>
    <a class="dropdown-item" href="logout.php">Logout</a>
</div>
        </div>
      </div>
    </nav>
    <!-- /.navbar -->

    <!-- Main Sidebar Container -->
    <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <a href="#" class="brand-link">
        <div class="brand-logo">
          <!-- Logo here -->
          <img src="../includes/img/logo.png" alt="TH Movie Ticket System Logo" class="brand-image">
          <span class="brand-text font-weight-light">TH Cinema Ticket System</span>
        </div>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
          <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
            <?php if ($userRole === 'Admin'): ?>
              <li class="nav-item">
                <a href="?page=Manage-employe" class="nav-link">
                  <i class="nav-icon fas fa-user-plus"></i>
                  <p>Manage Employee</p>
                </a>
              </li>

              <li class="nav-item">
                <a href="?page=add-theaters" class="nav-link">
                  <i class="nav-icon fas fa-theater-masks"></i>
                  <p>Add Theaters</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="?page=feedback" class="nav-link">
                  <i class="nav-icon fas fa-comments"></i>
                  <p>Customer Feedback</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="?page=report" class="nav-link">
                  <i class="nav-icon fas fa-calendar-alt"></i>
                  <p>view report</p>
                </a>
              </li>

            
            <?php endif; ?>
          </ul>
        </nav>
      </div>
      <!-- /.sidebar -->
    </aside>

    <!-- Content Wrapper -->
    <div class="content-wrapper">
      <section class="content">
        <div class="container-fluid">
          <?php
          // Render different sections based on the 'page' query parameter and user role
          if (isset($_GET['page'])) {
              switch ($_GET['page']) {
                  case 'Manage-employe':
                      if ($userRole === 'Admin') {
                        include 'employee_list.php'; 
                      } else {
                          echo "<h2>Access Denied</h2>";
                      }
                      break;
                  case 'add-theaters':
                      if ($userRole === 'Admin') {
                          include 'theater.php';
                      } else {
                          echo "<h2>Access Denied</h2>";
                      }
                      break;
                  case 'feedback':
                      if ($userRole === 'Admin') {
                          include 'feedback.php';
                      } else {
                          echo "<h2>Access Denied</h2>";
                      }
                      break;
                  case 'report':
                      if ($userRole === 'Admin') {
                          include 'view_re.php';
                      } else {
                          echo "<h2>Access Denied</h2>";
                      }
                      break;
                  default:
                      echo "<h1>Welcome to the Dashboard</h1>";
                      break;
              }
          } else {
              echo "<h1>Welcome to the Admin Dashboard</h1>";
          }
          ?>
        </div>
      </section>
    </div>
    <!-- /.content-wrapper -->
  </div>
  <!-- ./wrapper -->

  <!-- AdminLTE Scripts -->
  <script src="https://adminlte.io/themes/v3/dist/js/adminlte.js"></script>
  <script>
    // Sidebar Toggle
    $(document).ready(function() {
        $('[data-widget="pushmenu"]').PushMenu();
    });
  
$(document).ready(function () {
    // Load profile page content dynamically
    $('#profileLink').on('click', function (e) {
        e.preventDefault(); // Prevent default link behavior
        $('.content-wrapper').load('profile.php', function (response, status, xhr) {
            if (status === "error") {
                console.error("Error loading profile: " + xhr.status + " " + xhr.statusText);
                $('.content-wrapper').html('<h2>Error loading profile content. Please try again.</h2>');
            }
        });
    });
});
let inactivityTime = function () {
        let time;
        // Reset the timer when activity is detected
        window.onload = resetTimer;
        document.onmousemove = resetTimer;
        document.onkeypress = resetTimer;
        document.onscroll = resetTimer;

        function logout() {
            alert("You have been logged out due to inactivity.");
            window.location.href = "../login.php?error=session_timeout"; // Redirect to login page
        }

        function resetTimer() {
            clearTimeout(time);
            time = setTimeout(logout, 15 * 60 * 1000); // 15 minutes
        }
    };

    inactivityTime();



  </script>
</body>
</html>
