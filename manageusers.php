<?php
session_start();
require 'connection.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle User Deletion
$delete_success_message = '';
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);
    // Extra safety: prevent admin from deleting their own account from this interface
    if ($user_id_to_delete !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        if ($stmt) {
            $stmt->bind_param("i", $user_id_to_delete);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $delete_success_message = "User deleted successfully.";
            } else {
                $delete_success_message = "User not found or could not be deleted.";
            }
            $stmt->close();
        } else {
            error_log("Failed to prepare statement for user deletion: " . $conn->error);
            $delete_success_message = "Error preparing delete statement.";
        }
    } else {
        $delete_success_message = "You cannot delete your own admin account from here.";
    }
    // Redirect to clear GET parameters and display message
    header("Location: manageUsers.php?status=" . urlencode($delete_success_message));
    exit();
}

$page_title = "Manage Users";
require 'header.php';

// Fetch all users
$users_result = $conn->query("SELECT user_id, name, email, phone_number, role, created_at FROM users ORDER BY created_at DESC"); // NEW: Fetch phone_number

// Check for status message from redirect
$status_message = '';
if (isset($_GET['status'])) {
    $status_message = htmlspecialchars($_GET['status']);
}

?>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Manage Users</h1>
        </div>

        <?php if (!empty($status_message)): ?>
            <div style="text-align:center; margin-top:20px;">
                <p style="color: green; font-weight: bold; background-color: #d4edda; border: 1px solid #c3e6cb; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                    <?= $status_message ?>
                </p>
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px; max-width: 1200px; margin: 30px auto 20px auto;">
            <a href="addUser.php" class="form-button" style="width: auto; padding: 10px 20px; background-color: var(--color-primary);">+ Add New User</a>
        </div>

        <div class="table-container" style="max-width: 1200px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Name</th>
                        <th style="padding: 10px;">Email</th>
                        <th style="padding: 10px;">Phone Number</th> <!-- NEW -->
                        <th style="padding: 10px;">Role</th>
                        <th style="padding: 10px;">Joined On</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($user = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 10px;"><?php echo $user['user_id']; ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($user['name']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($user['email']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($user['phone_number'] ?? 'N/A'); ?></td> <!-- NEW -->
                            <td style="padding: 10px;"><?php echo htmlspecialchars($user['role']); ?></td>
                            <td style="padding: 10px;"><?php echo date('Y-m-d', strtotime($user['created_at'])); ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <a href="userDashboard.php?id=<?php echo $user['user_id']; ?>" target="_blank">View</a>
                                <?php if ($user['user_id'] !== $_SESSION['user_id']): // Can't delete self ?>
                                    | <a href="?action=delete&id=<?php echo $user['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this user? This is irreversible.');" style="color: red;">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
