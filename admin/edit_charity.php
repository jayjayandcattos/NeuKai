<?php
include '../configuration/db_connect.php';

$charity_id = $_GET['id'];
$result = $conn->query("SELECT * FROM tbl_charity WHERE charity_id = $charity_id");
$row = $result->fetch_assoc();

if (isset($_POST['update_charity'])) {
    $stmt = $conn->prepare("UPDATE tbl_charity SET charity_name=?, charity_reg_no=?, establishment_date=?, charity_description=?, website=?, street_address=?, barangay=?, municipality=?, province=? WHERE charity_id=?");
    $stmt->bind_param("sssssssssi", $_POST['charity_name'], $_POST['charity_reg_no'], $_POST['establishment_date'], $_POST['charity_description'], $_POST['website'], $_POST['street_address'], $_POST['barangay'], $_POST['municipality'], $_POST['province'], $charity_id);

    if ($stmt->execute()) {
        header("Location: charity_list.php");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

<form action="" method="POST">
    <input type="text" name="charity_name" value="<?= $row['charity_name'] ?>" required>
    <input type="text" name="charity_reg_no" value="<?= $row['charity_reg_no'] ?>" required>
    <input type="date" name="establishment_date" value="<?= $row['establishment_date'] ?>" required>
    <textarea name="charity_description"><?= $row['charity_description'] ?></textarea>
    <input type="text" name="website" value="<?= $row['website'] ?>">
    <input type="text" name="street_address" value="<?= $row['street_address'] ?>">
    <input type="text" name="barangay" value="<?= $row['barangay'] ?>">
    <input type="text" name="municipality" value="<?= $row['municipality'] ?>">
    <input type="text" name="province" value="<?= $row['province'] ?>">
    <input type="submit" name="update_charity" value="Update">
</form>