<?php
// Start session and check user role
if ($_SESSION['user_role'] !== 'admin') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised/roles not set
    session_destroy();
    header("Location: ../login.php");
    exit();
    die("Access denied. Only admins can delete students.");
}

// Fetch student details using session metric number
if (isset($_SESSION['metric_number'])) {
    $metric_number = $_SESSION['metric_number'];
}else {
    header("Location: index.php");
    exit();
}

if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {

    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
        session_destroy();
        echo "Error: Invalid CSRF token. Please log in again.";
        echo "<a href='../login.php'>Click here to log in</a>";
        exit();  
        die("CSRF token validation failed!"); //CSRF Token Checking
    }

    // Prepare the delete query
    $delete_query = $conn->prepare("DELETE FROM student WHERE metric_number = ?");
    $delete_query->bind_param("s", $metric_number);

    // Execute the delete query
    if ($delete_query->execute()) {
        // Successfully deleted, redirect to student records page
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        header("Location: index.php?route=students&status=delete_success");
        exit;
    } else {
        // Error during delete
        header("Location: index.php?route=students");
        exit;
    }
    // Close the query
    $delete_query->close();
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Student</title>
</head>
<header>
<h1>Are you sure you want to delete this student?</h1>
</header>
<div class="containertext">
    <nav>
        <br>
        <br>
</div>
</nav>
<div class="container">
<body>
<table>
<form method='POST' action="index.php?route=delete&metric_number=<?= htmlspecialchars($metric_number) ?>">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"> <!--Declare CSRF value from the session to ensure proper authorization for POST request-->

    <div class="alert">
        <div class=close-button>
            <button type="submit" name="confirm" value="yes">Yes</button>
        </div>
        <div class=close-button>
            <a href="index.php?route=students&status=delete_fail">No</a>
        </div>
</form>
</body>
    </div>
</html>