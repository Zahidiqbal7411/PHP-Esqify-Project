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

// Read raw input
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);

// Merge JSON and $_POST, giving priority to form-data
$input = [];
if (!empty($_POST)) {
    $input = $_POST;
} elseif (is_array($decodedJson)) {
    $input = $decodedJson;
}

// Helper function to get params safely
function getParam($key, $default = null) {
    global $input;
    return isset($input[$key]) && $input[$key] !== '' ? trim($input[$key]) : $default;
}

// Parameters
$state_id = getParam('state_id');
$per_page = max(1, (int)getParam('per_page', 50));
$page = max(1, (int)getParam('page', 1));
$offset = ($page - 1) * $per_page;

try {
    // Check if citys table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'citys'");
    if ($tableCheck->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Cities table does not exist in database"
        ]);
        exit();
    }

    // Build base query
    $sql_base = "
        FROM citys c
        LEFT JOIN states s ON s.id = c.state_id
        WHERE 1=1
    ";

    $params = [];

    // Filter by state if provided
    if ($state_id && is_numeric($state_id)) {
        $sql_base .= " AND c.state_id = ?";
        $params[] = (int)$state_id;
    }

    // Count total
    $count_sql = "SELECT COUNT(*) AS total " . $sql_base;
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();

    if ($total === 0) {
        echo json_encode([
            "status" => true,
            "message" => "No cities found.",
            "page" => $page,
            "per_page" => $per_page,
            "total" => 0,
            "total_pages" => 0,
            "data" => []
        ]);
        exit();
    }

    // Main query
    $sql = "
        SELECT 
            c.id,
            c.name,
            c.state_id,
            c.country_id,
            s.name AS state_name
        " . $sql_base . "
        ORDER BY c.name ASC
        LIMIT ? OFFSET ?
    ";

    $params_with_limit = array_merge($params, [$per_page, $offset]);
    $stmt = $conn->prepare($sql);
    
    foreach ($params_with_limit as $i => $val) {
        $stmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    
    $stmt->execute();
    $cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "status" => true,
        "message" => "Cities fetched successfully.",
        "page" => $page,
        "per_page" => $per_page,
        "total" => $total,
        "total_pages" => ceil($total / $per_page),
        "data" => $cities
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Cities List API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
