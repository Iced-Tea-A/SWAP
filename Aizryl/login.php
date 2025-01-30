<?php
include 'db_connection.php'; // Include the database connection file

session_start();

// Handle login form submission
$alertMessage = ''; // Variable to store alert messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['change_email'])) {
        // Reset email verification to allow changing email
        unset($_SESSION['email_verified'], $_SESSION['email']);
    } else {
        $email = $_POST['email'] ?? null;
        $password = $_POST['password'] ?? null;

        if ($email && !isset($_SESSION['email_verified'])) {
            // Check the email in the account table
            $stmt = $pdo->prepare("SELECT * FROM account WHERE email = ?");
            $stmt->execute([$email]);
            $account = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($account) {
                // Email found in the account table, store account info in session
                $_SESSION['email_verified'] = true;
                $_SESSION['email'] = $account['email'];
                $_SESSION['role_id'] = $account['role_id'];
                $_SESSION['name'] = null; // No name for non-students
            } else {
                // Check the email in the student table
                $stmt = $pdo->prepare("SELECT * FROM student WHERE student_email = ?");
                $stmt->execute([$email]);
                $student = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($student) {
                    // Email found only in the student table, first-time login
                    $alertMessage = 'First time login please refer to your email.';
                    $_SESSION['email_verified'] = true; // Allow password entry
                    $_SESSION['email'] = $email; // Store email for tracking
                    $_SESSION['name'] = $student['name']; // Store student name
                    $_SESSION['role_id'] = null; // No role_id for first-time login
                } else {
                    // Email not found in either table
                    $alertMessage = 'Invalid email.';
                    unset($_SESSION['email_verified'], $_SESSION['email'], $_SESSION['name']);
                }
            }
        } elseif (isset($_SESSION['email_verified'], $_SESSION['email']) && $password) {
            // Verify the password for the email
            $stmt = $pdo->prepare("SELECT * FROM account WHERE email = ?");
            $stmt->execute([$_SESSION['email']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && $password === $user['password']) {
                // Start a session and store account details
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role_id'] = $user['role_id'];
                unset($_SESSION['email_verified'], $_SESSION['email'], $_SESSION['name']); // Clean up session variables

                // Redirect to class_main.php after successful login
                header("Location: modify.php");
                exit;
            } else {
                $alertMessage = 'Invalid password.';
            }
        } else {
            $alertMessage = 'Please fill in all fields.';
        }
    }
}
?>
<script>

</script>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h1>Login</h1>
        <?php if ($alertMessage): ?>
            <div class="alert"><?= htmlspecialchars($alertMessage) ?></div>
        <?php endif; ?>
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your Email" value="<?= htmlspecialchars($_SESSION['email'] ?? '') ?>" required <?= isset($_SESSION['email_verified']) ? 'readonly' : '' ?>>

            <?php if (isset($_SESSION['email_verified'])): ?>
                <input type="password" name="password" placeholder="Enter your Password">
                <button type="submit">Login</button>
                <button type="submit" name="change_email">Change Email</button>
            <?php else: ?>
                <button type="submit">Next</button>
            <?php endif; ?>
        </form>
    </div>
</body>
</html>