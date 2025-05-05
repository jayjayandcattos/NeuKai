<?php
session_start();
include '../configuration/db_connect.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if donor ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid donor ID.");
}

$donator_id = intval($_GET['id']);

// Fetch donor details
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, email, contact_no, status FROM tbl_donor WHERE donator_id = ?");
$stmt->bind_param("i", $donator_id);
$stmt->execute();
$result = $stmt->get_result();
$donor = $result->fetch_assoc();
$stmt->close();

if (!$donor) {
    die("Donor not found.");
}

// Handle form submission for updating donor details
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $contact_no = $_POST['contact_no'];
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE tbl_donor SET first_name = ?, middle_name = ?, last_name = ?, email = ?, contact_no = ?, status = ? WHERE donator_id = ?");
    $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $email, $contact_no, $status, $donator_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_page.php#donor_list");
    exit();
}
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
            overflow: scroll;
            height: 700px;
        }

        .form-title {
            color: #007bff;
            font-size: 24px;
            border-bottom: 3px solid #007bff;
            padding-bottom: 10px;
            margin-bottom: 25px;
            text-align: left;
        }

        /* Form Styling */
        form {
            margin-top: 20px;
        }

        input[type="text"],
        input[type="email"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        select:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        input[type="submit"],
        .back-button {
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s;
            text-decoration: none;
            display: inline-block;
        }

        input[type="submit"] {
            background-color: #007bff;
        }

        input[type="submit"]:hover {
            background-color: #0069d9;
        }

        .back-button {
            background-color: #6c757d;
            margin-right: 10px;
        }

        .back-button:hover {
            background-color: #5a6268;
        }

        .button-container {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
            gap: 10px;
        }

        /* Status Select Options */
        select {
            background-color: #ffffff;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            padding: 10px;
            width: 100%;
            margin-bottom: 20px;
        }

        option[value="Pending"] {
            color: orange;
        }

        option[value="Approved"] {
            color: green;
        }

        option[value="Declined"] {
            color: red;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .charity-container {
                padding: 20px;
                margin: 20px 10px;
            }
            
            .button-container {
                flex-direction: column;
            }
            
            .back-button {
                margin-right: 0;
                margin-bottom: 10px;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="charity-container">
        <h2 class="form-title">Edit Donor Information</h2>
        <form action="" method="POST" id="updateForm">
            <label>First Name:</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($donor['first_name']) ?>" required>

            <label>Middle Name:</label>
            <input type="text" name="middle_name" value="<?= htmlspecialchars($donor['middle_name']) ?>">

            <label>Last Name:</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($donor['last_name']) ?>" required>

            <label>Email:</label>
            <input type="email" name="email" value="<?= htmlspecialchars($donor['email']) ?>" required>

            <label>Contact No:</label>
            <input type="text" name="contact_no" value="<?= htmlspecialchars($donor['contact_no']) ?>" required>

            <label>Status:</label>
            <select name="status" required>
                <option value="Pending" <?= $donor['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Approved" <?= $donor['status'] === 'Approved' ? 'selected' : '' ?>>Approved</option>
                <option value="Declined" <?= $donor['status'] === 'Declined' ? 'selected' : '' ?>>Declined</option>
            </select>

            <div class="button-container">
                <a href="admin_page.php#donor_list" class="back-button">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <input type="submit" value="Update Donor">
            </div>
        </form>
    </div>
</body>

</html>