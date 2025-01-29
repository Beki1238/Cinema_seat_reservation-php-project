<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
    header('Location: login.php');
    exit;
}
// Get the user's role
$userRole = $_SESSION['user_role'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- CSS -->
    <link rel="stylesheet" href="../includes/head.css">

    <!-- Boxicons CSS -->
    <link href='https://unpkg.com/boxicons@2.1.1/css/boxicons.min.css' rel='stylesheet'>

    <!-- Google Font -->
    <style>
        <?php include "styles.css"; ?>
    </style>
</head>

<body>
    <!-- Sidebar -->
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <img src="../includes/img/logo.png" alt="Logo">
                </span>
                <div class="text logo-text">
                    <span class="name">TH Movie System</span>
                    <span class="profession"><?php echo htmlspecialchars($userRole); ?></span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <ul class="menu-links">
                    <?php if ($userRole === 'Admin'): ?>
                        <li class="nav-link">
                            <!-- Link to load the "Manage Employees" dynamically -->
                            <a href="#" onclick="loadPage('../Admin/employee_list.php')">
                                <i class='bx bx-user-plus icon'></i>
                                <span class="text nav-text">Manage Employees</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="#" onclick="loadPage('add-theaters.php')">
                                <i class='bx bx-bar-chart-alt-2 icon'></i>
                                <span class="text nav-text">Add Theaters</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="#" onclick="loadPage('feedback.php')">
                                <i class='bx bx-comment icon'></i>
                                <span class="text nav-text">Feedback</span>
                            </a>
                        </li>
                    <?php elseif ($userRole === 'Manager'): ?>
                        <li class="nav-link">
                            <a href="#" onclick="loadPage('manage-schedule.php')">
                                <i class='bx bx-calendar icon'></i>
                                <span class="text nav-text">Manage Movies</span>
                            </a>
                        </li>
                    <?php elseif ($userRole === 'front_desk officer'): ?>
                        <li class="nav-link">
                            <a href="#" onclick="loadPage('view-schedule.php')">
                                <i class='bx bx-calendar icon'></i>
                                <span class="text nav-text">View Schedule</span>
                            </a>
                        </li>
                        <li class="nav-link">
                            <a href="#" onclick="loadPage('manage-tickets.php')">
                                <i class='bx bx-ticket icon'></i>
                                <span class="text nav-text">Manage Tickets</span>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="bottom-content">
                <li>
                    <a href="../login.php">
                        <i class='bx bx-log-out icon'></i>
                        <span class="text nav-text">Logout</span>
                    </a>
                </li>

                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Dark mode</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </div>
        </div>
    </nav>

    <!-- Dynamic Content Section -->
    <section class="home" id="content-area">
        <div class="text">Welcome to the Dashboard</div>
    </section>

    <!-- JavaScript -->
    <script>
        // Select elements
        const body = document.querySelector('body'),
            sidebar = body.querySelector('nav'),
            toggle = body.querySelector(".toggle"),
            modeSwitch = body.querySelector(".toggle-switch"),
            modeText = body.querySelector(".mode-text");

        // Apply the saved mode from localStorage
        const savedMode = localStorage.getItem('theme');
        if (savedMode && savedMode === 'dark') {
            body.classList.add('dark');
            modeText.innerText = "Light mode";
        }

        // Sidebar toggle event
        toggle.addEventListener("click", () => {
            sidebar.classList.toggle("close");
        });

        // Mode switch event
        modeSwitch.addEventListener("click", () => {
            body.classList.toggle("dark");

            if (body.classList.contains("dark")) {
                modeText.innerText = "Light mode";
                localStorage.setItem('theme', 'dark'); // Save the theme as dark
            } else {
                modeText.innerText = "Dark mode";
                localStorage.setItem('theme', 'light'); // Save the theme as light
            }
        });

        // Load page content dynamically
        function loadPage(page) {
            const contentArea = document.getElementById('content-area');
            fetch(page)
                .then(response => response.text())
                .then(data => {
                    contentArea.innerHTML = data; // Update content area with the new page content

                    // Reinitialize any JavaScript functionality (e.g., buttons, forms)
                    if (page.includes("employee_list.php")) {
                        initializeEmployeeListPage();
                    }
                    // Add more conditionals if needed for other pages
                })
                .catch(error => {
                    contentArea.innerHTML = "<p>Error loading page. Please try again later.</p>";
                });
        }

        // Initialize the employee list page with necessary JavaScript functionality
        function initializeEmployeeListPage() {
            // Re-initialize any JavaScript functionality for the employee list page
            // For example, setting up event listeners, buttons, etc.

            // Example: If you have a button that should trigger a specific action, add the listener here:
            const deleteButtons = document.querySelectorAll('.delete-button');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Implement your delete functionality
                    console.log("Delete button clicked");
                });
            });
        }
    </script>
</body>

</html>
