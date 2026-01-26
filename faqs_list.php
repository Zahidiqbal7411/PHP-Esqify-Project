<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'connection.php'; // DB connection

// Only allow GET requests
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use GET only."
    ]);
    exit();
}

try {
    // Check if table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'faqs'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "FAQs table does not exist in database"
        ]);
        exit();
    }

    $sql = "
        SELECT 
            id,
            subject,
            question,
            answer,
            `order`,
            created_at
        FROM faqs
        WHERE status = 1
        ORDER BY `order` ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $faqs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => count($faqs) > 0 ? "FAQs fetched successfully." : "No FAQs available.",
        "count" => count($faqs),
        "data" => $faqs
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("FAQs API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
