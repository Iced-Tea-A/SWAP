<?php
session_start(); 
$alertMessage = '';

include 'db.php'; // Database connection (MySQLi, using $mysqli)
require_once 'mail.php';     // Contains your sendResetPasswordEmail() and OTP generation logic
$mysqli = getDbConnection();
// ---------------------------
// FORGOT PASSWORD LOGIC
// ---------------------------
if (isset($_POST['forgot_password'])) {
    // Handle forgot password independently of the login steps
    $email = $_POST['email'] ?? '';
    if (!$email && isset($_SESSION['pending_email'])) {
        $email = $_SESSION['pending_email'];
    }
    if (!$email) {
        $alertMessage = "Please enter your email to request a password reset.";
    } else {
        // 1) Check if email exists in `account`
        $stmtAcc = $mysqli->prepare("SELECT * FROM account WHERE email = ? LIMIT 1");
        if (!$stmtAcc) { die("Prepare failed: " . $mysqli->error); }
        $stmtAcc->bind_param("s", $email);
        $stmtAcc->execute();
        $resultAcc = $stmtAcc->get_result();
        $accountRow = $resultAcc->fetch_assoc();
        $stmtAcc->close();

        if ($accountRow) {
            // Must be a student (role_id == 3) or already in forced reset (role_id == 4) to reset via email
            if ($accountRow['role_id'] == 3 || $accountRow['role_id'] == 4) {
                // Update role_id to 4 (forced reset)
                $stmt = $mysqli->prepare("UPDATE account SET role_id = 4 WHERE email = ?");
                if (!$stmt) { die("Prepare failed: " . $mysqli->error); }
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $stmt->close();

                // Check if an OTP already exists
                $stmtFP = $mysqli->prepare("SELECT * FROM one_time_password WHERE email = ? LIMIT 1");
                if (!$stmtFP) { die("Prepare failed: " . $mysqli->error); }
                $stmtFP->bind_param("s", $email);
                $stmtFP->execute();
                $resultFP = $stmtFP->get_result();
                $fpRow = $resultFP->fetch_assoc();
                $stmtFP->close();

                if ($fpRow) {
                    $generatedPassword = $fpRow['generatedpassword']; // Reuse existing OTP
                } else {
                    $generatedPassword = generateRandomPassword(40);
                    $stmtInsertFP = $mysqli->prepare("INSERT INTO one_time_password (email, generatedpassword) VALUES (?, ?)");
                    if (!$stmtInsertFP) { die("Prepare failed: " . $mysqli->error); }
                    $stmtInsertFP->bind_param("ss", $email, $generatedPassword);
                    $stmtInsertFP->execute();
                    $stmtInsertFP->close();
                }
                $otpCreated = sendResetPasswordEmail($email, $generatedPassword);

                $alertMessage = $otpCreated
                    ? "A temporary password has been sent to your email. If email entered is valid"
                    : "Failed to send email.";
            } else {
                $alertMessage = "Password reset is only available for students. Contact your Administrator.";
            }
        } else {
            $alertMessage = "A temporary password has been sent to your email. If email entered is valid";
        }
    }
}

// ---------------------------
// CHANGE EMAIL
// ---------------------------
elseif (isset($_POST['change_email'])) {
    // Clear pending email to allow re-entry
    unset($_SESSION['pending_email']);
    $alertMessage = "You can now change your email.";
}

// ---------------------------
// STEP 1: USER CLICKS "NEXT" WITH EMAIL ONLY
// ---------------------------
elseif (isset($_POST['next_step'])) {
    $email = $_POST['email'] ?? '';

    if (!$email) {
        $alertMessage = "Please enter your email first.";
    } else {
        // Store the email in session, but do NOT verify it yet
        $_SESSION['pending_email'] = $email;
    }
}

// ---------------------------
// STEP 2: USER CLICKS "LOGIN" WITH PASSWORD
// ---------------------------
elseif (isset($_POST['final_login'])) {
    $email = $_SESSION['pending_email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $alertMessage = "Please fill in the password field.";
    } else {
        // ----------------
        // Check `account`
        // ----------------
        $stmt = $mysqli->prepare("SELECT * FROM account WHERE email = ? LIMIT 1");
        if (!$stmt) { die("Prepare failed: " . $mysqli->error); }
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $accountRow = $result->fetch_assoc();
        $stmt->close();
        if ($accountRow) {
            // If the user has role_id=4 (forced reset), they might need an OTP
            if ($accountRow['role_id'] == 4) {
                // 1) Check if it matches the account's existing password (if not empty)
                if (!empty($accountRow['password']) && password_verify($password, $accountRow['password'])) {
                    // They used their old password; revert them to role_id=3
                    $stmtUpd = $mysqli->prepare("UPDATE account SET role_id = 3 WHERE email = ?");
                    if (!$stmtUpd) { die("Prepare failed: " . $mysqli->error); }
                    $stmtUpd->bind_param("s", $email);
                    $stmtUpd->execute();
                    $stmtUpd->close();

                    $stmtDel = $mysqli->prepare("DELETE FROM one_time_password WHERE email = ?");
                    if (!$stmtDel) { die("Prepare failed: " . $mysqli->error); }
                    $stmtDel->bind_param("s", $email);
                    $stmtDel->execute();
                    $stmtDel->close();

                    $stmtrole = $mysqli->prepare("SELECT * FROM role WHERE role_id = ? LIMIT 1");
                    $stmtrole->bind_param("i", $accountRow['role_id']);
                    $stmtrole->execute();
                    $result = $stmtrole->get_result();
                    $roleRow = $result->fetch_assoc();
                    $stmtrole->close();
                    $_SESSION['user_role'] = $roleRow['name'];
                    $_SESSION['role_id'] = $accountRow['role_id'];
                    $_SESSION['email'] = $email;
                    $_SESSION['user_id'] = $accountRow['id'];
                    header("Location: home.php");
                    exit;
                } else {
                    // 2) Otherwise, check the OTP table for a match
                    $stmtOTP = $mysqli->prepare("SELECT * FROM one_time_password WHERE email = ? LIMIT 1");
                    if (!$stmtOTP) { die("Prepare failed: " . $mysqli->error); }
                    $stmtOTP->bind_param("s", $email);
                    $stmtOTP->execute();
                    $resultOTP = $stmtOTP->get_result();
                    $otpRow = $resultOTP->fetch_assoc();
                    $stmtOTP->close();

                    if ($otpRow && $otpRow['generatedpassword'] === $password) {
                        // Valid OTP => proceed with forced reset or direct login
                        $stmtDel = $mysqli->prepare("DELETE FROM one_time_password WHERE email = ?");
                        if (!$stmtDel) { die("Prepare failed: " . $mysqli->error); }
                        $stmtDel->bind_param("s", $email);
                        $stmtDel->execute();
                        $stmtDel->close();

                        $stmtrole = $mysqli->prepare("SELECT * FROM role WHERE role_id = ? LIMIT 1");
                        $stmtrole->bind_param("i", $accountRow['role_id']);
                        $stmtrole->execute();
                        $result = $stmtrole->get_result();
                        $roleRow = $result->fetch_assoc();
                        $stmtrole->close();
                        
                        $_SESSION['user_role'] = $roleRow['name'];
                        $_SESSION['email'] = $email;
                        $_SESSION['role_id'] = $accountRow['role_id'];
                        $_SESSION['user_id'] = $accountRow['id'];
                        header("Location: update.php");
                        exit;
                    }
                    $alertMessage = "Invalid Credentials. Please try again.";
                }
            } else {
                // role_id != 4 => normal user
                if (!empty($accountRow['password']) && password_verify($password, $accountRow['password'])) {

                    $stmtrole = $mysqli->prepare("SELECT * FROM role WHERE role_id = ? LIMIT 1");
                    $stmtrole->bind_param("i", $accountRow['role_id']);
                    $stmtrole->execute();
                    $result = $stmtrole->get_result();
                    $roleRow = $result->fetch_assoc();
                    $stmtrole->close();
                    $_SESSION['user_role'] = $roleRow['name'];
                    $_SESSION['email'] = $email;
                    $_SESSION['role_id'] = $accountRow['role_id'];
                    $_SESSION['user_id'] = $accountRow['id'];
                    header("Location: home.php");
                    exit;
                } else {
                    $alertMessage = "Invalid Credentials. Please try again.";
                }
            }
        } else {
            // Possibly first-time user => Check if they exist in `student`
            $stmtStudent = $mysqli->prepare("SELECT * FROM student WHERE student_email = ? LIMIT 1");
            if (!$stmtStudent) { die("Prepare failed: " . $mysqli->error); }
            $stmtStudent->bind_param("s", $email);
            $stmtStudent->execute();
            $resultStudent = $stmtStudent->get_result();
            $student = $resultStudent->fetch_assoc();
            $stmtStudent->close();

            if ($student) {
                // Insert them into `account` with role=4 & empty password, create OTP, etc.
                $stmtInsert = $mysqli->prepare("INSERT INTO account (email, role_id, password) VALUES (?, 4, '')");
                if (!$stmtInsert) { die("Prepare failed: " . $mysqli->error); }
                $stmtInsert->bind_param("s", $email);
                $stmtInsert->execute();
                $insertedId = $mysqli->insert_id;
                $stmtInsert->close();

                // Generate OTP & send (using your function in mail.php)
                $generatedPassword = generateRandomPassword(40);
                $stmtInsertFP = $mysqli->prepare("INSERT INTO one_time_password (email, generatedpassword) VALUES (?, ?)");
                if (!$stmtInsertFP) { die("Prepare failed: " . $mysqli->error); }
                $stmtInsertFP->bind_param("ss", $email, $generatedPassword);
                $stmtInsertFP->execute();
                $stmtInsertFP->close();

                $sent = sendResetPasswordEmail($email, $generatedPassword);

                $_SESSION['role_id'] = 4;
                $_SESSION['email'] = $email;
                $_SESSION['user_id'] = $insertedId;
                $alertMessage = $sent
                  ? "First-time user detected. Check your email for OTP."
                  : "Failed to send OTP. Please contact admin.";
            } else {
                $alertMessage = "Email not found in both account and student records.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Multi-Step Login</title>
  <link rel="stylesheet" href="style\style.css">
</head>
<body>
  <div class="login-container">
    <h1>Login</h1>

    <?php if ($alertMessage): ?>
      <div class="alert"><?= htmlspecialchars($alertMessage) ?></div>
    <?php endif; ?>

    <!-- Step 1: If we have NOT stored pending_email in session, show email field only -->
    <?php if (!isset($_SESSION['pending_email'])): ?>
      <form method="POST">
        <input type="email" name="email" placeholder="Enter your Email">
        <button type="submit" name="next_step">Next</button>
        <button type="submit" name="forgot_password">Forgot Password?</button>
      </form>

    <!-- Step 2: If we DO have pending_email, show password field, plus "Change Email" option -->
    <?php else: ?>
      <form method="POST">
        <p>Email: <strong><?= htmlspecialchars($_SESSION['pending_email']) ?></strong></p>
        <input type="password" name="password" placeholder="Enter your Password">
        <button type="submit" name="final_login">Login</button>
        <button type="submit" name="change_email">Change Email</button>
        <button type="submit" name="forgot_password">Forgot Password?</button>
      </form>
    <?php endif; ?>
  </div>
</body>
</html>
