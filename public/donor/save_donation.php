<?php
require_once __DIR__ . '/../../app/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_SESSION['donator_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if (!isset($_POST['csrf_token']) || !hash_equals(csrf_token(), (string) $_POST['csrf_token'])) {
    http_response_code(419);
    echo json_encode(['success' => false, 'message' => 'Invalid security token']);
    exit;
}

$response = ['success' => false, 'message' => ''];
$uploadDir = PUBLIC_PATH . '/uploads/donations';
$validCategories = ['shirt', 'pants', 'headwear', 'footwear'];

try {
    $conn->begin_transaction();

    $items = $_POST['items'] ?? [];
    if (!is_array($items) || $items === []) {
        throw new RuntimeException('No donation items were submitted.');
    }

    $successCount = 0;

    foreach ($items as $index => $item) {
        if (empty($item['clothingType']) || empty($item['ageGroup']) || empty($item['quantity'])) {
            continue;
        }

        $clothingType = strtolower((string) $item['clothingType']);
        if ($clothingType === 'shoes') {
            $clothingType = 'footwear';
        }

        if (!in_array($clothingType, $validCategories, true)) {
            throw new RuntimeException('Invalid clothing type submitted.');
        }

        $ageGroup = (string) $item['ageGroup'];
        $quantity = max(1, (int) $item['quantity']);
        $donatorId = (int) $_SESSION['donator_id'];
        $description = $clothingType . ' for ' . $ageGroup;

        $imagePath = null;
        $file = $_FILES['items']['name'][$index]['image'] ?? null;
        if ($file !== null && ($_FILES['items']['error'][$index]['image'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $upload = [
                'name' => $_FILES['items']['name'][$index]['image'],
                'type' => $_FILES['items']['type'][$index]['image'] ?? '',
                'tmp_name' => $_FILES['items']['tmp_name'][$index]['image'],
                'error' => $_FILES['items']['error'][$index]['image'],
                'size' => $_FILES['items']['size'][$index]['image'] ?? 0,
            ];

            $filename = store_uploaded_image($upload, $uploadDir);
            $imagePath = 'uploads/donations/' . $filename;
        }

        $stmt = $conn->prepare("INSERT INTO tbl_donation_items
                              (donator_id, category, description, quantity, image_path, status)
                              VALUES (?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param('issis', $donatorId, $clothingType, $description, $quantity, $imagePath);

        if (!$stmt->execute()) {
            throw new RuntimeException('Failed to insert the donation item.');
        }

        $stmt->close();
        $successCount++;
    }

    $conn->commit();
    $response = ['success' => true, 'message' => "$successCount items donated successfully"];
} catch (RuntimeException $e) {
    $conn->rollback();
    $response['message'] = $e->getMessage();
} catch (Throwable $e) {
    $conn->rollback();
    error_log('Donation save error: ' . $e->getMessage());
    $response['message'] = 'The donation could not be saved. Please try again.';
}

echo json_encode($response);
