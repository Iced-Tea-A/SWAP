<?php
include 'db_connection.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['originalClassname']) || !isset($data['originalClasstype']) || !isset($data['originalCourseCode']) ||
    !isset($data['newClassname']) || !isset($data['newClasstype']) || !isset($data['newCourseCode'])) {
    echo json_encode(['success' => false, 'error' => 'Missing parameters']);
    exit;
}

$originalClassname = trim($data['originalClassname']);
$originalClasstype = trim($data['originalClasstype']);
$originalCourseCode = trim($data['originalCourseCode']);
$newClassname = trim($data['newClassname']);
$newClasstype = trim($data['newClasstype']);
$newCourseCode = trim($data['newCourseCode']);

try {
    // Get the new class IDs
    $stmt = $pdo->prepare("SELECT classname_id FROM classname WHERE name = ?");
    $stmt->execute([$newClassname]);
    $classnameId = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT classtype_id FROM classtype WHERE type = ?");
    $stmt->execute([$newClasstype]);
    $classtypeId = $stmt->fetchColumn();

    if (!$classnameId || !$classtypeId) {
        echo json_encode(['success' => false, 'error' => 'Invalid class name or type']);
        exit;
    }

    // Check for duplicate entry
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM course_class WHERE classname_id = ? AND type_id = ? AND course_code = ?");
    $stmt->execute([$classnameId, $classtypeId, $newCourseCode]);

    if ($stmt->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'error' => 'Duplicate entry exists']);
        exit;
    }

    // Update the record
    $stmt = $pdo->prepare("UPDATE course_class 
                           SET classname_id = ?, type_id = ?, course_code = ?
                           WHERE classname_id = (SELECT classname_id FROM classname WHERE name = ?) 
                           AND type_id = (SELECT classtype_id FROM classtype WHERE type = ?) 
                           AND course_code = ?");
    
    if ($stmt->execute([$classnameId, $classtypeId, $newCourseCode, $originalClassname, $originalClasstype, $originalCourseCode])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update class.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
