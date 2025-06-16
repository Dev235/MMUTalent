<?php
// cart_logic.php handles session start and actions.
require 'cart_logic.php'; 

$page_title = "My Shopping Cart";
require 'header.php';
?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1>Shopping Cart</h1>
        </div>

        <div class="cart-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <?php if (empty($_SESSION['cart'])): ?>
                <p>Your shopping cart is empty.</p>
                <a href="index.php" class="form-button" style="width: auto; display: inline-block;">Browse Talents</a>
            <?php else: ?>
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
                        $total_price = 0;
                        foreach ($_SESSION['cart'] as $id => $item):
                            $total_price += $item['price'];
                        ?>
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 10px;"><?php echo htmlspecialchars($item['title']); ?></td>
                                <td style="padding: 15px 10px;">RM <?php echo number_format($item['price'], 2); ?></td>
                                <td style="padding: 15px 10px; text-align: right;">
                                    <a href="cart_logic.php?action=remove&talent_id=<?php echo $id; ?>" style="color: red; text-decoration: none;">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td style="padding: 20px 10px; font-weight: bold; font-size: 1.2em;">Total</td>
                            <td style="padding: 20px 10px; font-weight: bold; font-size: 1.2em;" colspan="2">RM <?php echo number_format($total_price, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>

                <div class="cart-actions" style="display: flex; justify-content: space-between; margin-top: 30px;">
                    <a href="cart_logic.php?action=clear" class="form-button" style="background-color: #dc3545; width: auto;">Clear Cart</a>
                    <a href="checkout.php" class="form-button" style="background-color: #28a745; width: auto;">Proceed to Checkout</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
