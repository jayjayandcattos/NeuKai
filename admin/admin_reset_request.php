<?php
session_start();
include '../configuration/db_connect.php';

if (isset($_GET['approve'])) {
    $ticket_id = intval($_GET['approve']);
    $stmt = $conn->prepare("SELECT email, role FROM password_reset_tickets WHERE ticket_id = ?");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $ticket = $result->fetch_assoc();

    if ($ticket) {
        $email = $ticket['email'];
        $role = $ticket['role'];
        $default_password = password_hash('password123', PASSWORD_DEFAULT);

        $table_map = [
            'Donor' => 'tbl_donor',
            'Charity' => 'tbl_charity_login',
            'Admin' => 'tbl_admin'
        ];
        $table = $table_map[$role];

        $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
        $stmt->bind_param("ss", $default_password, $email);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE password_reset_tickets SET status = 'Approved' WHERE ticket_id = ?");
        $stmt->bind_param("i", $ticket_id);
        $stmt->execute();

    }

    header("Location: admin_reset_request.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password Requests</title>
</head>
<body>
    <h2>Password Reset Tickets</h2>
    <table border="1">
        <tr>
            <th>Email</th>
            <th>Role</th>
            <th>ID Image</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        $result = $conn->query("SELECT * FROM password_reset_tickets ORDER BY requested_at DESC");
        while ($row = $result->fetch_assoc()):
        ?>
        <tr>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= $row['role'] ?></td>
            <td><a href="../<?= $row['id_image_path'] ?>" target="_blank">View ID</a></td>
            <td><?= $row['status'] ?></td>
            <td>
                <?php if ($row['status'] === 'Pending'): ?>
                    <a href="?approve=<?= $row['ticket_id'] ?>" onclick="return confirm('Approve and reset password?')">Approve</a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</body>
</html>