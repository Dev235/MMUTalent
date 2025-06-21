<?php
session_start();
require 'connection.php';

// This page should only be accessed after a "successful" checkout.

$current_order_details = []; // To store details for the contact page

// --- Record transactions before clearing the cart ---
if (isset($_SESSION['cart']) && !empty($_SESSION['cart']) && isset($_SESSION['user_id'])) {
    $buyer_user_id = $_SESSION['user_id'];

    foreach ($_SESSION['cart'] as $service_id => $item) {
        $service_price = $item['price']; // Use the price stored in the cart

        // Prepare and execute the SQL statement to insert into the transactions table
        // The new 'status' column is set to 'pending' by default.
        $stmt = $conn->prepare("INSERT INTO transactions (buyer_user_id, service_id, price_at_purchase, status) VALUES (?, ?, ?, 'pending')");
        if ($stmt) {
            $stmt->bind_param("iid", $buyer_user_id, $service_id, $service_price);
            $stmt->execute();
            $stmt->close();
            
            // Store service_id for the next page to fetch contact details
            $current_order_details[$service_id] = true; 

        } else {
            // Handle error preparing statement, e.g., log it or show a message
            error_log("Failed to prepare statement for transaction insertion: " . $conn->error);
        }
    }
}

// Store the service IDs of the just-ordered items in a session for the contact page
if (!empty($current_order_details)) {
    $_SESSION['last_order_details'] = $current_order_details;
}

// We clear the cart here.
if (isset($_SESSION['cart'])) {
    unset($_SESSION['cart']);
}

// --- NEW: Redirect to the order contact details page ---
header("Location: order_contact_details.php");
exit();

