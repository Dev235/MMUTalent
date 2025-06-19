<?php
require 'connection.php';
$page_title = "Talent Catalogue";
require 'header.php';
session_start();

$search_query = '';
$category_filter = '';

if (isset($_GET['search'])) {
    $search_query = trim($_GET['search']);
} else if (isset($_GET['category'])) {
    $category_filter = trim($_GET['category']);
    $search_query = $category_filter; // Use category as search query
}

$sql = "SELECT s.service_id, s.service_title, s.service_description, s.service_image, u.name as user_name
        FROM services s
        JOIN users u ON s.user_id = u.user_id";

$params = [];
$types = '';

if (!empty($search_query)) {
    $sql .= " WHERE s.service_title LIKE ? OR s.service_description LIKE ?";
    $search_param = '%' . $search_query . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$sql .= " ORDER BY s.service_title ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$talents_result = $stmt->get_result();

?>
<style>
    .catalogue-section {
        padding: 50px 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .catalogue-header {
        text-align: center;
        margin-bottom: 40px;
        color: white;
    }
    .search-filter-container {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 30px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .search-filter-container form {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .search-filter-container input[type="text"] {
        flex-grow: 1;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }
    .category-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        justify-content: center;
    }
    .category-buttons .form-button {
        background-color: var(--color-surface);
        color: var(--color-text);
        padding: 8px 15px;
        border-radius: 5px;
        text-decoration: none;
        width: auto;
    }
    .category-buttons .form-button:hover {
        background-color: var(--color-primary);
        color: white;
    }
    .talents-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
    }
    .talent-card {
        background: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .talent-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }
    .talent-card-content {
        padding: 15px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }
    .talent-card-content h3 {
        margin-top: 0;
        font-size: 1.3em;
        margin-bottom: 5px;
    }
    .talent-card-content p {
        font-size: 0.9em;
        color: #555;
        margin-bottom: 10px;
        flex-grow: 1;
    }
    .talent-card-content .view-button {
        display: block;
        text-align: center;
        padding: 8px 15px;
        background-color: var(--color-primary);
        color: white;
        border-radius: 5px;
        text-decoration: none;
        margin-top: 10px;
    }
    .talent-card-content .view-button:hover {
        background-color: #3b5f58;
    }
</style>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Talent Catalogue</h1>
            <p style="color:white;">Browse and discover amazing talents at MMU!</p>
        </div>

        <section class="catalogue-section">
            <div class="search-filter-container">
                <form action="talentCatalogue.php" method="GET">
                    <input type="text" name="search" placeholder="Search talents (e.g., Music, Tech, Art, Writing)" value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit" class="form-button" style="width: auto;">Search</button>
                </form>
                
            </div>

            <div class="talents-grid">
                <?php if ($talents_result->num_rows > 0): ?>
                    <?php while ($talent = $talents_result->fetch_assoc()): ?>
                        <div class="talent-card">
                            <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>">
                            <div class="talent-card-content">
                                <h3><?php echo htmlspecialchars($talent['service_title']); ?></h3>
                                <p>by <?php echo htmlspecialchars($talent['user_name']); ?></p>
                                <p><?php echo substr(htmlspecialchars($talent['service_description']), 0, 100); ?>...</p>
                                <a href="viewTalent.php?id=<?php echo $talent['service_id']; ?>" class="view-button">View Talent</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="text-align: center; width: 100%; color: white;">No talents found matching your criteria.</p>
                <?php endif; ?>
            </div>
        </section>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>