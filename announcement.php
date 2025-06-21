<?php
require 'connection.php';
$page_title = "Announcements";
require 'header.php';
require 'navbar.php';

// --- PAGINATION LOGIC ---
$limit = 3; // Number of announcements per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// 1. Get the total number of announcements for calculating total pages
$count_result = $conn->query("SELECT COUNT(*) as total FROM announcements");
$total_announcements = $count_result->fetch_assoc()['total'];
$total_pages = ceil($total_announcements / $limit);

// 2. Fetch announcements for the current page using LIMIT and OFFSET
$stmt = $conn->prepare("SELECT * FROM announcements ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$announcements = $stmt->get_result();
?>

<style>
    /* Simple styles for the pagination buttons */
    .pagination {
        text-align: center;
        margin-top: 30px;
    }
    .pagination a, .pagination span {
        display: inline-block;
        padding: 10px 15px;
        margin: 0 5px;
        border-radius: 5px;
        text-decoration: none;
    }
    .pagination a {
        background-color: var(--color-primary);
        color: white;
    }
    .pagination a:hover {
        background-color: #3b5f58;
    }
    .pagination .disabled {
        background-color: #6c757d;
        color: #ccc;
        cursor: not-allowed;
    }
</style>

<div id="main-content">
    <div class="title-container">
        <h1>News & Announcements</h1>
        <p style="color:white;">Stay updated with the latest talent events and workshops at MMU!</p>
    </div>

    <section style="max-width:800px;margin:30px auto;">
        <?php if ($announcements->num_rows > 0): ?>
            <?php while ($row = $announcements->fetch_assoc()): ?>
                <div class="faq-card"
                     style="margin-bottom:25px;background:#f9f9f9;padding:20px;border-radius:10px;">
                    <h3 style="margin-top:0;color:var(--color-title);">
                        <?= htmlspecialchars($row['title']) ?>
                    </h3>

                    <p style="margin-bottom:10px;">
                        <?= nl2br(htmlspecialchars($row['content'])) ?>
                    </p>

                    <!-- Prepend the correct directory path to the image -->
                    <?php if (!empty($row['image_path'])): ?>
                        <img src="images/uploads/announcements/<?= htmlspecialchars($row['image_path']) ?>"
                             alt="Announcement image"
                             style="max-width:100%;height:auto;margin:15px 0;border-radius:6px;">
                    <?php endif; ?>

                    <small style="color:gray;">
                        Posted on <?= date('F j, Y', strtotime($row['created_at'])) ?>
                    </small>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;color:white;">No announcements yet.</p>
        <?php endif; ?>
    </section>

    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo $page - 1; ?>">&laquo; Previous</a>
        <?php else: ?>
            <span class="disabled">&laquo; Previous</span>
        <?php endif; ?>

        <?php if ($page < $total_pages): ?>
            <a href="?page=<?php echo $page + 1; ?>">Next &raquo;</a>
        <?php else: ?>
            <span class="disabled">Next &raquo;</span>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
