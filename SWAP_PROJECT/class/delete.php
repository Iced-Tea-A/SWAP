<?php
require_once '../auth.php';
include '../db.php'; // Database connection (MySQLi, using $mysqli)
$mysqli = getDbConnection();

generateCSRFToken();

// --- CSRF Token Validation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: modify.php?error=" . urlencode("Invalid CSRF token."));
        exit;
    }
} else {
    header("Location: modify.php?error=" . urlencode("Invalid request method."));
    exit;
}

// If a bulk deletion flag is set, delete all empty classes from the classname table
if (isset($_POST['delete_empty']) && $_POST['delete_empty'] == 1) {
    // Delete from classname where the classname_id is not found in course_class.
    // This uses a LEFT JOIN to find classname records without matching course_class records.
    $query = "DELETE c
              FROM classname c
              LEFT JOIN course_class cc ON c.classname_id = cc.classname_id
              WHERE cc.classname_id IS NULL";
    
    $stmt = $mysqli->prepare($query);
    if (!$stmt) {
        header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
        exit;
    }
    if ($stmt->execute()) {
        header("Location: modify.php?message=" . urlencode("Empty classes deleted successfully"));
        exit;
    } else {
        header("Location: modify.php?error=" . urlencode("Failed to delete empty classes."));
        exit;
    }
    $stmt->close();
}else {
    // Otherwise, assume deletion of a single record from course_class by its id.
    $id = $_POST['id'] ?? null;
    if (!$id) {
        header("Location: modify.php?error=" . urlencode("Invalid deletion request."));
        exit;
    }
    $stmt = $mysqli->prepare("DELETE FROM course_class WHERE course_class_id = ?");
    if (!$stmt) {
        header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
        exit;
    }
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: modify.php?message=" . urlencode("Deleted successfully"));
        exit;
    } else {
        header("Location: modify.php?error=" . urlencode("Failed to delete."));
        exit;
    }
    $stmt->close();
}
?>
