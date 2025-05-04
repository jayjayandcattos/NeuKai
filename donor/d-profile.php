<?php
session_start();
require('../configuration/db_connect.php');


if (!isset($_SESSION['donator_id'])) {
    header("Location: login.php");
    exit();
}

$donator_id = $_SESSION['donator_id'];

// Fetch donor details
$stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param('i', $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donator = $result->fetch_assoc();

// Check if data exists
if (!$donator) {
    // Handle case where the donor data is not found
    die("Donor not found.");
}

// Fetch completed donation transactions for the donor, including charity name
$completed_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.delivered_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'delivered'
";
$completed_donation_stmt = $conn->prepare($completed_donation_query);
$completed_donation_stmt->bind_param('i', $donator_id);
$completed_donation_stmt->execute();
$completed_donation_result = $completed_donation_stmt->get_result();

// Fetch pending donation transactions for the donor, including charity name
$pending_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.created_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'pending'
";
$pending_donation_stmt = $conn->prepare($pending_donation_query);
$pending_donation_stmt->bind_param('i', $donator_id);
$pending_donation_stmt->execute();
$pending_donation_result = $pending_donation_stmt->get_result();

$cancelled_donation_query = "
    SELECT dt.transaction_id, dt.charity_id, dt.rejected_at, c.charity_name 
    FROM tbl_donation_transactions dt
    JOIN tbl_charity c ON dt.charity_id = c.charity_id
    WHERE dt.donator_id = ? AND dt.status = 'rejected'
";
$cancelled_donation_stmt = $conn->prepare($cancelled_donation_query);
$cancelled_donation_stmt->bind_param('i', $donator_id);
$cancelled_donation_stmt->execute();
$cancelled_donation_result = $cancelled_donation_stmt->get_result();
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../js/navbarScroll.js" defer></script>
    <script src="../js/slideAnimation.js" defer></script>
    <script src="../js/loading.js" defer></script>
    <script src="../js/mobilenav.js" defer></script>
    <script src="../js/donorprofilekeverlu.js" defer></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/success.css">
    <link rel="stylesheet" href="../css/donorpage.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>


<body class="relative min-h-screen bg-black text-white font-poppins">

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
    <?php include '../section/LoggedInDonorNavFolder.php'; ?>

    <!-- Mobile Menu -->
    <?php include '../section/LoggedInDonorNavMobileFolder.php'; ?>

    <div class="container overflow-hidden overflow-y-hidden mt-[-20px]">
        <div class="sidebar">
            <div class="profile-section">
                <div class="user-info">
                    <div class="user-name">
                        <img src="../images/signin.svg" alt="Profile Icon" class="rounded-full">
                        <span>
                            <?php
                            echo htmlspecialchars($donator['first_name']) . ' ' .
                            //   (!empty($donator['middle_name']) ? htmlspecialchars($donator['middle_name']) . ' ' : '') . //COMMENT OUT KO MUNA, DI S'YA AESTHETICALLY PLEASING PAG KASAMA MIDDLE NAME T_T
                                htmlspecialchars($donator['last_name']);
                            ?>
                        </span>
                    </div>
                    <div class="user-detail">
                        <img src="../images/email.svg" alt="Email Icon">
                        <span><?php echo htmlspecialchars($donator['email']); ?></span>
                    </div>
                    <div class="user-detail">
                        <img src="../images/call.svg" alt="Phone Icon">
                        <span><?php echo htmlspecialchars($donator['contact_no']); ?></span>
                    </div>
                    <a href="edit_profile.php" class="edit-profile">
                        <img src="../images/orangepen.svg" alt="Edit Icon" class="w-6 h-6">
                        <span>Edit Profile</span>
                    </a>
                </div>
            </div>
            <form action="../logout.php" method="post">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>

        <div class="content">
            <div class="header-container">
                <div class="header">
                    <div class="title">Donation History</div>
                <div class="navigation-container">
                    <div class="tab-buttons">
                        <button class="tab-button active" onclick="showTab('completed')">Completed</button>
                        <button class="tab-button" onclick="showTab('pending')">Pending</button>
                        <button class="tab-button" onclick="showTab('cancelled')">Canceled</button>
                    </div>
                    <a href="d-donate.php" class="back-btn">
                        <svg width="16" height="16" viewBox="0 0 24 24">
                            <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" fill="#333" />
                        </svg>
                        Donation Page
                    </a>
                </div>
            </div>

            <div id="completed" class="tab-content active">
                <?php
                if ($completed_donation_result->num_rows > 0) {
                    echo '<table class="donation-table">
                            <tr>
                                <th>View</th>
                                <th>Charity Name</th>
                                <th>Completed Date</th>
                            </tr>';

                    while ($donation = $completed_donation_result->fetch_assoc()) {
                        echo '<tr>
                                <td>
                                    <a href="d-completed_summary.php?transaction_id=' . htmlspecialchars($donation['transaction_id']) . '" class="view-link">
                                        <svg width="20" height="20" viewBox="0 0 24 24" class="view-icon">
                                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" fill="#999" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                                <td>' . htmlspecialchars($donation['charity_name']) . '</td>
                                <td>' . htmlspecialchars($donation['delivered_at']) . '</td>
                            </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>No completed donations found.</p>';
                }
                ?>
            </div>

            <div id="pending" class="tab-content">
                <?php
                if ($pending_donation_result->num_rows > 0) {
                    echo '<table class="donation-table">
                            <tr>
                                <th>View</th>
                                <th>Charity Name</th>
                                <th>Requested Date</th>
                            </tr>';

                    while ($donation = $pending_donation_result->fetch_assoc()) {
                        echo '<tr>
                                <td>
                                    <a href="d-pending_summary.php?transaction_id=' . htmlspecialchars($donation['transaction_id']) . '" class="view-link">
                                        <svg width="20" height="20" viewBox="0 0 24 24" class="view-icon">
                                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" fill="#999" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                                <td>' . htmlspecialchars($donation['charity_name']) . '</td>
                                <td>' . htmlspecialchars($donation['created_at']) . '</td>
                            </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>No pending donations found.</p>';
                }
                ?>
            </div>

            <div id="cancelled" class="tab-content">
                <?php
                if ($cancelled_donation_result->num_rows > 0) {
                    echo '<table class="donation-table">
                            <tr>
                                <th>View</th>
                                <th>Charity Name</th>
                                <th>Cancelled Date</th>
                            </tr>';

                    while ($donation = $cancelled_donation_result->fetch_assoc()) {
                        echo '<tr>
                                <td>
                                    <a href="d-cancelled_summary.php?transaction_id=' . htmlspecialchars($donation['transaction_id']) . '" class="view-link">
                                        <svg width="20" height="20" viewBox="0 0 24 24" class="view-icon">
                                            <path d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" fill="#999" />
                                        </svg>
                                        View
                                    </a>
                                </td>
                                <td>' . htmlspecialchars($donation['charity_name']) . '</td>
                                <td>' . htmlspecialchars($donation['rejected_at']) . '</td>
                            </tr>';
                    }

                    echo '</table>';
                } else {
                    echo '<p>No cancelled donations found.</p>';
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Parallax Background -->
    <?php include '../section/donorparallax.php'; ?>
</body>

</html>