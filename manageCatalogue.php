<?php
session_start();
require 'connection.php';

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Manage Catalogue";
require 'header.php';

$successMessage = "";

// Handle Talent Deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $talent_id_to_delete = intval($_GET['id']);
    
    // First, get the image filename to delete the file
    $stmt = $conn->prepare("SELECT service_image FROM services WHERE service_id = ?");
    $stmt->bind_param("i", $talent_id_to_delete);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $talent_to_delete = $result->fetch_assoc();
        $image_file_path = 'images/uploads/talent_images/' . $talent_to_delete['service_image']; // Adjust path
        
        // Delete image file if it exists and is not a placeholder
        if (!empty($talent_to_delete['service_image']) && $talent_to_delete['service_image'] !== 'service_placeholder.png' && file_exists($image_file_path)) {
            unlink($image_file_path);
        }

        // Now, delete the record from the database
        $delete_stmt = $conn->prepare("DELETE FROM services WHERE service_id = ?");
        $delete_stmt->bind_param("i", $talent_id_to_delete);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        $successMessage = "Talent deleted successfully.";
    } else {
        $successMessage = "Talent not found.";
    }
    $stmt->close();
    header("Location: manageCatalogue.php?status=" . urlencode($successMessage)); // Redirect to show message
    exit();
}

// Fetch all talents from the database
$stmt_talents = $conn->prepare(
    "SELECT s.service_id, s.service_title, s.service_image, u.name as user_name 
     FROM services s 
     JOIN users u ON s.user_id = u.user_id 
     ORDER BY s.service_id DESC"
);
$stmt_talents->execute();
$talents_result = $stmt_talents->get_result();
$stmt_talents->close();

// Check for status message from redirect
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

        <?php if (!empty($successMessage)): ?>
            <div style="text-align:center; margin-top:20px;">
                <p style="color: green; font-weight: bold; background-color: #d4edda; border: 1px solid #c3e6cb; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                    <?= $successMessage ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="table-container" style="max-width: 1200px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
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
                        <?php while ($talent = $talents_result->fetch_assoc()): 
                            $talent_image_src = 'images/uploads/talent_images/' . htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png');
                            if (empty($talent['service_image']) || !file_exists($talent_image_src)) {
                                $talent_image_src = 'https://placehold.co/60x60/EFEFEF/AAAAAA&text=No+Image'; // Placeholder if no image or file missing
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
                                    <a href="viewTalent.php?id=<?= $talent['service_id'] ?>" target="_blank" class="form-button" style="background-color: #6c757d; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-right: 5px;">View</a>
                                    <a href="editTalent.php?id=<?= $talent['service_id'] ?>" class="form-button" style="background-color: #ffc107; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-right: 5px;">Edit</a>
                                    <a href="?action=delete&id=<?= $talent['service_id'] ?>" onclick="return confirm('Are you sure you want to delete this talent? This action cannot be undone.');" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
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