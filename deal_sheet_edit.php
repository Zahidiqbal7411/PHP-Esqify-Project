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

// Get POST body as JSON or form data
$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = $_POST;
}

function getParam($key, $default = null) {
    global $input;
    return isset($input[$key]) && $input[$key] !== '' ? trim($input[$key]) : $default;
}

$id = getParam('id');
if (!$id || !is_numeric($id)) {
    echo json_encode([
        "status" => false,
        "message" => "Valid deal sheet ID is required."
    ]);
    exit();
}

// Check if deal sheet exists and not deleted
$stmt = $conn->prepare("SELECT * FROM deal_sheets WHERE id = ? AND deleted_at IS NULL");
$stmt->execute([$id]);
$deal = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deal) {
    echo json_encode([
        "status" => false,
        "message" => "Deal sheet not found."
    ]);
    exit();
}

// Read update fields
$title        = getParam('title');
$descriptions = getParam('descriptions');
$user_id      = getParam('user_id');

// If no update fields provided, return original record
if ($title === null && $descriptions === null && $user_id === null) {
    echo json_encode([
        "status" => true,
        "message" => "Deal sheet fetched successfully. No update made.",
        "data" => $deal
    ]);
    exit();
}

// Validate user_id if provided
if ($user_id !== null) {
    if (!is_numeric($user_id)) {
        echo json_encode([
            "status" => false,
            "message" => "User ID must be a number."
        ]);
        exit();
    }
<<<<<<< HEAD
    $stmtUser = $conn->prepare("SELECT id FROM users WHERE id = ?");
=======
    $stmtUser = $conn->prepare("SELECT id FROM new_users WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmtUser->execute([(int)$user_id]);
    if (!$stmtUser->fetch()) {
        echo json_encode([
            "status" => false,
            "message" => "User with given user_id does not exist."
        ]);
        exit();
    }
}

// Prepare update query
$fields = [];
$params = [];

if ($title !== null) {
    $fields[] = "title = ?";
    $params[] = $title;
}

if ($descriptions !== null) {
    $fields[] = "descriptions = ?";
    $params[] = $descriptions;
}

if ($user_id !== null) {
    $fields[] = "user_id = ?";
    $params[] = (int)$user_id;
}

if (count($fields) === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No valid fields provided to update."
    ]);
    exit();
}

$fields[] = "updated_at = NOW()";
$sql = "UPDATE deal_sheets SET " . implode(", ", $fields) . " WHERE id = ?";
$params[] = $id;

// Execute update
$stmt = $conn->prepare($sql);
$updated = $stmt->execute($params);

if ($updated) {
    $stmt = $conn->prepare("SELECT * FROM deal_sheets WHERE id = ?");
    $stmt->execute([$id]);
    $updatedDeal = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Deal sheet updated successfully.",
        "data" => $updatedDeal
    ]);
} else {
    echo json_encode([
        "status" => false,
        "message" => "Failed to update deal sheet."
    ]);
}
