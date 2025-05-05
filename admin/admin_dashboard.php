
<?php
session_start();
require_once "../configuration/db_connect.php";

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch monthly donations for each charity (both approved and delivered)
$query = "
    SELECT 
        c.charity_name, 
        MONTH(dt.created_at) AS month, 
        COUNT(dt.transaction_id) AS total_donations
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.status IN ('approved', 'delivered')
    GROUP BY c.charity_name, month
    ORDER BY month;
";

$result = mysqli_query($conn, $query);

// Initialize data structure for chart
$charities = [];
$months = range(1, 12);

while ($row = mysqli_fetch_assoc($result)) {
    $charityName = $row['charity_name'];
    $month = (int) $row['month'];
    $donations = (int) $row['total_donations'];

    if (!isset($charities[$charityName])) {
        $charities[$charityName] = array_fill(1, 12, 0); // Months 1-12
    }
    $charities[$charityName][$month] = $donations;
}

// Helper function to count database rows
function countRows($conn, $table, $column) {
    $query = "SELECT COUNT($column) AS total FROM `$table`";
    $result = mysqli_query($conn, $query);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

// Helper function to count donations by status
function countDonationsByStatus($conn, $status) {
    $stmt = mysqli_prepare($conn, "SELECT COUNT(donation_id) AS total FROM tbl_donations WHERE status = ?");
    mysqli_stmt_bind_param($stmt, "s", $status);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    return mysqli_fetch_assoc($result)['total'] ?? 0;
}

// Get all counts
$donorCount = countRows($conn, 'tbl_donor', 'donator_id');
$charityCount = countRows($conn, 'tbl_charity', 'charity_id');
$deliveredCount = countDonationsByStatus($conn, 'delivered');
$approvedCount = countDonationsByStatus($conn, 'approved');
$totalDonations = $deliveredCount + $approvedCount;

mysqli_close($conn);
?>


<style>
    /* .dashboard-container {
        padding: 20px;
    } */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    .stat-card {
        background: white;
        border-radius: 10px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        text-align: center;
    }
    .stat-card h3 {
        margin: 0 0 10px;
        font-size: 16px;
        color: #555;
    }
    .stat-card p {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
        color: #000A19;
    }
    .css-chart {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-top: 20px;
    }
    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }
    .chart-bars {
        display: flex;
        height: 300px;
        align-items: flex-end;
        border-bottom: 2px solid #000A19;
        position: relative;
    }
    .month-column {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        height: 100%;
        position: relative;
    }
    .month-label {
        position: absolute;
        bottom: -25px;
        font-weight: bold;
        color: #000A19;
    }
    .bar-container {
        width: 80%;
        height: 100%;
        display: flex;
        align-items: flex-end;
        position: relative;
    }
    .charity-bar {
        width: 100%;
        transition: height 0.5s ease;
        position: absolute;
        bottom: 0;
    }
    .bar-value {
        position: absolute;
        top: -20px;
        width: 100%;
        text-align: center;
        font-size: 12px;
        font-weight: bold;
    }
    .chart-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-top: 30px;
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-right: 15px;
    }
    .legend-color {
        width: 16px;
        height: 16px;
        margin-right: 8px;
        border-radius: 3px;
    }
</style>

<div class="dashboard-container">
    <h2>ADMIN DASHBOARD</h2>
    <br>
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total Donors</h3>
            <p><?= htmlspecialchars($donorCount) ?></p>
        </div>
        <div class="stat-card">
            <h3>Total Charities</h3>
            <p><?= htmlspecialchars($charityCount) ?></p>
        </div>
        <div class="stat-card">
            <h3>Approved Donations</h3>
            <p><?= htmlspecialchars($approvedCount) ?></p>
        </div>
        <div class="stat-card">
            <h3>Delivered Donations</h3>
            <p><?= htmlspecialchars($deliveredCount) ?></p>
        </div>
    </div>

    <div class="css-chart">
        <div class="chart-header">
            <h3>Monthly Donations per Charity</h3>
        </div>
        
        <div class="chart-bars">
            <?php
            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            $colors = ['#4e79a7', '#f28e2b', '#e15759', '#76b7b2', '#59a14f', '#edc948'];
            
            // Find maximum donation count for scaling
            $maxDonation = 0;
            foreach ($charities as $charityData) {
                foreach ($charityData as $month => $count) {
                    if ($count > $maxDonation) {
                        $maxDonation = $count;
                    }
                }
            }
            
            // Add 20% padding to max value for better visualization
            $maxValue = $maxDonation * 1.2;
            
            foreach (range(1, 12) as $month) {
                echo '<div class="month-column">';
                echo '<div class="month-label">' . $monthNames[$month-1] . '</div>';
                echo '<div class="bar-container">';
                
                $colorIndex = 0;
                foreach ($charities as $charityName => $monthlyData) {
                    $count = $monthlyData[$month] ?? 0;
                    $height = $maxValue > 0 ? ($count / $maxValue * 100) : 0;
                    
                    echo '<div class="charity-bar" style="';
                    echo 'height: ' . $height . '%;';
                    echo 'background-color: ' . $colors[$colorIndex % count($colors)] . ';';
                    echo 'left: ' . ($colorIndex * (100 / count($charities))) . '%;';
                    echo 'width: ' . (100 / count($charities)) . '%;';
                    echo '">';
                    
                    if ($count > 0) {
                        echo '<div class="bar-value">' . $count . '</div>';
                    }
                    
                    echo '</div>';
                    $colorIndex++;
                }
                
                echo '</div></div>';
            }
            ?>
        </div>
        <br>
        <div class="chart-legend">
            <?php
            $colorIndex = 0;
            foreach ($charities as $charityName => $monthlyData) {
                echo '<div class="legend-item">';
                echo '<span class="legend-color" style="background-color: ' . $colors[$colorIndex % count($colors)] . '"></span>';
                echo '<span>' . htmlspecialchars($charityName) . '</span>';
                echo '</div>';
                $colorIndex++;
            }
            ?>
        </div>
    </div>
</div>