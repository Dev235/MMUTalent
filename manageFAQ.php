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
    if ($stmt) {
        $stmt->bind_param("si", $answer, $id);
        $stmt->execute();
        $stmt->close();
        $successMessage = "Answer updated successfully.";
    } else {
        error_log("Failed to prepare statement for FAQ answer update: " . $conn->error);
        $successMessage = "Error updating answer.";
    }
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
    if ($stmt) {
        $stmt->bind_param("si", $type, $id);
        $stmt->execute();
        $stmt->close();
        $successMessage = "FAQ type updated.";
    } else {
        error_log("Failed to prepare statement for FAQ type update: " . $conn->error);
        $successMessage = "Error updating FAQ type.";
    }
}

// Handle checkbox flag update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['checkbox_update'])) {
    $id = intval($_POST['faq_id']);
    $flagged = isset($_POST['flagged']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE faq SET flagged = ? WHERE id = ?");
    if ($stmt) {
        $stmt->bind_param("ii", $flagged, $id);
        $stmt->execute();
        $stmt->close();
        $successMessage = "Feedback checkbox updated.";
    } else {
        error_log("Failed to prepare statement for feedback checkbox update: " . $conn->error);
        $successMessage = "Error updating feedback checkbox.";
    }
}

// Fetch questions
$answered = $conn->query("SELECT * FROM faq WHERE answer IS NOT NULL ORDER BY submitted_at DESC");
$unanswered_questions = $conn->query("SELECT * FROM faq WHERE answer IS NULL AND type = 'question' ORDER BY submitted_at DESC");
$unanswered_feedbacks = $conn->query("SELECT * FROM faq WHERE answer IS NULL AND type = 'feedback' ORDER BY submitted_at DESC");
?>

<style>
    /* General Container for Tables */
    .table-container {
        max-width: 1200px;
        margin: 30px auto;
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    /* Table Styling */
    .manage-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 15px; /* Adjust spacing */
        border: 1px solid #ddd; /* Light border around table */
        border-radius: 8px; /* Rounded corners for the table */
        overflow: hidden; /* Ensures rounded corners apply to content */
    }

    .manage-table th, .manage-table td {
        padding: 12px 15px;
        text-align: left;
        border-bottom: 1px solid #eee; /* Lighter row separators */
    }

    .manage-table thead tr {
        background-color: var(--color-surface); /* Header background from color palette */
        color: white; /* White text for headers */
    }

    .manage-table tbody tr:nth-child(even) {
        background-color: #f9f9f9; /* Zebra striping for readability */
    }

    .manage-table tbody tr:hover {
        background-color: #f1f9ff; /* Subtle hover effect */
    }

    /* Form within Table Cells (e.g., for answer textarea) */
    .manage-table td form {
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 5px; /* Spacing between textarea and button */
    }

    .manage-table td textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ccc;
        border-radius: 4px;
        box-sizing: border-box; /* Include padding in width */
        resize: vertical; /* Allow vertical resizing */
        min-height: 50px;
    }

    /* Action Buttons within Table */
    .manage-table .form-button {
        padding: 6px 12px;
        font-size: 0.9em;
        width: auto; /* Allow buttons to size to content */
        display: inline-block;
        margin: 2px; /* Small margin between buttons */
        border-radius: 5px; /* Rounded buttons */
        transition: background-color 0.2s ease;
    }

    .manage-table .form-button.red { background-color: #dc3545; }
    .manage-table .form-button.green { background-color: #28a745; }
    .manage-table .form-button.orange { background-color: #ffc107; color: #333; } /* For Edit/Save */
    .manage-table .form-button.blue { background-color: var(--color-primary); }

    .manage-table .form-button.red:hover { background-color: #c82333; }
    .manage-table .form-button.green:hover { background-color: #218838; }
    .manage-table .form-button.orange:hover { background-color: #e0a800; }
    .manage-table .form-button.blue:hover { background-color: #3b5f58; }

    /* Success/Error Message Styling */
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
    
    /* Select Dropdowns */
    .manage-table select {
        padding: 6px 10px;
        border-radius: 4px;
        border: 1px solid #ccc;
        background-color: white;
        font-size: 0.9em;
    }

    /* Checkbox Styling */
    .manage-table input[type="checkbox"] {
        transform: scale(1.2); /* Make checkbox slightly larger */
        margin-right: 5px;
    }
</style>

<div id="main-content">
    <div class="title-container">
        <h1>Manage FAQ & Feedback</h1>
    </div>

    <?php if (!empty($successMessage)): ?>
        <div class="status-message">
            <?= $successMessage ?>
        </div>
    <?php endif; ?>

    <h2 style="margin-top: 40px;">Pending Questions</h2>
    <div class="table-container">
        <?php if ($unanswered_questions->num_rows > 0): ?>
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Question</th>
                        <th>Submitted By</th>
                        <th>Type</th>
                        <th>Answer</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                            <td>
                                <form method="POST">
                                    <input type="hidden" name="faq_id" value="<?= $row['id'] ?>">
                                    <textarea name="answer" rows="2" placeholder="Provide answer..." required></textarea>
                                    <button type="submit" class="form-button orange">Save Answer</button>
                                </form>
                            </td>
                            <td>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this entry?')" class="form-button red">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #555;">No pending questions.</p>
        <?php endif; ?>
    </div>

    <h2 style="margin-top: 60px;">Pending Feedbacks</h2>
    <div class="table-container">
        <?php if ($unanswered_feedbacks->num_rows > 0): ?>
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Feedback</th>
                        <th>Submitted By</th>
                        <th>Flagged</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
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
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this entry?')" class="form-button red">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #555;">No pending feedback.</p>
        <?php endif; ?>
    </div>

    <h2 style="margin-top: 60px;">Answered FAQs</h2>
    <div class="table-container">
        <?php if ($answered->num_rows > 0): ?>
            <table class="manage-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Question</th>
                        <th>Submitted By</th>
                        <th>Answer</th>
                        <th>Visible</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; while ($row = $answered->fetch_assoc()): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row['question']) ?></td>
                            <td><?= htmlspecialchars($row['submitted_by']) ?></td>
                            <td><?= htmlspecialchars($row['answer']) ?></td>
                            <td><?= $row['visible'] ? '✔ Yes' : '❌ No' ?></td>
                            <td>
                                <?php if (!$row['visible']): ?>
                                    <a href="?add=<?= $row['id'] ?>" class="form-button green">Add to Public</a>
                                <?php else: ?>
                                    <span style="font-size: 0.9em; color: green; font-weight: bold;">✔ Added</span>
                                <?php endif; ?>
                                <a href="?delete=<?= $row['id'] ?>" onclick="return confirm('Are you sure you want to delete this entry?')" class="form-button red" style="margin-left: 5px;">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p style="text-align: center; padding: 20px; color: #555;">No answered FAQs available yet.</p>
        <?php endif; ?>
    </div>
</div>

<?php require 'footer.php'; ?>
