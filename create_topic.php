<?php
session_start();
require 'connection.php';

// User must be logged in to create a topic
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login/home
    exit();
}

$page_title = "Create New Topic";
require 'header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $subject = trim($_POST['subject']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($subject) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO forum_topics (topic_subject, topic_content, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $subject, $content, $user_id);
        
        if ($stmt->execute()) {
            $new_topic_id = $stmt->insert_id;
            header("Location: view_topic.php?id=" . $new_topic_id);
            exit();
        } else {
            $message = "Error creating topic. Please try again.";
        }
        $stmt->close();
    } else {
        $message = "Subject and content cannot be empty.";
    }
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Create a New Topic</h1>
        </div>
        <div class="form-container" style="max-width: 800px; margin: 30px auto; background-color: #fff; padding: 30px; border-radius: 10px;">
            <?php if ($message): ?>
                <div style="color: red; margin-bottom: 15px;"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <label for="subject">Topic Subject:</label>
                <input type="text" id="subject" name="subject" required style="width: 100%; padding: 10px; margin-bottom: 15px; box-sizing: border-box;">

                <label for="content">Content:</label>
                <textarea id="content" name="content" rows="10" required style="width: 100%; padding: 10px; margin-bottom: 20px; box-sizing: border-box;"></textarea>

                <div class="form-actions" style="display: flex; gap: 10px;">
                    <button type="submit" class="form-button" style="flex: 1;">Create Topic</button>
                    <a href="forum.php" class="form-button" style="flex: 1; background-color: #6c757d; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
