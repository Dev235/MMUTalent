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
    "SELECT s.service_title, s.service_description, s.service_image, s.service_price, u.name as user_name, u.profile_picture, u.user_id 
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
    echo "<div id='main-content'><p>Sorry, this talent could not be found.</p></div>";
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

// NEW: Check for error messages from redirects (e.g., admin trying to add to cart)
$error_message = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'admin_cannot_add_to_cart') {
        $error_message = "Administrators cannot add items to the shopping cart.";
    } elseif ($_GET['error'] == 'login_required') {
        $error_message = "You must be logged in to add items to your cart.";
    }
}

?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1><?php echo htmlspecialchars($talent['service_title']); ?></h1>
        </div>

        <div class="talent-detail-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            
            <a href="#" onclick="history.go(-1); return false;" class="form-button" style="width: auto; padding: 10px 20px; background-color: #6c757d; display: inline-block; margin-bottom: 20px;">&larr; Back</a>
            
            <img src="<?php echo $talent_image_src; ?>" alt="<?php echo htmlspecialchars($talent['service_title']); ?>" style="width: 100%; height: auto; max-height: 400px; object-fit: cover; border-radius: 8px; margin-bottom: 20px;">

            <section id="talent-description">
                <h2 style="color: var(--color-title); border-bottom: 2px solid var(--color-surface); padding-bottom: 10px;">About this Talent</h2>
                <p style="line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($talent['service_description'])); ?>
                </p>
            </section>
            
            <section id="add-to-cart" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <?php if (!empty($error_message)): ?>
                    <div style="text-align: center; background-color: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
                        <p style="margin: 0; font-size: 1.1em;"><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <?php if ($_SESSION['user_id'] == $talent['user_id']): ?>
                        <div style="text-align: center; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                            <p style="margin: 0; font-size: 1.1em; color: var(--color-title);">You cannot purchase your own talent.</p>
                        </div>
                    <?php elseif ($_SESSION['role'] === 'admin'): ?>
                         <!-- Message displayed by the error_message check above -->
                        <div style="text-align: center; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                            <p style="margin: 0; font-size: 1.1em; color: var(--color-title);">Administrators cannot use the shopping cart functionality.</p>
                        </div>
                    <?php else: ?>
                        <form action="cart_logic.php" method="POST">
                            <input type="hidden" name="action" value="add">
                            <input type="hidden" name="talent_id" value="<?php echo $talent_id; ?>">
                            <button type="submit" class="form-button" style="width: 100%; background-color: var(--color-primary); font-size: 1.2em;">Add to Cart (RM <?php echo number_format($talent['service_price'], 2); ?>)</button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <div style="text-align: center; background-color: #f9f9f9; padding: 20px; border-radius: 8px;">
                        <p style="margin: 0; font-size: 1.1em;">You must be logged in to add items to your cart.</p>
                        <a href="login.php" class="form-button" style="width: auto; display: inline-block; margin-top: 15px; padding: 10px 30px;">Login to Continue</a>
                    </div>
                <?php endif; ?>
            </section>

            <section id="talent-owner" style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee;">
                <h3 style="color: var(--color-title);">Offered By</h3>
                <div class="owner-info" style="display: flex; align-items: center; gap: 15px;">
                    <img src="<?php echo $owner_avatar_src; ?>" alt="<?php echo htmlspecialchars($talent['user_name']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                    <div>
                        <h4 style="margin: 0;"><?php echo htmlspecialchars($talent['user_name']); ?></h4>
                        <a href="userDashboard.php?id=<?php echo $talent['user_id']; ?>" style="font-size: 0.9em; color: var(--color-primary);">View Profile</a>
                    </div>
                </div>
            </section>

        </div>

    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
