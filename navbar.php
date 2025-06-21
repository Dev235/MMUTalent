<?php
// We need to start the session on every page that uses session variables.
// If not, PHP won't know who is logged in.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// This part is for the small red circle on the shopping cart icon.
// We check if the 'cart' session exists and count how many items are inside.
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!-- This is the hamburger icon to open the navbar on mobile -->
<span id="openBtn"><a href="#" onclick="openNavBar()">☰</a></span>

<!-- The main sidebar container -->
<div class="sideNavBar">
    <!-- The 'x' button to close the navbar -->
    <div id="closeBtn"><a href="#" onclick="closeNavBar()">&times;</a></div>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- NEW: This is the welcome message. Only shows up if the user is logged in. -->
        <div style="padding: 15px 20px; text-align: center; color: var(--color-text); background-color: rgba(0,0,0,0.05);">
            Hello, <strong><?php echo htmlspecialchars($_SESSION['name']); ?></strong>
        </div>
    <?php endif; ?>

    <h3>Side Navigation</h3>
    
    <!-- These are the main links that everyone can see -->
    <a href="index.php">Home</a>
    <a href="forum.php">Forum</a>
    <a href="faq.php">FAQ</a>
    <a href="announcement.php">Announcements</a>
    <a href="talentCatalogue.php">Catalogue</a>
    <a href="aboutUs.php">Contact Us</a>
    
    <?php 
    // This is the main PHP block to check if someone is logged in or not.
    // Super useful for showing different links to guests vs members.
    if (isset($_SESSION['user_id'])): 
    ?>
        <?php 
        // If the user is logged in, we need to check if they are 'admin' or just a normal 'student'.
        if ($_SESSION['role'] === 'admin'): 
        ?>
            <!-- This is the ADMIN section. Only admins can see these links. -->
            <hr style="border-color: rgba(0,0,0,0.1);">
            <a href="adminDashboard.php">Admin Dashboard</a>
            <a href="manageUsers.php">Manage Users</a>
            <a href="manageForum.php">Manage Forum</a>
            <a href="manageAnnouncements.php">Manage Announcements</a>
            <a href="manageFAQ.php">Manage FAQ</a>
            <a href="manageCatalogue.php">Manage Catalogue</a>
        <?php 
        // If the role is NOT 'admin', then they must be a student. So show the student links.
        else: 
        ?>
            <!-- This is the STUDENT section -->
            <hr style="border-color: rgba(0,0,0,0.1);">
            <a href="userDashboard.php">My Profile</a>
            <a href="shoppingCart.php">
                Shopping Cart 
                <?php if ($cart_item_count > 0): ?>
                    <!-- The red notification badge. It only appears if cart got items. -->
                    <span style="background-color: #dc3545; color: white; border-radius: 50%; padding: 2px 8px; font-size: 0.8em;"><?php echo $cart_item_count; ?></span>
                <?php endif; ?>
            </a>
        <?php endif; // End of the admin/student role check ?>
        
        <!-- Every logged-in user, whether admin or student, gets a logout button. -->
        <hr style="border-color: rgba(0,0,0,0.1);">
        <a href="logout.php">Logout</a>
    <?php 
    // If the `user_id` session is not set, it means the user is a guest.
    else: 
    ?>
        <!-- So for guests, we only show the Login button. -->
        <hr style="border-color: rgba(0,0,0,0.1);">
        <a href="login.php">Login</a>
    <?php endif; // End of the logged-in check ?>
</div>
