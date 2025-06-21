<?php
session_start();
require 'connection.php';

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$page_title = "Add New User";
require 'header.php';

$message = '';
$message_type = ''; // 'success' or 'error'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $role = $_POST['role']; // Get selected role

    if (empty($name) || empty($email) || empty($phone_number) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "All fields are required.";
        $message_type = 'error';
    } elseif (strlen($password) < 8) {
        $message = "Password must be at least 8 characters.";
        $message_type = 'error';
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
        $message_type = 'error';
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if email already exists
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        if ($check_result->num_rows > 0) {
            $message = "User with this email already exists.";
            $message_type = 'error';
        } else {
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("sssss", $name, $email, $phone_number, $hashed_password, $role);
                if ($stmt->execute()) {
                    $message = "User added successfully!";
                    $message_type = 'success';
                    // Clear form fields after successful submission
                    $_POST = array(); // Clear all POST data
                } else {
                    $message = "Error adding user: " . $conn->error;
                    $message_type = 'error';
                }
                $stmt->close();
            } else {
                $message = "Database error: Could not prepare statement.";
                $message_type = 'error';
                error_log("Failed to prepare statement for adding user: " . $conn->error);
            }
        }
        $check_stmt->close();
    }
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content" style="padding: 40px;">
        <div class="title-container">
            <h1>Add New User</h1>
        </div>
        <div class="profile-form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px;">
            <?php if ($message): ?>
                <div style="padding: 10px; border-radius: 5px; margin-bottom: 20px; 
                    background-color: <?= $message_type == 'success' ? '#d4edda' : '#f8d7da' ?>; 
                    color: <?= $message_type == 'success' ? '#155724' : '#721c24' ?>; 
                    border: 1px solid <?= $message_type == 'success' ? '#c3e6cb' : '#f5c6cb' ?>;">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="name">Full Name:</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="email">Email Address:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required style="width: 100%; padding: 8px; margin-bottom: 10px;">
                
                <label for="phone_number">Phone Number:</label>
                <input type="text" id="phone_number" name="phone_number" value="<?= htmlspecialchars($_POST['phone_number'] ?? '') ?>" placeholder="e.g., 0123456789" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="password">Password (min 8 characters):</label>
                <input type="password" id="password" name="password" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required style="width: 100%; padding: 8px; margin-bottom: 10px;">

                <label for="role">User Role:</label>
                <select id="role" name="role" required style="width: 100%; padding: 8px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; background-color: white;">
                    <option value="student" <?= (($_POST['role'] ?? '') == 'student') ? 'selected' : '' ?>>Student</option>
                    <option value="admin" <?= (($_POST['role'] ?? '') == 'admin') ? 'selected' : '' ?>>Admin</option>
                </select>

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1;">Add User</button>
                    <a href="manageUsers.php" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center; text-decoration: none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
