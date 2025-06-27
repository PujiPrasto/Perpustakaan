<?php
// signup.php [cite: 29]
include 'db.php';

$username = $_POST['username'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash the password for security

$sql = "INSERT INTO users (username, password) VALUES ('$username', '$password')";

if ($conn->query($sql) === TRUE) {
    echo "Sign up successful! You can now <a href='login.html'>login</a>."; // [cite: 29]
} else {
    echo "Error: " . $conn->error; // [cite: 29]
}

$conn->close();
?>