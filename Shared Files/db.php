<?php
// db.php - Database connection file
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    header("Location: ../login.php");
    exit();
    die("Access denied."); //Session start and ensure that current user role is either Faculty or Admin
}

$host = 'localhost';
$dbname = 'school';
$user = 'root';
$pass = '';

function getDbConnection() {
    global $host, $dbname, $user, $pass;
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>