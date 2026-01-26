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
    // Try positions table first, then job_positions as fallback
    $tableName = 'positions';
    $tableCheck = $conn->query("SHOW TABLES LIKE 'positions'");
    
    if ($tableCheck->rowCount() === 0) {
        // Try alternative table name
        $tableCheck = $conn->query("SHOW TABLES LIKE 'job_positions'");
        if ($tableCheck->rowCount() === 0) {
            echo json_encode([
                "status" => false,
                "message" => "Positions table does not exist in database"
            ]);
            exit();
        }
        $tableName = 'job_positions';
    }

    $sql = "
        SELECT 
            id,
            title,
            description
        FROM {$tableName}
        WHERE status = 1
        ORDER BY title ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => count($positions) > 0 ? "Positions fetched successfully." : "No positions available.",
        "count" => count($positions),
        "data" => $positions
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Positions List API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
