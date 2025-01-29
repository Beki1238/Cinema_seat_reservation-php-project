<?php
// Start session to manage user authentication
session_start();



Include "sessionManager.php";
// Include necessary files
require_once 'movies.php';

// Redirect to signin.php if the user is not logged in
if (!isset($_SESSION['user'])) {
    header("Location: signin.php");
    exit();
}
// If logged in, update last activity time
$_SESSION['last_activity'] = time();

// Prevent the page from being cached
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

$db = (new Database())->getConnection();
$movie = new Movie($db);

// Fetch inactive (coming soon) movies
$comingSoonMovies = $movie->getInactiveMovies();

// Fetch active movies for the movie slider section
$activeMovies = $movie->getActiveMovies();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Triangle Cinema Hub</title>
    <link rel="stylesheet" href="../Cu/styles/style.css"> <!-- Your CSS file -->
    <link rel="stylesheet" href="../Cu/styles/moslider.css"> <!-- Your CSS file -->
    <!--<link rel="stylesheet" href="../Cu/styles/style.css">  Your CSS file -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css"> <!-- Swiper CSS -->
</head>
<body>
    <!-- Navbar Section -->
    <header>
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
            <li><a href="#movies">Movies </a></li>
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
            <a href="signin.php" class="btn">Logout</a>
        </div>
        <?php else: ?>
        <a href="signin.php" class="btn">Sign In</a>
        <?php endif; ?>
    </header>

    <!-- Home Section (Coming Soon Movies) -->
    <section class="home swiper" id="home">
        <div class="swiper-wrapper">
            <?php if (!empty($comingSoonMovies)): ?>
                <?php foreach ($comingSoonMovies as $movie): ?>
                    <div class="swiper-slide">
                        <div class="container">
                            <img src="../employees/Manager/assets/img/<?= htmlspecialchars($movie['cover_img']); ?>" 
                                 alt="<?= htmlspecialchars($movie['title']); ?>">
                            <div class="home-text">
                                <span>Coming soon</span>
                                <h1><?= htmlspecialchars($movie['title']); ?> <br> </h1>
                                <a href="<?= htmlspecialchars($movie['youtube_link']); ?>" class='trailer-btn'>Watch Trailer</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No movies currently showing.</p>
            <?php endif; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
    </section>

    <!-- Active Movies Section -->
    <section class="movies" id="movies">
        <h2>Active Movies</h2>
        <div class="movie-slider-container">
            <!-- Slider Wrapper -->
            <div class="movie-slider">
                <?php if (!empty($activeMovies)): ?>
                    <?php foreach ($activeMovies as $movie): ?>
                        <div class='movie-item'>
                            <div class='box-img'>
                                <img src='../employees/Manager/assets/img/<?= htmlspecialchars($movie['cover_img']); ?>' alt='<?= htmlspecialchars($movie['title']); ?>'>
                                <a href='select_room.php?id=<?= $movie['id']; ?>' class='btn book-btn'>Book Now</a>
                            </div>
                            <h2><?= htmlspecialchars($movie['title']); ?></h2>
                            <p><?= htmlspecialchars($movie['duration']); ?> min | <?= htmlspecialchars($movie['description']); ?></p>
                            <p>Release Date: <?= htmlspecialchars($movie['release_date']); ?></p>
                            <a href='<?= htmlspecialchars($movie['youtube_link']); ?>' class='trailer-btn'>Watch Trailer</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No active movies available at the moment.</p>
                <?php endif; ?>
            </div>
            <!-- Navigation Arrows -->
            <button class="slider-btn prev-btn">&lt;</button>
            <button class="slider-btn next-btn">&gt;</button>
        </div>
    </section>

    <!-- Coming Soon Section -->
    <section class="coming" id="coming">
        <h2>Coming Soon</h2>
        <div class="movie-slider-container">
            <!-- Slider Wrapper -->
            <div class="movie-slider">
                <?php if (!empty($comingSoonMovies)): ?>
                    <?php foreach ($comingSoonMovies as $movie): ?>
                        <div class='movie-item'>
                            <div class='box-img'>
                                <img src='../employees/Manager/assets/img/<?= htmlspecialchars($movie['cover_img']); ?>' alt='<?= htmlspecialchars($movie['title']); ?>'>
                            </div>
                            <h2><?= htmlspecialchars($movie['title']); ?></h2>
                            <p><?= htmlspecialchars($movie['duration']); ?> min | <?= htmlspecialchars($movie['description']); ?></p>
                            <p>Release Date: <?= htmlspecialchars($movie['release_date']); ?></p>
                            <a href='<?= htmlspecialchars($movie['youtube_link']); ?>' class='trailer-btn'>Watch Trailer</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No upcoming movies available at the moment.</p>
                <?php endif; ?>
            </div>
            <!-- Navigation Arrows -->
            <button class="slider-btn prev-btn">&lt;</button>
            <button class="slider-btn next-btn">&gt;</button>
        </div>
    </section>

    <!-- Newsletter Section -->
    <?php if (isset($_SESSION['user'])): ?>
    <section class="newsletter" id="newsletter">
        <h2>Send Your Feedback</h2>
        <form action="submit_feedback.php" method="POST">
            <textarea name="feedback" placeholder="Write your feedback here..." required></textarea>
            <button type="submit" class="btn">Submit Feedback</button>
        </form>
    </section>
<?php else: ?>
    <p>Please <a href="signin.php">log in</a> to submit feedback.</p>
<?php endif; ?>


    <footer>
    <p>&copy; 2024 Triangle Cinema. All Rights Reserved.</p>
    </footer>

    <!-- Swiper JS -->
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        const swiper = new Swiper('.swiper', {
            loop: true, // Enable looping
            autoplay: {
                delay: 3000, // 3 seconds delay between slides
                disableOnInteraction: false, // Continue autoplay after interaction
            },
            pagination: {
                el: '.swiper-pagination', // Pagination bullets
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next', // Next button
                prevEl: '.swiper-button-prev', // Previous button
            },
        });

        // JavaScript for Slider Functionality
        const slider = document.querySelector('.movie-slider');
        const nextBtn = document.querySelector('.next-btn');
        const prevBtn = document.querySelector('.prev-btn');
        const totalItems = document.querySelectorAll('.movie-item').length;
        const itemWidth = 320; // 300px + 10px margin on both sides
        const sliderWidth = totalItems * itemWidth; // Total width of the items
        let currentIndex = 0;

        // Set slider width dynamically to accommodate all items
        slider.style.width = `${sliderWidth}px`;

        // Next button functionality
        nextBtn.addEventListener('click', () => {
            if (currentIndex < totalItems - 1) {
                currentIndex += 1;
            } else {
                currentIndex = 0; // Loop back to the beginning
            }
            slider.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        });

        // Prev button functionality
        prevBtn.addEventListener('click', () => {
            if (currentIndex > 0) {
                currentIndex -= 1;
            } else {
                currentIndex = totalItems - 1; // Loop back to the last item
            }
            slider.style.transform = `translateX(-${currentIndex * itemWidth}px)`;
        });
    </script>

    <!-- Your custom JS -->
    <script src="../Customer/scripts/script.js"></script>
   
</body>
</html>
