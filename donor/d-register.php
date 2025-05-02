<?php
require '../configuration/db_connect.php';

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = $conn->real_escape_string($_POST['first_name'] ?? '');
    $lastName = $conn->real_escape_string($_POST['last_name'] ?? '');
    $middleName = $conn->real_escape_string($_POST['middle_name'] ?? '');
    $email = $conn->real_escape_string($_POST['email'] ?? '');
    $contactNo = $conn->real_escape_string($_POST['contact_no'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (empty($firstName) || empty($lastName) || empty($email) || empty($contactNo) || empty($password)) {
        $errors[] = "All fields except middle name are required";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }

    $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_donor WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $errors[] = "Email already registered";
    }

    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO tbl_donor (first_name, middle_name, last_name, email, contact_no, password) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $firstName, $middleName, $lastName, $email, $contactNo, $hashedPassword);
            $stmt->execute();

            $success = "Registration successful! Your account is pending approval.";
            $stmt->close();
        } catch (Exception $e) {
            $errors[] = "Registration failed: " . $e->getMessage();
        }
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
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/success.css">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: black;
            font-family: 'Poppins', sans-serif;
        }

        main {
            flex: 1;
        }

        footer {
            flex-shrink: 0;
        }

        .scrollbar-hidden::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="relative min-h-screen bg-black text-white font-poppins">

    <!-- Navbar -->
    <?php include '../section/donorNav.php'; ?>

    <!-- Mobile Menu -->
    <?php include '../section/Donormobilenavbar.php'; ?>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <div id="top" class="flex-grow flex justify-center px-4 mt-[100px] min-h-screen z-0">
        <div class="w-full max-w-[800px] h-auto max-h-[770px] md:h-auto bg-white rounded-3xl overflow-auto scrollbar-hidden">

            <!-- Header Section -->
            <div class="relative flex flex-col items-center justify-center p-6 bg-[#FF8000]">
            <div class="absolute inset-0 bg-black/10 cbackdrop-blur-lg z-0"></div>
            <div class="relative z-10 flex flex-col items-center">
                <img src="../images/signin.png" alt="Sign In" class="w-8 h-8 mb-2 object-contain" />
                <span class="text-2xl font-bold text-white">DONOR SIGN UP</span>
            </div>
            </div>

            <div class="p-8">
            <?php if (!empty($errors)): ?>
                <div class="error mb-4 text-red-500">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($success)): ?>
                <?php else: ?>
                    <form method="POST" class="space-y-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-black">Name</label>
                            <div class="grid grid-cols-3 gap-3">
                                <input type="text" name="first_name" placeholder="First Name"
                                    class="text-black w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <input type="text" name="last_name" placeholder="Last Name"
                                    class="text-black w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                                <input type="text" name="middle_name" placeholder="Middle Name"
                                    class="text-black w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-black">Email</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <img src="../images/emailblack.svg" class="h-5 w-5" alt="Email Icon" />
                                </div>
                                <input type="email" name="email" placeholder="Email"
                                    class="text-black block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-black">Phone</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <img src="../images/callblack.svg" class="h-5 w-5" alt="Call Icon" />
                                </div>
                                <input type="text" name="contact_no" placeholder="63-XXX-XXX-XXXX"
                                    class="text-black block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-black">Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <img src="../images/lock.svg" class="h-5 w-5" alt="Lock Icon" />
                                </div>
                                <input type="password" name="password" placeholder="Password"
                                    class="text-black block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-black">Confirm Password</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <img src="../images/lock.svg" class="h-5 w-5" alt="Lock Icon" />
                                </div>
                                <input type="password" name="confirm_password" placeholder="Confirm Password"   
                                    class="text-black block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>

                        <div class="mt-6 flex items-center justify-center">
                            <div class="flex items-center">
                                <input id="terms" name="terms" type="checkbox" required
                                    class="h-4 w-4 text-orange-600 focus:ring-orange-500 border-gray-300 rounded">
                                <label for="terms" class="ml-2 text-sm text-black">
                                    Accept <a href="http://localhost/neukai/about.php#terms"
                                        class="text-orange-600 hover:text-orange-800">terms and conditions</a>
                                </label>
                            </div>
                        </div>

                        <div class="flex mt-6 justify-center items-center">
                            <button type="submit" class="fancy-button ">
                                Sign up
                            </button>
                        </div>
                    </form>
                <?php endif; ?>

                <div class="mt-4 text-center text-sm">
                    <span class="text-gray-600">Already have an account?</span>
                    <a href="../login.php" class="font-medium text-orange-600 hover:text-orange-500"> Login</a>
                </div>
            </div>
        </div>
    </div>


    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Success</h3>
            </div>
            <div class="modal-body">
                <div class="checkmark-circle mx-auto mb-4">
                    <svg class="w-24 h-24 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <p>Registration successful! Your account is pending approval.</p>
            </div>
            <div class="modal-footer">
                <button id="closeSuccessModal" class="modal-close">Continue</button>
            </div>
        </div>
    </div>

    <!-- Parallax Background -->
    <?php include '../section/folderbg.php'; ?>

    <script>
      document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($success)): ?>
        const successModal = document.getElementById('successModal');
        if (successModal) {
            
            successModal.style.display = 'flex';
            successModal.style.justifyContent = 'center';
            successModal.style.alignItems = 'center';
            successModal.style.position = 'fixed';
            successModal.style.top = '0';
            successModal.style.left = '0';
            successModal.style.width = '100%'; 
            successModal.style.height = '100%';
            successModal.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            successModal.style.zIndex = '1000';
            
 
            successModal.style.opacity = '0';
            
       
            setTimeout(() => {
                successModal.style.transition = 'opacity 0.4s ease-out';
                successModal.style.opacity = '1';
            }, 50);
            
           
            const modalContent = successModal.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.transform = 'translateY(-40px)';
                modalContent.style.transition = 'transform 0.4s ease-out';
                
                setTimeout(() => {
                    modalContent.style.transform = 'translateY(0)';
                }, 50);
            }
        }

        const closeButton = document.getElementById('closeSuccessModal');
        if (closeButton) {
            closeButton.addEventListener('click', function() {
                const modalContent = successModal.querySelector('.modal-content');
                if (modalContent) {
                    modalContent.style.transform = 'translateY(-40px)';
                }
                
                successModal.style.opacity = '0';
                
                setTimeout(() => {
                    window.location.href = '../login.php';
                }, 400);
            });
        }
    <?php endif; ?>
});
    </script>
</body>

</html>