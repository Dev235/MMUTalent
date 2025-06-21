<?php
session_start();
require 'connection.php'; // Note the '..' to go up one directory to find connection.php

// Security check: Ensure user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the main login page if not an admin
    header("Location: login.php");
    exit();
}

$page_title = "Admin Dashboard";
// We need to adjust the path to the header as well
require 'header.php'; 
?>

<style>
    /* Admin Dashboard Specific Styles */
    .admin-menu {
        max-width: 800px;
        margin: 30px auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Added box shadow for better visual */
    }

    .admin-menu h2 {
        text-align: center;
        margin-bottom: 25px; /* Increased margin for better spacing */
        color: var(--color-title); /* Using CSS variable for consistency */
        font-family: "Copperplate Gothic", "Copperplate", fantasy; /* Consistent font */
    }

    .admin-menu ul {
        list-style: none;
        padding: 0;
        text-align: center;
    }

    .admin-menu li {
        margin: 15px 0;
    }

    .admin-menu a {
        display: inline-block; /* Allows padding and width */
        font-size: 1.2em;
        color: var(--color-primary); /* Consistent link color */
        text-decoration: none;
        padding: 10px 20px; /* Added padding for clickable area */
        border-radius: 5px; /* Rounded corners for links */
        transition: background-color 0.3s ease, color 0.3s ease; /* Smooth transition on hover */
    }

    .admin-menu a:hover {
        background-color: var(--color-primary); /* Background color on hover */
        color: white; /* Text color on hover */
        box-shadow: 0 2px 10px rgba(0,0,0,0.2); /* Subtle shadow on hover */
    }
</style>

<body>
    <?php require 'navbar.php'; // Adjust path for navbar ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Admin Dashboard</h1>
            <p style="color:white;">Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</p>
        </div>

        <div class="admin-menu">
            <h2>Management Links</h2>
            <ul>
                <li>
                    <a href="manageUsers.php">Manage Users</a>
                </li>
                <li>
                    <a href="manageForum.php">Manage Forum</a>
                </li>
                <li>
                    <a href="manageAnnouncements.php">Manage Announcements</a>
                </li>
                 <li>
                    <a href="manageFAQ.php">Manage FAQ</a>
                </li>
            </ul>
        </div>
    </div>
    <?php require 'footer.php'; // Adjust path for footer ?>
</body>
</html>