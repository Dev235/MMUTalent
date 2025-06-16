<?php
session_start();
require 'connection.php';

// Redirect user if they are not logged in or if cart is empty
if (!isset($_SESSION['user_id']) || empty($_SESSION['cart'])) {
    header("Location: shoppingCart.php");
    exit();
}

$page_title = "Checkout";
require 'header.php';

$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['price'];
}
?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1>Checkout</h1>
        </div>

        <div class="checkout-container" style="max-width: 900px; margin: 30px auto; display: flex; gap: 30px; flex-wrap: wrap;">

            <!-- Order Summary -->
            <div class="order-summary" style="flex: 1; min-width: 300px; background-color: #f9f9f9; padding: 20px; border-radius: 10px;">
                <h3 style="border-bottom: 2px solid #eee; padding-bottom: 10px; margin-top: 0;">Your Order</h3>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <span><?php echo htmlspecialchars($item['title']); ?></span>
                        <span>RM <?php echo number_format($item['price'], 2); ?></span>
                    </div>
                <?php endforeach; ?>
                <hr>
                <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 1.2em;">
                    <span>Total</span>
                    <span>RM <?php echo number_format($total_price, 2); ?></span>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="payment-form" style="flex: 2; min-width: 400px; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
                <h3>Payment Details (Simulation)</h3>
                <p>This is a simulated payment page. No real transaction will occur.</p>
                <form action="order_success.php" method="POST">
                    <label for="card_name">Name on Card:</label>
                    <input type="text" id="card_name" name="card_name" required style="width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box;">

                    <label for="card_number">Card Number:</label>
                    <input type="text" id="card_number" name="card_number" placeholder="1111-2222-3333-4444" required style="width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box;">

                    <div style="display: flex; gap: 15px;">
                        <div style="flex: 1;">
                            <label for="expiry_date">Expiry Date:</label>
                            <input type="text" id="expiry_date" name="expiry_date" placeholder="MM/YY" required style="width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box;">
                        </div>
                        <div style="flex: 1;">
                            <label for="cvv">CVV:</label>
                            <input type="text" id="cvv" name="cvv" placeholder="123" required style="width: 100%; padding: 8px; margin-bottom: 15px; box-sizing: border-box;">
                        </div>
                    </div>
                    
                    <button type="submit" class="form-button" style="width: 100%; background-color: #28a745;">Complete Purchase</button>
                </form>
            </div>

        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
