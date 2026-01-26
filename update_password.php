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

// Get parameters from POST
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$current_password = isset($_POST['current_password']) ? trim($_POST['current_password']) : '';
$new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
$confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

// Validation
if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit();
}

if (empty($current_password)) {
    echo json_encode([
        "status" => false,
        "message" => "current_password is required"
    ]);
    exit();
}

if (empty($new_password)) {
    echo json_encode([
        "status" => false,
        "message" => "new_password is required"
    ]);
    exit();
}

if ($new_password !== $confirm_password) {
    echo json_encode([
        "status" => false,
        "message" => "new_password and confirm_password do not match"
    ]);
    exit();
}

if (strlen($new_password) < 6) {
    echo json_encode([
        "status" => false,
        "message" => "new_password must be at least 6 characters long"
    ]);
    exit();
}

try {
    // Fetch user
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id, password FROM users WHERE id = ? LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT id, password FROM new_users WHERE id = ? LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "status" => false,
            "message" => "User not found"
        ]);
        exit();
    }

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        echo json_encode([
            "status" => false,
            "message" => "Current password is incorrect"
        ]);
        exit();
    }

    // Hash new password
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update password
<<<<<<< HEAD
    $updateStmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
=======
    $updateStmt = $conn->prepare("UPDATE new_users SET password = ?, updated_at = NOW() WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $updateStmt->execute([$new_password_hash, $user_id]);

    echo json_encode([
        "status" => true,
        "message" => "Password updated successfully"
    ]);

} catch (PDOException $e) {
    error_log("Update password error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
