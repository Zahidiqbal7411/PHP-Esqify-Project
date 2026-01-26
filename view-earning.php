<?php
header('Content-Type: application/json');
require_once 'connection.php'; // sets up $conn

$response = [
    'status' => false,
    'message' => '',
    'earnings' => [],
    'data' => null
];

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Only POST requests allowed.';
    echo json_encode($response);
    exit;
}

// ✅ Get user_id from POST (required)
$user_id = isset($_POST['user_id']) && is_numeric($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
if ($user_id <= 0) {
    $response['message'] = 'Invalid or missing user_id.';
    echo json_encode($response);
    exit;
}

// Optional: sorting
$sort_data = $_POST['sort_data'] ?? 'latest';
$sortOptions = [
    'latest'     => ['column' => 'id', 'direction' => 'DESC'],
    'oldest'     => ['column' => 'id', 'direction' => 'ASC'],
    'ascending'  => ['column' => 'title', 'direction' => 'ASC'],
    'descending' => ['column' => 'title', 'direction' => 'DESC']
];
$sortColumn = $sortOptions[$sort_data]['column'] ?? 'id';
$sortDir    = $sortOptions[$sort_data]['direction'] ?? 'DESC';

try {
    // 1. Get all earnings
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT * FROM user_earnings WHERE user_id = :user_id ORDER BY $sortColumn $sortDir");
=======
    $stmt = $conn->prepare("SELECT * FROM user_earnings e
    JOIN new_users u ON e.user_id = u.id
WHERE e.user_id = :user_id ORDER BY $sortColumn $sortDir");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([':user_id' => $user_id]);
    $earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2. Get total earnings
    $stmt = $conn->prepare("SELECT SUM(amount) FROM user_earnings WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $total_earnings = (float) $stmt->fetchColumn();

    // 3. Get total withdrawals
    $stmt = $conn->prepare("SELECT SUM(amount) FROM withdrawals WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $user_id]);
    $total_withdraw = (float) $stmt->fetchColumn();

    // 4. Get last completed withdrawal date
    $stmt = $conn->prepare("
        SELECT created_at 
        FROM withdrawals 
        WHERE user_id = :user_id AND status = 'completed' 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $user_id]);
    $last_withdraw = $stmt->fetchColumn() ?: false;

    // 5. Get current user balance
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT balance FROM users WHERE id = :user_id");
=======
    $stmt = $conn->prepare("SELECT balance FROM new_users WHERE id = :user_id");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([':user_id' => $user_id]);
    $current_balance = (float) ($stmt->fetchColumn() ?? 0);

    // Build response
    $response['status'] = true;
    $response['message'] = 'Earnings data fetched successfully.';
    $response['earnings'] = $earnings;
    $response['data'] = [
        'total_earnings'   => $total_earnings,
        'total_withdraw'   => $total_withdraw,
        'last_withdraw_on' => $last_withdraw,
        'current_balance'  => $current_balance
    ];

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
