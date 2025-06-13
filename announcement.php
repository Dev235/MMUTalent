<?php
require 'connection.php';
$page_title = "Announcements";
require 'header.php';
require 'navbar.php';

// Fetch announcements from the database
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>News & Announcements</h1>
        <p style="color: white;">Stay updated with the latest talent events and workshops at MMU!</p>
    </div>

    <section style="max-width: 800px; margin: 30px auto;">
        <?php if ($announcements->num_rows > 0): ?>
            <?php while ($row = $announcements->fetch_assoc()): ?>
                <div class="faq-card" style="margin-bottom: 25px; background-color: #f9f9f9; padding: 20px; border-radius: 10px;">
                    <h3 style="margin-top: 0; color: var(--color-title);"><?= htmlspecialchars($row['title']) ?></h3>
                    <p style="margin-bottom: 10px;"><?= nl2br(htmlspecialchars($row['content'])) ?></p>
                    <small style="color: gray;">Posted on <?= date('F j, Y', strtotime($row['created_at'])) ?></small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center; color: white;">No announcements yet.</p>
        <?php endif; ?>
    </section>
</div>

<?php require 'footer.php'; ?>
