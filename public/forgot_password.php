<?php require_once __DIR__ . '/../app/bootstrap.php'; ?><?php
require_once __DIR__ . '/../app/services/mail/reset-mail.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $role = (string) ($_POST['role'] ?? '');

    $table_map = [
        'Donor' => 'tbl_donor',
        'Charity' => 'tbl_charity_login',
        'Admin' => 'tbl_admin',
    ];

    if (!isset($table_map[$role])) {
        $error = 'Please select a valid account role.';
    } elseif (!isset($_FILES['id_image']) || !is_array($_FILES['id_image'])) {
        $error = 'Please attach a valid ID image.';
    } else {
        $table = $table_map[$role];
        $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        $accountExists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$accountExists) {
            $error = 'No account found with that email.';
        } else {
            try {
                $filename = store_uploaded_image(
                    $_FILES['id_image'],
                    STORAGE_PATH . '/id_verifications'
                );

                $relativePath = 'id_verifications/' . $filename;
                $stmt = $conn->prepare("INSERT INTO password_reset_tickets (email, role, id_image_path, status) VALUES (?, ?, ?, 'Pending')");
                $stmt->bind_param('sss', $email, $role, $relativePath);
                $stmt->execute();
                $ticketId = $conn->insert_id;
                $stmt->close();

                $emailContent = prepareUserResetEmail($email, $role, $ticketId);
                sendResetRequestEmail($email, $emailContent['subject'], $emailContent['body']);
                $success = 'Your reset request has been sent. Please wait for admin approval.';
            } catch (RuntimeException $e) {
                $error = $e->getMessage();
            }
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
    <script src="assets/js/navbarScroll.js" defer></script>
    <script src="assets/js/slideAnimation.js" defer></script>
    <script src="assets/js/loading.js" defer></script>
    <script src="assets/js/mobilenav.js" defer></script>
    <script src="assets/js/indexAos.js" defer></script>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/forgot.css">
    <link rel="stylesheet" href="assets/css/charityinvoice.css">
    <link rel="icon" href="assets/images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
        .donor-btn {
            display: block;
            margin: 0 auto;
            max-height: 55px;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }

            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(52, 211, 153, 0.7);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(52, 211, 153, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(52, 211, 153, 0);
            }
        }

        /* Modal Styles */
        .modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
            animation: fadeIn 0.3s ease-out;
        }

        .success-modal {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            position: relative;
            animation: slideIn 0.3s ease-out;
        }

        .success-icon {
            background-color: #10B981;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: pulse 1.5s infinite;
        }

        .error-modal {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            max-width: 400px;
            position: relative;
            animation: slideIn 0.3s ease-out;
        }

        .error-icon {
            background-color: #800000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            animation: pulse 1.5s infinite;
        }

        .error-modal-btn {
            background-color: #FF0000;
            color: white;
            padding: 0.5rem 1.5rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        .error-modal-btn:hover {
            background-color: #ef4444;
        }     
    </style>
</head>

<body>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="assets/images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
    </div>

    <!-- Navbar -->
<?php require_once __DIR__ . '/../app/includes/desktopnavbar.php'; ?>

    <!-- Mobile Menu -->
<?php require_once __DIR__ . '/../app/includes/mobilenavbar.php'; ?>

    <section id="home" class="flex flex-col items-center justify-center text-center pt-36 md:pt-44 px-4 w-[99%] mx-auto">
        <h2 class="text-3xl font-bold mb-6 text-white">Forgot Password</h2>
    </section>
    <form class="mt-50 w-[95%] mx-auto" method="POST" enctype="multipart/form-data"><?= csrf_field() ?>
        <label>Select Role:</label>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="Donor">Donor</option>
            <option value="Charity">Charity</option>
            <!-- <option value="Admin">Admin</option> -->
        </select>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Upload Valid ID (jpg, png):</label>
        <input type="file" name="id_image" accept="image/*" required>

        <button class="donor-btn" type="submit">Send Reset Request</button>
        <a href="login.php">Back to Login</a>
    </form>

    <!-- Success Modal -->
    <?php if ($success): ?>
        <div id="success-modal" class="modal-backdrop">
            <div class="success-modal">
                <div class="success-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" viewBox="0 0 16 16">
                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z" />
                    </svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Success!</h3>
                <p class="text-gray-600 mb-4"><?= e($success) ?></p>
                <button class="donor-btn" onclick="closeModal()">Continue</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Error Modal -->
    <?php if ($error): ?>
        <div id="error-modal" class="modal-backdrop">
            <div class="error-modal">
                <div class="error-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="white" viewBox="0 0 16 16">
  <path d="M7.938 2.016a.13.13 0 0 1 .124 0l6.857 11.856c.03.052.046.11.046.17a.267.267 0 0 1-.267.267H1.302a.267.267 0 0 1-.267-.267c0-.06.016-.118.046-.17L7.938 2.016zM8 5c-.535 0-.954.462-.9.995l.35 3.507a.552.552 0 0 0 1.1 0l.35-3.507A.905.905 0 0 0 8 5zm.002 6a1 1 0 1 0 0 2 1 1 0 0 0 0-2z"/>
</svg>
                </div>
                <h3 class="text-xl font-semibold mb-2">Error</h3>
                <p class="text-red-600 mb-4"><?= e($error) ?></p>
                <button class="error-modal-btn" onclick="closeErrorModal()">Close</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Footer -->
<?php require_once __DIR__ . '/../app/includes/footer.php'; ?>

    <!-- Parallax Background -->
<?php require_once __DIR__ . '/../app/includes/parallaxbg.php'; ?>

    <script>
        function closeModal() {
            document.getElementById('success-modal').style.display = 'none';
            window.location.href = 'login.php';
        }

        function closeErrorModal() {
            document.getElementById('error-modal').style.display = 'none';
        }
    </script>
</body>

</html>