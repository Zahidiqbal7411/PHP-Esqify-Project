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

// Required: user_id
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;
if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required parameter: user_id"
    ]);
    exit();
}

// Optional filter: 'following' or 'political'
$filter = isset($_GET['filter']) ? $_GET['filter'] : null;

try {
    $sql = "
<<<<<<< HEAD
        SELECT 
            id,
            title,
            descriptions,
            tags,
            photos,
            owner,
            posted_date,
            status,
            political_post,
            is_shared,
            shared_from_id,
            created_at,
            updated_at,
            deleted_at
        FROM feeds
        WHERE status = 'active' AND deleted_at IS NULL
    ";

    $params = [];

    if ($filter === 'following') {
        // Fixed: Actually check follows table for users the current user follows
        $sql .= " AND owner IN (SELECT followed_id FROM follows WHERE follower_id = ?)";
        $params[] = $user_id;
        $sql .= " AND political_post = 0";
=======
        SELECT
            f.id,
            f.title,
            f.descriptions,
            f.tags,
            f.photos,
            f.owner,
            f.posted_date,
            f.status,
            f.political_post,
            f.is_shared,
            f.shared_from_id,
            f.created_at,
            f.updated_at,
            f.deleted_at,
            CASE WHEN fl.feed_id IS NOT NULL THEN TRUE ELSE FALSE END AS is_liked
        FROM feeds f
        INNER JOIN new_users u ON u.id = f.owner
        LEFT JOIN feed_likes fl ON fl.feed_id = f.id AND fl.user_id = :user_id
        WHERE f.status = 'active' AND f.deleted_at IS NULL
    ";

    $params = [
        'user_id' => $user_id
    ];

    if ($filter === 'following') {
        // Fixed: Actually check follows table for users the current user follows
        $sql .= " AND f.owner IN (SELECT followed_id FROM follows WHERE follower_id = :follower_id)";
        $params['follower_id'] = $user_id;
        $sql .= " AND f.political_post = 0";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    }

    if ($filter === 'political') {
        // Only posts where political_post = 1
<<<<<<< HEAD
        $sql .= " AND political_post = 1";
    }

    // Order by newest first
    $sql .= " ORDER BY posted_date DESC";
=======
        $sql .= " AND f.political_post = 1";
    }

    // Order by newest first
    $sql .= " ORDER BY f.posted_date DESC";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Add leaderboard position (top 100 only)
    foreach ($feeds as &$feed) {
        $stmtRank = $conn->prepare("
            SELECT rank_position 
            FROM leaderboard 
            WHERE user_id = ? AND rank_position <= 100
        ");
        $stmtRank->execute([$feed['owner']]);
        $rank = $stmtRank->fetch(PDO::FETCH_ASSOC);
        $feed['leaderboard_position'] = $rank['rank_position'] ?? null;
    }

    echo json_encode([
        "status" => true,
        "message" => "Feeds fetched successfully.",
        "data" => $feeds
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
<<<<<<< HEAD
=======
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
