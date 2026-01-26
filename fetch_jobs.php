<?php
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
header("Content-Type: application/json");
require_once 'connection.php';

// Helper: Get param from POST or GET
function getRequestParam($name, $default = null) {
    if (isset($_POST[$name])) return $_POST[$name];
    if (isset($_GET[$name])) return $_GET[$name];
    return $default;
}

// Helper: Convert string to array if needed
function ensureArray($value) {
    if (is_array($value)) return $value;
    if (is_string($value) && trim($value) !== '') {
        // split by comma and trim each item
        $arr = explode(',', $value);
        return array_map('trim', $arr);
    }
    return [];
}

// Request Parameters
$id                  = trim((string) getRequestParam('id', ''));
$title               = trim((string) getRequestParam('title', ''));
$firm                = trim((string) getRequestParam('firm', ''));
$job_city            = trim((string) getRequestParam('job_city', ''));
$job_state           = trim((string) getRequestParam('job_state', ''));
$tag                 = trim((string) getRequestParam('tag', ''));
$posted_from         = trim((string) getRequestParam('posted_from', ''));
$posted_to           = trim((string) getRequestParam('posted_to', ''));
$state_for_search    = trim((string) getRequestParam('state_for_search', ''));
$bar_for_search      = trim((string) getRequestParam('bar_for_search', ''));

<<<<<<< HEAD
$industries_for_search    = ensureArray(getRequestParam('industries_for_search', []));
$practice_areas_for_search = ensureArray(getRequestParam('practice_areas_for_search', []));
$specialities_for_search  = ensureArray(getRequestParam('specialities_for_search', []));
=======
$industries_for_search    = ensureArray(getRequestParam('industries_for_search', getRequestParam('industry_for_search', [])));
$practice_areas_for_search = ensureArray(getRequestParam('practice_areas_for_search', getRequestParam('practice_area_for_search', [])));
$specialities_for_search  = ensureArray(getRequestParam('specialities_for_search', getRequestParam('speciality_for_search', [])));
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

$sort = getRequestParam('sort_data', 'latest');

// NEW: Filter type for "For you", "Remote", "Part time"
$filter_type = trim((string) getRequestParam('filter_type', ''));
$user_id = trim((string) getRequestParam('user_id', '')); // For "for_you" filter and has_applied flag

// Pagination params with default limit = 10
$per_page = intval(getRequestParam('limit', getRequestParam('per_page', getRequestParam('par_page', 10))));
$page     = intval(getRequestParam('page', 1));
$page     = max($page, 1);
$per_page = max($per_page, 1);
$offset   = ($page - 1) * $per_page;

// Base Query with necessary joins and conditions
$sql_base = "FROM eq_jobs j
    LEFT JOIN states s ON s.id = j.job_state
    LEFT JOIN industrys i ON i.id = j.industry
<<<<<<< HEAD
    LEFT JOIN users u ON u.id = j.owner
=======
    LEFT JOIN new_users u ON u.id = j.owner
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    WHERE j.deleted_at IS NULL 
    AND j.job_type = 'regular'";

$params = [];

// Apply filters
if ($id !== '') {
    $sql_base .= " AND j.id = ?";
    $params[] = $id;
}

// Title filter includes title OR owner full name match
if ($title !== '') {
    $sql_base .= " AND (j.title LIKE ? OR CONCAT(u.first_name, ' ', u.last_name) LIKE ?)";
    $params[] = "%{$title}%";
    $params[] = "%{$title}%";
}

if ($firm !== '') {
    $sql_base .= " AND j.firm = ?";
    $params[] = $firm;
}

if (!empty($industries_for_search)) {
    $placeholders = implode(',', array_fill(0, count($industries_for_search), '?'));
    $sql_base .= " AND j.industry IN ($placeholders)";
    foreach ($industries_for_search as $ind) $params[] = $ind;
}

if (!empty($practice_areas_for_search)) {
    $placeholders = implode(',', array_fill(0, count($practice_areas_for_search), '?'));
    $sql_base .= " AND j.practice_area IN ($placeholders)";
    foreach ($practice_areas_for_search as $pa) $params[] = $pa;
}

if (!empty($specialities_for_search)) {
    $placeholders = implode(',', array_fill(0, count($specialities_for_search), '?'));
    $sql_base .= " AND j.speciality IN ($placeholders)";
    foreach ($specialities_for_search as $sp) $params[] = $sp;
}

if ($job_city !== '') {
    $sql_base .= " AND j.job_city LIKE ?";
    $params[] = "%{$job_city}%";
}

// For state filters, check both possible params
if ($job_state !== '') {
    $sql_base .= " AND j.job_state = ?";
    $params[] = $job_state;
} elseif ($state_for_search !== '') {
    $sql_base .= " AND j.job_state = ?";
    $params[] = $state_for_search;
}

// bar_for_search filter uses JSON_CONTAINS on user.bar
if ($bar_for_search !== '') {
    $sql_base .= " AND JSON_CONTAINS(u.bar, JSON_QUOTE(?))";
    $params[] = $bar_for_search;
}

// Date posted range filter
if ($posted_from !== '' && $posted_to !== '') {
    $sql_base .= " AND j.posted_date BETWEEN ? AND ?";
    $params[] = date("Y-m-d 00:00:00", strtotime($posted_from));
    $params[] = date("Y-m-d 23:59:59", strtotime($posted_to));
}

// Tag filter: monthly, week, today for created_at date
if ($tag !== '') {
    switch (strtolower($tag)) {
        case 'monthly':
            $sql_base .= " AND j.created_at BETWEEN ? AND ?";
            $params[] = date("Y-m-01 00:00:00");
            $params[] = date("Y-m-t 23:59:59");
            break;
        case 'week':
            $sql_base .= " AND j.created_at BETWEEN ? AND ?";
            $params[] = date("Y-m-d 00:00:00", strtotime('monday this week'));
            $params[] = date("Y-m-d 23:59:59", strtotime('sunday this week'));
            break;
        case 'today':
            $sql_base .= " AND DATE(j.created_at) = ?";
            $params[] = date("Y-m-d");
            break;
    }
}

// NEW: Apply filter_type filters
if ($filter_type !== '') {
    switch (strtolower($filter_type)) {
        case 'for_you':
            // Match jobs based on user's industry/practice areas (requires user_id)
            if ($user_id !== '' && is_numeric($user_id)) {
                try {
<<<<<<< HEAD
                    $userStmt = $conn->prepare("SELECT industry, practice_area, speciality FROM users WHERE id = ? LIMIT 1");
=======
                    $userStmt = $conn->prepare("SELECT industry, practice_area, speciality FROM new_users WHERE id = ? LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
                    $userStmt->execute([$user_id]);
                    $userInfo = $userStmt->fetch(PDO::FETCH_ASSOC);
                    
                    if ($userInfo) {
                        $conditions = [];
                        if (!empty($userInfo['industry'])) {
                            $conditions[] = "j.industry = ?";
                            $params[] = $userInfo['industry'];
                        }
                        if (!empty($userInfo['practice_area'])) {
                            $conditions[] = "j.practice_area = ?";
                            $params[] = $userInfo['practice_area'];
                        }
                        if (!empty($userInfo['speciality'])) {
                            $conditions[] = "j.speciality = ?";
                            $params[] = $userInfo['speciality'];
                        }
                        
                        if (!empty($conditions)) {
                            $sql_base .= " AND (" . implode(" OR ", $conditions) . ")";
                        }
                    }
                } catch (PDOException $e) {
                    error_log("For you filter error: " . $e->getMessage());
                }
            }
            break;
            
        case 'remote':
            // Check for is_remote flag or search in descriptions
<<<<<<< HEAD
            $sql_base .= " AND (j.is_remote = 1 OR j.descriptions LIKE ?)";
=======
            // Note: is_remote will be added by migration, using a safe check or OR condition
            $sql_base .= " AND (j.descriptions LIKE ?)";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            $params[] = "%remote%";
            break;
            
        case 'part_time':
            // Check job_type field or search in descriptions
            $sql_base .= " AND (j.job_type LIKE ? OR j.descriptions LIKE ?)";
            $params[] = "%part%";
            $params[] = "%part time%";
            break;
    }
}

// Count query for pagination
try {
    $count_sql = "SELECT COUNT(*) as total " . $sql_base;
    $count_stmt = $conn->prepare($count_sql);
    $count_stmt->execute($params);
    $total = (int) $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    echo json_encode(["status" => false, "message" => "Error fetching count: " . $e->getMessage()]);
    exit();
}

// Sorting options - fixed to use posted_date for date sorting
$sortOptions = [
    'latest'     => "j.posted_date DESC",
    'oldest'     => "j.posted_date ASC",
    'ascending'  => "j.title ASC",
    'descending' => "j.title DESC"
];
$orderBy = isset($sortOptions[strtolower(trim($sort))]) ? $sortOptions[strtolower(trim($sort))] : $sortOptions['latest'];

<<<<<<< HEAD
// Debug log (remove in production)
error_log("OrderBy: " . $orderBy);

=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// Final query with limit and offset
$sql = "SELECT j.*, s.name AS state_name, i.title AS industry_name, 
        CONCAT(u.first_name, ' ', u.last_name) AS owner_name, 
        u.email AS owner_email, 
        u.image AS owner_image_filename
        " . $sql_base . " 
        ORDER BY $orderBy 
        LIMIT " . intval($per_page) . " OFFSET " . intval($offset);

try {
    $stmt = $conn->prepare($sql);
    foreach ($params as $i => $val) {
        $stmt->bindValue($i + 1, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }
    $stmt->execute();
    $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Append full image URL if image filename exists
    foreach ($jobs as &$job) {
        if (!empty($job['owner_image_filename'])) {
            $job['owner_image'] = $GLOBALS['jobimagepath'] . $job['owner_image_filename'];
        } else {
<<<<<<< HEAD
            $job['owner_image'] = null; // or default image URL if you want
        }
        unset($job['owner_image_filename']); // remove raw filename if you want
=======
            $job['owner_image'] = null;
        }
        unset($job['owner_image_filename']);
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        
        // NEW: Add has_applied flag if user_id is provided
        $job['has_applied'] = false;
        if ($user_id !== '' && is_numeric($user_id)) {
            try {
<<<<<<< HEAD
=======
                // job_applications will be added by migration
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
                $appliedCheck = $conn->prepare("SELECT id FROM job_applications WHERE applicant_id = ? AND job_id = ? LIMIT 1");
                $appliedCheck->execute([$user_id, $job['id']]);
                $job['has_applied'] = $appliedCheck->rowCount() > 0;
            } catch (PDOException $e) {
<<<<<<< HEAD
                error_log("Has applied check error: " . $e->getMessage());
=======
                // Still false if table doesn't exist yet
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            }
        }
    }
    unset($job);

    echo json_encode([
        "status"      => true,
        "message"     => "Jobs fetched successfully",
        "data"        => $jobs,
        "page"        => $page,
        "per_page"    => $per_page,
        "total"       => $total,
        "total_pages" => ceil($total / $per_page)
    ]);
} catch (PDOException $e) {
    echo json_encode(["status" => false, "message" => "Error fetching jobs: " . $e->getMessage()]);
}
<<<<<<< HEAD
=======

require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';

include 'connection.php';
header('Content-Type: application/json');


$parPage = isset($_GET['par_page']) ? intval($_GET['par_page']) : 10;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $parPage;


function getPaginationData($conn, $countSql, $countParams = [], $countTypes = "", $parPage) {
    $countStmt = mysqli_prepare($conn, $countSql);
    if (!$countStmt) {
        return ['total' => 0, 'error' => 'Prepare failed (count): ' . mysqli_error($conn)];
    }

    if (!empty($countParams)) {
        $bind_names = [];
        $bind_names[] = $countTypes;
        for ($i = 0; $i < count($countParams); $i++) {
            $bind_name = 'bind' . $i;
            $$bind_name = $countParams[$i];
            $bind_names[] = &$$bind_name;
        }
        call_user_func_array([$countStmt, 'bind_param'], $bind_names);
    }

    mysqli_stmt_execute($countStmt);
    mysqli_stmt_bind_result($countStmt, $total);
    mysqli_stmt_fetch($countStmt);
    mysqli_stmt_close($countStmt);

    $totalPages = $parPage > 0 ? ceil($total / $parPage) : 1;
    $noOfPages = range(1, $totalPages);

    return ['total' => $total, 'totalPages' => $totalPages, 'noOfPages' => $noOfPages];
}


$stateId = isset($_GET['state_for_search']) ? intval($_GET['state_for_search']) : 0;

if ($stateId > 0) {
    $countSql = "SELECT COUNT(*) FROM eq_jobs WHERE job_state = ?";
    $pagination = getPaginationData($conn, $countSql, [$stateId], "i", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*, states.name AS state_name
        FROM eq_jobs
        INNER JOIN states ON eq_jobs.job_state = states.id
        WHERE eq_jobs.job_state = ?
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (state): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iii", $stateId, $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'state_for_search',
        'state_id' => $stateId,
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


$industryId = isset($_GET['industry_for_search']) ? intval($_GET['industry_for_search']) : 0;

if ($industryId > 0) {
    $countSql = "SELECT COUNT(*) FROM eq_jobs WHERE industry = ?";
    $pagination = getPaginationData($conn, $countSql, [$industryId], "i", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*, industrys.title AS industry_name
        FROM eq_jobs
        INNER JOIN industrys ON eq_jobs.industry = industrys.id
        WHERE eq_jobs.industry = ?
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (industry): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iii", $industryId, $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'industry_for_search',
        'industry_id' => $industryId,
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


$bar = isset($_GET['bar_for_search']) ? trim($_GET['bar_for_search']) : '';

if (!empty($bar)) {
    $escapedBar = mysqli_real_escape_string($conn, $bar);

    $countSql = "SELECT COUNT(*) FROM eq_jobs INNER JOIN users ON users.id = eq_jobs.owner WHERE JSON_CONTAINS(users.bar, '\"$escapedBar\"')";
    $pagination = getPaginationData($conn, $countSql, [], "", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*
        FROM eq_jobs
        INNER JOIN users ON users.id = eq_jobs.owner
        WHERE JSON_CONTAINS(users.bar, '\"$escapedBar\"')
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (bar): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ii", $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'bar_for_search',
        'bar' => $bar,
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


$practiceArea = isset($_GET['practice_area_for_search']) ? trim($_GET['practice_area_for_search']) : '';

if (!empty($practiceArea)) {
    $countSql = "SELECT COUNT(*) FROM eq_jobs WHERE practice_area = ?";
    $pagination = getPaginationData($conn, $countSql, [$practiceArea], "s", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*
        FROM eq_jobs
        WHERE practice_area = ?
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (practice area): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sii", $practiceArea, $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'practice_area_for_search',
        'practice_area' => $practiceArea,
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


$speciality = isset($_GET['speciality_for_search']) ? trim($_GET['speciality_for_search']) : '';

if (!empty($speciality)) {
    $countSql = "SELECT COUNT(*) FROM eq_jobs WHERE speciality = ?";
    $pagination = getPaginationData($conn, $countSql, [$speciality], "s", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*
        FROM eq_jobs
        WHERE speciality = ?
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (speciality): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "sii", $speciality, $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'speciality_for_search',
        'speciality' => $speciality,
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


if (
    $stateId === 0 &&
    $industryId === 0 &&
    empty($bar) &&
    empty($practiceArea) &&
    empty($speciality)
) {
    $countSql = "SELECT COUNT(*) FROM eq_jobs";
    $pagination = getPaginationData($conn, $countSql, [], "", $parPage);
    if (isset($pagination['error'])) {
        echo json_encode(['success' => false, 'message' => $pagination['error'], 'data' => []]);
        exit;
    }

    $sql = "
        SELECT eq_jobs.*, states.name AS state_name, industrys.title AS industry_name
        FROM eq_jobs
        LEFT JOIN states ON eq_jobs.job_state = states.id
        LEFT JOIN industrys ON eq_jobs.industry = industrys.id
        LIMIT ? OFFSET ?
    ";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed (fetch all): ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "ii", $parPage, $offset);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $jobs = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $jobs[] = $row;
    }

    echo json_encode([
        'success' => count($jobs) > 0,
        'filter' => 'none',
        'total_jobs' => (int)$pagination['total'],
        'current_page' => $page,
        'par_page' => $parPage,
        'total_pages' => $pagination['totalPages'],
        'no_of_pages' => $pagination['noOfPages'],
        'data' => $jobs
    ]);
    mysqli_stmt_close($stmt);
    exit;
}


echo json_encode([
    'success' => false,
    'message' => 'No valid filter provided.',
    'data' => []
]);
exit;
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======

>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
