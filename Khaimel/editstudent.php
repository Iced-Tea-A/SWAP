<?php
// Start session and check user role
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty')) {
    die("Access denied. Only admins can edit students."); //Session start and ensure that current user role is either Faculty or Admin
}
// Database connection details
include('../sql/db.php');

$conn = getDbConnection();

// Fetch student details if metric_number is provided
if (isset($_GET['metric_number'])) {
    $metric_number = $_GET['metric_number'];

    // Fetch student data
    $student_query = $conn->prepare("SELECT * FROM student WHERE metric_number = ?");
    $student_query->bind_param("s", $metric_number);
    $student_query->execute();
    $result = $student_query->get_result();
    $student = $result->fetch_assoc();

    if (!$student) {
        die("Student not found.");
    }

    // Handle form submission for updating student record
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = htmlspecialchars(trim($_POST['name']));
        $email = htmlspecialchars(trim($_POST['email']));
        $mobile_number = htmlspecialchars(trim($_POST['mobile_number']));
        $metric_number = htmlspecialchars(trim($_POST['metric_number']));
        $department_id = htmlspecialchars((int) $_POST['department_id']);
        $error = '';

        // Format input as needed
        $name = strtolower($name);
        $name = ucwords(strtolower($name));
        $email = strtolower($email);
        $metric_number = strtoupper($metric_number);
        //Use regex to filter form input
        //Filter Name: Only letters, spaces, hyphens, and apostrophes allowed
        if (!preg_match("/^[a-zA-Z\s'-]+$/", $name)) {
            $error = "Invalid name. Only letters, spaces, hyphens, and apostrophes are allowed.";
        }

        //Filter Email: Use filter_var to validate email format
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.(com)$/', $email)) {
            $error = "Invalid email format.";
        }

        //Filter Mobile Number: Only numbers, spaces, plus, and dashes are allowed
        elseif (!preg_match("/^\d{8}$/", $mobile_number)) {
            $error = "Mobile number must contain exactly 8 numbers.";
        }

        //Filter Metric Number: Ensure it's numeric and within length constraints
        elseif (!preg_match("/^\d{7}[A-Za-z]$/", $metric_number)) {
            $error = "Invalid metric number. It should be 7 digits followed by a single alphabet letter.";
        }

        // Check for duplicate name, email, or mobile number
        if (empty($error)) {
            // Check for duplicate name
            $check_name_query = $conn->prepare("SELECT COUNT(*) FROM student WHERE name = ? AND metric_number != ?");
            $check_name_query->bind_param("ss", $name, $student['metric_number']);
            $check_name_query->execute();
            $check_name_query->bind_result($name_count);
            $check_name_query->fetch();
            $check_name_query->close();

            // Check for duplicate email
            $check_email_query = $conn->prepare("SELECT COUNT(*) FROM student WHERE student_email = ? AND metric_number != ?");
            $check_email_query->bind_param("ss", $email, $student['metric_number']);
            $check_email_query->execute();
            $check_email_query->bind_result($email_count);
            $check_email_query->fetch();
            $check_email_query->close();

            // Check for duplicate mobile number
            $check_mobile_query = $conn->prepare("SELECT COUNT(*) FROM student WHERE mobile_number = ? AND metric_number != ?");
            $check_mobile_query->bind_param("ss", $mobile_number, $student['metric_number']);
            $check_mobile_query->execute();
            $check_mobile_query->bind_result($mobile_count);
            $check_mobile_query->fetch();
            $check_mobile_query->close();

            // Check for duplicate metric number
            $check_metric_query = $conn->prepare("SELECT COUNT(*) FROM student WHERE metric_number = ? AND metric_number != ?");
            $check_metric_query->bind_param("ss", $metric_number, $student['metric_number']);
            $check_metric_query->execute();
            $check_metric_query->bind_result($metric_count);
            $check_metric_query->fetch();
            $check_metric_query->close();

            if ($name_count > 0) {
                $error = "A student with this name already exists.";
            } elseif ($email_count > 0) {
                $error = "A student with this email address already exists.";
            } elseif ($mobile_count > 0) {
                $error = "A student with this mobile number already exists.";
            } elseif ($metric_count > 0) {
                $error = "A student with this metric number already exists.";
            }
        }

        // If no errors, update the student record
        if (empty($error)) {
            if (empty($name) || empty($email) || empty($mobile_number) || empty($metric_number) || empty($department_id)) {
                $error = "All fields are required.";
            } else {
                // Update student record
                $update_stmt = $conn->prepare("UPDATE student SET name = ?, student_email = ?, mobile_number = ?, department_id = ?, metric_number = ? WHERE metric_number = ?");
                $update_stmt->bind_param("sssiss", $name, $email, $mobile_number, $department_id, $metric_number, $student['metric_number']);
                if ($update_stmt->execute()) {
                header('Location: index.php?route=students&status=edit_success');
                } else {
                    $error_message="$update_stmt->error";
                }
                $update_stmt->close();
            }
        } else {
            // If there is an error, update error message variable
            $error_message="$error";
        }
    }
} else {
    die("No student ID provided.");
}

// Fetch departments for department dropdown
$department_query = $conn->query("SELECT department_id, name FROM department");
$departments = $department_query->fetch_all(MYSQLI_ASSOC); //Fetch all the results for department rows

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
</head>
<header> 
    <h1>Edit Student</h1> 
</header>
<div class = "containertext">        
<?php include("nav.php") ?>
<body>

    <div class="formbox">
        <form method="POST" action="">

            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?= isset($name) ? htmlspecialchars($name) : htmlspecialchars($student['name']) ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?= isset($email) ? htmlspecialchars($email) : htmlspecialchars($student['student_email']) ?>" required>

            <label for="mobile_number">Mobile Number:</label>
            <input type="text" id="mobile_number" name="mobile_number" value="<?= isset($mobile_number) ? htmlspecialchars($mobile_number) : htmlspecialchars($student['mobile_number']) ?>" required>

            <label for="metric_number">Metric Number:</label>
            <input type="text" id="metric_number" name="metric_number" value="<?= isset($metric_number) ? htmlspecialchars($metric_number) : htmlspecialchars($student['metric_number']) ?>" required>

            <label for="department_id">Department:</label>
            <select id="department_id" name="department_id" required>
                <option value="" disabled>Select Department</option>
                <?php foreach ($departments as $department): ?>
                    <option value="<?= $department['department_id'] ?>" <?= $department['department_id'] == $student['department_id'] ? 'selected' : '' ?>><?= htmlspecialchars($department['name']) ?></option>
                <?php endforeach; ?>
            </select><br><br>

            <button type="submit">Update</button>
        </form>
                </div>
<!-- Display error if input error detected -->
<?php if (isset($error_message)): ?>
    <div class="alert error">
            <p><?= $error_message ?></p>
            <div class=close-button>
            <a href="index.php?route=edit&metric_number=<?= htmlspecialchars($student['metric_number']) ?>">Close</a>
            </div>
<?php endif; ?>
</body>
</html>