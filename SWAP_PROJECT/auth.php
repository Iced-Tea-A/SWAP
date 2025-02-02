<?php
session_start();

// Define timeout duration (in seconds)
$timeout_duration = 900; // 900 seconds = 15 minutes

function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}


// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /SWAP_PROJECT/login.php"); // Redirect to login if not authenticated
    exit;
}

// Prevent session hijacking (Regenerate Session ID every 5 minutes)
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 300 seconds = 5 min
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Check for session timeout
if (isset($_SESSION['last_activity'])) {
    $inactive_time = time() - $_SESSION['last_activity'];

    if ($inactive_time > $timeout_duration) {
        // Destroy session and log the user out
        session_unset();
        session_destroy();
        header("Location: /SWAP_PROJECT/login.php"); // Redirect with timeout message
        exit;
    }
}

// Update last activity timestamp
$_SESSION['last_activity'] = time();
?>
