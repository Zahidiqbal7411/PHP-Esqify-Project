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

// Get raw input
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);
if (!is_array($decodedJson)) $decodedJson = [];

// Helper function to get params
function getParam($key, $default = null) {
    global $decodedJson;
    if (isset($decodedJson[$key])) return $decodedJson[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

$user_id = getParam('user_id');
$blocked_id = getParam('blocked_id');
$action = getParam('action', 'block'); // block, unblock

if (!$user_id || !$blocked_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id and blocked_id are required"
    ]);
    exit();
}

try {
    if (strtolower($action) === 'block') {
        // Check if already blocked
        $checkStmt = $conn->prepare("SELECT id FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
        $checkStmt->execute([$user_id, $blocked_id]);
        
        if ($checkStmt->rowCount() === 0) {
            $stmt = $conn->prepare("INSERT INTO blocked_users (blocker_id, blocked_id, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$user_id, $blocked_id]);
        }
        
        echo json_encode([
            "status" => true,
            "message" => "User blocked successfully"
        ]);
    } else {
        $stmt = $conn->prepare("DELETE FROM blocked_users WHERE blocker_id = ? AND blocked_id = ?");
        $stmt->execute([$user_id, $blocked_id]);
        
        echo json_encode([
            "status" => true,
            "message" => "User unblocked successfully"
        ]);
    }

} catch (PDOException $e) {
    error_log("Block user error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
