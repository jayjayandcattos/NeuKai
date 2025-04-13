<?php
session_start();
require '../configuration/db_connect.php';

date_default_timezone_set('Asia/Manila');

if (!isset($_GET['charity_id'])) {
    die("No charity specified.");
}

$items = [];
$donator_id = '';
$charity_id = $_GET['charity_id'];
$loggedin = isset($_SESSION['donator_id']); 

// Ensure the donator is logged in
if (!$loggedin) {
    die("You must be logged in to donate.");
}

$donator_id = $_SESSION['donator_id']; // Get the logged-in donator's ID

// Fetch charity details
$stmt = $conn->prepare("SELECT * FROM tbl_charity WHERE charity_id = ?");
$stmt->bind_param("i", $charity_id); 
$stmt->execute();
$result = $stmt->get_result();
$charity = $result->fetch_assoc(); 
$stmt->close();

// Debugging: Check if charity data was fetched successfully
if ($charity) {
    // Full address construction
    $full_address = htmlspecialchars($charity['street_address'] . ', ' . $charity['barangay'] . ', ' . $charity['municipality'] . ', ' . $charity['province']);
} else {
    die("Charity not found.");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get donation details
    $donation_name = $_POST['donation_name'];
    $total_donation = 0;
    $status = 'pending'; // Default status (change as needed)
    $donation_date = date('Y-m-d H:i:s');
    
    // Insert donation details into tbl_donation
    $stmt = $conn->prepare("INSERT INTO tbl_donations (donator_id, donation_name, total_donation, status, donation_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiss", $donator_id, $donation_name, $total_donation, $status, $donation_date);
    $stmt->execute();
    $donation_id = $stmt->insert_id; // Get the inserted donation ID
    $stmt->close();

    // Insert items into tbl_donation_items
    foreach ($_POST['items'] as $item) {
        $category = $item['category'];
        $description = $item['description'];
        $quantity = $item['quantity'];
        
        // Handle file upload for images
        $image_path = '';
        if (isset($_FILES['items']['name']['image'])) {
            $image_path = 'uploads/' . basename($_FILES['items']['name']['image']);
            move_uploaded_file($_FILES['items']['tmp_name']['image'], $image_path);
        }

        // Insert each item into tbl_donation_items
        $stmt = $conn->prepare("INSERT INTO tbl_donation_items (donation_id, category, description, quantity, image_path, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("isssis", $donation_id, $category, $description, $quantity, $image_path, $status);
        $stmt->execute();
        $stmt->close();

        // Update total donation amount (you can modify this based on the donation's value or item price)
        $total_donation += $quantity; // For simplicity, assuming quantity equals amount donated
    }

    // Update total donation amount in tbl_donation
    $stmt = $conn->prepare("UPDATE tbl_donations SET total_donation = ? WHERE donation_id = ?");
    $stmt->bind_param("ii", $total_donation, $donation_id);
    $stmt->execute();
    $stmt->close();

    // Insert into tbl_donation_transactions
    $admin_id = 1; // Assuming admin ID, you can adjust this
    $remarks = 'Donation made by ' . $donator_id;
    $created_at = date('Y-m-d H:i:s');
    $updated_at = date('Y-m-d H:i:s');
    $approved_at = null; // Assuming the donation is pending approval
    $rejected_at = null;
    $delivered_at = null;

    $stmt = $conn->prepare("INSERT INTO tbl_donation_transactions (donator_id, charity_id, donation_id, status, admin_id, remarks, created_at, updated_at, approved_at, rejected_at, delivered_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiissssssss", $donator_id, $charity_id, $donation_id, $status, $admin_id, $remarks, $created_at, $updated_at, $approved_at, $rejected_at, $delivered_at);
    $stmt->execute();
    $stmt->close();

    // Redirect to a thank you or confirmation page
    header("Location: d-donate.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Form</title>
    <style>
        .remove-item-btn {
            cursor: pointer;
            color: red;
            font-weight: bold;
            margin-left: 10px;
        }
    </style>
</head>
<body>
<h1><?php echo isset($charity) ? htmlspecialchars($charity['charity_name']) : 'Charity not found'; ?> - Profile</h1>
<p><strong>Address:</strong> <?= isset($full_address) ? $full_address : 'Address not available'; ?></p>
<p><strong>Description:</strong> <?= isset($charity) ? htmlspecialchars($charity['charity_description']) : 'Description not available'; ?></p>
<h3>has received (total) donations</h3>

<h2>Donation Form</h2>

<form action="" method="POST" enctype="multipart/form-data">
    <label>Donation Name:</label>
    <input type="text" name="donation_name" required>

    <!-- Item Categories and Fields (Dynamic) -->
    <div id="items-container">
        <div class="item" id="item-0">
            <label>Category:</label>
            <select name="items[0][category]" required>
                <option value="shirt">Shirt</option>
                <option value="pants">Pants</option>
                <option value="headwear">Headwear</option>
                <option value="footwear">Footwear</option>
            </select>

            <label>Description:</label>
            <textarea name="items[0][description]" required placeholder="Describe the item"></textarea>

            <label for="quantity">Quantity:</label>
            <input type="number" name="items[0][quantity]" value="1" min="1" required>

            <label for="image">Item Image:</label>
            <input type="file" name="items[0][image]" accept="image/*" required>

            <!-- Remove Button -->
            <span class="remove-item-btn" onclick="removeItem(0)">X</span>
        </div>
    </div>

    <!-- Button to add new item -->
    <button type="button" onclick="addNewItem()">Add New Item</button>

    <br><br>
    <button type="submit" class="submit" name="submit">Submit</button>
</form>

<script>
    let itemCount = 1; // Track the number of items
    function addNewItem() {
        const container = document.getElementById('items-container');

        // Create a new div for the new item
        const newItem = document.createElement('div');
        newItem.classList.add('item');
        newItem.id = 'item-' + itemCount;

        newItem.innerHTML = `
            <label>Category:</label>
            <select name="items[${itemCount}][category]" required>
                <option value="shirt">Shirt</option>
                <option value="pants">Pants</option>
                <option value="headwear">Headwear</option>
                <option value="footwear">Footwear</option>
            </select>

            <label>Description:</label>
            <textarea name="items[${itemCount}][description]" required placeholder="Describe the item"></textarea>

            <label for="quantity">Quantity:</label>
            <input type="number" name="items[${itemCount}][quantity]" value="1" min="1" required>

            <label for="image">Item Image:</label>
            <input type="file" name="items[${itemCount}][image]" accept="image/*" required>

            <!-- Remove Button -->
            <span class="remove-item-btn" onclick="removeItem(${itemCount})">X</span>
        `;

        container.appendChild(newItem);
        itemCount++;
    }

    function removeItem(itemId) {
        const itemToRemove = document.getElementById('item-' + itemId);
        itemToRemove.remove();
    }
</script>

</body>
</html>
