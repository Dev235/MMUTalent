<?php
require 'connection.php';
$page_title = "Edit Announcement";
require 'header.php';
require 'navbar.php';

$error = "";
$announcement = null;

/* ── 1. FETCH ───────────────────────────────────────────── */
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $announcement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$announcement) $error = "Announcement not found.";
} else {
    $error = "No ID specified.";
}

/* ── 2. UPDATE ──────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id      = intval($_POST['id']);
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    /* 2a │ handle new image (optional) */
    $newPath = $announcement['image_path'];    // default = keep old
    $remove  = isset($_POST['remove_image']);  // checkbox

    if ($remove) {
        // delete old file
        if (!empty($newPath) && file_exists($newPath)) unlink($newPath);
        $newPath = null;
    }

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp   = $_FILES['image']['tmp_name'];
        $name  = basename($_FILES['image']['name']);
        $ext   = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $okExt = ['png','jpg','jpeg','gif'];

        if (in_array($ext, $okExt) && $_FILES['image']['size'] <= 2*1024*1024) {
            // delete old file if replacing
            if (!empty($newPath) && file_exists($newPath)) unlink($newPath);

            $newPath = 'uploads/' . time() . '_' . preg_replace('/\s+/', '_', $name);
            move_uploaded_file($tmp, $newPath);
        }
    }

    /* 2b │ update row */
    $stmt = $conn->prepare(
        "UPDATE announcements
         SET title = ?, content = ?, image_path = ?
         WHERE id = ?"
    );
    $stmt->bind_param("sssi", $title, $content, $newPath, $id);

    if ($stmt->execute()) {
        header("Location: manageAnnouncements.php");
        exit;
    } else {
        $error = "Update failed. Please try again.";
    }
}
?>

<div id="main-content" style="padding:40px;">
    <div class="title-container"><h1>Edit Announcement</h1></div>

    <?php if ($error): ?>
        <p style="color:red;text-align:center;"><?= $error ?></p>
    <?php elseif ($announcement): ?>
        <form method="POST" enctype="multipart/form-data"
              style="max-width:600px;margin:0 auto;background:#fff;
                     padding:20px;border-radius:8px;">
            <input type="hidden" name="id" value="<?= $announcement['id'] ?>">

            <label>Title:</label>
            <input type="text" name="title" required
                   value="<?= htmlspecialchars($announcement['title']) ?>"
                   style="width:95%;padding:10px;margin-bottom:10px;">

            <label>Content:</label>
            <textarea name="content" rows="4" required
                      style="width:95%;padding:10px;">
                <?= htmlspecialchars($announcement['content']) ?>
            </textarea>

            <!-- ── Current image preview ── -->
            <?php if (!empty($announcement['image_path'])): ?>
                <p>Current image:</p>
                <img src="<?= htmlspecialchars($announcement['image_path']) ?>"
                     alt="current image"
                     style="max-width:100%;height:auto;margin-bottom:10px;border-radius:6px;">
                <div style="margin-bottom:10px;">
                    <label>
                        <input type="checkbox" name="remove_image">
                        Remove this image
                    </label>
                </div>
            <?php endif; ?>

            <!-- ── New image upload ── -->
            <label>Upload new image (optional):</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.gif"
                   style="margin-bottom:15px;">

            <button type="submit" class="form-button"
                    style="margin-top:15px;">Update Announcement</button>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
