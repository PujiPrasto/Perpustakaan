<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit();
}
include 'db.php';

// --- Handle Create (Record New Return) ---
if (isset($_POST['record_return'])) {
    $loan_id = $_POST['loan_id'];
    $return_date = $_POST['return_date'];

    // First, insert into returns table
    $sql_insert_return = "INSERT INTO returns (loan_id, return_date) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert_return);
    $stmt_insert->bind_param("is", $loan_id, $return_date);

    if ($stmt_insert->execute()) {
        // Second, update the 'returned' status in the loans table
        $sql_update_loan = "UPDATE loans SET returned = 1 WHERE loan_id = ?";
        $stmt_update = $conn->prepare($sql_update_loan);
        $stmt_update->bind_param("i", $loan_id);

        if ($stmt_update->execute()) {
            echo "<p style='color:green;'>Book returned successfully and loan marked as returned!</p>";
        } else {
            echo "<p style='color:red;'>Error updating loan status: " . $stmt_update->error . "</p>";
        }
        $stmt_update->close();
    } else {
        echo "<p style='color:red;'>Error recording return: " . $stmt_insert->error . "</p>";
    }
    $stmt_insert->close();
}

// --- Handle Delete (Remove Return Record - Use with caution!) ---
if (isset($_GET['delete_return_id'])) {
    $return_id = $_GET['delete_return_id'];

    // Get loan_id before deleting return record to reset loan status
    $sql_get_loan_id = "SELECT loan_id FROM returns WHERE return_id = ?";
    $stmt_get_loan = $conn->prepare($sql_get_loan_id);
    $stmt_get_loan->bind_param("i", $return_id);
    $stmt_get_loan->execute();
    $result_get_loan = $stmt_get_loan->get_result();
    $loan_to_reset = null;
    if ($result_get_loan->num_rows > 0) {
        $loan_to_reset = $result_get_loan->fetch_assoc()['loan_id'];
    }
    $stmt_get_loan->close();

    $conn->begin_transaction(); // Start transaction for atomicity

    try {
        // Delete from returns table
        $sql_delete_return = "DELETE FROM returns WHERE return_id = ?";
        $stmt_delete_return = $conn->prepare($sql_delete_return);
        $stmt_delete_return->bind_param("i", $return_id);
        $stmt_delete_return->execute();
        $stmt_delete_return->close();

        // If a loan was associated, reset its 'returned' status to 0 (not returned)
        if ($loan_to_reset) {
            $sql_reset_loan = "UPDATE loans SET returned = 0 WHERE loan_id = ?";
            $stmt_reset_loan = $conn->prepare($sql_reset_loan);
            $stmt_reset_loan->bind_param("i", $loan_to_reset);
            $stmt_reset_loan->execute();
            $stmt_reset_loan->close();
        }
        $conn->commit();
        echo "<p style='color:green;'>Return record deleted and loan status reset successfully!</p>";
    } catch (mysqli_sql_exception $e) {
        $conn->rollback();
        echo "<p style='color:red;'>Error deleting return record: " . $e->getMessage() . "</p>";
    }
}

// --- Handle Fetch for Edit Form (less common for returns, but included for completeness) ---
$return_to_edit = null;
if (isset($_GET['edit_return_id'])) {
    $return_id = $_GET['edit_return_id'];
    $sql = "SELECT * FROM returns WHERE return_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $return_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $return_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Returns</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Manage Book Returns (Pengembalian)</h1>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <h2>Record New Return</h2>
    <form method="post" action="returns.php">
        <label for="loan_id">Loan to Return:</label><br>
        <select id="loan_id" name="loan_id" required>
            <option value="">Select an Unreturned Loan</option>
            <?php
            // Fetch loans that have not yet been returned
            $loans_to_return_sql = "SELECT l.loan_id, b.title AS book_title, m.name AS member_name, l.loan_date, l.due_date
                                    FROM loans l
                                    JOIN books b ON l.book_id = b.book_id
                                    JOIN members m ON l.member_id = m.member_id
                                    WHERE l.returned = 0
                                    ORDER BY l.loan_date DESC";
            $loans_result = $conn->query($loans_to_return_sql);
            while ($loan = $loans_result->fetch_assoc()):
            ?>
                <option value="<?php echo $loan['loan_id']; ?>">
                    <?php echo htmlspecialchars($loan['book_title']) . " (to " . htmlspecialchars($loan['member_name']) . ", Due: " . $loan['due_date'] . ")"; ?>
                </option>
            <?php endwhile; ?>
        </select><br><br>

        <label for="return_date">Return Date:</label><br>
        <input type="date" id="return_date" name="return_date" value="<?php echo date('Y-m-d'); ?>" required><br><br>

        <button type="submit" name="record_return">Record Return</button>
    </form>

    <h2>All Returns</h2>
    <?php
    $sql = "SELECT r.return_id, l.loan_id, b.title AS book_title, m.name AS member_name, r.return_date, l.due_date
            FROM returns r
            JOIN loans l ON r.loan_id = l.loan_id
            JOIN books b ON l.book_id = b.book_id
            JOIN members m ON l.member_id = m.member_id
            ORDER BY r.return_date DESC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>Return ID</th><th>Loan ID</th><th>Book Title</th><th>Member Name</th><th>Due Date</th><th>Actual Return Date</th><th>Action</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["return_id"] . "</td>";
            echo "<td>" . $row["loan_id"] . "</td>";
            echo "<td>" . htmlspecialchars($row["book_title"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["member_name"]) . "</td>";
            echo "<td>" . $row["due_date"] . "</td>";
            echo "<td>" . $row["return_date"] . "</td>";
            echo "<td><a href='returns.php?delete_return_id=" . $row["return_id"] . "' onclick='return confirm(\"Are you sure you want to delete this return record? This will also revert the loan status.\")'>Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No returns recorded yet.</p>";
    }
    // $conn->close(); // Close if this is the last script using $conn
    ?>
</body>
</html>