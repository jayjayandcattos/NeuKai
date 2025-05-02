<?php
session_start();
require('../configuration/db_connect.php');
date_default_timezone_set('Asia/Manila');
if (!isset($_SESSION['charity_id'])) {
    header("Location: ../login.php");
    exit();
}

$charity_id = $_SESSION['charity_id'];

$query = "
    SELECT 
        d.donator_id,
        d.first_name, 
        ds.donation_id,
        ds.total_donation,
        ds.status,
        t.transaction_id,
        t.created_at,
        c.charity_name
    FROM 
        tbl_donation_transactions t
    JOIN 
        tbl_donor d ON t.donator_id = d.donator_id
    JOIN 
        tbl_donations ds ON t.donation_id = ds.donation_id
    JOIN
        tbl_charity c ON t.charity_id = c.charity_id
    WHERE 
        t.status = 'pending'
        AND t.charity_id = ?
    ORDER BY 
        t.created_at DESC";

$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Prepare failed: (" . $conn->errno . ") " . $conn->error);
}

$stmt->bind_param('i', $charity_id);

$stmt->execute();
$result = $stmt->get_result();
$total_donations = $result->num_rows;

// Get charity name for display
$query_charity_name = "SELECT charity_name FROM tbl_charity WHERE charity_id = ?";
$stmt_name = $conn->prepare($query_charity_name);
$stmt_name->bind_param('i', $charity_id);
$stmt_name->execute();
$result_name = $stmt_name->get_result();
$charity_row = $result_name->fetch_assoc();
$charity_name = $charity_row['charity_name'];
$stmt_name->close();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['status']) && isset($_POST['donation_id'])) {
    $status = $_POST['status'];
    $donation_id = intval($_POST['donation_id']);

    $current_timestamp = date('Y-m-d H:i:s');
    $approved_at = ($status == 'approved') ? $current_timestamp : null;
    $rejected_at = ($status == 'rejected') ? $current_timestamp : null;

    // Update status in tbl_donations
    $update_stmt = $conn->prepare("UPDATE tbl_donations SET status = ? WHERE donation_id = ?");
    if ($update_stmt === false) {
        die('Error preparing SQL query: ' . $conn->error);
    }

    $update_stmt->bind_param("si", $status, $donation_id);
    $update_result = $update_stmt->execute();
    $update_stmt->close();

    // If updating tbl_donations was successful, update tbl_donation_transactions as well
    if ($update_result) {
        $update_transaction_stmt = $conn->prepare(
            "UPDATE tbl_donation_transactions 
            SET status = ?, updated_at = ?, approved_at = ?, rejected_at = ? 
            WHERE donation_id = ?"
        );
        if ($update_transaction_stmt === false) {
            die('Error preparing SQL query: ' . $conn->error);
        }

        $update_transaction_stmt->bind_param("ssssi", $status, $current_timestamp, $approved_at, $rejected_at, $donation_id);
        $update_transaction_stmt->execute();
        $update_transaction_stmt->close();

        // Fixed redirect - no need for donator_id parameter
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } else {
        echo "Error updating status in donations.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI - Charity Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        neukai: {
                            50: '#EFF6FF',
                            100: '#DBEAFE',
                            200: '#BFDBFE',
                            300: '#93C5FD',
                            400: '#60A5FA',
                            500: '#3B82F6',
                            600: '#2563EB',
                            700: '#1D4ED8',
                            800: '#1E40AF',
                            900: '#1E3A8A'
                        }
                    },
                    fontFamily: {
                        poppins: ['Poppins', 'sans-serif'],
                        rubik: ['Rubik Mono One', 'monospace']
                    }
                }
            }
        }
    </script>
    <script src="../js/slideAnimation.js" defer></script>
    <script src="../js/loading.js" defer></script>
    <script src="../js/mobilenav.js" defer></script>
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/charity_dashboard.css">
    <link rel="stylesheet" href="../css/navigation_links.css">
</head>

<body class="bg-white text-gray-800 font-poppins min-h-screen flex flex-col">
    <!-- Loading Overlay -->
    <div id="loading-overlay"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="w-32 h-32 animate-pulse" />
    </div>

    <!-- Navbar -->
    <?php include '../section/LoggedInCharityNav.php'; ?>

    <!-- Main Content -->
    <main class="flex-grow p-3 md:p-6 flex justify-center">
        <div class="w-full max-w-6xl">
            <!-- Page Header -->
            <div class="text-center mb-6">
                <h1 class="text-3xl font-bold text-neukai-700 mb-2">Charity Dashboard</h1>
                <p class="text-white shadow-black">Welcome to <?php echo htmlspecialchars($charity_name); ?>'s dashboard</p>
            </div>

            <!-- Dashboard Content -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">

                <!-- Stats Cards Row -->
                <div class="md:col-span-12 grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                    <!-- Pending Donations Card -->
                    <div class="bg-white rounded-xl p-4 shadow border border-gray-100 flex items-center">
                        <div class="rounded-full bg-neukai-100 p-3 mr-4">
                            <i class="fas fa-clock text-xl text-neukai-600"></i>
                        </div>
                        <div>
                            <h2 class="text-sm text-gray-500 font-medium">Pending Donations</h2>
                            <p class="text-2xl font-bold text-neukai-800"><?php echo $total_donations; ?></p>
                        </div>
                    </div>

                    <!-- Charity Info Card -->
                    <div class="bg-white rounded-xl p-4 shadow border border-gray-100 flex items-center">
                        <div class="rounded-full bg-neukai-100 p-3 mr-4">
                            <i class="fas fa-building-ngo text-xl text-neukai-600"></i>
                        </div>
                        <div>
                            <h2 class="text-sm text-gray-500 font-medium">Charity Name</h2>
                            <p class="text-lg font-semibold text-neukai-800 truncate">
                                <?php echo htmlspecialchars($charity_name); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Main Content Container -->
                <div class="md:col-span-12">
                    <div class="md:col-span-12 bg-white rounded-xl shadow border border-gray-100 overflow-hidden h-[400px] flex-grow">
                        <!-- Navigation Tabs -->
                        <div class="border-b border-gray-200 px-4">
                            <nav class="flex overflow-x-auto hide-scrollbar py-3">
                                <?php include('navigation_links.php'); ?>
                            </nav>
                        </div>



                        <?php if ($total_donations > 0): ?>
                            <div class="px-4 pt-4">
                                <h2 class='text-xl font-bold text-neukai-700 mb-4'>Request Summary for Charity: <?php echo htmlspecialchars($charity_name); ?></h2>
                            </div>
                            <div class="overflow-x-auto -mx-4 sm:mx-0">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                View
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Donor
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Quantity
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Date
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Status
                                            </th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Action
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <a href="c-request_summary.php?transaction_id=<?= $row['transaction_id']; ?>"
                                                        class="text-neukai-600 hover:text-neukai-800 font-medium">
                                                        <i class="fas fa-eye mr-1"></i> View
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <?= htmlspecialchars($row['first_name']); ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <?= htmlspecialchars($row['total_donation']); ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <?= date('M d, Y h:i A', strtotime($row['created_at'])); ?>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        Pending
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 whitespace-nowrap text-sm">
                                                    <div class="flex flex-col sm:flex-row gap-2">
                                                        <form method="POST">
                                                            <input type="hidden" name="donation_id" value="<?= $row['donation_id']; ?>">
                                                            <input type="hidden" name="status" value="approved">
                                                            <button type="submit"
                                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-green-200 bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                                                                <i class="fas fa-check mr-1"></i> Approve
                                                            </button>
                                                        </form>

                                                        <form method="POST">
                                                            <input type="hidden" name="donation_id" value="<?= $row['donation_id']; ?>">
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="submit"
                                                                class="inline-flex items-center px-2.5 py-1.5 text-xs font-medium rounded border border-red-200 bg-red-100 text-red-800 hover:bg-red-200 transition-colors">
                                                                <i class="fas fa-times mr-1"></i> Reject
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-12 px-4">
                                <div class="inline-block p-4 rounded-full bg-neukai-100 mb-4">
                                    <i class="fas fa-inbox text-3xl text-neukai-600"></i>
                                </div>
                                <h3 class="text-xl font-semibold text-gray-800 mb-2">No Pending Requests</h3>
                                <p class="text-gray-500 max-w-md mx-auto">
                                    There are currently no pending donation requests for your charity.
                                    When donors submit new requests, they will appear here.
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </main>

    <!-- Footer -->
    <?php include '../section/donorparallax.php'; ?>

    <style>
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        @media (max-width: 375px) {
            .text-3xl {
                font-size: 1.5rem;
            }

            .text-xl {
                font-size: 1.125rem;
            }

            .px-4 {
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            table {
                font-size: 0.75rem;
            }
        }
    </style>

    <script>
        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 ${type === 'success' ? 'bg-neukai-600' : 'bg-red-500'} text-white px-6 py-3 rounded-lg shadow-lg transform transition-all duration-500 opacity-0 translate-y-2`;
            toast.innerHTML = message;
            document.body.appendChild(toast);


            setTimeout(() => {
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
            }, 100);


            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-2');
                setTimeout(() => {
                    document.body.removeChild(toast);
                }, 500);
            }, 3000);
        }


        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('updated') && urlParams.get('updated') === 'true') {
                showToast('Donation status successfully updated!');
            }
        });
    </script>
</body>

</html>
<?php
$stmt->close();
$conn->close();
?>