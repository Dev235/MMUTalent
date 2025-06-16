<?php
// Start session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Get the number of items in the cart from the session
$cart_item_count = isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<span id="openBtn"><a href="#" onclick="openNavBar()">☰</a></span>
<div class="sideNavBar">
    <div id="closeBtn"><a href="#" onclick="closeNavBar()">&times;</a></div>
    <h3>Side Navigation Bar</h3>
    
    <!-- Correct Application Links -->
    <a href="index.php">Home</a>
    <a href="userDashboard.php">My Profile</a>
    
    <!-- Cart Link with Item Count -->
    <a href="shoppingCart.php">
        Shopping Cart 
        <?php if ($cart_item_count > 0): ?>
            <span style="background-color: #dc3545; color: white; border-radius: 50%; padding: 2px 8px; font-size: 0.8em;"><?php echo $cart_item_count; ?></span>
        <?php endif; ?>
    </a>

    <a href="forum.php">Forum</a>
    <a href="faq.php">FAQ</a>
    <a href="announcement.php">Announcements</a>
    <a href="logout.php">Logout</a>
</div>
