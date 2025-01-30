<?php
session_start();

if (!isset($_SESSION['role_id'])) {
    header("Location: login.php");
    exit;
}

require 'db_connection.php';

// Gather role info
$role_id   = (int)$_SESSION['role_id'];
$userId   = $_SESSION['user_id'];

// Get search and buildFilter from GET
$search = trim($_GET['search'] ?? '');
$buildFilter = $_GET['buildFilter'] ?? '';
// buildFilter can be: '', 'built', or 'nonbuilt'


// ------------------- QUERY LOGIC -------------------

if ($role_id === 3) {
    // STUDENT => Show only classes they are enrolled in.
    // We'll do an INNER JOIN with enrollment.
    // If course_class_id is NULL, that means no row in course_class => cannot match enrollment.
    $sql = "SELECT email FROM account WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $userId]);
    $userRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userRow) {
        $userEmail = $userRow['email'];
    } else {
        // No row found. Possibly handle error or redirect
        echo "Error: student not found.";
        exit;
    }


    $query = "
      SELECT
          c.classname_id,
          c.name AS classname,
          cc.course_class_id,
          cc.course_code,
          t.type       AS classtype,
          co.name      AS course_name
      FROM classname c
      LEFT JOIN course_class cc
             ON cc.classname_id = c.classname_id
      LEFT JOIN classtype t
             ON t.classtype_id = cc.type_id
      LEFT JOIN course co
             ON co.course_code  = cc.course_code
      INNER JOIN enrollment e
             ON e.course_class_id = cc.course_class_id
            AND e.student_email   = :studentEmail
      WHERE 1=1
    ";

    // Students do NOT have a “builtFilter” because for them “nonbuilt” 
    // can’t exist (there’s no row to enroll in if it’s truly nonbuilt).
    // If you wanted them to see only "built" classes, that’s implied, 
    // because cc.course_class_id must be non-null to match enrollment.

    // Search filter
    if ($search !== '') {
        $query .= "
          AND (
            c.name        LIKE :search
            OR t.type     LIKE :search
            OR cc.course_code LIKE :search
            OR co.name    LIKE :search
          )
        ";
    }

} else {
    // ADMIN (1) or FACULTY (2) => See ALL classes from `classname`.
    // Left join to `course_class` to detect “built vs. nonbuilt.”
    $query = "
      SELECT
          c.classname_id,
          c.name AS classname,
          cc.course_class_id,
          cc.course_code,
          t.type       AS classtype,
          co.name      AS course_name
      FROM classname c
      LEFT JOIN course_class cc
             ON cc.classname_id = c.classname_id
      LEFT JOIN classtype t
             ON t.classtype_id = cc.type_id
      LEFT JOIN course co
             ON co.course_code  = cc.course_code
      WHERE 1=1
    ";

    // “Built” => cc.course_class_id IS NOT NULL
    // “Nonbuilt” => cc.course_class_id IS NULL
    if ($buildFilter === 'built') {
        $query .= " AND cc.course_class_id IS NOT NULL ";
    } elseif ($buildFilter === 'nonbuilt') {
        $query .= " AND cc.course_class_id IS NULL ";
    }

    // Search filter
    if ($search !== '') {
        $query .= "
          AND (
            c.name        LIKE :search
            OR t.type     LIKE :search
            OR cc.course_code LIKE :search
            OR co.name    LIKE :search
          )
        ";
    }
}

// ------------------- EXECUTE QUERY -------------------
$stmt = $pdo->prepare($query);

// Bind for students
if ($role_id === 3) {
    $stmt->bindValue(':studentEmail', $userEmail, PDO::PARAM_STR);
}
// Bind search if needed
if ($search !== '') {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}

$stmt->execute();
$classes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>View Classes</title>
  <link rel="stylesheet" href="styles.css">
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
<div class="container">
  <h1>View Classes</h1>

  <!-- SINGLE FORM for search + radio -->
  <!-- For Students, we hide the radio inputs with CSS or conditionally skip them. -->
  <div class="search-section">
    <form method="GET">
      <input type="text" name="search"
             placeholder="Search (Name, Type, Code, Course)"
             value="<?= htmlspecialchars($search) ?>">
      
      <?php if ($role_id !== 3): ?>
        <!-- Admin/Faculty: show radio buttons for builtFilter -->
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
