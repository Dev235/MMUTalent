<?php
// Start session if it hasn't been started already. This is important for accessing session variables.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the number of items in the cart to display a notification badge.
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<span id="openBtn"><a href="#" onclick="openNavBar()">☰</a></span>
<div class="sideNavBar">
    <div id="closeBtn"><a href="#" onclick="closeNavBar()">&times;</a></div>
    <h3>Side Navigation Bar</h3>
    
    <!-- Standard navigation links -->
    <a href="index.php">Home</a>
    <a href="forum.php">Forum</a>
    <a href="faq.php">FAQ</a>
    <a href="announcement.php">Announcements</a>

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- These links only show if the user IS logged in -->
        <a href="userDashboard.php">My Profile</a>
        <a href="shoppingCart.php">
            Shopping Cart 
            <?php if ($cart_item_count > 0): ?>
                <span style="background-color: #dc3545; color: white; border-radius: 50%; padding: 2px 8px; font-size: 0.8em;"><?php echo $cart_item_count; ?></span>
            <?php endif; ?>
        </a>
    <?php endif; ?>
    
    <hr style="border-color: rgba(0,0,0,0.1);">

    <?php if (isset($_SESSION['user_id'])): ?>
        <!-- If a user session exists, show the "Logout" link -->
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <!-- If no user session exists, show the "Login" link -->
        <a href="login.php">Login</a>
    <?php endif; ?>
</div>
