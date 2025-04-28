<?php
session_start();
require_once "../configuration/db_connect.php"; // Database connection

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch monthly donations for each charity
$query = "
    SELECT 
        c.charity_name, 
        MONTH(dt.created_at) AS month, 
        COUNT(dt.transaction_id) AS total_donations
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.status = 'delivered'
    GROUP BY c.charity_name, month
    ORDER BY month;
";

$result = mysqli_query($conn, $query);

$charities = [];
$months = range(1, 12);
$data = [];

while ($row = mysqli_fetch_assoc($result)) {
    $charityName = $row['charity_name'];
    $month = (int)$row['month'];
    $donations = (int)$row['total_donations'];

    if (!isset($charities[$charityName])) {
        $charities[$charityName] = array_fill(0, 12, 0);
    }
    $charities[$charityName][$month - 1] = $donations;
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #333;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background: #575757;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
        }
        .chart-container {
            width: 70%;
            height: 500px;
            display: inline-block;
        }
        .legend-container {
            width: 25%;
            display: inline-block;
            vertical-align: top;
            padding-left: 20px;
        }
        .legend {
            list-style: none;
            padding: 0;
        }
        .legend li {
            margin-bottom: 10px;
            font-size: 14px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
    <h2 style="text-align: center;">Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="charity_list.php">Charity</a>
    <a href="donor_list.php" class="active">Donors</a>
    <a href="admin_list.php">Admins</a>
    <a href="admin_reset_request.php">Reset Requests</a>
    <a href="logout.php">Logout</a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1>Admin Dashboard</h1>
        <p>CHART/Monthly Donation per Charity</p>

        <div class="chart-container">
            <canvas id="donationChart"></canvas>
        </div>
        <div class="legend-container">
            <h3>Legend</h3>
            <ul class="legend" id="chartLegend"></ul>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('donationChart').getContext('2d');
            const monthLabels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            const charitiesData = <?php echo json_encode($charities); ?>;
            const colors = ["#FF5733", "#33FF57", "#3357FF", "#FF33A1", "#A133FF", "#FFC300", "#FF5733", "#FFBD33", "#57FF33", "#33FFBD"];

            const datasets = Object.keys(charitiesData).map((charity, index) => ({
                label: charity,
                backgroundColor: colors[index % colors.length],
                borderColor: colors[index % colors.length],
                borderWidth: 1,
                data: charitiesData[charity]
            }));

            const donationChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    indexAxis: 'x', // Ensures bars are **vertical**
                    plugins: {
                        legend: {
                            display: false // Hide default legend, use custom one
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Generate custom legend
            const legendContainer = document.getElementById("chartLegend");
            datasets.forEach((dataset, index) => {
                const legendItem = document.createElement("li");
                legendItem.innerHTML = `<span style="display:inline-block; width:12px; height:12px; background:${dataset.backgroundColor}; margin-right:10px;"></span> ${dataset.label}`;
                legendContainer.appendChild(legendItem);
            });
        });
    </script>

</body>
</html>