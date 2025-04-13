<?php
session_start();
require('../configuration/db_connect.php');


if (!isset($_SESSION['donator_id'])) {
    header("Location: login.php");
    exit();
}

$donator_id = $_SESSION['donator_id'];

// Fetch donor details
$stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param('i', $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donator = $result->fetch_assoc();

// Check if data exists
if (!$donator) {
    // Handle case where the donor data is not found
    die("Donor not found.");
}

// Fetch completed donation transactions for the donor, including charity name
$completed_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.delivered_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'delivered'
";
$completed_donation_stmt = $conn->prepare($completed_donation_query);
$completed_donation_stmt->bind_param('i', $donator_id);
$completed_donation_stmt->execute();
$completed_donation_result = $completed_donation_stmt->get_result();

// Fetch pending donation transactions for the donor, including charity name
$pending_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.approved_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'pending'
";
$pending_donation_stmt = $conn->prepare($pending_donation_query);
$pending_donation_stmt->bind_param('i', $donator_id);
$pending_donation_stmt->execute();
$pending_donation_result = $pending_donation_stmt->get_result();

// Fetch pending donation transactions for the donor, including charity name
$cancelled_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.rejected_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'rejected'
";
$cancelled_donation_stmt = $conn->prepare($cancelled_donation_query);
$cancelled_donation_stmt->bind_param('i', $donator_id);
$cancelled_donation_stmt->execute();
$cancelled_donation_result = $cancelled_donation_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border: 1px solid #ddd;
        }
        th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h2>Profile</h2>
    <p><strong>
        <?php 
            echo htmlspecialchars($donator['first_name']) . ' ' . 
                (!empty($donator['middle_name']) ? htmlspecialchars($donator['middle_name']) . ' ' : '') . 
                htmlspecialchars($donator['last_name']);
        ?>
        </strong>
    </p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($donator['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($donator['contact_no']); ?></p>

    <form action="edit_profile.php" method="get">
        <button type="submit">Edit Profile</button>
    </form>

    <h2>Donation History</h2>
    
    <h2>Completed</h2>
    <?php
    if ($completed_donation_result->num_rows > 0) {
        echo "<table class='completed-table'>
                <tr>
                    <th>View</th>
                    <th>Charity Name</th>
                    <th>Completed Date</th>
                </tr>";
        
        while ($donation = $completed_donation_result->fetch_assoc()) {
            echo "<tr>
                    <td><a href='d-completed_summary.php?transaction_id=" . htmlspecialchars($donation['transaction_id']) . "'>View</a></td>
                    <td>" . htmlspecialchars($donation['charity_name']) . "</td>
                    <td>" . htmlspecialchars($donation['delivered_at']) . "</td>
                </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No completed donations found.</p>";
    }
    ?>

<h2>Pending</h2>
    <?php
    if ($pending_donation_result->num_rows > 0) {
        echo "<table class='pending-table'>
                <tr>
                    <th>View</th>
                    <th>Charity Name</th>
                    <th>Approved Date</th>
                </tr>";
        
        while ($donation = $pending_donation_result->fetch_assoc()) {
            echo "<tr>
                    <td><a href='d-pending_summary.php?transaction_id=" . htmlspecialchars($donation['transaction_id']) . "'>View</a></td>
                    <td>" . htmlspecialchars($donation['charity_name']) . "</td>
                    <td>" . htmlspecialchars($donation['approved_at']) . "</td>
                </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No pending donations found.</p>";
    }
    ?>

<h2>Cancelled</h2>
    <?php
    if ($cancelled_donation_result->num_rows > 0) {
        echo "<table class='cancelled-table'>
                <tr>
                    <th>View</th>
                    <th>Charity Name</th>
                    <th>Cancelled Date</th>
                </tr>";
        
        while ($donation = $cancelled_donation_result->fetch_assoc()) {
            echo "<tr>
                    <td><a href='d-cancelled_summary.php?transaction_id=" . htmlspecialchars($donation['transaction_id']) . "'>View</a></td>
                    <td>" . htmlspecialchars($donation['charity_name']) . "</td>
                    <td>" . htmlspecialchars($donation['rejected_at']) . "</td>
                </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No cancelled donations found.</p>";
    }
    ?>
</body>
</html>
