<?php
session_start();
require('../configuration/db_connect.php');


if (!isset($_SESSION['charity_id'])) {
    header("Location: ../login.php");
    exit(); 
}

$charity_id = $_SESSION['charity_id'];

// Fetch existing charity details
$stmt = $conn->prepare("SELECT * FROM tbl_charity WHERE charity_id = ?");
$stmt->bind_param('i', $charity_id);
$stmt->execute();
$result = $stmt->get_result();
$charity = $result->fetch_assoc();
$charity_image = $charity['charity_photo'];
$reg_image = $charity['verification_photo'];

// Fetch existing contact person details
$stmt = $conn->prepare("SELECT * FROM tbl_charity_contact_person WHERE charity_id = ?");
$stmt->bind_param('i', $charity_id);
$stmt->execute();
$result = $stmt->get_result();
$contact_person = $result->fetch_assoc();

// Fetch existing login details (email)
$stmt = $conn->prepare("SELECT email FROM tbl_charity_login WHERE charity_id = ?");
$stmt->bind_param('i', $charity_id);
$stmt->execute();
$result = $stmt->get_result();
$login_details = $result->fetch_assoc();

$email = $login_details['email']; // Set email to be used in the image fetching query

// Handle profile update submission
if (isset($_POST['update'])) {
    // Sanitize and validate inputs
    $charityname = $_POST['charityname'];
    $charitynumber = mysqli_real_escape_string($conn, $_POST['charitynumber']);
    $establishmentdate = mysqli_real_escape_string($conn, $_POST['establishmentdate']);
    $charitydesc = mysqli_real_escape_string($conn, $_POST['charitydesc']);
    $website = mysqli_real_escape_string($conn, $_POST['website']);
    $streetaddress = mysqli_real_escape_string($conn, $_POST['streetaddress']);
    $barangay = mysqli_real_escape_string($conn, $_POST['barangay']);
    $municipality = mysqli_real_escape_string($conn, $_POST['municipality']);
    $province = mysqli_real_escape_string($conn, $_POST['province']);
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $middlename = mysqli_real_escape_string($conn, $_POST['middlename']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $cp_email = mysqli_real_escape_string($conn, $_POST['cp_email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);

    $old_password = $_POST["old_password"] ?? '';
    $new_password = $_POST["new_password"] ?? '';
    $cpassword = $_POST['confirm_new_password'] ?? '';
    $current_password = $customer['password'] ?? null;

    // Update charity details
    $stmt = $conn->prepare("UPDATE tbl_charity SET charity_name = ?, charity_reg_no = ?, establishment_date = ?, charity_description = ?, website = ?, street_address = ?, barangay = ?, municipality = ?, province = ? WHERE charity_id = ?");
    $stmt->bind_param('sssssssssi', $charityname, $charitynumber, $establishmentdate, $charitydesc, $website, $streetaddress, $barangay, $municipality, $province, $charity_id);
    $stmt->execute();

    // Update contact person details
    $stmt = $conn->prepare("UPDATE tbl_charity_contact_person SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_no = ? WHERE charity_id = ?");
    $stmt->bind_param('sssssi', $firstname, $middlename, $lastname, $cp_email, $phone, $charity_id);
    $stmt->execute();

    // Update email
    $stmt = $conn->prepare("UPDATE tbl_charity_login SET email = ? WHERE charity_id = ?");
    $stmt->bind_param('si', $email, $charity_id);
    $stmt->execute();

    // Update password
    if (!empty($old_password) && !empty($new_password) && !empty($cpassword)) {
        if ($current_password && password_verify($old_password, $current_password)) {
            if ($new_password === $cpassword) {
                if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                } else {
                    $errors[] = "Password must be at least 8 characters long, include an uppercase, lowercase letter, number, and special character.";
                }
            } else {
                $errors[] = "New password and confirmation do not match.";
            }
        } else {
            $errors[] = "Old password is incorrect.";
        }
    } else {
        $hashed_password = $current_password; // Keep old password if not updated
    }

    // update the login credentials table only if a new hashed password is set and no errors exist
    if (empty($errors) && isset($hashed_password)) {
        $updateLoginQuery = "UPDATE tbl_charity_login 
                             SET email = ?, password = ? 
                             WHERE charity_id = ?";
        $stmt = $conn->prepare($updateLoginQuery);
        if ($stmt) {
            $stmt->bind_param('ssi', $email, $hashed_password, $charity_id);
            if ($stmt->execute()) {
                $success_message = "Password updated successfully!";
            } else {
                $errors[] = "Error updating password: " . $stmt->error;
            }    
            $stmt->close();
        } else {
            $errors[] = "Error preparing password update statement: " . $conn->error;
        }
    }

    // Handle charity image update
    if (!empty($_FILES['charity_image']['tmp_name'])) {
        $image_tmp = $_FILES['charity_image']['tmp_name'];
        $image_data = file_get_contents($image_tmp); // Convert image to binary

        // Update the database with the new image
        $stmt = $conn->prepare("UPDATE tbl_charity SET charity_photo = ? WHERE charity_id = ?");
        $stmt->bind_param('bi', $image_data, $charity_id);
        $stmt->send_long_data(0, $image_data);
        $stmt->execute();
    }

    if (empty($errors)) {
      
        header("Location: charity_dashboard.php");
        exit(); 
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
</head>
<body>
<a href='charity_dashboard.php'>Profile</a>
<a href='c-received.php'>Received</a>
<a href='c-request.php'>Request</a>

    <h2>Edit Charity Profile</h2>
    <form action="" method="POST" enctype="multipart/form-data">
        <h3>Charity Details</h3>
        <label for="charityname">Charity Name:</label>
        <input type="text" id="charityname" name="charityname" value="<?php echo htmlspecialchars($charity['charity_name']); ?>"><br>

        <label for="charitynumber">Charity Number:</label>
        <input type="text" id="charitynumber" name="charitynumber" value="<?php echo htmlspecialchars($charity['charity_reg_no']); ?>"><br>

        <label for="establishmentdate">Establishment Date:</label>
        <input type="date" id="establishmentdate" name="establishmentdate" value="<?php echo htmlspecialchars($charity['establishment_date']); ?>"><br>

        <label for="charitydesc">Charity Description:</label>
        <input type="text" id="charitydesc" name="charitydesc" value="<?php echo htmlspecialchars($charity['charity_description']); ?>"><br>

        <label for="website">Website:</label>
        <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($charity['website']); ?>"><br>

        <h3>Address Details</h3>
        <label for="streetaddress">Street Address:</label>
        <input type="text" id="streetaddress" name="streetaddress" value="<?php echo htmlspecialchars($charity['street_address']); ?>"><br>

        <label for="barangay">Barangay:</label>
        <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($charity['barangay']); ?>"><br>

        <label for="municipality">Municipality:</label>
        <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($charity['municipality']); ?>"><br>

        <label for="province">Province:</label>
        <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($charity['province']); ?>"><br>

        <h3>Contact Person</h3>
        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($contact_person['first_name']); ?>"><br>

        <label for="middlename">Middle Name:</label>
        <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($contact_person['middle_name']); ?>"><br>

        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($contact_person['last_name']); ?>"><br>

        <label for="cp_email">Email:</label>
        <input type="email" id="cp_email" name="cp_email" value="<?php echo htmlspecialchars($contact_person['email']); ?>"><br>

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($contact_person['contact_no']); ?>"><br>

        <h3>Login Details</h3>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($login_details['email']); ?>"><br>

        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password"><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br>

        <label for="confirm_new_password">Confirm New Password:</label>
        <input type="password" id="confirm_new_password" name="confirm_new_password"><br>

        <h3>Profile Picture</h3>
        <?php if (!empty($charity['charity_photo'])) {
                    echo "<div>
                            <img src='data:image/jpeg;base64," . base64_encode($charity['charity_photo']) . "' alt='Charity Image' width='100' height='100' />
                        </div>";
                } else {
                    echo "<p>No image available.</p>";
                }
                ?>
        <label for="charity_image">Upload New Charity Image:</label>
        <input type="file" id="charity_image" name="charity_image"><br>

        <button type="submit" name="update">Update Profile</button>
    </form>
</body>
</html>
