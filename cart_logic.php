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
    
    // ** NEW: CHECK IF USER IS LOGGED IN AND IS NOT AN ADMIN **
    if (!isset($_SESSION['user_id'])) {
        // Redirect to login page if not logged in
        header("Location: login.php?error=login_required");
        exit();
    } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        // Redirect if the user is an admin
        header("Location: viewTalent.php?id=" . intval($_POST['talent_id']) . "&error=admin_cannot_add_to_cart");
        exit();
    }

    $talent_id = intval($_POST['talent_id']);

    // For this project, we'll assume a quantity of 1 for each talent/service.
    // Check if the item is already in the cart
    if (!isset($_SESSION['cart'][$talent_id])) {
        // Fetch talent details to store in the cart, including service_price
        // CHANGED: Fetching service_price from the database instead of hardcoding '100.00 as price'
        $stmt = $conn->prepare("SELECT service_title, service_price FROM services WHERE service_id = ?"); 
        if ($stmt) {
            $stmt->bind_param("i", $talent_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($item = $result->fetch_assoc()) {
                $_SESSION['cart'][$talent_id] = [
                    'title' => $item['service_title'], // Use service_title
                    'price' => floatval($item['service_price']), // Use service_price and ensure it's a float
                    'quantity' => 1 // Quantity is always 1
                ];
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement to fetch talent for cart: " . $conn->error);
        }
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
