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

// Get parameters
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
$referral_code = isset($_POST['referral_code']) ? trim($_POST['referral_code']) : '';

// Validation
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
    $stmt = $conn->prepare("SELECT id, referral_status FROM users WHERE id = ? LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT id, referral_status FROM new_users WHERE id = ? LIMIT 1");
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

    // Check if user is already in referral program
    if ($user['referral_status'] == 1 || $user['referral_status'] == 'active') {
        echo json_encode([
            "status" => false,
            "message" => "User is already enrolled in the referral program"
        ]);
        exit();
    }

    // Generate unique referral code if not provided
    if (empty($referral_code)) {
        $referral_code = strtoupper(substr(md5($user_id . time()), 0, 8));
    }

    // Check if referral code is unique
<<<<<<< HEAD
    $checkStmt = $conn->prepare("SELECT id FROM users WHERE referral_code = ? AND id != ? LIMIT 1");
=======
    $checkStmt = $conn->prepare("SELECT id FROM new_users WHERE referral_code = ? AND id != ? LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $checkStmt->execute([$referral_code, $user_id]);
    
    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            "status" => false,
            "message" => "Referral code already in use, please try again"
        ]);
        exit();
    }

    // Update user to join referral program
    $updateStmt = $conn->prepare("
<<<<<<< HEAD
        UPDATE users 
=======
        UPDATE new_users 
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        SET referral_status = 1, 
            referral_code = ?, 
            referral_joined_at = NOW(),
            updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([$referral_code, $user_id]);

    echo json_encode([
        "status" => true,
        "message" => "Successfully joined referral program",
        "data" => [
            "user_id" => $user_id,
            "referral_code" => $referral_code,
            "referral_status" => "active"
        ]
    ]);

} catch (PDOException $e) {
    error_log("Join referral error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
