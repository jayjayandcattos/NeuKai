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

// First query: Get the donation details and charity name (non-repeating data)
$stmt = $conn->prepare("
SELECT 
    d.donation_id,
    d.donation_name, 
    d.total_donation, 
    d.status AS donation_status, 
    d.donation_date, 
    c.charity_name
FROM tbl_donations d
JOIN tbl_donation_transactions t ON d.donation_id = t.donation_id
JOIN tbl_charity c ON t.charity_id = c.charity_id
WHERE t.transaction_id = ? AND d.donator_id = ? 
AND t.status = 'rejected'
LIMIT 1
");

if ($stmt === false) {
    die('Error preparing SQL query: ' . $conn->error);
}

$stmt->bind_param("ii", $transaction_id, $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donation_data = $result->fetch_assoc();
$stmt->close();

// Only if we found donation data, fetch the donation items
$items = [];
if ($donation_data) {
    $donation_id = $donation_data['donation_id'];
    
    // Second query: Get all items for this donation
    $items_stmt = $conn->prepare("
    SELECT 
        category, 
        quantity, 
        image_path
    FROM tbl_donation_items
    WHERE donation_id = ?
    ");
    
    if ($items_stmt === false) {
        die('Error preparing items SQL query: ' . $conn->error);
    }
    
    $items_stmt->bind_param("i", $donation_id);
    $items_stmt->execute();
    $items_result = $items_stmt->get_result();
    
    // Store all items in an array
    while ($item = $items_result->fetch_assoc()) {
        $items[] = $item;
    }
    
    $items_stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI - Rejected Donation</title>
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
        --primary-color: #E53935;
        --secondary-color: #EF5350;
        --accent-color: #C62828;
        --background-color: #f8f9fa;
        --text-color: #212529;
        --border-color: #FFCDD2;
    }

    body {
        font-family: 'Poppins', sans-serif;
        margin: 0;
        padding: 0;
        background-color: var(--background-color);
        color: var(--text-color);
        line-height: 1.6;
    }

    .receipt-container {
        max-width: 900px;
        margin: 0 auto;
        background-color: white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        padding: 0;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 90%;

    }

    .receipt-header {
        background-color: var(--primary-color);
        color: white;
        padding: 25px 40px;
        position: relative;
    }

    .receipt-header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 600;
    }

    .invoice-label {
        position: absolute;
        top: 15px;
        right: 40px;
        font-size: 14px;
        background-color: rgba(255, 255, 255, 0.2);
        padding: 5px 15px;
        border-radius: 30px;
    }

    .receipt-body {
        padding: 30px 40px;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        border-radius: 5px;
        overflow: hidden;
    }

    .summary-table th {
        background-color: #FFEBEE;
        color: var(--text-color);
        text-align: left;
        padding: 15px;
        font-weight: 600;
        border-bottom: 2px solid var(--border-color);
    }

    .summary-table td {
        padding: 15px;
        border-bottom: 1px solid var(--border-color);
        vertical-align: middle;
    }

    .summary-table tr:last-child td {
        border-bottom: none;
    }

    .summary-table tr:nth-child(even) {
        background-color: #FFF5F5;
    }

    .item-image {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        display: block;
        border: 1px solid var(--border-color);
    }

    .back-btn {
        display: inline-block;
        margin-top: 20px;
        background-color: var(--primary-color);
        color: white;
        padding: 10px 20px;
        text-decoration: none;
        border-radius: 5px;
        font-weight: 500;
        transition: background-color 0.3s ease;
        text-transform: uppercase;
        letter-spacing: 1px;
        box-shadow: 0 4px 6px rgba(229, 57, 53, 0.2);
    }

    .back-btn:hover {
        background-color: var(--accent-color);
        transform: translateY(-2px);
    }

    .receipt-footer {
        padding: 20px 40px;
        background-color: #FFEBEE;
        border-top: 1px solid var(--border-color);
        text-align: center;
        font-size: 14px;
        color: var(--accent-color);
    }

    .no-records {
        padding: 40px;
        text-align: center;
        color: var(--secondary-color);
    }

    .status-rejected {
        background-color: #FFEBEE;
        color: #B71C1C;
        padding: 5px 10px;
        border-radius: 4px;
        font-size: 14px;
        display: inline-block;
    }

    .transaction-info {
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 1px dashed var(--border-color);
    }

    .transaction-info p {
        margin: 5px 0;
        display: flex;
        justify-content: space-between;
    }

    .transaction-info span.label {
        font-weight: 600;
        color: var(--accent-color);
    }

    @media screen and (max-width: 768px) {
        .receipt-container {
            position: relative;
            top: auto;
            left: auto;
            transform: none;
            margin: 5rem auto;
            width: 90%;
        }

        .receipt-header {
            padding: 20px;
        }

        .receipt-body {
            padding: 20px;
        }

        .summary-table {
            display: block;
            overflow-x: auto;
        }

        .summary-table th,
        .summary-table td {
            padding: 12px 10px;
        }

        .receipt-footer {
            padding: 15px;
        }

        .invoice-label {
            position: static;
            display: inline-block;
            margin-top: 10px;
        }
    }

    @media screen and (max-width: 480px) {
        .receipt-header h2 {
            font-size: 20px;
        }

        .item-image {
            width: 60px;
            height: 60px;
        }
    }

    .donor-btn {
        display: flex;
        align-content: center;
        width: full;
        height: 50%;
        padding: 0.25rem 0.5rem;
        display: inline-block;
        font-size: 20px;
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

        <?php if ($donation_data): ?>
            <div class="receipt-header">
                <div class="invoice-label">Transaction #<?php echo htmlspecialchars($transaction_id); ?></div>
                <h2>Rejected Donation Summary</h2>
            </div>

            <div class="receipt-body">
                <div class="transaction-info">
                    <p>
                        <span class="label">Charity:</span>
                        <span><?php echo htmlspecialchars($donation_data['charity_name']); ?></span>
                    </p>
                    <p>
                        <span class="label">Donation Date:</span>
                        <span><?php echo htmlspecialchars($donation_data['donation_date']); ?></span>
                    </p>
                    <p>
                        <span class="label">Status:</span>
                        <span class="status-rejected">Rejected</span>
                    </p>
                </div>

                <h3>Donation Items</h3>
                <table class="summary-table">
                    <thead>
                        <tr>
                            <th>Item Category</th>
                            <th>Quantity</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['category']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>
                                    <?php if (!empty($item['image_path'])): ?>
                                        <img class='item-image' src='data:image/jpeg;base64,<?php echo base64_encode($item['image_path']); ?>' alt='Donation Image' />
                                    <?php else: ?>
                                        <p>No image available</p>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="flex justify-center items-center">
                    <a href="d-profile.php" class="donor-btn">Back to Profile</a>
                </div>
            </div>

            <div class="receipt-footer">
                We're sorry, but this donation has been rejected. Please contact the charity for more information.
            </div>
        <?php else: ?>
            <div class="receipt-header">
                <h2>Rejected Donation</h2>
            </div>
            <div class="no-records">
                <p>No rejected donation records found for this transaction.</p>
                <a href="d-profile.php" class="back-btn">Back to Profile</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Parallax Background -->
    <?php include '../section/donorparallax.php'; ?>
</body>

</html>