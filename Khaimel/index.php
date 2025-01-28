<?php
session_start();
$student_email = "lax22@gmail.com";
$user_role = "admin";
// Check user role for access control
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    die("Access denied.");
}

// Define available routes and their corresponding files
$routes = [
    'students' => 'displaystudents.php',
    'edit' => 'editstudent.php',
    'assign' => 'assignstudent.php',
    'delete' => 'deletestudent.php',
    'add' => 'addstudent.php',
    'enrollment' => 'displaystudentcourse.php',
    'css' => '../style/style.css',
    'home' => 'home.php', // Default page
];

// Get the requested route from the URL
$route = $_GET['route'] ?? 'home'; // Default to 'home' if no route is provided
if (array_key_exists($route, $routes)) {
    $filePath = __DIR__ . '/' . $routes[$route]; // Use absolute paths to avoid directory traversal
    if (file_exists($filePath)) {
        include($filePath);
    } else {
        die("Error: File not found.");
    }
} else {
    die("Invalid route specified.");
}

// Check if the route is valid, then include the corresponding file
// if (array_key_exists($route, $routes)) {
//     include($routes[$route]);
// } else {
//     die("Invalid route specified.");
// }
?>