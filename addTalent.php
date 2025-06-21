<?php
session_start();
require 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$page_title = "Add New Talent";
require 'header.php';

$user_id = $_SESSION['user_id'];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talent_title = trim($_POST['talent_title']);
    $talent_description = trim($_POST['talent_description']);
    $talent_price = floatval($_POST['talent_price']); // Get the price
    $talent_image_filename = null;

    if (isset($_FILES['talent_image']) && $_FILES['talent_image']['error'] == 0) {
        // ** UPDATED PATH **
        $upload_dir = 'images/uploads/talent_images/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $new_filename = uniqid() . '-' . basename($_FILES['talent_image']['name']);
        $upload_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['talent_image']['tmp_name'], $upload_file)) {
            $talent_image_filename = $new_filename;
        } else {
            $message = "Error uploading talent image.";
        }
    }

    if (empty($message)) {
        // The table is `services`, but we call it "talent" on the frontend.
        // Updated INSERT query to include service_price
        $stmt = $conn->prepare("INSERT INTO services (user_id, service_title, service_description, service_image, service_price) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("isssd", $user_id, $talent_title, $talent_description, $talent_image_filename, $talent_price); // Added 'd' for double/float

        if ($stmt->execute()) {
            header("Location: userDashboard.php?status=talent_added");
            exit();
        } else {
            $message = "Error adding talent. Please try again.";
        }
        $stmt->close();
    }
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Add a New Talent</h1>
        </div>
        <div class="profile-form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px;">
            <?php if ($message): ?>
                <div style="color: red; margin-bottom: 15px;"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="" enctype="multipart/form-data">
                <label for="talent_title">Talent Title:</label>
                <input type="text" id="talent_title" name="talent_title" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="talent_description">Talent Description:</label>
                <textarea id="talent_description" name="talent_description" rows="5" style="width: 100%; padding: 8px; margin-bottom: 10px;"></textarea>

                <label for="talent_price">Price (RM):</label>
                <input type="number" id="talent_price" name="talent_price" step="0.01" min="0" required value="0.00" style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="talent_image">Talent Image:</label>
                <input type="file" id="talent_image" name="talent_image" accept="image/*" style="display: block; margin-bottom: 20px;">

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1;">Add Talent</button>
                    <a href="userDashboard.php" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>