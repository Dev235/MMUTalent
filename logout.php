<?php
require 'connection.php';
$page_title = "Login";
require 'header.php';
session_start();

// If user is already logged in, redirect them to their dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
?>

<body>
    <div class="login-container">
        <h2>Login to Your Account</h2>

        <?php
        $error_message = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
            $email = trim($_POST['email']);
            $password = $_POST['password'];

            $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['name'] = $user['name'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin/adminDashboard.php"); // Assuming you have an admin dashboard
                } else {
                    header("Location: userDashboard.php");
                }
                exit;
            } else {
                $error_message = "Invalid email or password. Please try again.";
            }
        }
        ?>

        <?php if (!empty($error_message)): ?>
            <p style="color: red; background-color: #f8d7da; padding: 10px; border-radius: 5px; text-align: center;"><?= $error_message ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" name="login" value="Login" class="form-button" style="margin-top: 15px;">
        </form>
        
        <div style="margin-top: 20px; text-align: center;">
            <p>Don't have an account? <a href="registration.php" style="color: var(--color-primary);">Register here</a></p>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
