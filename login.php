<?php
require 'connection.php';
$page_title = "Login";
require 'header.php';
session_start();

// If user is already logged in, redirect them to their dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: userDashboard.php");
    exit;
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="login-container">
            <h2>Login</h2>

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
                    
                    if ($user['role'] === 'admin') {
                        header("Location: admin/adminDashboard.php");
                    } else {
                        header("Location: userDashboard.php");
                    }
                    exit;
                } else {
                    $error_message = "Invalid email or password.";
                }
            }
            ?>

            <?php if (!empty($error_message)): ?>
                <p style='color: red;'><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <label>Email:</label><br>
                <input type="email" name="email" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="password" required><br><br>

                <input type="submit" name="login" value="Login" class="form-button">

                <button type="button" onclick="window.location.href='registration.php'" class="form-button" style="margin-top: 10px;">
                    Register
                </button>
            </form>
        </div>
        <?php require 'footer.php'; ?>
    </div>
</body>
</html>
