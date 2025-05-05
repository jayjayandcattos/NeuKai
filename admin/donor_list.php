<?php
session_start();
include '../configuration/db_connect.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Approval
if (isset($_GET['approve'])) {
  $donator_id = intval($_GET['approve']);
  $info = $conn->prepare("SELECT email, first_name FROM tbl_donor WHERE donator_id = ?");
  $info->bind_param("i", $donator_id);
  $info->execute();
  $info->bind_result($email, $first_name);
  $info->fetch();
  $info->close();

  $stmt = $conn->prepare("UPDATE tbl_donor SET status = 'Approved' WHERE donator_id = ?");
  $stmt->bind_param("i", $donator_id);
  $stmt->execute();
  $stmt->close();
  require 'admin-mail-function.php'; // Make sure this file has your function
  sendDonorStatusEmail($email, $first_name, 'approved');
  header("Location: admin_page.php#donor_list");
  exit();
}

// Handle Decline
if (isset($_GET['decline'])) {
  $donator_id = intval($_GET['decline']);
  $info = $conn->prepare("SELECT email, first_name FROM tbl_donor WHERE donator_id = ?");
  $info->bind_param("i", $donator_id);
  $info->execute();
  $info->bind_result($email, $first_name);
  $info->fetch();
  $info->close();
  $stmt = $conn->prepare("UPDATE tbl_donor SET status = 'Declined' WHERE donator_id = ?");
  $stmt->bind_param("i", $donator_id);
  $stmt->execute();
  $stmt->close();
  require 'admin-mail-function.php';
  sendDonorStatusEmail($email, $first_name, 'declined');
  header("Location: admin_page.php#donor_list");
  exit();
}

// Handle Add Donor 
if (isset($_POST['add_donor'])) {
  $first_name = trim($_POST['first_name']);
  $middle_name = trim($_POST['middle_name']);
  $last_name = trim($_POST['last_name']);
  $email = trim($_POST['email']);
  $contact_no = trim($_POST['contact_no']);
  $password = password_hash("default123", PASSWORD_DEFAULT); // Default password

  // Email checker
  $check = $conn->prepare("SELECT email FROM tbl_donor WHERE email = ?");
  $check->bind_param("s", $email);
  $check->execute();
  $check->store_result();

  if ($check->num_rows == 0) {
      $stmt = $conn->prepare("INSERT INTO tbl_donor (first_name, middle_name, last_name, email, contact_no, password, status) VALUES (?, ?, ?, ?, ?, ?, 'Approved')");
      $stmt->bind_param("ssssss", $first_name, $middle_name, $last_name, $email, $contact_no, $password);
      $stmt->execute();
      $stmt->close();
      require 'admin-mail-function.php';
      addDonorMailer($email, $first_name);
      header("Location: admin_page.php#donor_list");
      exit();
  }
}

// Fetch Donors
$stmt = $conn->prepare("SELECT donator_id, first_name, middle_name, last_name, email, contact_no, status FROM tbl_donor");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<?php
$result = $conn->query("SELECT * FROM tbl_donor");

// Total number of admins
$total_donors = $result->num_rows;
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
  form {
    /* background-color: #f4f7fc; */
    padding: 20px;
    border-radius: 8px;
    max-width: 800px;
    margin: 0 auto;
  }

  form input[type="text"],
  form input[type="email"] {
    border: none;
    border-bottom: 2px solid #000;
    background-color: transparent;
    padding: 8px;
    width: 100%;
    font-size: 16px;
    margin-bottom: 20px;
    outline: none;
  }

  form input[type="submit"] {
    background-color: #000;
    color: #fff;
    padding: 10px 20px;
    border: none;
    cursor: pointer;
    font-weight: bold;
    margin-top: 10px;
  }

  form small {
    display: block;
    margin-top: 10px;
    font-size: 14px;
  }

  form strong {
    font-weight: bold;
  }

  .form-row {
    display: flex;
    gap: 20px;
    margin-bottom: 10px;
  }

  .form-group {
    flex: 1;
    display: flex;
    flex-direction: column;
  }

  /* ADD DONOR BUTTON */
  .form-submit-wrapper {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    margin-top: 10px;
    gap: 10px;
  }

  .form-submit-wrapper input[type="submit"] {
    margin-top: 0;
  }

  .form-submit-wrapper small {
    font-size: 12px;
    margin-left: 10px;
  }


  td {
    font-size: 13px;
  }

  th {
    text-align: center;
  }

  .admin-table table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  /* Header row */
  .admin-table th {
    padding: 20px 15px;
    text-align: left;
    font-weight: bold;
    background-color: #E8F0FE;
  }

  .admin-table td {
    text-align: left;
    border-bottom: 3px solid #E8F0FE;

  }

  .admin-table td {
    padding: 12px 15px;
    vertical-align: middle;
    word-wrap: break-word;
  }

  .admin-table .btn {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    font-size: 12px;
    transition: background-color 0.3s ease;
    cursor: pointer;
    width: 80px;
    height: 40px;
  }


  .admin-table .btn:hover {
    background-color: rgb(0, 29, 75);
  }

  .admin-table .btn-danger {
    background-color: #dc3545;
  }

  .admin-table .btn-danger:hover {
    background-color: rgb(153, 17, 31);
  }

  .admin-table .btn-approve {
    background-color: #28a745;
  }

  .admin-table .btn-approve:hover {
    background-color: #218838;
  }

  .admin-table .btn-warning {
    background-color: #aaa;
    color: #fff;
  }

  .admin-table .btn-warning:hover {
    background-color: #808080;
  }

  /* Status tags di ko muna aalisin itey */
  .pending {
    color: #e67e22;
    font-weight: bold;
  }

  .approved {
    color: #28a745;
    font-weight: bold;
  }

  .declined {
    color: #e74c3c;
    font-weight: bold;
  }

  .btn-eye {
    background-color: transparent;
    color: inherit;
    border: none;
    padding: 4px 6px;
    font-size: 14px;
    cursor: pointer;
    display: inline-block;
    text-decoration: none;
    opacity: 0.6;
    /* Makes the whole button (including icon) more transparent */
    transition: opacity 0.2s ease;
  }

  .btn-eye:hover {
    opacity: 1;
    /* Full opacity on hover for feedback */
  }


  .btn-eye i {
    vertical-align: middle;
    font-size: 16px;
  }


  .btn-eye i {
    vertical-align: middle;
    font-size: 16px;
  }

  .icon-input {
    position: relative;
  }

  .icon-input i {
    position: absolute;
    top: 50%;
    left: -50px;
    transform: translateY(-50%);
    color: #555;
  }

  .icon-input input {
    padding-left: 35px;
  }
</style>

<div class="main-content">
  <div class="title">
    <h2>MANAGE DONORS</h2>
    <h2 style="color: #aaa; position: relative;">Existing Donors : <?= $total_donors ?></h2>
  </div>
  <br>
  <!-- Display messages -->
  <?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
    <?php unset($_SESSION['message']); ?>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
  <?php endif; ?>

  <!-- Add Donor Form -->
  <div class="collapsible-wrapper">
    <div class="collapsible-header">
      <h3> + Add Donor</h3>
      <span class="arrow-icon rotate">&#9654;</span>
    </div>
    <div class="collapsible-body">
      <form id="add-donor-form" action="donor_list.php" method="POST">
        <div class="form-row">
          <div class="form-group icon-input">
            <i class="fas fa-user"></i>
            <input type="text" name="first_name" placeholder="First Name" required>
          </div>
          <div class="form-group icon-input">
            <input type="text" name="middle_name" placeholder="Middle Name">
          </div>
          <div class="form-group icon-input">
            <input type="text" name="last_name" placeholder="Last Name" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group icon-input">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="Email" required>
          </div>
        </div>
        <div class="form-group icon-input">
            <i class="fas fa-phone"></i>
            <input type="text" name="contact_no" placeholder="Contact Number" required>
          </div>

        <div class="form-submit-wrapper">
          <input type="submit" name="add_donor" value="Add Donor">
          <small><i>Default password: <strong>default123</strong></i></small>
        </div>
      </form>

    </div>
  </div>
  <br>
  <?php if ($result->num_rows > 0): ?>
    <div class="admin-table input-group">
      <table>
        <thead>
          <tr>
            <th style="width: 80px;">View</th>
            <th>Name</th>
            <th>Email</th>
            <th>Contact</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($row = $result->fetch_assoc()): ?>
            <?php $status = trim($row['status'] ?? ''); ?>
            <tr>
              <td>
                <a href="donor_profile.php?donator_id=<?= $row['donator_id'] ?>" class="btn-eye">
                  <i class="fa fa-eye"></i>
                </a>
              </td>
              <td>
                <?= htmlspecialchars($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name']) ?>
              </td>
              <td><?= htmlspecialchars($row['email']) ?></td>
              <td><?= htmlspecialchars($row['contact_no']) ?></td>
              <td>
                <?php if ($status == 'Pending'): ?>
                  <span class="pending">Pending</span>
                <?php elseif ($status == 'Approved'): ?>
                  <span class="approved">Approved</span>
                <?php elseif ($status == 'Declined'): ?>
                  <span class="declined">Declined</span>
                <?php else: ?>
                  <span style="color: gray;">Unknown</span>
                <?php endif; ?>
              </td>
              <td>
                <a href="edit_donor.php?id=<?= $row['donator_id'] ?>" class="btn">Edit</a>
                <a href="donor_list.php?delete=<?= $row['donator_id'] ?>" class="btn btn-danger"
                  onclick="return confirm('Are you sure?')">Delete</a>
                <?php if ($status == 'Pending'): ?>
                  <a href="donor_list.php?approve=<?= $row['donator_id'] ?>" class="btn btn-approve"
                    onclick="return confirm('Approve this donor?')">Approve</a>
                  <a href="donor_list.php?decline=<?= $row['donator_id'] ?>" class="btn btn-warning"
                    onclick="return confirm('Decline this donor?')">Decline</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  <?php else: ?>
    <p>No donors found.</p>
  <?php endif; ?>
</div>

<script src="a_script.js"></script>