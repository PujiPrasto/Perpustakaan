<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html"); // Redirect to login if not logged in
    exit();
}
include 'db.php'; // Include your database connection file 
?>
<!DOCTYPE html>
<html>
<head>
    <title>Library Dashboard - All Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f4f4;
        }
        h1, h2 {
            color: #333;
        }
        .section {
            background-color: #fff;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .logout-link {
            display: block;
            margin-top: 20px;
            color: #d9534f;
            text-decoration: none;
        }
        .logout-link:hover {
            text-decoration: underline;
        }
        .link-list {
            list-style: none;
            padding: 0;
        }
        .link-list li {
            margin-bottom: 5px;
        }
        .link-list a {
            text-decoration: none;
            color: #007bff;
        }
        .link-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>Welcome to the Library System, <?php echo $_SESSION['username']; ?>!</h1>
    <p>Here's an overview of your library data.</p>

    <div class="section">
        <h2>Quick Navigation</h2>
        <ul class="link-list">
            <li><a href="books.php">Manage Books</a></li>
            <li><a href="members.php">Manage Members</a></li>
            <li><a href="loans.php">Manage Loans (Peminjaman)</a></li>
            <li><a href="returns.php">Manage Returns (Pengembalian)</a></li>
        </ul>
    </div>

    <div class="section">
        <h2>All Books</h2>
        <?php
        $sql_books = "SELECT book_id, title, author, publication_year, isbn FROM books ORDER BY title ASC";
        $result_books = $conn->query($sql_books);

        if ($result_books->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Title</th><th>Author</th><th>Publication Year</th><th>ISBN</th></tr>";
            while($row = $result_books->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["book_id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["title"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["author"]) . "</td>";
                echo "<td>" . $row["publication_year"] . "</td>";
                echo "<td>" . htmlspecialchars($row["isbn"]) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No books found.</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>All Members</h2>
        <?php
        $sql_members = "SELECT member_id, name, address, phone FROM members ORDER BY name ASC";
        $result_members = $conn->query($sql_members);

        if ($result_members->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th></tr>";
            while($row = $result_members->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["member_id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["name"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["address"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["phone"]) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No members found.</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>All Loans (Peminjaman)</h2>
        <?php
        $sql_loans = "SELECT l.loan_id, b.title AS book_title, m.name AS member_name, l.loan_date, l.due_date, l.returned
                      FROM loans l
                      JOIN books b ON l.book_id = b.book_id
                      JOIN members m ON l.member_id = m.member_id
                      ORDER BY l.loan_date DESC";
        $result_loans = $conn->query($sql_loans);

        if ($result_loans->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Loan ID</th><th>Book Title</th><th>Member Name</th><th>Loan Date</th><th>Due Date</th><th>Returned</th></tr>";
            while($row = $result_loans->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["loan_id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["book_title"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["member_name"]) . "</td>";
                echo "<td>" . $row["loan_date"] . "</td>";
                echo "<td>" . $row["due_date"] . "</td>";
                echo "<td>" . ($row["returned"] ? 'Yes' : 'No') . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No loans found.</p>";
        }
        ?>
    </div>

    <div class="section">
        <h2>All Returns (Pengembalian)</h2>
        <?php
        $sql_returns = "SELECT r.return_id, l.loan_id, b.title AS book_title, m.name AS member_name, r.return_date, l.due_date
                       FROM returns r
                       JOIN loans l ON r.loan_id = l.loan_id
                       JOIN books b ON l.book_id = b.book_id
                       JOIN members m ON l.member_id = m.member_id
                       ORDER BY r.return_date DESC";
        $result_returns = $conn->query($sql_returns);

        if ($result_returns->num_rows > 0) {
            echo "<table>";
            echo "<tr><th>Return ID</th><th>Loan ID</th><th>Book Title</th><th>Member Name</th><th>Due Date (Loan)</th><th>Actual Return Date</th></tr>";
            while($row = $result_returns->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $row["return_id"] . "</td>";
                echo "<td>" . $row["loan_id"] . "</td>";
                echo "<td>" . htmlspecialchars($row["book_title"]) . "</td>";
                echo "<td>" . htmlspecialchars($row["member_name"]) . "</td>";
                echo "<td>" . $row["due_date"] . "</td>";
                echo "<td>" . $row["return_date"] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No returns recorded yet.</p>";
        }
        $conn->close(); // Close the database connection after all queries are done
        ?>
    </div>

    <p><a href="logout.php" class="logout-link">Logout</a></p>
</body>
</html>