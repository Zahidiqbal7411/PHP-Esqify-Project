<?php
require_once 'connection.php'; // provides $conn (PDO) and $GLOBALS['dealimage']
header('Content-Type: application/json');

// ✅ Get user_id from POST (replace session auth)
$userId = $_POST['user_id'] ?? null;
$commentId = $_POST['comment_id'] ?? null;

if (!$userId || !$commentId) {
    echo json_encode(['success' => false, 'message' => 'User ID and Comment ID are required']);
    exit;
}

try {
    // ✅ Check comment exists
    $stmt = $conn->prepare("SELECT * FROM feed_comments WHERE id = ?");
    $stmt->execute([$commentId]);
    $comment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$comment) {
        echo json_encode(['success' => false, 'message' => 'Comment not found']);
        exit;
    }

    // ✅ Check if user already liked
    $stmt = $conn->prepare("SELECT id FROM comment_likes WHERE comment_id = ? AND user_id = ?");
    $stmt->execute([$commentId, $userId]);
    $like = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($like) {
        // Unlike
        $stmt = $conn->prepare("DELETE FROM comment_likes WHERE id = ?");
        $stmt->execute([$like['id']]);

        echo json_encode(['success' => true, 'status' => 'unliked']);
    } else {
        // Like
        $stmt = $conn->prepare("INSERT INTO comment_likes (comment_id, user_id, created_at) VALUES (?, ?, NOW())");
        $stmt->execute([$commentId, $userId]);

        // ✅ Send notification if the comment owner is not the liker
        if ($comment['user_id'] != $userId) {
<<<<<<< HEAD
            $stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
=======
            // Original: $stmt = $conn->prepare("SELECT first_name FROM new_users WHERE id = ?");
            $stmt = $conn->prepare("SELECT first_name FROM new_users WHERE id = ?"); // Changed 'users' to 'new_users'
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstName = $user['first_name'] ?? 'User';

            $notif = $conn->prepare("INSERT INTO notifications (user_id, title, message, type, created_at)
                                     VALUES (?, ?, ?, ?, NOW())");
            $notif->execute([
                $comment['user_id'],
                'Someone liked your comment',
                "$firstName liked your comment.",
                'like'
            ]);
        }

        echo json_encode(['success' => true, 'status' => 'liked']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
