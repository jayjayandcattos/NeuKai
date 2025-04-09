<?php
session_start();
require '../configuration/db_connect.php';

// Check if the charity ID is passed in the URL
if (!isset($_GET['charity_id'])) {
    die("No charity specified.");
}
$charity_id = $_GET['charity_id'];

// Check if the admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_id = $_SESSION['admin_id'];

$stmt = $conn->prepare("SELECT * FROM tbl_charity WHERE charity_id = ?");
$stmt->bind_param("i", $charity_id); 
$stmt->execute();
$result = $stmt->get_result();
$charity = $result->fetch_assoc(); 
$stmt->close();

if (!$charity) {
    die("Charity not found in the database.");
}

// Format the full address
$full_address = htmlspecialchars($charity['street_address'] . ', ' . $charity['barangay'] . ', ' . $charity['municipality'] . ', ' . $charity['province']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($charity['charity_name']); ?> - Profile</title>
</head>
<body>

    <h1><?= htmlspecialchars($charity['charity_name']); ?> - Profile</h1>
    <p><strong>Registration No:</strong> <?= htmlspecialchars($charity['charity_reg_no']); ?></p>
    <p><strong>Establishment Date:</strong> <?= htmlspecialchars($charity['establishment_date']); ?></p>
    <p><strong>Description:</strong> <?= htmlspecialchars($charity['charity_description']); ?></p>
    <p><strong>Website:</strong> 
        <?php
        if (!empty($charity['website'])) {
            echo '<a href="' . htmlspecialchars($charity['website']) . '" target="_blank">' . htmlspecialchars($charity['website']) . '</a>';
        } else {
            echo 'none'; 
        }
        ?>
    </p>
    <p><strong>Email:</strong> <?= htmlspecialchars($charity['email']); ?></p>
    <p><strong>Address:</strong> <?= $full_address; ?></p>
    <p>
        <?php if (!empty($charity['charity_photo'])) {
            echo "<div>
                    <img src='data:image/jpeg;base64," . base64_encode($charity['charity_photo']) . "' alt='Charity Photo' width='100' height='100' />
                </div>";
        } else {
            echo "<p>No image available.</p>";
        }
        ?>
    </p>
    
    <a href="charity_list.php">Back to Charity List</a>

</body>
</html>

<?php mysqli_close($conn); ?>
