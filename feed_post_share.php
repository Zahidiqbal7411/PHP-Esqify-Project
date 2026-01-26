<?php
require_once 'connection.php'; // provides $conn and addNotification()
header('Content-Type: application/json');
date_default_timezone_set('Asia/Karachi'); // adjust your timezone

// ✅ Simulate auth via POST
$userId = $_POST['user_id'] ?? null;
$shareHeading = $_POST['share_post_heading'] ?? '';
$feedId = $_POST['feed_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized: user_id required']);
    exit;
}

if (!$feedId) {
    echo json_encode(['success' => false, 'message' => 'feed_id is required']);
    exit;
}

try {
    // ✅ Find original feed
    $stmt = $conn->prepare("SELECT * FROM feeds WHERE id = ?");
    $stmt->execute([$feedId]);
    $originalFeed = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$originalFeed) {
        echo json_encode(['success' => false, 'message' => 'Original feed not found']);
        exit;
    }

    // ✅ Get sharer's first name & username
<<<<<<< HEAD
    $userStmt = $conn->prepare("SELECT first_name, username FROM users WHERE id = ?");
=======
    $userStmt = $conn->prepare("SELECT first_name, username FROM new_users WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $userStmt->execute([$userId]);
    $userData = $userStmt->fetch(PDO::FETCH_ASSOC);
    $firstName = $userData['first_name'] ?? 'User';
    $username = $userData['username'] ?? '';

    // ✅ Insert shared feed
    $insert = $conn->prepare("
        INSERT INTO feeds (descriptions, posted_date, owner, is_shared, shared_from_id, created_at, updated_at)
        VALUES (?, NOW(), ?, 1, ?, NOW(), NOW())
    ");
    $insert->execute([$shareHeading, $userId, $feedId]);

    $newFeedId = $conn->lastInsertId();

    // ✅ Add notification if not self-share
    if ($originalFeed['owner'] != $userId) {
        $messageText = "$firstName (@$username) shared your feed post.";

        addNotification(
            $originalFeed['owner'],  // user_id to notify
            'Someone shared your post', // title
            $messageText,            // message
            'share',                  // type
            $userId,                  // notification_from
            'Feed',                   // related_class
            $feedId                   // class_id
        );
    }

    echo json_encode([
        'success' => true,
        'message' => 'Post shared successfully',
        'shared_feed_id' => $newFeedId
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to share post: ' . $e->getMessage()
    ]);
}
