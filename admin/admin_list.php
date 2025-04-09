<?php
session_start();
include '../configuration/db_connect.php';

// Handle Adding New Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Secure password hashing
    $role = $_POST['role']; // Should be main_admin or assistant_admin

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($_POST['password'])) {
        $stmt = $conn->prepare("INSERT INTO tbl_admin (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Admin added successfully!";
        } else {
            $_SESSION['error'] = "Failed to add admin.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "All fields are required!";
    }
    header("Location: admin_list.php");
    exit();
}

// Handle Updating Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_admin'])) {
    $admin_id = $_POST['admin_id'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $role = $_POST['role']; // main_admin or assistant_admin

    if (!empty($first_name) && !empty($last_name) && !empty($email)) {
        $stmt = $conn->prepare("UPDATE tbl_admin SET first_name=?, last_name=?, email=?, role=? WHERE admin_id=?");
        $stmt->bind_param("ssssi", $first_name, $last_name, $email, $role, $admin_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Admin updated successfully!";
        } else {
            $_SESSION['error'] = "Failed to update admin.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "All fields are required!";
    }
    header("Location: admin_list.php");
    exit();
}

// Handle Deletion
if (isset($_GET['delete'])) {
    $admin_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM tbl_admin WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin deleted successfully!";
    } else {
        $_SESSION['error'] = "Failed to delete admin.";
    }
    $stmt->close();
    header("Location: admin_list.php");
    exit();
}

// Fetch All Admins
$result = $conn->query("SELECT * FROM tbl_admin ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #007bff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-danger {
            background: #dc3545;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2 style="text-align: center;">Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="charity_list.php">Charity</a>
    <a href="donor_list.php">Donors</a>
    <a href="admin_list.php" class="active">Admins</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- Main Content -->
<div class="main-content">
    <h2>Manage Admins</h2>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <!-- Form to Add Admin -->
    <h3>Add New Admin</h3>
    <form action="admin_list.php" method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br><br>
        <input type="email" name="email" placeholder="Email" required><br><br>
        <input type="password" name="password" placeholder="Password" required><br><br>
        <select name="role" required>
            <option value="main_admin">Main Admin</option>
            <option value="assistant_admin">Assistant Admin</option>
        </select><br><br>
        <input type="submit" name="add_admin" value="Add Admin">
    </form>

    <hr>

    <!-- Display Admins -->
    <h3>Existing Admins</h3>
    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['first_name']) ?></td>
                <td><?= htmlspecialchars($row['last_name']) ?></td>
                <td><?= htmlspecialchars($row['email']) ?></td>
                <td><?= htmlspecialchars($row['role']) ?></td>
                <td>
                    <form action="admin_list.php" method="POST" style="display:inline;">
                        <input type="hidden" name="admin_id" value="<?= $row['admin_id'] ?>">
                        <input type="text" name="first_name" value="<?= $row['first_name'] ?>" required>
                        <input type="text" name="last_name" value="<?= $row['last_name'] ?>" required>
                        <input type="email" name="email" value="<?= $row['email'] ?>" required>
                        <select name="role">
                            <option value="main_admin" <?= $row['role'] == 'main_admin' ? 'selected' : '' ?>>Main Admin</option>
                            <option value="assistant_admin" <?= $row['role'] == 'assistant_admin' ? 'selected' : '' ?>>Assistant Admin</option>
                        </select>
                        <input type="submit" name="update_admin" value="Update">
                    </form>
                    <a href="admin_list.php?delete=<?= $row['admin_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No admins available.</p>
    <?php endif; ?>

</div>

</body>
</html>