<?php
session_start();
require 'connection.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle Topic Deletion
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $topic_id_to_delete = intval($_GET['id']);
    // Deleting a topic will also delete all its posts due to the database's ON DELETE CASCADE rule.
    $stmt = $conn->prepare("DELETE FROM forum_topics WHERE topic_id = ?");
    $stmt->bind_param("i", $topic_id_to_delete);
    $stmt->execute();
    $stmt->close();
    header("Location: manageForum.php");
    exit();
}


$page_title = "Manage Forum";
require 'header.php';

// Fetch all forum topics
$topics_result = $conn->query(
    "SELECT t.topic_id, t.topic_subject, t.topic_date, u.name as author 
     FROM forum_topics t JOIN users u ON t.user_id = u.user_id 
     ORDER BY t.topic_date DESC"
);
?>
<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Manage Forum Topics</h1>
        </div>

        <div class="table-container" style="max-width: 1200px; margin: 30px auto; background-color: #fff; padding: 20px; border-radius: 10px;">
            <table border="1" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background-color: #f2f2f2;">
                        <th style="padding: 10px;">Subject</th>
                        <th style="padding: 10px;">Author</th>
                        <th style="padding: 10px;">Created On</th>
                        <th style="padding: 10px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($topic = $topics_result->fetch_assoc()): ?>
                        <tr>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($topic['topic_subject']); ?></td>
                            <td style="padding: 10px;"><?php echo htmlspecialchars($topic['author']); ?></td>
                            <td style="padding: 10px;"><?php echo date('Y-m-d', strtotime($topic['topic_date'])); ?></td>
                            <td style="padding: 10px; text-align: center;">
                                <a href="view_topic.php?id=<?php echo $topic['topic_id']; ?>" target="_blank">View Topic</a> |
                                <a href="?action=delete&id=<?php echo $topic['topic_id']; ?>" onclick="return confirm('Are you sure you want to delete this entire topic and all its replies?');" style="color: red;">Delete</a>
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
