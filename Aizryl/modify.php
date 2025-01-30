<?php
include 'db_connection.php';
session_start();

// Ensure only authorized users can access
if (!isset($_SESSION['role_id']) || ($_SESSION['role_id'] != 1 && $_SESSION['role_id'] != 2)) {
    header("Location: login.php");
    exit;
}
function toggleOrder($order) {
    return $order === 'ASC' ? 'DESC' : 'ASC';
}

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$itemsPerPage = 12;
$offset = ($page - 1) * $itemsPerPage;

// Fetch dropdown options
$classnames = $pdo->query("SELECT name FROM classname")->fetchAll(PDO::FETCH_ASSOC);
$classtypes = $pdo->query("SELECT type FROM classtype")->fetchAll(PDO::FETCH_ASSOC);
$courses    = $pdo->query("SELECT course_code FROM course")->fetchAll(PDO::FETCH_ASSOC);


// Sorting, searching, etc.
$sortColumn = $_GET['sort'] ?? 'classname';
$sortOrder = $_GET['order'] ?? 'ASC';
$searchQuery = trim($_GET['search'] ?? '');
$showEmptyClasses = isset($_GET['show_empty_classes']) && $_GET['show_empty_classes'] == "1";

// 1) Count the total number of matching records
$countQuery = "
    SELECT COUNT(*) as total
    FROM classname c
    LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
    LEFT JOIN classtype t ON cc.type_id = t.classtype_id
    LEFT JOIN course co ON cc.course_code = co.course_code
    WHERE (c.name LIKE :search OR cc.course_code LIKE :search OR co.name LIKE :search)
";
if (!$showEmptyClasses) {
    $countQuery .= " AND cc.course_code IS NOT NULL";
}

$countStmt = $pdo->prepare($countQuery);
$countStmt->execute(['search' => "%$searchQuery%"]);
$totalRecords = $countStmt->fetchColumn();
$totalPages = ceil($totalRecords / $itemsPerPage);

// 2) Fetch the actual classes with LIMIT/OFFSET
$query = "
    SELECT c.name AS classname, t.type AS classtype, cc.course_code, co.name AS course_name
    FROM classname c
    LEFT JOIN course_class cc ON cc.classname_id = c.classname_id
    LEFT JOIN classtype t ON cc.type_id = t.classtype_id
    LEFT JOIN course co ON cc.course_code = co.course_code
    WHERE (c.name LIKE :search OR cc.course_code LIKE :search OR co.name LIKE :search)
";
if (!$showEmptyClasses) {
    $query .= " AND cc.course_code IS NOT NULL";
}
$query .= " ORDER BY $sortColumn $sortOrder LIMIT :limit OFFSET :offset";

$classesStmt = $pdo->prepare($query);
// Bind parameters
$classesStmt->bindValue(':search', "%$searchQuery%", PDO::PARAM_STR);
$classesStmt->bindValue(':limit', $itemsPerPage, PDO::PARAM_INT);
$classesStmt->bindValue(':offset', $offset, PDO::PARAM_INT);

$classesStmt->execute();
$classes = $classesStmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <script>
        function enableEditing(button, classname, classtype, courseCode) {
            const row = button.closest("tr");

            // Store the current edit state in localStorage
            localStorage.setItem("editingRow", JSON.stringify({
                classname: classname,
                classtype: classtype,
                courseCode: courseCode,
            }));

            row.innerHTML = `
                <td>
                    <select name="classname">
                        <?php foreach ($classnames as $classnameOption): ?>
                            <option value="<?= htmlspecialchars($classnameOption['name']) ?>" ${classname === '<?= htmlspecialchars($classnameOption['name']) ?>' ? 'selected' : ''}>
                                <?= htmlspecialchars($classnameOption['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="classtype">
                        <?php foreach ($classtypes as $classtypeOption): ?>
                            <option value="<?= htmlspecialchars($classtypeOption['type']) ?>" ${classtype === '<?= htmlspecialchars($classtypeOption['type']) ?>' ? 'selected' : ''}>
                                <?= htmlspecialchars($classtypeOption['type']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="course_code">
                        <?php foreach ($courses as $courseOption): ?>
                            <option value="<?= htmlspecialchars($courseOption['course_code']) ?>" ${courseCode === '<?= htmlspecialchars($courseOption['course_code']) ?>' ? 'selected' : ''}>
                                <?= htmlspecialchars($courseOption['course_code']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button type="button" onclick="submitEdit(this)">Finish</button>
                    <button type="button" onclick="cancelEdit()">Cancel</button>
                </td>
            `;
        }
        function submitEdit(button) {
            // Parse the stored edit object
            const editingRowData = JSON.parse(localStorage.getItem("editingRow"));
            // Remove the stored edit state so we don’t re-parse it
            localStorage.removeItem("editingRow");

            const row = button.closest("tr");
            const classname = row.querySelector("[name='classname']").value;
            const classtype = row.querySelector("[name='classtype']").value;
            const courseCode = row.querySelector("[name='course_code']").value;

            fetch("edit.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ 
                    originalClassname: editingRowData.classname,
                    originalClasstype: editingRowData.classtype,
                    originalCourseCode: editingRowData.courseCode,
                    newClassname: classname, 
                    newClasstype: classtype, 
                    newCourseCode: courseCode
                }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Class updated successfully!");
                    location.reload();
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while updating.");
            });
        }

            function cancelEdit() {
                localStorage.removeItem("editingRow"); // Remove stored edit state
                location.reload(); // Reload to restore original state
        }


        function deleteClass(classname, classtype = null, courseCode = null) {
            if (!confirm("Are you sure you want to delete this?")) {
                return;
            }

            fetch("delete.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ classname, classtype, courseCode }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message || "Deleted successfully!");
                    location.reload();
                } else {
                    alert("Failed to delete: " + (data.error || "Unknown error"));
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while deleting.");
            });
        }
        function addNewClass() {
            const newClassname = document.getElementById('new-classname').value.trim();
            if (!newClassname) {
                alert("Please enter a class name.");
                return;
            }

            fetch("add.php", { 
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ classname: newClassname }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Class name added successfully!");
                    location.reload(); // Refresh to update the dropdown
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while adding the class.");
            });
        }

        function finalizeInsertClass(button) {
            const row = button.closest("tr");
            const classname = row.querySelector("[name='classname']").value;
            const classtype = row.querySelector("[name='classtype']").value;
            const courseCode = row.querySelector("[name='course_code']").value;

            if (!classname || !classtype || !courseCode) {
                alert("Please fill all fields before finalizing.");
                return;
            }

            fetch("add.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ classname, classtype, courseCode }),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Class added successfully!");
                    row.classList.add("confirmed-class"); // Mark as added
                    row.querySelectorAll("select, button").forEach(el => el.disabled = true); // Prevent edits
                } else {
                    alert("Error: " + data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
                alert("An error occurred while adding the class.");
            });
        }
        function removeRow(button) {
        const row = button.closest("tr");
        row.remove();
        }
        function addRowToInsertTable() {
            const tbody = document.getElementById("insert-body");
            const row = document.createElement("tr");

            row.innerHTML = `
                <td>
                    <select name="classname" required>
                        <option value="">Select Class Name</option>
                        <?php foreach ($classnames as $classnameOption): ?>
                            <option value="<?= htmlspecialchars($classnameOption['name']) ?>"><?= htmlspecialchars($classnameOption['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="classtype" required>
                        <option value="">Select Class Type</option>
                        <?php foreach ($classtypes as $classtypeOption): ?>
                            <option value="<?= htmlspecialchars($classtypeOption['type']) ?>"><?= htmlspecialchars($classtypeOption['type']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <select name="course_code" required>
                        <option value="">Select Course Code</option>
                        <?php foreach ($courses as $courseOption): ?>
                            <option value="<?= htmlspecialchars($courseOption['course_code']) ?>"><?= htmlspecialchars($courseOption['course_code']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <button type="button" onclick="finalizeInsertClass(this)">Finalize</button>
                    <button type="button" onclick="removeRow(this)">X</button>
                </td>
            `;

            tbody.appendChild(row);
        }
        document.addEventListener("DOMContentLoaded", function () {
            const editingRow = JSON.parse(localStorage.getItem("editingRow"));
            
            if (editingRow) {
                const rows = document.querySelectorAll("tr");
                
                rows.forEach(row => {
                    const classnameCell = row.children[0]; // Classname column
                    const classtypeCell = row.children[1]; // Classtype column
                    const courseCodeCell = row.children[2]; // Course Code column

                    if (
                        classnameCell.innerText.trim() === editingRow.classname &&
                        classtypeCell.innerText.trim() === editingRow.classtype &&
                        courseCodeCell.innerText.trim() === editingRow.courseCode
                    ) {
                        // Re-enable editing for the correct row
                        enableEditing(
                            row.querySelector("button"), 
                            editingRow.classname, 
                            editingRow.classtype, 
                            editingRow.courseCode
                        );
                    }
                });
            }
        });

    </script>
    <title>Modify Classes</title>
</head>
<body>
    <div class="container">
        <h2>Modify Classes</h2>
            <!-- Search Bar -->
            <form method="GET">
                <input type="text" name="search" placeholder="Search classes" value="<?= htmlspecialchars($searchQuery) ?>">
                <input type="hidden" name="show_empty_classes" value="<?= $showEmptyClasses ? '1' : '0' ?>">
                <button type="submit">Search</button>
            </form>

            <!-- Toggle Show Empty Classes -->
            <form method="GET">
                <input type="hidden" name="search" value="<?= htmlspecialchars($searchQuery) ?>">
                <label>
                    <input type="checkbox" name="show_empty_classes" value="1" <?= $showEmptyClasses ? 'checked' : '' ?> onchange="this.form.submit()">
                    Show Classes Without Course Code
                </label>
            </form>

        <!-- Existing Classes Table -->
        <h3>Existing Classes</h3>
        <table id="table">
            <thead>
                <tr>
                <th><a href="?sort=classname&order=<?= toggleOrder($sortOrder) ?>&search=<?= htmlspecialchars($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Class Name</a></th>
                <th><a href="?sort=classtype&order=<?= toggleOrder($sortOrder) ?>&search=<?= htmlspecialchars($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Class Type</a></th>
                <th><a href="?sort=course_code&order=<?= toggleOrder($sortOrder) ?>&search=<?= htmlspecialchars($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Course Code</a></th>
                <th><a href="?sort=course_name&order=<?= toggleOrder($sortOrder) ?>&search=<?= htmlspecialchars($searchQuery) ?>&show_empty_classes=<?= $showEmptyClasses ? '1' : '0' ?>">Course Name</a></th>

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
                        <button onclick="enableEditing(this, '<?= htmlspecialchars($class['classname']) ?>', '<?= htmlspecialchars($class['classtype']) ?>', '<?= htmlspecialchars($class['course_code']) ?>')">Edit</button>
                            <?php if ($_SESSION['role_id'] == 1): ?>
                                <button onclick="deleteClass('<?= htmlspecialchars($class['classname']) ?>', '<?= htmlspecialchars($class['classtype']) ?>', '<?= htmlspecialchars($class['course_code']) ?>')">Delete Entry</button>
                                <button onclick="deleteClass('<?= htmlspecialchars($class['classname']) ?>')">Delete Full Class</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="pagination">
            <?php if ($totalPages > 1): ?>
                <?php
                    // Create a basic "Previous" link
                    if ($page > 1):
                        $prevPage = $page - 1;
                        echo '<a href="?page='.$prevPage.'&sort='.urlencode($sortColumn).
                            '&order='.urlencode($sortOrder).
                            '&search='.urlencode($searchQuery).
                            '&show_empty_classes='.(int)$showEmptyClasses.'">Previous</a>';
                    endif;

                    // Page number links
                    for ($i = 1; $i <= $totalPages; $i++):
                        // Highlight the current page
                        if ($i == $page) {
                            echo '<a class="active" href="#">'.$i.'</a>';
                        } else {
                            echo '<a href="?page='.$i.'&sort='.urlencode($sortColumn).
                                '&order='.urlencode($sortOrder).
                                '&search='.urlencode($searchQuery).
                                '&show_empty_classes='.(int)$showEmptyClasses.'">'.$i.'</a>';
                        }
                    endfor;

                    // Create a basic "Next" link
                    if ($page < $totalPages):
                        $nextPage = $page + 1;
                        echo '<a href="?page='.$nextPage.'&sort='.urlencode($sortColumn).
                            '&order='.urlencode($sortOrder).
                            '&search='.urlencode($searchQuery).
                            '&show_empty_classes='.(int)$showEmptyClasses.'">Next</a>';
                    endif;
                ?>
            <?php endif; ?>
        </div>
        <!-- Add New Class Form -->
        <form id="add-class-form" method="POST">
            <input type="text" id="new-classname" name="new_classname" placeholder="Enter new class name" required>
            <button type="button" onclick="addNewClass()">Add New Class Name</button>
        </form>

        <!-- Insert Class Table -->
        <h3>Insert Class</h3>
        <button type="button" onclick="addRowToInsertTable()">Add New Class</button>

        <table id="table">
            <thead>
                <tr>
                    <th>Class Name</th>
                    <th>Class Type</th>
                    <th>Course Code</th>
                    <th>Finalize</th>
                </tr>
            </thead>
            <tbody id="insert-body"></tbody>
        </table>
    </div>
    <script src="script.js"></script>
</body>
</html>
