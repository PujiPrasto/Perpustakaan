<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.html");
    exit();
}
include 'db.php';

// --- Handle Create (Add New Member) ---
if (isset($_POST['add_member'])) {
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $sql = "INSERT INTO members (name, address, phone) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $name, $address, $phone); // 'sss' for three string parameters

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Member added successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error adding member: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Update (Edit Member) ---
if (isset($_POST['edit_member'])) {
    $member_id = $_POST['member_id'];
    $name = $_POST['name'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];

    $sql = "UPDATE members SET name = ?, address = ?, phone = ? WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssi", $name, $address, $phone, $member_id); // 'sssi' for three strings, one integer

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Member updated successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error updating member: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Delete (Remove Member) ---
if (isset($_GET['delete_member_id'])) {
    $member_id = $_GET['delete_member_id'];

    $sql = "DELETE FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id); // 'i' for integer

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Member deleted successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error deleting member: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// --- Handle Fetch for Edit Form ---
$member_to_edit = null;
if (isset($_GET['edit_member_id'])) {
    $member_id = $_GET['edit_member_id'];
    $sql = "SELECT * FROM members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $member_to_edit = $result->fetch_assoc();
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Members</title>
    <style>
        table, th, td {
            border: 1px solid black;
            border-collapse: collapse;
            padding: 8px;
        }
    </style>
</head>
<body>
    <h1>Manage Library Members</h1>
    <p><a href="dashboard.php">Back to Dashboard</a></p>

    <h2><?php echo ($member_to_edit ? 'Edit Member' : 'Add New Member'); ?></h2>
    <form method="post" action="members.php">
        <?php if ($member_to_edit): ?>
            <input type="hidden" name="member_id" value="<?php echo $member_to_edit['member_id']; ?>">
        <?php endif; ?>
        <label for="name">Name:</label><br>
        <input type="text" id="name" name="name" value="<?php echo ($member_to_edit ? $member_to_edit['name'] : ''); ?>" required><br><br>
        <label for="address">Address:</label><br>
        <input type="text" id="address" name="address" value="<?php echo ($member_to_edit ? $member_to_edit['address'] : ''); ?>"><br><br>
        <label for="phone">Phone:</label><br>
        <input type="text" id="phone" name="phone" value="<?php echo ($member_to_edit ? $member_to_edit['phone'] : ''); ?>"><br><br>
        <button type="submit" name="<?php echo ($member_to_edit ? 'edit_member' : 'add_member'); ?>">
            <?php echo ($member_to_edit ? 'Update Member' : 'Add Member'); ?>
        </button>
        <?php if ($member_to_edit): ?>
            <a href="members.php">Cancel Edit</a>
        <?php endif; ?>
    </form>

    <h2>Current Members</h2>
    <?php
    $sql = "SELECT * FROM members ORDER BY name ASC";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        echo "<table>";
        echo "<tr><th>ID</th><th>Name</th><th>Address</th><th>Phone</th><th>Action</th></tr>";
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row["member_id"] . "</td>";
            echo "<td>" . $row["name"] . "</td>";
            echo "<td>" . $row["address"] . "</td>";
            echo "<td>" . $row["phone"] . "</td>";
            echo "<td><a href='members.php?edit_member_id=" . $row["member_id"] . "'>Edit</a> | <a href='members.php?delete_member_id=" . $row["member_id"] . "' onclick='return confirm(\"Are you sure you want to delete this member?\")'>Delete</a></td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No members found.</p>";
    }
    $conn->close();
    ?>
</body>
</html>