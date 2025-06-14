<?php
// Start the session to access session variables.
session_start();

// Include the database connection file.
require 'connection.php';

// Check if a talent ID is provided in the URL. If not, redirect home.
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit();
}

// Sanitize the ID and fetch the talent details along with the owner's info using a JOIN.
$talent_id = intval($_GET['id']);

$stmt = $conn->prepare(
    "SELECT s.service_title, s.service_description, s.service_image, u.name as user_name, u.profile_picture, u.user_id 
     FROM services s 
     JOIN users u ON s.user_id = u.user_id 
     WHERE s.service_id = ?"
);
$stmt->bind_param("i", $talent_id);
$stmt->execute();
$result = $stmt->get_result();
$talent = $result->fetch_assoc();
$stmt->close();

// If no talent is found with that ID, display an error message.
if (!$talent) {
    $page_title = "Talent Not Found";
    require 'header.php';
    echo "<div id='main-content' style='padding: 40px;'><p>Sorry, this talent could not be found.</p></div>";
    require 'footer.php';
    exit();
}

// Set the page title and include the header.
$page_title = htmlspecialchars($talent['service_title']);
require 'header.php';

// --- Determine image sources using the same logic as the dashboard ---
define('DEFAULT_AVATAR_URL', 'https://placehold.co/60x60/EFEFEF/AAAAAA&text=No+Image');

$talent_image_src = 'images/uploads/talent_images/' . htmlspecialchars($talent['service_image'] ?? 'service_placeholder.png');
if (empty($talent['service_image']) || !file_exists($talent_image_src)) {
    $talent_image_src = 'https://placehold.co/800x400/EFEFEF/AAAAAA&text=No+Image';
}

$owner_avatar_src = DEFAULT_AVATAR_URL;
if (!empty($talent['profile_picture']) && file_exists('images/uploads/profile_pictures/' . $talent['profile_picture'])) {
    $owner_avatar_src = 'images/uploads/profile_pictures/' . htmlspecialchars($talent['profile_picture']);
}

?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1><?php echo htmlspecialchars($talent['service_title']); ?></h1>
        </div>

        <!-- Talent Detail Container -->
        <div class="talent-detail-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <!-- ** MOVED JAVASCRIPT BACK BUTTON ** -->
            <a href="#" onclick="history.go(-1); return false;" class="form-button" style="width: auto; padding: 10px 20px; background-color: #6c757d; display: inline-block; margin-bottom: 20px;">&larr; Back</a>
            
            <!-- Talent Image -->
            <img src="<?php echo $talent_image_src; ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>" style="width: 100%; height: auto; max-height: 400px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">

            <!-- Talent Description -->
            <section id="talent-description">
                <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">About this Talent</h2>
                <p style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($talent['service_description'])); ?>
                </p>
            </section>

            <!-- Talent Owner Info -->
            <section id="talent-owner" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h3 style="color: var(--color-title);">Offered By</h3>
                <div class="owner-info" style="display: flex; align-items: center; gap: 15px;">
                    <img src="<?php echo $owner_avatar_src; ?>" alt="<?php echo htmlspecialchars($talent['user_name']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($talent['user_name']); ?></h4>
                        <!-- In the future, this could link to the user's public profile -->
                        <a href="userDashboard.php?id=<?php echo $talent['user_id']; ?>" style="font-size: 0.9em; color: var(--color-primary);">View Profile</a>
                    </div>
                </div>
            </section>

        </div>

    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
