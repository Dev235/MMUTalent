<?php
require 'connection.php';
$page_title = "Manage Announcements";
require 'header.php';
require 'navbar.php';

$successMessage = "";

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM announcements WHERE id = $id");
    $successMessage = "Announcement deleted successfully.";
}

// Handle addition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);

    $stmt = $conn->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
    $stmt->bind_param("ss", $title, $content);
    $stmt->execute();
    $stmt->close();

    $successMessage = "Announcement added successfully.";
}

// Fetch announcements
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Manage Announcements</h1>
    </div>

    <h2 style="color: white; margin-top: 30px;">Add New Announcement</h2>
    <div style="display: flex; justify-content: center;">
        <form method="POST" style="width: 100%; max-width: 600px; margin-top: 20px; background-color: #fff; padding: 20px; border-radius: 8px;">
            <input type="hidden" name="add_announcement" value="1">
            <label>Title:</label>
            <input type="text" name="title" required style="width: 95%; padding: 10px; margin-bottom: 10px;">

            <label>Content:</label>
            <textarea name="content" rows="4" required style="width: 95%; padding: 10px; margin-bottom: 10px;"></textarea>

            <button type="submit" class="form-button">Add Announcement</button>
        </form>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div style="text-align:center; margin-top:20px;">
            <p style="color: white; font-weight: bold; background-color: green; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                <?= $successMessage ?>
            </p>
        </div>
    <?php endif; ?>

    <h2 style="color: white; margin-top: 50px;">Existing Announcements</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; background-color: #fff; text-align: center;">
        <tr style="background-color: #eee;">
            <th>No.</th>
            <th>Title</th>
            <th>Content</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php $no = 1; while ($row = $announcements->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['title']) ?></td>
                <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                <td>
                    <a href="editAnnouncement.php?id=<?= $row['id'] ?>" class="form-button" style="background-color: #ffc107; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-bottom: 5px;">Edit</a>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require 'footer.php'; ?>
