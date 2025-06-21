<?php
// Session start first, standard procedure. Gotta know who is logged in.
session_start();
// Connect to the database, if not how to get the data right?
require 'connection.php';

// Security check here. Only logged-in students can see this page.
// If not a student (maybe guest or admin), we kick them out to the login page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Just setting the page title for the browser tab.
$page_title = "Full Sales History";
require 'header.php';

// Get the user's ID from the session. We need this for the SQL query.
$user_id = $_SESSION['user_id'];

// --- This part is to fetch ALL sales history for the logged-in user ---
// This is another one of those complex JOIN queries. Don't panic, lek je.
// We need data from THREE tables:
// 1. `transactions` (tr): To get the price, date, and status of the sale.
// 2. `services` (s): To get the name of the talent that was sold.
// 3. `users` (bu): To get the name and contact details of the person who BOUGHT the talent. 'bu' stands for buyer user.
// We link them all together using their IDs.
// The `WHERE s.user_id = ?` part is the most important, it makes sure we only get sales for OUR talents.
$full_sales_history_query = $conn->prepare(
    "SELECT tr.transaction_id, tr.price_at_purchase, tr.transaction_date, tr.status,
            s.service_title,
            bu.name as buyer_name, bu.email as buyer_email, bu.phone_number as buyer_phone
     FROM transactions tr
     JOIN services s ON tr.service_id = s.service_id
     JOIN users bu ON tr.buyer_user_id = bu.user_id
     WHERE s.user_id = ?
     ORDER BY tr.transaction_date DESC"
);
if ($full_sales_history_query) {
    // Bind our user ID to the '?' placeholder.
    $full_sales_history_query->bind_param("i", $user_id);
    // Run the query.
    $full_sales_history_query->execute();
    // Get all the results.
    $full_sales_history_result = $full_sales_history_query->get_result();
    $full_sales_history_query->close();
} else {
    // If something goes wrong with the SQL, this will log the error on the server.
    error_log("Failed to prepare statement for full sales history: " . $conn->error);
}

// --- This is the logic to handle the 'Mark as Completed' button ---
// It's the same logic as in the userDashboard.php, just copied here.
if (isset($_GET['mark_as_completed'])) {
    $transaction_id_to_mark = intval($_GET['mark_as_completed']);
    
    // Security check again, make sure the transaction belongs to the logged-in user before updating.
    $check_stmt = $conn->prepare(
        "SELECT tr.transaction_id
         FROM transactions tr
         JOIN services s ON tr.service_id = s.service_id
         WHERE tr.transaction_id = ? AND s.user_id = ? AND tr.status = 'pending'"
    );
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $transaction_id_to_mark, $user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            // If the check passes, then we update the status to 'completed'.
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $transaction_id_to_mark);
                $update_stmt->execute();
                $update_stmt->close();
                // Redirect back to this same page to show the updated status.
                header("Location: fullSalesHistory.php?status=sale_completed"); 
                exit();
            }
        }
        $check_stmt->close();
    }
}

// This is to show a small success message after the redirect.
$status_message = '';
if (isset($_GET['status']) && $_GET['status'] == 'sale_completed') {
    $status_message = '<p style="color: green; text-align: center; font-weight: bold;">Order marked as completed!</p>';
}

?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Full Sales History</h1>
            <p style="color: white;">All sales for your talents.</p>
        </div>

        <div class="table-container" style="max-width: 1000px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <!-- Display the success message here if there is one -->
            <?= $status_message ?>
            <a href="userDashboard.php" class="form-button" style="width: auto; display: inline-block; margin-bottom: 20px; background-color: #6c757d;">&larr; Back to Dashboard</a>
            
            <?php if ($full_sales_history_result && $full_sales_history_result->num_rows > 0): ?>
                <table style="width: 100%; border-collapse: collapse; margin-top: 20px; text-align: left;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="padding: 10px;">Talent Sold</th>
                            <th style="padding: 10px;">Buyer Name</th>
                            <th style="padding: 10px;">Buyer Email</th>
                            <th style="padding: 10px;">Buyer Phone</th>
                            <th style="padding: 10px; text-align: right;">Price (RM)</th>
                            <th style="padding: 10px; text-align: right;">Date</th>
                            <th style="padding: 10px; text-align: center;">Status</th>
                            <th style="padding: 10px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loop through all the sales history records and display them in the table -->
                        <?php while ($sale = $full_sales_history_result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['service_title']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_name']) ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_email'] ?? 'N/A') ?></td>
                            <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_phone'] ?? 'N/A') ?></td>
                            <td style="padding: 10px; text-align: right;"><?= number_format($sale['price_at_purchase'], 2) ?></td>
                            <td style="padding: 10px; text-align: right;"><?= date('Y-m-d H:i', strtotime($sale['transaction_date'])) ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <!-- This is just some style to make the status look nice, green for completed, yellow for pending. -->
                                <span style="padding: 5px 10px; border-radius: 5px; font-weight: bold; background-color: <?= ($sale['status'] == 'completed') ? '#d4edda; color: #155724;' : '#fff3cd; color: #856404;' ?>">
                                    <?= ucfirst(htmlspecialchars($sale['status'])) ?>
                                </span>
                            </td>
                            <td style="padding: 10px; text-align: center;">
                                <?php if ($sale['status'] == 'pending'): ?>
                                    <!-- Only show the 'Mark Completed' button if the status is still pending. -->
                                    <a href="?mark_as_completed=<?= $sale['transaction_id'] ?>" 
                                       onclick="return confirm('Mark this order as completed?');" 
                                       class="form-button" 
                                       style="background-color: var(--color-primary); padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">
                                        Mark Completed
                                    </a>
                                <?php else: ?>
                                    <!-- If it's already completed, just show a dash. -->
                                    —
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center;">You have no sales transactions yet.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
