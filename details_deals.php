<?php
header("Content-Type: application/json");

// ===== Only allow POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Only POST method is allowed"]);
    exit();
}

require_once 'connection.php';


// ===== Get raw input (JSON support) =====
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);

// ===== Get deal_id from JSON or form-data =====
$deal_id = 0;
if (isset($decodedJson['id'])) {
    $deal_id = intval($decodedJson['id']);
} elseif (isset($_POST['id'])) {
    $deal_id = intval($_POST['id']);
}

if (!$deal_id) {
    echo json_encode(["status" => false, "message" => "Deal ID is required"]);
    exit();
}

try {
    // ===== Fetch main deal =====
    $stmt = $conn->prepare("SELECT * FROM deals WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $deal_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        echo json_encode(["status" => false, "message" => "Deal not found"]);
        exit();
    }

    // Decode JSON fields for main deal
    $deal['tags'] = !empty($deal['tags']) ? json_decode($deal['tags'], true) : [];
    
    // Decode photos and prepend full URL
    $photos_decoded = !empty($deal['photos']) ? json_decode($deal['photos'], true) : [];
    $deal['photos'] = [];
    if (is_array($photos_decoded)) {
        foreach ($photos_decoded as $photo) {
            if (!empty($photo)) {
                $deal['photos'][] = rtrim($GLOBALS['dealimage'], '/') . '/' . ltrim($photo, '/');
            }
        }
    }
    
    $deal['is_deleted'] = !is_null($deal['deleted_at']);

    // ===== Return response =====
    echo json_encode([
        "status" => true,
        "message" => "Deal details fetched successfully",
        "data" => [
            "deal" => $deal
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Error fetching deal: " . $e->getMessage()
    ]);
}
?>
