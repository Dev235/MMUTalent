<?php
session_start();
require 'connection.php';
$page_title = "Community Forum";
require 'header.php';

// Fetch all forum topics, joining with users table to get the author's name
$query = "SELECT t.topic_id, t.topic_subject, t.topic_date, u.name as author, 
          (SELECT COUNT(*) FROM forum_posts WHERE topic_id = t.topic_id) as reply_count
          FROM forum_topics t
          JOIN users u ON t.user_id = u.user_id
          ORDER BY t.topic_date DESC";
$topics_result = $conn->query($query);
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="title-container">
            <h1>Community Forum</h1>
            <p style="color:white;">Discuss ideas, ask questions, and collaborate with other talents.</p>
        </div>

        <div class="forum-container" style="max-width: 1000px; margin: 30px auto;">
            <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
                <a href="create_topic.php" class="form-button" style="width: auto; padding: 10px 20px;">+ Create New Topic</a>
            </div>

            <div class="topics-list" style="background-color: #fff; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); overflow: hidden;">
                <div class="topics-header" style="background-color: #f9f9f9; padding: 15px; display: flex; font-weight: bold;">
                    <div style="flex: 3;">Topic</div>
                    <div style="flex: 1; text-align: center;">Author</div>
                    <div style="flex: 1; text-align: center;">Replies</div>
                </div>
                <?php if ($topics_result->num_rows > 0): ?>
                    <?php while ($topic = $topics_result->fetch_assoc()): ?>
                        <div class="topic-item" style="padding: 15px; display: flex; border-top: 1px solid #eee;">
                            <div style="flex: 3;">
                                <a href="view_topic.php?id=<?php echo $topic['topic_id']; ?>" style="text-decoration: none; color: var(--color-primary); font-weight: bold; font-size: 1.1em;">
                                    <?php echo htmlspecialchars($topic['topic_subject']); ?>
                                </a>
                                <p style="margin: 5px 0 0 0; font-size: 0.8em; color: #777;">Posted on <?php echo date('M d, Y', strtotime($topic['topic_date'])); ?></p>
                            </div>
                            <div style="flex: 1; text-align: center;"><?php echo htmlspecialchars($topic['author']); ?></div>
                            <div style="flex: 1; text-align: center;"><?php echo $topic['reply_count']; ?></div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p style="padding: 20px; text-align: center;">No topics have been created yet. Be the first!</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php require 'footer.php'; ?>
</body>
</html>
