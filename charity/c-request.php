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
        t.created_at,
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
        t.created_at DESC;
";

$stmt = $conn->prepare($query);
$stmt->bind_param('i', $charity_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<a href='charity_dashboard.php'>Profile</a>
<a href='c-received.php'>Received</a>
<a href='c-request.php'>Request</a>";

if ($result->num_rows > 0) {
   
    $row = $result->fetch_assoc();  
    echo "<h2>Donations Summary for Charity: " . htmlspecialchars($row['charity_name']) . "</h2>";  

    echo "<table class='request-table'>
        <tr>
            <th>View</th>
            <th>Donator Name</th>
            <th>Received Date</th>
        </tr>";

    do {
        echo "<tr>
                <td><a href='c-request_summary.php?transaction_id=" . $row['transaction_id'] . "'>View</a></td>
                <td>" . htmlspecialchars($row['first_name']) . "</td>
                <td>" . htmlspecialchars($row['created_at']) . "</td>
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
    echo "<p>No donations request found for this charity.</p>";


    $stmt_name->close();
}

echo "<div class='logout-container'>
        <a href='../logout.php'>
            <span>Logout</span>
        </a>
      </div>";

$stmt->close();
$conn->close();
?>
