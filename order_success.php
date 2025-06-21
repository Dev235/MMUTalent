<?php
// Gotta start the session first, because we need to access the cart and user ID from it.
session_start();
// And of course, need the database connection.
require 'connection.php';

// This page is a bit special. It's not supposed to show any HTML.
// Its only job is to do some backend work after the user "pays" at checkout.php.
// Think of it like a silent worker page.

// Create an empty array to hold the stuff the user just bought.
$current_order_details = []; 

// --- Record the transaction into our database BEFORE we clear the cart ---
// This check is important. We only run this code if the user is logged in AND their cart is not empty.
if (isset($_SESSION['cart']) && !empty($_SESSION['cart']) && isset($_SESSION['user_id'])) {
    // Get the ID of the person who is buying.
    $buyer_user_id = $_SESSION['user_id'];

    // The cart can have many items, so we need a loop to go through each one.
    // `foreach` is perfect for this. It goes through each item in the `$_SESSION['cart']` array.
    foreach ($_SESSION['cart'] as $service_id => $item) {
        // Get the price from the item details stored in the cart.
        $service_price = $item['price'];

        // This is the SQL command to save the sale into our `transactions` table.
        // The `status` is 'pending' by default because the seller needs to confirm it later.
        $stmt = $conn->prepare("INSERT INTO transactions (buyer_user_id, service_id, price_at_purchase, status) VALUES (?, ?, ?, 'pending')");
        
        if ($stmt) {
            // "iid" means the data types are Integer, Integer, and Double (a number with decimals).
            $stmt->bind_param("iid", $buyer_user_id, $service_id, $service_price);
            // Run the command.
            $stmt->execute();
            // Close it, good practice.
            $stmt->close();
            
            // We store the service ID of what was just bought.
            // This is so the next page can show the seller's contact info.
            $current_order_details[$service_id] = true; 

        } else {
            // If something goes wrong with the database, we can check the error log on the server.
            error_log("Failed to prepare statement for transaction insertion: " . $conn->error);
        }
    }
}

// After the loop is done, we save the list of ordered items into a new session variable.
// This is how we pass data from this page to the next one.
if (!empty($current_order_details)) {
    $_SESSION['last_order_details'] = $current_order_details;
}

// Now that we've saved the transaction, it's safe to empty the cart.
// If we clear the cart first, then we lose all the information! So order is important.
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']); // `unset` just removes the variable. Poof, cart is gone.
}

// --- NEW: Redirect to the order contact details page ---
// After all the work is done, we immediately send the user to the next page.
// They won't even see this page, it all happens in a split second.
header("Location: order_contact_details.php");
// `exit()` is important to make sure no other code runs after the redirect.
exit();

// See? No HTML at all on this page. Just pure PHP logic.
?>
