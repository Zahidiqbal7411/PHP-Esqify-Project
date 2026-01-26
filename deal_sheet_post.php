<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'connection.php'; // DB connection

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use POST only."
    ]);
    exit();
}

// Get parameters from form-data or x-www-form-urlencoded
$title        = isset($_POST['title']) ? trim($_POST['title']) : null;
$descriptions = isset($_POST['descriptions']) ? trim($_POST['descriptions']) : null;
$user_id      = isset($_POST['user_id']) ? trim($_POST['user_id']) : null;

// Validate required fields
if (!$title || !$descriptions || !$user_id || !is_numeric($user_id)) {
    echo json_encode([
        "status" => false,
        "message" => "Missing or invalid required parameters: title, descriptions, user_id."
    ]);
    exit();
}

try {
    // Insert into DB
    $sql = "INSERT INTO deal_sheets (title, descriptions, user_id, created_at) VALUES (?, ?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$title, $descriptions, (int)$user_id]);
    $insertedId = $conn->lastInsertId();

    // Fetch inserted record
    $sqlFetch = "SELECT ds.id, ds.title, ds.descriptions, ds.user_id, u.first_name, u.last_name, ds.created_at 
                 FROM deal_sheets ds
<<<<<<< HEAD
                 LEFT JOIN users u ON ds.user_id = u.id
=======
                 LEFT JOIN new_users u ON ds.user_id = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
                 WHERE ds.id = ?";
    $stmtFetch = $conn->prepare($sqlFetch);
    $stmtFetch->execute([$insertedId]);
    $data = $stmtFetch->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Deal sheet created successfully.",
        "data" => $data
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
