<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'connection.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use POST only."
    ]);
    exit();
}

// Get user_id from POST
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit();
}

try {
    // Check if user exists
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id FROM users WHERE id = ? LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT id FROM new_users WHERE id = ? LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$user_id]);
    if ($stmt->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
        exit();
    }

    // Handle multiple file types: PDFs, images, videos
    $uploaded_files = [];
    $upload_dir = __DIR__ . '/../public/uploads/user-documents/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    // Process files if uploaded
    if (!empty($_FILES['files']) && is_array($_FILES['files']['tmp_name'])) {
        foreach ($_FILES['files']['tmp_name'] as $key => $tmp_name) {
            if (is_uploaded_file($tmp_name)) {
                $original_name = $_FILES['files']['name'][$key];
                $file_ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                
                // Determine file type
                $file_type = 'other';
                if (in_array($file_ext, ['pdf', 'doc', 'docx'])) {
                    $file_type = 'pdf';
                } elseif (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $file_type = 'image';
                } elseif (in_array($file_ext, ['mp4', 'mov', 'avi'])) {
                    $file_type = 'video';
                }
                
                // Generate unique filename
                $filename = time() . '_' . uniqid() . '.' . $file_ext;
                $target_path = $upload_dir . $filename;
                
                if (move_uploaded_file($tmp_name, $target_path)) {
                    // Insert into user_documents table
                    $insertStmt = $conn->prepare("
                        INSERT INTO user_documents (user_id, file, file_type, created_at, updated_at)
                        VALUES (?, ?, ?, NOW(), NOW())
                    ");
                    $insertStmt->execute([$user_id, $filename, $file_ext]);
                    
                    $uploaded_files[] = [
                        'id' => $conn->lastInsertId(),
                        'filename' => $filename,
                        'file_type' => $file_type,
                        'url' => $GLOBALS['userdocument'] . $filename
                    ];
                } else {
                    echo json_encode([
                        "status" => false,
                        "message" => "Failed to upload file: " . $original_name
                    ]);
                    exit();
                }
            }
        }
    }

    if (empty($uploaded_files)) {
        echo json_encode([
            "status" => false,
            "message" => "No files uploaded"
        ]);
        exit();
    }

    echo json_encode([
        "status" => true,
        "message" => "Media uploaded successfully",
        "count" => count($uploaded_files),
        "data" => $uploaded_files
    ]);

} catch (PDOException $e) {
    error_log("Update media error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
