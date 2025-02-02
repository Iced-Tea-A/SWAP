<?php

require_once 'auth.php';
include 'db.php'; // Include the database connection file

// Redirect if the user is not logged in or doesn't have role 4 or 3
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 4 && $_SESSION['role_id'] != 3)) {
    header("Location: home.php"); // Redirect to home if not authenticated or unauthorized
    exit;
}

$mysqli = getDbConnection();
$alertMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_SESSION['role_id'] == 4 || $_SESSION['role_id'] == 3) {
        $newPassword = trim($_POST['new_password'] ?? '');
        $confirmPassword = trim($_POST['confirm_password'] ?? '');

        // Validate that both fields are filled.
        if (empty($newPassword) || empty($confirmPassword)) {
            $alertMessage = 'Please fill in both password fields.';
        }
        // Validate password match.
        elseif ($newPassword !== $confirmPassword) {
            $alertMessage = 'Passwords do not match!';
        }
        else {
            // Define password complexity requirements:
            // At least 8 characters, one uppercase letter, one lowercase letter, and one digit.
            $complexityPattern = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/';

            if (!preg_match($complexityPattern, $newPassword)) {
                $alertMessage = 'Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.';
            } else {
                // Hash the new password and update it in the database
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                
                // Prepare the statement (using MySQLi's object-oriented approach)
                $stmt = $mysqli->prepare("UPDATE account SET password = ?, role_id = 3 WHERE email = ?");
                if (!$stmt) {
                    $alertMessage = "Database error: " . $mysqli->error;
                } else {
                    $stmt->bind_param("ss", $hashedPassword, $_SESSION['email']);
                    if ($stmt->execute()) {
                        // Update successful: log the user out
                        session_unset();
                        session_destroy();
                        header("Location: /SWAP_PROJECT/login.php");
                        exit;
                    } else {
                        $alertMessage = "Failed to update the password. Please try again.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<header>
    <h1>Update Password</h1>
</header>
<body>
<?php if ($_SESSION['user_role'] !== 'OTP'): ?>
<nav>
    <div class="dropdown">
    <button>Home</button>
        <div class="dropdown-content">
            <a href="home.php">Home page</a>
        </div>
    </div>
    <div class="dropdown">
        <button>Students</button>
        <div class="dropdown-content">
            <a href="students/index.php?route=students">Student Records</a>
            <a href="students/index.php?route=enrollment">Student Enrollment</a>
            <?php if ($_SESSION['user_role'] !== 'student'): ?>
                <a href="student_create.php">Add Student</a>
            <?php endif; ?>
        </div>
    </div>
    <div class="dropdown">
        <button>Classes</button>
        <div class="dropdown-content">
        <a href="class/view.php">View Class</a>
        <?php if ($_SESSION['user_role'] !== 'student'): ?>
        <a href="class/modify.php">Modify Class</a>
        <?php endif; ?>
    </div>
    </div>
    <?php if ($_SESSION['user_role'] !== 'student'): ?>
    <div class="dropdown">
        <button>Courses</button>
        <div class="dropdown-content">
        <a href="courses/read_courses.php">Course Main</a>
        <a href="courses/create_courses.php">Course Create</a>
        <?php endif; ?>
    </div>
    </div>
    <div class="dropdown">
        <button>Account</button>
        <div class="dropdown-content">
        <a href="logout.php">Logout</a>
        <?php if ($_SESSION['user_role'] !== 'admin'||$_SESSION['user_role'] !== 'admin'): ?>
        <a href="update.php">Update Password</a>
        <?php endif; ?>
    </div>
    </div>
</nav>
<?php endif; ?>
    <div class="login-container">
        <h1>Update Password</h1>
        <h3>Please note that you will be signed out after updating your password</h3>
        <?php if ($alertMessage): ?>
            <div class="alert"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" required>
            
            <label for="confirm_password">Confirm New Password:</label>
            <input type="password" name="confirm_password" required>
            
            <button type="submit" name="next_step">Update</button>
        </form>
    </div>
</body>
</html>
