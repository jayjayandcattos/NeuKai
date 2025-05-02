<?php
session_start();
require('../configuration/db_connect.php');

if (!isset($_SESSION['charity_id'])) {
    header("Location: ../login.php");
    exit();
}

$charity_id = $_SESSION['charity_id'];

// Fetch charity name first, so it's available throughout the page
$charity_query = "SELECT charity_name FROM tbl_charity WHERE charity_id = ?";
$charity_stmt = $conn->prepare($charity_query);
$charity_stmt->bind_param('i', $charity_id);
$charity_stmt->execute();
$charity_result = $charity_stmt->get_result();
$charity_data = $charity_result->fetch_assoc();
$charity_name = $charity_data['charity_name'];
$charity_stmt->close();

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>NEUKAI - Donations Summary</title>
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
                <div class="md:col-span-12 grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <!-- Approved Donations Card -->
                    <div class="bg-white rounded-xl p-4 shadow border border-gray-100 flex items-center">
                        <div class="rounded-full bg-neukai-100 p-3 mr-4">
                            <i class="fas fa-check-circle text-xl text-neukai-600"></i>
                        </div>
                        <div>
                            <h2 class="text-sm text-gray-500 font-medium">Approved Donations</h2>
                            <p class="text-2xl font-bold text-neukai-800"><?php echo $total_approved; ?></p>
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
                             
                <!-- Right Column Content -->
                <div class="md:col-span-12 bg-white rounded-xl shadow border border-gray-100 overflow-hidden h-[350px] flex-grow">
                    <!-- Navigation Tabs -->
                    <div class="border-b border-gray-200 px-4">
                        <nav class="flex overflow-x-auto hide-scrollbar py-3">
                            <?php include('navigation_links.php'); ?>
                        </nav>
                    </div>

                    <!-- Donations Summary Section -->
                    <div class="p-4">
                        <?php
                        if ($total_approved > 0) {
                            $row = $result->fetch_assoc();
                            echo "<h2 class='text-xl font-bold text-neukai-700 mb-4'>Donations Summary for Charity: " . 
                                htmlspecialchars($row['charity_name']) . "</h2>"; 

                            // Reset result pointer to start
                            
                            
                            echo '<div class="overflow-x-auto -mx-4 sm:mx-0">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead>
                                        <tr class="bg-gray-50">
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">View</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Donator Name</th>
                                            <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Approved Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">';

                            while ($row = $result->fetch_assoc()) {
                                echo '<tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">
                                            <a href="c-approved_summary.php?transaction_id=' . $row['transaction_id'] . '"
                                                class="text-neukai-600 hover:text-neukai-800 font-medium">
                                                <i class="fas fa-eye mr-1"></i> View
                                            </a>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">' . 
                                            htmlspecialchars($row['first_name']) . 
                                        '</td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm">' . 
                                            htmlspecialchars($row['approved_at']) . 
                                        '</td>
                                      </tr>';
                            }

                            echo '</tbody>
                                </table>
                            </div>';
                        } else {
                            echo '<div class="text-center py-12 px-4">
                                    <div class="inline-block p-4 rounded-full bg-neukai-100 mb-4">
                                        <i class="fas fa-check-circle text-3xl text-neukai-600"></i>
                                    </div>
                                    <h3 class="text-xl font-semibold text-gray-800 mb-2">Donations Summary for Charity: ' . 
                                        htmlspecialchars($charity_name) . 
                                    '</h3>
                                    <p class="text-gray-500 max-w-md mx-auto">
                                        No donations approved found for this charity.
                                    </p>
                                </div>';
                        }

                        $stmt->close();
                        $conn->close();
                        ?>
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
</body>
</html>