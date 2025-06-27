<?php
// db.php [cite: 28]
$host = "localhost";
$user = "root";
$pass = ""; // Default XAMPP password is empty
$db = "library_system"; // Our new database
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); // [cite: 29]
}
?>