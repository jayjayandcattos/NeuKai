<?php require_once __DIR__ . '/../../app/bootstrap.php'; ?><?php require_admin(); ?><?php require_once __DIR__ . '/../../app/services/mail/admin-mail.php'; ?><?php

// Declare error messages
$errors = [];

$charityname = '';
$charitynumber = '';
$establishmentdate = '';
$charitydesc = '';
$website = '';
$charity_image = '';
$streetaddress = '';
$barangay = '';
$municipality = '';
$province = '';
$reg_image = '';
$firstname = '';
$middlename = '';
$lastname = '';
$cp_email = '';
$phone = '';
$email = '';
$password = '';

if (isset($_POST['submit'])) {
    csrf_verify();
    // user inputs
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
    $temporaryPassword = bin2hex(random_bytes(6));
    $password = password_hash($temporaryPassword, PASSWORD_DEFAULT);

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

                $fileType = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['charity_image']['tmp_name']) ?: '';
                if (!isset(allowed_image_types()[$fileType])) {
                    die('Error: Invalid file type. Only JPG, PNG, GIF and WEBP images are allowed.');
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

                $fileType = (new finfo(FILEINFO_MIME_TYPE))->file($_FILES['reg_image']['tmp_name']) ?: '';
                if (!isset(allowed_image_types()[$fileType])) {
                    die('Error: Invalid file type. Only JPG, PNG, GIF and WEBP images are allowed.');
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

        $stmt = $conn->prepare("INSERT INTO tbl_charity (charity_name, charity_reg_no, establishment_date, charity_description, website, charity_photo, street_address, barangay, municipality, province, verification_photo, email, status) 
                                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Approved')");
        $stmt->bind_param('ssssssssssss', $charityname, $charitynumber, $establishmentdate, $charitydesc, $website, $charity_image, $streetaddress, $barangay, $municipality, $province, $reg_image, $email);

        if ($stmt->execute()) {
            $charity_id = $stmt->insert_id;

            $stmt = $conn->prepare("INSERT INTO tbl_charity_login (charity_id, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('iss', $charity_id, $email, $password);
            $stmt->execute();

            $stmt = $conn->prepare("INSERT INTO tbl_charity_contact_person (charity_id, first_name, middle_name, last_name, email, contact_no) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('isssss', $charity_id, $firstname, $middlename, $lastname, $cp_email, $phone);
            $stmt->execute();

            addCharityMailer($email, $charityname, $temporaryPassword);

            $_POST = array();
            header("Location: admin_page.php#charity_list");

        } else {
            error_log('NeuKai charity insert failed: ' . $stmt->error);
            $error_message = 'The charity could not be registered. Please try again.';
        }

    }
}

// Handle status change (approve, decline, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['charity_action'], $_POST['charity_id'])) {
    csrf_verify();

    $charity_id = (int) $_POST['charity_id'];
    $action = (string) $_POST['charity_action'];

    if ($action === 'delete') {
        $stmt = $conn->prepare('DELETE FROM tbl_charity WHERE charity_id = ?');
        $stmt->bind_param('i', $charity_id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'approve' || $action === 'decline') {
        $status = $action === 'approve' ? 'approved' : 'declined';

        $stmt = $conn->prepare('UPDATE tbl_charity SET status = ? WHERE charity_id = ?');
        $stmt->bind_param('si', $status, $charity_id);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare('SELECT charity_name, email FROM tbl_charity WHERE charity_id = ?');
        $stmt->bind_param('i', $charity_id);
        $stmt->execute();
        $charity = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($charity) {
            if ($action === 'approve') {
                sendApprovalEmail($charity['email'], $charity['charity_name'], $charity_id);
            } else {
                sendDeclineEmail($charity['email'], $charity['charity_name'], $charity_id);
            }
        }
    }

    redirect('admin_page.php#charity_list');
}

// Fetch Charities
$query = "SELECT * FROM tbl_charity";
$result = $conn->query($query);

// Check for query error
if (!$result) {
    error_log('NeuKai query failure: ' . $conn->error); http_response_code(500); exit('Something went wrong. Please try again.');
}
?>

<?php
$result = $conn->query("SELECT * FROM tbl_charity");

// Total number of admins
$total_charities = $result->num_rows;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Charity List</title>
    <link rel="stylesheet" href="a_styles.css">
    <style>
        /* Add these styles to your existing CSS */
        .charities-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            table-layout: fixed;
        }

        .charities-table th {
            background-color: #f2f2f2;
            padding: 12px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #ddd;
        }

        .charities-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .charities-table tr:hover {
            background-color: #f5f5f5;
        }

        .charities-table .image-cell {
            width: 120px;
            text-align: center;
        }

        .charities-table .status-cell {
            width: 100px;
        }

        .charities-table .actions-cell {
            width: 300px;
        }

        .charities-table img {
            max-width: 100px;
            max-height: 100px;
            display: block;
            margin: 0 auto;
        }

        .charity-link {
            text-decoration: none;
            color: #007bff;
        }

        .charity-link:hover {
            text-decoration: underline;
        }

        .btn {
            display: inline-block;
            padding: 6px 12px;
            margin: 2px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            font-size: 14px;
            text-align: center;
        }

        .btn-success {
            background-color: #00813a;
        }

        .btn-edit {
            background-color: #0e2a86;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-warning {
            background-color: #808080;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-badge.approved {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.declined {
            background-color: #f8d7da;
            color: #721c24;
        }

        .status-badge.pending {
            background-color: #fff3cd;
            color: #856404;
        }
    </style>
    <script>
        function deleteCharity(charityId) {
            return confirm("Delete this charity?");
        }
    </script>
</head>

<body>

    <!-- Main Content -->
    <div class="main-content">
        <div class="title">
            <h2>MANAGE CHARITIES</h2>
            <h2 style="color: #aaa; position: relative; left: 427px;">Existing Charities : <?= $total_charities ?></h2>
        </div>
<br>
        <div class="collapsible-wrapper">
            <div class="collapsible-header">
                <h3> + Add Charities</h3>
                <span class="arrow-icon rotate">&#9654;</span>
            </div>

            <div class="collapsible-body">
                <form id="add-charity-form" action="charity_list.php" method="POST" enctype="multipart/form-data"
                    class="admin-form-table"><?= csrf_field() ?>

                    <div
                        style="max-height: 400px; overflow-y: auto; border: 1px solid white; border-radius: 4px; padding: 10px; scrollbar-width: none; -ms-overflow-style: none;">
                        <table class="admin-form-table" style="width: 100%;">

                            <tr>
                                <td colspan="2">
                                    <h3 style="margin-bottom: 20px;">Charity's Information</h3>
                                </td>
                            </tr>
                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-user"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="charityname" name="charityname"
                                                value="<?php echo htmlspecialchars(stripslashes($charityname)); ?>"
                                                required autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Charity Name</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-id-card"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="charitynumber" name="charitynumber"
                                                value="<?php echo htmlspecialchars($charitynumber); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Registration Number</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-image"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <label>Charity Photo (Required)</label>
                                        <div>
                                            <input type="file" id="charity_image" name="charity_image"
                                                accept="image/png, image/jpeg, image/jpg"
                                                value="<?php echo htmlspecialchars($image); ?>" required
                                                placeholder=" ">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-calendar-alt"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <label>Establishment Date</label>
                                        <div>
                                            <input type="date" id="establishmentdate" name="establishmentdate"
                                                value="<?php echo htmlspecialchars($establishmentdate); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">

                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-info-circle"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="charitydesc" name="charitydesc"
                                                value="<?php echo htmlspecialchars($charitydesc); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Description</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-globe"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="url" id="website" name="website"
                                                value="<?php echo htmlspecialchars($website); ?>" autocomplete="off"
                                                maxlength="" placeholder=" ">
                                            <label>Website (optional)</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-map-marker-alt"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="streetaddress" name="streetaddress"
                                                value="<?php echo htmlspecialchars($streetaddress); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Street Address</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-map-marker-alt"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="barangay" name="barangay"
                                                value="<?php echo htmlspecialchars($barangay); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Barangay</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-map-marker-alt"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="municipality" name="municipality"
                                                value="<?php echo htmlspecialchars($municipality); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Municipality</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-map-marker-alt"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="province" name="province"
                                                value="<?php echo htmlspecialchars($province); ?>" required
                                                autocomplete="off" maxlength="" required placeholder=" ">
                                            <label>Province</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-image"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div>
                                            <label>Verification Photo (Required)</label>
                                            <input type="file" id="reg_image" name="reg_image"
                                                accept="image/png, image/jpeg, image/jpg"
                                                value="<?php echo htmlspecialchars($image); ?>" required
                                                placeholder=" ">
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td colspan="2">
                                    <h3 style="margin-bottom: 20px; margin-top: 20px;">Charity's Contact Person</h3>
                                </td>
                            </tr>
                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-user"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="firstname" name="firstname"
                                                value="<?php echo htmlspecialchars($firstname); ?>" required
                                                autocomplete="off" maxlength="" placeholder=" ">
                                            <label>Contact Person: First Name</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-user"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="middlename" name="middlename"
                                                value="<?php echo htmlspecialchars($middlename); ?>" autocomplete="off"
                                                maxlength="" placeholder=" ">
                                            <label>Contact Person: Middle Name (optional)</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-user"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="text" id="lastname" name="lastname"
                                                value="<?php echo htmlspecialchars($lastname); ?>" required
                                                autocomplete="off" maxlength="" placeholder=" ">
                                            <label>Contact Person: Last Name</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-envelope"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="email" id="cp_email" name="cp_email"
                                                value="<?php echo htmlspecialchars($cp_email); ?>" required
                                                autocomplete="off" maxlength="" placeholder=" ">
                                            <label>Contact Person: Email</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-phone"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="tel" id="phone" name="phone"
                                                value="<?php echo htmlspecialchars($phone); ?>" required
                                                autocomplete="off" maxlength="" placeholder=" ">
                                            <label>Contact Person: Phone Number</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Charity Account Section Header -->
                            <tr>
                                <td colspan="2">
                                    <h3 style="margin-bottom: 20px; margin-top: 20px;">Setting Up Charity's Account</h3>
                                </td>
                            </tr>
                            <tr>
                                <td class="icon" rowspan="1"><i class="fas fa-envelope"></i></td>
                                <td>
                                    <div class="form-group input-row">
                                        <div class="input-group">
                                            <input type="email" id="email" name="email"
                                                value="<?php echo htmlspecialchars($email); ?>" required
                                                autocomplete="off" maxlength="" placeholder=" ">
                                            <label>Charity Email</label>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td><small>A random temporary password is generated and emailed to the charity.</small></td>
                            </tr>
                        </table>

                        <button type="submit" name="submit" class="btn" style="background-color: #00ac5f;  color: white;
                        font-weight: bold;
                        cursor: pointer;
                        border: none;
                        padding: 10px;
                        border-radius: 50px;
                        width: 200px;
                        margin-left: 680px;
                        ">Add Charity</button>
                </form>
            </div>
        </div>
    </div>
    <br>
    <?php if ($result->num_rows > 0): ?>
        <table class="charities-table">
            <thead>
                <tr>
                    <th style="width: 10%;">Name</th>
                    <th style="width: 10%;">Registration No.</th>
                    <th style="width: 10%;" class="image-cell">Photo</th>
                    <th style="width: 15%;">Address</th>
                    <th style="width: 10%;">Email</th>
                    <th style="width: 10%;" class="image-cell">Verification</th>
                    <th style="width: 10%;" class="status-cell">Status</th>
                    <th style="width: 20%;" class="actions-cell">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><a class="charity-link"
                                href="charity_profile.php?charity_id=<?= e($row['charity_id']) ?>"><?= htmlspecialchars($row['charity_name']) ?></a>
                        </td>
                        <td><?= htmlspecialchars($row['charity_reg_no']) ?></td>
                        <td class="image-cell">
                            <?php if (!empty($row['charity_photo'])): ?>
                                <img src='data:image/jpeg;base64,<?= base64_encode($row['charity_photo']) ?>' alt='Charity Image' />
                            <?php else: ?>
                                <p>No image</p>
                            <?php endif; ?>
                        </td>
                        <td><?= htmlspecialchars($row['street_address'] . ', ' . $row['barangay'] . ', ' . $row['municipality'] . ', ' . $row['province']) ?>
                        </td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td class="image-cell">
                            <?php if (!empty($row['verification_photo'])): ?>
                                <img src='data:image/jpeg;base64,<?= base64_encode($row['verification_photo']) ?>'
                                    alt='Registration Certificate' />
                            <?php else: ?>
                                <p>No image</p>
                            <?php endif; ?>
                        </td>
                        <td class="status-cell">
                            <?php 
                            $status = htmlspecialchars($row['status']);
                            $statusClass = strtolower($status);
                            echo "<span class='status-badge $statusClass'>$status</span>";
                            ?>
                        </td>
                        <td>
                            <?php if ($row['status'] != 'Approved' && $row['status'] != 'Declined'): ?>
                                <form method="post" action="charity_list.php" style="display:inline"
                                    onsubmit="return confirm('Approve this charity?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="charity_action" value="approve">
                                    <input type="hidden" name="charity_id" value="<?= (int) $row['charity_id'] ?>">
                                    <button type="submit" class="btn btn-success"
                                        style="margin-left: 45px; background-color: #00813a;">Approve</button>
                                </form>
                                <form method="post" action="charity_list.php" style="display:inline"
                                    onsubmit="return confirm('Decline this charity?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="charity_action" value="decline">
                                    <input type="hidden" name="charity_id" value="<?= (int) $row['charity_id'] ?>">
                                    <button type="submit" class="btn btn-warning"
                                        style="margin-left: 45px; background-color: #808080;">Decline</button>
                                </form>
                            <?php endif; ?>
                            <a style="margin-left: 45px; background-color: #0e2a86;"
                                href="edit_charity.php?id=<?= (int) $row['charity_id'] ?>" class="btn btn-edit">Edit</a>
                            <form method="post" action="charity_list.php" style="display:inline"
                                onsubmit="return confirm('Are you sure you want to delete this charity?');">
                                <?= csrf_field() ?>
                                <input type="hidden" name="charity_action" value="delete">
                                <input type="hidden" name="charity_id" value="<?= (int) $row['charity_id'] ?>">
                                <button type="submit" class="btn btn-danger" style="margin-left: 45px;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>No charities available.</p>
    <?php endif; ?>

    <script src="a_script.js"></script>

</body>

</html>