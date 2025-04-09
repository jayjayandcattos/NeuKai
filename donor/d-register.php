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
  <link rel="stylesheet" href="css/error.css">
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

<body>
<div id="loading-overlay"
    class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
  </div>

    <div id="top" class="flex-grow flex justify-center px-4 mt-[200px] min-h-screen z-0">
    <div class="w-full max-w-[800px] h-auto max-h-[640px] md:h-auto bg-white rounded-3xl overflow-hidden">

    <div class="relative flex flex-col items-center justify-center p-6 bg-[#FF8000]">
        <div class="absolute inset-0 bg-black/10 backdrop-blur-lg z-0"></div>
        <div class="relative z-10 flex flex-col items-center">
          <img src="../images/signin.png" alt="Sign In" class="w-8 h-8 mb-2 object-contain" />
          <span class="text-[24pt] font-bold text-white">DONOR SIGN UP</span>
        </div>
      </div>
        <?php if (!empty($errors)): ?>
            <div class="error">
                <?php foreach ($errors as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (isset($success)): ?>
    <div class="success"><?= htmlspecialchars($success) ?></div>
<?php else: ?>
    <form method="POST">
    <form method="POST" class="max-w-2xl mx-auto p-6 bg-white rounded-md shadow-md text-black">
    <!-- Grid layout -->
    <div class="grid grid-cols-[120px_1fr] gap-4 items-center">
        <!-- Name -->
        <label class="text-sm font-medium text-black">Name</label>
        <div class="flex items-center gap-2 flex-wrap">
            <img src="icons/user.svg" class="w-5 h-5">
            <input type="text" name="first_name" placeholder="Firstname" class="border-b text-sm px-2 py-1 outline-none text-black w-[130px]">
            <input type="text" name="last_name" placeholder="Lastname" class="border-b text-sm px-2 py-1 outline-none text-black w-[130px]">
            <input type="text" name="middle_name" placeholder="Middle Name" class="border-b text-sm px-2 py-1 outline-none text-black w-[130px]">
        </div>

        <!-- Email -->
        <label class="text-sm font-medium text-black">Email</label>
        <div class="flex items-center gap-2">
            <img src="icons/email.svg" class="w-5 h-5">
            <input type="email" name="email" placeholder="email@email.com" class="flex-1 border-b text-sm px-2 py-1 outline-none text-black">
        </div>

        <!-- Phone -->
        <label class="text-sm font-medium text-black">Phone</label>
        <div class="flex items-center gap-2">
            <img src="icons/phone.svg" class="w-5 h-5">
            <input type="text" name="contact_no" placeholder="1234–567–8901" class="flex-1 border-b text-sm px-2 py-1 outline-none text-black">
        </div>

        <!-- Password -->
        <label class="text-sm font-medium text-black">Password</label>
        <div class="flex items-center gap-2">
            <img src="icons/lock.svg" class="w-5 h-5">
            <input type="password" name="password" class="flex-1 border-b text-sm px-2 py-1 outline-none text-black">
        </div>
    </div>

    <!-- Terms -->
    <div class="mt-6 flex items-center gap-2">
        <input type="checkbox" required class="accent-orange-500 w-4 h-4">
        <label class="text-sm">
            Accept <a href="http://localhost/neukai/about.php#terms" class="text-orange-500 underline hover:text-orange-600">terms and conditions</a>
        </label>
    </div>

    <!-- Register Button -->
    <div class="mt-6 text-center">
        <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md font-semibold">
            Register
        </button>
    </div>


</form>

<?php endif; ?>

<p class="text-center text-sm mt-4">
        Already have an account? <a href="../login.php" class="text-orange-500 font-medium hover:underline">Login</a>
    </p>

 <!-- Parallax Background -->
 <?php include '../section/folderbg.php'; ?>
</body>

</html>