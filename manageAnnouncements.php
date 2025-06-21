<?php
require 'connection.php';
$page_title = "Manage Announcements";
require 'header.php';
require 'navbar.php';

$successMessage = "";

/* Delete */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // Remove image file (if any)
    $stmt_fetch_img = $conn->prepare("SELECT image_path FROM announcements WHERE id = ?");
    if ($stmt_fetch_img) {
        $stmt_fetch_img->bind_param("i", $id);
        $stmt_fetch_img->execute();
        $old = $stmt_fetch_img->get_result()->fetch_assoc();
        if (!empty($old['image_path']) && file_exists($old['image_path'])) {
            unlink($old['image_path']);
        }
        $stmt_fetch_img->close();
    }

    $stmt_delete = $conn->prepare("DELETE FROM announcements WHERE id = ?");
    if ($stmt_delete) {
        $stmt_delete->bind_param("i", $id);
        $stmt_delete->execute();
        $stmt_delete->close();
        $successMessage = "Announcement deleted successfully.";
    } else {
        error_log("Failed to prepare delete statement for announcement: " . $conn->error);
        $successMessage = "Error deleting announcement.";
    }
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
            $newName  = 'images/uploads/announcements/' . time() . '_' . preg_replace('/\s+/', '_', $name); // Updated path
            // Ensure directory exists
            if (!is_dir(dirname($newName))) {
                mkdir(dirname($newName), 0755, true);
            }
            if (move_uploaded_file($tmp, $newName)) {
                $imgPath = $newName;
            } else {
                $successMessage = "Error moving uploaded file.";
            }
        } else {
            $successMessage = "Invalid file type or size too large (max 2MB).";
        }
    }

    /* Insert */
    if (empty($successMessage) || strpos($successMessage, "Error moving") !== false || strpos($successMessage, "Invalid file") !== false) { // Only proceed if no upload errors or specific upload errors
        $stmt = $conn->prepare(
            "INSERT INTO announcements (title, content, image_path) VALUES (?, ?, ?)"
        );
        if ($stmt) {
            $stmt->bind_param("sss", $title, $content, $imgPath);
            if ($stmt->execute()) {
                $successMessage = "Announcement added successfully.";
            } else {
                $successMessage = "Error adding announcement: " . $conn->error;
            }
            $stmt->close();
        } else {
            $successMessage = "Database error: Could not prepare statement for adding announcement.";
            error_log("Failed to prepare statement for adding announcement: " . $conn->error);
        }
    }
}

/* fetching */
$announcements = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC");
?>

<style>
    /* General Container for Forms and Tables */
    .form-container, .table-container {
        max-width: 800px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Form Styling */
    .announcement-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
        color: var(--color-text);
    }

    .announcement-form input[type="text"],
    .announcement-form textarea {
        width: calc(100% - 20px); /* Account for padding */
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1em;
        box-sizing: border-box;
    }

    .announcement-form textarea {
        resize: vertical;
        min-height: 100px;
    }

    .announcement-form input[type="file"] {
        margin-bottom: 20px;
        border: 1px solid #ccc;
        padding: 8px;
        border-radius: 5px;
        background-color: #f9f9f9;
        width: calc(100% - 16px);
        box-sizing: border-box;
    }

    .announcement-form .form-button {
        width: 100%;
        padding: 12px 20px;
        font-size: 1.1em;
        background-color: var(--color-primary);
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .announcement-form .form-button:hover {
        background-color: #3b5f58;
    }

    /* Table Styling (similar to manageFAQ) */
    .manage-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        overflow: hidden;
    }

    .manage-table th, .manage-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee;
    }

    .manage-table thead tr {
        background-color: var(--color-surface);
        color: white;
    }

    .manage-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .manage-table tbody tr:hover {
        background-color: #f1f9ff;
    }

    .manage-table img {
        max-width: 80px;
        height: auto;
        border-radius: 4px;
        display: block;
        margin: 0 auto;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    /* Action Buttons within Table */
    .manage-table .form-button {
        padding: 6px 12px;
        font-size: 0.9em;
        width: auto;
        display: inline-block;
        margin: 2px;
        border-radius: 5px;
        transition: background-color 0.2s ease;
    }

    .manage-table .form-button.red { background-color: #dc3545; }
    .manage-table .form-button.green { background-color: #28a745; }
    .manage-table .form-button.orange { background-color: #ffc107; color: #333; }
    .manage-table .form-button.blue { background-color: var(--color-primary); }

    .manage-table .form-button.red:hover { background-color: #c82333; }
    .manage-table .form-button.green:hover { background-color: #218838; }
    .manage-table .form-button.orange:hover { background-color: #e0a800; }
    .manage-table .form-button.blue:hover { background-color: #3b5f58; }

    /* Success Message Styling */
    .status-message {
        text-align: center;
        margin-top: 20px;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: bold;
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    /* Section Headings */
    h2 {
        color: var(--color-title);
        text-align: center;
        margin-top: 40px;
        margin-bottom: 20px;
        border-bottom: 2px solid var(--color-surface);
        padding-bottom: 10px;
    }
</style>

<div id="main-content">
    <div class="title-container"><h1>Manage Announcements</h1></div>

    <h2 style="margin-top:30px;">Add New Announcement</h2>
    <div class="form-container">
        <form method="POST" enctype="multipart/form-data" class="announcement-form">
            <input type="hidden" name="add_announcement" value="1">

            <label for="title">Title:</label>
            <input type="text" id="title" name="title" required>

            <label for="content">Content:</label>
            <textarea id="content" name="content" rows="4" required></textarea>

            <label for="image">Image or Poster (size ≤ 2 MB):</label>
            <input type="file" id="image" name="image" accept=".png,.jpg,.jpeg,.gif">

            <button type="submit" class="form-button">Add Announcement</button>
        </form>
    </div>

    <?php if ($successMessage): ?>
        <div class="status-message">
            <?= $successMessage ?>
        </div>
    <?php endif; ?>

    <h2 style="margin-top:50px;">Existing Announcements</h2>
    <div class="table-container">
        <?php if ($announcements->num_rows > 0): ?>
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Content</th>
                        <th>Image</th>
                        <th>Created&nbsp;At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $announcements->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['content'])) ?></td>
                        <td>
                            <?php if (!empty($row['image_path']) && file_exists($row['image_path'])): ?>
                                <img src="<?= htmlspecialchars($row['image_path']) ?>" alt="Announcement Image">
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= date('F j, Y', strtotime($row['created_at'])) ?></td>
                        <td>
                            <a href="editAnnouncement.php?id=<?= $row['id'] ?>"
                               class="form-button orange" style="margin-bottom:5px;">
                               Edit
                            </a>
                            <a href="?delete=<?= $row['id'] ?>"
                               onclick="return confirm('Are you sure you want to delete this announcement?')"
                               class="form-button red">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #555;">No announcements found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
