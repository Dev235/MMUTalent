<?php
session_start();
require 'connection.php';

// Security check: User must be logged in and a talent ID must be provided
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$page_title = "Edit Talent";
require 'header.php';

$user_id = $_SESSION['user_id'];
// Get the user's role from the session, default to 'student' if not set
$user_role = $_SESSION['role'] ?? 'student'; 
$talent_id = intval($_GET['id']);
$message = '';

// --- MODIFIED PERMISSION LOGIC ---

// 1. Fetch the talent from the database first.
$stmt = $conn->prepare("SELECT * FROM services WHERE service_id = ?");
$stmt->bind_param("i", $talent_id);
$stmt->execute();
$result = $stmt->get_result();
$talent = $result->fetch_assoc();
$stmt->close();

// 2. Check for permission. Access is granted if:
//    a) The talent exists AND
//    b) The logged-in user is the owner OR the logged-in user's role is 'admin'.
if (!$talent || ($talent['user_id'] != $user_id && $user_role !== 'admin')) {
    echo "<div id='main-content' style='padding: 40px; text-align: center;'><h2>Access Denied</h2><p>Talent not found or you don't have permission to edit it.</p><a href='#' onclick='history.go(-1);'>Go Back</a></div>";
    require 'footer.php';
    exit();
}


// --- FORM SUBMISSION LOGIC (Remains the same) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $talent_title = trim($_POST['talent_title']);
    $talent_description = trim($_POST['talent_description']);
    $talent_image_filename = $talent['service_image']; // Keep old image by default

    if (isset($_FILES['talent_image']) && $_FILES['talent_image']['error'] == 0) {
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
        // If an admin edited it, redirect them to the catalogue. Otherwise, go to user dashboard.
        if ($user_role === 'admin') {
            header("Location: admin/managecatalogue.php?status=talent_updated"); // Assuming this is your admin page path
        } else {
            header("Location: userDashboard.php?status=talent_updated");
        }
        exit();
    } else {
        $message = "Error updating talent.";
    }
    $stmt->close();
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
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
                <img src="images/uploads/talent_images/<?php echo htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png'); ?>" alt="Talent Image" style="width: 150px; height: auto; margin-bottom: 10px;"><br>
                
                <label for="talent_image">Change Image:</label>
                <input type="file" id="talent_image" name="talent_image" accept="image/*" style="display: block; margin-bottom: 20px;">

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1;">Save Changes</button>
                    <a href="#" onclick="history.go(-1); return false;" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
