<?php
require_once '../auth.php';
include '../db.php'; // Database connection (MySQLi, using $mysqli)
$mysqli = getDbConnection();

// Generate CSRF token if not already set.
generateCSRFToken();
if (!isset($_GET['id'])) {
    header("Location: modify.php?error=" . urlencode("Invalid request."));
    exit;
}

if (isset($_GET['id'])) {
    $decodedId = base64_decode($_GET['id']);
    if (!is_numeric($decodedId)) {
        die("Invalid ID.");
    }
}
    $id = (int) $decodedId;

// Fetch the record details using the unique id.
$stmt = $mysqli->prepare("
    SELECT cc.course_class_id as id, c.name AS classname, t.type AS classtype, 
           cc.course_code, co.name AS course_name 
    FROM course_class cc 
    JOIN classname c ON cc.classname_id = c.classname_id 
    JOIN classtype t ON cc.type_id = t.classtype_id 
    LEFT JOIN course co ON cc.course_code = co.course_code 
    WHERE cc.course_class_id = ?
");
if (!$stmt) {
    header("Location: modify.php?error=" . urlencode("Prepare failed: " . $mysqli->error));
    exit;
}
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$record = $result->fetch_assoc();
$stmt->close();

if (!$record) {
    header("Location: modify.php?error=" . urlencode("Record not found."));
    exit;
}

// Retrieve options for the select elements.
$classnames = [];
$result = $mysqli->query("SELECT name FROM classname");
while ($row = $result->fetch_assoc()) {
    $classnames[] = $row;
}

$classtypes = [];
$result = $mysqli->query("SELECT type FROM classtype");
while ($row = $result->fetch_assoc()) {
    $classtypes[] = $row;
}

$courses = [];
$result = $mysqli->query("SELECT course_code FROM course");
while ($row = $result->fetch_assoc()) {
    $courses[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Class</title>
    <link rel="stylesheet" href="../style/style.css">
</head>
<body>
    <div class="login-container">
        <h2>Edit Class</h2>
        <form action="edit.php" method="POST">
            <!-- Include CSRF token -->
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <!-- Include record id -->
            <input type="hidden" name="id" value="<?= htmlspecialchars($record['id']) ?>">
            
            <label for="classname">Class Name:</label>
            <select name="newClassname" id="classname" required>
                <?php foreach ($classnames as $option): ?>
                    <option value="<?= htmlspecialchars($option['name']) ?>" <?= ($record['classname'] == $option['name'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($option['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            
            <label for="classtype">Class Type:</label>
            <select name="newClasstype" id="classtype" required>
                <?php foreach ($classtypes as $option): ?>
                    <option value="<?= htmlspecialchars($option['type']) ?>" <?= ($record['classtype'] == $option['type'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($option['type']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            
            <label for="course_code">Course Code:</label>
            <select name="newCourseCode" id="course_code" required>
                <?php foreach ($courses as $option): ?>
                    <option value="<?= htmlspecialchars($option['course_code']) ?>" <?= ($record['course_code'] == $option['course_code'] ? 'selected' : '') ?>>
                        <?= htmlspecialchars($option['course_code']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <br>
            
            <button type="submit" name="final_login">Update</button>
        </form>
        <button type="button" onclick="window.location.href='modify.php'">Cancel</button>
    </div>
</body>
</html>
