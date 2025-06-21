<?php
// Standard stuff, need to connect to the database first.
require 'connection.php';
// This is for the tab title in the browser.
$page_title = "Talent Catalogue";
require 'header.php';
// Gotta start the session to check if user is logged in or not.
session_start();

// --- PAGINATION LOGIC ---
// This part is for the "Next Page" and "Previous Page" buttons.
$limit = 8; // We want to show 8 talents per page. Can change this number if we want.
// Check which page we are on. If the URL has `?page=2`, then $page will be 2.
// If it's the first time loading, no `?page` in URL, so just assume it's page 1.
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
// This is the formula to calculate the starting point for the database query.
// E.g., for Page 1, (1-1) * 8 = 0. Start from the 0th record.
// For Page 2, (2-1) * 8 = 8. Start from the 8th record.
$offset = ($page - 1) * $limit;

// --- SEARCH & FILTER LOGIC ---
// This part handles the search bar functionality.
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
// This is the basic SQL to get data from two tables.
// We need to JOIN `services` and `users` table so we can get the talent's name and the user's name together.
$base_sql = "FROM services s JOIN users u ON s.user_id = u.user_id";
$where_clause = ""; // This will be empty if there's no search.
$params = []; // To store the search term securely.
$types = ''; // To tell the database what type of data we are sending (s for string, i for integer).

// If the user actually typed something in the search box...
if (!empty($search_query)) {
    // ...then we add this WHERE clause to the SQL query.
    // The `LIKE ?` is a placeholder, to prevent SQL injection hack.
    $where_clause = " WHERE s.service_title LIKE ? OR s.service_description LIKE ?";
    // The '%' is a wildcard. Means it will search for the word anywhere in the title or description.
    $search_param = '%' . $search_query . '%';
    // Add the search term to our parameters array, twice because we have two `?`.
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss'; // Two strings, so 'ss'.
}

// --- DATABASE QUERIES ---

// 1. Get the total number of talents for pagination.
// We need to know the total number of pages to show, like "Page 1 of 5".
$count_sql = "SELECT COUNT(*) as total " . $base_sql . $where_clause;
$count_stmt = $conn->prepare($count_sql);
// If there was a search, we need to bind the search parameters to the query.
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_talents = $count_stmt->get_result()->fetch_assoc()['total'];
// `ceil` is to round up. E.g., if we have 17 talents, 17/8 = 2.125, so ceil makes it 3 pages.
$total_pages = ceil($total_talents / $limit);
$count_stmt->close();

// 2. Get the actual talents for the current page.
// This is the main query to fetch the data we want to display.
// We add `LIMIT ? OFFSET ?` at the end for the pagination.
$talents_sql = "SELECT s.service_id, s.service_title, s.service_description, s.service_image, u.name as user_name " . $base_sql . $where_clause . " ORDER BY s.service_title ASC LIMIT ? OFFSET ?";
$types .= 'ii'; // Add 'ii' for the two integers (limit and offset).
$params[] = $limit;
$params[] = $offset;

$stmt = $conn->prepare($talents_sql);
// This `...$params` is a "splat operator", it's a cool way to pass the array elements as individual arguments.
$stmt->bind_param($types, ...$params);
$stmt->execute();
$talents_result = $stmt->get_result();

?>
<style>
    /* These styles are just to make the page look nice lah */
    .catalogue-section { padding: 50px 20px; max-width: 1200px; margin: 0 auto; }
    .catalogue-header { text-align: center; margin-bottom: 40px; color: white; }
    .search-filter-container { background-color: #fff; padding: 20px; border-radius: 10px; margin-bottom: 30px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .search-filter-container form { display: flex; gap: 10px; margin-bottom: 20px; }
    .search-filter-container input[type="text"] { flex-grow: 1; padding: 10px; border: 1px solid #ccc; border-radius: 5px; }
    .talents-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; }
    .talent-card { background: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden; display: flex; flex-direction: column; }
    .talent-card img { width: 100%; height: 200px; object-fit: cover; }
    .talent-card-content { padding: 15px; flex-grow: 1; display: flex; flex-direction: column; justify-content: space-between; }
    .talent-card-content h3 { margin-top: 0; font-size: 1.3em; margin-bottom: 5px; }
    .talent-card-content p { font-size: 0.9em; color: #555; margin-bottom: 10px; flex-grow: 1; }
    .talent-card-content .view-button { display: block; text-align: center; padding: 8px 15px; background-color: var(--color-primary); color: white; border-radius: 5px; text-decoration: none; margin-top: 10px; }
    .talent-card-content .view-button:hover { background-color: #3b5f58; }
    
    /* Pagination Styles */
    .pagination { display: flex; justify-content: center; align-items: center; margin-top: 40px; }
    .pagination a, .pagination span {
        padding: 10px 15px;
        margin: 0 5px;
        text-decoration: none;
        color: var(--color-text);
        background-color: white;
        border: 1px solid #ddd;
        border-radius: 5px;
    }
    .pagination .current-page {
        background-color: var(--color-primary);
        color: white;
        border-color: var(--color-primary);
    }
    .pagination .disabled {
        color: #aaa;
        background-color: #f9f9f9;
        cursor: not-allowed;
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
                    <input type="text" name="search" placeholder="Search talents..." value="<?php echo htmlspecialchars($search_query); ?>">
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
                    <p style="text-align: center; width: 100%; color: white; grid-column: 1 / -1;">Aiyo, no talents found la matching your search.</p>
                <?php endif; ?>
            </div>

            <!-- Pagination Links -->
            <div class="pagination">
                <?php if ($page > 1): ?>
                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search_query); ?>">&laquo; Previous</a>
                <?php else: ?>
                    <span class="disabled">&laquo; Previous</span>
                <?php endif; ?>

                <span class="current-page"><?php echo "Page $page of $total_pages"; ?></span>

                <?php if ($page < $total_pages): ?>
                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search_query); ?>">Next &raquo;</a>
                <?php else: ?>
                    <span class="disabled">Next &raquo;</span>
                <?php endif; ?>
            </div>

        </section>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
