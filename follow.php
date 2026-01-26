<?php
header('Content-Type: application/json');
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
require_once 'connection.php'; // this provides $conn and addNotification()

$response = ['status' => false, 'message' => '', 'data' => null];

<<<<<<< HEAD
// ✅ Input
=======

$response = ['status' => false, 'message' => '', 'data' => null];

>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
$follower_id = $_POST['follower_id'] ?? null;
$followed_id = $_POST['followed_id'] ?? null;

if (!$follower_id || !$followed_id) {
    $response['message'] = 'Both follower_id and followed_id are required.';
    echo json_encode($response);
    exit;
}

if ($follower_id == $followed_id) {
    $response['message'] = 'You cannot follow yourself.';
    echo json_encode($response);
    exit;
}

try {
<<<<<<< HEAD
<<<<<<< HEAD
    // ✅ Insert follow relationship (ignore if already exists)
    $stmt = $conn->prepare("
=======
    require_once 'connection.php'; // your PDO $pdo

    // Insert follow relationship with created_at and updated_at timestamps
    // Use NOW() for current timestamp
    $stmt = $pdo->prepare("
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
    // ✅ Insert follow relationship (ignore if already exists)
    $stmt = $conn->prepare("
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        INSERT IGNORE INTO follows (follower_id, followed_id, created_at, updated_at) 
        VALUES (?, ?, NOW(), NOW())
    ");
    $stmt->execute([$follower_id, $followed_id]);

<<<<<<< HEAD
<<<<<<< HEAD
    // ✅ Fetch follower details (like Laravel auth()->user())
    $stmt = $conn->prepare("SELECT id, first_name FROM users WHERE id = ? LIMIT 1");
=======
    // ✅ Fetch follower details
    $stmt = $conn->prepare("SELECT id, first_name FROM new_users WHERE id = ? LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$follower_id]);
    $followerUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($followerUser && $follower_id != $followed_id) {
        // ✅ Call global addNotification()
        addNotification(
            $followed_id,                                   // user_id (receiver)
            'Someone Followed you',                        // title
            $followerUser['first_name'] . ' followed you.',// message
            'follow',                                      // type
            $follower_id,                                  // notification_from
            'App\\Models\\User',                           // related_class
            $follower_id                                   // class_id
        );
    }

<<<<<<< HEAD
=======
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $response['status'] = true;
    $response['message'] = 'Followed successfully.';
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

<<<<<<< HEAD
<<<<<<< HEAD
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
=======
echo json_encode($response);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
