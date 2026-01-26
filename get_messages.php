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

// Read raw input
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);

// Merge JSON and $_POST
$input = [];
if (!empty($_POST)) {
    $input = $_POST;
} elseif (is_array($decodedJson)) {
    $input = $decodedJson;
}

function getParam($key, $default = null) {
    global $input;
    return isset($input[$key]) && $input[$key] !== '' ? trim($input[$key]) : $default;
}

$chat_id = getParam('chat_id');
$user_id = getParam('user_id');
$per_page = max(1, (int)getParam('per_page', 50));
$page = max(1, (int)getParam('page', 1));
$offset = ($page - 1) * $per_page;

if (!$chat_id || !$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "chat_id and user_id are required"
    ]);
    exit();
}

try {
    $conn->beginTransaction();

    // Verify user is part of this chat
    $verifyStmt = $conn->prepare("
        SELECT id FROM chats 
        WHERE id = ? AND (sender_id = ? OR receiver_id = ?)
        LIMIT 1
    ");
    $verifyStmt->execute([$chat_id, $user_id, $user_id]);
    
    if ($verifyStmt->rowCount() === 0) {
        $conn->rollBack();
        echo json_encode([
            "status" => false,
            "message" => "Invalid chat_id or unauthorized access"
        ]);
        exit();
    }

    // Mark messages as read
    $updateStmt = $conn->prepare("
        UPDATE messages 
        SET is_read = 1 
        WHERE chat_id = ? AND receiver_id = ? AND is_read = 0
    ");
    $updateStmt->execute([$chat_id, $user_id]);

    // Count total messages
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM messages WHERE chat_id = ?");
    $countStmt->execute([$chat_id]);
    $total = (int)$countStmt->fetchColumn();

    // Fetch messages
    $sql = "
        SELECT 
            m.id,
            m.sender_id,
            CONCAT(s.first_name, ' ', s.last_name) AS sender_name,
            s.image AS sender_image,
            m.receiver_id,
            CONCAT(r.first_name, ' ', r.last_name) AS receiver_name,
            r.image AS receiver_image,
            m.message_text,
            m.message_type,
            m.file_path,
            m.is_read,
            m.created_at
        FROM messages m
<<<<<<< HEAD
        LEFT JOIN users s ON m.sender_id = s.id
        LEFT JOIN users r ON m.receiver_id = r.id
=======
        LEFT JOIN new_users s ON m.sender_id = s.id
        LEFT JOIN new_users r ON m.receiver_id = r.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        WHERE m.chat_id = ?
        ORDER BY m.created_at ASC
        LIMIT ? OFFSET ?
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(1, (int)$chat_id, PDO::PARAM_INT);
    $stmt->bindValue(2, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(3, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepend full URLs
    foreach ($messages as &$msg) {
        // Sender image
        if (!empty($msg['sender_image'])) {
            $msg['sender_image'] = $GLOBALS['dealownerimagepath'] . $msg['sender_image'];
        } else {
            $msg['sender_image'] = null;
        }

        // Receiver image
        if (!empty($msg['receiver_image'])) {
            $msg['receiver_image'] = $GLOBALS['dealownerimagepath'] . $msg['receiver_image'];
        } else {
            $msg['receiver_image'] = null;
        }

        // File path
        if (!empty($msg['file_path'])) {
            $msg['file_path'] = $GLOBALS['chat_file'] . $msg['file_path'];
        } else {
            $msg['file_path'] = null;
        }
    }
    unset($msg);

    $conn->commit();

    echo json_encode([
        "status" => true,
        "message" => "Messages fetched successfully.",
        "page" => $page,
        "per_page" => $per_page,
        "total" => $total,
        "total_pages" => ceil($total / $per_page),
        "data" => $messages
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    $conn->rollBack();
    error_log("Messages API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
