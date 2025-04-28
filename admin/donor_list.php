<?php
session_start();
include '../configuration/db_connect.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Deletion
if (isset($_GET['delete'])) {
    $donator_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM tbl_donor WHERE donator_id = ?");
    $stmt->bind_param("i", $donator_id);
    $stmt->execute();
    $stmt->close();
    header("Location: donor_list.php");
    exit();
}

// Handle Approval
if (isset($_GET['approve'])) {
    $donator_id = intval($_GET['approve']);
    $stmt = $conn->prepare("UPDATE tbl_donor SET status = 'Approved' WHERE donator_id = ?");
    $stmt->bind_param("i", $donator_id);
    $stmt->execute();
    $stmt->close();
    header("Location: donor_list.php");
    exit();
}

// Handle Decline
if (isset($_GET['decline'])) {
    $donator_id = intval($_GET['decline']);
    $stmt = $conn->prepare("UPDATE tbl_donor SET status = 'Declined' WHERE donator_id = ?");
    $stmt->bind_param("i", $donator_id);
    $stmt->execute();
    $stmt->close();
    header("Location: donor_list.php");
    exit();
}

// Handle Add Donor 
if (isset($_POST['add_donor'])) {
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $contact_no = trim($_POST['contact_no']);
    $password = password_hash("default123", PASSWORD_DEFAULT); // Default password

    // Email checker
    $check = $conn->prepare("SELECT email FROM tbl_donor WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows == 0) {
        $stmt = $conn->prepare("INSERT INTO tbl_donor (first_name, middle_name, last_name, email, contact_no, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Approved')");
        $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $email, $contact_no, $password);
        $stmt->execute();
        $stmt->close();
        header("Location: donor_list.php");
        exit();
    }
}

// Fetch Donors
$stmt = $conn->prepare("SELECT donator_id, first_name, middle_name, last_name, email, contact_no, status FROM tbl_donor");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor List</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #343a40; color: white; padding-top: 20px; position: fixed; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; }
        .sidebar a:hover { background: #007bff; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        .btn { display: inline-block; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; margin-right: 5px; }
        .btn-danger { background: #dc3545; }
        .btn-approve { background: #28a745; }
        .btn-warning { background: #ffc107; color: black; }
        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .declined { color: red; font-weight: bold; }
        form input { margin: 5px 0; padding: 5px; width: 100%; max-width: 400px; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="text-align: center;">Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="charity_list.php">Charity</a>
    <a href="donor_list.php" class="active">Donors</a>
    <a href="admin_list.php">Admins</a>
    <a href="admin_reset_request.php">Reset Requests</a>
    <a href="logout.php">Logout</a>
</div>

<div class="main-content">
    <h2>Donors</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <?php $status = trim($row['status']); ?>
                <tr>
                    <td>
                        <a href="donor_profile.php?donator_id=<?= $row['donator_id'] ?>" class="btn">View</a>
                        <?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) ?>
                    </td>
                    <td><?= htmlspecialchars($row['email']) ?></td>
                    <td><?= htmlspecialchars($row['contact_no']) ?></td>
                    <td>
                        <?php if ($status == 'Pending'): ?>
                            <span class="pending">Pending</span>
                        <?php elseif ($status == 'Approved'): ?>
                            <span class="approved">Approved</span>
                        <?php elseif ($status == 'Declined'): ?>
                            <span class="declined">Declined</span>
                        <?php else: ?>
                            <span style="color: gray;">Unknown</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_donor.php?id=<?= $row['donator_id'] ?>" class="btn">Edit</a>
                        <a href="donor_list.php?delete=<?= $row['donator_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                        <?php if ($status == 'Pending'): ?>
                            <a href="donor_list.php?approve=<?= $row['donator_id'] ?>" class="btn btn-approve" onclick="return confirm('Approve this donor?')">Approve</a>
                            <a href="donor_list.php?decline=<?= $row['donator_id'] ?>" class="btn btn-warning" onclick="return confirm('Decline this donor?')">Decline</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No donors found.</p>
    <?php endif; ?>

    <!-- Add Donor Form -->
    <h2>Add Donor</h2>
    <form action="" method="POST">
        <input type="text" name="first_name" placeholder="First Name" required>
        <input type="text" name="middle_name" placeholder="Middle Name">
        <input type="text" name="last_name" placeholder="Last Name" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="contact_no" placeholder="Contact Number" required>
        <input type="submit" name="add_donor" value="Add Donor">
        <small>Default password: <strong>default123</strong></small>
    </form>
</div>

</body>
</html>