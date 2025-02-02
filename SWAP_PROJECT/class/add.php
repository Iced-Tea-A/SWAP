<?php
require_once '../auth.php';
include '../db.php'; // Database connection (MySQLi, using $mysqli)
$mysqli = getDbConnection();

// --- CSRF Token Generation (if not already set) ---
generateCSRFToken();

// --- CSRF Token Validation ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        header("Location: modify.php?error=" . urlencode("Invalid CSRF token."));
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: modify.php?error=" . urlencode("Invalid request method."));
    exit;
}

// CASE 1: Adding a new class name only.
if (isset($_POST['classname']) && !isset($_POST['classtype']) && !isset($_POST['courseCode'])) {
    $classname = trim($_POST['classname']);
    
    // Check if the class already exists.
    $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM classname WHERE name = ?");
    if (!$stmt) {
        header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
        exit;
    }
    $stmt->bind_param("s", $classname);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    if ($count > 0) {
        header("Location: modify.php?error=" . urlencode("Class name already exists."));
        exit;
    }
    
    // Insert new class name.
    $stmt = $mysqli->prepare("INSERT INTO classname (name) VALUES (?)");
    if (!$stmt) {
        header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
        exit;
    }
    $stmt->bind_param("s", $classname);
    if ($stmt->execute()) {
        header("Location: modify.php?message=" . urlencode("Class name added successfully"));
        exit;
    } else {
        header("Location: modify.php?error=" . urlencode("Failed to add class name."));
        exit;
    }
    $stmt->close();
}

// CASE 2: Adding a full class (requires classname, classtype, and courseCode).
if (!isset($_POST['classname'], $_POST['classtype'], $_POST['courseCode'])) {
    header("Location: modify.php?error=" . urlencode("Missing parameters."));
    exit;
}

$classname = trim($_POST['classname']);
$classtype = trim($_POST['classtype']);
$courseCode = trim($_POST['courseCode']);

// Get the classname_id.
$stmt = $mysqli->prepare("SELECT classname_id FROM classname WHERE name = ?");
if (!$stmt) {
    header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
    exit;
}
$stmt->bind_param("s", $classname);
$stmt->execute();
$stmt->bind_result($classnameId);
$stmt->fetch();
$stmt->close();

// Get the classtype_id.
$stmt = $mysqli->prepare("SELECT classtype_id FROM classtype WHERE type = ?");
if (!$stmt) {
    header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
    exit;
}
$stmt->bind_param("s", $classtype);
$stmt->execute();
$stmt->bind_result($classtypeId);
$stmt->fetch();
$stmt->close();

if (!$classnameId || !$classtypeId) {
    header("Location: modify.php?error=" . urlencode("Invalid class name or type."));
    exit;
}

// Check for duplicate entry in course_class.
$stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM course_class WHERE classname_id = ? AND type_id = ? AND course_code = ?");
if (!$stmt) {
    header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
    exit;
}
$stmt->bind_param("iis", $classnameId, $classtypeId, $courseCode);
$stmt->execute();
$stmt->bind_result($duplicateCount);
$stmt->fetch();
$stmt->close();

if ($duplicateCount > 0) {
    header("Location: modify.php?error=" . urlencode("Duplicate entry exists."));
    exit;
}

// Insert the full class record.
$stmt = $mysqli->prepare("INSERT INTO course_class (classname_id, type_id, course_code) VALUES (?, ?, ?)");
if (!$stmt) {
    header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
    exit;
}
$stmt->bind_param("iis", $classnameId, $classtypeId, $courseCode);
if ($stmt->execute()) {
    header("Location: modify.php?message=" . urlencode("Class added successfully"));
    exit;
} else {
    header("Location: modify.php?error=" . urlencode("Failed to insert class."));
    exit;
}
$stmt->close();
?>
