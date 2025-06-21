<?php
session_start();
require 'connection.php'; // Note the '..' to go up one directory to find connection.php

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the main login page if not an admin
    header("Location: login.php");
    exit();
}

$page_title = "Admin Dashboard";
// We need to adjust the path to the header as well
require 'header.php'; 

//  Fetch Total Sales Revenue 
$total_sales_query = $conn->query("SELECT SUM(price_at_purchase) AS total_revenue FROM transactions");
$total_sales_result = $total_sales_query->fetch_assoc();
$total_revenue = $total_sales_result['total_revenue'] ?? 0;

//  Fetch Top 3 Sales Revenue Per User (Seller) for Dashboard, dont want to overload the dashboard with too much data
$top_sales_per_user_query = $conn->prepare(
    // this is the query to get the top 3 sales per user
    // it joins transactions, services, and users to get the total revenue per user
    "SELECT u.user_id, u.name AS seller_name, SUM(tr.price_at_purchase) AS user_total_revenue
     FROM transactions tr
     JOIN services s ON tr.service_id = s.service_id
     JOIN users u ON s.user_id = u.user_id
     GROUP BY u.user_id, u.name
     ORDER BY user_total_revenue DESC
     LIMIT 3" // Limit to top 3
);
$top_sales_per_user_result = null;
if ($top_sales_per_user_query) {
    $top_sales_per_user_query->execute();
    $top_sales_per_user_result = $top_sales_per_user_query->get_result();
    $top_sales_per_user_query->close();
} else {
    error_log("Failed to prepare statement for top sales per user: " . $conn->error);
}

?>

<style>
    /* Admin Dashboard Specific Styles */
    .admin-menu, .sales-summary-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Added box shadow for better visual */
    }

    .admin-menu h2, .sales-summary-container h2 {
        text-align: center;
        margin-bottom: 25px; /* Increased margin for better spacing */
        color: var(--color-title); /* Using CSS variable for consistency */
        font-family: "Copperplate Gothic", "Copperplate", fantasy; /* Consistent font */
    }

    .admin-menu ul {
        list-style: none;
        padding: 0;
        text-align: center;
    }

    .admin-menu li {
        margin: 15px 0;
    }

    .admin-menu a {
        display: inline-block; /* Allows padding and width */
        font-size: 1.2em;
        color: var(--color-primary); /* Consistent link color */
        text-decoration: none;
        padding: 10px 20px; /* Added padding for clickable area */
        border-radius: 5px; /* Rounded corners for links */
        transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transition on hover */
    }

    .admin-menu a:hover {
        background-color: var(--color-primary); /* Background color on hover */
        color: white; /* Text color on hover */
        box-shadow: 0 2px 10px rgba(0,0,0,0.2); /* Subtle shadow on hover */
    }

    .sales-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        text-align: left;
    }
    .sales-table th, .sales-table td {
        padding: 10px;
        border: 1px solid #eee;
    }
    .sales-table thead tr {
        background-color: #f2f2f2;
    }
</style>

<body>
    <?php require 'navbar.php'; // Adjust path for navbar ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Admin Dashboard</h1>
            <p style="color:white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>

        <!-- NEW: Sales Summary Section -->
        <div class="sales-summary-container">
            <h2>Sales Overview</h2>
            <div style="text-align: center; margin-bottom: 20px;">
                <p style="font-size: 1.5em; font-weight: bold; color: var(--color-primary);">
                    Total Platform Revenue: RM <?= number_format($total_revenue, 2) ?>
                </p>
            </div>

            <h3>Top 3 Talent Providers by Sales</h3>

            <?php
            // getting the top 3 sales per user (seller) for the dashboard can refer above for the quuery
             if ($top_sales_per_user_result && $top_sales_per_user_result->num_rows > 0): ?>

                <table class="sales-table">
                    <thead>
                        <tr>
                            <th>Seller Name</th>
                            <th style="text-align: right;">Total Sales (RM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($seller_sales = $top_sales_per_user_result->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($seller_sales['seller_name']) ?></td>
                                <td style="text-align: right;"><?= number_format($seller_sales['user_total_revenue'], 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                <div style="text-align: center; margin-top: 20px;">
                    <a href="adminSalesByUser.php" class="form-button" style="width: auto; display: inline-block;">View All Sales by User</a>
                </div>
            <?php else: ?>
                <p style="text-align: center;">No sales data available yet.</p>
            <?php endif; ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="adminTotalSales.php" class="form-button" style="width: auto; display: inline-block;">View All Transactions Report</a>
            </div>
        </div>

        <div class="admin-menu">
            <h2>Management Links</h2>
            <ul>
                <li>
                    <a href="manageUsers.php">Manage Users</a>
                </li>
                <li>
                    <a href="manageForum.php">Manage Forum</a>
                </li>
                <li>
                    <a href="manageAnnouncements.php">Manage Announcements</a>
                </li>
                 <li>
                    <a href="manageFAQ.php">Manage FAQ</a>
                </li>
                <li>
                    <a href="manageCatalogue.php">Manage Catalogue</a>
                </li>
            </ul>
        </div>
    </div>
    <?php require 'footer.php'; // Adjust path for footer ?>
</body>
</html>
