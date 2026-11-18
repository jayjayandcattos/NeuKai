<?php require_once __DIR__ . '/../app/bootstrap.php'; ?><?php

$email = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $role = (string) ($_POST['role'] ?? '');

    $error = 'Invalid email or password.';

    if (!in_array($role, ['Donor', 'Charity', 'Admin'], true)) {
        $error = 'Please select a valid account role.';
    } elseif ($email === '' || $password === '') {
        $error = 'Please enter your email and password.';
    } elseif ($role === 'Donor') {
        $stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE email = ? AND status = 'Approved'");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $donor = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($donor && password_verify($password, $donor['password'])) {
            session_regenerate_id(true);
            $_SESSION['donator_id'] = (int) $donor['donator_id'];
            $_SESSION['donor_email'] = $donor['email'];
            redirect('donorhome.php');
        }

        $error = 'Invalid email or password, or the account is not approved yet.';
    } elseif ($role === 'Charity') {
        $stmt = $conn->prepare('SELECT * FROM tbl_charity_login WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $charity = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($charity && password_verify($password, $charity['password'])) {
            session_regenerate_id(true);
            $_SESSION['charity_id'] = (int) $charity['charity_id'];
            $_SESSION['email'] = $charity['email'];
            redirect('charity/charity_dashboard.php');
        }
    } elseif ($role === 'Admin') {
        $stmt = $conn->prepare('SELECT * FROM tbl_admin WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['admin_id'];
            $_SESSION['admin_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['role'] = $admin['role'];
            redirect('admin/admin_page.php#admin_dashboard');
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
  <script src="assets/js/navbarScroll.js" defer></script>
  <script src="assets/js/slideAnimation.js" defer></script>
  <script src="assets/js/loading.js" defer></script>
  <script src="assets/js/mobilenav.js" defer></script>
  <link rel="stylesheet" href="assets/css/index.css">
  <link rel="stylesheet" href="assets/css/error.css">
  <link rel="icon" href="assets/images/TempIco.png" type="image/x-icon">
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
<?php require_once __DIR__ . '/../app/includes/desktopNavbar.php'; ?>

  <!-- Mobile Menu -->
<?php require_once __DIR__ . '/../app/includes/mobilenavbar.php'; ?>

  <div id="loading-overlay"
    class="fixed inset-0 bg-black flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
    <img src="assets/images/Neukai Logo.svg" alt="Loading" class="loading-logo w-50 h-50" />
  </div>
  <div id="top" class="flex-grow flex justify-center px-4 mt-[200px] min-h-screen z-0">
    <div class="w-full max-w-[800px] h-auto max-h-[640px] md:h-auto bg-white rounded-3xl overflow-hidden">

      <div class="relative flex flex-col items-center justify-center p-6 bg-black/60 bg-[url('assets/images/background.png')] bg-cover bg-center">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-lg z-0"></div>
        <div class="relative z-10 flex flex-col items-center">
          <img src="assets/images/signin.png" alt="Sign In" class="w-8 h-8 mb-2 object-contain" />
          <span class="text-[24pt] font-bold text-white">LOGIN</span>
        </div>
      </div>

      <div class="bg-white/70 p-6 space-y-4">
        <?php if ($error !== ''): ?>
          <p class="text-red-500 text-center mb-4"><?= e($error) ?></p>
        <?php endif; ?>

        <form action="" method="POST"><?= csrf_field() ?>
          <div class="input-group">
            <label class="input-label">Select Role</label>
            <div class="input-container">
              <div class="icon-container">
                <img src="assets/images/blacklogin.svg" alt="Sign In" class="w-8 h-8 mb-2 " />
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
                <img src="assets/images/emailblack.svg" alt="Email" class="icon">
              </div>
              <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="email@email.com" class="text-black" required>
            </div>
          </div>

          <div class="input-group">
            <label class="input-label">Password</label>
            <div class="input-container">
              <div class="icon-container">
                <img src="assets/images/lock.svg" alt="Password" class="icon">
              </div>
              <input type="password" name="password" placeholder="**********" class="text-black" required>
            </div>
            <div class="forgot-password">
            <a href="forgot_password.php">Forgot Password?</a>
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
<?php require_once __DIR__ . '/../app/includes/parallaxbg.php'; ?>
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
      showErrorModal(<?= json_encode($_SESSION['login_error'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>);
      <?php unset($_SESSION['login_error']); ?>
    <?php endif; ?>
  };
</script>