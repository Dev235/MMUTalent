<?php
// As usual, gotta start session first to check if admin is logged in.
session_start();
// Include the database connection, if not cannot talk to database la.
require 'connection.php';

// This part is damn important for security.
// We check if the user is actually logged in AND if their role is 'admin'.
// If either one is not true, we kick them out to the login page.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit(); // Always exit after redirect.
}

// Just setting the title for the browser tab.
$page_title = "Manage Catalogue";
require 'header.php';

$successMessage = ""; // Prepare an empty variable for success messages.

// --- Handle Talent Deletion ---
// This block runs when the admin clicks the 'Delete' button.
// The link for delete button has something like "?action=delete&id=..."
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $talent_id_to_delete = intval($_GET['id']); // Get the ID of the talent to delete from the URL.
    
    // Before we delete the record from database, we should delete the image file from our server first.
    // If not, the image will become a ghost file, taking up space for no reason.
    $stmt = $conn->prepare("SELECT service_image FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $talent_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $talent_to_delete = $result->fetch_assoc();
        // Construct the full path to the image file.
        $image_file_path = 'images/uploads/talent_images/' . $talent_to_delete['service_image'];
        
        // Check if the image path is not empty and the file actually exists, then delete it.
        if (!empty($talent_to_delete['service_image']) && file_exists($image_file_path)) {
            unlink($image_file_path); // `unlink` is the PHP function for deleting a file.
        }

        // Okay, after the file is gone, now we can safely delete the record from the 'services' table.
        $delete_stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
        $delete_stmt->bind_param("i", $talent_id_to_delete);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        $successMessage = "Talent deleted successfully.";
    } else {
        $successMessage = "Talent not found.";
    }
    $stmt->close();
    // After deleting, we redirect back to this same page. This is to prevent re-deleting if the user refreshes.
    // We also pass the success message in the URL.
    header("Location: manageCatalogue.php?status=" . urlencode($successMessage));
    exit();
}

// --- Fetch all talents to display in the table ---
// This query is a bit more complex, it's a JOIN.
// We need to get the service details from `services` table AND the user's name from `users` table.
// So we JOIN them together where `s.user_id` matches `u.user_id`.
$stmt_talents = $conn->prepare(
    "SELECT s.service_id, s.service_title, s.service_image, u.name as user_name 
     FROM services s 
     JOIN users u ON s.user_id = u.user_id 
     ORDER BY s.service_id DESC"
);
$stmt_talents->execute();
$talents_result = $stmt_talents->get_result();
$stmt_talents->close();

// This is to display the message after we redirect from deleting.
if (isset($_GET['status'])) {
    $successMessage = htmlspecialchars($_GET['status']);
}

?>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Manage Catalogue Entries</h1>
        </div>

        <!-- If got success message, show it here -->
        <?php if (!empty($successMessage)): ?>
            <div style="text-align:center; margin-top:20px;">
                <p style="color: green; font-weight: bold; background-color: #d4edda; border: 1px solid #c3e6cb; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                    <?= $successMessage ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="table-container" style="max-width: 1200px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <!-- This is a basic HTML table to display all the talents -->
            <table border="1" style="width: 100%; border-collapse: collapse; text-align: left;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Image</th>
                        <th style="padding: 10px;">Title</th>
                        <th style="padding: 10px;">Offered By</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($talents_result->num_rows > 0): ?>
                        <!-- Loop through all the talents we fetched from the database -->
                        <?php while ($talent = $talents_result->fetch_assoc()): 
                            // This part is to prepare the image path.
                            $talent_image_src = 'images/uploads/talent_images/' . htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png');
                            // If the talent has no image or the file is missing, we use a placeholder link.
                            if (empty($talent['service_image']) || !file_exists($talent_image_src)) {
                                $talent_image_src = 'https://placehold.co/60x60/EFEFEF/AAAAAA&text=No+Image';
                            }
                        ?>
                            <tr>
                                <td style="padding: 10px;"><?= $talent['service_id'] ?></td>
                                <td style="padding: 10px;">
                                    <img src="<?= $talent_image_src ?>" alt="<?= htmlspecialchars($talent['service_title']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($talent['service_title']) ?></td>
                                <td style="padding: 10px;"><?= htmlspecialchars($talent['user_name']) ?></td>
                                <td style="padding: 10px;">
                                    <!-- These are the action buttons for each talent -->
                                    <a href="viewTalent.php?id=<?= $talent['service_id'] ?>" target="_blank" class="form-button" style="background-color: #6c757d; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-right: 5px;">View</a>
                                    <a href="editTalent.php?id=<?= $talent['service_id'] ?>" class="form-button" style="background-color: #ffc107; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-right: 5px;">Edit</a>
                                    <a href="?action=delete&id=<?= $talent['service_id'] ?>" onclick="return confirm('Are you sure you want to delete this talent? This action cannot be undone.');" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <!-- If no talents are found in the database at all -->
                        <tr>
                            <td colspan="5" style="padding: 10px; text-align: center;">No talents found in the catalogue.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
