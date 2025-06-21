<?php
require 'connection.php';
$page_title = "Welcome to MMU Talent";
require 'header.php';
session_start();

// Fetch 4 random talents to feature on the homepage.
// We use a JOIN to also get the name of the talent owner.
$featured_talents = $conn->query(
    "SELECT s.service_id, s.service_title, s.service_image, u.name as user_name 
     FROM services s
     JOIN users u ON s.user_id = u.user_id
     ORDER BY RAND() 
     LIMIT 4"
);
?>

<style>
    /* Custom styles for the new homepage layout */
    .hero-section {
        color: white;
        text-align: center;
        padding: 60px 20px;
        background: rgba(0, 0, 0, 0.3); /* Semi-transparent overlay to make text pop */
    }
    .hero-section h1 {
        font-size: 3em;
        margin-bottom: 10px;
        font-family: 'Bebas Neue', sans-serif;
    }
    .hero-section p {
        font-size: 1.2em;
        margin-bottom: 30px;
    }
    .hero-section .cta-button {
        background-color: var(--color-primary);
        color: white;
        padding: 15px 30px;
        text-decoration: none;
        border-radius: 8px;
        font-weight: bold;
        transition: background-color 0.3s;
    }
    .hero-section .cta-button:hover {
        background-color: #3b5f58;
    }

    .featured-section {
        max-width: 1200px;
        margin: 40px auto;
        padding: 0 20px;
    }
    .featured-section h2 {
        text-align: center;
        font-size: 2.5em;
        color: var(--color-title);
        margin-bottom: 30px;
        background-color: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .featured-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: center;
    }
    .featured-card {
        background-color: white;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        width: calc(25% - 20px); /* Four items per row, accounting for gap */
        text-decoration: none;
        color: var(--color-text);
        transition: transform 0.3s, box-shadow 0.3s;
    }
    .featured-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }
    .featured-card img {
        width: 100%;
        height: 180px;
        object-fit: cover;
    }
    .featured-card-content {
        padding: 15px;
    }
    .featured-card-content h4 {
        margin: 0 0 5px 0;
        font-size: 1.1em;
    }
    .featured-card-content p {
        margin: 0;
        font-size: 0.9em;
        color: var(--text-light);
    }
    
    /* Responsive adjustments */
    @media (max-width: 1024px) {
        .featured-card {
            width: calc(50% - 20px); /* Two items per row on tablets */
        }
    }
    @media (max-width: 600px) {
        .featured-card {
            width: 100%; /* One item per row on mobile */
        }
    }
</style>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
             <h1>MMU Talent</h1>
        </div>
        
        <section class="hero-section">
            <h1>Discover & Showcase Your Talent</h1>
            <p>The ultimate platform for MMU students to connect, collaborate, and shine.</p>
            
            <!-- **UPDATED LOGIC FOR CTA BUTTON** -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="talentCatalogue.php" class="cta-button">Browse Catalogue</a>
            <?php else: ?>
                <a href="registration.php" class="cta-button">Join Now & Get Discovered</a>
            <?php endif; ?>
        </section>

        <section class="featured-section">
            <h2>Featured Talents</h2>
            <div class="featured-grid">
                <?php if ($featured_talents->num_rows > 0): ?>
                    <?php while ($talent = $featured_talents->fetch_assoc()): ?>
                        <a href="viewTalent.php?id=<?php echo $talent['service_id']; ?>" class="featured-card">
                            <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>">
                            <div class="featured-card-content">
                                <h4><?php echo htmlspecialchars($talent['service_title']); ?></h4>
                                <p>by <?php echo htmlspecialchars($talent['user_name']); ?></p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>No featured talents available at the moment. Check back soon!</p>
                <?php endif; ?>
            </div>
        </section>

    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
