<?php
session_start();
require('../configuration/db_connect.php');

if (!isset($_SESSION['charity_id'])) {
    header("Location: ../login.php");
    exit();
}

$charity_id = $_SESSION['charity_id'];  

$query = "
    SELECT 
        d.donator_id,
        d.first_name, 
        t.transaction_id,
        t.approved_at,
        c.charity_name
    FROM 
        tbl_donation_transactions t
    JOIN 
        tbl_donor d ON t.donator_id = d.donator_id
    JOIN
        tbl_charity c ON t.charity_id = c.charity_id
    WHERE 
        t.status = 'approved'
        AND t.charity_id = ? 
    ORDER BY 
        t.approved_at DESC;
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $charity_id);
$stmt->execute();
$result = $stmt->get_result();

// Count the total number of delivered donations
$total_approved = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donations Summary</title>
    <link rel="stylesheet" href="../css/c-received.css">
</head>
<body>

<div class="dashboard-wrapper">
    <!-- LEFT COLUMN -->
    <div class="left-column">
        <h1 class="main">Charity Dashboard</h1>
        <h2>APPROVED</h2>
        <h3>Total Approved Donations</h3>
        <h1 class="donation-count"><?php echo $total_approved; ?></h1> 
    </div>

    <!-- RIGHT COLUMN -->
    <div class="right-column">
        <div class="navigation">
            <?php include('navigation_links.php'); ?>
        </div>

        <div class="form-wrapper">
        <?php
        if ($total_approved > 0) {
            $row = $result->fetch_assoc();  
            echo "<h2>Donations Summary for Charity: " . htmlspecialchars($row['charity_name']) . "</h2>"; 

            echo "<table class='received-table'>
                <tr>
                    <th>View</th>
                    <th>Donator Name</th>
                    <th>Approved Date</th>
                </tr>";

            do {
                echo "<tr>
                        <td><a href='c-approved_summary.php?transaction_id=" . $row['transaction_id'] . "'>View</a></td>
                        <td>" . htmlspecialchars($row['first_name']) . "</td>
                        <td>" . htmlspecialchars($row['approved_at']) . "</td>
                      </tr>";
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
            echo "<p>No donations approved found for this charity.</p>";

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
