<?php
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'connection.php';

// ✅ Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Invalid request method. Use POST only."
    ]);
    exit();
}

// ✅ Read raw input
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);

// ✅ Merge JSON and $_POST, giving priority to form-data
$input = [];
if (!empty($_POST)) {
    $input = $_POST; // multipart/form-data or application/x-www-form-urlencoded
} elseif (is_array($decodedJson)) {
    $input = $decodedJson; // raw JSON body
}

// ✅ Ensure $input is an array
if (!is_array($input)) {
    $input = [];
}

// ✅ Helper function to get params safely
function getParam($key, $default = null) {
    global $input;
    return isset($input[$key]) && $input[$key] !== '' ? trim($input[$key]) : $default;
}

// 🔹 Parameters
$id                        = getParam('id');
$title                     = getParam('title');
$state_for_search          = getParam('state_for_search');
$search_for                = getParam('search_for');
$sort_data                 = getParam('sort_data');

$industries_for_search         = array_filter((array)($input['industries_for_search'] ?? []));
$practice_areas_for_search     = array_filter((array)($input['practice_areas_for_search'] ?? []));
$specialities_areas_for_search = array_filter((array)($input['specialities_areas_for_search'] ?? []));

$per_page = max(1, (int)getParam('per_page', 10));
$page     = max(1, (int)getParam('page', 1));
$offset   = ($page - 1) * $per_page;

// 🔹 Check table existence to prevent 500 errors
$tables_exist = ['industrys' => false, 'bars' => false];
try {
    $check = $conn->query("SHOW TABLES LIKE 'industrys'");
    $tables_exist['industrys'] = $check->rowCount() > 0;
    
    $check = $conn->query("SHOW TABLES LIKE 'bars'");
    $tables_exist['bars'] = $check->rowCount() > 0;
} catch (PDOException $e) {
    error_log("Leaderboard table check error: " . $e->getMessage());
}

// 🔹 Base SQL
$sql_base = "
<<<<<<< HEAD
FROM users u
=======
FROM new_users u
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
LEFT JOIN deals d ON d.owner = u.id AND d.deleted_at IS NULL
LEFT JOIN states s ON s.id = u.state_province
" . ($tables_exist['bars'] ? "LEFT JOIN bars b ON b.id = u.bar" : "") . "
" . ($tables_exist['industrys'] ? "LEFT JOIN industrys ind ON ind.id = u.industry" : "") . "
WHERE u.role_id = 3 AND u.deleted_at IS NULL
";

$params = [];

// 🔍 Filters
if ($id && is_numeric($id)) {
    $sql_base .= " AND u.id = ?";
    $params[] = (int)$id;

    // Ensure user has deals
    $sql_base .= " AND EXISTS (SELECT 1 FROM deals d3 WHERE d3.owner = u.id AND d3.deleted_at IS NULL)";
}

if (!empty($title)) {
    if ($search_for === 'law_firm') {
        $sql_base .= " AND u.law_firm LIKE ?";
        $params[] = "%{$title}%";
    } elseif ($search_for === 'user') {
        $sql_base .= " AND CONCAT(u.first_name, ' ', u.last_name) LIKE ?";
        $params[] = "%{$title}%";
    } else {
        $sql_base .= " AND (CONCAT(u.first_name, ' ', u.last_name) LIKE ? OR u.law_firm LIKE ?)";
        $params[] = "%{$title}%";
        $params[] = "%{$title}%";
    }
}

if (!empty($state_for_search)) {
    if (is_numeric($state_for_search)) {
        $sql_base .= " AND u.state_province = ?";
        $params[] = (int)$state_for_search;
    } else {
        $sql_base .= " AND s.name LIKE ?";
        $params[] = "%{$state_for_search}%";
    }
}

if (!empty($industries_for_search)) {
    $placeholders = implode(',', array_fill(0, count($industries_for_search), '?'));
    $sql_base .= " AND u.industry IN ($placeholders)";
    $params = array_merge($params, $industries_for_search);

    // Ensure user has deals
    $sql_base .= " AND EXISTS (SELECT 1 FROM deals d2 WHERE d2.owner = u.id AND d2.deleted_at IS NULL)";
}

if (!empty($practice_areas_for_search)) {
    $conds = [];
    foreach ($practice_areas_for_search as $area) {
        $conds[] = "u.practice_area LIKE ?";
        $params[] = '%"'.$area.'"%';
    }
    $sql_base .= " AND (" . implode(" OR ", $conds) . ")";
}

if (!empty($specialities_areas_for_search)) {
    $conds = [];
    foreach ($specialities_areas_for_search as $spec) {
        $conds[] = "u.speciality LIKE ?";
        $params[] = '%"'.$spec.'"%';
    }
    $sql_base .= " AND (" . implode(" OR ", $conds) . ")";
}

// 🔹 Count total
$count_sql = "SELECT COUNT(DISTINCT u.id) AS total " . $sql_base;
try {
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total = (int)$count_stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Leaderboard count query failed: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error in count query: " . $e->getMessage()
    ]);
    exit();
}

if ($total === 0) {
    echo json_encode([
        "status" => false,
        "message" => "No records found.",
        "page" => $page,
        "per_page" => $per_page,
        "total" => 0,
        "total_pages" => 0,
        "data" => []
    ]);
    exit();
}

// 🔹 Sorting
$allowedSort = [
    'deal_volume'  => "deal_count DESC",
    'deal_total'   => "deal_total_amount DESC",
    'latest'       => "u.id DESC",
    'oldest'       => "u.id ASC",
    'ascending'    => "u.first_name ASC, u.last_name ASC, u.id ASC",
    'descending'   => "u.first_name DESC, u.last_name DESC, u.id DESC"
];

$orderBy = $allowedSort[$sort_data] ?? "u.id DESC";

// 🔹 Main query
$sql = "
SELECT 
    u.id, 
    u.first_name, 
    u.last_name, 
    u.email, 
    u.law_firm,
    u.image,
    s.name AS state_name" . 
    ($tables_exist['bars'] ? ", b.title AS bar_title" : ", NULL AS bar_title") . 
    ($tables_exist['industrys'] ? ", ind.title AS industry_title" : ", NULL AS industry_title") . ",
    COUNT(DISTINCT d.id) AS deal_count,
    IFNULL(SUM(d.amount), 0) AS deal_total_amount
" . $sql_base . "
GROUP BY u.id
ORDER BY $orderBy
LIMIT ? OFFSET ?
";

$params_with_limit = array_merge($params, [$per_page, $offset]);
try {
    $stmt = $conn->prepare($sql);
    foreach ($params_with_limit as $i => $val) {
        $stmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Leaderboard main query failed: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error in main query: " . $e->getMessage()
    ]);
    exit();
}

// ✅ Concat image path
foreach ($data as &$row) {
    if (!empty($row['image'])) {
        $row['image'] = $GLOBALS['dealownerimagepath'] . $row['image'];
    } else {
        $row['image'] = null;
    }
}
unset($row);

// ✅ Response
echo json_encode([
    "status" => true,
    "message" => "Leaderboards fetched successfully.",
    "page" => $page,
    "per_page" => $per_page,
    "total" => $total,
    "total_pages" => ceil($total / $per_page),
    "data" => $data
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
