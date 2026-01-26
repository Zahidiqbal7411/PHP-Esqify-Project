<?php
header("Content-Type: application/json; charset=UTF-8");
require_once 'connection.php';  // your DB connection

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use POST only."
    ]);
    exit();
}

// Get input JSON or POST data
$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    $input = $_POST;
}

function getParam($key, $default = null) {
    global $input;
    return isset($input[$key]) && $input[$key] !== '' ? trim($input[$key]) : $default;
}

$perPage   = max(1, (int)getParam('per_page', 10));
$page      = max(1, (int)getParam('page', 1));
$offset    = ($page - 1) * $perPage;

// Filters
$id        = getParam('id');
$title     = getParam('title');
$owner     = getParam('owner');
$sort_data = getParam('sort_data');
$tag       = getParam('tag'); // <-- FIX: get tag value

// Base SQL
$sqlBase = "FROM deal_sheets ds
<<<<<<< HEAD
LEFT JOIN users u ON ds.user_id = u.id
=======
LEFT JOIN new_users u ON ds.user_id = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
WHERE ds.deleted_at IS NULL";

$params = [];

// Filter by id
if ($id && is_numeric($id)) {
    $sqlBase .= " AND ds.id = ?";
    $params[] = (int)$id;
}

// Filter by title (like)
if ($title) {
    $sqlBase .= " AND ds.title LIKE ?";
    $params[] = "%$title%";
}

// Filter by owner/user_id
if ($owner && is_numeric($owner)) {
    $sqlBase .= " AND ds.user_id = ?";
    $params[] = (int)$owner;
}

// Filter by date range (tag)
if ($tag) {
    switch (strtolower($tag)) {
        case 'monthly':
            $sqlBase .= " AND YEAR(ds.created_at) = YEAR(CURDATE()) 
                          AND MONTH(ds.created_at) = MONTH(CURDATE())";
            break;
        case 'week':
            $sqlBase .= " AND YEARWEEK(ds.created_at, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'today':
            $sqlBase .= " AND DATE(ds.created_at) = CURDATE()";
            break;
    }
}

// Count total records
$countSql = "SELECT COUNT(*) AS total " . $sqlBase;
$stmt = $conn->prepare($countSql);
$stmt->execute($params);
$total = (int)$stmt->fetchColumn();

if ($total === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No deal sheets found.",
        "page" => $page,
        "per_page" => $perPage,
        "total" => 0,
        "total_pages" => 0,
        "data" => []
    ]);
    exit();
}

// Sorting
$orderBy = "ds.id DESC";
if ($sort_data === 'oldest') {
    $orderBy = "ds.id ASC";
} elseif ($sort_data === 'ascending') {
    $orderBy = "ds.title ASC";
} elseif ($sort_data === 'descending') {
    $orderBy = "ds.title DESC";
}

// Main query
$sql = "SELECT ds.id, ds.title, ds.descriptions, ds.user_id, u.first_name, u.last_name, ds.created_at "
     . $sqlBase . " ORDER BY $orderBy LIMIT ? OFFSET ?";

$paramsWithLimit = array_merge($params, [$perPage, $offset]);
$stmt = $conn->prepare($sql);
foreach ($paramsWithLimit as $i => $val) {
    $stmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
}
$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Response
echo json_encode([
    "status" => true,
    "message" => "Deal sheets fetched successfully.",
    "page" => $page,
    "per_page" => $perPage,
    "total" => $total,
    "total_pages" => ceil($total / $perPage),
    "data" => $data
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
