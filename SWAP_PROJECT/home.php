<?php
require_once 'auth.php'; 

if (!isset($_SESSION['user_role']) ||
    !in_array(strtolower($_SESSION['user_role']), ['admin', 'faculty', 'student'])) {
    session_unset();
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education Portal</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
<header>
    <h1>Education Portal</h1>
</header>

<!-- Navigation Bar -->
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

<!-- Main Content --> 
<div class="container">
    <p>Welcome to the Education Portal. Use the navigation above to access different sections.</p>