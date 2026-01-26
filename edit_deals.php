<?php
header("Content-Type: application/json");

// Allow only POST or PUT
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    echo json_encode(["status" => false, "message" => "Only POST or PUT method is allowed"]);
    exit();
}

require_once 'connection.php';

// Get input data - from POST form-data or JSON body
if (!empty($_POST)) {
    $data = $_POST;
} else {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
}

if (!$data) {
    echo json_encode(["status" => false, "message" => "Invalid input data"]);
    exit();
}

// Required deal ID
$deal_id = isset($data['id']) ? intval($data['id']) : null;
if (!$deal_id) {
    echo json_encode(["status" => false, "message" => "Deal ID is required"]);
    exit();
}

// Check if deal exists
try {
    $checkSql = "SELECT * FROM deals WHERE id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$deal_id]);
    $deal = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        echo json_encode(["status" => false, "message" => "Deal not found"]);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(["status" => false, "message" => "Error fetching deal: " . $e->getMessage()]);
    exit();
}

// Check process_type to decide update or fetch
$process_type = isset($data['process_type']) ? strtolower(trim($data['process_type'])) : '';

// Allowed fields for update
$allowedFields = [
    'title',
    'descriptions',
    'notes',
    'press_release_link',
    'tags',
    'photos', // will handle separately
    'amount',
    'owner',
    'firm',
    'posted_date',
    'status',
    'client',
    'industry',
    'company_name',
    'state',
    'city',
    'practice_area',
    'speciality',
    'other_attorneys'
];

if ($process_type === 'update') {
    $upload_dir = __DIR__ . '/../public/uploads/deal-images/';

    // Delete old images if new photos are uploaded
    $newPhotosUploaded = !empty($_FILES['photos']) && is_array($_FILES['photos']['tmp_name']);

    if ($newPhotosUploaded) {
        $existing_photos = [];
        if (!empty($deal['photos'])) {
            $existing_photos = json_decode($deal['photos'], true);
            if (!is_array($existing_photos)) {
                $existing_photos = [];
            }
        }

        // Delete old images from disk
        foreach ($existing_photos as $old_img) {
            $old_path = $upload_dir . $old_img;
            if (file_exists($old_path) && is_file($old_path)) {
                @unlink($old_path);
            }
        }
    }

    // Handle new photos upload
    $image_names = [];

    if ($newPhotosUploaded) {
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $filename = basename($_FILES['photos']['name'][$key]);
                // Sanitize filename
                $filename = preg_replace("/[^a-zA-Z0-9\.\-\s]/", "", $filename);
                $target = $upload_dir . $filename;
                if (file_exists($target)) {
                    $filename = time() . "_" . $filename;
                    $target = $upload_dir . $filename;
                }
                if (move_uploaded_file($tmp_name, $target)) {
                    $image_names[] = $filename;
                } else {
                    echo json_encode([
                        "status" => false,
                        "message" => "Failed to upload file: " . $_FILES['photos']['name'][$key]
                    ]);
                    exit();
                }
            }
        }
    }

    // Build update query dynamically
    $setParts = [];
    $params = [];

    foreach ($allowedFields as $field) {
        if ($field === 'photos') {
            if ($newPhotosUploaded) {
                $setParts[] = "photos = ?";
                $params[] = json_encode($image_names);
            }
            // else no update to photos field
        } elseif (array_key_exists($field, $data)) { // allow null values
            // For tags and other_attorneys fields, encode JSON if they are arrays
            if (in_array($field, ['tags', 'other_attorneys'])) {
                if (is_string($data[$field])) {
                    // Try decode if string
                    $decoded = json_decode($data[$field], true);
                    $params[] = json_encode(is_array($decoded) ? $decoded : []);
                } elseif (is_array($data[$field])) {
                    $params[] = json_encode($data[$field]);
                } else {
                    $params[] = json_encode([]);
                }
                $setParts[] = "$field = ?";
            } else {
                $setParts[] = "$field = ?";
                $params[] = $data[$field];
            }
        }
    }

    if (empty($setParts)) {
        echo json_encode(["status" => false, "message" => "No valid fields to update"]);
        exit();
    }

    $setSql = implode(", ", $setParts);

    try {
        $params[] = $deal_id;
        $sql = "UPDATE deals SET $setSql, updated_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => true, "message" => "Deal updated successfully"]);
        } else {
            echo json_encode(["status" => true, "message" => "No changes made"]);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => false, "message" => "Error updating deal: " . $e->getMessage()]);
    }
} else {
    // Just return the deal data with owner image full URL and deal photos full URLs

    // Append full owner image URL
    if (!empty($deal['owner'])) {
        try {
<<<<<<< HEAD
            $ownerSql = "SELECT image FROM users WHERE id = ?";
=======
            $ownerSql = "SELECT image FROM new_users WHERE id = ?";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            $ownerStmt = $conn->prepare($ownerSql);
            $ownerStmt->execute([$deal['owner']]);
            $ownerImgRow = $ownerStmt->fetch(PDO::FETCH_ASSOC);
            if ($ownerImgRow && !empty($ownerImgRow['image'])) {
                $deal['owner_image'] = $GLOBALS['dealownerimagepath'] . $ownerImgRow['image'];
            } else {
                $deal['owner_image'] = null;
            }
        } catch (PDOException $e) {
            $deal['owner_image'] = null;
        }
    } else {
        $deal['owner_image'] = null;
    }

    // Append full URLs for deal photos
    if (!empty($deal['photos'])) {
        $photosArray = json_decode($deal['photos'], true);
        if (is_array($photosArray)) {
            foreach ($photosArray as &$photo) {
                $photo = $GLOBALS['dealimage'] . $photo;
            }
            unset($photo);
            $deal['photos'] = $photosArray; // override photos with full URLs array
        } else {
            $deal['photos'] = [];
        }
    } else {
        $deal['photos'] = [];
    }

    echo json_encode(["status" => true, "message" => "Deal data fetched", "data" => $deal]);
}
