<?php
require 'connection.php';
$page_title = "Manage Announcements";
require 'header.php';
require 'navbar.php';

$successMessage = "";

/* Deletee */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Remove image file (if any)
    $old = $conn->query("SELECT image_path FROM announcements WHERE id = $id")->fetch_assoc();
    if (!empty($old['image_path']) && file_exists($old['image_path'])) {
        unlink($old['image_path']);
    }

    $conn->query("DELETE FROM announcements WHERE id = $id");
    $successMessage = "Announcement deleted successfully.";
}

/* ADDING */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_announcement'])) {
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);
    $imgPath = null;

    /* uplodaing file */
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp   = $_FILES['image']['tmp_name'];
        $name  = basename($_FILES['image']['name']);
        $ext   = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        $allowed = ['png','jpg','jpeg','gif'];
        if (in_array($ext, $allowed) && $_FILES['image']['size'] <= 2*1024*1024) {     // ≤2 MB
            $newName  = 'uploads/' . time() . '_' . preg_replace('/\s+/', '_', $name);
            if (move_uploaded_file($tmp, $newName)) {
                $imgPath = $newName;
            }
        }
    }

    /* 2b │ insert */
    $stmt = $conn->prepare(
        "INSERT INTO announcements (title, content, image_path) VALUES (?, ?, ?)"
    );
    $stmt->bind_param("sss", $title, $content, $imgPath);
    $stmt->execute();
    $stmt->close();

    $successMessage = "Announcement added successfully.";
}

/* fetching */
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<div id="main-content" style="padding:40px;">
    <div class="title-container"><h1>Manage Announcements</h1></div>

    <h2 style="color:white;margin-top:30px;">Add New Announcement</h2>
    <div style="display:flex;justify-content:center;">
        <form method="POST" enctype="multipart/form-data"
              style="width:100%;max-width:600px;margin-top:20px;background:#fff;
                     padding:20px;border-radius:8px;">
            <input type="hidden" name="add_announcement" value="1">

            <label>Title:</label>
            <input type="text" name="title" required
                   style="width:95%;padding:10px;margin-bottom:10px;">

            <label>Content:</label>
            <textarea name="content" rows="4" required
                      style="width:95%;padding:10px;margin-bottom:10px;"></textarea>

            <label>Image or Poster size ≤ 2 MB:</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.gif"
                   style="margin-bottom:15px;">

            <button type="submit" class="form-button">Add Announcement</button>
        </form>
    </div>

    <?php if ($successMessage): ?>
        <div style="text-align:center;margin-top:20px;">
            <p style="color:white;font-weight:bold;background:green;display:inline-block;
                      padding:10px 20px;border-radius:8px;">
                <?= $successMessage ?>
            </p>
        </div>
    <?php endif; ?>

    <h2 style="color:white;margin-top:50px;">Existing Announcements</h2>
    <table border="1" cellpadding="10" cellspacing="0"
           style="width:100%;background:#fff;text-align:center;">
        <tr style="background:#eee;">
            <th>No.</th>
            <th>Title</th>
            <th>Content</th>
            <th>Image</th>
            <th>Created&nbsp;At</th>
            <th>Actions</th>
        </tr>
        <?php $no = 1; while ($row = $announcements->fetch_assoc()): ?>
        <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['title']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
            <td>
                <?php if ($row['image_path']): ?>
                    <img src="<?= htmlspecialchars($row['image_path']) ?>"
                         style="max-width:100px;height:auto;border-radius:4px;">
                <?php else: ?>—<?php endif; ?>
            </td>
            <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
            <td>
                <a href="editAnnouncement.php?id=<?= $row['id'] ?>"
                   class="form-button"
                   style="background:#ffc107;padding:4px 8px;font-size:.8em;display:inline-block;width:auto;margin-bottom:5px;">
                   Edit
                </a>
                <a href="?delete=<?= $row['id'] ?>"
                   onclick="return confirm('Are you sure?')"
                   class="form-button"
                   style="background:#dc3545;padding:4px 8px;font-size:.8em;display:inline-block;width:auto;">
                   Delete
                </a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<?php require 'footer.php'; ?>
