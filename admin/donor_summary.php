<?php
session_start();
include '../configuration/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['donator_id']) || empty($_GET['donator_id'])) {
    die("Donor ID is missing.");
}

$donator_id = intval($_GET['donator_id']);

// Step 1: Fetch all the donation_ids made by the given donator_id from tbl_donation_transactions
$query = "
    SELECT DISTINCT dt.donation_id
    FROM tbl_donation_transactions dt
    WHERE dt.donator_id = ?
";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $donator_id);
$stmt->execute();
$result = $stmt->get_result();

// Step 2: Collect all donation_ids for the given donator_id
$donation_ids = [];
while ($row = $result->fetch_assoc()) {
    $donation_ids[] = $row['donation_id'];
}

if (empty($donation_ids)) {
    die("No donations found for this donor.");
}

// Step 3: Fetch donation items from tbl_donation_items that belong to the donations made by the donator
$donation_ids_placeholder = implode(',', array_fill(0, count($donation_ids), '?'));
$query_items = "
    SELECT di.item_id, di.donation_id, di.category, di.description, di.quantity, di.image_path, di.status
    FROM tbl_donation_items di
    WHERE di.donation_id IN ($donation_ids_placeholder)
";

$stmt_items = $conn->prepare($query_items);

if (!$stmt_items) {
    die("Query preparation failed: " . $conn->error);
}
$stmt_items->bind_param(str_repeat('i', count($donation_ids)), ...$donation_ids);
$stmt_items->execute();
$donations_items = $stmt_items->get_result();

if (isset($_GET['approve']) || isset($_GET['reject'])) {
    $item_id = $_GET['approve'] ?? $_GET['reject'] ?? null;
    $status = isset($_GET['approve']) ? 'approved' : 'rejected';
    $donation_id = $_GET['donation_id'] ?? null;

    if (!$donation_id) {
        die("Donation ID is missing.");
    }

    // Update item status
    $query = "UPDATE tbl_donation_items SET status = ? WHERE item_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $status, $item_id);
    if ($stmt->execute()) {
        echo ucfirst($status) . " successful!";
    } else {
        echo "Failed to update item status.";
    }

    // If the item is rejected, update donation and transaction statuses
    if ($status == 'rejected') {
        // Update the status of the entire donation to 'rejected'
        $query_update_donation = "UPDATE tbl_donations SET status = 'rejected' WHERE donation_id = ?";
        $stmt_update_donation = $conn->prepare($query_update_donation);
        $stmt_update_donation->bind_param("i", $donation_id);
        $stmt_update_donation->execute();

        // Update the status of the donation transaction to 'rejected'
        $query_update_transaction = "UPDATE tbl_donation_transactions SET status = 'rejected' WHERE donation_id = ?";
        $stmt_update_transaction = $conn->prepare($query_update_transaction);
        $stmt_update_transaction->bind_param("i", $donation_id);
        $stmt_update_transaction->execute();

        header("Location: donor_profile.php?donator_id=" . $donator_id);
        exit(); // Stop further execution
    }

    // if all items in the donation are approved
    if ($status == 'approved') {
        $query_approved_count = "SELECT COUNT(*) AS approved_count FROM tbl_donation_items WHERE donation_id = ? AND status = 'approved'";
        $stmt_approved_count = $conn->prepare($query_approved_count);
        $stmt_approved_count->bind_param("i", $donation_id);
        $stmt_approved_count->execute();
        $result_approved_count = $stmt_approved_count->get_result();
        $row_approved_count = $result_approved_count->fetch_assoc();

        $query_total_items = "SELECT COUNT(*) AS total_count FROM tbl_donation_items WHERE donation_id = ?";
        $stmt_total_items = $conn->prepare($query_total_items);
        $stmt_total_items->bind_param("i", $donation_id);
        $stmt_total_items->execute();
        $result_total_items = $stmt_total_items->get_result();
        $row_total_items = $result_total_items->fetch_assoc();

        if ($row_approved_count['approved_count'] == $row_total_items['total_count']) {
            $query_update_donation = "UPDATE tbl_donations SET status = 'approved' WHERE donation_id = ?";
            $stmt_update_donation = $conn->prepare($query_update_donation);
            $stmt_update_donation->bind_param("i", $donation_id);
            $stmt_update_donation->execute();

            $query_update_transaction = "UPDATE tbl_donation_transactions SET status = 'approved' WHERE donation_id = ?";
            $stmt_update_transaction = $conn->prepare($query_update_transaction);
            $stmt_update_transaction->bind_param("i", $donation_id);
            $stmt_update_transaction->execute();

            header("Location: donor_profile.php?donator_id=" . $donator_id);
            exit(); 
        } else {
            header("Location: donor_summary.php?donator_id=" . $donator_id);
            exit(); 
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Items</title>
</head>
<body>
    <h2>Donation Items</h2>
    <table> 
        <thead>
            <tr>
                <th>Image</th>
                <th>Quantity</th>
                <th>Description</th>
                <th>Category</th>
                <th>Item Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($donation_item = $donations_items->fetch_assoc()): ?>
            <tr>
                <td>
                <?php if (!empty($donation_item['image_path'])) {
                    echo "<div>
                            <img src='data:image/jpeg;base64," . base64_encode($donation_item['image_path']) . "' alt='Item Image' width='100' height='100' />
                        </div>";
                } else {
                    echo "<p>No image available.</p>";
                }
                ?>
                </td>
                <td><?= htmlspecialchars($donation_item['quantity']) ?></td>
                <td><?= htmlspecialchars($donation_item['description']) ?></td>
                <td><?= htmlspecialchars($donation_item['category']) ?></td>
                <td><?= htmlspecialchars($donation_item['status']) ?></td>
                <td>
                    <a href="donor_summary.php?approve=<?= $donation_item['item_id'] ?>&donator_id=<?= $donator_id ?>&donation_id=<?= $donation_item['donation_id'] ?>" onclick="return confirm('Approve this item?')">Approve</a>
                    <a href="donor_summary.php?reject=<?= $donation_item['item_id'] ?>&donator_id=<?= $donator_id ?>&donation_id=<?= $donation_item['donation_id'] ?>" onclick="return confirm('Reject this item?')">Reject</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="donor_profile.php?donator_id=<?= $donator_id ?>">Back</a>
</body>
</html>
