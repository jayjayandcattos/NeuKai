<?php
session_start();
require('../configuration/db_connect.php');

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
AND i.status = 'pending' AND t.status = 'pending'
");
if ($stmt === false) {
    die('Error preparing SQL query: ' . $conn->error);
}

$stmt->bind_param("ii", $transaction_id, $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI - Pending Donation Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/loading.js" defer></script>
    <script src="../js/mobilenav.js" defer></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/invoice.css">
    <link rel="stylesheet" href="../css/donorpage.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<style>
    :root {
        --primary-color: #FF7F00;
        --secondary-color: #FF9E44;
        --accent-color: #FF5500;
        --background-color: #f8f9fa;
        --text-color: #212529;
        --border-color: #FFD8B8;
    }

    .status-pending {
        background-color: #FFEBCC;
        color: #CC4400;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        display: inline-block;
    }
</style>
</head>

<body>

    <!-- Navbar -->
    <?php include '../section/LoggedInDonorNavFolder.php'; ?>

    <!-- Mobile Menu -->
    <?php include '../section/LoggedInDonorNavMobileFolder.php'; ?>
    <div class="receipt-container">

        <div id="loading-overlay"
            class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
            <img src="../images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
        </div>

        <?php

        if ($result->num_rows > 0) {

            $row = $result->fetch_assoc();
            $first_row = $row;


            $result->data_seek(0);
        ?>
            <div class="receipt-header">
                <div class="invoice-label">Transaction #<?php echo htmlspecialchars($transaction_id); ?></div>
                <h2>Pending Donation Summary</h2>
            </div>

            <div class="receipt-body">
                <div class="transaction-info">
                    <p>
                        <span class="label">Charity:</span>
                        <span><?php echo htmlspecialchars($first_row['charity_name']); ?></span>
                    </p>
                    <p>
                        <span class="label">Donation Date:</span>
                        <span><?php echo htmlspecialchars($first_row['donation_date']); ?></span>
                    </p>
                    <p>
                        <span class="label">Status:</span>
                        <span class="status-pending">Pending</span>
                    </p>
                </div>

                <h3>Donated Items</h3>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Item Category</th>
                            <th>Quantity</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        do {
                            echo "<tr>
                                <td>" . htmlspecialchars($row['category']) . "</td>
                                <td>" . $row['quantity'] . "</td>
                                <td>";


                            if (!empty($row['image_path'])) {
                                echo "<img class='item-image' src='data:image/jpeg;base64," . base64_encode($row['image_path']) . "' alt='Donation Image' />";
                            } else {
                                echo "<p>No image available</p>";
                            }

                            echo "</td></tr>";
                        } while ($row = $result->fetch_assoc());
                        ?>
                    </tbody>
                </table>
                <div class="flex justify-center items-center">

                    <a href="d-profile.php" class="donor-btn">Back to Profile</a>
                </div>
            </div>

            <div class="receipt-footer">
                Thank you for your generous donation! Your items are awaiting approval.
            </div>
        <?php
        } else {
        ?>
            <div class="receipt-header">
                <h2>Pending Donation Summary</h2>
            </div>
            <div class="no-records">
                <p>No pending donation records or items found for this transaction.</p>
                <a href="d-profile.php" class="back-btn">Back to Profile</a>
            </div>
        <?php
        }
        ?>
    </div>


    <!-- Parallax Background -->
    <?php include '../section/donorparallax.php'; ?>
</body>

</html>