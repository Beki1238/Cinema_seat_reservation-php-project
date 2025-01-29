<style>
            .name-link {
                color: white;
            }
        </style>
        <a href="index.php" class="logo">
            <i class='bx bxs-movie'></i>Triangle Cinema Hub
        </a>
        <div class="bx bx-menu" id="menu-icon"></div>
        <ul class="navbar">
            <li><a href="index.php" class="home-active">Home</a></li>
            <li><a href="#movies">Now showing</a></li>
            <li><a href="#coming">Coming</a></li>
            <li><a href="#newsletter">Feedback</a></li>
        </ul>
        <?php if (isset($_SESSION['user'])): ?>
        <div class="user">
            <span>
                Welcome, 
                <a href="my_booking.php" class="name-link">
                    <?php echo htmlspecialchars($_SESSION['user']['name']); ?>
                </a>
            </span>
            <a href="logout.php" class="btn">Logout</a>
        </div>
        <?php else: ?>
        <a href="signin.php" class="btn">Sign In</a>
        <?php endif; ?>