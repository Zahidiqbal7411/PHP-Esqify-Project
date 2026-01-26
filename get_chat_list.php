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

$user_id = isset($input['user_id']) ? intval($input['user_id']) : 0;

if (!$user_id) {
    echo json_encode([
        "status" => false,
        "message" => "user_id is required"
    ]);
    exit();
}

try {
    $sql = "
        SELECT 
            c.id AS chat_id,
            CASE 
                WHEN c.sender_id = ? THEN c.receiver_id
                ELSE c.sender_id
            END AS partner_id,
            CASE 
                WHEN c.sender_id = ? THEN CONCAT(receiver.first_name, ' ', receiver.last_name)
                ELSE CONCAT(sender.first_name, ' ', sender.last_name)
            END AS partner_name,
            CASE 
                WHEN c.sender_id = ? THEN receiver.image
                ELSE sender.image
            END AS partner_image,
            (
                SELECT message_text 
                FROM messages 
                WHERE chat_id = c.id 
                ORDER BY created_at DESC 
                LIMIT 1
            ) AS last_message,
            (
                SELECT message_type
                FROM messages 
                WHERE chat_id = c.id 
                ORDER BY created_at DESC 
                LIMIT 1
            ) AS last_message_type,
            (
                SELECT created_at
                FROM messages 
                WHERE chat_id = c.id 
                ORDER BY created_at DESC 
                LIMIT 1
            ) AS last_message_time,
            (
                SELECT COUNT(*) 
                FROM messages 
                WHERE chat_id = c.id 
                AND receiver_id = ?
                AND is_read = 0
            ) AS unread_count,
            c.created_at
        FROM chats c
<<<<<<< HEAD
        LEFT JOIN users sender ON c.sender_id = sender.id
        LEFT JOIN users receiver ON c.receiver_id = receiver.id
=======
        LEFT JOIN new_users sender ON c.sender_id = sender.id
        LEFT JOIN new_users receiver ON c.receiver_id = receiver.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        WHERE c.sender_id = ? OR c.receiver_id = ?
        ORDER BY last_message_time DESC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $chats = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepend image path
    foreach ($chats as &$chat) {
        if (!empty($chat['partner_image'])) {
            $chat['partner_image'] = $GLOBALS['dealownerimagepath'] . $chat['partner_image'];
        } else {
            $chat['partner_image'] = null;
        }

        // If last message is not text, show appropriate indicator
        if ($chat['last_message_type'] === 'image') {
            $chat['last_message'] = '📷 Image';
        } elseif ($chat['last_message_type'] === 'video') {
            $chat['last_message'] = '🎥 Video';
        } elseif ($chat['last_message_type'] === 'file') {
            $chat['last_message'] = '📎 File';
        }
    }
    unset($chat);

    echo json_encode([
        "status" => true,
        "message" => count($chats) > 0 ? "Chat list fetched successfully." : "No chats found.",
        "count" => count($chats),
        "data" => $chats
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (PDOException $e) {
    error_log("Chat List API Error: " . $e->getMessage());
    echo json_encode([
        "status" => false,
        "message" => "Database error: " . $e->getMessage()
    ]);
}
