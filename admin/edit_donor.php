<?php
session_start();
include '../configuration/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if donor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid donor ID.");
}

$donator_id = intval($_GET['id']);

// Fetch donor details
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact_no, status FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param("i", $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    die("Donor not found.");
}

// Handle form submission for updating donor details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tbl_donor SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_no = ?, status = ? WHERE donator_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $email, $contact_no, $status, $donator_id);
    $stmt->execute();
    $stmt->close();
    
    header("Location: donor_list.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Donor</title>
</head>
<body>
    <h2>Edit Donor</h2>
    <form action="" method="POST">
        <label>First Name:</label>
        <input type="text" name="first_name" value="<?= htmlspecialchars($donor['first_name']) ?>" required>
        <br>
        
        <label>Middle Name:</label>
        <input type="text" name="middle_name" value="<?= htmlspecialchars($donor['middle_name']) ?>">
        <br>
        
        <label>Last Name:</label>
        <input type="text" name="last_name" value="<?= htmlspecialchars($donor['last_name']) ?>" required>
        <br>
        
        <label>Email:</label>
        <input type="email" name="email" value="<?= htmlspecialchars($donor['email']) ?>" required>
        <br>
        
        <label>Contact No:</label>
        <input type="text" name="contact_no" value="<?= htmlspecialchars($donor['contact_no']) ?>" required>
        <br>
        
        <label>Status:</label>
        <select name="status">
            <option value="pending" <?= $donor['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
            <option value="approved" <?= $donor['status'] == 'approved' ? 'selected' : '' ?>>Approved</option>
            <option value="declined" <?= $donor['status'] == 'declined' ? 'selected' : '' ?>>Declined</option>
        </select>
        <br>
        
        <input type="submit" value="Update Donor">
    </form>
    
    <a href="donor_list.php">Back to Donor List</a>
</body>
</html>