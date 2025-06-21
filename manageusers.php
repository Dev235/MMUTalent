<?php
session_start();
require 'connection.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle User Deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $user_id_to_delete = intval($_GET['id']);
    // Extra safety: prevent admin from deleting their own account from this interface
    if ($user_id_to_delete !== $_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $user_id_to_delete);
        $stmt->execute();
        $stmt->close();
    }
    header("Location: manageUsers.php");
    exit();
}


$page_title = "Manage Users";
require 'header.php';

// Fetch all users
$users_result = $conn->query("SELECT user_id, name, email, role, created_at FROM users ORDER BY created_at DESC");
?>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Manage Users</h1>
        </div>

        <div class="table-container" style="max-width: 1200px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px;">ID</th>
                        <th style="padding: 10px;">Name</th>
                        <th style="padding: 10px;">Email</th>
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
