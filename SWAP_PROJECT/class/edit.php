<?php
require_once '../auth.php';
include '../db.php'; // Database connection (MySQLi, using $mysqli)
$mysqli = getDbConnection();

generateCSRFToken();

$error_message = '';

// Ensure the request method is POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error_message = "Invalid request method.";
}

// Validate CSRF token.
if (empty($error_message)) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error_message = "Invalid CSRF token.";
    }
}

// Validate required POST parameters.
if (empty($error_message)) {
    if (!isset($_POST['id'], $_POST['newClassname'], $_POST['newClasstype'], $_POST['newCourseCode'])) {
        $error_message = "Missing parameters.";
    }
}

if (empty($error_message)) {
    $id = (int) $_POST['id'];
    $newClassname = trim($_POST['newClassname']);
    $newClasstype = trim($_POST['newClasstype']);
    $newCourseCode = trim($_POST['newCourseCode']);

    // Retrieve the new class ID.
    $stmt = $mysqli->prepare("SELECT classname_id FROM classname WHERE name = ?");
    if (!$stmt) {
        $error_message = "Prepare failed: " . $mysqli->error;
    } else {
        $stmt->bind_param("s", $newClassname);
        $stmt->execute();
        $stmt->bind_result($classnameId);
        $stmt->fetch();
        $stmt->close();
    }

    // Retrieve the new class type ID.
    if (empty($error_message)) {
        $stmt = $mysqli->prepare("SELECT classtype_id FROM classtype WHERE type = ?");
        if (!$stmt) {
            $error_message = "Prepare failed: " . $mysqli->error;
        } else {
            $stmt->bind_param("s", $newClasstype);
            $stmt->execute();
            $stmt->bind_result($classtypeId);
            $stmt->fetch();
            $stmt->close();
        }
    }

    if (empty($error_message)) {
        if (!$classnameId || !$classtypeId) {
            $error_message = "Invalid class name or type.";
        }
    }

    // Check for duplicate entry (excluding the current record).
    if (empty($error_message)) {
        $stmt = $mysqli->prepare("SELECT COUNT(*) as cnt FROM course_class WHERE classname_id = ? AND type_id = ? AND course_code = ? AND course_class_id != ?");
        if (!$stmt) {
            $error_message = "Prepare failed: " . $mysqli->error;
        } else {
            $stmt->bind_param("iisi", $classnameId, $classtypeId, $newCourseCode, $id);
            $stmt->execute();
            $stmt->bind_result($duplicateCount);
            $stmt->fetch();
            $stmt->close();
            if ($duplicateCount > 0) {
                $error_message = "Duplicate entry exists.";
            }
        }
    }

    // Update the record.
    if (empty($error_message)) {
        $stmt = $mysqli->prepare("UPDATE course_class SET classname_id = ?, type_id = ?, course_code = ? WHERE course_class_id = ?");
        if (!$stmt) {
            $error_message = "Prepare failed: " . $mysqli->error;
        } else {
            $stmt->bind_param("iisi", $classnameId, $classtypeId, $newCourseCode, $id);
            if ($stmt->execute()) {
                header("Location: modify.php?message=" . urlencode("Class updated successfully"));
                exit;
            } else {
                $error_message = "Failed to update class.";
            }
            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class - Error</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="container">
        <h2>Edit Class</h2>
        <?php if (!empty($error_message)): ?>
            <div class="alert"><?= htmlspecialchars($error_message) ?></div>
            <button type="button" onclick="window.location.href='modify.php'">Back to Modify</button>
        <?php endif; ?>
    </div>
</body>
</html>
