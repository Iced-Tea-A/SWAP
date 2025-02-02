<!--Navigation bar for the Student Pages-->
<?php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    header("Location: ../login.php");
    exit();
    die("Access denied."); //Session start and ensure that current user role is either Faculty or Admin
}
?>

<div class="containertext">
    <nav>
        <div class="dropdown">
            <button>Home</button>
            <div class="dropdown-content">
                <a href="../home.php">Home page</a>
            </div>
        </div>
        <div class="dropdown">
            <button>Students</button>
            <div class="dropdown-content">
                <a href="../students/index.php?route=students">Student Records</a>
                <a href="../students/index.php?route=enrollment">Student Enrollment</a>
                <?php if ($_SESSION['user_role'] !== 'student'): ?>
                <a href="../students/index.php?route=add">Add Student</a>
                <?php endif; ?>
            </div>
        </div>
        <div class="dropdown">
            <button>Classes</button>
            <div class="dropdown-content">
                <a href="view.php">View Class</a>
                <?php if ($_SESSION['user_role'] !== 'student'): ?>
                <a href="modify.php">Modify Class</a>
                <?php endif; ?>
            </div>
        </div>
            <?php if ($_SESSION['user_role'] !== 'student'): ?>
            <div class="dropdown">
            <button>Courses</button>
            <div class="dropdown-content">
                <a href="../courses/read_courses.php">Course Main</a>
                <a href="../courses/create_courses.php">Course Create</a>
            </div>
            </div>
            <?php endif; ?>
            <div class="dropdown">
            <button>Account</button>
                <div class="dropdown-content">
                <a href="../logout.php">Logout</a>
                <?php if ($_SESSION['user_role'] !== 'admin'||$_SESSION['user_role'] !== 'admin'): ?>
                <a href="../update.php">Update Password</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</div>