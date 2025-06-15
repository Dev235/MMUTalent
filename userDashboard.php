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
$page_title = "My Profile";
require 'header.php';

// Retrieve user's ID from the session.
$user_id = $_SESSION['user_id'];

// --- Handle Talent Deletion ---
if (isset($_GET['delete_talent'])) {
    $talent_id_to_delete = intval($_GET['delete_talent']);
    
    // First, get the image filename to delete the file
    $stmt = $conn->prepare("SELECT service_image FROM services WHERE service_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $talent_id_to_delete, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $talent_to_delete = $result->fetch_assoc();
        // ** UPDATED PATH **
        $image_file = 'images/uploads/talent_images/' . $talent_to_delete['service_image'];
        if (!empty($talent_to_delete['service_image']) && file_exists($image_file)) {
            unlink($image_file);
        }

        // Now, delete the record from the database
        $delete_stmt = $conn->prepare("DELETE FROM services WHERE service_id = ? AND user_id = ?");
        $delete_stmt->bind_param("ii", $talent_id_to_delete, $user_id);
        $delete_stmt->execute();
        $delete_stmt->close();
        
        header("Location: userDashboard.php?status=talent_deleted");
        exit();
    }
    $stmt->close();
}


// --- Fetch All Profile Data from the Database ---

// 1. Fetch the user's full details from the 'users' table.
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 2. Fetch all talents offered by this user from the 'services' table.
$stmt = $conn->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY service_id");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$talents_result = $stmt->get_result();
$stmt->close();

// Determine the correct image source for the profile picture
$profile_pic_src = DEFAULT_AVATAR_URL; // Default to URL
if (!empty($user['profile_picture'])) {
    // ** UPDATED PATH **
    $profile_pic_src = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']);
}

?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1>MMU GOT TALENT</h1>
            <p style="color: white;"><?php echo htmlspecialchars($user['name']); ?>'s Profile</p>
        </div>

        <!-- Main Profile View Container -->
        <div class="profile-view-container" style="max-width: 1000px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); display: flex; flex-wrap: wrap; gap: 40px;">

            <!-- Left Sidebar for Profile Info -->
            <aside class="profile-sidebar" style="flex: 1; min-width: 250px;">
                <img src="<?php echo $profile_pic_src; ?>" alt="Profile Picture" style="width: 100%; max-width: 200px; height: auto; border-radius: 50%; object-fit: cover; border: 4px solid #eee; display: block; margin: 0 auto 20px auto;">
                
                <div class="profile-details" style="text-align: left;">
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Name:</strong><br><?php echo htmlspecialchars($user['name']); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Date of Birth:</strong><br><?php echo htmlspecialchars($user['date_of_birth'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 10px;"><strong>Faculty:</strong><br><?php echo htmlspecialchars($user['faculty'] ?? 'Not set'); ?></p>
                    <p style="background-color: #f9f9f9; padding: 10px; border-radius: 5px; margin-bottom: 20px;"><strong>Student ID:</strong><br><?php echo htmlspecialchars($user['student_id'] ?? 'Not set'); ?></p>
                </div>

                <a href="editProfile.php" class="form-button" style="text-decoration: none; text-align:center; display: block; background-color: var(--color-primary);">Edit Profile</a>
            </aside>

            <!-- Right Main Content for About and Talents -->
            <main class="profile-main-content" style="flex: 2; min-width: 300px;">
                <section id="about-me">
                    <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px; margin-top: 0;">About Me</h2>
                    <p><?php echo !empty($user['about_me']) ? nl2br(htmlspecialchars($user['about_me'])) : 'No information provided yet. Click "Edit Profile" to add a description!'; ?></p>
                </section>

                <section id="talents-offered" style="margin-top: 40px;">
                    <div style="display:flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">
                       <h2 style="color: var(--color-title); margin:0;">Talents Offered</h2>
                       <a href="addTalent.php" class="form-button" style="width:auto; padding: 8px 12px; font-size: 0.9em;">+ Add New Talent</a>
                    </div>
                    
                    <!-- ** UPDATED LAYOUT FOR TALENTS GRID ** -->
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
                                    <div class="talent-actions" style="display:flex; gap: 5px; margin-top: 10px;">
                                       <a href="editTalent.php?id=<?php echo $talent['service_id']; ?>" class="form-button" style="flex:1; background-color:#ffc107; font-size:0.8em; padding: 8px 10px; text-align: center;">Edit</a>
                                       <a href="?delete_talent=<?php echo $talent['service_id']; ?>" class="form-button" onclick="return confirm('Are you sure you want to delete this talent?');" style="flex:1; background-color:#dc3545; font-size:0.8em; padding: 8px 10px; text-align: center;">Delete</a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p>No talents have been added yet.</p>
                        <?php endif; ?>
                    </div>
                </section>
            </main>

        </div>

        <?php require 'footer.php'; ?>
    </div>
</body>
</html>
