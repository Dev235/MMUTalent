<?php
require 'connection.php';
$page_title = "Edit Announcement";
require 'header.php';
require 'navbar.php';

$error = "";
$announcement = null;

// Check for ID and fetch announcement
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $announcement = $result->fetch_assoc();
    $stmt->close();

    if (!$announcement) {
        $error = "Announcement not found.";
    }
} else {
    $error = "No ID specified.";
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $stmt = $conn->prepare("UPDATE announcements SET title = ?, content = ? WHERE id = ?");
    $stmt->bind_param("ssi", $title, $content, $id);

    if ($stmt->execute()) {
        $stmt->close();
        header("Location: manageAnnouncements.php");
        exit();
    } else {
        $error = "Update failed. Please try again.";
    }
}
?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Edit Announcement</h1>
    </div>

    <?php if ($error): ?>
        <p style="color: red; text-align: center;"><?= $error ?></p>
    <?php elseif ($announcement): ?>
        <form method="POST" style="max-width: 600px; margin: 0 auto; background-color: #fff; padding: 20px; border-radius: 8px;">
            <input type="hidden" name="id" value="<?= $announcement['id'] ?>">

            <label>Title:</label>
            <input type="text" name="title" required value="<?= htmlspecialchars($announcement['title']) ?>" style="width: 95%; padding: 10px; margin-bottom: 10px;">

            <label>Content:</label>
            <textarea name="content" rows="4" required style="width: 95%; padding: 10px;"><?= htmlspecialchars($announcement['content']) ?></textarea>

            <button type="submit" class="form-button" style="margin-top: 15px;">Update Announcement</button>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
