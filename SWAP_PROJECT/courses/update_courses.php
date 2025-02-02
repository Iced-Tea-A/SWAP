<?php
require_once '../auth.php';
require '../db.php';
generateCSRFToken();

// Database connection
$conn =  getDbConnection();

if ($conn->connect_error) {
    // Log the error securely
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed. Please try again later.");
}

// Role-based access control
if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], ['admin', 'faculty'])) {
    die("You do not have access to this page.");
}

// Fetch course details for editing
$course_code = $_GET['course_code'] ?? '';
$sql = "SELECT c.course_code, c.name, c.status_id, c.date_id 
        FROM course c 
        WHERE c.course_code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $course_code);
$stmt->execute();
$result = $stmt->get_result();
$course = $result->fetch_assoc();

// Fetch available course statuses
$status_options = [];
$status_query = "SELECT status_id, name FROM status";
$status_result = $conn->query($status_query);
if ($status_result->num_rows > 0) {
    while ($row = $status_result->fetch_assoc()) {
        $status_options[] = $row; // Store all rows for use in the dropdown
    }
}

// Fetch available date ranges
$date_options = [];
$date_query = "SELECT date_id, start, end FROM date";
$date_result = $conn->query($date_query);
if ($date_result->num_rows > 0) {
    while ($row = $date_result->fetch_assoc()) {
        $date_options[] = $row; // Store all rows for use in the dropdown
    }
}

// Handle the update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("CSRF token validation failed.");
    }

    // Validate and sanitize input data
    $course_code = htmlspecialchars($_POST['course_code'] ?? '');
    $course_name = htmlspecialchars($_POST['course_name'] ?? '');
    $status_id = (int) ($_POST['status_id'] ?? 1); // Default to status "Start" if not selected
    $date_id = (int) ($_POST['date_id'] ?? 1); // Default to the first date range if not selected

    // Input validation: Check if course_code contains only alphanumeric characters, hyphens, or underscores
    if (!preg_match('/^[a-zA-Z0-9-_]+$/', $course_code)) {
        $error_message = "Input Error: Course Code must contain only letters, numbers, and spaces.";
    } elseif (!preg_match('/^[a-zA-Z0-9\s]+$/', $course_name)) { 
        // Validate course name (only letters, numbers, and spaces allowed)
        $error_message = "Input Error: Course Name must contain only letters, numbers, and spaces.";
    } else {
        // Check if the course_code already exists in the database
        $duplicate_check_sql = "SELECT 1 FROM course WHERE course_code = ? AND course_code != ?";
        $stmt_check = $conn->prepare($duplicate_check_sql);
        $stmt_check->bind_param("ss", $course_code, $course['course_code']);
        $stmt_check->execute();
        if ($stmt_check->get_result()->num_rows > 0) {
            $error_message = "Course code already exists. Please choose a different one.";
        } else {
            // Validate other fields
            if (empty($course_name) || empty($status_id) || empty($date_id)) {
                $error_message = "All fields are required.";
            } else {
                // Ensure status_id and date_id are valid (must exist in the database)
                $status_check = $conn->prepare("SELECT 1 FROM status WHERE status_id = ?");
                $status_check->bind_param("i", $status_id);
                $status_check->execute();
                if ($status_check->get_result()->num_rows === 0) {
                    $error_message = "Invalid status selected.";
                }

                $date_check = $conn->prepare("SELECT 1 FROM date WHERE date_id = ?");
                $date_check->bind_param("i", $date_id);
                $date_check->execute();
                if ($date_check->get_result()->num_rows === 0) {
                    $error_message = "Invalid date selected.";
                }
            }
        }
    }

    // If no error, update the course
    if (empty($error_message)) {
        // Update the `course` table
        $update_course_sql = "UPDATE course SET name = ?, status_id = ?, date_id = ?, course_code = ? WHERE course_code = ?";
        if ($stmt = $conn->prepare($update_course_sql)) {
            $stmt->bind_param("sisss", $course_name, $status_id, $date_id, $course_code, $course["course_code"]);
            if ($stmt->execute()) {
                $success_message = "Course updated successfully.";
                header('Location: read_courses.php?msg=' . urlencode($success_message));
                exit();
            } else {
                // Log the error securely
                error_log("Error updating course: " . $conn->error);
                $error_message = "Error updating course. Please try again later.";
            }
            $stmt->close();
        }
    }
}

$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Courses</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<header>
<h1>Edit Courses</h1>
</header>
<?php
require_once 'nav.php';
?>

    <div class="container">
        <h2>Edit Course</h2>
        <div class="formbox">
            <form method="post">
                <!-- CSRF Token Hidden Field -->
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                <label for="course_name">Course Name:</label>
                <input type="text" name="course_name" id="course_name" value="<?php echo htmlspecialchars($course['name']); ?>" required>

                <label for="course_code">Course Code:</label>
                <input type="text" name="course_code" id="course_code" value="<?php echo htmlspecialchars($course['course_code']); ?>" required>

                <label for="date_id">Select Date:</label>
                <select name="date_id" id="date_id" required>
                    <option value="">-- Select Date Range --</option>
                    <?php foreach ($date_options as $date): ?>
                        <option value="<?= $date['date_id'] ?>" <?php if ($date['date_id'] == $course['date_id']) echo 'selected'; ?>>
                            <?= $date['start'] ?> - <?= $date['end'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="status_id">Select Status:</label>
                <select name="status_id" id="status_id" required>
                    <option value="">-- Select Status --</option>
                    <?php foreach ($status_options as $status): ?>
                        <option value="<?= $status['status_id'] ?>" <?php if ($status['status_id'] == $course['status_id']) echo 'selected'; ?>>
                            <?= $status['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Update Course</button>
            </form>
        </div>
    </div>
    <div class="alert">
    <?php if (isset($error_message)): ?>
            <div class="error"><?= $error_message ?></div>
        <?php endif; ?>
    <?php if (isset($success_message)): ?>
        <div class="success"><?= $success_message ?></div>
    <?php endif; ?>
    </div>
</body>
</html>
