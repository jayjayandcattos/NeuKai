<?php
session_start();
include '../configuration/db_connect.php';

date_default_timezone_set('Asia/Manila');
// Enable Error Reporting for Debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if `donator_id` is provided in the URL
if (!isset($_GET['donator_id']) || empty($_GET['donator_id'])) {
    die("Donor ID is missing.");
}

$donator_id = intval($_GET['donator_id']);

// Fetch Donor Details
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact_no, status FROM tbl_donor WHERE donator_id = ?");
if ($stmt === false) {
    die('Error preparing SQL query: ' . $conn->error);
}

$stmt->bind_param("i", $donator_id);
$stmt->execute();
$donor_result = $stmt->get_result();
$donor = $donor_result->fetch_assoc();
$stmt->close();

if (!$donor) {
    die("Donor not found.");
}

// Fetch Donation History
$stmt = $conn->prepare("
SELECT 
d.donator_id,
    d.donation_id, 
    d.donation_name, 
    d.total_donation, 
    d.status, 
    d.donation_date, 
    c.charity_name 
FROM tbl_donations d    
JOIN tbl_donation_transactions t ON d.donation_id = t.donation_id
JOIN tbl_charity c ON t.charity_id = c.charity_id
WHERE d.donator_id = ?
");if ($stmt === false) {
    die('Error preparing SQL query: ' . $conn->error);
}

$stmt->bind_param("i", $donator_id);
$stmt->execute();
$donations = $stmt->get_result();
$stmt->close();

// Update donation status
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['donation_id'])) {
    $status = 'delivered';
    $donation_id = intval($_POST['donation_id']);
    // $admin_id = intval($_POST['admin_id']);

    // Update status in tbl_donations
    $update_stmt = $conn->prepare("UPDATE tbl_donations SET status = ? WHERE donation_id = ?");
    if ($update_stmt === false) {
        die('Error preparing SQL query: ' . $conn->error);
    }

    $update_stmt->bind_param("si", $status, $donation_id);
    $update_result = $update_stmt->execute();
    $update_stmt->close();

    if ($update_result) {

        // Update status in tbl_donation_transactions
        $current_timestamp = date("Y-m-d H:i:s");
        $admin_id = intval($_SESSION['admin_id']);
        $delivered_at = ($status === 'delivered') ? $current_timestamp : null;
        
        $update_transaction_stmt = $conn->prepare("
            UPDATE tbl_donation_transactions 
            SET status = ?, updated_at = ?, delivered_at = ?, admin_id = ? 
            WHERE donation_id = ?
        ");
        
        if ($update_transaction_stmt === false) {
            die('Error preparing SQL query: ' . $conn->error);
        }
        
        $update_transaction_stmt->bind_param("sssii", $status, $current_timestamp, $delivered_at, $admin_id, $donation_id);
        $update_transaction_stmt->execute();
        $update_transaction_stmt->close();
        

        // Refresh the page after successful update
        header("Location: " . $_SERVER['PHP_SELF'] . "?donator_id=" . $donator_id);
        exit;
    } else {
        echo "Error updating status in donations.";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor Profile</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; display: flex; }
        .sidebar { width: 250px; height: 100vh; background: #343a40; color: white; padding-top: 20px; position: fixed; }
        .sidebar a { display: block; color: white; padding: 15px; text-decoration: none; }
        .sidebar a:hover { background: #007bff; }
        .main-content { margin-left: 250px; padding: 20px; width: calc(100% - 250px); }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid black; padding: 8px; text-align: left; }
        .btn { display: inline-block; padding: 8px 12px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }
        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
        .delivered { color: blue; font-weight: bold; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2 style="text-align: center;">Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="charity_list.php">Charity</a>
    <a href="donor_list.php" class="active">Donors</a>
    <a href="admin_list.php">Admins</a>
    <a href="admin_reset_request.php">Reset Requests</a>
    <a href="logout.php">Logout</a>
</div>
<!-- Main Content -->
<div class="main-content">

    <h2>Donor Profile</h2>
    <table>
        <tr><th>Name:</th><td><?= htmlspecialchars($donor['first_name'] . ' ' . $donor['middle_name'] . ' ' . $donor['last_name']) ?></td></tr>
        <tr><th>Email:</th><td><?= htmlspecialchars($donor['email']) ?></td></tr>
        <tr><th>Contact:</th><td><?= htmlspecialchars($donor['contact_no']) ?></td></tr>
        <tr><th>Status:</th>
<td>
    <?php $status = trim($donor['status']); ?>
    <?php if ($status == 'Pending'): ?>
        <span class="pending">Pending</span>
    <?php elseif ($status == 'Approved'): ?>
        <span class="approved">Approved</span>
    <?php elseif ($status == 'Declined'): ?>
        <span class="rejected">Declined</span>
    <?php else: ?>
        <span style="color: gray;">Unknown</span>
    <?php endif; ?>
</td>
</tr>


    </table>

    <h2>Donation History</h2>
    <?php if ($donations->num_rows > 0): ?>
    <table>
        <tr>
            <th>Donation Name</th>
            <th>Quantity</th>
            <th>Donated To</th>
            <th>Donation Status</th>
            <th>Donation Date</th>
            <th>Action</th>
        </tr>
        <?php while ($donation = $donations->fetch_assoc()): ?>
        <tr>
        <td><a href="donor_summary.php?donator_id=<?= $donation['donator_id'] ?>&donation_id=<?= $donation['donation_id'] ?>"><?= htmlspecialchars($donation['donation_name']) ?></a></td>

            <td><?= htmlspecialchars($donation['total_donation']) ?></td>
            <td><?= htmlspecialchars($donation['charity_name']) ?></td>
            <td>
                <?php
                    if ($donation['status'] == 'pending') {
                        echo '<span class="pending">Pending</span>';
                    } elseif ($donation['status'] == 'approved') {
                        echo '<span class="approved">Approved</span>';
                    } elseif ($donation['status'] == 'rejected') { 
                        echo '<span class="rejected">Rejected</span>';
                    } else {
                        echo '<span class="delivered">Delivered</span>';
                    }
                ?>
            </td>

            <td><?= htmlspecialchars($donation['donation_date']) ?></td>
            <?php
            $is_disabled = in_array($donation['status'], ['pending', 'rejected', 'delivered']);
            ?>
            <td>
                <form action="" method="POST">
                <input type="hidden" name="admin_id" value="<?= $_SESSION['admin_id'] ?>">
                    <input type="hidden" name="donation_id" value="<?= $donation['donation_id'] ?>">
                    <input type="hidden" name="status" value="delivered">
                    <button type="submit"
                        class="btn"
                        style="background: <?= $is_disabled ? '#ccc' : '#007bff' ?>;
                            color: white;
                            border: none;
                            cursor: <?= $is_disabled ? 'not-allowed' : 'pointer' ?>;
                            display: inline-block;
                            padding: 8px 12px;
                            border-radius: 5px;"
                        <?= $is_disabled ? 'disabled' : '' ?>>
                    Delivered
                </button>
                </form>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <?php else: ?>
        <p>No donation records found.</p>
    <?php endif; ?>
    
    <a href='donor_list.php'>Back</a>
</div>
</body>
</html>
