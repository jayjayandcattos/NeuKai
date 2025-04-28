<?php
session_start();
include 'configuration/db_connect.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email']));
    $role = $_POST['role'];
    $attachment = $_FILES['id_image'];

    // Check if user exists
    $table_map = [
        'Donor' => 'tbl_donor',
        'Charity' => 'tbl_charity_login',
        'Admin' => 'tbl_admin'
    ];

    $table = $table_map[$role];
    $stmt = $conn->prepare("SELECT email FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Save ID image
        $upload_dir = 'uploads/id_verifications/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        $filename = uniqid() . "_" . basename($attachment['name']);
        $target_file = $upload_dir . $filename;

        if (move_uploaded_file($attachment["tmp_name"], $target_file)) {
            // Log ticket to table
            $stmt = $conn->prepare("INSERT INTO password_reset_tickets (email, role, id_image_path, status) VALUES (?, ?, ?, 'Pending')");
            $stmt->bind_param("sss", $email, $role, $target_file);
            $stmt->execute();
            $success = "Your reset request has been sent. Please wait for admin approval.";
        } else {
            $error = "Error uploading file.";
        }
    } else {
        $error = "No account found with that email.";
    }

    $stmt->close();
}
?>

<!-- HTML -->
<!DOCTYPE html>
<html>
<head>
    <title>Forgot Password</title>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>
<div class="container">
    <h2>Forgot Password</h2>
    <?php if ($error): ?><p style="color: red;"><?= $error ?></p><?php endif; ?>
    <?php if ($success): ?><p style="color: green;"><?= $success ?></p><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <label>Select Role:</label>
        <select name="role" required>
            <option value="">Select Role</option>
            <option value="Donor">Donor</option>
            <option value="Charity">Charity</option>
            <option value="Admin">Admin</option>
        </select>

        <label>Email:</label>
        <input type="email" name="email" required>

        <label>Upload Valid ID (jpg, png):</label>
        <input type="file" name="id_image" accept="image/*" required>

        <button type="submit">Send Reset Request</button>
        <a href="login.php">Back to Login</a>
    </form>
</div>
</body>
</html>