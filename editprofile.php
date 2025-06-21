<?php
// Start the session to access session variables.
session_start();

// Include the database connection file.
require 'connection.php';

// Define the URL for the default profile picture
define('DEFAULT_AVATAR_URL', 'https://placehold.co/150x150/EFEFEF/AAAAAA&text=No+Image');

// --- CRITICAL: DETERMINE WHICH USER TO EDIT AND CHECK PERMISSIONS ---

// Check if user is logged in at all
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];
$current_user_role = $_SESSION['role'] ?? 'student';
$profile_id_to_edit = 0; // Initialize to 0

// Determine which profile ID to edit based on role and URL parameters
if ($current_user_role === 'admin' && isset($_GET['id'])) {
    // If the user is an admin and an ID is provided, they are editing someone else.
    $profile_id_to_edit = intval($_GET['id']);
} else {
    // Otherwise, users (including admins without a GET id) edit their own profile.
    $profile_id_to_edit = $current_user_id;
}

// Security check: If a non-admin tries to edit another profile via URL, deny access.
if ($current_user_role !== 'admin' && $profile_id_to_edit != $current_user_id) {
    die("Access Denied: You do not have permission to edit this profile.");
}


// --- Fetch the correct user's data for the form ---
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $profile_id_to_edit);
$stmt->execute();
$user_result = $stmt->get_result();
$user = $user_result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("User not found.");
}
$old_profile_picture = $user['profile_picture'];


// --- Handle the form submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile_picture_filename = $old_profile_picture;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $upload_dir = 'images/uploads/profile_pictures/';
        if (!is_dir($upload_dir)) { mkdir($upload_dir, 0755, true); }
        
        $new_filename = uniqid() . '-' . basename($_FILES['profile_picture']['name']);
        $upload_file = $upload_dir . $new_filename;

        if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_file)) {
            $profile_picture_filename = $new_filename;
            // Unlink old picture only if it's not the default one
            if (!empty($old_profile_picture) && $old_profile_picture !== 'default_avatar.png' && file_exists($upload_dir . $old_profile_picture)) {
                unlink($upload_dir . $old_profile_picture);
            }
        }
    }

    $name = trim($_POST['name']);
    $student_id = trim($_POST['student_id']);
    $faculty = trim($_POST['faculty']);
    $date_of_birth = trim($_POST['date_of_birth']);
    $about_me = trim($_POST['about_me']);

    // The UPDATE query now uses the correct ID ($profile_id_to_edit)
    $stmt = $conn->prepare("UPDATE users SET name = ?, student_id = ?, faculty = ?, date_of_birth = ?, about_me = ?, profile_picture = ? WHERE user_id = ?");
    $stmt->bind_param("ssssssi", $name, $student_id, $faculty, $date_of_birth, $about_me, $profile_picture_filename, $profile_id_to_edit);

    if ($stmt->execute()) {
        // If user edited their own profile, update session name
        if ($profile_id_to_edit == $current_user_id) {
            $_SESSION['name'] = $name;
        }
        
        // Smart Redirect
        if ($current_user_role === 'admin' && $current_user_id != $profile_id_to_edit) {
            header("Location: manageUsers.php?status=updated"); // Admin goes back to user list
        } else {
            header("Location: userDashboard.php?status=success"); // User goes back to their own dash
        }
        exit();
    }
    $stmt->close();
}


// Set the page title and include the header.
$page_title = "Edit Profile: " . htmlspecialchars($user['name']);
require 'header.php';

// Determine the correct image source
$profile_pic_src = DEFAULT_AVATAR_URL; 
if (!empty($user['profile_picture'])) {
    $profile_pic_src = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
}

?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1>Edit Profile: <?php echo htmlspecialchars($user['name']); ?></h1>
            <p style="color: white;">Keep your talent profile up to date.</p>
        </div>

        <div class="profile-form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <form method="POST" action="editProfile.php?id=<?php echo $profile_id_to_edit; ?>" enctype="multipart/form-data">
                <div style="display: flex; align-items: center; margin-bottom: 20px;">
                    <div class="profile-pic-container" style="margin-right: 20px; text-align: center;">
                        <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" style="width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 3px solid #ccc;">
                        <label for="profile_picture" style="display: block; margin-top: 10px;">Change Photo:</label>
                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="font-size: 0.9em;">
                    </div>

                    <div style="flex-grow: 1;">
                        <label for="name">Full Name:</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                        
                        <label for="student_id">Student ID:</label>
                        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($user['student_id'] ?? ''); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">
                    </div>
                </div>

                <label for="faculty">Faculty:</label>
                <select id="faculty" name="faculty" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px; background-color: white;">
                    <option value="">-- Select Your Faculty --</option>
                    <option value="FCI" <?php if (($user['faculty'] ?? '') == 'FCI') echo 'selected'; ?>>Faculty of Computing and Informatics (FCI)</option>
                    <option value="FOE" <?php if (($user['faculty'] ?? '') == 'FOE') echo 'selected'; ?>>Faculty of Engineering (FOE)</option>
                    <option value="FOM" <?php if (($user['faculty'] ?? '') == 'FOM') echo 'selected'; ?>>Faculty of Management (FOM)</option>
                    <option value="FCA" <?php if (($user['faculty'] ?? '') == 'FCA') echo 'selected'; ?>>Faculty of Creative Arts (FCA)</option>
                    <option value="FAC" <?php if (($user['faculty'] ?? '') == 'FAC') echo 'selected'; ?>>Faculty of Applied Communication (FAC)</option>
                    <option value="FCM" <?php if (($user['faculty'] ?? '') == 'FCM') echo 'selected'; ?>>Faculty of Cinematic Arts (FCM)</option>
                </select>

                <label for="date_of_birth">Date of Birth:</label>
                <input type="date" id="date_of_birth" name="date_of_birth" value="<?php echo htmlspecialchars($user['date_of_birth'] ?? ''); ?>" style="width: 100%; padding: 8px; margin-bottom: 10px; border: 1px solid #ccc; border-radius: 5px;">

                <label for="about_me">About Me:</label>
                <textarea id="about_me" name="about_me" rows="5" style="width: 100%; padding: 8px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px;"><?php echo htmlspecialchars($user['about_me'] ?? ''); ?></textarea>

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1; background-color: var(--color-primary);">Save Changes</button>
                    
                    <?php
                        $cancel_url = ($current_user_role === 'admin' && $current_user_id != $profile_id_to_edit) ? 'manageUsers.php' : 'userDashboard.php';
                    ?>
                    <a href="<?php echo $cancel_url; ?>" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none; padding: 10px;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
