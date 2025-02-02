<?php
// db.php - Database connection file

$host = 'localhost';
$dbname = 'school';
$user = 'root';
$pass = '';

function getDbConnection() {
    global $host, $dbname, $user, $pass;
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
?>