<?php
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty' && $_SESSION['user_role'] !== 'student') {
    session_unset(); //Remove session variables, destroy the session and bring user back to login page if unauthorised
    session_destroy();
    header("Location: ../login.php");
    exit();
    die("Access denied. Only admins can edit students."); //Session start and ensure that current user role is either Faculty or Admin
}


//Declare CSRF token as a variable to compare with the current session's token
$csrf_token = $_SESSION['csrf_token'];

// Pagination
$students_per_page = 10; //Set the number of students to display on 1 page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1; // Get current page from URL, default to 1 if not present
$offset = ($page - 1) * $students_per_page;  // Calculate the offset based on the page number   

// Search
$search_query = isset($_GET['search']) ? trim($_GET['search']) : ''; //Check if there is a search parameter in the url and trim the value of the search parameter
$filtered_search_query = preg_replace("/[^a-zA-Z\s'-]/", '', $search_query); 

// Prepare SQL to count the total number of students that match the search query
$total_students_sql = "SELECT COUNT(*) AS total FROM student s WHERE ? = '' OR s.name LIKE ? OR s.student_email LIKE ?"; //Get the total number of rows that match the query
$student_query = $conn->prepare($total_students_sql);  //Prepared statement to avoid SQL injection
$search_param = "%$filtered_search_query%"; //Insert wildcard to match query to any part of student's name or email
$student_query->bind_param('sss', $filtered_search_query, $search_param, $search_param);   
$student_query->execute();
$total_result = $student_query->get_result();
$total_students = $total_result->fetch_assoc()['total'];  //Retrieves the total number of students
$total_pages = ceil($total_students / $students_per_page); //Round up the total number of pages needed
$student_query->close();

//Where condition for student roles to display only the student's records
$where_condition = "";
if ($_SESSION['user_role'] === 'student') {
    // $where_condition = "AND (? = '' OR s.student_email = ?";
    $where_condition = "AND s.student_email = ?";
}

// Prepare SQL to fetch student records based on search query and pagination
$sql = "SELECT s.name, s.student_email, s.mobile_number, s.metric_number, d.name AS department_name
        FROM student s
        INNER JOIN department d ON s.department_id = d.department_id
        WHERE (? = '' OR s.name LIKE ? OR s.student_email LIKE ?) 
        $where_condition
        LIMIT ?, ?";   //Limit to extract the student based on the page number and the number of students needed per page
$student_query = $conn->prepare($sql);
if ($_SESSION['user_role'] === 'student') {
    // If the user is a student, bind their email to the query
    $student_query->bind_param('ssssii', $search_query, $search_param, $search_param, $_SESSION['user_email'], $offset, $students_per_page);
} else {
    // If user is admin/faculty, no need for filtering by student email
    $student_query->bind_param('sssii', $search_query, $search_param, $search_param, $offset, $students_per_page);
}
$student_query->execute();
$result = $student_query->get_result();
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Records</title>
</head>
<header> 
        <h1>Student Records</h1> 
    </header>
    <div class = "containertext">        
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
                <a href="index.php?route=students">Student Records</a>  <!-- Only display Students: Records/Enrollment if user is student -->
                <a href="index.php?route=enrollment">Student Enrollment</a>
                <?php if ($_SESSION['user_role'] !== 'student'): ?>
                <a href="index.php?route=add">Add Student</a>
                <?php endif; ?>
            </div> 
        </div> 
        <div class="dropdown"> 
            <button>Classes</button> 
            <div class="dropdown-content"> 
                <a href="../class/view.php">View Class</a>
                <?php if ($_SESSION['user_role'] !== 'student'): ?>
                <a href="../class/modify.php">Modify Class</a>
                <?php endif; ?>
            </div> 
        </div>
        <div class="dropdown"> 
        <?php if ($_SESSION['user_role'] !== 'student'): ?>
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
<body>
    <div class="container">
    <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>
        <div class="search-bar">
            <form method="GET">
            <input type="hidden" name="route" value="students"> <!-- Maintain route and current page number when user searches for a record-->
            <input type="hidden" name="page" value="<?= $page ?>">
            <input type="text" name="search" placeholder="Search by name or email..." value="<?= htmlspecialchars($search_query) ?>"> <!--Convert special characters into HTML entity to avoid XSS as it will treat it as plain text-->
            <button type="submit">Search</button>
            </form>
        </div>
    <?php endif ?>
        <table id="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Metric Number</th>
                    <th>Department</th>
                    <?php if ($_SESSION['user_role'] !== 'student'): ?> <!-- Only display actions column if user is not student role -->
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>  <!--Display students if there are 1 or more rows, display no records found if 0 rows-->
                    <?php while ($student = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($student['name']) ?></td>
                            <td><?= htmlspecialchars($student['student_email']) ?></td>
                            <td><?= htmlspecialchars($student['mobile_number']) ?></td>
                            <td><?= htmlspecialchars($student['metric_number']) ?></td>
                            <td><?= htmlspecialchars($student['department_name']) ?></td>
                                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>
                                    <td>  <a href="storemetric.php?action=edit&metric_number=<?= htmlspecialchars($student['metric_number']) ?>&csrf_token=<?= $csrf_token ?>">Edit</a> | 
                                    <a href="storemetric.php?action=assign&metric_number=<?= htmlspecialchars($student['metric_number']) ?>&csrf_token=<?= $csrf_token ?>">Assign</a>
                                    <?php if ($_SESSION['user_role'] === 'admin'): ?>
|                                       <a href="storemetric.php?action=delete&metric_number=<?= htmlspecialchars($student['metric_number']) ?>&csrf_token=<?= $csrf_token ?>">Delete</a>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6">No records found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>            
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="index.php?route=students&page=<?= $i ?>&search=<?= htmlspecialchars($search_query) ?>" class="<?= ($i === $page) ? 'active' : '' ?>"><?= $i ?></a> 
                    <!--Creates a clickable link for the page numbers, retain the page number and search parameter in the search query, highlight the page number that is active-->
                <?php endfor; ?>
            </div>
                <div class="add-button">
                <a href="index.php?route=add">Add Student</a>
            </div>
        </div>
        <?php endif ?>
    <?php if (isset($_GET['status'])): ?>
        <div class="alert">
            <?php if ($_GET['status'] === 'delete_success'): ?>
                <div class='success'>
                <p>Student deleted successfully!</p>
                <div class=close-button>
                <a href="index.php?route=students">Close</a>
                </div>
            <?php elseif ($_GET['status'] ==='delete_fail'): ?>
                <div class='error'>
                <p>Student was not deleted.</p>
                <div class=close-button>
                <a href="index.php?route=students">Close</a>
                </div>
            <?php elseif ($_GET['status'] ==='add_success'): ?>
                <div class='success'>
                <p>Student successfully added!</p>
                <div class=close-button>
                <a href="index.php?route=students">Close</a>
                </div>
            <?php elseif ($_GET['status'] ==='edit_success'): ?>
                <div class='success'>
                <p>Student successfully updated!</p>
                <div class=close-button>
                <a href="index.php?route=students">Close</a>
                </div>
            <?php endif; ?> <!--Show error/success status at the bottom-->
            </div>
    <?php endif; ?>
</body>
</html>