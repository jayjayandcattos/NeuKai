<?php
session_start();
include 'configuration/db_connect.php';

$email = '';
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $email = strtolower(trim($_POST['email']));
  $password = trim($_POST['password']);
  $role = $_POST['role'];

  function authenticateUser($conn, $email, $password, $table, $id_col, $name_col, $redirect, $status_check = false)
  {
    $query = "SELECT * FROM $table WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
      if ($status_check && $user['status'] !== 'approved') {
        return "Your account is pending approval.";
      }

      $_SESSION[$id_col] = $user[$id_col];
      $_SESSION[$name_col] = $user[$name_col];
      header("Location: $redirect");
      exit();
    }
    return false;
  }

  $error = "Invalid email or password.";

  if ($role == "Donor") {
    $stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE email = ? AND status = 'Approved'");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $donor = $result->fetch_assoc();
    $stmt->close();

    if ($donor && password_verify($password, $donor['password'])) {
      $_SESSION['donator_id'] = $donor['donator_id'];
      $_SESSION['donor_email'] = $donor['email'];
      header('Location: index.php');
      exit;
    } else {
      $error = "Invalid email or password, or account not approved yet.";
    }
  } elseif ($role == "Charity") {
    $query = "SELECT * FROM tbl_charity_login WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $charity = $result->fetch_assoc();

    if ($charity && password_verify($password, $charity['password'])) {
      $_SESSION['charity_id'] = $charity['charity_id'];
      $_SESSION['email'] = $charity['email'];
      header("Location: charity/charity_dashboard.php");
      exit();
    }
  } elseif ($role == "Admin") {
    $query = "SELECT * FROM tbl_admin WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
      $_SESSION['admin_id'] = $admin['admin_id'];
      $_SESSION['admin_name'] = $admin['admin_name'];
      header("Location: admin/admin_dashboard.php");
      exit();
    }
  }

  $_SESSION['login_error'] = $error;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NEUKAI</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="js/navbarScroll.js" defer></script>
  <script src="js/slideAnimation.js" defer></script>
  <script src="js/loading.js" defer></script>
  <script src="js/mobilenav.js" defer></script>
  <link rel="stylesheet" href="css/index.css">
  <link rel="stylesheet" href="css/error.css">
  <link rel="icon" href="images/TempIco.png" type="image/x-icon">
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <style>
    body {
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      background-color: black;
      color: white;
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
  <?php include 'section/desktopNavbar.php'; ?>

  <!-- Mobile Menu -->
  <?php include 'section/mobilenavbar.php'; ?>

  <div id="loading-overlay"
    class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <img src="images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
  </div>
  <div id="top" class="flex-grow flex justify-center px-4 mt-[200px] min-h-screen z-0">
    <div class="w-full max-w-[800px] h-auto max-h-[640px] md:h-auto bg-white rounded-3xl overflow-hidden">

      <div class="relative flex flex-col items-center justify-center p-6 bg-black/60 bg-[url('images/background.png')] bg-cover bg-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-lg z-0"></div>
        <div class="relative z-10 flex flex-col items-center">
          <img src="images/signin.png" alt="Sign In" class="w-8 h-8 mb-2 object-contain" />
          <span class="text-[24pt] font-bold text-white">LOGIN</span>
        </div>
      </div>

      <div class="bg-white/70 p-6 space-y-4">
        <?php if (isset($error)) echo "<p class='text-red-500 text-center mb-4'>$error</p>"; ?>

        <form action="" method="POST">
          <div class="input-group">
            <label class="input-label">Select Role</label>
            <div class="input-container">
              <div class="icon-container">
                <img src="images/blacklogin.svg" alt="Sign In" class="w-8 h-8 mb-2 " />
              </div>
              <select name="role" class="text-black" required>
                <option value="" disabled selected>Select Role</option>
                <option value="Donor">Donor</option>
                <option value="Charity">Charity</option>
                <option value="Admin">Admin</option>
              </select>
            </div>
          </div>

          <div class="input-group">
            <label class="input-label">Email</label>
            <div class="input-container">
              <div class="icon-container">
                <img src="images/emailblack.svg" alt="Email" class="icon">
              </div>
              <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="email@email.com" class="text-black" required>
            </div>
          </div>

          <div class="input-group">
            <label class="input-label">Password</label>
            <div class="input-container">
              <div class="icon-container">
                <img src="images/lock.svg" alt="Password" class="icon">
              </div>
              <input type="password" name="password" placeholder="**********" class="text-black" required>
            </div>
            <div class="forgot-password">
              <a href="#">Forgot Password?</a>
            </div>
          </div>

          <button type="submit" class="login-button">Login</button>

          <div class="signup-text">
            Don't have an account? <a href="signup.php">Sign up</a>
          </div>
      </div>

      </form>
    </div>
  </div>
  </div>

  <div id="errorModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Error</h3>
      </div>
      <div class="modal-body">
        <div class="error-icon">
          <i class="fas fa-exclamation-circle"></i>
        </div>
        <p id="errorMessage"></p>
      </div>
      <div class="modal-footer">
        <button class="modal-close" onclick="closeModal()">Close</button>
      </div>
    </div>
  </div>

  <!-- Parallax Background -->
  <?php include 'section/parallaxbg.php'; ?>
</body>

</html>

<script>
  function showErrorModal(message) {

    document.getElementById('errorMessage').textContent = message;


    const modal = document.getElementById('errorModal');
    modal.classList.add('show');


    const modalContent = document.querySelector('.modal-content');
    modalContent.style.transform = 'translateY(100%)';
    modalContent.style.opacity = '0';

   
    setTimeout(() => {
      modalContent.style.transition = 'transform 0.5s cubic-bezier(0.19, 1, 0.22, 1), opacity 0.5s ease';
      modalContent.style.transform = 'translateY(0)';
      modalContent.style.opacity = '1';
    }, 10);
  }

  function closeModal() {
    const modalContent = document.querySelector('.modal-content');

    modalContent.style.transition = 'transform 0.4s cubic-bezier(0.215, 0.61, 0.355, 1), opacity 0.3s ease';
    modalContent.style.transform = 'translateY(100%)';
    modalContent.style.opacity = '0';

    setTimeout(() => {
      const modal = document.getElementById('errorModal');
      modal.classList.remove('show');
    }, 400);
  }

  window.onload = function() {
    <?php if (!empty($_SESSION['login_error'])): ?>
      showErrorModal("<?php echo $_SESSION['login_error']; ?>");
      <?php
      
      unset($_SESSION['login_error']);
      ?>
    <?php endif; ?>
  };
</script>