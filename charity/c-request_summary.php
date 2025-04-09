<?php
session_start();
require('../configuration/db_connect.php');

if (!isset($_GET['transaction_id'])) {
    die("No transaction specified.");
}
$transaction_id = $_GET['transaction_id'];

if (!isset($_SESSION['charity_id'])) {
   // die("User not logged in.");
    header("Location: ../login.php");
    exit();
}

$charity_id = $_SESSION['charity_id'];

$query = "
    SELECT 
        d.donator_id,
        d.first_name,
        items.category,
        items.quantity,
        items.image_path
    FROM 
        tbl_donation_transactions t
    JOIN 
        tbl_donor d ON t.donator_id = d.donator_id
    JOIN 
        tbl_donation_items items ON items.donation_id = t.donation_id
    WHERE 
        t.status = 'approved'
        AND t.charity_id = '$charity_id'
        AND t.transaction_id = '$transaction_id'
";

$result = $conn->query($query);

if (!$result) {
    die("Query failed: " . $conn->error);
}
if ($row = $result->fetch_assoc()) {

    echo "<h2>Donation Summary for " . htmlspecialchars($row['first_name']) . "</h2>";
    echo "<table class='summary-table'>
        <tr>
            <th>Item Category</th>
            <th>Quantity</th>
            <th>Image</th>
        </tr>";

    do {
        echo "<tr>
                <td>" . htmlspecialchars($row['category']) . "</td>
                <td>" . $row['quantity'] . "</td>
                <td>";

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
    echo "<a href='c-request.php'>Back</a>";
} else {
    echo "<p>No items found for this donator.</p>";
}
?>
