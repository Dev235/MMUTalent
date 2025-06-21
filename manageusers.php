<?php
session_start();
require 'connection.php'; // Path fixed

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php"); // Path fixed
    exit();
}

$page_title = "Admin: Manage Users";
require 'header.php'; // Path fixed

// Get the ID of the currently logged-in admin
$current_admin_id = $_SESSION['user_id'];
$message = '';

// Handle user deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);

    // CRITICAL: Prevent admin from deleting their own account
    if ($user_id_to_delete == $current_admin_id) {
        $message = "Error: You cannot delete your own account.";
    } else {
        // **CORRECTED LOGIC**: Get the user's profile picture before deleting the user
        $stmt_get_pic = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        $stmt_get_pic->bind_param("i", $user_id_to_delete);
        $stmt_get_pic->execute();
        $result = $stmt_get_pic->get_result();
        if ($user_to_delete = $result->fetch_assoc()) {
            $pic_file = 'images/uploads/profile_pictures/' . $user_to_delete['profile_picture']; // Path fixed
            if (!empty($user_to_delete['profile_picture']) && file_exists($pic_file)) {
                unlink($pic_file); // Delete the actual image file
            }
        }
        $stmt_get_pic->close();

        // Proceed with deleting the user from the database
        $stmt_delete_user = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt_delete_user->bind_param("i", $user_id_to_delete);
        if ($stmt_delete_user->execute()) {
            $message = "User deleted successfully.";
        } else {
            $message = "Error deleting user.";
        }
        $stmt_delete_user->close();
    }
}

// Fetch all users from the database, including their profile picture
$all_users = $conn->query("SELECT user_id, name, email, role, created_at, profile_picture FROM users ORDER BY created_at DESC");
?>

<style>
    /* Admin table styles */
    .admin-table { width: 100%; border-collapse: collapse; background-color: white; }
    .admin-table th, .admin-table td { padding: 12px; border: 1px solid #ddd; text-align: left; vertical-align: middle; }
    .admin-table th { background-color: #f2f2f2; }
    .admin-table .user-avatar {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        object-fit: cover;
    }
    .admin-table .actions a, .admin-table .actions .disabled-link {
        margin-right: 10px;
        text-decoration: none;
        padding: 5px 10px;
        border-radius: 4px;
        color: white;
        display: inline-block;
    }
    .admin-table .actions .edit-link { background-color: #ffc107; }
    .admin-table .actions .delete-link { background-color: #dc3545; }
    .admin-table .actions .disabled-link {
        background-color: #6c757d;
        cursor: not-allowed;
        opacity: 0.6;
    }
    .message-banner {
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        text-align: center;
        font-weight: bold;
    }
    .message-success { background-color: #d4edda; color: #155724; }
    .message-error { background-color: #f8d7da; color: #721c24; }
</style>

<body>
    <?php require 'navbar.php'; ?>

    <div id="main-content">
        <div class="title-container">
            <h1>Manage All Users</h1>
        </div>

        <div class="admin-container" style="max-width: 1200px; margin: 30px auto;">
            <?php if ($message): ?>
                <div class="message-banner <?php echo (strpos($message, 'Error') !== false) ? 'message-error' : 'message-success'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Joined On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($all_users->num_rows > 0): ?>
                        <?php while ($user = $all_users->fetch_assoc()): ?>
                            <tr <?php if ($user['user_id'] == $current_admin_id) echo 'style="background-color: #e3f2fd;"'; ?>>
                                <td>
                                    <?php
                                    $pic_path = 'images/uploads/profile_pictures/' . htmlspecialchars($user['profile_picture']); // Path fixed
                                    if (!empty($user['profile_picture']) && file_exists($pic_path)) {
                                        $img_src = $pic_path;
                                    } else {
                                        $img_src = 'https://placehold.co/50x50/EFEFEF/AAAAAA&text=...';
                                    }
                                    ?>
                                    <img src="<?php echo $img_src; ?>" alt="<?php echo htmlspecialchars($user['name']); ?>" class="user-avatar">
                                </td>
                                <td><?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['role']); ?></td>
                                <td><?php echo date('F j, Y', strtotime($user['created_at'])); ?></td>
                                <td class="actions">
                                    <a href="editProfile.php?id=<?php echo $user['user_id']; ?>" class="edit-link">Edit</a> <!-- Path fixed -->
                                    <?php if ($user['user_id'] == $current_admin_id): ?>
                                        <span class="disabled-link" title="You cannot delete your own account.">Delete</span>
                                    <?php else: ?>
                                        <a href="?action=delete&id=<?php echo $user['user_id']; ?>" class="delete-link" onclick="return confirm('Are you sure you want to delete this user? This will also remove all their talents.');">Delete</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No users found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <?php require 'footer.php'; ?>
</body>
</html>
