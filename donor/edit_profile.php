<?php
session_start();
require('../configuration/db_connect.php');

// Check if the user is logged in
if (!isset($_SESSION['donator_id'])) {
    header("Location: login.php");
    exit();
}

$donator_id = $_SESSION['donator_id'];

// Fetch existing donor details
$stmt = $conn->prepare("SELECT * FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param('i', $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donator = $result->fetch_assoc();

// Handle profile update submission
if (isset($_POST['update'])) {
    $errors = []; // Initialize errors array

    // Sanitize inputs
    $firstname = $_POST['firstname'];
    $middlename = $_POST['middlename'];
    $lastname = $_POST['lastname'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];

    $old_password = $_POST["old_password"] ?? '';
    $new_password = $_POST["new_password"] ?? '';
    $cpassword = $_POST['confirm_new_password'] ?? '';
    $current_password = $donator['password'] ?? null;

    // Validate required fields
    if (empty($firstname) || empty($lastname) || empty($phone) || empty($email)) {
        $errors[] = "Please fill out all required fields.";
    }

    // Update donor details
    if (empty($errors)) {
        // Update donor details query
        $stmt = $conn->prepare("UPDATE tbl_donor SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_no = ? WHERE donator_id = ?");
        $stmt->bind_param('sssssi', $firstname, $middlename, $lastname, $email, $phone, $donator_id);
        $stmt->execute();
    }

    // Handle password change logic if password fields are set
    if (!empty($old_password) && !empty($new_password) && !empty($cpassword)) {
        if (password_verify($old_password, $current_password)) {
            if ($new_password === $cpassword) {
                // Validate new password (you can modify the regex if needed)
                if (preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $new_password)) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                    // Update password if validated
                    $updatePasswordStmt = $conn->prepare("UPDATE tbl_donor SET password = ? WHERE donator_id = ?");
                    $updatePasswordStmt->bind_param('si', $hashed_password, $donator_id);
                    $updatePasswordStmt->execute();
                    $success_message = "Password updated successfully!";
                } else {
                    $errors[] = "Password must be at least 8 characters long and include an uppercase letter, a lowercase letter, a number, and a special character.";
                }
            } else {
                $errors[] = "New password and confirmation do not match.";
            }
        } else {
            $errors[] = "Old password is incorrect.";
        }
    }

    // Redirect to profile page if no errors
    if (empty($errors)) {
        header("Location: d-profile.php?success=1");
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

    <h2>Edit Profile</h2>

    <?php
    // Display errors if any
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color: red;'>$error</p>";
        }
    }
    ?>

    <form action="" method="POST">
        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($donator['first_name']); ?>"><br>

        <label for="middlename">Middle Name:</label>
        <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($donator['middle_name']); ?>"><br>

        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($donator['last_name']); ?>"><br>

        <label for="phone">Phone:</label>
        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($donator['contact_no']); ?>"><br>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($donator['email']); ?>"><br>

        <label for="old_password">Old Password:</label>
        <input type="password" id="old_password" name="old_password"><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password"><br>

        <label for="confirm_new_password">Confirm New Password:</label>
        <input type="password" id="confirm_new_password" name="confirm_new_password"><br>

        <button type="submit" name="update">Update Profile</button>
    </form>
    
    <?php if (isset($success_message)) { echo "<p style='color: green;'>$success_message</p>"; } ?>

</body>
</html>
