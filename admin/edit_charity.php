<?php
include '../configuration/db_connect.php';

$charity_id = $_GET['id'];
$result = $conn->query("SELECT * FROM tbl_charity WHERE charity_id = $charity_id");
$row = $result->fetch_assoc();

if (isset($_POST['update_charity'])) {
    $stmt = $conn->prepare("UPDATE tbl_charity SET charity_name=?, charity_reg_no=?, establishment_date=?, charity_description=?, website=?, street_address=?, barangay=?, municipality=?, province=? WHERE charity_id=?");
    $stmt->bind_param("sssssssssi", $_POST['charity_name'], $_POST['charity_reg_no'], $_POST['establishment_date'], $_POST['charity_description'], $_POST['website'], $_POST['street_address'], $_POST['barangay'], $_POST['municipality'], $_POST['province'], $charity_id);

    if ($stmt->execute()) {
        header("Location: admin_page.php#charity_list");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>

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

        form {
            margin-top: 20px;
        }

        form input[type="text"],
        form input[type="date"],
        form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 16px;
            transition: border-color 0.3s;
        }

        form input[type="text"]:focus,
        form input[type="date"]:focus,
        form textarea:focus {
            border-color: #007bff;
            outline: none;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.3);
        }

        form textarea {
            height: 120px;
            resize: vertical;
        }

        form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #444;
        }

        h3 {
            color: #007bff;
            margin: 25px 0 15px 0;
            font-size: 18px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }

        form input[type="submit"],
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

        form input[type="submit"] {
            background-color: #007bff;
        }

        form input[type="submit"]:hover {
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

        .address-fields {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        @media (max-width: 600px) {
            .charity-container {
                padding: 20px;
                margin: 20px 10px;
            }

            .address-fields {
                grid-template-columns: 1fr;
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
    <form action="" method="POST" class="charity-edit-form">
        <h2 class="form-title">Edit Charity Information</h2>

        <label for="charity_name">Charity Name</label>
        <input type="text" name="charity_name" value="<?= $row['charity_name'] ?>" required>

        <label for="charity_reg_no">Registration Number</label>
        <input type="text" name="charity_reg_no" value="<?= $row['charity_reg_no'] ?>" required>

        <label for="establishment_date">Establishment Date</label>
        <input type="date" name="establishment_date" value="<?= $row['establishment_date'] ?>" required>

        <label for="charity_description">Description</label>
        <textarea name="charity_description"><?= $row['charity_description'] ?></textarea>

        <label for="website">Website</label>
        <input type="text" name="website" value="<?= $row['website'] ?>">

        <h3>Address Information</h3>
        <div class="address-fields">
            <div>
                <label for="street_address">Street Address</label>
                <input type="text" name="street_address" value="<?= $row['street_address'] ?>">
            </div>
            <div>
                <label for="barangay">Barangay</label>
                <input type="text" name="barangay" value="<?= $row['barangay'] ?>">
            </div>
            <div>
                <label for="municipality">Municipality</label>
                <input type="text" name="municipality" value="<?= $row['municipality'] ?>">
            </div>
            <div>
                <label for="province">Province</label>
                <input type="text" name="province" value="<?= $row['province'] ?>">
            </div>
        </div>

        <div class="button-container">
            <a href="admin_page.php#charity_list" class="back-button">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <input type="submit" name="update_charity" value="Update Charity">
        </div>
    </form>
</div>

</body>