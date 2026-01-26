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

// Get user_id from POST
$user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;

if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit();
}

// Pagination parameters
$per_page = max(1, (int)($_POST['per_page'] ?? 20));
$page = max(1, (int)($_POST['page'] ?? 1));
$offset = ($page - 1) * $per_page;

try {
    // Count total following
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM follows WHERE follower_id = ?");
    $countStmt->execute([$user_id]);
    $total = (int)$countStmt->fetchColumn();

    // Fetch following with user info
    $sql = "
        SELECT 
            u.id,
            u.first_name,
            u.last_name,
            u.email,
            u.username,
            u.image,
            u.law_firm,
            u.city,
            u.state_province,
            f.created_at AS followed_at
        FROM follows f
<<<<<<< HEAD
        INNER JOIN users u ON u.id = f.followed_id
        WHERE f.follower_id = ?
        ORDER BY f.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
=======
        JOIN new_users u ON f.followed_id = u.id
        WHERE f.follower_id = :user_id
        ORDER BY f.created_at DESC
        LIMIT :limit OFFSET :offset
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute();
    $following = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add full URLs for images
    foreach ($following as &$user) {
        $user['image_url'] = !empty($user['image']) 
            ? $GLOBALS['jobimagepath'] . $user['image'] 
            : null;
        unset($user['image']);
    }
    unset($user);

    echo json_encode([
        "status" => true,
        "message" => "Following list fetched successfully.",
        "page" => $page,
        "per_page" => $per_page,
        "total" => $total,
        "total_pages" => ceil($total / $per_page),
        "data" => $following
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Get following list error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
