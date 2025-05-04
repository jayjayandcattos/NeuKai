<?php
require '../configuration/db_connect.php';
require 'admin-mail-function.php';

if (isset($_GET['action'], $_GET['charity_id'])) {
    $action = $_GET['action'];
    $charityId = intval($_GET['charity_id']);

    $stmt = $conn->prepare("SELECT charity_name, email FROM tbl_charity WHERE charity_id = ?");
    $stmt->bind_param("i", $charityId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $charityname = $row['charity_name'];
        $email = $row['email'];

        if ($action === 'approve') {
            sendApprovalEmail($email, $charityname, $charityId);
        } elseif ($action === 'decline') {
            sendDeclineEmail($email, $charityname, $charityId);
        }

        header("Location: charity_list.php?message=Email Sent Successfully");
        exit;
    } else {
        header("Location: charity_list.php?message=Charity Not Found");
        exit;
    }
}
?>