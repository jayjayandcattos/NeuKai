<?php
session_start();
include '../configuration/db_connect.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);


// Handle Adding New Admin
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    if (!empty($first_name) && !empty($last_name) && !empty($email) && !empty($_POST['password'])) {
        try {
            $stmt = $conn->prepare("INSERT INTO tbl_admin (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);
            $stmt->execute();
            $_SESSION['success'] = "Admin added successfully!";
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) {
                $_SESSION['error'] = "Email already exists. Please use a different email.";
            } else {
                $_SESSION['error'] = "Failed to add admin: " . $e->getMessage();
            }
        }
    } else {
        $_SESSION['error'] = "All fields are required!";
    }
    header("Location: admin_page.php#admin_list");
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
    header("Location: admin_page.php#admin_list");
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
    header("Location: admin_page.php#admin_list");
    exit();
}

// Fetch All Admins
$result = $conn->query("SELECT * FROM tbl_admin ORDER BY created_at DESC");

// Total number of admins
$total_admins = $result->num_rows;
?>

<!-- Main Content -->
<div class="main-content">
    <div class="title">
        <h2>MANAGE ADMINS</h2>
        <h2 style="color: #aaa;">Existing Admins : <?= $total_admins ?></h2>
    </div>

    <br>
    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <p style="color: green;"><?php echo $_SESSION['success'];
        unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p style="color: red;"><?php echo $_SESSION['error'];
        unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <!-- Form to Add Admin -->
    <div class="collapsible-wrapper">
        <div class="collapsible-header">
            <h3> + Add Admin</h3>
            <span class="arrow-icon rotate">&#9654;</span>
        </div>
        <div class="collapsible-body">
            <form id="form-add-admin" action="admin_list.php" method="POST" class="form_admin">
                <table class="admin-form-table">
                    <tr>
                        <td class="icon" rowspan="1"><i class="fas fa-user"></i></td>
                        <td>
                            <div class="input-row">
                                <div class="input-group">
                                    <input type="text" name="first_name"
                                        value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>"
                                        required placeholder=" ">
                                    <label>First Name</label>
                                </div>
                                <div class="input-group">
                                    <input type="text" name="last_name"
                                        value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>"
                                        required placeholder=" ">
                                    <label>Last Name</label>
                                </div>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="icon"><i class="fas fa-envelope"></i></td>
                        <td>
                            <div class="input-group">
                                <input type="email" name="email"
                                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                    required placeholder=" ">
                                <label>Email</label>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="icon"><i class="fas fa-lock"></i></td>
                        <td>
                            <div class="input-group">
                                <input type="password" name="password" required placeholder=" ">
                                <label>Password</label>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td class="icon"><i class="fas fa-user-tag"></i></td>
                        <td>
                            <div class="input-group">
                                <select name="role" required>
                                    <option value="" disabled selected hidden>- Select Role -</option>
                                    <option value="main_admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'main_admin') ? 'selected' : ''; ?>>Main Admin</option>
                                    <option value="assistant_admin" <?php echo (isset($_POST['role']) && $_POST['role'] == 'assistant_admin') ? 'selected' : ''; ?>>Assistant Admin
                                    </option>
                                </select>
                            </div>
                        </td>
                    </tr>
                </table>

                <input type="submit" name="add_admin" value="Add Admin" class="btn btn-primary">
            </form>
        </div>
    </div>
    <br>

    <!-- Display Admins -->
    <?php if ($result->num_rows > 0): ?>
        <div class="admin-table input-group">
            <div class="admin-row admin-header">
                <div class="admin-cell">First Name</div>
                <div class="admin-cell">Last Name</div>
                <div class="admin-cell">Email</div>
                <div class="admin-cell">Role</div>
                <div class="admin-cell">Actions</div>
            </div>

            <?php while ($row = $result->fetch_assoc()): ?>
                <form id="form-update-admin-<?= $row['admin_id'] ?>" action="admin_list.php" method="POST" class="admin-row">
                    <input type="hidden" name="admin_id" value="<?= $row['admin_id'] ?>">

                    <div class="admin-cell">
                        <input type="text" name="first_name" value="<?= htmlspecialchars($row['first_name']) ?>" required>
                    </div>
                    <div class="admin-cell">
                        <input type="text" name="last_name" value="<?= htmlspecialchars($row['last_name']) ?>" required>
                    </div>
                    <div class="admin-cell">
                        <input type="email" name="email" value="<?= htmlspecialchars($row['email']) ?>" required>
                    </div>
                    <div class="admin-cell">
                        <select name="role" required>
                            <option value="main_admin" <?= $row['role'] == 'main_admin' ? 'selected' : '' ?>>Main Admin</option>
                            <option value="assistant_admin" <?= $row['role'] == 'assistant_admin' ? 'selected' : '' ?>>Assistant
                                Admin</option>
                        </select>
                    </div>
                    <div class="admin-cell">
                        <input type="submit" name="update_admin" value="Update" class="btn btn-update">
                        <a href="admin_list.php?delete=<?= $row['admin_id'] ?>" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to delete Admin <?php echo htmlspecialchars(addslashes($row['first_name'] . ' ' . $row['last_name'])); ?>?')">
                            Delete</a>
                    </div>
                </form>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <p>No admins available.</p>
    <?php endif; ?>


</div>