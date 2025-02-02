<?php
require_once '../auth.php';
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    header("Location: ../login.php");
    exit();
    die("Access denied. Only admins or faculty are allowed.");
}
if (!isset($_GET['csrf_token']) || $_GET['csrf_token'] !== $_SESSION['csrf_token']) {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    echo "Error: Invalid CSRF token. Please log in again.";
    echo "<a href='login.php'>Click here to log in</a>";
    exit();  
    die("CSRF token validation failed!"); //CSRF Token Checking
}
if (isset($_GET['metric_number']) && isset($_GET['action'])) {
    // Store the metric_number in the session
    $_SESSION['metric_number'] = $_GET['metric_number'];

    // Action check and go to corresponding page
    if ($_GET['action'] === 'edit') {
        // Redirect to edit page
        header("Location: index.php?route=edit");
        exit;
    } elseif ($_GET['action'] === 'assign') {
        // Redirect to assign page
        header("Location: index.php?route=assign");
        exit;
    } elseif ($_GET['action'] === 'delete') {
        // Redirect to delete page
        header("Location: index.php?route=delete");
        exit;
    } else {
        // If invalid, redirect to home
        header("Location: ../home.php");
        exit;
    }
} else {
    // If no parameters, redirect to home
    header("Location: ../home.php");
    exit;
}
?>