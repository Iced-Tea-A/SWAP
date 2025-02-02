<?php
require_once 'auth.php';

// Destroy all session data
session_unset();
session_destroy();

// Redirect to login page
header("Location: login.php");
exit;
?>
