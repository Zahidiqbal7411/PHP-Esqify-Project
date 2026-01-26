<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'connection.php';

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use GET only."
    ]);
    exit();
}

try {
    // Check if bars table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'bars'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Bars table does not exist in database"
        ]);
        exit();
    }

    $sql = "
        SELECT 
            id,
            title,
            state_id,
            country_id,
            description,
            status,
            created_at
        FROM bars
        WHERE status = 1
        ORDER BY title ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $bars = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => count($bars) > 0 ? "Bars fetched successfully." : "No bars available.",
        "count" => count($bars),
        "data" => $bars
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Bars List API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
