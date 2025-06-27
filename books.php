<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit();
}
include 'db.php';

// Handle Create (Add New Book)
if (isset($_POST['add_book'])) {
    $title = $_POST['title'];
    $author = $_POST['author'];
    $publication_year = $_POST['publication_year'];
    $isbn = $_POST['isbn'];

    $sql = "INSERT INTO books (title, author, publication_year, isbn) VALUES ('$title', '$author', '$publication_year', '$isbn')";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Book added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error adding book: " . $conn->error . "</p>";
    }
}

// Handle Delete (optional, but good for completeness)
if (isset($_GET['delete_book_id'])) {
    $book_id = $_GET['delete_book_id'];
    $sql = "DELETE FROM books WHERE book_id = $book_id";
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green;'>Book deleted successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error deleting book: " . $conn->error . "</p>";
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Books</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Manage Books</h1>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <h2>Add New Book</h2>
    <form method="post" action="books.php">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" required><br><br>
        <label for="author">Author:</label><br>
        <input type="text" id="author" name="author"><br><br>
        <label for="publication_year">Publication Year:</label><br>
        <input type="number" id="publication_year" name="publication_year"><br><br>
        <label for="isbn">ISBN:</label><br>
        <input type="text" id="isbn" name="isbn"><br><br>
        <button type="submit" name="add_book">Add Book</button>
    </form>

    <h2>Available Books</h2>
    <?php
    $sql = "SELECT * FROM books";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Publication Year</th><th>ISBN</th><th>Action</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["book_id"] . "</td>";
            echo "<td>" . $row["title"] . "</td>";
            echo "<td>" . $row["author"] . "</td>";
            echo "<td>" . $row["publication_year"] . "</td>";
            echo "<td>" . $row["isbn"] . "</td>";
            echo "<td><a href='edit_book.php?id=" . $row["book_id"] . "'>Edit</a> | <a href='books.php?delete_book_id=" . $row["book_id"] . "' onclick='return confirm(\"Are you sure?\")'>Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "0 results";
    }
    $conn->close();
    ?>
</body>
</html>