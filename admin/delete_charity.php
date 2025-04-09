<?php
require_once "../configuration/db_connect.php";

$charity_id = $_GET['id'] ?? null;

if ($charity_id) {
    $query = "DELETE FROM tbl_charity WHERE charity_id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $charity_id);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: charity_list.php");
        exit();
    } else {
        echo "Error deleting charity.";
    }
}

mysqli_close($conn);
?>