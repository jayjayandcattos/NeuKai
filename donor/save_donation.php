<?php
session_start();
require 'configuration/db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['donor_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        $conn->begin_transaction();

        // Process items
        $items = $_POST['items'] ?? [];
        $successCount = 0;

        foreach ($items as $index => $item) {
            // Validate required fields
            if (empty($item['clothingType']) || empty($item['ageGroup']) || empty($item['quantity'])) {
                continue;
            }

            // Map clothing type to ENUM values
            $clothingType = strtolower($item['clothingType']);
            if ($clothingType === 'shoes') {
                $clothingType = 'footwear';
            }

            // Validate against ENUM values
            $validCategories = ['shirt', 'pants', 'headwear', 'footwear'];
            if (!in_array($clothingType, $validCategories)) {
                throw new Exception("Invalid clothing type: $clothingType");
            }

            $ageGroup = $conn->real_escape_string($item['ageGroup']);
            $quantity = (int) $item['quantity'];
            $donatorId = (int) $_SESSION['donor_id'];
            $description = "{$item['clothingType']} for $ageGroup";

            // Handle file upload
            $imagePath = null;
            if (isset($_FILES['items']['name'][$index]['image'])) {
                $uploadDir = '../uploads/donations/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $fileType = mime_content_type($_FILES['items']['tmp_name'][$index]['image']);
                
                if (!in_array($fileType, $allowedTypes)) {
                    throw new Exception('Invalid file type. Only JPG, PNG, and GIF are allowed.');
                }

                $ext = pathinfo($_FILES['items']['name'][$index]['image'], PATHINFO_EXTENSION);
                $filename = uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $filename;

                if (move_uploaded_file($_FILES['items']['tmp_name'][$index]['image'], $targetPath)) {
                    $imagePath = $targetPath;
                }
            }

            // Insert donation item
            $stmt = $conn->prepare("INSERT INTO tbl_donation_items 
                                  (donator_id, category, description, quantity, image_path, status)
                                  VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->bind_param("issis", $donatorId, $clothingType, $description, $quantity, $imagePath);

            if (!$stmt->execute()) {
                throw new Exception("Failed to insert donation item: " . $stmt->error);
            }

            $stmt->close();
            $successCount++;
        }

        $conn->commit();
        $response = ['success' => true, 'message' => "$successCount items donated successfully"];
    } catch (Exception $e) {
        $conn->rollback();
        $response = ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }

    echo json_encode($response);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>