<?php
session_start();
include '../configuration/db_connect.php';
require 'admin-mail-function.php';
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

        sendResetApprovedEmail($email, $role);
    }
    
    header("Location: admin_page.php#admin_reset_request");
    exit;
}
?>

<style>
    .table-wrapper {
        background: #fff;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    table {
        width: 100%;
        border-collapse: collapse;
        color: #121820;
    }

    thead tr {
        background: #E8F0FE;
        text-align: center;
        font-weight: bold;
    }

    thead th,
    tbody td {
        padding: 15px 0;
        text-align: center;
        vertical-align: middle;
        word-wrap: break-word;
    }

    tbody tr {
        background: #fff;
        transition: background-color 0.2s;
    }

    tbody tr:hover {
        background: #f9fafc;
    }

    .email-cell {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.5rem;
        width: 100%;
        padding-left: 10px;
    }

    .email-cell i {
        color: #555;
        margin-top: 1px
    }

    .view-link {
        color: #888;
        font-weight: 600;
        text-decoration: none;
    }

    .view-link:hover {
        text-decoration: underline;
    }

    .approve-btn {
        background: #00ac5f;
        color: #fff;
        padding: 8px 16px;
        border-radius: 6px;
        font-weight: 600;
        text-decoration: none;
        display: inline-block;
    }

    .approve-btn:hover {
        background: #009c56;
    }
</style>

<div class="container">
    <h2>PASSWORD RESET REQUESTS</h2>
    <br>
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th scope="col" style="text-align: left; padding-left: 70px;">Email</th>
                    <th scope="col">Role</th>
                    <th scope="col">Status</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result = $conn->query("SELECT * FROM password_reset_tickets ORDER BY requested_at DESC");
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td>
                            <div class="email-cell">
                                <a class="view-link" href="../<?= $row['id_image_path'] ?>" target="_blank" title="View ID Image">
                                    <i class="fas fa-eye" style="padding: 0 20px 0 10px ;"></i>
                                </a>
                                <?= htmlspecialchars($row['email']) ?>
                            </div>
                        </td>
                        <td><?= $row['role'] ?></td>
                        <td><?= $row['status'] ?></td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <a href="admin_reset_request.php?approve=<?= $row['ticket_id'] ?>" class="approve-btn">Approve</a>
                            <?php endif; ?>
                            <!-- tinangal ko nalang yung onclick hsjahahhahshajsa-->
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>