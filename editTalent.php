<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$page_title = "Edit Talent";
require 'header.php';

$user_id = $_SESSION['user_id'];
$talent_id = intval($_GET['id']);
$message = '';

// Fetch existing talent to ensure it belongs to the user
$stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ? AND user_id = ?");
$stmt->bind_param("ii", $talent_id, $user_id);
$stmt->execute();
$talent = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$talent) {
    echo "<p>Talent not found or you don't have permission to edit it.</p>";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talent_title = trim($_POST['talent_title']);
    $talent_description = trim($_POST['talent_description']);
    $talent_image_filename = $talent['service_image']; // Keep old image by default

    if (isset($_FILES['talent_image']) && $_FILES['talent_image']['error'] == 0) {
        // ** UPDATED PATH **
        $upload_dir = 'images/uploads/talent_images/';
        $new_filename = uniqid() . '-' . basename($_FILES['talent_image']['name']);
        $upload_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['talent_image']['tmp_name'], $upload_file)) {
            // Delete old image if it exists
            if (!empty($talent_image_filename) && file_exists($upload_dir . $talent_image_filename)) {
                unlink($upload_dir . $talent_image_filename);
            }
            $talent_image_filename = $new_filename;
        }
    }

    $stmt = $conn->prepare("UPDATE services SET service_title = ?, service_description = ?, service_image = ? WHERE service_id = ?");
    $stmt->bind_param("sssi", $talent_title, $talent_description, $talent_image_filename, $talent_id);

    if ($stmt->execute()) {
        header("Location: userDashboard.php?status=talent_updated");
        exit();
    } else {
        $message = "Error updating talent.";
    }
    $stmt->close();
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1>Edit Talent</h1>
        </div>
        <div class="profile-form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px;">
             <form method="POST" action="" enctype="multipart/form-data">
                <label for="talent_title">Talent Title:</label>
                <input type="text" id="talent_title" name="talent_title" value="<?php echo htmlspecialchars($talent['service_title']); ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="talent_description">Talent Description:</label>
                <textarea id="talent_description" name="talent_description" rows="5" style="width: 100%; padding: 8px; margin-bottom: 10px;"><?php echo htmlspecialchars($talent['service_description']); ?></textarea>

                <label for="talent_image">Current Image:</label><br>
                <!-- ** UPDATED PATH ** -->
                <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="Talent Image" style="width: 150px; height: auto; margin-bottom: 10px;"><br>
                
                <label for="talent_image">Change Image:</label>
                <input type="file" id="talent_image" name="talent_image" accept="image/*" style="display: block; margin-bottom: 20px;">

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1;">Save Changes</button>
                    <a href="userDashboard.php" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
