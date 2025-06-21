<?php
// Okay, this is the main dashboard page, need to start the session first la.
// If not, cannot access any of the $_SESSION variables.
session_start();

// Gotta link to the database, if not how to get the data deyh.
require 'connection.php';

// Just a placeholder link for the default profile picture, in case the user never upload.
define('DEFAULT_AVATAR_URL', 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=No+Image');

// --- Okay this part abit confusing... need to figure out whose profile to show ---
$profile_user_id = null;
$is_own_profile = false; // This one is to check whether to show the 'Edit' button later.

// Case 1: Someone click on a profile from catalogue or wherever. Got 'id' in the URL.
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $profile_user_id = intval($_GET['id']); // intval is for security, make sure it's a number.
    // Check if I'm viewing my own profile through the URL.
    if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_user_id) {
        $is_own_profile = true;
    }
} 
// Case 2: I just click 'My Profile' from the navbar. No 'id' in URL.
else if (isset($_SESSION['user_id'])) {
    // So just show my own profile la, using my session ID.
    $profile_user_id = $_SESSION['user_id'];
    $is_own_profile = true;
} 
// Case 3: Some random person not logged in tries to access this page. Kick them out.
else {
    header("Location: login.php");
    exit(); // Important to stop the script, if not it will continue running.
}


// --- This part is for deleting talents. Can only run if it's my own profile ---
if ($is_own_profile && isset($_GET['delete_talent'])) {
    $talent_id_to_delete = intval($_GET['delete_talent']);
    $user_id_for_security = $_SESSION['user_id']; // Double confirm it's me.
    
    // Before deleting from DB, must get the image name to delete the file also.
    $stmt = $conn->prepare("SELECT service_image FROM services WHERE service_id = ? AND user_id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $talent_id_to_delete, $user_id_for_security);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $talent_to_delete = $result->fetch_assoc();
            $image_file = 'images/uploads/talent_images/' . $talent_to_delete['service_image'];
            // Check if got image and if the file really exists on the server.
            if (!empty($talent_to_delete['service_image']) && file_exists($image_file)) {
                unlink($image_file); // unlink() is the function to delete file.
            }

            // After deleting file, now can delete the record from the database.
            $delete_stmt = $conn->prepare("DELETE FROM services WHERE service_id = ? AND user_id = ?");
            if ($delete_stmt) {
                $delete_stmt->bind_param("ii", $talent_id_to_delete, $user_id_for_security);
                $delete_stmt->execute();
                $delete_stmt->close();
            }
        }
        $stmt->close();
    }
    // After done, refresh the page so the deleted talent is gone.
    header("Location: userDashboard.php?status=talent_deleted");
    exit();
}

// --- NEW STUFF: For seller to mark a sale as 'completed' ---
if ($is_own_profile && isset($_GET['mark_as_completed'])) {
    $transaction_id_to_mark = intval($_GET['mark_as_completed']);
    
    // This query is to double check la, make sure the sale really belongs to me.
    // Cannot simply change other people's sales status.
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
        
        // If the query finds one matching row, then proceed to update.
        if ($check_stmt->num_rows > 0) {
            $update_stmt = $conn->prepare("UPDATE transactions SET status = 'completed' WHERE transaction_id = ?");
            if ($update_stmt) {
                $update_stmt->bind_param("i", $transaction_id_to_mark);
                $update_stmt->execute();
                $update_stmt->close();
                header("Location: userDashboard.php?status=sale_completed");
                exit();
            }
        }
        $check_stmt->close();
    }
}


// --- Okay, now fetch all the profile data from database using the correct ID ---
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
if ($stmt) {
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc(); // Get all the user data into this $user variable.
    $stmt->close();
}


// If user ID not found in database, just show error message.
if (!$user) {
    $page_title = "User Not Found";
    require 'header.php';
    echo "<div id='main-content'><p>Deyh, this user does not exist la.</p></div>";
    require 'footer.php';
    exit();
}


// Same thing, get all the talents from the `services` table for this user.
$stmt = $conn->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY service_id");
if ($stmt) {
    $stmt->bind_param("i", $profile_user_id);
    $stmt->execute();
    $talents_result = $stmt->get_result();
    $stmt->close();
}


// --- NEW: Fetch Purchase History for me (as a buyer) ---
$purchase_history_result = null;
if ($is_own_profile) {
    // This query is a bit more complex, need to JOIN three tables to get all the info.
    // Basically, we want to know what service was bought (from `services`), who sold it (from `users`), and the transaction details (from `transactions`).
    // So must link all three tables together using their IDs.
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
    }
}

// --- NEW: Fetch Sales History for talents I sold (as a seller) ---
$sales_history_result = null;
if ($is_own_profile) {
    // Another JOIN query to see who bought my stuff.
    // Almost same as above, but this time we check where the service's user_id is mine.
    $sales_history_query = $conn->prepare(
        "SELECT tr.transaction_id, tr.price_at_purchase, tr.transaction_date, tr.status,
                s.service_title,
                bu.name as buyer_name
         FROM transactions tr
         JOIN services s ON tr.service_id = s.service_id
         JOIN users bu ON tr.buyer_user_id = bu.user_id
         WHERE s.user_id = ?
         ORDER BY tr.transaction_date DESC
         LIMIT 3" // Just show the latest 3 sales on the dashboard, don't want to make it too long.
    );
    if ($sales_history_query) {
        $sales_history_query->bind_param("i", $profile_user_id);
        $sales_history_query->execute();
        $sales_history_result = $sales_history_query->get_result();
        $sales_history_query->close();
    }
}


// Set the page title and include the header.
$page_title = htmlspecialchars($user['name']) . "'s Profile";
require 'header.php';

// Check which profile pic to use.
$profile_pic_src = 'images/uploads/profile_pictures/default_avatar.png'; // By default, use this one.
if (!empty($user['profile_picture']) && file_exists('images/uploads/profile_pictures/' . $user['profile_picture'])) {
    // If user uploaded a picture and the file exists, then use their picture.
    $profile_pic_src = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
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
            <!-- Left side of the profile page -->
            <aside class="profile-sidebar" style="flex: 1; min-width: 250px;">
                <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" style="width: 100%; max-width: 200px; height: auto; border-radius: 50%; object-fit: cover; border: 4px solid #eee; display: block; margin: 0 auto 20px auto;">
                <div class="profile-details" style="text-align: left;">
                    <!-- Just echo out all the user details we got from the DB earlier. -->
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Name:</strong><br><?php echo htmlspecialchars($user['name']); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Email:</strong><br><?php echo htmlspecialchars($user['email'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Phone:</strong><br><?php echo htmlspecialchars($user['phone_number'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Date of Birth:</strong><br><?php echo htmlspecialchars($user['date_of_birth'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Faculty:</strong><br><?php echo htmlspecialchars($user['faculty'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><strong>Student ID:</strong><br><?php echo htmlspecialchars($user['student_id'] ?? 'Not set'); ?></p>
                </div>

                <?php if ($is_own_profile): ?>
                    <!-- This `if` statement is damn useful. Only if it's my own profile, then the 'Edit Profile' button will show up. Lek la. -->
                    <a href="editProfile.php" class="form-button" style="text-decoration: none; text-align:center; display: block; background-color: var(--color-primary);">Edit Profile</a>
                <?php endif; ?>
            </aside>
            <!-- Right side of the profile page -->
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
                        <?php if ($talents_result && $talents_result->num_rows > 0): ?>
                            <!-- This `while` loop is for showing all the talents one by one. Like a for loop la, but for database results. -->
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
                                        <!-- Same thing, only show edit/delete buttons on my own talents. -->
                                        <div class="talent-actions" style="display:flex; gap: 5px; margin-top: 10px;">
                                           <a href="editTalent.php?id=<?php echo $talent['service_id']; ?>" class="form-button" style="flex:1; background-color:#ffc107; font-size:0.8em; padding: 8px 10px; text-align: center;">Edit</a>
                                           <a href="?delete_talent=<?php echo $talent['service_id']; ?>" class="form-button" onclick="return confirm('Are you sure you want to delete this talent?');" style="flex:1; background-color:#dc3545; font-size:0.8em; padding: 8px 10px; text-align: center;">Delete</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>This user has not added any talents yet.</p>
                        <?php endif; ?>
                    </div>
                </section>

                <!-- NEW SECTION: MY PURCHASE HISTORY -->
                <?php if ($is_own_profile && $purchase_history_result): ?>
                <section id="purchase-history" style="margin-top: 40px;">
                    <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">My Purchase History</h2>
                    <?php if ($purchase_history_result->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 10px; text-align: left;">Talent</th>
                                    <th style="padding: 10px; text-align: left;">Seller Contact</th>
                                    <th style="padding: 10px; text-align: right;">Price (RM)</th>
                                    <th style="padding: 10px; text-align: center;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($purchase = $purchase_history_result->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 10px;"><?= htmlspecialchars($purchase['service_title']) ?></td>
                                    <td style="padding: 10px;">
                                        <?= htmlspecialchars($purchase['seller_name']) ?><br>
                                        <small><?= htmlspecialchars($purchase['seller_email'] ?? 'N/A') ?></small><br>
                                        <small><?= htmlspecialchars($purchase['seller_phone'] ?? 'N/A') ?></small>
                                    </td>
                                    <td style="padding: 10px; text-align: right;"><?= number_format($purchase['price_at_purchase'], 2) ?></td>
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

                <!-- NEW SECTION: MY SALES HISTORY -->
                <?php if ($is_own_profile && $sales_history_result): ?>
                <section id="sales-history" style="margin-top: 40px;">
                    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">
                        <h2 style="color: var(--color-title); margin:0;">My Sales History</h2>
                        <a href="fullSalesHistory.php" class="form-button" style="width:auto; padding: 8px 12px; font-size: 0.9em;">View All</a>
                    </div>
                    <?php if ($sales_history_result->num_rows > 0): ?>
                        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                            <thead>
                                <tr style="background-color: #f2f2f2;">
                                    <th style="padding: 10px; text-align: left;">Talent Sold</th>
                                    <th style="padding: 10px; text-align: left;">Buyer</th>
                                    <th style="padding: 10px; text-align: center;">Status</th>
                                    <th style="padding: 10px; text-align: center;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($sale = $sales_history_result->fetch_assoc()): ?>
                                <tr>
                                    <td style="padding: 10px;"><?= htmlspecialchars($sale['service_title']) ?></td>
                                    <td style="padding: 10px;"><?= htmlspecialchars($sale['buyer_name']) ?></td>
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
