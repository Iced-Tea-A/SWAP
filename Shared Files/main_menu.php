<?php
$_SESSION['role'] = 'Student';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Education Portal</title>
    <link rel="stylesheet" href="index.php">
</head>
<body>
<header>
    <h1>Education Portal</h1>
</header>

<!-- Navigation Bar -->
<nav>
    <div class="dropdown">
        <button>Students</button>
        <div class="dropdown-content">
            <a href="student_records.php">Student Records</a>
            <?php if ($_SESSION['role'] !== 'Student'): ?>
                <a href="student_create.php">Add Student</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($_SESSION['role'] !== 'Student'): ?>
    <div class="dropdown">
        <button>Classes</button>
        <div class="dropdown-content">
            <a href="class_main.php">Class Main</a>
            <a href="class_details.php">Class Details</a>
            <a href="class_create.php">Create Class</a>
            <?php endif; ?>
        </div>
    </div>
    <?php if ($_SESSION['role'] !== 'Student'): ?>
    <div class="dropdown">
        <button>Courses</button>
        <div class="dropdown-content">
            <a href="read_courses.php">Course Main</a>
            <a href="create_courses.php">Create Course</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<!-- Main Content --> 
<div class="container">
    <p>Welcome to the Education Portal. Use the navigation above to access different sections.</p>