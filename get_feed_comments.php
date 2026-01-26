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

// Get feed_id from POST
$feed_id = isset($_POST['feed_id']) ? intval($_POST['feed_id']) : 0;

if (!$feed_id) {
    echo json_encode([
        "status" => false,
        "message" => "feed_id is required"
    ]);
    exit();
}

// Pagination parameters
$per_page = max(1, (int)($_POST['per_page'] ?? 20));
$page = max(1, (int)($_POST['page'] ?? 1));
$offset = ($page - 1) * $per_page;

try {
    // Check if feed exists
    $feedCheck = $conn->prepare("SELECT id FROM feeds WHERE id = ? LIMIT 1");
    $feedCheck->execute([$feed_id]);
    if ($feedCheck->rowCount() === 0) {
        echo json_encode([
            "status" => false,
            "message" => "Feed not found"
        ]);
        exit();
    }

    // Count total comments
    $countStmt = $conn->prepare("SELECT COUNT(*) as total FROM feed_comments WHERE feed_id = ?");
    $countStmt->execute([$feed_id]);
    $total = (int)$countStmt->fetchColumn();

    // Fetch comments with user info
    $sql = "
        SELECT 
            fc.id,
            fc.feed_id,
            fc.user_id,
            fc.comment,
            fc.image_path,
            fc.created_at,
            fc.updated_at,
            u.first_name,
            u.last_name,
            u.image AS user_image
        FROM feed_comments fc
<<<<<<< HEAD
        LEFT JOIN users u ON u.id = fc.user_id
        WHERE fc.feed_id = ?
=======
        LEFT JOIN new_users u ON fc.user_id = u.id
        WHERE fc.feed_id = :feed_id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        ORDER BY fc.created_at DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
<<<<<<< HEAD
    $stmt->bindValue(1, $feed_id, PDO::PARAM_INT);
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add full URLs for images
    foreach ($comments as &$comment) {
        $comment['image_url'] = !empty($comment['image_path']) 
            ? $GLOBALS['comment_images'] . $comment['image_path'] 
            : null;
        $comment['user_image_url'] = !empty($comment['user_image']) 
            ? $GLOBALS['jobimagepath'] . $comment['user_image'] 
            : null;
        unset($comment['image_path'], $comment['user_image']);
    }
    unset($comment);

    echo json_encode([
        "status" => true,
        "message" => "Comments fetched successfully.",
        "page" => $page,
        "per_page" => $per_page,
        "total" => $total,
        "total_pages" => ceil($total / $per_page),
        "data" => $comments
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Get feed comments error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
