<?php
include 'db_connection.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

// Case 1: Adding a new class name only
if (isset($data['classname']) && !isset($data['classtype']) && !isset($data['courseCode'])) {
    try {
        // Check if the class already exists
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM classname WHERE name = ?");
        $stmt->execute([$data['classname']]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(['success' => false, 'error' => 'Class name already exists.']);
            exit;
        }

        // Insert new class name
        $stmt = $pdo->prepare("INSERT INTO classname (name) VALUES (?)");
        if ($stmt->execute([$data['classname']])) {
            echo json_encode(['success' => true, 'message' => 'Class name added successfully.']);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to add class name.']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// Case 2: Adding a full class with classname, classtype, and courseCode
if (!isset($data['classname']) || !isset($data['classtype']) || !isset($data['courseCode'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$classname = trim($data['classname']);
$classtype = trim($data['classtype']);
$courseCode = trim($data['courseCode']);

try {
    // Get the class IDs
    $stmt = $pdo->prepare("SELECT classname_id FROM classname WHERE name = ?");
    $stmt->execute([$classname]);
    $classnameId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT classtype_id FROM classtype WHERE type = ?");
    $stmt->execute([$classtype]);
    $classtypeId = $stmt->fetchColumn();

    if (!$classnameId || !$classtypeId) {
        echo json_encode(['success' => false, 'error' => 'Invalid class name or type']);
        exit;
    }

    // Check for duplicate entry considering class type
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course_class WHERE classname_id = ? AND type_id = ? AND course_code = ?");
    $stmt->execute([$classnameId, $classtypeId, $courseCode]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Duplicate entry exists']);
        exit;
    }

    // Insert the class
    $stmt = $pdo->prepare("INSERT INTO course_class (classname_id, type_id, course_code) VALUES (?, ?, ?)");
    
    if ($stmt->execute([$classnameId, $classtypeId, $courseCode])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to insert class.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
