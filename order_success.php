<?php
session_start();

// This page should only be accessed after a "successful" checkout.
// We clear the cart here.
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

$page_title = "Order Successful";
require 'header.php';
?>
<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1>Thank You For Your Order!</h1>
        </div>

        <div class="success-container" style="max-width: 600px; margin: 50px auto; text-align: center; background-color: #fff; padding: 40px; border-radius: 10px;">
            <p style="font-size: 1.2em;">Your purchase has been successfully simulated.</p>
            <p>You will be contacted by the talent provider shortly.</p>
            <a href="index.php" class="form-button" style="width: auto; display: inline-block; margin-top: 20px;">Return to Homepage</a>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
