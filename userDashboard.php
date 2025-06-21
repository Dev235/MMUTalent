<?php
// Start the session to access session variables.
session_start();

// Include the database connection file.
require 'connection.php';

// --- NEW LOGIC TO DETERMINE WHICH PROFILE TO SHOW ---
$profile_user_id = null;
$is_own_profile = false;

// Case 1: A specific user's profile is requested via URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $profile_user_id = intval($_GET['id']);
    // Check if the logged-in user is viewing their own profile
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_user_id) {
        $is_own_profile = true;
    }
} 
// Case 2: No specific profile requested, so show the logged-in user's profile
else if (isset($_SESSION['user_id'])) {
    $profile_user_id = $_SESSION['user_id'];
    $is_own_profile = true;
} 
// Case 3: Not logged in and no profile requested, redirect to login page.
else {
    header("Location: login.php");
    exit();
}


// --- Handle Talent Deletion (only if viewing own profile) ---
if ($is_own_profile && isset($_GET['delete_talent'])) {
    $talent_id_to_delete = intval($_GET['delete_talent']);
    $user_id_for_security = $_SESSION['user_id'];
    
    // First, get the image filename to delete the file
    $stmt = $conn->prepare("SELECT service_image FROM services WHERE service_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $talent_id_to_delete, $user_id_for_security);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $talent_to_delete = $result->fetch_assoc();
            $image_file = 'images/uploads/talent_images/' . $talent_to_delete['service_image'];
            if (!empty($talent_to_delete['service_image']) && file_exists($image_file)) {
                unlink($image_file);
            }

            // Now, delete the record from the database
            $delete_stmt = $conn->prepare("DELETE FROM services WHERE service_id = ? AND user_id = ?");
            if ($delete_stmt) {
                $delete_stmt->bind_param("ii", $talent_id_to_delete, $user_id_for_security);
                $delete_stmt->execute();
                $delete_stmt->close();
            } else {
                error_log("Failed to prepare statement for talent deletion: " . $conn->error);
            }
            
            header("Location: userDashboard.php?status=talent_deleted");
            exit();
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for fetching talent image: " . $conn->error);
    }
}

// --- NEW: Handle Marking a Sale as Completed ---
if ($is_own_profile && isset($_GET['mark_as_completed'])) {
    $transaction_id_to_mark = intval($_GET['mark_as_completed']);
    
    // Ensure the transaction belongs to a service owned by the current user
    $check_stmt = $conn->prepare(
        "SELECT tr.transaction_id
         FROM transactions tr
         JOIN services s ON tr.service_id = s.service_id
         WHERE tr.transaction_id = ? AND s.user_id = ? AND tr.status = 'pending'"
    );
    if ($check_stmt) {
        $check_stmt->bind_param("ii", $transaction_id_to_mark, $profile_user_id);
        $check_stmt->execute();
        $check_stmt->store_result();
        
        if ($check_stmt->num_rows > 0) {
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $transaction_id_to_mark);
                $update_stmt->execute();
                $update_stmt->close();
                header("Location: userDashboard.php?status=sale_completed");
                exit();
            } else {
                error_log("Failed to prepare statement for marking sale as completed: " . $conn->error);
            }
        }
        $check_stmt->close();
    } else {
        error_log("Failed to prepare statement for checking transaction ownership: " . $conn->error);
    }
}


// --- Fetch All Profile Data from the Database using the correct ID ---
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();
} else {
    error_log("Failed to prepare statement for fetching user data: " . $conn->error);
}


// If user doesn't exist, show error
if (!$user) {
    $page_title = "User Not Found";
    require 'header.php';
    echo "<div id='main-content'><p>This user does not exist.</p></div>";
    require 'footer.php';
    exit();
}


// Fetch all talents offered by this user.
$stmt = $conn->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY service_id");
if ($stmt) {
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $talents_result = $stmt->get_result();
    $stmt->close();
} else {
    error_log("Failed to prepare statement for fetching user talents: " . $conn->error);
}


// --- NEW: Fetch Purchase History for the logged-in user (as a buyer) ---
$purchase_history_result = null;
if ($is_own_profile) {
    $purchase_history_query = $conn->prepare(
        "SELECT t.transaction_id, s.service_title, s.service_image, u.name as seller_name, u.email as seller_email, u.phone_number as seller_phone, t.price_at_purchase, t.transaction_date, t.status
         FROM transactions t
         JOIN services s ON t.service_id = s.service_id
         JOIN users u ON s.user_id = u.user_id
         WHERE t.buyer_user_id = ?
         ORDER BY t.transaction_date DESC"
    );
    if ($purchase_history_query) {
        $purchase_history_query->bind_param("i", $profile_user_id);
        $purchase_history_query->execute();
        $purchase_history_result = $purchase_history_query->get_result();
        $purchase_history_query->close();
    } else {
        error_log("Failed to prepare statement for purchase history: " . $conn->error);
    }
}

// --- NEW: Fetch Sales History for talents offered by this user (as a seller) ---
$sales_history_result = null;
if ($is_own_profile) {
    $sales_history_query = $conn->prepare(
        "SELECT tr.transaction_id, tr.price_at_purchase, tr.transaction_date, tr.status,
                s.service_title,
                bu.name as buyer_name
         FROM transactions tr
         JOIN services s ON tr.service_id = s.service_id
         JOIN users bu ON tr.buyer_user_id = bu.user_id
         WHERE s.user_id = ?
         ORDER BY tr.transaction_date DESC
         LIMIT 3" // Display only latest 3 sales
    );
    if ($sales_history_query) {
        $sales_history_query->bind_param("i", $profile_user_id);
        $sales_history_query->execute();
        $sales_history_result = $sales_history_query->get_result();
        $sales_history_query->close();
    } else {
        error_log("Failed to prepare statement for sales history: " . $conn->error);
    }
}


// Set the page title and include the header.
$page_title = htmlspecialchars($user['name']) . "'s Profile";
require 'header.php';

// Determine the correct image source for the profile picture
// UPDATED LOGIC: Use default_avatar.png if not empty AND file exists, otherwise use the placeholder
$profile_pic_src = 'images/uploads/profile_pictures/default_avatar.png'; // Default local avatar
if (!empty($user['profile_picture']) && file_exists('images/uploads/profile_pictures/' . $user['profile_picture'])) {
    $profile_pic_src = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
} else if (!empty($user['profile_picture']) && !file_exists('images/uploads/profile_pictures/' . $user['profile_picture'])) {
    // Fallback if the profile_picture entry exists but the file does not (e.g., deleted manually)
    $profile_pic_src = 'images/uploads/profile_pictures/default_avatar.png';
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>MMU GOT TALENT</h1>
            <p style="color: white;"><?php echo htmlspecialchars($user['name']); ?>'s Profile</p>
        </div>
        <div class="profile-view-container" style="max-width: 1000px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; gap: 40px;">
            <aside class="profile-sidebar" style="flex: 1; min-width: 250px;">
                <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" style="width: 100%; max-width: 200px; height: auto; border-radius: 50%; object-fit: cover; border: 4px solid #eee; display: block; margin: 0 auto 20px auto;">
                <div class="profile-details" style="text-align: left;">
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Name:</strong><br><?php echo htmlspecialchars($user['name']); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Email:</strong><br><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></p> <!-- NEW: Display Email -->
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Phone:</strong><br><?php echo htmlspecialchars($user['phone_number'] ?? 'Not set'); ?></p> <!-- NEW: Display Phone Number -->
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Date of Birth:</strong><br><?php echo htmlspecialchars($user['date_of_birth'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Faculty:</strong><br><?php echo htmlspecialchars($user['faculty'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><strong>Student ID:</strong><br><?php echo htmlspecialchars($user['student_id'] ?? 'Not set'); ?></p>
                </div>

                <?php if ($is_own_profile): ?>
                    <a href="editProfile.php" class="form-button" style="text-decoration: none; text-align:center; display: block; background-color: var(--color-primary);">Edit Profile</a>
                <?php endif; ?>
            </aside>
            <main class="profile-main-content" style="flex: 2; min-width: 300px;">
                <section id="about-me">
                    <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px; margin-top: 0;">About Me</h2>
                    <p><?php echo !empty($user['about_me']) ? nl2br(htmlspecialchars($user['about_me'])) : 'This user has not provided a description yet.'; ?></p>
                </section>
                <section id="talents-offered" style="margin-top: 40px;">
                    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">
                       <h2 style="color: var(--color-title); margin:0;">Talents Offered</h2>
                       <?php if ($is_own_profile): ?>
                           <a href="addTalent.php" class="form-button" style="width:auto; padding: 8px 12px; font-size: 0.9em;">+ Add New Talent</a>
                       <?php endif; ?>
                    </div>
                    <div class="talents-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-top: 20px;">
                        <?php if ($talents_result->num_rows > 0): ?>
                            <?php while ($talent = $talents_result->fetch_assoc()): ?>
                                <div class="talent-card" style="display: flex; flex-direction: column; background-color:#f9f9f9; padding:15px; border-radius:8px; text-align: center;">
                                    <a href="viewTalent.php?id=<?php echo $talent['service_id']; ?>" style="display: block; text-decoration: none; color: inherit;">
                                        <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>" style="width: 100%; height: 140px; object-fit: cover; background-color: #eee; border-radius: 8px;">
                                        <div class="talent-info" style="flex-grow: 1; padding: 10px 0;">
                                            <h4 style="margin: 0 0 5px 0;"><?php echo htmlspecialchars($talent['service_title']); ?></h4>
                                        </div>
                                    </a>
                                    <p style="margin: 0; font-size: 0.9em; color: #555; flex-grow: 1;"><?php echo htmlspecialchars($talent['service_description']); ?></p>
                                    <?php if ($is_own_profile): ?>
                                        <div class="talent-actions" style="display:flex; gap: 5px; margin-top: 10px;">
                                           <a href="editTalent.php?id=<?php echo $talent['service_id']; ?>" class="form-button" style="flex:1; background-color:#ffc107; font-size:0.8em; padding: 8px 10px; text-align: center;">Edit</a>
                                           <a href="?delete_talent=<?php echo $talent['service_id']; ?>" class="form-button" onclick="return confirm('Are you sure you want to delete this talent?');" style="flex:1; background-color:#dc3545; font-size:0.8em; padding: 8px 10px; text-align: center;">Delete</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No talents have been added yet.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <?php if ($is_own_profile): ?>
                <section id="purchase-history" style="margin-top: 40px;">
                    <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">My Purchase History</h2>
                    <?php if ($purchase_history_result && $purchase_history_result->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 10px; text-align: left;">Talent</th>
                                    <th style="padding: 10px; text-align: left;">Seller</th>
                                    <th style="padding: 10px; text-align: left;">Seller Email</th> <!-- NEW -->
                                    <th style="padding: 10px; text-align: left;">Seller Phone</th> <!-- NEW -->
                                    <th style="padding: 10px; text-align: right;">Price (RM)</th>
                                    <th style="padding: 10px; text-align: right;">Date</th>
                                    <th style="padding: 10px; text-align: center;">Status</th> <!-- NEW -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($purchase = $purchase_history_result->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 10px;"><?= htmlspecialchars($purchase['service_title']) ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($purchase['seller_name']) ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($purchase['seller_email'] ?? 'N/A') ?></td> <!-- NEW -->
                                    <td style="padding: 10px;"><?= htmlspecialchars($purchase['seller_phone'] ?? 'N/A') ?></td> <!-- NEW -->
                                    <td style="padding: 10px; text-align: right;"><?= number_format($purchase['price_at_purchase'], 2) ?></td>
                                    <td style="padding: 10px; text-align: right;"><?= date('Y-m-d H:i', strtotime($purchase['transaction_date'])) ?></td>
                                    <td style="padding: 10px; text-align: center;">
                                        <span style="padding: 5px 10px; border-radius: 5px; font-weight: bold; background-color: <?= ($purchase['status'] == 'completed') ? '#d4edda; color: #155724;' : '#fff3cd; color: #856404;' ?>">
                                            <?= ucfirst(htmlspecialchars($purchase['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>You have not made any purchases yet.</p>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

                <?php if ($is_own_profile): ?>
                <section id="sales-history" style="margin-top: 40px;">
                    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">
                        <h2 style="color: var(--color-title); margin:0;">My Sales History (Latest 3)</h2> <!-- NEW: Title reflects limit -->
                        <a href="fullSalesHistory.php" class="form-button" style="width:auto; padding: 8px 12px; font-size: 0.9em;">View All Sales</a> <!-- NEW: Link to full history -->
                    </div>
                    <?php if ($sales_history_result && $sales_history_result->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 10px; text-align: left;">Talent Sold</th>
                                    <th style="padding: 10px; text-align: left;">Buyer</th>
                                    <th style="padding: 10px; text-align: right;">Price (RM)</th>
                                    <th style="padding: 10px; text-align: right;">Date</th>
                                    <th style="padding: 10px; text-align: center;">Status</th>
                                    <th style="padding: 10px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sale = $sales_history_result->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 10px;"><?= htmlspecialchars($sale['service_title']) ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_name']) ?></td>
                                    <td style="padding: 10px; text-align: right;"><?= number_format($sale['price_at_purchase'], 2) ?></td>
                                    <td style="padding: 10px; text-align: right;"><?= date('Y-m-d H:i', strtotime($sale['transaction_date'])) ?></td>
                                    <td style="padding: 10px; text-align: center;">
                                        <span style="padding: 5px 10px; border-radius: 5px; font-weight: bold; background-color: <?= ($sale['status'] == 'completed') ? '#d4edda; color: #155724;' : '#fff3cd; color: #856404;' ?>">
                                            <?= ucfirst(htmlspecialchars($sale['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="padding: 10px; text-align: center;">
                                        <?php if ($sale['status'] == 'pending'): ?>
                                            <a href="?mark_as_completed=<?= $sale['transaction_id'] ?>" 
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
                    <?php else: ?>
                        <p>You have not made any sales yet.</p>
                    <?php endif; ?>
                </section>
                <?php endif; ?>

            </main>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
