<?php
// Start session and check user role
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'faculty') {
    die("Access denied. Only admins can edit students.");
}
// Include the database connection file
include('../sql/db.php');

$conn = getDbConnection();

// Initialize variables
$courses = [];
$classes = [];
$studentName = '';
$metricNumber = '';
$selectedCourse = null;
$selectedClass = null;


if (isset($_GET['metric_number'])) {           // Get the Metric Number from URL
    $metricNumber = $_GET['metric_number'];    
    $query = "SELECT name, student_email FROM `Student` WHERE metric_number = '$metricNumber'"; // Fetch student name based on the metric number
    $result = mysqli_query($conn, $query);
    if ($row = mysqli_fetch_assoc($result)) {   //Check if a row is found, if assign the values to student_name and student_email 
        $studentName = $row['name'];      
        $studentEmail = $row['student_email'];
    } else {
        die("Student not found.");     // If not found, kill the connection
    }
} else {
    die("Metric number not provided."); //If no Metric Number, kill the connection
}

$result = mysqli_query($conn, "SELECT * FROM `Course`"); //Fetch all courses
while ($row = mysqli_fetch_assoc($result)) {
    $courses[] = $row;
}

$studentEnrolledInCourse = false; //Initialise duplicate enrollment variable

//SQL to check if student is already enrolled into a selected course
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedCourse = $_POST['course_code'];

    $checkEnrollmentQuery = "SELECT e.*   
    FROM `Enrollment` e
    JOIN `Course_Class` cc ON e.course_class_id = cc.course_class_id
    JOIN `Course` c ON cc.course_code = c.course_code
    WHERE e.student_email = '$studentEmail' AND c.course_code = '$selectedCourse'";   //Check if student is already assigned to the chosen course in the database
$enrollmentResult = mysqli_query($conn, $checkEnrollmentQuery);

if (mysqli_num_rows($enrollmentResult) > 0) {       //Implementing IF function to prompt alert if there is a duplicate course, else proceed with choosing a class
    $selectedCourse = null;
    $error_message="This student is already enrolled in this course. Please select another one.";

} else { //If there is the course is not enrolled to the student yet, proceed with displaying the classes available
    $query = "SELECT cc.course_class_id, cn.name AS class_name 
              FROM `Course_Class` cc
              JOIN `ClassName` cn ON cc.classname_id = cn.classname_id
              WHERE cc.course_code = '$selectedCourse'"; //Get classes based on the course chosen
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $classes[] = $row;   //Appending the row array to the classes array
    }
}
//Insert new enrollment record
if (isset($_POST['course_class_id'])) {  //After selecting a class, submit it to the enrollment table
    $selectedClass = $_POST['course_class_id'];
    $stmt = $conn->prepare("INSERT INTO `Enrollment` (`student_email`, `course_class_id`) VALUES (?, ?)");
    $stmt->bind_param("si", $studentEmail, $selectedClass);
    // Insert enrollment data
    $query = "INSERT INTO `Enrollment` (`student_email`, `course_class_id`) 
                VALUES ('$studentEmail', '$selectedClass')";
    if ($stmt->execute()){
        header("Location: index.php?route=enrollment&status=enrollment_success"); 
        exit;
    } else {
    $error_message = "Error: " . $stmt->error;
    }      
    $stmt->close(); // Close the prepared statement
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="stylesheet" href="../style/style.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Student</title>
</head>
<header>
    <h1>Assign Student</h1>
</header>
<?php include("nav.php") ?>
<body>
<div class="formbox">
<form method="POST">
<!-- Autofill name column with the student name that was assigned and readonly -->
    <label for= "name">Name:</label> 
    <input type="text" id="name" name="name" value="<?= htmlspecialchars($studentName) ?>" readonly required> 


<?php if ($selectedCourse === null): ?>
    <label for="course_code">Select Course:</label>
    <select name="course_code" id="course_code" required>
        <option value="">Select a Course</option>
        <?php foreach ($courses as $course): ?>
            <option value="<?= htmlspecialchars($course['course_code']) ?>"><?= htmlspecialchars($course['name']) ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<?php if ($selectedCourse !== null && !empty($classes)): ?>
    <label for="course_class_id">Select Class:</label>
    <select name="course_class_id" id="course_class_id" required>
        <option value="">Select a Class</option>
        <?php foreach ($classes as $class): ?>
            <option value="<?= $class['course_class_id'] ?>"><?= htmlspecialchars($class['class_name']) ?></option>
        <?php endforeach; ?>
    </select>
<?php endif; ?>

<button type="submit">Assign</button>

</form>
</div>
<?php if (isset($error_message)): ?>
    <div class="alert error"><?= $error_message ?></div>
<?php endif; ?>
</body>
</html>