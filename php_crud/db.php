<?php
// db.php - Database Connection

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change to your MySQL username
define('DB_PASS', '');            // Change to your MySQL password
define('DB_NAME', 'Assignment');

$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if (!$conn) {
    die("<div style='font-family:Arial;color:red;padding:20px;'>
        <strong>Database Connection Failed:</strong> " . mysqli_connect_error() . "
        <br><small>Please check your DB credentials in db.php</small>
    </div>");
}

mysqli_set_charset($conn, "utf8");
?>
