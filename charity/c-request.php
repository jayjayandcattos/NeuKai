<?php
session_start();
require('../configuration/db_connect.php');
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['charity_id'])) {
    header("Location: ../login.php");
    exit();
}

$charity_id = $_SESSION['charity_id'];

$query = "
    SELECT 
        d.donator_id,
        d.first_name, 
        ds.donation_id,
        ds.total_donation,
        ds.status,
        t.transaction_id,
        t.created_at,
        c.charity_name
    FROM 
        tbl_donation_transactions t
    JOIN 
        tbl_donor d ON t.donator_id = d.donator_id
    JOIN 
        tbl_donations ds ON t.donation_id = ds.donation_id  -- use donation_id here
    JOIN
        tbl_charity c ON t.charity_id = c.charity_id
    WHERE 
    t.status = 'pending'
    AND t.charity_id = ?  -- This correctly filters by the logged-in charity
ORDER BY 
    t.created_at DESC;

";


$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param('i', $charity_id);

$stmt->execute();
$result = $stmt->get_result();
$total_donations = $result->num_rows;

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['donation_id'])) {
    $status = $_POST['status'];
    $donation_id = intval($_POST['donation_id']);
    
      $current_timestamp = date('Y-m-d H:i:s');
      $approved_at = ($status == 'approved') ? $current_timestamp : null;
      $rejected_at = ($status == 'rejected') ? $current_timestamp : null;

    // Update status in tbl_donations
    $update_stmt = $conn->prepare("UPDATE tbl_donations SET status = ? WHERE donation_id = ?");
    if ($update_stmt === false) {
        die('Error preparing SQL query: ' . $conn->error);
    }

    $update_stmt->bind_param("si", $status, $donation_id);
    $update_result = $update_stmt->execute();
    $update_stmt->close();

    // If updating tbl_donations was successful, update tbl_donation_transactions as well
    if ($update_result) {
            $update_transaction_stmt = $conn->prepare(
                "UPDATE tbl_donation_transactions 
                SET status = ?, updated_at = ?, approved_at = ?, rejected_at = ? 
                WHERE donation_id = ?"
            );
            if ($update_transaction_stmt === false) {
                die('Error preparing SQL query: ' . $conn->error);
            }
    
            $update_transaction_stmt->bind_param("ssssi", $status, $current_timestamp, $approved_at, $rejected_at, $donation_id);
            $update_transaction_stmt->execute();
            $update_transaction_stmt->close();
        // Refresh the page after successful update
        header("Location: " . $_SERVER['PHP_SELF'] . "?donator_id=" . $donator_id); // Refresh the page
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
    <title>Approved Donations</title>
    <link rel="stylesheet" href="../css/c-received.css"> <!-- Use same CSS file for layout -->
    <style>
        .pending { color: orange; font-weight: bold; }
        .approved { color: green; font-weight: bold; }
        .rejected { color: red; font-weight: bold; }
    </style>
</head>
<body>

<div class="dashboard-wrapper">
    <!-- LEFT COLUMN -->
    <div class="left-column">
        <h1 class="main">Charity Dashboard</h1>
        <h2>REQUESTS</h2>
        <h3>Total Pending Donations</h3>
        <h1 class="donation-count"><?php echo $total_donations; ?></h1>
    </div>

    <!-- RIGHT COLUMN -->
    <div class="right-column">
        <!-- Navigation -->
        <div class="navigation">
            <?php include('navigation_links.php'); ?>
        </div>

        <div class="form-wrapper">
            <?php
            if ($total_donations > 0) {
                $row = $result->fetch_assoc();
                echo "<h2>Donations Summary for Charity: " . htmlspecialchars($row['charity_name']) . "</h2>";

                echo "<table class='received-table'>
                        <tr>
                            <th>View</th>
                            <th>Donator Name</th>
                            <th>Quantity</th>
                            <th>Requested Date</th>
                            <th>Donation Status</th>
                            <th>Action</th>
                        </tr>";

                        do {
                            $status_display = ($row['status'] == 'pending') 
                                ? '<span class="pending">Pending</span>' 
                                : ($row['status'] == 'approved' ? '<span class="approved">Approved</span>' : '<span class="rejected">Rejected</span>');
                        ?>
                        <tr>
                            <td><a href='c-request_summary.php?transaction_id=<?= $row['transaction_id']; ?>'>View</a></td>
                            <td><?= htmlspecialchars($row['first_name']); ?></td>
                            <td><?= htmlspecialchars($row['total_donation']); ?></td>
                            <td><?= htmlspecialchars($row['created_at']); ?></td>
                            <td><?= $status_display; ?></td>
                            <td>
                                <?php if ($row['status'] == 'pending'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="donation_id" value="<?= $row['donation_id']; ?>">
                                        <input type="hidden" name="status" value="approved">
                                        <button type="submit" class="btn" style="background: #28a745;
            color: white;
            border: none;
            cursor: pointer;display: inline-block;
            padding: 8px 12px;border-radius: 5px;">Approve</button>
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="donation_id" value="<?= $row['donation_id']; ?>">
                                        <input type="hidden" name="status" value="rejected">
                                        <button type="submit" class="btn" style="background: #dc3545;
            color: white;
            border: none;
            cursor: pointer;display: inline-block;
            padding: 8px 12px;border-radius: 5px;">Reject</button>
                                    </form>
                                <?php else: ?>
                                    <em>No action available</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php
                        } while ($row = $result->fetch_assoc());
                        
                echo "</table>";
            } else {
                $query_charity_name = "SELECT charity_name FROM tbl_charity WHERE charity_id = ?";
                $stmt_name = $conn->prepare($query_charity_name);
                $stmt_name->bind_param('i', $charity_id);
                $stmt_name->execute();
                $result_name = $stmt_name->get_result();
                $charity_row = $result_name->fetch_assoc();

                echo "<h2>Donations Summary for Charity: " . htmlspecialchars($charity_row['charity_name']) . "</h2>";
                echo "<p>No pending donation requests found for this charity.</p>";

                $stmt_name->close();
            }

            $stmt->close();
            $conn->close();
            ?>
        </div>
    </div>
</div>

</body>
</html>
