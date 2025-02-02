<?php
require_once '../auth.php';
require '../db.php'; // This should set up $mysqli
// error handling functions for every error promtp given
function handleError($message) {
    echo "<p>An error occurred: " . htmlspecialchars($message) . "</p>";
    error_log($message); // Log the error message for debugging
}


$conn =  getDbConnection();

if (!$conn) { //error handling for failing to connect to database
    handleError("Database connection failed.");
    exit();
}

// Role-based access control allowing only Admin and Faculty to entrer this page.
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'faculty'])) {
    //show error when Student tries to enter this page.
    handleError("You do not have access to this page.");
    exit();
}

// Handle course deletion securely with status_id check
// allowing only Admin to be able to use delete function
// delete_user is an id assign to the button delete below
if (isset($_GET['delete_user']) && $_SESSION['user_role'] === 'admin') {
    $delete_course = $_GET['delete_user'];

    // getting query for status_id from the course table
    $status_query = "SELECT status_id FROM course WHERE course_code = ?";
    $status_stmt = $conn->prepare($status_query);

    if (!$status_stmt) {
        $_SESSION['error_message'] = "Failed to prepare the status check query."; //error message if fail to get query.
    } else {
        $status_stmt->bind_param('s', $delete_course);
        if ($status_stmt->execute()) {
            $status_result = $status_stmt->get_result();
            if ($status_result->num_rows > 0) {
                $status_row = $status_result->fetch_assoc();
                if ($status_row['status_id'] != 4) {
                    // If status_id is 4, show an error message
                    $_SESSION['error_message'] = "unable to delete course.";
                } else {
                    // Proceed with deletion if status_id is not 4
                    $delete_query = "DELETE FROM course WHERE course_code = ?";
                    $stmt = $conn->prepare($delete_query);

                    if (!$stmt) {
                        $_SESSION['error_message'] = "Failed to prepare the delete query.";
                    } else {
                        $stmt->bind_param('s', $delete_course);
                        if ($stmt->execute()) {
                            $_SESSION['success_message'] = "Course delete successfully.";
                        } else {
                            $_SESSION['error_message'] = "Error executing delete query: " . $stmt->error;
                        }
                        $stmt->close();
                    }
                }
            } else {
                $_SESSION['error_message'] = "No course found with the specified course code.";
            }
        } else {
            $_SESSION['error_message'] = "Error executing status check query.";
        }
        $status_stmt->close();
    }
    // Redirect to the same page to show the messages
    header('Location: ' . htmlspecialchars($_SERVER['PHP_SELF']));
    exit();
}

// Fetch courses securely, using join from date [table=date_id] and [status=status_id]
$course_query = "SELECT c.course_code, c.name as course_name, d.start, d.end, s.name as status_name
                 FROM course AS c
                 JOIN date AS d ON c.date_id = d.date_id
                 JOIN status AS s ON c.status_id = s.status_id";

$stmt = $conn->prepare($course_query);

if (!$stmt) {
    handleError("Failed to prepare the fetch query: " . $conn->error);
} else {
    $stmt->execute();
    $course_result = $stmt->get_result();

    if (!$course_result) {
        handleError("Error fetching courses: " . $stmt->error);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Display Courses</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<header>
<h1>View Courses</h1>
</header>
</html>
<?php
require_once 'nav.php';
?>
<body>
<!-- Main Content -->
<div class="container">
    <table border="1" id="table">
        <thead>
            <tr>
                <th>Course Code</th>
                <th>Course Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <?php if ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'faculty'): ?>
                    <th>Actions</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if ($course_result && $course_result->num_rows > 0): ?>
                <?php while ($row = $course_result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['course_code']) ?></td>
                        <td><?= htmlspecialchars($row['course_name']) ?></td>
                        <td><?= htmlspecialchars($row['start']) ?></td>
                        <td><?= htmlspecialchars($row['end']) ?></td>
                        <td><?= htmlspecialchars($row['status_name']) ?></td>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <td>
                                <a href="update_courses.php?course_code=<?= htmlspecialchars($row['course_code']) ?>">Edit</a> |
                                <a href="?delete_user=<?= htmlspecialchars($row['course_code']) ?>">Delete</a>
                            </td>
                        <?php elseif ($_SESSION['user_role'] === 'faculty'): ?>
                            <td>
                                <a href="update_courses.php?course_code=<?= htmlspecialchars($row['course_code']) ?>">Edit</a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6">No courses found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

    <!-- Display success or error messages after the form -->

    <div class="alert">
    <?php

    if (isset($_SESSION['error_message'])) {
            echo '<p class="error">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    }
    unset($_SESSION['error_message']);

    if (isset($_SESSION['success_message'])) {
            echo '<p class="success">' . htmlspecialchars($_SESSION['success_message']) . '</p>';
    }
    unset($_SESSION['success_message']);
    ?>
</div>

</body>
</html>
