<?php
// Start session and check user role
if ($_SESSION['user_role'] !== 'admin') {
    die("Access denied. Only admins can delete students.");
}

// Database connection details
include('../sql/db.php');

$conn = getDbConnection();

// Check if the ID (metric_number) is provided in the URL
if (isset($_GET['metric_number'])) {
    $metric_number = $_GET['metric_number'];

    // Prepare the delete query
    $delete_query = $conn->prepare("DELETE FROM student WHERE metric_number = ?");
    $delete_query->bind_param("s", $metric_number);

    // Execute the delete query
    if ($delete_query->execute()) {
        // Successfully deleted, redirect to student records page
        header("Location: DisplayStudents.php?status=delete_success");
        exit;
    } else {
        // Error during delete
        header("Location: DisplayStudents.php?status=delete_error");
        exit;
    }

    // Close the query
    $delete_query->close();
} else {
    // If no ID is provided, show an error
    echo "No student ID provided.";
}

// Close the database connection
$conn->close();
?>