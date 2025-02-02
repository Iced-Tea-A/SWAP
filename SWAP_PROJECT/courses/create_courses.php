<?php
require_once '../auth.php';
require '../db.php'; // This should set up $mysqli

// Ensure session security by regenerating session ID
if (!isset($_SESSION['user_role'])) {
    die("Access Denied: Please log in first.");
}

// Role-based access control
if (!in_array($_SESSION['user_role'], ['admin', 'faculty'])) {
    die("You do not have access to this page.");
}

// Database connection connected to a php file to hide database name.
$conn =  getDbConnection();

if ($conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error); // Log error
    die("Connection failed. Please try again later.");
}

// CSRF Token Generation (if not already set)
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch existing dates for the dropdown using JOIN
$date_options = [];
$date_query = "SELECT date.date_id, date.start, date.end FROM date";
$date_result = $conn->query($date_query);

if ($date_result->num_rows > 0) {
    while ($row = $date_result->fetch_assoc()) {
        $date_options[] = $row; // Store all fropdown rows
    }
}

// Fetch course status for dropdown
$status_options = [];
$status_query = "SELECT status_id, name FROM status";
$status_result = $conn->query($status_query);

if ($status_result->num_rows > 0) {
    while ($row = $status_result->fetch_assoc()) {
        $status_options[] = $row; // Store all rows for use in the dropdown
    }
}

// Initialize message variables
$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "CSRF token validation failed.";
    } else {
        $course_code = htmlspecialchars($_POST['course_code'] ?? '');
        $name = htmlspecialchars($_POST['name'] ?? '');
        $date_id = (int) $_POST['date_id'] ?? 0;
        $status_id = (int) $_POST['status_id'] ?? 1; // Set Defualt status to id 1 = Start

        // Validate input
        if (empty($course_code) || empty($name) || empty($date_id) || empty($status_id)) { // if some empty are empty = prompt error message
            $error_message = "All fields are required.";
        } elseif (!preg_match('/^[A-Za-z1-9]+$/', $course_code)) { // Validate course_code: Only letters (A-Z, a-z) and numbers (1-9)
            $error_message = "Input Error: Course Code must contain only letters, numbers, and spaces.";
        }elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $name)) { 
            // Validate course name (only letters, numbers, and spaces allowed)
            $error_message = "Input Error: Course Name must contain only letters, numbers, and spaces.";
        } else {
            // Check for duplicates
            $duplicate_query = "SELECT * FROM course WHERE course_code = ? OR name = ?"; // get the value course_code || name from course table.
            $stmt = $conn->prepare($duplicate_query);
            $stmt->bind_param("ss", $course_code, $name);
            $stmt->execute();
            $duplicate_result = $stmt->get_result();

            if ($duplicate_result->num_rows > 0) {
                $error_message = "Error Input: similar course name or course code already exists.";
            } else {
                // Use prepared statements to prevent SQL injection
                $stmt = $conn->prepare("INSERT INTO course (course_code, name, date_id, status_id) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssii", $course_code, $name, $date_id, $status_id);

                if ($stmt->execute()) {
                    $success_message = "New course created successfully.";
                } else {
                    error_log("SQL Error: " . $stmt->error); // Log the error
                    $error_message = "An error occurred. Please try again later.";
                }
                $stmt->close();
            }
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Courses</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<header>
<h1>Create Courses</h1>
</header>
<?php
require_once 'nav.php';
?>
    <div>
        <div class="formbox">
            <form method="post">
                <label for="name">Course Name:</label>
                <input type="text" id="name" name="name" required>

                <label for="course_code">Course Code:</label>
                <input type="text" id="course_code" name="course_code" required>

                <label for="date_id">Select Date:</label>
                <select name="date_id" id="date_id" required>
                    <option value="">-- Select Date Range --</option>
                    <?php foreach ($date_options as $date): ?>
                        <option value="<?= htmlspecialchars($date['date_id']) ?>">
                            <?= htmlspecialchars($date['start']) ?> - <?= htmlspecialchars($date['end']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="status_id">Select Status:</label>
                <select name="status_id" id="status_id" required>
                    <option value="">-- Select Status --</option>
                    <?php foreach ($status_options as $status): ?>
                        <option value="<?= htmlspecialchars($status['status_id']) ?>">
                            <?= htmlspecialchars($status['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <!-- Hide CSRF token -->
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <button type="submit">Create Course</button>
            </form>
        </div>
    </div>
<!-- Display success and error messages -->
    <div class="alert">
        <?php if (!empty($error_message)): ?>
        <div class="error">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success_message)): ?>
        <div class="success">
            <?= htmlspecialchars($success_message) ?>
        </div>
    <?php endif; ?>
    </div>
</body>
</html>
