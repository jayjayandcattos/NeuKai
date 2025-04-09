<?php
session_start();
if (!isset($_SESSION['charity_id'])) {
    header("Location: charity_login.php");
    exit();
}
?>
