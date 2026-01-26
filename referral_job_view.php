<?php
include 'connection.php';
header('Content-Type: application/json');

// Get POST inputs
$title         = $_POST['title'] ?? null;
$state         = $_POST['state_for_search'] ?? null;
$industries    = isset($_POST['industries_for_search']) ? (array) $_POST['industries_for_search'] : [];
$practiceAreas = isset($_POST['practice_areas_for_search']) ? (array) $_POST['practice_areas_for_search'] : [];
$specialities  = isset($_POST['specialities_areas_for_search']) ? (array) $_POST['specialities_areas_for_search'] : [];

$startDate     = $_POST['start_date'] ?? null;
$endDate       = $_POST['end_date'] ?? null;

$sort          = $_POST['sort_data'] ?? 'latest';
$perPage       = (isset($_POST['par_page']) && is_numeric($_POST['par_page'])) ? (int)$_POST['par_page'] : 10;
$page          = (isset($_POST['page']) && is_numeric($_POST['page'])) ? (int)$_POST['page'] : 1;
$offset        = ($page - 1) * $perPage;

// Start SQL
$sql        = "SELECT * FROM eq_jobs WHERE deleted_at IS NULL AND job_type = 'referral'";
$conditions = [];
$params     = [];

// Title filter
if (!empty($title)) {
    $conditions[]    = "title LIKE :title";
    $params[':title'] = "%$title%";
}

// State filter
if (!empty($state)) {
    $conditions[]   = "job_state = :state";
    $params[':state'] = $state;
}

// Industries filter
if (!empty($industries)) {
    $placeholders = [];
    foreach ($industries as $i => $val) {
        $key = ":industry_$i";
        $placeholders[] = $key;
        $params[$key] = $val;
    }
    $conditions[] = "industry IN (" . implode(',', $placeholders) . ")";
}

// Practice Areas filter
if (!empty($practiceAreas)) {
    $paConditions = [];
    foreach ($practiceAreas as $i => $val) {
        $key = ":practice_$i";
        $paConditions[] = "FIND_IN_SET($key, practice_area)";
        $params[$key] = $val;
    }
    $conditions[] = "(" . implode(" OR ", $paConditions) . ")";
}

// Specialities filter
if (!empty($specialities)) {
    $spConditions = [];
    foreach ($specialities as $i => $val) {
        $key = ":speciality_$i";
        $spConditions[] = "FIND_IN_SET($key, speciality)";
        $params[$key] = $val;
    }
    $conditions[] = "(" . implode(" OR ", $spConditions) . ")";
}

// Date filtering
if (!empty($startDate)) {
    $conditions[]       = "DATE(created_at) >= :start_date";
    $params[':start_date'] = $startDate;
}
if (!empty($endDate)) {
    $conditions[]      = "DATE(created_at) <= :end_date";
    $params[':end_date'] = $endDate;
}

// Combine conditions
if (!empty($conditions)) {
    $sql .= " AND " . implode(" AND ", $conditions);
}

// Sorting
$sortOptions = [
    'latest'     => 'id DESC',
    'oldest'     => 'id ASC',
    'ascending'  => 'title ASC',
    'descending' => 'title DESC'
];
$sql .= " ORDER BY " . ($sortOptions[$sort] ?? $sortOptions['latest']);

// Pagination (direct injection of integers)
$sql .= " LIMIT $offset, $perPage";

// Prepare and execute main query
try {
    $stmt = $conn->prepare($sql); // Use $conn from connection.php
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo json_encode([
        'status' => false,
        'error'  => 'Query failed: ' . $e->getMessage()
    ]);
    exit;
}

// Count total (without LIMIT/OFFSET)
$countSQL = "SELECT COUNT(*) FROM eq_jobs WHERE deleted_at IS NULL AND job_type = 'referral'";
if (!empty($conditions)) {
    $countSQL .= " AND " . implode(" AND ", $conditions);
}

try {
    $countStmt = $conn->prepare($countSQL);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value, is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $countStmt->execute();
    $total = $countStmt->fetchColumn();
} catch (PDOException $e) {
    echo json_encode([
        'status' => false,
        'error'  => 'Count query failed: ' . $e->getMessage()
    ]);
    exit;
}

// Output JSON
echo json_encode([
    'status'     => true,
    'jobs'       => $jobs,
    'total_jobs' => (int)$total,
    'page'       => $page,
    'per_page'   => $perPage
]);
