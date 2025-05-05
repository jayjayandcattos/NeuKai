<?php
session_start();
require '../configuration/db_connect.php';

date_default_timezone_set('Asia/Manila');

if (!isset($_GET['charity_id'])) {
    die("No charity specified.");
}

$items = [];
$donator_id = '';
$charity_id = $_GET['charity_id'];
$loggedin = isset($_SESSION['donator_id']); 

// Ensure the donator is logged in
if (!$loggedin) {
    die("You must be logged in to donate.");
}

$donator_id = $_SESSION['donator_id']; // Get the logged-in donator's ID

// Fetch charity details
$stmt = $conn->prepare("SELECT * FROM tbl_charity WHERE charity_id = ?");
$stmt->bind_param("i", $charity_id); 
$stmt->execute();
$result = $stmt->get_result();
$charity = $result->fetch_assoc(); 
$stmt->close();

// Debugging: Check if charity data was fetched successfully
if ($charity) {
    // Full address construction
    $full_address = htmlspecialchars($charity['street_address'] . ', ' . $charity['barangay'] . ', ' . $charity['municipality'] . ', ' . $charity['province']);
} else {
    die("Charity not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get donation details
    $donation_name = $_POST['donation_name'];
    $total_donation = 0;
    $status = 'pending'; // Default status (change as needed)
    $donation_date = date('Y-m-d H:i:s');
    
    // Insert donation details into tbl_donation
    $stmt = $conn->prepare("INSERT INTO tbl_donations (donator_id, donation_name, total_donation, status, donation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $donator_id, $donation_name, $total_donation, $status, $donation_date);
    $stmt->execute();
    $donation_id = $stmt->insert_id; // Get the inserted donation ID
    $stmt->close();

// Insert items into tbl_donation_items
foreach ($_POST['items'] as $index => $item) {
    $category = $item['category'];
    $description = $item['description'];
    $quantity = $item['quantity'];
    
    $image_data = null; 
    if (isset($_FILES['items']['name'][$index]['image'])) {
        if ($_FILES['items']['error'][$index]['image'] === UPLOAD_ERR_OK) {
            $image_data = file_get_contents($_FILES['items']['tmp_name'][$index]['image']);
            if ($image_data === false) {
                die("Error reading the uploaded image. Please try again.");
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $fileType = $_FILES['items']['type'][$index]['image'];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg']; 
            if (!in_array($fileType, $allowedTypes)) {
                die("Error: Invalid file type. Only JPEG, JPG, and PNG are allowed.");
            }
        } else {
           
            $errorCode = $_FILES['items']['error'][$index]['image'];
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.",
                UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                UPLOAD_ERR_NO_FILE => "No file was uploaded.",
            ];

            $errorMessage = $errorMessages[$errorCode] ?? "An unknown error occurred during file upload.";
            die("Error uploading the image: $errorMessage. Please try again.");
        }
    } else {
        die("No file upload detected for item $index. Please try again.");
    }

    // Prepare the SQL statement (remove 'status' column and no need to bind NOW() for created_at/updated_at)
$stmt = $conn->prepare("INSERT INTO tbl_donation_items (donation_id, category, description, quantity, image_path, created_at, updated_at) VALUES (?, ?, ?, ?, ?, NOW(), NOW())");

// Bind parameters for the columns (5 variables)
$stmt->bind_param("issss", $donation_id, $category, $description, $quantity, $image_data);  // 5 variables, no 'status'
$stmt->send_long_data(5, $image_data);  // Send the binary image data (the 5th parameter is for image_path)
$stmt->execute();
$stmt->close();


    $total_donation += $quantity; 
}
    // Update total donation amount in tbl_donation
    $stmt = $conn->prepare("UPDATE tbl_donations SET total_donation = ? WHERE donation_id = ?");
    $stmt->bind_param("ii", $total_donation, $donation_id);
    $stmt->execute();
    $stmt->close();

    // Insert into tbl_donation_transactions
    $admin_id = 1; // Assuming admin ID, you can adjust this
    $remarks = 'Donation made by ' . $donator_id;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    $approved_at = null; // Assuming the donation is pending approval
    $rejected_at = null;
    $delivered_at = null;

    $stmt = $conn->prepare("INSERT INTO tbl_donation_transactions (donator_id, charity_id, donation_id, status, admin_id, remarks, created_at, updated_at, approved_at, rejected_at, delivered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissssssss", $donator_id, $charity_id, $donation_id, $status, $admin_id, $remarks, $created_at, $updated_at, $approved_at, $rejected_at, $delivered_at);
    $stmt->execute();
    $stmt->close();

    require 'donor-mail-function.php';

    $emailData = prepareDonationEmail($conn, $donator_id, $charity_id, $donation_id);

    sendDonationEmail(
        $emailData['donor_email'],
        $emailData['donor_name'],
        $emailData['donor_subject'],
        $emailData['donor_body']
    );

    sendDonationEmail(
        $emailData['charity_email'],
        $emailData['charity_name'],
        $emailData['charity_subject'],
        $emailData['charity_body']
    );
    // Redirect to a thank you or confirmation page
    header("Location: d-donate.php");
    exit();
}
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
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/success.css">
    <link rel="stylesheet" href="../css/donorpage.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary': '#FD5008',
                        'primary-hover': '#e04400',
                    },
                    fontFamily: {
                        'poppins': ['Poppins', 'sans-serif'],
                        'rubik': ['Rubik Mono One', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style>
        .rubik-font {
            font-family: 'Rubik Mono One', sans-serif;
        }

        .remove-item-btn {
            position: absolute;
            top: 1rem;
            right: 1rem;
            width: 26px;
            height: 26px;
            background: linear-gradient(135deg, #ff3333, #cc0000);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            border: none;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            transform: scale(1);
            z-index: 10;
            padding: 0;
            line-height: 1;

        }

        .remove-item-btn:hover {
            background: linear-gradient(135deg, #ff4d4d, #e60000);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: scale(1.15);
        }

        .remove-item-btn:active {
            transform: scale(0.92);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }


        @keyframes gentlePulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .remove-item-btn.render-animation {
            animation: gentlePulse 0.6s ease;
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

    <div class="w-full max-w-[1300px] h-auto min-h-[600px] bg-white rounded-3xl overflow-y-auto overflow-x-hidden text-black p-4 md:p-8 mx-auto">
        <!-- Back Button -->
        <a href="d-donate.php" class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition duration-300 text-sm text-gray-800">
            <svg width="16" height="16" viewBox="0 0 24 24" class="mr-1">
                <path d="M20,11V13H8L13.5,18.5L12.08,19.92L4.16,12L12.08,4.08L13.5,5.5L8,11H20Z" fill="#333" />
            </svg>
            Donation Page
        </a>

        <!-- Charity Information Section -->
        <div class="flex flex-col md:flex-row gap-4 mt-8 md:mt-12 px-2 md:px-10">
            <!-- Charity Details -->
            <div class="w-full md:w-[55%]">
                <h1 class="rubik-font text-2xl md:text-3xl lg:text-4xl uppercase mb-2">
                    <?php echo isset($charity) ? htmlspecialchars($charity['charity_name']) : 'Charity not found'; ?>
                </h1>
                <p class="font-poppins text-lg md:text-xl font-semibold mb-3">
                    <?= isset($full_address) ? $full_address : 'Address not available'; ?>
                </p>
                <p class="border-l-4 border-gray-500 pl-3 font-poppins text-sm md:text-base text-justify">
                    <?= isset($charity) ? htmlspecialchars($charity['charity_description']) : 'Description not available'; ?>
                </p>
            </div>

            <!-- Charity Image -->
            <div class="w-full md:w-[45%] flex justify-center items-center">
                <?php if (isset($charity['charity_photo']) && !empty($charity['charity_photo'])): ?>
                    <img src="data:image/jpeg;base64,<?= base64_encode($charity['charity_photo']) ?>"
                        alt="<?= htmlspecialchars($charity['charity_name']) ?>"
                        class="w-full max-w-[480px] h-auto max-h-[300px] object-cover rounded-2xl">
                <?php else: ?>
                    <div class="rounded-2xl w-full max-w-[480px] h-[200px] md:h-[300px] bg-gray-300 flex items-center justify-center">
                        <img src="../images/noimagefound.svg" alt="Placeholder" class="w-full h-1/2 object-fill">
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Section Header -->
        <div class="flex items-center w-full my-6 md:my-8">
            <h2 class="ml-2 md:ml-10 text-primary uppercase text-lg md:text-2xl font-extrabold font-poppins">
                Donation Form
            </h2>
            <div class="flex-grow h-1 bg-primary rounded-full ml-4 mr-2 md:mr-10"></div>
        </div>

        <!-- Donation Form -->
        <form action="" method="POST" enctype="multipart/form-data" class="px-2 md:px-10">
            <div class="mb-4">
                <label class="block mb-2 font-poppins font-semibold">Donation Name:</label>
                <input type="text" name="donation_name" required
                    class="w-full px-3 py-3 border border-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
            </div>

            <div id="items-container" class="mb-6">
                <div class="item relative p-4 md:p-6 mb-6 border rounded-lg bg-white" id="item-0">
                    <div class="mb-4">
                        <label class="block mb-2 font-poppins font-semibold">Category:</label>
                        <select name="items[0][category]" required
                            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                            <option value="shirt">Shirt</option>
                            <option value="pants">Pants</option>
                            <option value="headwear">Headwear</option>
                            <option value="footwear">Footwear</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2 font-poppins font-semibold">Description:</label>
                        <textarea name="items[0][description]" required placeholder="Describe the item"
                            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2 font-poppins font-semibold">Quantity:</label>
                        <input type="number" name="items[0][quantity]" value="1" min="1" required
                            class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <div class="mb-4">
                        <label class="block mb-2 font-poppins font-semibold">Item Image:</label>
                        <input type="file" name="items[0][image]" accept="image/*" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                    </div>

                    <!-- Remove Button -->
                    <span class="remove-item-btn" onclick="removeItem(0)">X</span>
                </div>
            </div>

            <button type="button" onclick="addNewItem()"
  class="charity-btn w-42 h-12 rounded-lg font-semibold flex items-center justify-center">
                Add New Item
            </button>

            <div class="mt-8 mb-6 ">
            <button type="submit" name="submit" onclick="return confirmSubmit()"
    class="donor-btn w-42 h-12 rounded-lg bg-primary font-semibold flex items-center justify-center">
    Submit
</button>
            </div>
        </form>
    </div>

    <?php include '../section/donorparallax.php'; ?>

    <script>
        let itemCount = 1;

        function addNewItem() {
            const container = document.getElementById('items-container');

            const newItem = document.createElement('div');
            newItem.classList.add('item', 'relative', 'p-4', 'md:p-6', 'mb-6', 'border', 'border-gray-300', 'rounded-lg', 'bg-white');
            newItem.id = 'item-' + itemCount;

            newItem.innerHTML = `
                <div class="mb-4">
                    <label class="block mb-2 font-poppins font-semibold">Category:</label>
                    <select name="items[${itemCount}][category]" required
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                        <option value="shirt">Shirt</option>
                        <option value="pants">Pants</option>
                        <option value="headwear">Headwear</option>
                        <option value="footwear">Footwear</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-poppins font-semibold">Description:</label>
                    <textarea name="items[${itemCount}][description]" required placeholder="Describe the item"
                              class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary"></textarea>
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-poppins font-semibold">Quantity:</label>
                    <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" required
                           class="w-full px-3 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <div class="mb-4">
                    <label class="block mb-2 font-poppins font-semibold">Item Image:</label>
                    <input type="file" name="items[${itemCount}][image]" accept="image/*" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary">
                </div>

                <!-- Remove Button -->
                <span class="remove-item-btn" onclick="removeItem(${itemCount})">X</span>
            `;

            container.appendChild(newItem);
            itemCount++;
        }

        function removeItem(itemId) {
            const itemToRemove = document.getElementById('item-' + itemId);
            itemToRemove.remove();
        }

        function confirmSubmit() {
    const confirmed = confirm("Are you sure you want to submit this donation to <?php echo isset($charity) ? htmlspecialchars($charity['charity_name']) : 'this charity'; ?>?");
    if (confirmed) {
 
        return true;
    }
    return false; 
}
    </script>
</body>

</html>