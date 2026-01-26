<<<<<<< HEAD
<?php
<<<<<<< HEAD
require_once 'connection.php'; // ✅ loads $conn (PDO) and globals
header('Content-Type: application/json');

// ✅ Allow only POST
=======
session_start();
require_once 'connection.php';

// Use user_id from session or fallback to 1 temporarily
$user_id = $_SESSION['user_id'] ?? 1;

header('Content-Type: application/json');

// Only allow POST
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
header('Content-Type: application/json');
require_once 'connection.php'; // ✅ loads $conn (PDO) and globals

// ✅ Allow only POST
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => false, 'message' => 'Invalid request method']);
    exit;
}

<<<<<<< HEAD
<<<<<<< HEAD
// ✅ Get raw input (JSON support)
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);

// ✅ Merge JSON and $_POST
$sender_id    = $decodedJson['sender_id'] ?? $_POST['sender_id'] ?? null;
$receiver_id  = $decodedJson['receiver_id'] ?? $_POST['receiver_id'] ?? null;
$message_text = $decodedJson['message'] ?? $_POST['message'] ?? null;
=======
// ✅ Get input
$sender_id    = $_POST['sender_id'] ?? null;
$receiver_id  = $_POST['receiver_id'] ?? null;
$message_text = $_POST['message'] ?? null;
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

// ✅ Validate
if (!$sender_id || !$receiver_id || !$message_text) {
    echo json_encode(['status' => false, 'message' => 'sender_id, receiver_id and message are required']);
<<<<<<< HEAD
=======
// Get input
$receiver_id = $_POST['receiver_id'] ?? null;
$message_text = $_POST['message'] ?? null;

// Validate required fields
if (!$receiver_id || !$message_text) {
    echo json_encode(['status' => false, 'message' => 'receiver_id and message are required']);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    exit;
}

try {
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $conn->beginTransaction();

    // ✅ Check if chat exists (both directions)
    $stmt = $conn->prepare("
        SELECT * FROM chats
        WHERE (sender_id = :s1 AND receiver_id = :r1)
           OR (sender_id = :r2 AND receiver_id = :s2)
        LIMIT 1
    ");
    $stmt->execute([
        's1' => $sender_id, 
        'r1' => $receiver_id,
        'r2' => $receiver_id, 
        's2' => $sender_id
    ]);
    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        // ✅ Create new chat
        $stmt = $conn->prepare("
            INSERT INTO chats (sender_id, receiver_id, created_at) 
            VALUES (?, ?, NOW())
        ");
        $stmt->execute([$sender_id, $receiver_id]);
        $chat_id = $conn->lastInsertId();
<<<<<<< HEAD
=======
    $pdo->beginTransaction();

    // Check if chat exists
    $stmt = $pdo->prepare("
        SELECT * FROM chats
        WHERE (sender_id = :user AND receiver_id = :receiver)
           OR (sender_id = :receiver AND receiver_id = :user)
        LIMIT 1
    ");
    $stmt->execute(['user' => $user_id, 'receiver' => $receiver_id]);
    $chat = $stmt->fetch();

    if (!$chat) {
        // Create new chat
        $stmt = $pdo->prepare("INSERT INTO chats (sender_id, receiver_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $receiver_id]);
        $chat_id = $pdo->lastInsertId();
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    } else {
        $chat_id = $chat['id'];
    }

<<<<<<< HEAD
<<<<<<< HEAD
    // ✅ Handle file upload (file/image/video)
    $uploadDir = __DIR__ . '/../public/uploads/message-file/'; // go one folder up
=======
    // Handle file upload (optional)
    $uploadDir = __DIR__ . '/uploads/message-file/';
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
    // ✅ Handle file upload (file/image/video)
    $uploadDir = __DIR__ . '/../public/uploads/message-file/'; // go one folder up
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $file = $_FILES['file'] ?? $_FILES['image'] ?? $_FILES['video'] ?? null;
    $filename = null;
    $message_type = 'text';

    if ($file && is_uploaded_file($file['tmp_name'])) {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $mime = mime_content_type($file['tmp_name']);
        $filename = time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            if (strpos($mime, 'image') === 0) {
                $message_type = 'image';
            } elseif (strpos($mime, 'video') === 0) {
                $message_type = 'video';
            } else {
                $message_type = 'file';
            }
        }
    }

<<<<<<< HEAD
<<<<<<< HEAD
    // ✅ Insert message (only filename stored in DB)
    $stmt = $conn->prepare("
        INSERT INTO messages 
        (chat_id, sender_id, receiver_id, message_text, file_path, message_type, created_at)
=======
    // Save message
    $stmt = $pdo->prepare("
        INSERT INTO messages (chat_id, sender_id, receiver_id, message_text, file_path, message_type, created_at)
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
    // ✅ Insert message
    $stmt = $conn->prepare("
        INSERT INTO messages 
        (chat_id, sender_id, receiver_id, message_text, file_path, message_type, created_at)
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([
        $chat_id,
<<<<<<< HEAD
<<<<<<< HEAD
        $sender_id,
        $receiver_id,
        $message_text,
        $filename,   // only filename saved
=======
        $sender_id,
        $receiver_id,
        $message_text,
        $filename,
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        $message_type
    ]);

    $message_id = $conn->lastInsertId();

<<<<<<< HEAD
    // ✅ Fetch back message with sender & receiver info
=======
    // ✅ Fetch back message with sender & receiver info from new_users
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt = $conn->prepare("
        SELECT m.*,
               s.id   AS sender_id,
               CONCAT(s.first_name, ' ', s.last_name) AS sender_name,
               s.image AS sender_profile_image,
               r.id   AS receiver_id,
               CONCAT(r.first_name, ' ', r.last_name) AS receiver_name,
               r.image AS receiver_profile_image
        FROM messages m
<<<<<<< HEAD
        LEFT JOIN users s ON m.sender_id = s.id
        LEFT JOIN users r ON m.receiver_id = r.id
=======
        LEFT JOIN new_users s ON m.sender_id = s.id
        LEFT JOIN new_users r ON m.receiver_id = r.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        WHERE m.id = ?
    ");
    $stmt->execute([$message_id]);
    $messageData = $stmt->fetch(PDO::FETCH_ASSOC);

    // ✅ If file exists, prepend full URL
    if (!empty($messageData['file_path'])) {
        $messageData['file_path'] = $GLOBALS['chat_file'] . $messageData['file_path'];
<<<<<<< HEAD
    } else {
        $messageData['file_path'] = null;
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    }

    $conn->commit();

    echo json_encode([
        'status'  => true,
        'message' => 'Message sent successfully',
        'data'    => $messageData
    ]);
} catch (Exception $e) {
<<<<<<< HEAD
    $conn->rollBack();
=======
    if ($conn->inTransaction()) $conn->rollBack();
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    echo json_encode([
        'status'  => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
<<<<<<< HEAD
=======
        $user_id,
        $receiver_id,
        $message_text,
        $filename,
        $message_type
    ]);

    $pdo->commit();

    echo json_encode([
        'status' => true,
        'message' => 'Message sent successfully',
        'data' => [
            'chat_id' => $chat_id,
            'sender_id' => $user_id,
            'receiver_id' => $receiver_id,
            'message_text' => $message_text,
            'file_path' => $filename,
            'message_type' => $message_type
        ]
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['status' => false, 'message' => 'Error: ' . $e->getMessage()]);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
}
=======
}
?>}
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
