<?php
require_once 'connection.php'; // loads $conn and globals
header('Content-Type: application/json');

// ✅ Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// ✅ Get inputs
$feed_id = $_POST['feed_id'] ?? null;
$user_id = $_POST['user_id'] ?? null;
$comment = $_POST['comment'] ?? null;

// ✅ Validation
if (!$feed_id || !$user_id) {
    echo json_encode(['success' => false, 'message' => 'feed_id and user_id are required']);
    exit;
}

<<<<<<< HEAD
=======
// ✅ Check if user exists in new_users table
$userCheck = $conn->prepare("SELECT id FROM new_users WHERE id = ?");
$userCheck->execute([$user_id]);
$existingUser = $userCheck->fetch(PDO::FETCH_ASSOC);

if (!$existingUser) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// ✅ Check feed exists
$stmt = $conn->prepare("SELECT * FROM feeds WHERE id = ?");
$stmt->execute([$feed_id]);
$feed = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$feed) {
    echo json_encode(['success' => false, 'message' => 'Feed not found']);
    exit;
}

// ✅ Handle file upload (comment image)
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $uploadDir = __DIR__ . '/../public/uploads/comment-images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
    $filename = time() . '_' . substr(md5(uniqid()), 0, 10) . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetPath)) {
        $imagePath = $filename; // only filename stored in DB
    }
}

// ✅ Insert comment
$stmt = $conn->prepare("
    INSERT INTO feed_comments (feed_id, user_id, comment, image_path, created_at, updated_at)
    VALUES (?, ?, ?, ?, NOW(), NOW())
");
$stmt->execute([$feed_id, $user_id, $comment, $imagePath]);
$commentId = $conn->lastInsertId();

// ✅ Prepare response with full image URL
$responseComment = [
    'id' => $commentId,
    'feed_id' => $feed_id,
    'user_id' => $user_id,
    'comment' => $comment,
    'image_url' => $imagePath ? $GLOBALS['comment_images'] . $imagePath : null,
    'created_at' => date('Y-m-d H:i:s'),
];

// ✅ Add notification if commenter is not the feed owner
if ($feed['owner'] != $user_id) {
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
=======
    $stmt = $conn->prepare("SELECT first_name FROM new_users WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    addNotification(
        $feed['owner'],
        'Someone commented on your post',
        ($user['first_name'] ?? 'Someone') . ' commented on your post.',
        'comment',
        $user_id,
        'Feed',
        $feed_id
    );
}

// ✅ Final response
echo json_encode(['success' => true, 'data' => $responseComment]);
