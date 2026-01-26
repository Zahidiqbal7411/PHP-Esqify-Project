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
$membership_type = isset($_POST['membership_type']) ? trim($_POST['membership_type']) : '';

// Validation
if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit();
}

if (empty($membership_type)) {
    echo json_encode([
        "status" => false,
        "message" => "membership_type is required (e.g., basic, premium, pro)"
    ]);
    exit();
}

$valid_memberships = ['basic', 'premium', 'pro', 'free'];
if (!in_array(strtolower($membership_type), $valid_memberships)) {
    echo json_encode([
        "status" => false,
        "message" => "Invalid membership_type. Valid options: " . implode(', ', $valid_memberships)
    ]);
    exit();
}

try {
    // Check if user exists
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id, membership_type FROM users WHERE id = ? LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT id, membership_type FROM new_users WHERE id = ? LIMIT 1");
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

    $old_membership = $user['membership_type'] ?? 'free';

    // Update membership
    $updateStmt = $conn->prepare("
<<<<<<< HEAD
        UPDATE users 
=======
        UPDATE new_users 
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        SET membership_type = ?, updated_at = NOW() 
        WHERE id = ?
    ");
    $updateStmt->execute([strtolower($membership_type), $user_id]);

    // Log the membership change (optional - create a membership_changes table if needed)
    try {
        $logStmt = $conn->prepare("
            INSERT INTO membership_changes (user_id, old_membership, new_membership, changed_at)
            VALUES (?, ?, ?, NOW())
        ");
        $logStmt->execute([$user_id, $old_membership, strtolower($membership_type)]);
    } catch (PDOException $e) {
        // Table might not exist, just log the error
        error_log("Membership change log error: " . $e->getMessage());
    }

    echo json_encode([
        "status" => true,
        "message" => "Membership updated successfully",
        "data" => [
            "user_id" => $user_id,
            "old_membership" => $old_membership,
            "new_membership" => strtolower($membership_type)
        ]
    ]);

} catch (PDOException $e) {
    error_log("Change membership error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
