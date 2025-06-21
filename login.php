<?php
require 'connection.php';
$page_title = "Login";
require 'header.php';
session_start();

// If user is already logged in, redirect them to their dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') { // Check user role from session
        header("Location: adminDashboard.php"); // Redirect admin to admin dashboard
    } else {
        header("Location: userDashboard.php"); // Redirect non-admin to user dashboard
    }
    exit;
}
?>

<body>
    <?php require 'navbar.php'; ?>
    <div id="main-content">
        <div class="login-container">
            <h2 id="login-title">Student Login</h2>

            <?php
            $error_message = '';
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
                $email = trim($_POST['email']);
                $password = $_POST['password'];
                $selected_role_from_form = $_POST['selected_role'] ?? 'student'; // Get selected role from hidden input

                $stmt = $conn->prepare("SELECT user_id, name, email, password, role FROM users WHERE email = ?"); // Select role from users table
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();
                $stmt->close();

                if ($user && password_verify($password, $user['password'])) { // Verify password
                    // NEW: Enforce slider selection
                    if ($user['role'] !== $selected_role_from_form) {
                        $error_message = "Your account role does not match the selected login type (" . ucfirst($selected_role_from_form) . ").";
                    } else {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['role'] = $user['role']; // Store user's role in session
                        
                        if ($user['role'] === 'admin') { // Redirect based on role
                            header("Location: adminDashboard.php");
                        } else {
                            header("Location: userDashboard.php");
                        }
                        exit;
                    }
                } else {
                    $error_message = "Invalid email or password.";
                }
            }
            ?>

            <?php if (!empty($error_message)): ?>
                <p style='color: red;'><?= htmlspecialchars($error_message) ?></p>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="login-toggle-switch" style="margin-bottom: 20px; text-align: center;">
                    <label class="switch" style="position: relative; display: inline-block; width: 120px; height: 34px;">
                        <input type="checkbox" id="adminToggle" style="opacity: 0; width: 0; height: 0;">
                        <span class="slider round" style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: var(--color-primary); -webkit-transition: .4s; transition: .4s; border-radius: 34px;"></span>
                        <span class="slider-text student-text" style="position: absolute; color: white; left: 10px; top: 50%; transform: translateY(-50%); font-size: 0.9em; transition: .4s;"></span>
                        <span class="slider-text admin-text" style="position: absolute; color: white; right: 10px; top: 50%; transform: translateY(-50%); font-size: 0.9em; opacity: 0; transition: .4s;"></span>
                    </label>
                </div>
                
                <label>Email:</label><br>
                <input type="email" name="email" required><br><br>

                <label>Password:</label><br>
                <input type="password" name="password" required><br><br>

                <input type="hidden" name="selected_role" id="selectedRole" value="student"> 

                <input type="submit" name="login" value="Login" class="form-button">

                <button type="button" onclick="window.location.href='registration.php'" class="form-button" style="margin-top: 10px;">
                    Register
                </button>
            </form>
        </div>
        <?php require 'footer.php'; ?>
    </div>

    <style>
        /* Styles for the slider toggle */
        .switch input:checked + .slider {
            background-color: var(--color-title); /* Darker color for admin */
        }

        .switch input:focus + .slider {
            box-shadow: 0 0 1px var(--color-title);
        }

        .switch input:checked + .slider:before {
            -webkit-transform: translateX(86px); /* Adjusted to move to the right */
            -ms-transform: translateX(86px);
            transform: translateX(86px);
        }

        /* Slider button */
        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%;
        }

        /* Text positioning - These rules are technically still present but operate on empty spans */
        .slider-text {
            pointer-events: none; /* Allows clicks to pass through to the slider */
        }
        
        .switch input:checked ~ .student-text {
            opacity: 0;
        }

        .switch input:checked ~ .admin-text {
            opacity: 1; /* This rule would still make an empty span visible if desired, though no text is there */
        }
        .switch input:not(:checked) ~ .admin-text {
            opacity: 0; /* Hide admin text when not checked */
        }
        .switch input:not(:checked) ~ .student-text {
            opacity: 1; /* Show student text when not checked */
        }
    </style>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const adminToggle = document.getElementById('adminToggle');
            const loginTitle = document.getElementById('login-title');
            const selectedRoleInput = document.getElementById('selectedRole'); // Get the hidden input

            // Set initial state based on default unchecked checkbox
            loginTitle.textContent = "Student Login";
            selectedRoleInput.value = "student"; // Initialize hidden input value

            adminToggle.addEventListener('change', function() {
                if (this.checked) {
                    loginTitle.textContent = "Admin Login";
                    selectedRoleInput.value = "admin"; // Set hidden input to 'admin'
                } else {
                    loginTitle.textContent = "Student Login";
                    selectedRoleInput.value = "student"; // Set hidden input to 'student'
                }
            });
        });
    </script>
</body>
</html>