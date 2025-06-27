<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit();
}
include 'db.php';

// --- Handle Create (Add New Loan) ---
if (isset($_POST['add_loan'])) {
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $loan_date = $_POST['loan_date'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO loans (book_id, member_id, loan_date, due_date, returned) VALUES (?, ?, ?, ?, 0)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $book_id, $member_id, $loan_date, $due_date); // 'iiss' for two integers, two strings (dates)

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Loan added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error adding loan: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Update (Edit Loan - e.g., changing due date) ---
if (isset($_POST['edit_loan'])) {
    $loan_id = $_POST['loan_id'];
    $book_id = $_POST['book_id'];
    $member_id = $_POST['member_id'];
    $loan_date = $_POST['loan_date'];
    $due_date = $_POST['due_date'];

    $sql = "UPDATE loans SET book_id = ?, member_id = ?, loan_date = ?, due_date = ? WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissi", $book_id, $member_id, $loan_date, $due_date, $loan_id);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Loan updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error updating loan: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Delete (Remove Loan) ---
if (isset($_GET['delete_loan_id'])) {
    $loan_id = $_GET['delete_loan_id'];

    $sql = "DELETE FROM loans WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loan_id);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Loan deleted successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error deleting loan: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Fetch for Edit Form ---
$loan_to_edit = null;
if (isset($_GET['edit_loan_id'])) {
    $loan_id = $_GET['edit_loan_id'];
    $sql = "SELECT * FROM loans WHERE loan_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $loan_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $loan_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Loans</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Manage Book Loans (Peminjaman)</h1>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <h2><?php echo ($loan_to_edit ? 'Edit Loan' : 'Add New Loan'); ?></h2>
    <form method="post" action="loans.php">
        <?php if ($loan_to_edit): ?>
            <input type="hidden" name="loan_id" value="<?php echo $loan_to_edit['loan_id']; ?>">
        <?php endif; ?>

        <label for="book_id">Book:</label><br>
        <select id="book_id" name="book_id" required>
            <option value="">Select a Book</option>
            <?php
            $books_result = $conn->query("SELECT book_id, title FROM books ORDER BY title ASC");
            while ($book = $books_result->fetch_assoc()):
                $selected = ($loan_to_edit && $loan_to_edit['book_id'] == $book['book_id']) ? 'selected' : '';
            ?>
                <option value="<?php echo $book['book_id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($book['title']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="member_id">Member:</label><br>
        <select id="member_id" name="member_id" required>
            <option value="">Select a Member</option>
            <?php
            $members_result = $conn->query("SELECT member_id, name FROM members ORDER BY name ASC");
            while ($member = $members_result->fetch_assoc()):
                $selected = ($loan_to_edit && $loan_to_edit['member_id'] == $member['member_id']) ? 'selected' : '';
            ?>
                <option value="<?php echo $member['member_id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($member['name']); ?></option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="loan_date">Loan Date:</label><br>
        <input type="date" id="loan_date" name="loan_date" value="<?php echo ($loan_to_edit ? $loan_to_edit['loan_date'] : date('Y-m-d')); ?>" required><br><br>
        <label for="due_date">Due Date:</label><br>
        <input type="date" id="due_date" name="due_date" value="<?php echo ($loan_to_edit ? $loan_to_edit['due_date'] : ''); ?>" required><br><br>

        <button type="submit" name="<?php echo ($loan_to_edit ? 'edit_loan' : 'add_loan'); ?>">
            <?php echo ($loan_to_edit ? 'Update Loan' : 'Add Loan'); ?>
        </button>
        <?php if ($loan_to_edit): ?>
            <a href="loans.php">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <h2>Current Loans</h2>
    <?php
    $sql = "SELECT l.loan_id, b.title AS book_title, m.name AS member_name, l.loan_date, l.due_date, l.returned
            FROM loans l
            JOIN books b ON l.book_id = b.book_id
            JOIN members m ON l.member_id = m.member_id
            ORDER BY l.loan_date DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Loan ID</th><th>Book Title</th><th>Member Name</th><th>Loan Date</th><th>Due Date</th><th>Returned</th><th>Action</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["loan_id"] . "</td>";
            echo "<td>" . htmlspecialchars($row["book_title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["member_name"]) . "</td>";
            echo "<td>" . $row["loan_date"] . "</td>";
            echo "<td>" . $row["due_date"] . "</td>";
            echo "<td>" . ($row["returned"] ? 'Yes' : 'No') . "</td>";
            echo "<td><a href='loans.php?edit_loan_id=" . $row["loan_id"] . "'>Edit</a> | <a href='loans.php?delete_loan_id=" . $row["loan_id"] . "' onclick='return confirm(\"Are you sure you want to delete this loan record?\")'>Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No loans found.</p>";
    }
    // We don't close the connection here if it's reused for other includes.
    // For standalone scripts, it's good practice.
    // $conn->close();
    ?>
</body>
</html>