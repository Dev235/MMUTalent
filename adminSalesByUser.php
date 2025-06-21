<?php
// Same as always, session start and database connection are a must.
session_start();
require 'connection.php';

// Security check, must be admin. If not, go back to login page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Sales by User";
require 'header.php';

// --- Pagination stuff ---
$records_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

// Get the search term from the URL if there is one.
$search_seller_name = isset($_GET['seller_name']) ? trim($_GET['seller_name']) : '';

// --- Building the SQL Queries ---
// This page is a bit different. We want to show the TOTAL sales for each user.
// So we use GROUP BY and SUM().

// First, the query to count the total number of unique sellers.
$count_query_sql = "SELECT COUNT(DISTINCT s.user_id) AS total_sellers
                    FROM transactions tr
                    JOIN services s ON tr.service_id = s.service_id
                    JOIN users u ON s.user_id = u.user_id";

// Second, the query to get the actual data.
// `SUM(tr.price_at_purchase)` adds up all the sales for one user.
// `GROUP BY u.user_id, u.name` tells the database to group all rows with the same user_id together and sum up their prices.
$data_query_sql = "SELECT u.user_id, u.name AS seller_name, SUM(tr.price_at_purchase) AS user_total_revenue
                   FROM transactions tr
                   JOIN services s ON tr.service_id = s.service_id
                   JOIN users u ON s.user_id = u.user_id";

$params = [];
$types = '';

// Add the search condition if the admin is searching for a specific seller.
if (!empty($search_seller_name)) {
    $where_clause = " WHERE u.name LIKE ?";
    $count_query_sql .= $where_clause;
    $data_query_sql .= $where_clause;
    $params[] = '%' . $search_seller_name . '%';
    $types .= 's';
}

// Add the GROUP BY, ORDER BY, and LIMIT clauses to the main data query.
$data_query_sql .= " GROUP BY u.user_id, u.name
                     ORDER BY user_total_revenue DESC
                     LIMIT ? OFFSET ?";


// Get total number of sellers for pagination
$count_stmt = $conn->prepare($count_query_sql);
if (!empty($search_seller_name)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_sellers = $count_stmt->get_result()->fetch_assoc()['total_sellers'] ?? 0;
$total_pages = ceil($total_sellers / $records_per_page);
$count_stmt->close();


// Fetch the sales data per user for the current page
$sales_per_user_query = $conn->prepare($data_query_sql);
if ($sales_per_user_query) {
    // Combine the search params with the pagination params
    $pagination_params = array_merge($params, [$records_per_page, $offset]);
    $pagination_types = $types . 'ii'; // Add 'ii' for LIMIT and OFFSET

    $sales_per_user_query->bind_param($pagination_types, ...$pagination_params);
    $sales_per_user_query->execute();
    $sales_per_user_result = $sales_per_user_query->get_result();
    $sales_per_user_query->close();
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Sales by Talent Provider</h1>
            <p style="color:white;">Detailed breakdown of sales revenue per user.</p>
        </div>

        <div class="table-container" style="max-width: 1000px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <a href="adminDashboard.php" class="form-button" style="width: auto; display: inline-block; margin-bottom: 20px; background-color: #6c757d;">&larr; Back to Admin Dashboard</a>

            <!-- Search Form. -->
            <form method="GET" action="adminSalesByUser.php" style="margin-bottom: 20px; display: flex; gap: 10px;">
                <input type="text" name="seller_name" placeholder="Search by Seller Name" value="<?= htmlspecialchars($search_seller_name) ?>" style="flex-grow: 1; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                <button type="submit" class="form-button" style="width: auto; padding: 8px 15px;">Search</button>
                <a href="adminSalesByUser.php" class="form-button" style="width: auto; padding: 8px 15px; background-color: #f0ad4e;">Clear</a>
            </form>

            <?php if ($sales_per_user_result && $sales_per_user_result->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 10px;">Seller Name</th>
                            <th style="padding: 10px; text-align: right;">Total Sales (RM)</th>
                            <th style="padding: 10px; text-align: center;">View Profile</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($seller_sales = $sales_per_user_result->fetch_assoc()): ?>
                            <tr>
                                <td style="padding: 10px;"><?= htmlspecialchars($seller_sales['seller_name']) ?></td>
                                <td style="padding: 10px; text-align: right;"><?= number_format($seller_sales['user_total_revenue'], 2) ?></td>
                                <td style="padding: 10px; text-align: center;">
                                    <a href="userDashboard.php?id=<?= $seller_sales['user_id'] ?>" target="_blank" class="form-button" style="background-color: var(--color-primary); padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">View Profile</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

                <!-- Pagination Links -->
                <div style="text-align: center; margin-top: 20px;">
                    <?php if ($total_pages > 1): ?>
                        <?php
                        $base_pagination_url = '?';
                        if (!empty($search_seller_name)) $base_pagination_url .= 'seller_name=' . urlencode($search_seller_name) . '&';
                        ?>
                        <?php if ($current_page > 1): ?>
                            <a href="<?= $base_pagination_url ?>page=<?= $current_page - 1 ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; background-color: #6c757d;">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= $base_pagination_url ?>page=<?= $i ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; <?= ($i == $current_page) ? 'background-color: var(--color-primary);' : 'background-color: #ccc; color: #333;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?= $base_pagination_url ?>page=<?= $current_page + 1 ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; background-color: #6c757d;">Next</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p style="text-align: center;">No sales data available yet matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
