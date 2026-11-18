<?php require_once __DIR__ . '/../../app/bootstrap.php'; ?><?php require_admin(); ?><?php
require_once __DIR__ . '/../../app/services/mail/admin-mail.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_ticket'])) {
    csrf_verify();
    $ticket_id = (int) $_POST['approve_ticket'];
    $stmt = $conn->prepare("SELECT email, role FROM password_reset_tickets WHERE ticket_id = ? AND status = 'Pending'");
    $stmt->bind_param("i", $ticket_id);
    $stmt->execute();
    $ticket = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($ticket) {
        $email = $ticket['email'];
        $role = $ticket['role'];

        $table_map = [
            'Donor' => 'tbl_donor',
            'Charity' => 'tbl_charity_login',
            'Admin' => 'tbl_admin'
        ];

        if (isset($table_map[$role])) {
            $table = $table_map[$role];
            $temporaryPassword = bin2hex(random_bytes(6));
            $hashed_password = password_hash($temporaryPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("UPDATE $table SET password = ? WHERE email = ?");
            $stmt->bind_param("ss", $hashed_password, $email);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("UPDATE password_reset_tickets SET status = 'Approved' WHERE ticket_id = ?");
            $stmt->bind_param("i", $ticket_id);
            $stmt->execute();
            $stmt->close();

            sendResetApprovedEmail($email, $role, $temporaryPassword);
        }
    }

    redirect('admin_page.php#admin_reset_request');
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
                                <a class="view-link" href="id_image.php?ticket_id=<?= (int) $row['ticket_id'] ?>" target="_blank" rel="noopener" title="View ID Image">
                                    <i class="fas fa-eye" style="padding: 0 20px 0 10px ;"></i>
                                </a>
                                <?= e($row['email']) ?>
                            </div>
                        </td>
                        <td><?= e($row['role']) ?></td>
                        <td><?= e($row['status']) ?></td>
                        <td>
                            <?php if ($row['status'] === 'Pending'): ?>
                                <form method="post" action="admin_reset_request.php" style="display:inline"
                                    onsubmit="return confirm('Approve this password reset request?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="approve_ticket" value="<?= (int) $row['ticket_id'] ?>">
                                    <button type="submit" class="approve-btn">Approve</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>