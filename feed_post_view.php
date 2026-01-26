<?php
require_once 'connection.php'; // must set $conn (PDO)
header('Content-Type: application/json');

// ✅ Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

// ✅ Get user_id from POST
$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => false, 'message' => 'user_id is required']);
    exit;
}

// ✅ Fetch current user
<<<<<<< HEAD
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id AND deleted_at IS NULL");
=======
$stmt = $conn->prepare("SELECT * FROM new_users WHERE id = :id AND deleted_at IS NULL");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) {
    echo json_encode(['status' => false, 'message' => 'User not found']);
    exit;
}

/**
 * ======================
 *  LEADERBOARD USERS
 * ======================
 */
$stmt = $conn->prepare("
    SELECT u.*, COUNT(d.id) AS deals_count
<<<<<<< HEAD
    FROM users u
=======
    FROM new_users u
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    INNER JOIN deals d ON d.owner = u.id AND d.deleted_at IS NULL
    WHERE u.role_id = 3 
      AND u.id <> :uid 
      AND u.deleted_at IS NULL
    GROUP BY u.id
    ORDER BY deals_count DESC
    LIMIT 6
");
$stmt->execute([':uid' => $userId]);
$leaderboards = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($leaderboards as &$lb) {
    if (!empty($lb['image'])) {
        $lb['image'] = $GLOBALS['dealownerimagepath'] . $lb['image'];
    }
    if (!empty($lb['resume'])) {
        $lb['resume'] = $GLOBALS['dealownerimagepath'] . $lb['resume'];
    }
}

/**
 * ======================
 *  LATEST 3 UNIQUE DEALS
 * ======================
 */
$stmt = $conn->prepare("
    SELECT d.*, u.first_name, u.last_name, u.image AS owner_image, u.resume AS owner_resume
<<<<<<< HEAD
    FROM deals d
    INNER JOIN users u ON d.owner = u.id
=======
    FROM deals
    INNER JOIN new_users u ON d.owner = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    WHERE d.deleted_at IS NULL
    ORDER BY d.created_at DESC
");
$stmt->execute();
$allDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

$uniqueOwners = [];
$deals = [];
foreach ($allDeals as $deal) {
    if (!in_array($deal['owner'], $uniqueOwners)) {
        $uniqueOwners[] = $deal['owner'];

        if (!empty($deal['owner_image'])) {
            $deal['owner_image'] = $GLOBALS['dealownerimagepath'] . $deal['owner_image'];
        }
        if (!empty($deal['owner_resume'])) {
            $deal['owner_resume'] = $GLOBALS['dealownerimagepath'] . $deal['owner_resume'];
        }
        if (!empty($deal['image'])) {
            $deal['image'] = $GLOBALS['dealownerimagepath'] . $deal['image'];
        }
        if (!empty($deal['resume'])) {
            $deal['resume'] = $GLOBALS['dealownerimagepath'] . $deal['resume'];
        }

        // ✅ Handle deal photos if JSON array exists
        if (!empty($deal['photos'])) {
            $photos = json_decode($deal['photos'], true);
            if (is_array($photos)) {
                $photosWithPath = [];
                foreach ($photos as $p) {
                    $photosWithPath[] = $GLOBALS['dealownerimagepath'] . $p;
                }
                $deal['photos'] = $photosWithPath;
            }
        }

        $deals[] = $deal;
    }
    if (count($deals) >= 3) break;
}

/**
 * ======================
 *  FEEDS QUERY
 * ======================
 */
$sql = "
    SELECT f.*,
           (SELECT COUNT(*) FROM feed_likes l WHERE l.feed_id = f.id) AS likes_count,
           (SELECT COUNT(*) FROM feed_comments c WHERE c.feed_id = f.id) AS comments_count,
           (SELECT COUNT(*) FROM feed_likes l2 WHERE l2.feed_id = f.id AND l2.user_id = :uid) AS liked_by_auth_user,
           u.first_name AS owner_first_name,
           u.last_name AS owner_last_name,
           u.image AS owner_image,
           u.resume AS owner_resume
    FROM feeds f
<<<<<<< HEAD
    INNER JOIN users u ON f.owner = u.id
=======
    INNER JOIN new_users u ON f.owner = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    WHERE f.deleted_at IS NULL
      AND NOT EXISTS (
        SELECT 1 FROM feed_not_interested ni WHERE ni.feed_id = f.id AND ni.user_id = :uid
      )
";

$params = [':uid' => $userId];

// ✅ Pagination (default for non-AJAX)
$perPage = isset($_POST['per_feed_record']) ? (int)$_POST['per_feed_record'] : 7;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $perPage;

$sql .= " ORDER BY f.id DESC LIMIT :offset, :limit";

$stmt = $conn->prepare($sql);
$stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
$stmt->execute();
$feeds = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($feeds as &$feed) {
    if (!isset($feed['title']) || trim($feed['title']) === '') {
        $feed['title'] = '(No Title)';
    }

    // ✅ Owner image
    if (!empty($feed['owner_image'])) {
        $feed['owner_image'] = $GLOBALS['dealownerimagepath'] . $feed['owner_image'];
    }

    // ✅ Owner resume
    if (!empty($feed['owner_resume'])) {
        $feed['owner_resume'] = $GLOBALS['dealownerimagepath'] . $feed['owner_resume'];
    }

    // ✅ Feed image / photo
    if (!empty($feed['image'])) {
        $feed['image'] = $GLOBALS['dealownerimagepath'] . $feed['image'];
    }
    if (!empty($feed['photo'])) {
        $feed['photo'] = $GLOBALS['dealownerimagepath'] . $feed['photo'];
    }

    // ✅ Feed photos (JSON array)
    if (!empty($feed['photos'])) {
        $photos = json_decode($feed['photos'], true);
        if (is_array($photos)) {
            $photosWithPath = [];
            foreach ($photos as $p) {
                $photosWithPath[] = $GLOBALS['dealimage'] . $p;
            }
            $feed['photos'] = $photosWithPath;
        }
    }
}

echo json_encode([
    'status'       => count($feeds) > 0,
    'feeds'        => $feeds,
    'leaderboards' => $leaderboards,
    'deals'        => $deals,
    'page'         => $page,
    'per_page'     => $perPage,
    'input'        => $_POST
]);
