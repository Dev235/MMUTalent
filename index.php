<?php
require 'connection.php';
$page_title = "Index Page";
require 'header.php';
session_start();
?>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1 id="title">MMU Talent</h1>
            <h2>Welcome to MMU Talents Management System</h2>
        </div>

        <!-- Page specific content here -->


        <!-- Login Form Section -->
        <div class="login-container">
            <h2>Login</h2>

            <?php
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
                $email = htmlspecialchars($_POST['email']);
                $password = $_POST['password'];

                $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
                mysqli_stmt_bind_param($stmt, "s", $email);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $user = mysqli_fetch_assoc($result);

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
                    echo "<p style='color: red;'>Invalid email or password.</p>";
                }
            }
            ?>

            <form method="POST">
                <label>Email:</label><br>
                <input type="email" name="email" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="password" required><br><br>

                <!-- Login Button -->
                <input type="submit" name="login" value="Login" class="form-button">

                <!-- Register Button -->
                <button type="button" onclick="window.location.href='registration.php'" class="form-button">
                    Register
                </button>
            </form>

        </div>

        <?php require 'footer.php'; ?>
    </div>
</body>
</html>
