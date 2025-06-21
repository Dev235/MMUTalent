<?php
require 'connection.php';
$page_title = "Register";
require 'header.php';
?>

<body> 
<div class="register-container">
    <h2>Create an Account</h2>

    <?php
    $registration_successful = false;
    $error_message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']); // NEW: Get phone number
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            // NEW: Updated INSERT query to include phone_number
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $phone_number, $hashed_password, $role); // 's' for phone_number
                if ($stmt->execute()) {
                    $registration_successful = true;
                } else {
                    $error_message = "Registration failed. Please try again. " . $conn->error;
                }
                $stmt->close();
            } else {
                $error_message = "Database error: Could not prepare statement.";
                error_log("Failed to prepare statement for registration: " . $conn->error);
            }
        }
    }
    ?>

    <?php if ($registration_successful): ?>
        <div class="registration-success">
            <p>Registration successful!</p>
            <a href="login.php" class="success-button" style="width:auto; display:inline-block;">Back to Login</a>
        </div>
    <?php else: ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red; margin-bottom: 15px;"><?= $error_message ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone_number" placeholder="Phone Number (e.g., 0123456789)" required>
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Register" class="form-button">
            <!-- NEW: Back to Login button -->
            <button type="button" onclick="window.location.href='login.php'" class="form-button" style="margin-top: 10px; background-color: #6c757d;">
                Back to Login
            </button>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
</body>
</html>
