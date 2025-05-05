<?php
session_start();
require('../configuration/db_connect.php');


$loggedin = isset($_SESSION['donator_id']);


$filter = isset($_GET['filter']) ? $_GET['filter'] : '';

$query = "SELECT * FROM tbl_charity WHERE status = 'approved'";
$result = $conn->query($query);


$charities = [];
$charityTypes = [];
while ($row = $result->fetch_assoc()) {
    $charities[] = $row;
    if (isset($row['charity_type']) && !empty($row['charity_type'])) {
        $type = $row['charity_type'];
        if (!isset($charityTypes[$type])) {
            $charityTypes[$type] = ['count' => 0, 'name' => $type];
        }
        $charityTypes[$type]['count']++;
    }
}


if (empty($charityTypes)) {
    $charityTypes = [
        'Clothing' => ['count' => count($charities) > 0 ? ceil(count($charities)/3) : 0, 'name' => 'Clothing'],
    ];
    
   
    foreach ($charities as $key => $charity) {
        $types = array_keys($charityTypes);
        $charities[$key]['charity_type'] = $types[$key % count($types)];
    }
}


mysqli_data_seek($result, 0);
?>

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
    <script src="../js/optionchecks.js" defer></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/success.css">
    <link rel="stylesheet" href="../css/donorpage.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        .selection-btn.active {
            background-color: #FBB901;
            color: white;
        }
    </style>
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

    <div class="flex-grow flex justify-center px-4 text-black">
        <div class="w-full max-w-[1300px] h-auto min-h-[600px] md:h-[986px] bg-white rounded-3xl overflow-hidden">
            <div class="h-auto md:h-[228px] w-full bg-white flex flex-col md:flex-row items-center justify-between p-8">
                <!-- Left Side: Welcome and Logo -->
                <div class="w-full md:w-[337px] ml-0 md:ml-14 text-center md:text-left">
                    <h1 class="text-[28px] md:text-[40px] font-bold text-[#FBB901] w-full md:w-[349px]"
                    style="font-family: 'Rubik Mono One', monospace;">WELCOME TO</h1>
                    <img src="../images/Neukai Logo.svg" alt="Neukai Logo" class="mt-2 w-[160px] md:w-auto mx-auto md:mx-0">
                </div>
                <!-- Right Side: Selections -->
                <div class="w-full md:w-[757px] mt-8 md:mt-0 text-center md:text-left">
                    <h2 class="text-xl md:text-2xl font-bold mb-4">Sort charities to match your clothing donations</h2>
                    <!-- Selection Buttons -->
                    <div class="flex flex-wrap gap-4 justify-center md:justify-start">
                        <?php foreach ($charityTypes as $type => $typeData): ?>
                            <a href="?<?= ($filter == $type) ? '' : "filter=$type" ?>" 
                               class="selection-btn w-auto min-w-[120px] px-3 md:min-w-[150px] h-[35px] md:h-[43px] 
                                      <?= ($filter == $type) ? 'active bg-[#FBB901] text-white' : 'bg-gray-200' ?> 
                                      rounded-lg flex items-center justify-center transition-all duration-300">
                                <span class="text-sm md:text-base"><?= htmlspecialchars($typeData['name']) ?></span>
                            </a>
                        <?php endforeach; ?>
                        <?php if ($filter): ?>
                            <a href="?" 
                               class="selection-btn w-auto min-w-[120px] px-3 md:min-w-[150px] h-[35px] md:h-[43px] 
                                      bg-gray-800 text-white rounded-lg flex items-center justify-center transition-all duration-300">
                                <span class="text-sm md:text-base">Show All</span>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="w-full flex flex-wrap justify-center gap-8 px-4 py-12">
                <?php 
       
                foreach ($charities as $charity): 
            
                    if ($filter && $charity['charity_type'] != $filter) continue;
                ?>
                <div class="relative h-[276px] w-full sm:w-[48%] lg:w-[30%] rounded-lg overflow-hidden shadow-lg transition-all duration-300 hover:shadow-xl group">
                    <?php if (!empty($charity['charity_photo'])): ?>
                        <div class="relative w-full h-full">
                            <img src="data:image/jpeg;base64,<?= base64_encode($charity['charity_photo']) ?>" 
                                 alt="<?= htmlspecialchars($charity['charity_name']) ?>" 
                                 class="w-full h-full object-cover">
                            <div class="absolute bottom-0 left-0 w-full h-2/3 bg-gradient-to-t from-black via-black/50 to-transparent"></div>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                            <p class="text-gray-500">No image available.</p>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-0 left-0 w-full p-4 text-white transition-all duration-300 group-hover:-translate-y-16">
                        <p class="text-[18px] font-poppins">
                            <?= htmlspecialchars($charity['barangay']) . ', ' . htmlspecialchars($charity['municipality']) ?>
                        </p>
                        <p class="text-[24px] font-poppins font-bold">
                            <?= htmlspecialchars($charity['charity_name']) ?>
                        </p>
                        <span class="inline-block px-2 py-1 bg-[#FBB901] text-white text-xs rounded mt-1">
                            <?= htmlspecialchars($charity['charity_type'] ?? 'General') ?>
                        </span>
                    </div>
                    <a href="donate_now.php?charity_id=<?= $charity['charity_id'] ?>" 
                       class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[calc(100%-20px)] h-[53px] bg-green-600 
                              text-white px-6 py-3 rounded-lg opacity-0 transition-all duration-300 
                              group-hover:opacity-100 group-hover:-translate-y-4 font-bold text-[16px] 
                              flex items-center justify-center">
                        DONATE
                    </a>
                </div>
                <?php endforeach; ?>
                
                <?php if ($filter && !array_filter($charities, function($c) use ($filter) { return $c['charity_type'] == $filter; })): ?>
                <div class="w-full text-center py-12">
                    <p class="text-xl text-gray-500">No charities found for this category.</p>
                    <a href="?" class="inline-block mt-4 px-6 py-3 bg-[#FBB901] text-white rounded-lg">Show All Charities</a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../section/donorparallax.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {

        const currentFilter = '<?= $filter ?>';
        if (currentFilter) {
            document.querySelectorAll('.selection-btn').forEach(btn => {
                if (btn.href.includes(`filter=${currentFilter}`)) {
                    btn.classList.add('active');
                }
            });
        }
    });
    </script>
</body>
</html>