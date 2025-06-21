<?php
// Always need these two la, for database connection and the HTML head part.
require 'connection.php';
$page_title = "Register";
require 'header.php';
?>

<body> 
<div class="register-container">
    <h2>Create an Account</h2>

    <?php
    // These two variables are for showing messages later.
    $registration_successful = false; // By default, registration is not successful.
    $error_message = ''; // Error message also empty at first.

    // This checks if the user clicked the 'Register' button (which sends a POST request).
    // Basically, this whole block of code only runs after the form is submitted.
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get all the data from the form. `trim()` is to remove any accidental spaces.
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone_number = trim($_POST['phone_number']); // Get phone number also.
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // --- Start of input validation ---
        // Simple check, password must be at least 8 characters long.
        if (strlen($password) < 8) {
            $error_message = "Password must be at least 8 characters.";
        } 
        // Another check, the two passwords must match.
        elseif ($password !== $confirm_password) {
            $error_message = "Passwords do not match.";
        } 
        // If all the checks above pass, then we can proceed.
        else {
            // This is a super important security step. Never store passwords as plain text.
            // `password_hash()` will turn the password into a long, crazy string that cannot be reversed.
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            // For now, everyone who registers is a 'student'. Admin account must create manually in DB.
            $role = 'student';

            // This is the SQL query to insert the new user's data into the database.
            // The '?' are placeholders. This is safer than putting variables directly into the query.
            $stmt = $conn->prepare("INSERT INTO users (name, email, phone_number, password, role) VALUES (?, ?, ?, ?, ?)");
            
            if ($stmt) {
                // `bind_param` is where we link our variables to the '?' placeholders.
                // "sssss" means all five variables are strings. s = string, i = integer.
                $stmt->bind_param("sssss", $name, $email, $phone_number, $hashed_password, $role);
                
                // `execute()` runs the query.
                if ($stmt->execute()) {
                    // If it runs successfully, we set this to true.
                    $registration_successful = true;
                } else {
                    // If it fails (e.g., email already exists), show an error.
                    $error_message = "Registration failed. Email might already be taken. Please try again. ";
                }
                // Always close the statement when you're done with it. Good practice.
                $stmt->close();
            } else {
                // This error happens if something is wrong with the SQL query itself.
                $error_message = "Database error: Could not prepare statement.";
            }
        }
    }
    ?>

    <!-- This part uses the $registration_successful variable from before. -->
    <?php if ($registration_successful): ?>
        <!-- If it's true, just show a success message and a button to go to login page. -->
        <div class="registration-success">
            <p>Registration successful! Can login now deyh.</p>
            <a href="login.php" class="form-button" style="width:auto; display:inline-block; background-color:#28a745;">Back to Login</a>
        </div>
    <?php else: ?>
        <!-- If it's false, then show the registration form again. -->
        <?php if (!empty($error_message)): ?>
            <!-- If got any error message, display it here in red color. -->
            <p style="color: red; margin-bottom: 15px;"><?= $error_message ?></p>
        <?php endif; ?>

        <!-- The registration form itself. -->
        <form method="POST" action="">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email Address" required>
            <input type="text" name="phone_number" placeholder="Phone Number (e.g., 0123456789)" required>
            <input type="password" name="password" placeholder="Password (min 8 characters)" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="submit" value="Register" class="form-button">
            <!-- Just a simple button to go back to login page if user gives up. -->
            <button type="button" onclick="window.location.href='login.php'" class="form-button" style="margin-top: 10px; background-color: #6c757d;">
                Back to Login
            </button>
        </form>
    <?php endif; ?>
</div>

<?php require 'footer.php'; ?>
</body>
</html>
