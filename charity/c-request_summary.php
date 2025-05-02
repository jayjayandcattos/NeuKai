<?php
session_start();
require('../configuration/db_connect.php');

if (!isset($_GET['transaction_id'])) {
    die("No transaction specified.");
}
$transaction_id = $_GET['transaction_id'];

if (!isset($_SESSION['charity_id'])) {
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
        t.status = 'pending'
        AND t.charity_id = '$charity_id'
        AND t.transaction_id = '$transaction_id'
";

$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI - Pending Donation Summary</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/charityinvoice.css">
    <link rel="stylesheet" href="../css/donorpage.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>


<style>
    .back-btn {
        background-color: #0000FF;
        color: white;
    }

    .back-btn:hover {
        background-color: #200ADF;
    }
</style>

<body>

    <?php include '../section/LoggedInCharityNav.php'; ?>

    <div class="receipt-container">
        <div class="receipt-header">
            <h2>Donation Summary</h2>
            <div class="invoice-label">Pending</div>
        </div>

        <?php
        if (!$result) {
            die("<div class='receipt-body'><p class='no-records'>Query failed: " . $conn->error . "</p></div>");
        }

        if ($row = $result->fetch_assoc()) {
        ?>
            <div class="receipt-body">
                <div class="transaction-info">
                    <p>
                        <span class="label">Transaction ID:</span>
                        <span><?php echo htmlspecialchars($_GET['transaction_id']); ?></span>
                    </p>
                    <p>
                        <span class="label">Donor Name:</span>
                        <span><?php echo htmlspecialchars($row['first_name']); ?></span>
                    </p>
                    <p>
                        <span class="label">Donor ID:</span>
                        <span><?php echo htmlspecialchars($row['donator_id']); ?></span>
                    </p>
                    <p>
                        <span class="label">Date:</span>
                        <span><?php echo date("F j, Y"); ?></span>
                    </p>
                </div>

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
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['category']) . '</td>';
                            echo '<td>' . $row['quantity'] . '</td>';
                            echo '<td>';

                            if (!empty($row['image_path'])) {
                                echo '<img class="item-image" src="data:image/jpeg;base64,' . base64_encode($row['image_path']) . '" alt="Donation Image" />';
                            } else {
                                echo '<p>No image available</p>';
                            }

                            echo '</td>';
                            echo '</tr>';
                        } while ($row = $result->fetch_assoc());
                        ?>
                    </tbody>
                </table>
                <div class="button-container">
                    <a href="c-request.php" class="back-btn">Back to Requests</a>
                </div>
            </div>
        <?php
        } else {
            echo "<div class='receipt-body'><p class='no-records'>No items found for this donation.</p></div>";
        }
        ?>


    </div>

    <?php include '../section/donorparallax.php'; ?>

</body>

</html>