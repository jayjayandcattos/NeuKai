<?php
session_start();
include '../configuration/db_connect.php';

// Declare error messages
$errors = [];

$charityname = '';
$charitynumber = '';
$establishmentdate = '';
$charitydesc = '';
$website = '';
$charity_image='';
$streetaddress = '';
$barangay = '';
$municipality = '';
$province = '';
$reg_image=''; 
$firstname = '';
$middlename = '';
$lastname = '';
$cp_email = '';
$phone=''; 
$email = '';
$password = '';

if (isset($_POST['submit'])) {
    // user inputs
    $charityname = $_POST['charityname'];
    $charitynumber =  mysqli_real_escape_string($conn, $_POST['charitynumber']);
    $establishmentdate =  mysqli_real_escape_string($conn, $_POST['establishmentdate']);
    $charitydesc =  mysqli_real_escape_string($conn, $_POST['charitydesc']);
    $website =  mysqli_real_escape_string($conn, $_POST['website']);
    $streetaddress =  mysqli_real_escape_string($conn, $_POST['streetaddress']);
    $barangay =  mysqli_real_escape_string($conn, $_POST['barangay']);
    $municipality =  mysqli_real_escape_string($conn, $_POST['municipality']);
    $province =  mysqli_real_escape_string($conn, $_POST['province']);
    $firstname =  mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename =  mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname =  mysqli_real_escape_string($conn, $_POST['lastname']);
    $cp_email =  mysqli_real_escape_string($conn, $_POST['cp_email']);
    $phone =  mysqli_real_escape_string($conn, $_POST['phone']);
    $email =  mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash("charity123", PASSWORD_DEFAULT);

        $stmt = $conn->prepare("SELECT email FROM tbl_charity_login WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error_message = "Email already exists. Please use a different email.";
        } else { 

            if (isset($_FILES['charity_image'])) {

                $charity_image = $_FILES['charity_image']; 
                if ($_FILES['charity_image']['error'] === UPLOAD_ERR_OK) {
                    
                    $fileType = $_FILES['charity_image']['type'];
                    $allowedTypes = ['image/jpeg','image/png', 'image/jpg']; 
                    if (!in_array($fileType, $allowedTypes)) {
                        die("Error: Invalid file type. Only JPEG, JPG, and PNG are allowed.");
                    }
                    $charity_image = file_get_contents($_FILES['charity_image']['tmp_name']);

                    if ($charity_image === false) {
                        die("Error reading the uploaded image. Please try again.");
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $imageType = $finfo->buffer($charity_image);

                    if (!in_array($imageType, $allowedTypes)) {
                        die("Error: The uploaded file is not a valid image type.");
                    }
                } else {

                    $errorCode = $_FILES['charity_image']['error'];
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.",
                        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
                    ];
        
                    $errorMessage = $errorMessages[$errorCode] ?? "An unknown error occurred during file upload.";
                    die("Error uploading the image: $errorMessage. Please try again.");
                }
            } else {
                die("No file upload detected. Please try again.");
            }

            if (isset($_FILES['reg_image'])) {

                $reg_image = $_FILES['reg_image']; 
                if ($_FILES['reg_image']['error'] === UPLOAD_ERR_OK) {
                    
                    $fileType = $_FILES['reg_image']['type'];
                    $allowedTypes = ['image/jpeg','image/png', 'image/jpg']; 
                    if (!in_array($fileType, $allowedTypes)) {
                        die("Error: Invalid file type. Only JPEG, JPG, and PNG are allowed.");
                    }
                    $reg_image = file_get_contents($_FILES['reg_image']['tmp_name']);

                    if ($reg_image === false) {
                        die("Error reading the uploaded image. Please try again.");
                    }

                    $finfo = new finfo(FILEINFO_MIME_TYPE);
                    $imageType = $finfo->buffer($reg_image);

                    if (!in_array($imageType, $allowedTypes)) {
                        die("Error: The uploaded file is not a valid image type.");
                    }
                } else {

                    $errorCode = $_FILES['reg_image']['error'];
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
                        UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive specified in the HTML form.",
                        UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
                        UPLOAD_ERR_NO_FILE => "No file was uploaded.",
                    ];
        
                    $errorMessage = $errorMessages[$errorCode] ?? "An unknown error occurred during file upload.";
                    die("Error uploading the image: $errorMessage. Please try again.");
                }
            } else {
                die("No file upload detected. Please try again.");
            }

            $stmt = $conn->prepare("INSERT INTO tbl_charity (charity_name, charity_reg_no, establishment_date, charity_description, website, charity_photo, street_address, barangay, municipality, province, verification_photo, email) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssssssssss', $charityname, $charitynumber, $establishmentdate, $charitydesc, $website, $charity_image, $streetaddress, $barangay, $municipality, $province, $reg_image, $email);

            if ($stmt->execute()) {
                $charity_id = $stmt->insert_id;

                $stmt = $conn->prepare("INSERT INTO tbl_charity_login (charity_id, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $charity_id, $email, $password);
                $stmt->execute(); 

                $stmt = $conn->prepare("INSERT INTO tbl_charity_contact_person (charity_id, first_name, middle_name, last_name, email, contact_no) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssss', $charity_id, $firstname, $middlename, $lastname, $cp_email, $phone);
                $stmt->execute(); 

                $_POST = array();
                header("Location: charity_list.php"); 

            } else {
                $error_message = "Error inserting customer details: " . $stmt->error;
            }

        }    
}

// Handle status change (approve, decline, delete)
if (isset($_GET['approve']) || isset($_GET['decline']) || isset($_GET['delete'])) {
    $charity_id = $_GET['approve'] ?? $_GET['decline'] ?? $_GET['delete'];

    if (isset($_GET['approve'])) {
        $status = 'approved'; // Correct status is set
        $query = "UPDATE tbl_charity SET status = ? WHERE charity_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $charity_id);
        if ($stmt->execute()) {
            // Log successful execution
            echo "Approval successful!";
        }
    }

    if (isset($_GET['decline'])) {
        $status = 'declined'; // Correct status for decline
        $query = "UPDATE tbl_charity SET status = ? WHERE charity_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $charity_id);
        if ($stmt->execute()) {
            // Log successful execution
            echo "Decline successful!";
        }
    }

    if (isset($_GET['delete'])) {
        $query = "DELETE FROM tbl_charity WHERE charity_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $charity_id);
        if ($stmt->execute()) {
            // Log successful deletion
            echo "Charity deleted!";
        }
    }

    // Always redirect after handling the actions
    header("Location: charity_list.php");
    exit();
}

// Fetch Charities
$query = "SELECT * FROM tbl_charity";
$result = $conn->query($query);

// Check for query error
if (!$result) {
    die("Error executing query: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charity List</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            background: #343a40;
            color: white;
            padding-top: 20px;
            position: fixed;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 15px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #007bff;
        }
        .main-content {
            margin-left: 250px;
            padding: 20px;
            width: calc(100% - 250px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 8px 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .btn-danger {
            background: #dc3545;
        }
        .form-group {
            margin-bottom: 10px;
        }
        input, textarea, button {
            display: block;
            width: 100%;
            padding: 8px;
            margin: 5px 0;
        }
        button {
            background: #28a745;
            color: white;
            border: none;
            cursor: pointer;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-warning {
            background: red;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h2 style="text-align: center;">Admin Panel</h2>
    <a href="admin_dashboard.php">Dashboard</a>
    <a href="charity_list.php">Charity</a>
    <a href="donor_list.php" class="active">Donors</a>
    <a href="admin_list.php">Admins</a>
    <a href="admin_reset_request.php">Reset Requests</a>
    <a href="logout.php">Logout</a>
</div>
<!-- Main Content -->
<div class="main-content">
    <h2>Charities</h2>
    <a href="admin_dashboard.php" class="btn">⬅ Back to Dashboard</a>

    <table>
        <tr>
            <th>Name</th>
            <th>Registration No.</th>
            <th>Charity Photo</th>
            <th>Website</th>
            <th>Description</th>
            <th>Address</th>
            <th>Email</th>
            <th>Verification Photo</th>
            <th>Status</th> 
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
        <tr><td><a href="charity_profile.php?charity_id=<?= $row['charity_id'] ?>"><?= htmlspecialchars($row['charity_name']) ?></a></td>
        
        <td><?= htmlspecialchars($row['charity_reg_no']) ?></td>
            <td>
            <?php if (!empty($row['charity_photo'])) {
                    echo "<div>
                            <img src='data:image/jpeg;base64," . base64_encode($row['charity_photo']) . "' alt='Charity Image' width='100' height='100' />
                        </div>";
                } else {
                    echo "<p>No image available.</p>";
                }
                ?>
            </td>
            <td><?= htmlspecialchars($row['website']) ?></td>
            <td><?= htmlspecialchars($row['charity_description']) ?></td>
            <td><?= htmlspecialchars($row['street_address'] . ', ' . $row['barangay'] . ', ' . $row['municipality'] . ', ' . $row['province']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td>
                <?php if (!empty($row['verification_photo'])) {
                    echo "<div>
                            <img src='data:image/jpeg;base64," . base64_encode($row['verification_photo']) . "' alt='Registration Certificate' width='100' height='100' />
                        </div>";
                } else {
                    echo "<p>No image available.</p>";
                }
                ?>
            </td>
            <td><?= htmlspecialchars($row['status']) ?></td> <!-- Show Status -->
            <td>
                <a href="edit_charity.php?id=<?= $row['charity_id'] ?>" class="btn">Edit</a>
                <a href="charity_list.php?delete=<?= $row['charity_id'] ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                <a href="charity_list.php?approve=<?= $row['charity_id'] ?>" class="btn btn-success" onclick="return confirm('Approve this charity?')">Approve</a>
                <a href="charity_list.php?decline=<?= $row['charity_id'] ?>" class="btn btn-warning" onclick="return confirm('Decline this charity?')">Decline</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <h2>Add Charity</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <input type="text" id="charityname" name="charityname" value="<?php echo htmlspecialchars(stripslashes($charityname)); ?>"  required autocomplete="off" maxlength="" placeholder="Charity Name">
        </div>
        <div class="form-group">
            <input type="text" id="charitynumber" name="charitynumber" value="<?php echo htmlspecialchars($charitynumber); ?>"  required autocomplete="off" maxlength="" placeholder="Registration Number">
        </div>
        <div class="form-group">
            <label>Charity Photo (Required)</label>
            <input type="file" id="charity_image" name="charity_image" accept="image/png, image/jpeg, image/jpg" value="<?php echo htmlspecialchars($image); ?>" required>
        </div>
        <div class="form-group">
            <input type="date" id="establishmentdate" name="establishmentdate" value="<?php echo htmlspecialchars($establishmentdate); ?>"  required autocomplete="off" maxlength=""  placeholder="Establishment Date">
        </div>
        <div class="form-group">
            <input type="text" id="charitydesc" name="charitydesc" value="<?php echo htmlspecialchars($charitydesc); ?>"  required autocomplete="off" maxlength="" placeholder="Description">
        </div>
        <div class="form-group">
            <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($website); ?>" autocomplete="off" maxlength="" placeholder="Website (optional)">
        </div>
        <div class="form-group">
        <input type="text" id="streetaddress" name="streetaddress" value="<?php echo htmlspecialchars($streetaddress); ?>"  required autocomplete="off" maxlength="" placeholder="Street Address">
        </div>
        <div class="form-group">
        <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>" required autocomplete="off" maxlength="" placeholder="Barangay">
        </div>
        <div class="form-group">
        <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($municipality); ?>" required autocomplete="off" maxlength="" placeholder="Municipality">
        </div>
        <div class="form-group">
        <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($province); ?>"  required autocomplete="off" maxlength="" placeholder="Province">
        </div>
        <div class="form-group">
        <label>Verification Photo (Required)</label>
        <input type="file" id="reg_image" name="reg_image" accept="image/png, image/jpeg, image/jpg" value="<?php echo htmlspecialchars($image); ?>" required>
        </div>

        <h3>Charity's Contact Person</h3>
        <div class="form-group">
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>"  required autocomplete="off" maxlength="" placeholder="Contact Person: First Name">
        </div>
        <div class="form-group">
        <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($middlename); ?>" autocomplete="off" maxlength="" placeholder="Contact Person: Middle Name (optional)">
        </div>
        <div class="form-group">
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required autocomplete="off" maxlength="" placeholder="Contact Person: Last Name">
        </div>
        <div class="form-group">
        <input type="email" id="cp_email" name="cp_email" value="<?php echo htmlspecialchars($cp_email); ?>" required autocomplete="off" maxlength="" placeholder="Contact Person: Email">
        </div>
        <div class="form-group">
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required autocomplete="off" maxlength="" placeholder="Contact Person: Phone Number">
        </div>

        <h3>Setting Up Charity's Account</h3>
        <div class="form-group">
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="off"maxlength=""  placeholder="Charity Email">
        </div>
        <small>Default password: <strong>charity123</strong></small>
        <button type="submit" name="submit">Add Charity</button>
    </form>

</div>
</body>
</html>