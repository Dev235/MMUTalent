<?php
session_start();
require 'connection.php';

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "All Transactions Report";
require 'header.php';

$records_per_page = 15;
$current_page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($current_page - 1) * $records_per_page;

// --- Handle Marking a Sale as Completed from this page ---
$status_message = '';
if (isset($_GET['mark_as_completed'])) {
    $transaction_id_to_mark = intval($_GET['mark_as_completed']);
    
    // Admins can mark any transaction as completed, no user ownership check needed here
    $check_stmt = $conn->prepare(
        "SELECT transaction_id FROM transactions WHERE transaction_id = ? AND status = 'pending'"
    );
    if ($check_stmt) {
        $check_stmt->bind_param("i", $transaction_id_to_mark);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $transaction_id_to_mark);
                $update_stmt->execute();
                $update_stmt->close();
                $status_message = "Order marked as completed!";
            } else {
                error_log("Failed to prepare statement for marking sale as completed: " . $conn->error);
                $status_message = "Error marking order as completed.";
            }
        } else {
            $status_message = "Order not found or already completed.";
        }
        $check_stmt->close();
    } else {
        error_log("Failed to prepare statement for checking transaction status: " . $conn->error);
        $status_message = "Database error during status check.";
    }
    // Redirect to clear GET parameters and show message, maintaining current page for pagination and filters
    $redirect_url = "adminTotalSales.php?page=" . $current_page;
    if (isset($_GET['talent_title'])) $redirect_url .= '&talent_title=' . urlencode($_GET['talent_title']);
    if (isset($_GET['buyer_name'])) $redirect_url .= '&buyer_name=' . urlencode($_GET['buyer_name']);
    if (isset($_GET['seller_name'])) $redirect_url .= '&seller_name=' . urlencode($_GET['seller_name']);
    if (isset($_GET['status_filter'])) $redirect_url .= '&status_filter=' . urlencode($_GET['status_filter']);
    $redirect_url .= "&msg=" . urlencode($status_message);
    header("Location: " . $redirect_url);
    exit();
}

// Check for status message from redirect
if (isset($_GET['msg'])) {
    $status_message = htmlspecialchars($_GET['msg']);
}

// --- Search and Filter Parameters ---
$search_talent_title = isset($_GET['talent_title']) ? trim($_GET['talent_title']) : '';
$search_buyer_name = isset($_GET['buyer_name']) ? trim($_GET['buyer_name']) : '';
$search_seller_name = isset($_GET['seller_name']) ? trim($_GET['seller_name']) : '';
$status_filter = isset($_GET['status_filter']) ? trim($_GET['status_filter']) : '';

// Base query for counting total transactions (for pagination)
$count_query_sql = "SELECT COUNT(*) AS total_transactions
                    FROM transactions tr
                    JOIN services s ON tr.service_id = s.service_id
                    JOIN users bu ON tr.buyer_user_id = bu.user_id
                    JOIN users se ON s.user_id = se.user_id";

// Base query for fetching all sales data
$data_query_sql = "SELECT tr.transaction_id, tr.price_at_purchase, tr.transaction_date, tr.status,
                           s.service_title,
                           bu.name as buyer_name, bu.email as buyer_email, bu.phone_number as buyer_phone,
                           se.name as seller_name, se.email as seller_email, se.phone_number as seller_phone
                    FROM transactions tr
                    JOIN services s ON tr.service_id = s.service_id
                    JOIN users bu ON tr.buyer_user_id = bu.user_id
                    JOIN users se ON s.user_id = se.user_id";

$conditions = [];
$params = [];
$types = '';

if (!empty($search_talent_title)) {
    $conditions[] = "s.service_title LIKE ?";
    $params[] = '%' . $search_talent_title . '%';
    $types .= 's';
}
if (!empty($search_buyer_name)) {
    $conditions[] = "bu.name LIKE ?";
    $params[] = '%' . $search_buyer_name . '%';
    $types .= 's';
}
if (!empty($search_seller_name)) {
    $conditions[] = "se.name LIKE ?";
    $params[] = '%' . $search_seller_name . '%';
    $types .= 's';
}
if (!empty($status_filter)) {
    $conditions[] = "tr.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($conditions)) {
    $count_query_sql .= " WHERE " . implode(" AND ", $conditions);
    $data_query_sql .= " WHERE " . implode(" AND ", $conditions);
}

$data_query_sql .= " ORDER BY tr.transaction_date DESC
                     LIMIT ? OFFSET ?";


// Get total number of transactions for pagination
$count_stmt = $conn->prepare($count_query_sql);
if (!empty($params)) {
    $count_stmt->bind_param($types, ...$params);
}
$count_stmt->execute();
$total_transactions_result = $count_stmt->get_result();
$total_transactions = $total_transactions_result->fetch_assoc()['total_transactions'] ?? 0;
$total_pages = ceil($total_transactions / $records_per_page);
$count_stmt->close();


// Fetch All Sales Transactions with pagination and search/filter
$all_sales_query = $conn->prepare($data_query_sql);
if ($all_sales_query) {
    // Add pagination parameters to the existing search parameters
    $pagination_params = array_merge($params, [$records_per_page, $offset]);
    $pagination_types = $types . 'ii'; // Add 'ii' for LIMIT and OFFSET

    $all_sales_query->bind_param($pagination_types, ...$pagination_params);
    $all_sales_query->execute();
    $all_sales_result = $all_sales_query->get_result();
    $all_sales_query->close();
} else {
    error_log("Failed to prepare statement for all sales reports (paginated with search): " . $conn->error);
}

?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>All Transactions Report</h1>
            <p style="color:white;">Overview of all transactions on the platform.</p>
        </div>

        <div class="table-container" style="max-width: 1200px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <?php if (!empty($status_message)): ?>
                <div style="text-align:center; margin-bottom:15px;">
                    <p style="color: green; font-weight: bold; background-color: #d4edda; border: 1px solid #c3e6cb; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                        <?= $status_message ?>
                    </p>
                </div>
            <?php endif; ?>

            <a href="adminDashboard.php" class="form-button" style="width: auto; display: inline-block; margin-bottom: 20px; background-color: #6c757d;">&larr; Back to Admin Dashboard</a>
            
            <!-- Search and Filter Form -->
            <form method="GET" action="adminTotalSales.php" style="margin-bottom: 20px; display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
                <div style="flex: 1; min-width: 180px;">
                    <label for="talent_title" style="display: block; font-size: 0.9em; margin-bottom: 5px;">Talent Title:</label>
                    <input type="text" id="talent_title" name="talent_title" placeholder="Search by Talent Title" value="<?= htmlspecialchars($search_talent_title) ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label for="buyer_name" style="display: block; font-size: 0.9em; margin-bottom: 5px;">Buyer Name:</label>
                    <input type="text" id="buyer_name" name="buyer_name" placeholder="Search by Buyer Name" value="<?= htmlspecialchars($search_buyer_name) ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div style="flex: 1; min-width: 180px;">
                    <label for="seller_name" style="display: block; font-size: 0.9em; margin-bottom: 5px;">Seller Name:</label>
                    <input type="text" id="seller_name" name="seller_name" placeholder="Search by Seller Name" value="<?= htmlspecialchars($search_seller_name) ?>" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                </div>
                <div style="flex: 1; min-width: 150px;">
                    <label for="status_filter" style="display: block; font-size: 0.9em; margin-bottom: 5px;">Status:</label>
                    <select id="status_filter" name="status_filter" style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 5px;">
                        <option value="">All Statuses</option>
                        <option value="pending" <?= ($status_filter == 'pending') ? 'selected' : '' ?>>Pending</option>
                        <option value="completed" <?= ($status_filter == 'completed') ? 'selected' : '' ?>>Completed</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="form-button" style="width: auto; padding: 8px 15px;">Filter</button>
                    <a href="adminTotalSales.php" class="form-button" style="width: auto; padding: 8px 15px; background-color: #f0ad4e;">Clear</a>
                </div>
            </form>

            <?php if ($all_sales_result && $all_sales_result->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 10px;">ID</th>
                            <th style="padding: 10px;">Talent</th>
                            <th style="padding: 10px;">Seller</th>
                            <th style="padding: 10px;">Buyer</th>
                            <th style="padding: 10px; text-align: right;">Price (RM)</th>
                            <th style="padding: 10px; text-align: right;">Date</th>
                            <th style="padding: 10px; text-align: center;">Status</th>
                            <th style="padding: 10px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sale = $all_sales_result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 10px;"><?= $sale['transaction_id'] ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['service_title']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['seller_name']) ?> <br> <small>(<?= htmlspecialchars($sale['seller_email']) ?>)</small> <br> <small>(<?= htmlspecialchars($sale['seller_phone'] ?? 'N/A') ?>)</small></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_name']) ?> <br> <small>(<?= htmlspecialchars($sale['buyer_email']) ?>)</small> <br> <small>(<?= htmlspecialchars($sale['buyer_phone'] ?? 'N/A') ?>)</small></td>
                            <td style="padding: 10px; text-align: right;"><?= number_format($sale['price_at_purchase'], 2) ?></td>
                            <td style="padding: 10px; text-align: right;"><?= date('Y-m-d H:i', strtotime($sale['transaction_date'])) ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <span style="padding: 5px 10px; border-radius: 5px; font-weight: bold; background-color: <?= ($sale['status'] == 'completed') ? '#d4edda; color: #155724;' : '#fff3cd; color: #856404;' ?>">
                                    <?= ucfirst(htmlspecialchars($sale['status'])) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <?php if ($sale['status'] == 'pending'): ?>
                                    <a href="?mark_as_completed=<?= $sale['transaction_id'] ?>&page=<?= $current_page ?><?= !empty($search_talent_title) ? '&talent_title=' . urlencode($search_talent_title) : '' ?><?= !empty($search_buyer_name) ? '&buyer_name=' . urlencode($search_buyer_name) : '' ?><?= !empty($search_seller_name) ? '&seller_name=' . urlencode($search_seller_name) : '' ?><?= !empty($status_filter) ? '&status_filter=' . urlencode($status_filter) : '' ?>" 
                                       onclick="return confirm('Mark this order as completed?');" 
                                       class="form-button" 
                                       style="background-color: var(--color-primary); padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">
                                        Mark Completed
                                    </a>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
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
                        if (!empty($search_talent_title)) $base_pagination_url .= '&talent_title=' . urlencode($search_talent_title);
                        if (!empty($search_buyer_name)) $base_pagination_url .= '&buyer_name=' . urlencode($search_buyer_name);
                        if (!empty($search_seller_name)) $base_pagination_url .= '&seller_name=' . urlencode($search_seller_name);
                        if (!empty($status_filter)) $base_pagination_url .= '&status_filter=' . urlencode($status_filter);
                        ?>
                        <?php if ($current_page > 1): ?>
                            <a href="<?= $base_pagination_url ?>&page=<?= $current_page - 1 ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; background-color: #6c757d;">Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="<?= $base_pagination_url ?>&page=<?= $i ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; <?= ($i == $current_page) ? 'background-color: var(--color-primary);' : 'background-color: #ccc; color: #333;' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($current_page < $total_pages): ?>
                            <a href="<?= $base_pagination_url ?>&page=<?= $current_page + 1 ?>" class="form-button" style="width: auto; display: inline-block; margin: 5px; background-color: #6c757d;">Next</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <p style="text-align: center;">No sales transactions to display matching your criteria.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
