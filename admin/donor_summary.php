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
<head>
<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link rel="stylesheet" href="a_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style> 

       body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: var(--background-color);
    color: var(--text-color);
    line-height: 1.6;
    font-size: 16px;
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
    max-height: 90vh;
    overflow-y: auto;
}
.receipt-header {
    color: black;
    padding: 20px;
    position: relative;
}

.receipt-header h2 {
    margin: 0;
    font-size: 30px;
    font-weight: 600;
    text-align: center;
    color: #007bff;
    /* background-color:rgb(0, 67, 104); */
    border-bottom: 2px solid #007bff;

}
.receipt-body {
    padding: 20px;
}

.summary-table {
    width: 100%;
    border-radius: 5px;
    overflow: hidden;
    background-color:#ffffff;
}

th {
    width: 900px;
}

.summary-table th {
    background-color:#ffffff;
    color: var(--text-color);
    text-align: center;
    padding: 12px 10px;
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

.summary-table td {
    padding: 12px 10px;
    border-bottom: 1px solid var(--border-color);
    vertical-align: middle;
    word-break: break-word;
}

.summary-table tr:last-child td {
    border-bottom: none;
}

.summary-table tr:nth-child(even) {
    background-color: rgb(255, 255, 255);
}

.summary-table tr:hover {
    background-color: rgb(240, 240, 240); /* Gray-colored hover effect */
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
    background-color:rgba(0, 68, 104, 0.4);
    color: white;
    padding: 10px 15px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: 500;
    transition: background-color 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 1px;
    font-size: 14px;
    text-align: center;
}

.back-btn:hover {
    background-color: rgb(78, 142, 198);
    transform: translateY(-2px);
    transition: all 0.3s ease;
}

.receipt-footer {
    padding: 15px;
    border-top: 1px solid var(--border-color);
    text-align: center;
    font-size: 13px;
    color: var(--accent-color);
}
.transaction-info {
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px dashed var(--border-color);
}

.transaction-info p {
    margin: 5px 0;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 5px;
}

.transaction-info span.label {
    font-weight: 600;
    color: var(--accent-color);
}
    </style>
    </style>
</head>
<body>
<div class="receipt-container">
    <div class="receipt-header">
    <h2>Donation Items</h2>
    </div>
<div class="receipt-body">
    <div class="summary-table">
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
                                <img src='data:image/jpeg;base64," . base64_encode($donation_item['image_path']) . "' alt='Item Image' class='item-image' />
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
    </div>
</div>
    <div class="receipt-footer">
    <a class="back-btn" href="donor_profile.php?donator_id=<?= $donator_id ?>">Back</a>
    </div>
</body>
</html>
