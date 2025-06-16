<?php
session_start();
require 'connection.php';

if (!isset($_GET['id'])) {
    header("Location: forum.php");
    exit();
}

$topic_id = intval($_GET['id']);

// Handle new reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_content'])) {
    if (!isset($_SESSION['user_id'])) {
        // Optional: redirect to login or show an error
        die("You must be logged in to reply."); 
    }
    $reply_content = trim($_POST['reply_content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($reply_content)) {
        $stmt = $conn->prepare("INSERT INTO forum_posts (post_content, topic_id, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $reply_content, $topic_id, $user_id);
        $stmt->execute();
        $stmt->close();
        // Redirect to the same page to prevent form re-submission
        header("Location: view_topic.php?id=" . $topic_id);
        exit();
    }
}

// Fetch the main topic
$stmt = $conn->prepare("SELECT t.*, u.name as author FROM forum_topics t JOIN users u ON t.user_id = u.user_id WHERE t.topic_id = ?");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$topic_result = $stmt->get_result();
$topic = $topic_result->fetch_assoc();
$stmt->close();

if (!$topic) {
    die("Topic not found.");
}

// Fetch all replies for this topic
$stmt = $conn->prepare("SELECT p.*, u.name as author, u.profile_picture FROM forum_posts p JOIN users u ON p.user_id = u.user_id WHERE p.topic_id = ? ORDER BY p.post_date ASC");
$stmt->bind_param("i", $topic_id);
$stmt->execute();
$posts_result = $stmt->get_result();
$stmt->close();

$page_title = htmlspecialchars($topic['topic_subject']);
require 'header.php';
define('DEFAULT_AVATAR_URL', 'https://placehold.co/60x60/EFEFEF/AAAAAA&text=No+Image');
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1><?php echo htmlspecialchars($topic['topic_subject']); ?></h1>
            <p style="color:white;">Posted by <?php echo htmlspecialchars($topic['author']); ?> on <?php echo date('F d, Y', strtotime($topic['topic_date'])); ?></p>
        </div>

        <div class="topic-container" style="max-width: 900px; margin: 30px auto;">
            <!-- Original Post -->
            <div class="post-card" style="background-color: #fff; padding: 20px; border-radius: 10px; margin-bottom: 20px; border: 2px solid var(--color-primary);">
                <p><?php echo nl2br(htmlspecialchars($topic['topic_content'])); ?></p>
            </div>

            <!-- Replies -->
            <h3 style="color: var(--color-title);">Replies</h3>
            <?php while ($post = $posts_result->fetch_assoc()): 
                $avatar_src = DEFAULT_AVATAR_URL;
                if (!empty($post['profile_picture']) && file_exists('images/uploads/profile_pictures/' . $post['profile_picture'])) {
                    $avatar_src = 'images/uploads/profile_pictures/' . htmlspecialchars($post['profile_picture']);
                }
            ?>
                <div class="post-card" style="background-color: #fff; padding: 20px; border-radius: 10px; margin-bottom: 15px; display: flex; gap: 15px;">
                    <div class="post-author-info" style="text-align: center; flex-shrink: 0;">
                        <img src="<?php echo $avatar_src; ?>" alt="<?php echo htmlspecialchars($post['author']); ?>" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        <h5 style="margin: 5px 0 0 0;"><?php echo htmlspecialchars($post['author']); ?></h5>
                    </div>
                    <div class="post-content" style="flex-grow: 1;">
                        <p style="margin-top: 0;"><?php echo nl2br(htmlspecialchars($post['post_content'])); ?></p>
                        <small style="color: #777;">Posted on <?php echo date('M d, Y, g:i a', strtotime($post['post_date'])); ?></small>
                    </div>
                </div>
            <?php endwhile; ?>

            <!-- Reply Form -->
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="reply-form" style="margin-top: 40px;">
                    <h3 style="color: var(--color-title);">Leave a Reply</h3>
                    <form method="POST" action="">
                        <textarea name="reply_content" rows="5" required style="width: 100%; padding: 10px; margin-bottom: 10px; box-sizing: border-box;"></textarea>
                        <button type="submit" class="form-button" style="width: auto; padding: 10px 20px;">Post Reply</button>
                    </form>
                </div>
            <?php else: ?>
                <p style="margin-top: 40px; text-align: center;"><a href="index.php">Log in</a> to post a reply.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
