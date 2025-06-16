<?php
// Start the session to access session variables.
session_start();

// Include the database connection file.
require 'connection.php';

// Define the URL for the default profile picture
define('DEFAULT_AVATAR_URL', 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=No+Image');

// Check if the user is logged in. If not, redirect to the login page.
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Set the page title and include the header.
$page_title = "Edit Profile";
require 'header.php';

// Get user ID from session.
$user_id = $_SESSION['user_id'];
$message = ''; // To store success or error messages.

// Fetch the user's current data to get old profile picture info if needed.
$stmt = $conn->prepare("SELECT profile_picture, name FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$old_profile_picture = $user_data['profile_picture'];
$stmt->close();

// Handle the form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- Handle Profile Picture Upload ---
    $profile_picture_filename = $old_profile_picture; // Default to old picture
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // ** UPDATED PATH **
        $upload_dir = 'images/uploads/profile_pictures/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_picture']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            // Create a unique filename to prevent overwriting.
            $new_filename = uniqid() . '-' . basename($_FILES['profile_picture']['name']);
            $upload_file = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_file)) {
                $profile_picture_filename = $new_filename;
                // Delete the old picture if it exists
                if (!empty($old_profile_picture) && file_exists($upload_dir . $old_profile_picture)) {
                    unlink($upload_dir . $old_profile_picture);
                }
            } else {
                $message = "Error uploading file.";
            }
        } else {
            $message = "Invalid file type. Please upload a JPG, PNG, or GIF.";
        }
    }

    // --- Handle Text Data Update ---
    if (empty($message)) { // Proceed only if file upload was successful or not attempted
        $name = trim($_POST['name']);
        $student_id = trim($_POST['student_id']);
        $faculty = trim($_POST['faculty']);
        $date_of_birth = trim($_POST['date_of_birth']);
        $about_me = trim($_POST['about_me']);

        // Prepare the update statement
        $stmt = $conn->prepare("UPDATE users SET name = ?, student_id = ?, faculty = ?, date_of_birth = ?, about_me = ?, profile_picture = ? WHERE user_id = ?");
        $stmt->bind_param("ssssssi", $name, $student_id, $faculty, $date_of_birth, $about_me, $profile_picture_filename, $user_id);

        if ($stmt->execute()) {
            $_SESSION['name'] = $name; // Update session name
            // Redirect to dashboard on success
            header("Location: userDashboard.php?status=success");
            exit();
        } else {
            $message = "Error updating profile. Please try again.";
        }
        $stmt->close();
    }
}

// Fetch the user's most current data to pre-fill the form.
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Determine the correct image source
$profile_pic_src = DEFAULT_AVATAR_URL; // Default to URL
if (!empty($user['profile_picture'])) {
    // ** UPDATED PATH **
    $profile_pic_src = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
}

?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1>Edit Your Profile</h1>
            <p style="color: white;">Keep your talent profile up to date.</p>
        </div>

        <!-- Edit Profile Form -->
        <div class="profile-form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <?php if ($message): ?>
                <div style="padding: 10px; background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <!-- Add enctype for file uploads -->
            <form method="POST" action="" enctype="multipart/form-data">
                <div style="display: flex; align-items: center; margin-bottom: 20px;">
                    <!-- Profile Picture Uploader -->
                    <div class="profile-pic-container" style="margin-right: 20px; text-align: center;">
                        <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">
                        <label for="profile_picture" style="display: block; margin-top: 10px;">Change Photo:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="font-size: 0.9em;">
                    </div>

                    <!-- Basic Info Fields -->
                    <div style="flex-grow: 1;">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                        
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    </div>
                </div>

                <label for="faculty">Faculty:</label>
                <input type="text" id="faculty" name="faculty" value="<?php echo htmlspecialchars($user['faculty'] ?? ''); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

                <label for="about_me">About Me:</label>
                <textarea id="about_me" name="about_me" rows="5" style="width: 100%; padding: 8px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;"><?php echo htmlspecialchars($user['about_me'] ?? ''); ?></textarea>

                <!-- Action Buttons -->
                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1; background-color: var(--color-primary);">Save Changes</button>
                    <a href="userDashboard.php" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none; padding: 10px;">Cancel</a>
                </div>
            </form>
        </div>

    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
