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
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters.";
        } elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $role = 'student';

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $hashed_password, $role);

            if ($stmt->execute()) {
                $registration_successful = true;
            } else {
                $error_message = "Registration failed. Please try again.";
            }

            $stmt->close();
        }
    }
    ?>

    <?php if ($registration_successful): ?>
        <div class="registration-success">
            <p>Registration successful!</p>
            <a href="login.php" class="form-button">Back to Login</a>
        </div>
    <?php else: ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red; margin-bottom: 15px;"><?= $error_message ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Register" class="form-button">
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
</body>
</html> <!-- Also required to close the document -->
