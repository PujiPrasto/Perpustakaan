<?php
// login.php [cite: 29]
session_start(); // Start a session to store user login status
include 'db.php';

$username = $_POST['username'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE username='$username'"; // [cite: 30]
$result = $conn->query($sql); // [cite: 30]

if ($result->num_rows > 0) { // [cite: 30]
    $user = $result->fetch_assoc(); // [cite: 30]
    if (password_verify($password, $user['password'])) { // [cite: 30]
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;
        header("Location: dashboard.php"); // Redirect to the dashboard
        exit();
    } else {
        echo "Invalid password. <a href='login.html'>Try again</a>."; // [cite: 30]
    }
} else {
    echo "User not found. <a href='signup.html'>Sign up</a> or <a href='login.html'>try again</a>."; // [cite: 30]
}

$conn->close();
?>