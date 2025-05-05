<?php
session_start();
if (!isset($_SESSION['admin_name']) || !isset($_SESSION['role'])) {
  header("Location: login.php");
  exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Admin Dashboard</title>
  <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
  <link rel="stylesheet" href="a_styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

  <div class="navbar">
    <div class="site-name">
      <img src="../images/NEUKAI Logo.svg" alt="Logo" style="height: 30px">
      Welcome <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
      - <?php
      $formatted_role = ucwords(str_replace('_', ' ', $_SESSION['role']));
      echo htmlspecialchars($formatted_role);
      ?>
    </div>
    <div class="datetime" id="datetime">Loading...</div>
  </div>

  <div class="main">
    <div class="sidebar">
      <div class="nav-links">
        <h1>ADMIN</h1>
        <a class="tab-link active" data-tab="admin_dashboard">DASHBOARD</a>
        <a class="tab-link" data-tab="donor_list">DONORS</a>
        <a class="tab-link" data-tab="charity_list">CHARITY</a>

        <?php if ($_SESSION['role'] !== 'assistant_admin'): ?>
          <a class="tab-link" data-tab="admin_list">ADMIN</a>
        <?php else: ?>
          <a class="tab-link disabled" title="Access Denied" style="pointer-events: none; opacity: 0.5;">ADMIN</a>
        <?php endif; ?>
        <a class="tab-link" data-tab="admin_reset_request">REQUESTS</a>

      </div>
      <div class="logout">
        <a href="../logout.php" class="logout-link">Logout</a>
      </div>
    </div>

    <div class="tab-container">
      <div class="tab-content" id="tab-content">
        <h2>Loading...</h2>
      </div>
    </div>
  </div>

  <script src="a_script.js"></script>

</body>

</html>