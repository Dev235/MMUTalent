<?php
session_start();
require 'connection.php';

$page_title = "Order Contact Details";
require 'header.php';
require 'navbar.php';

$ordered_services = [];

// Check if there are services stored in a temporary session variable after checkout
if (isset($_SESSION['last_order_details']) && !empty($_SESSION['last_order_details'])) {
    $ordered_services_ids = array_keys($_SESSION['last_order_details']);

    // Fetch seller details for each ordered service
    // Use IN clause for efficiency if multiple services were bought
    $placeholders = implode(',', array_fill(0, count($ordered_services_ids), '?'));
    $stmt = $conn->prepare(
        "SELECT s.service_id, s.service_title, s.service_description, s.service_image, 
                u.name as seller_name, u.email as seller_email, u.phone_number as seller_phone
         FROM services s
         JOIN users u ON s.user_id = u.user_id
         WHERE s.service_id IN ($placeholders)"
    );

    if ($stmt) {
        $types = str_repeat('i', count($ordered_services_ids)); // 'i' for integer IDs
        $stmt->bind_param($types, ...$ordered_services_ids);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $ordered_services[$row['service_id']] = $row;
        }
        $stmt->close();
    } else {
        error_log("Failed to prepare statement for fetching order contact details: " . $conn->error);
    }

    // Clear the temporary session variable after displaying
    unset($_SESSION['last_order_details']);
} else {
    // If no order details found, redirect to homepage or show a message
    // header("Location: index.php");
    // exit();
    // For demonstration, we'll just show an empty list below.
}

?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Your Order Contact Details</h1>
        <p style="color:white;">Please use these details to coordinate with the talent providers.</p>
    </div>

    <div class="contact-details-container" style="max-width: 900px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <?php if (!empty($ordered_services)): ?>
            <?php foreach ($ordered_services as $service_id => $service_details): ?>
                <div class="service-contact-card" style="border: 1px solid #eee; padding: 20px; margin-bottom: 20px; border-radius: 8px;">
                    <h3 style="margin-top: 0; color: var(--color-primary);"><?= htmlspecialchars($service_details['service_title']) ?></h3>
                    <p style="margin-bottom: 5px;"><strong>Seller:</strong> <?= htmlspecialchars($service_details['seller_name']) ?></p>
                    <p style="margin-bottom: 5px;"><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($service_details['seller_email']) ?>"><?= htmlspecialchars($service_details['seller_email']) ?></a></p>
                    <p><strong>Phone:</strong> <a href="tel:<?= htmlspecialchars($service_details['seller_phone']) ?>"><?= htmlspecialchars($service_details['seller_phone']) ?></a></p>
                    <?php if (!empty($service_details['service_description'])): ?>
                        <p style="font-size: 0.9em; color: #555; margin-top: 15px;"><em>Description:</em> <?= nl2br(htmlspecialchars($service_details['service_description'])) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="text-align: center;">No order details found. Please proceed to checkout first.</p>
            <p style="text-align: center; margin-top: 20px;"><a href="index.php" class="form-button" style="width: auto; display: inline-block;">Return to Homepage</a></p>
        <?php endif; ?>

        <?php if (!empty($ordered_services)): // Only show return home if there were details to show ?>
            <div style="text-align: center; margin-top: 30px;">
                <a href="index.php" class="form-button" style="width: auto; display: inline-block;">Return to Homepage</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
