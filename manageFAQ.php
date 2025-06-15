<?php
require 'connection.php';
$page_title = "Manage FAQ";
require 'header.php';
require 'navbar.php';

$successMessage = "";

// Handle deletion
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM faq WHERE id = $id");
    $successMessage = "FAQ deleted successfully.";
}

// Handle answering a question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['faq_id']) && isset($_POST['answer'])) {
    $id = intval($_POST['faq_id']);
    $answer = trim($_POST['answer']);

    $stmt = $conn->prepare("UPDATE faq SET answer = ? WHERE id = ?");
    $stmt->bind_param("si", $answer, $id);
    $stmt->execute();
    $stmt->close();

    $successMessage = "Answer updated successfully.";
}

// Handle adding to public FAQ
if (isset($_GET['add'])) {
    $id = intval($_GET['add']);
    $conn->query("UPDATE faq SET visible = 1 WHERE id = $id");
    $successMessage = "FAQ successfully added to public page.";
}

// Handle type update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_update'])) {
    $id = intval($_POST['faq_id']);
    $type = $_POST['type'];
    $stmt = $conn->prepare("UPDATE faq SET type = ? WHERE id = ?");
    $stmt->bind_param("si", $type, $id);
    $stmt->execute();
    $stmt->close();
    $successMessage = "FAQ type updated.";
}

// Handle checkbox flag update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkbox_update'])) {
    $id = intval($_POST['faq_id']);
    $flagged = isset($_POST['flagged']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE faq SET flagged = ? WHERE id = ?");
    $stmt->bind_param("ii", $flagged, $id);
    $stmt->execute();
    $stmt->close();

    $successMessage = "Feedback checkbox updated.";
}

// Fetch questions
$answered = $conn->query("SELECT * FROM faq WHERE answer IS NOT NULL ORDER BY submitted_at DESC");
$unanswered_questions = $conn->query("SELECT * FROM faq WHERE answer IS NULL AND type = 'question' ORDER BY submitted_at DESC");
$unanswered_feedbacks = $conn->query("SELECT * FROM faq WHERE answer IS NULL AND type = 'feedback' ORDER BY submitted_at DESC");
?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Manage FAQ</h1>
    </div>

    <!-- Unanswered Questions -->
    <h2 style="margin-top: 40px; color: white;">Pending Actions</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; background-color: #fff; text-align: center;">
        <tr style="background-color: #eee;">
            <th>No.</th>
            <th>Question</th>
            <th>Submitted By</th>
            <th>Type</th>
            <th>Answer</th>
            <th>Actions</th>
        </tr>
        <?php $no = 1; while ($row = $unanswered_questions->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['question']) ?></td>
                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="faq_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="type_update" value="1">
                        <select name="type" onchange="this.form.submit()">
                            <option value="question" <?= $row['type'] == 'question' ? 'selected' : '' ?>>Question</option>
                            <option value="feedback" <?= $row['type'] == 'feedback' ? 'selected' : '' ?>>Feedback</option>
                        </select>
                    </form>
                </td>
                <td style="text-align: left;">
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="faq_id" value="<?= $row['id'] ?>">
                        <textarea name="answer" rows="2" style="width: 100%;" required></textarea>
                        <button type="submit" class="form-button" style="margin-top: 5px;">Save Answer</button>
                    </form>
                </td>
                <td>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Unanswered Feedbacks -->
    <h2 style="margin-top: 60px; color: white;">Pending Feedbacks</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; background-color: #fff; text-align: center;">
        <tr style="background-color: #eee;">
            <th>No.</th>
            <th>Feedback</th>
            <th>Submitted By</th>
            <th>Checkbox</th>
            <th>Type</th>
            <th>Actions</th>
        </tr>
        <?php $no = 1; while ($row = $unanswered_feedbacks->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['question']) ?></td>
                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="faq_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="checkbox_update" value="1">
                        <input type="checkbox" name="flagged" value="1" onchange="this.form.submit()" <?= $row['flagged'] ? 'checked' : '' ?>>
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="faq_id" value="<?= $row['id'] ?>">
                        <input type="hidden" name="type_update" value="1">
                        <select name="type" onchange="this.form.submit()">
                            <option value="question" <?= $row['type'] == 'question' ? 'selected' : '' ?>>Question</option>
                            <option value="feedback" <?= $row['type'] == 'feedback' ? 'selected' : '' ?>>Feedback</option>
                        </select>
                    </form>
                </td>
                <td>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <!-- Answered FAQs -->
    <h2 style="margin-top: 60px; color: white;">Answered FAQs</h2>
    <table border="1" cellpadding="10" cellspacing="0" style="width: 100%; background-color: #fff; text-align: center;">
        <tr style="background-color: #eee;">
            <th>No.</th>
            <th>Question</th>
            <th>Submitted By</th>
            <th>Answer</th>
            <th>Visible</th>
            <th>Actions</th>
        </tr>
        <?php $no = 1; while ($row = $answered->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row['question']) ?></td>
                <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                <td><?= htmlspecialchars($row['answer']) ?></td>
                <td><?= $row['visible'] ? '✔ Yes' : '❌ No' ?></td>
                <td>
                    <?php if (!$row['visible']): ?>
                        <a href="?add=<?= $row['id'] ?>" class="form-button" style="background-color: #28a745; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto;">Add</a>
                    <?php else: ?>
                        <span style="font-size: 0.9em; color: green;">✔ Added</span>
                    <?php endif; ?>
                    <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="form-button" style="background-color: #dc3545; padding: 4px 8px; font-size: 0.8em; display: inline-block; width: auto; margin-top: 5px;">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

    <?php if (!empty($successMessage)): ?>
        <div style="text-align:center; margin-top:30px;">
            <p style="color: white; font-weight: bold; background-color: green; display: inline-block; padding: 10px 20px; border-radius: 8px;">
                <?= $successMessage ?>
            </p>
        </div>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
