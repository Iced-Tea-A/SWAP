<?php
require_once '../auth.php';
include '../db.php'; // Database connection (MySQLi, using $mysqli)
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header("Location: ../home.php");
    exit;
}

$mysqli = getDBconnection();
generateCSRFToken();

function toggleOrder($order) {
    return $order === 'ASC' ? 'DESC' : 'ASC';
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Fetch dropdown options (for the add form)
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

// Sorting, searching, etc.
$sortColumn = $_GET['sort'] ?? 'classname';
$sortOrder = $_GET['order'] ?? 'ASC';
$searchQuery = trim($_GET['search'] ?? '');
$showEmptyClasses = isset($_GET['show_empty_classes']) && $_GET['show_empty_classes'] == "1";

// Count total records
$countQuery = "
    SELECT COUNT(*) as total
    FROM classname c
    LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
    LEFT JOIN classtype t ON cc.type_id = t.classtype_id
    LEFT JOIN course co ON cc.course_code = co.course_code
    WHERE (c.name LIKE ? OR cc.course_code LIKE ? OR co.name LIKE ?)
";
if (!$showEmptyClasses) {
    $countQuery .= " AND cc.course_code IS NOT NULL";
}
$stmt = $mysqli->prepare($countQuery);
$searchTerm = "%$searchQuery%";
$stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$totalRecords = $row['total'];
$stmt->close();

$totalPages = ceil($totalRecords / $itemsPerPage);

// Fetch the actual records (including a unique id from course_class)
$query = "
    SELECT cc.course_class_id as id, c.name AS classname, t.type AS classtype, cc.course_code, co.name AS course_name
    FROM classname c
    LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
    LEFT JOIN classtype t ON cc.type_id = t.classtype_id
    LEFT JOIN course co ON cc.course_code = co.course_code
    WHERE (c.name LIKE ? OR cc.course_code LIKE ? OR co.name LIKE ?)
";
if (!$showEmptyClasses) {
    $query .= " AND cc.course_code IS NOT NULL";
}
$query .= " ORDER BY $sortColumn $sortOrder LIMIT ? OFFSET ?";
$classesStmt = $mysqli->prepare($query);
$classesStmt->bind_param("sssii", $searchTerm, $searchTerm, $searchTerm, $itemsPerPage, $offset);
$classesStmt->execute();
$result = $classesStmt->get_result();
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}
$classesStmt->close();

// Get messages if available.
$message = $_GET['message'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Modify Classes</title>
    <link rel="stylesheet" href="../style/style.css">
    <script>
        // Let user uncheck a radio button by clicking it again.
        document.addEventListener('DOMContentLoaded', function() {
            const buildRadios = document.querySelectorAll('input[name="buildFilter"]');
            let lastChecked = null;
            buildRadios.forEach(radio => {
                radio.addEventListener('click', function(e) {
                    if (lastChecked === this) {
                        // If user clicked the same radio => uncheck => show all
                        this.checked = false;
                        // Remove its name so it won't be submitted
                        this.removeAttribute('name');
                        this.form.submit();
                    } else {
                        lastChecked = this;
                    }
                });
            });
        });
    </script>
</head>
<body>
<header> 
    <h1>Modify Classes</h1> 
</header>
<?php require_once 'nav.php'; ?>
<div class="container">
    <!-- Display error or success messages -->
    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($message): ?>
        <div class="alert success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    
    <!-- Search Form (GET method, no CSRF token needed) -->
    <form method="GET" action="modify.php">
        <input type="text" name="search" placeholder="Search classes" value="<?= htmlspecialchars($searchQuery) ?>">
        <label>
            <input type="checkbox" name="show_empty_classes" value="1" <?= $showEmptyClasses ? 'checked' : '' ?>>
            Show Classes Without Course Code
        </label>
        <button type="submit">Search</button>
    </form>
    
    <!-- Existing Classes Table -->
    <h3>Existing Classes</h3>
    <table id="table">
        <thead>
            <tr>
                <th><a href="?sort=classname&order=<?= toggleOrder($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Class Name</a></th>
                <th><a href="?sort=classtype&order=<?= toggleOrder($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Class Type</a></th>
                <th><a href="?sort=course_code&order=<?= toggleOrder($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Course Code</a></th>
                <th><a href="?sort=course_name&order=<?= toggleOrder($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Course Name</a></th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($classes as $class): ?>
            <tr>
                <td><?= htmlspecialchars($class['classname']) ?></td>
                <td><?= htmlspecialchars($class['classtype']) ?></td>
                <td><?= htmlspecialchars($class['course_code']) ?></td>
                <td><?= htmlspecialchars($class['course_name']) ?></td>
                <td>
                    <?php
                    $encodedId = base64_encode($class['id']);
                    ?>
                    <?php if (empty($class['course_code']) && empty($class['classtype'])): ?>
                        <?php if ($_SESSION['role_id'] == 1): ?>
                        <?php else: ?>
                            N/A
                        <?php endif; ?>
                    <?php else: ?>
                        <!-- For non-empty classes, show both Edit and Delete buttons -->
                        <button type="button" onclick="window.location.href='edit_form.php?id=<?= urlencode($encodedId) ?>'">Edit</button>
                        <?php if ($_SESSION['role_id'] == 1): ?>
                            <form action="delete.php" method="POST" style="display:inline;" onsubmit="return">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($class['id']) ?>">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                <button type="submit">Delete Entry</button>
                            </form>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination Links -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>&sort=<?= urlencode($sortColumn) ?>&order=<?= urlencode($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span><?= $i ?></span>
            <?php else: ?>
                <a href="?page=<?= $i ?>&sort=<?= urlencode($sortColumn) ?>&order=<?= urlencode($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>&sort=<?= urlencode($sortColumn) ?>&order=<?= urlencode($sortOrder) ?>&search=<?= urlencode($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Next</a>
        <?php endif; ?>
    </div>
    
    <!-- Form to Add a New Class Name -->
    <form action="add.php" method="POST">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="new_classname">New Class Name:</label>
        <input type="text" id="new_classname" name="classname" placeholder="Enter new class name" required>
        <button type="submit">Add New Class Name</button>
    </form>
    
    <!-- Add New Class Form -->
    <h3>Add New Class</h3>
    <form action="add.php" method="POST">
        <!-- CSRF Token -->
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <label for="classname">Class Name:</label>
        <select name="classname" id="classname" required>
            <option value="">Select Class Name</option>
            <?php foreach ($classnames as $option): ?>
                <option value="<?= htmlspecialchars($option['name']) ?>"><?= htmlspecialchars($option['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        
        <label for="classtype">Class Type:</label>
        <select name="classtype" id="classtype" required>
            <option value="">Select Class Type</option>
            <?php foreach ($classtypes as $option): ?>
                <option value="<?= htmlspecialchars($option['type']) ?>"><?= htmlspecialchars($option['type']) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        
        <label for="course_code">Course Code:</label>
        <select name="courseCode" id="course_code" required>
            <option value="">Select Course Code</option>
            <?php foreach ($courses as $option): ?>
                <option value="<?= htmlspecialchars($option['course_code']) ?>"><?= htmlspecialchars($option['course_code']) ?></option>
            <?php endforeach; ?>
        </select>
        <br>
        
        <button type="submit">Add Class</button>
    </form>
    <?php if ($_SESSION['role_id'] == 1): ?>
    <form action="delete.php" method="POST" onsubmit="return confirm('Are you sure you want to delete all empty classes?');">
    <input type="hidden" name="delete_empty" value="1">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <button type="submit">Delete All Empty Classes</button>
    <?php endif; ?>
</form>
</div>
</body>
</html>
