<?php
// As usual, need to start session and connect to the database.
session_start();
require 'connection.php';

// Set the page title and include the header and navbar.
$page_title = "Order Contact Details";
require 'header.php';
require 'navbar.php';

// Prepare an empty array to store the details of the services we just "bought".
$ordered_services = [];

// This is the main logic. We check if the `last_order_details` session variable exists.
// The previous page (order_success.php) should have created this. If it doesn't exist, means the user came to this page directly, which is wrong.
if (isset($_SESSION['last_order_details']) && !empty($_SESSION['last_order_details'])) {
    // Get all the service IDs from the session variable.
    // `array_keys` is a function that just takes the keys (which are our service IDs) from an array.
    $ordered_services_ids = array_keys($_SESSION['last_order_details']);

    // --- This part is a bit tricky, it's for the SQL query ---
    // We want to fetch details for multiple services at once using `IN (...)`.
    // For a prepared statement, we can't just put the IDs in directly. We need placeholders (the '?').
    // So this line creates a string of question marks, like "?,?,?".
    $placeholders = implode(',', array_fill(0, count($ordered_services_ids), '?'));
    
    // This is the SQL query to get the seller's details for each service we ordered.
    // We need to JOIN `services` and `users` table to get all the info in one go.
    $stmt = $conn->prepare(
        "SELECT s.service_id, s.service_title, s.service_description, s.service_image, 
                u.name as seller_name, u.email as seller_email, u.phone_number as seller_phone
         FROM services s
         JOIN users u ON s.user_id = u.user_id
         WHERE s.service_id IN ($placeholders)"
    );

    if ($stmt) {
        // This line creates the type string for `bind_param`, like "iii" if we have 3 service IDs.
        $types = str_repeat('i', count($ordered_services_ids));
        // The `...` (splat operator) is a shortcut to pass all elements of the array as arguments.
        // So this binds all our service IDs to the question marks in the query.
        $stmt->bind_param($types, ...$ordered_services_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        // Loop through all the results and store them in our `$ordered_services` array.
        while ($row = $result->fetch_assoc()) {
            $ordered_services[$row['service_id']] = $row;
        }
        $stmt->close();
    }

    // This is important! After we have displayed the details, we must clear the session variable.
    // If not, the user can refresh the page and see the same details again, which might be confusing.
    unset($_SESSION['last_order_details']);
}

?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Your Order Contact Details</h1>
        <p style="color:white;">Please use these details to coordinate with the talent providers.</p>
    </div>

    <div class="contact-details-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <?php if (!empty($ordered_services)): ?>
            <!-- Loop through each service we fetched from the database -->
            <?php foreach ($ordered_services as $service_id => $service_details): ?>
                <div class="service-contact-card" style="border: 1px solid #eee; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: var(--color-primary);"><?= htmlspecialchars($service_details['service_title']) ?></h3>
                    <p style="margin-bottom: 5px;"><strong>Seller:</strong> <?= htmlspecialchars($service_details['seller_name']) ?></p>
                    <p style="margin-bottom: 5px;"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($service_details['seller_email']) ?>"><?= htmlspecialchars($service_details['seller_email']) ?></a></p>
                    <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($service_details['seller_phone']) ?>"><?= htmlspecialchars($service_details['seller_phone']) ?></a></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- This shows up if the user somehow gets to this page without ordering anything. -->
            <p style="text-align: center;">No order details found. Please proceed to checkout first.</p>
            <p style="text-align: center; margin-top: 20px;"><a href="index.php" class="form-button" style="width: auto; display: inline-block;">Return to Homepage</a></p>
        <?php endif; ?>

        <?php if (!empty($ordered_services)): // Only show this button if there were details to show ?>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="form-button" style="width: auto; display: inline-block;">Return to Homepage</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
