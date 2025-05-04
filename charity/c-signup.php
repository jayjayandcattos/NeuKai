<?php
require('../configuration/db_connect.php');

//step 1 form
$charityname = '';
$charitynumber = '';
$establishmentdate = '';
$charitydesc = '';
$website = '';
$charity_image = '';

//step 2 form
$streetaddress = '';
$barangay = '';
$municipality = '';
$province = '';
$reg_image = '';

//step 3 form
$firstname = '';
$middlename = '';
$lastname = '';
$cp_email = '';
$phone = '';

//step 4 form
$email = '';
$password = '';
$password_confirmation = '';

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
    $password = $_POST['password'];
    $password_confirmation = $_POST['password_confirmation'];

    if (!preg_match("/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/", $password)) {
        $error_message = "Password must be at least 8 characters long, include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } elseif ($password !== $password_confirmation) {
        $error_message = "Passwords do not match. Please try again.";
    } else {
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
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
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
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
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
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                $stmt = $conn->prepare("INSERT INTO tbl_charity_login (charity_id, email, password) VALUES (?, ?, ?)");
                $stmt->bind_param('iss', $charity_id, $email, $hashed_password);
                $stmt->execute();

                $stmt = $conn->prepare("INSERT INTO tbl_charity_contact_person (charity_id, first_name, middle_name, last_name, email, contact_no) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('isssss', $charity_id, $firstname, $middlename, $lastname, $cp_email, $phone);
                $stmt->execute();

                $_POST = array();
                header("Location: ../login.php");
            } else {
                $error_message = "Error inserting customer details: " . $stmt->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../images/TempIco.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Rubik+Mono+One&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <script src="../js/loading.js" defer></script>
    <script src="../js/mobilenav.js" defer></script>
    <script src="../js/charitysignup.js" defer></script>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/formStyles.css">
    <title>Charity Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <!-- Navbar -->
    <?php include '../section/desktopNavbar1.php'; ?>

    <!-- Mobile Menu -->
    <?php include '../section/mobilenavbar1.php'; ?>

    <div id="loading-overlay"
        class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm flex items-center justify-center z-50 opacity-0 pointer-events-none transition-opacity duration-300">
        <img src="../images/Neukai Logo.svg" alt="Loading" class="w-32 h-32 animate-pulse" />
    </div>

    <div class="charity-form-container mt-[4.2rem] w-full max-w-[700px] min-w-[100px] mx-auto">
        <div class="progress-container">
            <div class="progress-step active" id="progress-step1">
                <div class="step-circle">1</div>
                <div class="step-label">Charity Details</div>
            </div>
            <div class="progress-step" id="progress-step2">
                <div class="step-circle">2</div>
                <div class="step-label">Address</div>
            </div>
            <div class="progress-step" id="progress-step3">
                <div class="step-circle">3</div>
                <div class="step-label">Contact</div>
            </div>
            <div class="progress-step" id="progress-step4">
                <div class="step-circle">4</div>
                <div class="step-label">Account</div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="form-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form id="Registration_Steps" action="" method="POST" enctype="multipart/form-data">
            <div class="step active" id="step1">
                <h2>STEP 1: CHARITY DETAILS</h2>
                <label for="charityname">Charity Name:</label>
                <input type="text" id="charityname" name="charityname" value="<?php echo htmlspecialchars(stripslashes($charityname)); ?>" required autocomplete="off">

                <label for="charitynumber">Registered Charity Number:</label>
                <input type="text" id="charitynumber" name="charitynumber" value="<?php echo htmlspecialchars($charitynumber); ?>" required autocomplete="off">

                <label for="establishmentdate">Date of Establishment:</label>
                <input type="date" id="establishmentdate" name="establishmentdate" value="<?php echo htmlspecialchars($establishmentdate); ?>" required autocomplete="off">

                <label for="charitydesc">Charity Description:</label>
                <input type="text" id="charitydesc" name="charitydesc" value="<?php echo htmlspecialchars($charitydesc); ?>" required autocomplete="off">

                <label for="website">Charity's Official Website (if available):</label>
                <input type="url" id="website" name="website" value="<?php echo htmlspecialchars($website); ?>" placeholder="(optional)" autocomplete="off">

                <label for="charity_image">Charity Picture:</label>
                <input type="file" id="charity_image" name="charity_image" accept="image/png, image/jpeg, image/jpg" required>

                <div class="btn-container">
                    <div></div>
                    <button type="button" onclick="nextStep(1)">Next</button>
                </div>
            </div>

            <div class="step" id="step2">
                <h2>STEP 2: ADDRESS AND DOCUMENT</h2>
                <label for="streetaddress">Street Address:</label>
                <input type="text" id="streetaddress" name="streetaddress" value="<?php echo htmlspecialchars($streetaddress); ?>" required autocomplete="off">

                <label for="barangay">Barangay:</label>
                <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>" required autocomplete="off">

                <label for="municipality">Municipality:</label>
                <input type="text" id="municipality" name="municipality" value="<?php echo htmlspecialchars($municipality); ?>" required autocomplete="off">

                <label for="province">Province:</label>
                <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($province); ?>" required autocomplete="off">

                <label for="reg_image">Charity Registration Certificate:</label>
                <input type="file" id="reg_image" name="reg_image" accept="image/png, image/jpeg, image/jpg" required>

                <div class="btn-container">
                    <button type="button" onclick="prevStep(2)">Previous</button>
                    <button type="button" onclick="nextStep(2)">Next</button>
                </div>
            </div>

            <div class="step" id="step3">
                <h2>STEP 3: CONTACT PERSON</h2>
                <label for="firstname">First Name:</label>
                <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($firstname); ?>" required autocomplete="off">

                <label for="middlename">Middle Name:</label>
                <input type="text" id="middlename" name="middlename" value="<?php echo htmlspecialchars($middlename); ?>" placeholder="(optional)" autocomplete="off">

                <label for="lastname">Last Name:</label>
                <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($lastname); ?>" required autocomplete="off">

                <label for="cp_email">Email:</label>
                <input type="email" id="cp_email" name="cp_email" value="<?php echo htmlspecialchars($cp_email); ?>" required autocomplete="off">

                <label for="phone">Phone Number:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" required autocomplete="off">

                <div class="btn-container">
                    <button type="button" onclick="prevStep(3)">Previous</button>
                    <button type="button" onclick="nextStep(3)">Next</button>
                </div>
            </div>

            <div class="step" id="step4">
                <h2>STEP 4: Set Up Your Account</h2>
                <label for="email">Charity's Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required autocomplete="off">

                <label for="password">Password:</label>
                <input type="password" id="password" name="password" value="<?php echo htmlspecialchars($password); ?>" onpaste="return false;">
                <div class="text-xs text-gray-500 mt-[-0.5rem] mb-2">Password must be at least 8 characters with uppercase, lowercase, number, and special character</div>

                <label for="password_confirmation">Confirm Password:</label>
                <input type="password" id="password_confirmation" name="password_confirmation" value="<?php echo htmlspecialchars($password_confirmation); ?>" onpaste="return false;">

                <div class="btn-container">
                    <button type="button" onclick="prevStep(4)">Previous</button>
                    <button type="submit" name="submit">Submit</button>
                </div>
            </div>
        </form>
    </div>

       <?php include '../section/donorparallax.php'; ?>
</body>

</html>