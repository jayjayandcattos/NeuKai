<?php
session_start();
require('../configuration/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['donator_id'])) {
    header("Location: login.php");
    exit();
}

$donator_id = $_SESSION['donator_id'];

// Fetch existing donor details
$stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param('i', $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donator = $result->fetch_assoc();

// Handle profile update submission
if (isset($_POST['update'])) {
    $errors = []; // Initialize errors array

    // Sanitize inputs
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $old_password = $_POST["old_password"] ?? '';
    $new_password = $_POST["new_password"] ?? '';
    $cpassword = $_POST['confirm_new_password'] ?? '';
    $current_password = $donator['password'] ?? null;

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($phone) || empty($email)) {
        $errors[] = "Please fill out all required fields.";
    }

    // Update donor details
    if (empty($errors)) {
        // Update donor details query
        $stmt = $conn->prepare("UPDATE tbl_donor SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_no = ? WHERE donator_id = ?");
        $stmt->bind_param('sssssi', $firstname, $middlename, $lastname, $email, $phone, $donator_id);
        $stmt->execute();
    }

    // Handle password change logic if password fields are set
    if (!empty($old_password) && !empty($new_password) && !empty($cpassword)) {
        if (password_verify($old_password, $current_password)) {
            if ($new_password === $cpassword) {
                // Validate new password (you can modify the regex if needed)
                if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password if validated
                    $updatePasswordStmt = $conn->prepare("UPDATE tbl_donor SET password = ? WHERE donator_id = ?");
                    $updatePasswordStmt->bind_param('si', $hashed_password, $donator_id);
                    $updatePasswordStmt->execute();
                    $success_message = "Password updated successfully!";
                } else {
                    $errors[] = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.";
                }
            } else {
                $errors[] = "New password and confirmation do not match.";
            }
        } else {
            $errors[] = "Old password is incorrect.";
        }
    }

    // Redirect to profile page if no errors
    if (empty($errors)) {
        header("Location: d-profile.php?success=1");
        exit();
    }
}
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


<body class="relative min-h-screen bg-black text-black font-poppins">

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
    <?php include '../section/LoggedInDonorNavFolder.php'; ?>

    <!-- Mobile Menu -->
    <?php include '../section/LoggedInDonorNavMobileFolder.php'; ?>


    <?php
    // Display errors if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
    ?>

    <div class="container">
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

        <!-- Main Content -->
        <div class="w-full lg:w-3/4 bg-white rounded-lg p-12 overflow-auto">
            <div class="header-container pb-4 mb-6">
                <h1 class="text-orange-600 text-2xl md:text-3xl lg:text-4xl font-bold text-center">EDITING PROFILE</h1>
            </div>

            <form action="" method="POST" class="space-y-6">
                <!-- Name Row -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-48 flex flex-row md:flex-col items-center gap-2 mb-2 md:mb-0">
                        <img src="../images/blacklogin.svg" alt="Name" class="w-6 h-6 md:w-8 md:h-8">
                        <p class="text-xs font-medium text-gray-700 text-center">Name</p>
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="firstname" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($donator['first_name']); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="middlename" class="block text-sm font-medium text-gray-700 mb-1">Middle Name</label>
                                <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($donator['middle_name']); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="lastname" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($donator['last_name']); ?>"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-48 flex flex-row md:flex-col items-center gap-2 mb-2 md:mb-0">
                        <img src="../images/emailblack.svg" alt="Email" class="w-6 h-6 md:w-8 md:h-8">
                        <p class="text-xs font-medium text-gray-700 text-center">Email</p>
                    </div>
                    <div class="flex-1">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($donator['email']); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                </div>

                <!-- PHONE -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-48 flex flex-row md:flex-col items-center gap-2 mb-2 md:mb-0">
                        <img src="../images/callblack.svg" alt="Phone" class="w-6 h-6 md:w-8 md:h-8">
                        <p class="text-xs font-medium text-gray-700 text-center">Phone</p>
                    </div>
                    <div class="flex-1">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($donator['contact_no']); ?>"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                    </div>
                </div>

                <!-- PASSWORD -->
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="w-full md:w-48 flex flex-row md:flex-col items-center gap-2 mb-2 md:mb-0">
                        <img src="../images/lock.svg" alt="Password" class="w-6 h-6 md:w-8 md:h-8">
                        <p class="text-xs font-medium text-gray-700 text-center">Password</p>
                    </div>
                    <div class="flex-1">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label for="old_password" class="block text-sm font-medium text-gray-700 mb-1">Old Password</label>
                                <input type="password" id="old_password" name="old_password"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" id="new_password" name="new_password"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                            <div>
                                <label for="confirm_new_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                                <input type="password" id="confirm_new_password" name="confirm_new_password"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 sm:text-sm">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="editing-buttons">
                    <button type="submit" name="update" class="update-button">
                        <span>Update Profile</span>
                    </button>

                    <a href="d-profile.php" class="cancel-button">
                        <span>Cancel</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($success_message)) {
        echo "<div class='mt-4 p-4 bg-green-100 text-green-700 rounded'>$success_message</div>";
    } ?>

    <!-- Parallax Background -->
    <?php include '../section/donorparallax.php'; ?>
    </div>
</body>

</html>