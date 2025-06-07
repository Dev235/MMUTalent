<?php
require 'connection.php';
$page_title = "FAQ";
require 'header.php';
require 'navbar.php';

// Handle form submission
$submitted_successfully = false;

if ($_SERVER["REQUEST_METHOD"] === "POST" && !empty($_POST['question'])) {
    $question = htmlspecialchars($_POST['question']);
    $submitted_by = $_POST['email']; // optional

    $stmt = $conn->prepare("INSERT INTO faq (question, submitted_by) VALUES (?, ?)");
    $stmt->bind_param("ss", $question, $submitted_by);
    $stmt->execute();
    $stmt->close();

    $submitted_successfully = true;
}

// Fetch FAQs with answers
$result = $conn->query("SELECT * FROM faq WHERE answer IS NOT NULL ORDER BY submitted_at DESC");
?>

<div id="main-content" style="padding: 40px;">
    <div class="title-container">
        <h1>Frequently Asked Questions</h1>
    </div>

    <!-- Manually Added FAQs -->
    <section style="max-width: 800px; margin: 30px auto;">
        <div class="faq-card">
            <strong>1) How do I register as a talent?</strong><br>
            <span>Answer: Click on the "Register" button at the top and fill in your details. You’ll be redirected to your dashboard after login.</span>
        </div>

        <div class="faq-card">
            <strong>2) Can I upload videos or images of my work?</strong><br>
            <span>Answer: Yes, you can upload your portfolio including images, PDFs, and videos via your dashboard.</span>
        </div>

        <div class="faq-card">
            <strong>3) How do I contact other talents?</strong><br>
            <span>Answer: Use the messaging or forum features to collaborate and connect with others.</span>
        </div>

        <div class="faq-card">
            <strong>4) Test</strong><br>
            <span>Answer: Kalla gey.</span>
        </div>

        <div class="faq-card">
            <strong>5) Test</strong><br>
            <span>Answer: Cockroach gey.</span>
        </div>
    </section>

    <!-- Dynamic FAQs from database -->
    <section style="max-width: 800px; margin: 30px auto;">
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="faq-card">
                <strong>Q: <?= htmlspecialchars($row['question']) ?></strong><br>
                <span>A: <?= htmlspecialchars($row['answer']) ?></span>
            </div>
        <?php endwhile; ?>
    </section>

    <hr style="max-width: 800px; margin: 40px auto;">

    <!-- Submit a Question Form -->
    <section style="max-width: 600px; margin: 0 auto;">
        <h2 style="color: white;">Have a Question/Wanna give us a Feedback?</h2>
        <form method="POST">
            <label for="email" style="color: white;">Your Email (optional):</label><br>
            <input type="email" name="email" placeholder="you@mmu.edu.my"
                style="width: 100%; padding: 10px; margin-bottom: 10px;"><br>

            <label for="question" style="color: white;">Your Question/Feedback:</label><br>
            <textarea name="question" required rows="4"
                style="width: 100%; padding: 10px; margin-bottom: 15px;"></textarea><br>

            <button type="submit" class="form-button">Submit Question</button>

            <?php if ($submitted_successfully): ?>
                <p style="color: white; text-align: center; margin-top: 15px;">
                    Question submitted! An admin will review it soon.
                </p>
            <?php endif; ?>
        </form>
    </section>
</div>

<?php require 'footer.php'; ?>
