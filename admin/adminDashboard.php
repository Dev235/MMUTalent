<?php
session_start();
require '../connection.php'; // Note the '..' to go up one directory to find connection.php

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the main login page if not an admin
    header("Location: ../login.php");
    exit();
}

$page_title = "Admin Dashboard";
// We need to adjust the path to the header as well
require '../header.php'; 
?>

<body>
    <?php require '../navbar.php'; // Adjust path for navbar ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Admin Dashboard</h1>
            <p style="color:white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>

        <div class="admin-menu" style="max-width: 800px; margin: 30px auto; padding: 20px; background-color: #fff; border-radius: 10px;">
            <h2 style="text-align: center;">Management Links</h2>
            <ul style="list-style: none; padding: 0; text-align: center;">
                <li style="margin: 15px 0;">
                    <a href="manageUsers.php" style="font-size: 1.2em;">Manage Users</a>
                </li>
                <li style="margin: 15px 0;">
                    <a href="manageForum.php" style="font-size: 1.2em;">Manage Forum</a>
                </li>
                <li style="margin: 15px 0;">
                    <a href="../manageAnnouncements.php" style="font-size: 1.2em;">Manage Announcements</a>
                </li>
                 <li style="margin: 15px 0;">
                    <a href="../manageFAQ.php" style="font-size: 1.2em;">Manage FAQ</a>
                </li>
            </ul>
        </div>
    </div>
    <?php require '../footer.php'; // Adjust path for footer ?>
</body>
</html>
