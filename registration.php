<?php
require 'connection.php';
$page_title = "Register";
require 'header.php';
require 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars($_POST['name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = 'student';

    $check = mysqli_prepare($conn, "SELECT email FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check, "s", $email);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) > 0) {
        echo "<p style='color: red;'>Email is already registered.</p>";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $password, $role);
        if (mysqli_stmt_execute($stmt)) {
            // Output the form again with a success message at the bottom
            $registered = true;
        } else {
            echo "<p style='color: red;'>Registration failed. Try again.</p>";
        }
    }
}
?>

<div class="register-container">
    <h2>Register</h2>

    <form method="POST">
        <label>Name:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Password:</label><br>
        <input type="password" name="password" required><br><br>

        <input type="submit" value="Register" class="form-button">

        <?php if (!empty($registered)): ?>
            <div class="registration-success-inside">
                <p>🎉 Registration successful! 🎉</p>
                <button type="button" onclick="window.location.href='index.php'" class="form-button">
                    Back to Login
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>






<?php require 'footer.php'; ?>
