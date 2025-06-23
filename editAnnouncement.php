<?php
require 'connection.php';
$page_title = "Edit Announcement";
require 'header.php';
require 'navbar.php';

/* ─────────────────────────────────────────────────────────────
   Configuration
   ───────────────────────────────────────────────────────────── */
$upload_dir = 'images/uploads/announcements/';   // trailing “/” required
$max_size    = 2 * 1024 * 1024;                  // 2 MB
$allowed_ext = ['png', 'jpg', 'jpeg', 'gif'];

$error        = "";
$announcement = null;

/* ── 1. FETCH the record to edit ───────────────────────────── */
if (isset($_GET['id'])) {
    $id   = (int) $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $announcement = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$announcement) $error = "Announcement not found.";
} else {
    $error = "No ID specified.";
}

/* ── 2. UPDATE on POST ─────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* 2a │ gather basic fields */
    $id      = (int) $_POST['id'];
    $title   = trim($_POST['title']);
    $content = trim($_POST['content']);

    /* 2b │ start with the existing image filename (or null) */
    $fileName = $announcement['image_path'] ?? null;

    /* 2c │ handle “remove image” checkbox */
    if (isset($_POST['remove_image']) && $fileName) {
        $old = $upload_dir . $fileName;
        if (file_exists($old)) unlink($old);
        $fileName = null;
    }

    /* 2d │ handle new upload (if any) */
    if (!empty($_FILES['image']['name']) &&
        $_FILES['image']['error'] === UPLOAD_ERR_OK) {

        $tmp  = $_FILES['image']['tmp_name'];
        $orig = basename($_FILES['image']['name']);
        $ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed_ext)) {
            $error = "Invalid file type.";
        } elseif ($_FILES['image']['size'] > $max_size) {
            $error = "Image exceeds 2 MB limit.";
        } else {
            /* create folder on first use */
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

            /* delete any previous image */
            if ($fileName && file_exists($upload_dir.$fileName)) {
                unlink($upload_dir.$fileName);
            }

            /* generate unique filename, move file */
            $fileName = time() . '_' . preg_replace('/\s+/', '_', $orig);
            move_uploaded_file($tmp, $upload_dir.$fileName);
        }
    }

    /* 2e │ update DB if no validation error so far */
    if ($error === "") {
        $stmt = $conn->prepare(
            "UPDATE announcements
             SET title = ?, content = ?, image_path = ?
             WHERE id = ?"
        );
        $stmt->bind_param("sssi", $title, $content, $fileName, $id);

        if ($stmt->execute()) {
            header("Location: manageAnnouncements.php");
            exit;
        }
        $error = "Update failed. Please try again.";
        $stmt->close();
    }

    /* 2f │ keep latest data in $announcement for redisplay */
    $announcement['title']       = $title;
    $announcement['content']     = $content;
    $announcement['image_path']  = $fileName;
}
?>

<div id="main-content">
    <div class="title-container"><h1>Edit Announcement</h1></div>

    <?php if ($error): ?>
        <p style="color:red;text-align:center;"><?= htmlspecialchars($error) ?></p>
    <?php elseif ($announcement): ?>
        <form method="POST" enctype="multipart/form-data"
              style="max-width:600px;margin:0 auto;background:#fff;
                     padding:20px;border-radius:8px;">

            <input type="hidden" name="id" value="<?= $announcement['id'] ?>">

            <!-- Title -->
            <label>Title:</label>
            <input type="text" name="title" required
                   value="<?= htmlspecialchars($announcement['title']) ?>"
                   style="width:95%;padding:10px;margin-bottom:10px;">

            <!-- Content -->
            <label>Content:</label>
            <textarea name="content" rows="4" required
                      style="width:95%;padding:10px;"><?= htmlspecialchars($announcement['content']) ?></textarea>

            <!-- Current image preview -->
            <?php if (!empty($announcement['image_path'])): ?>
                <p>Current image:</p>
                <img src="<?= $upload_dir . htmlspecialchars($announcement['image_path']) ?>"
                     alt="current image"
                     style="max-width:100%;height:auto;margin-bottom:10px;border-radius:6px;">
                <div style="margin-bottom:10px;">
                    <label>
                        <input type="checkbox" name="remove_image">
                        Remove this image
                    </label>
                </div>
            <?php endif; ?>

            <!-- New image -->
            <label>Upload new image (optional):</label>
            <input type="file" name="image" accept=".png,.jpg,.jpeg,.gif"
                   style="margin-bottom:15px;">

            <button type="submit" class="form-button"
                    style="margin-top:15px;">Update Announcement</button>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
