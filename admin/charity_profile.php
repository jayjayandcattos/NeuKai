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
<meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Dashboard</title>
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link rel="stylesheet" href="a_styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style> 

.charity-container {
    max-width: 800px;
    margin: 0 auto;
    margin-top: 60px;
    background-color: #ffffff;
    padding: 30px 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
    overflow-y: auto;
    max-height: 700px;
}

img[alt="Charity Photo"] {
    width: 200px;
    height: 200px;
    object-fit: cover;
    border-radius: 5px;
    display: block;
    margin: 0 auto;
}

img[alt="Charity Photo"]::before {
    content: '';
    display: block;
}

img[alt="Charity Photo"] {
    padding: 10px;
    border: 2px solid #ccc;
    background-color: #f9f9f9;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.profile-container {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 30px;
    flex-wrap: wrap;
}

.charity-info {
    flex: 1;
    min-width: 250px;
}

.charity-photo {
    flex-shrink: 0;
    padding: 10px;
    border: 5px solid #ddd;
    border-radius: 8px;
    background-color: #fff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
}

.charity-photo img {
    width: 150px;
    height: 150px;
    object-fit: cover;
    border-radius: 5px;
    margin-top: 60px;

}


h2 {
    color: #007bff;
    font-size: 24px;
    border-bottom: 3px solid #007bff;
    padding-bottom: 10px;
    margin-bottom: 25px;
}

p {
    margin: 12px 0;
    font-size: 16px;
}

strong {
    display: inline-block;
    width: 130px;
    color: #444;
}

img {
    display: block;
    margin: 20px auto;
    border-radius: 6px;
    border: 1px solid #ccc;
    width: 100px;
    height: 100px;
    object-fit: cover;
}

a {
    color: #007bff;
    text-decoration: none;
    margin-top: 20px;
    display: inline-block;
}

a:hover {
    text-decoration: underline;
}

</style>
    
</head>
<body>
<div class="charity-container">
    <h2><?= htmlspecialchars($charity['charity_name']); ?> - Profile</h2>
    <div class="profile-container">
    <div class="charity-info">
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
    </div>
    <p>
        <?php if (!empty($charity['charity_photo'])) {
            echo "<div>
                    <img src='data:image/jpeg;base64," . base64_encode($charity['charity_photo']) . "' alt='Charity Photo' width='100' height='100' />
                </div>";
        } else {
            echo "<p>No image available.</p>";
        }
        ?>
          </div>
    </p>
    
    <a href="admin_page.php#charity_list">Back to Charity List</a>
</div>
</body>
</html>

<?php mysqli_close($conn); ?>
