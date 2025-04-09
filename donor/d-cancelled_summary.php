<?php
session_start();
require('configuration/db_connect.php');

// Check if transaction_id is provided
if (!isset($_GET['transaction_id'])) {
    die("No transaction specified.");
}

$transaction_id = $_GET['transaction_id'];

// Check if the user (donator) is logged in
if (!isset($_SESSION['donator_id'])) {
    // Redirect if not logged in
    header("Location: login.php");
    exit();
}

$donator_id = $_SESSION['donator_id'];

// Fetch all necessary data in one query
$stmt = $conn->prepare("
SELECT 
    d.donation_name, 
    d.total_donation, 
    d.status AS donation_status, 
    d.donation_date, 
    c.charity_name, 
    i.category, 
    i.quantity, 
    i.image_path
FROM tbl_donations d
JOIN tbl_donation_transactions t ON d.donation_id = t.donation_id
JOIN tbl_charity c ON t.charity_id = c.charity_id
JOIN tbl_donation_items i ON d.donation_id = i.donation_id
WHERE t.transaction_id = ? AND d.donator_id = ? 
AND t.status = 'rejected'
");
if ($stmt === false) {
    die('Error preparing SQL query: ' . $conn->error);
}

$stmt->bind_param("ii", $transaction_id, $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// Check if any rows are returned
if ($result->num_rows > 0) {
    // Fetch the donator's first name (for a more personalized message)
    $row = $result->fetch_assoc();
    echo "<h2>Donation Summary for " . htmlspecialchars($row['charity_name']) . "</h2>";
    echo "<table class='summary-table'>
            <tr>
                <th>Donated To</th>
                <th>Donation Date</th>
                <th>Item Category</th>
                <th>Quantity</th>
                <th>Image</th>
            </tr>";

    // Loop through each donation and display its details
    do {
        echo "<tr>
                <td>" . htmlspecialchars($row['charity_name']) . "</td>
                <td>" . htmlspecialchars($row['donation_date']) . "</td>
                <td>" . htmlspecialchars($row['category']) . "</td>
                <td>" . $row['quantity'] . "</td>
                <td>";

        // Display the image if it exists
        if (!empty($row['image_path'])) {
            echo "<div>
                    <img src='data:image/jpeg;base64," . base64_encode($row['image_path']) . "' alt='Donation Image' width='100' height='100' />
                  </div>";
        } else {
            echo "<p>No image available.</p>";
        }

        echo "</td></tr>";
    } while ($row = $result->fetch_assoc());

    echo "</table>";

    echo "<a href='d-profile.php'>Back to Profile</a>";
} else {
    echo "<p>No donation records or items found for this transaction.</p>";
}
?>
