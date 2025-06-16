<?php
require 'connection.php';
$page_title = "Welcome to MMU Got Talent!";
require 'header.php';
session_start();

// Fetch latest 3 announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");

// Fetch 4 random featured talents
$talents = $conn->query(
    "SELECT s.service_id, s.service_title, s.service_image, u.name as user_name 
     FROM services s 
     JOIN users u ON s.user_id = u.user_id 
     ORDER BY RAND() LIMIT 4"
);
?>
<style>
    /* Additional styles for the homepage */
    .hero {
        background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/background/hero-bg.jpg') no-repeat center center/cover;
        color: white;
        text-align: center;
        padding: 100px 20px;
    }
    .hero h1 { font-size: 3em; margin-bottom: 10px; }
    .hero p { font-size: 1.2em; margin-bottom: 30px; }
    .section { padding: 50px 20px; }
    .section-title { text-align: center; margin-bottom: 40px; font-size: 2em; color: var(--color-title); }
    .grid-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; max-width: 1200px; margin: 0 auto; }
    .card { background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; }
    .card img { width: 100%; height: 200px; object-fit: cover; }
    .card-content { padding: 20px; }
</style>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <!-- Hero Section -->
        <div class="hero">
            <h1>Discover the Talent Within MMU</h1>
            <p>A platform to showcase, collaborate, and hire the amazing talents of our students.</p>
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="registration.php" class="form-button" style="width: auto; display: inline-block; padding: 15px 30px; font-size: 1.1em;">Join Now</a>
            <?php endif; ?>
        </div>

        <!-- Featured Talents Section -->
        <section class="section">
            <h2 class="section-title">Featured Talents</h2>
            <div class="grid-container">
                <?php while ($talent = $talents->fetch_assoc()): ?>
                    <div class="card">
                        <a href="viewTalent.php?id=<?php echo $talent['service_id']; ?>" style="text-decoration: none; color: inherit;">
                            <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>">
                            <div class="card-content">
                                <h3 style="margin-top: 0;"><?php echo htmlspecialchars($talent['service_title']); ?></h3>
                                <p>by <?php echo htmlspecialchars($talent['user_name']); ?></p>
                            </div>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Latest Announcements Section -->
        <section class="section" style="background-color: #f9f9f9;">
            <h2 class="section-title">Latest Announcements</h2>
            <div class="grid-container">
                <?php while ($announcement = $announcements->fetch_assoc()): ?>
                    <div class="card">
                        <div class="card-content">
                            <h3 style="margin-top: 0;"><?php echo htmlspecialchars($announcement['title']); ?></h3>
                            <small style="color: #777;">Posted on <?php echo date('F j, Y', strtotime($announcement['created_at'])); ?></small>
                            <p><?php echo substr(htmlspecialchars($announcement['content']), 0, 100); ?>...</p>
                            <a href="announcement.php" style="color: var(--color-primary);">Read more</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        </section>
        
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
