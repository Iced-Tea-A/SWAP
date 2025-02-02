<?php
require_once '../auth.php';
require '../db.php'; // This should set up $mysqli
$mysqli = getDbConnection();

// Gather role info
$role_id  = (int) $_SESSION['role_id'];
$userId   = $_SESSION['user_id'];

// Get search and buildFilter from GET
$search      = trim($_GET['search'] ?? '');
$buildFilter = $_GET['buildFilter'] ?? ''; // '', 'built', or 'nonbuilt'

// ------------------- QUERY LOGIC -------------------
if ($role_id === 3) {
    // STUDENT => Show only classes they are enrolled in.
    // First, fetch the student email from the account table.
    $stmt = $mysqli->prepare("SELECT email FROM account WHERE id = ? LIMIT 1");
    if (!$stmt) {
        die("Prepare failed: " . $mysqli->error);
    }
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $userRow = $result->fetch_assoc();
    $stmt->close();
    
    if ($userRow) {
        $userEmail = $userRow['email'];
    } else {
        echo "Error: student not found.";
        exit;
    }
    
    // Build the query for students
    $query = "
      SELECT
          c.classname_id,
          c.name AS classname,
          cc.course_class_id,
          cc.course_code,
          t.type AS classtype,
          co.name AS course_name
      FROM classname c
      LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
      LEFT JOIN classtype t ON t.classtype_id = cc.type_id
      LEFT JOIN course co ON co.course_code = cc.course_code
      INNER JOIN enrollment e ON e.course_class_id = cc.course_class_id AND e.student_email = ?
      WHERE 1=1
    ";
    
    // Initialize binding arrays
    $params = [];
    $types  = "s"; // For student email
    $params[] = $userEmail;
    
    if ($search !== '') {
        $query .= "
          AND (
            c.name        LIKE ? 
            OR t.type     LIKE ? 
            OR cc.course_code LIKE ? 
            OR co.name    LIKE ?
          )
        ";
        $types .= "ssss";
        $searchParam = "%" . $search . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
} else {
    // ADMIN (1) or FACULTY (2) => See ALL classes from `classname`.
    $query = "
      SELECT
          c.classname_id,
          c.name AS classname,
          cc.course_class_id,
          cc.course_code,
          t.type AS classtype,
          co.name AS course_name
      FROM classname c
      LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
      LEFT JOIN classtype t ON t.classtype_id = cc.type_id
      LEFT JOIN course co ON co.course_code = cc.course_code
      WHERE 1=1
    ";
    $types = "";
    $params = [];
    
    // Apply built filter if provided
    if ($buildFilter === 'built') {
        $query .= " AND cc.course_class_id IS NOT NULL ";
    } elseif ($buildFilter === 'nonbuilt') {
        $query .= " AND cc.course_class_id IS NULL ";
    }
    
    if ($search !== '') {
        $query .= "
          AND (
            c.name        LIKE ? 
            OR t.type     LIKE ? 
            OR cc.course_code LIKE ? 
            OR co.name    LIKE ?
          )
        ";
        $types .= "ssss";
        $searchParam = "%" . $search . "%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
}

// ------------------- EXECUTE QUERY -------------------

// Prepare the statement
$stmt = $mysqli->prepare($query);
if (!$stmt) {
    die("Prepare failed: " . $mysqli->error);
}

if (!empty($params)) {
    $bindNames = [];
    $bindNames[] = $types;
    for ($i = 0; $i < count($params); $i++) {
        $bindNames[] = &$params[$i];
    }
    call_user_func_array([$stmt, 'bind_param'], $bindNames);
}

$stmt->execute();
$result = $stmt->get_result();

$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Classes</title>
  <link rel="stylesheet" href="../style/style.css">
  <script>
    // Let user uncheck a radio button by clicking it again.
    document.addEventListener('DOMContentLoaded', function() {
      const buildRadios = document.querySelectorAll('input[name="buildFilter"]');
      let lastChecked = null;

      buildRadios.forEach(radio => {
        radio.addEventListener('click', function(e) {
          if (lastChecked === this) {
            // If user clicked the same radio, uncheck it and submit form to show all
            this.checked = false;
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
    <h1>View Classes</h1> 
</header>
<?php
require_once 'nav.php';
?>
<div class="container">

  <!-- SINGLE FORM for search + radio -->
  <!-- For Students, we hide the radio inputs with CSS or conditionally skip them. -->
  <div class="search-section">
    <form method="GET">
      <input type="text" name="search"
             placeholder="Search (Name, Type, Code, Course)"
             value="<?= htmlspecialchars($search) ?>">
      
      <?php if ($role_id !== 3): ?>
        <!-- Admin/Faculty: show radio buttons for build filter -->
        <br><br>
        <label>
          <input type="radio" name="buildFilter" value="built"
            <?php if ($buildFilter === 'built') echo 'checked'; ?>>
          Show built classes only
        </label>
        <label style="margin-left:1em;">
          <input type="radio" name="buildFilter" value="nonbuilt"
            <?php if ($buildFilter === 'nonbuilt') echo 'checked'; ?>>
          Show non-built classes only
        </label>
        <br>
      <?php endif; ?>

      <button type="submit">Search</button>
    </form>
  </div>

  <!-- TABLE -->
  <table id="table">
    <thead>
      <tr>
        <th>Class Name</th>
        <th>Class Type</th>
        <th>Course Code</th>
        <th>Course Name</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($classes)): ?>
        <tr><td colspan="4" class="no-data">No classes found.</td></tr>
      <?php else: ?>
        <?php foreach ($classes as $class): ?>
          <tr>
            <td><?= htmlspecialchars($class['classname']) ?></td>
            <td><?= htmlspecialchars($class['classtype'] ?? '') ?></td>
            <td><?= htmlspecialchars($class['course_code'] ?? '') ?></td>
            <td><?= htmlspecialchars($class['course_name'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      <?php endif; ?>
    </tbody>
  </table>

  <!-- Modify button: bottom-right, only for Admin/Faculty -->
  <?php if ($role_id === 1 || $role_id === 2): ?>
    <div class="modify-button-container">
      <a href="modify.php">Modify</a>
    </div>
  <?php endif; ?>
</div>
</body>
</html>
