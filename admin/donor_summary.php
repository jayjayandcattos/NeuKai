<?php
session_start();
include '../configuration/db_connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if `donator_id` and `donation_id` are provided in the URL
if (!isset($_GET['donator_id']) || empty($_GET['donator_id']) || !isset($_GET['donation_id']) || empty($_GET['donation_id'])) {
    die("Donor ID or Donation ID is missing.");
}

$donator_id = intval($_GET['donator_id']);
$donation_id = intval($_GET['donation_id']);

// Step 1: Fetch donation items from tbl_donation_items based on the given donator_id and donation_id
$query_items = "
    SELECT di.item_id, di.donation_id, di.category, di.description, di.quantity, di.image_path
    FROM tbl_donation_items di
    JOIN tbl_donation_transactions dt ON di.donation_id = dt.donation_id
    WHERE di.donation_id = ? AND dt.donator_id = ?
";

$stmt_items = $conn->prepare($query_items);

if (!$stmt_items) {
    die("Query preparation failed: " . $conn->error);
}

// Bind the parameters
$stmt_items->bind_param("ii", $donation_id, $donator_id);

// Execute the statement
$stmt_items->execute();
$donations_items = $stmt_items->get_result();

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
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="donor_profile.php?donator_id=<?= $donator_id ?>">Back</a>
</body>
</html>
