<?php
include 'db_connection.php';
header('Content-Type: application/json');

session_start();
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$classname = $data['classname'] ?? null;
$classtype = $data['classtype'] ?? null;
$courseCode = $data['courseCode'] ?? null;

if (!$classname) {
    echo json_encode(['success' => false, 'error' => 'Invalid deletion request']);
    exit;
}

try {
    if ($classtype && $courseCode) {
        $stmt = $pdo->prepare("DELETE FROM course_class WHERE classname_id = (SELECT classname_id FROM classname WHERE name = ?) AND type_id = (SELECT classtype_id FROM classtype WHERE type = ?) AND course_code = ?");
        $stmt->execute([$classname, $classtype, $courseCode]);
    } else {
        $stmt = $pdo->prepare("DELETE FROM classname WHERE name = ?");
        $stmt->execute([$classname]);
    }

    echo json_encode(['success' => true, 'message' => 'Deleted successfully']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
