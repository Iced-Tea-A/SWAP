<?php
session_start();
$student_email = "lax22@gmail.com";
$user_role = "admin";
$_SESSION['user_role'] = $user_role;
$_SESSION['user_email'] = $student_email;  // Save the student's metric number in session
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    die("Access denied. Only admins can edit students."); //Session start and ensure that current user role is either Faculty or Admin
}

// Database connection details
include('../sql/db.php');

$conn = getDbConnection();
$where_condition = "";
if ($user_role === 'student') {
    // $where_condition = "AND (? = '' OR s.student_email = ?";
    $where_condition = "AND s.student_email = ?";
}
// Pagination
$enrollments_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Retrieve the current page from the URL, if there is a parameter, convert to integer
$offset = ($page - 1) * $enrollments_per_page; //Offset to determine which rows are displayed 

// Search function
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';  //Check if there is a search parameter in the url and trim the value of the search parameter
$filtered_search_query = preg_replace("/[^a-zA-Z\s'-]/", '', $search_query); 

// Fetch total records for pagination
$total_enrollments_sql = "SELECT COUNT(*) AS total 
                          FROM enrollment e
                          INNER JOIN student s ON e.student_email = s.student_email
                          INNER JOIN course_class cc ON e.course_class_id = cc.course_class_id
                          INNER JOIN course c ON cc.course_code = c.course_code
                          WHERE ? = '' OR s.name LIKE ? OR s.student_email LIKE ?";
$stmt = $conn->prepare($total_enrollments_sql);  //Prepared statement to avoid SQL injection
$search_param = "%$filtered_search_query%"; //Insert wildcard to match query to any part of student's name or email
$stmt->bind_param('sss', $filtered_search_query, $search_param, $search_param);   
$stmt->execute();
$total_result = $stmt->get_result();
$total_enrollments = $total_result->fetch_assoc()['total'];  //Retrieves the total number of enrollments
$total_pages = ceil($total_enrollments / $enrollments_per_page); //Round up the total number of pages needed
$stmt->close();


// Query to fetch enrollments with pagination and search
$sql = "SELECT s.name AS student_name, s.student_email, c.name AS course_name, cn.name AS class_name, d.name AS department_name
        FROM enrollment e
        INNER JOIN student s ON e.student_email = s.student_email
        INNER JOIN course_class cc ON e.course_class_id = cc.course_class_id
        INNER JOIN course c ON cc.course_code = c.course_code
        INNER JOIN classname cn ON cc.classname_id = cn.classname_id
        INNER JOIN department d ON s.department_id = d.department_id
        WHERE (? = '' OR s.name LIKE ? OR s.student_email LIKE ?)
        $where_condition
        LIMIT ?, ?";  //Limit to extract the enrollments based on the page number and the number of enrollments needed per page
$stmt = $conn->prepare($sql);
if ($user_role === 'student') {
    // If the user is a student, bind their email to the query
    $stmt->bind_param('ssssii', $search_query, $search_param, $search_param, $student_email, $offset, $enrollments_per_page);
} else {
    // Admin/faculty case: No need for filtering by metric number
    $stmt->bind_param('sssii', $search_query, $search_param, $search_param, $offset, $enrollments_per_page);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Records</title>
</head>
<header> 
        <h1>Student Enrollment</h1> 
    </header>
    <div class = "containertext">        
    <nav> 
        <div class="dropdown"> 
            <button>Students</button> 
            <div class="dropdown-content"> 
                <a href="index.php?route=students">Student Records</a> 
                <a href="index.php?route=enrollment">Student Enrollment</a>
                <?php if ($_SESSION['user_role'] !== 'student'): ?>
                    <a href="index.php?route=add">Add Student</a>
                <?php endif; ?>
            </div> 
        </div> 
        <?php if ($_SESSION['user_role'] !== 'student'): ?>
        <div class="dropdown"> 
            <button>Classes</button> 
            <div class="dropdown-content"> 
                <a href="class_main.php">Class Main</a> 
                <a href="class_details.php">Class Details</a> 
                <a href="class_create.php">Create Class</a> 
            </div> 
        </div> 
        <div class="dropdown"> 
            <button>Courses</button> 
            <div class="dropdown-content"> 
                <a href="course_main.php">Course Main</a> 
                <a href="course_details.php">Course Details</a> 
                <a href="course_create.php">Create Course</a> 
            </div> 
        </div>
        <?php endif; ?>
    </nav> 
<body>
    <div class = "container">
    <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>
    <div class="search-bar">
        <form method="GET" action="">
        <input type="hidden" name="route" value="enrollment"> <!-- Add the route parameter -->
        <input type="hidden" name="page" value="<?= $page ?>"> <!-- Preserve the current page -->
            <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>">
            <button type="submit">Search</button>
        </form>
    </div>
    <?php endif; ?>

    <table id="table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Course</th>
                <th>Class</th>
                <th>Department</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($enrollment = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($enrollment['student_name']) ?></td>
                        <td><?= htmlspecialchars($enrollment['student_email']) ?></td>
                        <td><?= htmlspecialchars($enrollment['course_name']) ?></td>
                        <td><?= htmlspecialchars($enrollment['class_name']) ?></td>
                        <td><?= htmlspecialchars($enrollment['department_name']) ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="5">No records found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
            <a href="index.php?route=enrollment&page=<?= $i ?>&search=<?= urlencode($search_query) ?>" class="<?= ($i === $page) ? 'active' : '' ?>"><?= $i ?></a>
            <!--Creates a clickable link for the page numbers, retain the page number and search parameter in the search query, highlight the page number that is active-->
        <?php endfor; ?>
    </div>
    <?php endif; ?>
    </div>
    <?php if (isset($_GET['status'])): ?>
        <div class=alert>
        <?php if ($_GET['status'] === 'enrollment_success'): ?>
            <div class='success'>
            <p>Student enrolled successfully</p>
            <div class=close-button>
            <a href="index.php?route=enrollment">Close</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>