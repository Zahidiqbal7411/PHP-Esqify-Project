<?php
require_once 'connection.php'; // loads $conn and globals
header('Content-Type: application/json');

// ✅ Get user_id and other POST data
$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'User ID is required']);
    exit;
}

$descriptions = $_POST['descriptions'] ?? null;
// ✅ Set status default to 'active'
$status = $_POST['status'] ?? 'active';

// ✅ Handle multiple image uploads
$images = $_FILES['images'] ?? null;
$validMime = ['image/jpeg','image/png','image/jpg','image/gif','image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB max
$imageNames = [];

try {
    if ($images) {
        // Normalize single file to array format
        if (!is_array($images['name'])) {
            $images = [
                'name' => [$images['name']],
                'type' => [$images['type']],
                'tmp_name' => [$images['tmp_name']],
                'error' => [$images['error']],
                'size' => [$images['size']],
            ];
        }

        $uploadDir = __DIR__ . '/../public/uploads/deal-images/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        for ($i = 0; $i < count($images['name']); $i++) {
            if ($images['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

            if ($images['error'][$i] !== UPLOAD_ERR_OK) {
                throw new Exception('Upload error: ' . $images['name'][$i]);
            }

            if ($images['size'][$i] > $maxSize) {
                throw new Exception('File too large: ' . $images['name'][$i]);
            }

            if (!in_array($images['type'][$i], $validMime)) {
                throw new Exception('Invalid file type: ' . $images['name'][$i]);
            }

            $ext = pathinfo($images['name'][$i], PATHINFO_EXTENSION);
            $filename = time() . '_' . bin2hex(random_bytes(5)) . '.' . $ext;
            $dest = $uploadDir . $filename;

            if (!move_uploaded_file($images['tmp_name'][$i], $dest)) {
                throw new Exception('Failed to move uploaded file: ' . $images['name'][$i]);
            }

            $imageNames[] = $filename;
        }
    }

    // Convert image names to JSON for DB
    $photosJson = json_encode($imageNames);

    // ✅ Insert feed into database
    $stmt = $conn->prepare("
        INSERT INTO feeds (owner, descriptions, status, photos, posted_date, created_at, updated_at)
        VALUES (?, ?, ?, ?, NOW(), NOW(), NOW())
    ");
    $stmt->execute([
        $userId,
        $descriptions,
        $status,
        $photosJson
    ]);

    $feedId = $conn->lastInsertId();

    // ✅ Prepare full image URLs for response
    $imageUrls = array_map(fn($img) => $GLOBALS['dealimage'] . $img, $imageNames);

    echo json_encode([
        'success' => true,
        'message' => 'Feed created successfully.',
        'feed_id' => $feedId,
        'photos' => $imageUrls
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
