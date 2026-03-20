<?php
require_once 'db.php';

$username = 'admin';
$password = 'admin123';
$hashed   = password_hash($password, PASSWORD_DEFAULT);

mysqli_query($conn, "DELETE FROM admin WHERE username = 'admin'");

$sql = "INSERT INTO admin (username, password) VALUES ('$username', '$hashed')";

if (mysqli_query($conn, $sql)) {
    echo "✅ Done! Username: admin | Password: admin123 — <a href='login.php'>Login now</a> — then DELETE this file!";
} else {
    echo "❌ Error: " . mysqli_error($conn);
}
?>