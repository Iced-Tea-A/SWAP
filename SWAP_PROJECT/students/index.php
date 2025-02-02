<?php
require_once '../auth.php';
require '../db.php';
generateCSRFToken();
// Check user role for access control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    header("Location: /SWAP_PROJECT/login.php");
    exit();
    die("Access denied.");
}
//Database Connection

$conn = getDbConnection();
// Define available routes and their corresponding files
$routes = [
    'students' => 'displaystudents.php',
    'edit' => 'editstudent.php',
    'assign' => 'assignstudent.php',
    'delete' => 'deletestudent.php',
    'add' => 'addstudent.php',
    'enrollment' => 'displaystudentcourse.php',
];

// Get the requested route from the URL
$route = $_GET['route']; 

if (array_key_exists($route, $routes)) { //Check if file path exists, if it doesn't then prompt user to login again
    $filePath = __DIR__ . '/' . $routes[$route]; // Use absolute paths to avoid directory traversal
    if (file_exists($filePath)) {
        include($filePath);
    } else {
        die("Error: File not found.");
    }
} else {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    echo "Error: Invalid Route Specified. Please log in again.";
    echo "<a href='../login.php'>Click here to log in</a>";
    exit();  
    die("Invalid route specified.");
}

?>