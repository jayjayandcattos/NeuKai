<?php
session_start();
require('configuration/db_connect.php');

// Check if the donator is logged in
$loggedin = isset($_SESSION['donator_id']); 

// Fetch Charities
$query = "SELECT * FROM tbl_charity";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donate Now</title>
</head>
<body>
<a href='index.php'>Home</a>
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="charity-item">
            <?php if (!empty($row['charity_photo'])): ?>
                <div class="charity-photo">
                    <img src="data:image/jpeg;base64,<?= base64_encode($row['charity_photo']) ?>" alt="Charity Image" width="100" height="100" />
                </div>
            <?php else: ?>
                <p>No image available.</p>
            <?php endif; ?>
            <p>
                <?= htmlspecialchars($row['barangay']) . ', ' . 
                    htmlspecialchars($row['municipality']) ?>
            </p>
            <h3><a href="donate_now.php?charity_id=<?= $row['charity_id'] ?>"><?= htmlspecialchars($row['charity_name']) ?></a></h3>
        </div>
    <?php endwhile; ?>
</body>
</html>
