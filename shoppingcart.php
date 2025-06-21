<?php
// This one is important, must include the logic file.
// All the 'add to cart', 'remove from cart' functions are inside this cart_logic.php file.
require 'cart_logic.php'; 

// Standard la, set the page title for the browser tab.
$page_title = "My Shopping Cart";
// Then include the header file, which has all the HTML head stuff.
require 'header.php';
?>

<body>
    <?php 
    // This is for the side navigation bar, just require it so no need to copy paste code everywhere.
    require 'navbar.php'; 
    ?>

    <div id="main-content">
        <div class="title-container">
            <h1>Shopping Cart</h1>
        </div>

        <div class="cart-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <?php 
            // This is a simple check. If the 'cart' in our session is empty...
            if (empty($_SESSION['cart'])): 
            ?>
                <!-- ...then just show this message. Simple right? -->
                <p>Your shopping cart is empty.</p>
                <a href="index.php" class="form-button" style="width: auto; display: inline-block;">Browse Talents</a>
            <?php 
            // If the cart is NOT empty, then the `else` part will run.
            else: 
            ?>
                <!-- We use a table to show the items, looks neat. -->
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead style="border-bottom: 2px solid #eee;">
                        <tr>
                            <th style="padding: 10px;">Talent/Service</th>
                            <th style="padding: 10px;">Price</th>
                            <th style="padding: 10px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Initialize the total price to zero first.
                        $total_price = 0;
                        // This foreach loop will go through every item in the cart session.
                        // For each item, it will create a new table row <tr>.
                        foreach ($_SESSION['cart'] as $id => $item):
                            // Add the item's price to the total price.
                            $total_price += $item['price'];
                        ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <!-- Show the item title. Use htmlspecialchars for security, so people cannot inject funny scripts. -->
                                <td style="padding: 15px 10px;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <!-- Show the price. number_format is to make it look nice, like 50.00 -->
                                <td style="padding: 15px 10px;">RM <?php echo number_format($item['price'], 2); ?></td>
                                <td style="padding: 15px 10px; text-align: right;">
                                    <!-- This is the link to remove the item. It calls cart_logic.php with an action 'remove'. -->
                                    <a href="cart_logic.php?action=remove&talent_id=<?php echo $id; ?>" style="color: red; text-decoration: none;">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; // End of the loop. ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <!-- Just to show the final total price. -->
                            <td style="padding: 20px 10px; font-weight: bold; font-size: 1.2em;">Total</td>
                            <td style="padding: 20px 10px; font-weight: bold; font-size: 1.2em;" colspan="2">RM <?php echo number_format($total_price, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <!-- These are the buttons at the bottom -->
                <div class="cart-actions" style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <!-- Clear Cart button, also calls the logic file -->
                    <a href="cart_logic.php?action=clear" class="form-button" style="background-color: #dc3545; width: auto;">Clear Cart</a>
                    <!-- This one will go to the checkout page to "pay" -->
                    <a href="checkout.php" class="form-button" style="background-color: #28a745; width: auto;">Proceed to Checkout</a>
                </div>
            <?php endif; // End of the if-else check. ?>
        </div>
    </div>

    <?php 
    // Finally, include the footer.
    require 'footer.php'; 
    ?>
</body>
</html>
