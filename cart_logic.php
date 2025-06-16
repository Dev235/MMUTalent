<?php
// Start session if it hasn't been started already
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require 'connection.php';

// Initialize the cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// --- CART ACTIONS ---

// 1. Add item to cart
if (isset($_POST['action']) && $_POST['action'] == 'add' && isset($_POST['talent_id'])) {
    
    // ** NEW: CHECK IF USER IS LOGGED IN **
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if not logged in
        header("Location: login.php?error=login_required");
        exit();
    }

    $talent_id = intval($_POST['talent_id']);

    // For this project, we'll assume a quantity of 1 for each talent/service.
    // Check if the item is already in the cart
    if (!isset($_SESSION['cart'][$talent_id])) {
        // Fetch talent details to store in the cart
        $stmt = $conn->prepare("SELECT service_title as talent_title, 100.00 as price FROM services WHERE service_id = ?"); // Assuming a fixed price for now
        $stmt->bind_param("i", $talent_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($item = $result->fetch_assoc()) {
            $_SESSION['cart'][$talent_id] = [
                'title' => $item['talent_title'],
                'price' => $item['price'],
                'quantity' => 1 // Quantity is always 1
            ];
        }
        $stmt->close();
    }
    // Redirect back to the page the user was on
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// 2. Remove item from cart
if (isset($_GET['action']) && $_GET['action'] == 'remove' && isset($_GET['talent_id'])) {
    $talent_id = intval($_GET['talent_id']);
    if (isset($_SESSION['cart'][$talent_id])) {
        unset($_SESSION['cart'][$talent_id]);
    }
    // Redirect to the shopping cart page
    header("Location: shoppingCart.php");
    exit();
}

// 3. Clear the entire cart
if (isset($_GET['action']) && $_GET['action'] == 'clear') {
    $_SESSION['cart'] = [];
    header("Location: shoppingCart.php");
    exit();
}
