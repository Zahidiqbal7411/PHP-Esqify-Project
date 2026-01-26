<?php
require_once 'connection.php'; // ✅ provides $conn (PDO)

header("Content-Type: application/json");

// ✅ Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "status" => false,
        "message" => "Only POST requests allowed"
    ]);
    exit;
}

// ✅ Get input from POST
$sender_id   = $_POST['sender_id'] ?? null;
$receiver_id = $_POST['receiver_id'] ?? null;
$is_archive  = isset($_POST['is_archive']) ? (int)$_POST['is_archive'] : null;

// ✅ Validate input
if (!$sender_id || !$receiver_id || !in_array($is_archive, [0, 1], true)) {
    echo json_encode([
        "status" => false,
        "message" => "sender_id, receiver_id and is_archive (0 or 1) are required"
    ]);
    exit;
}

try {
    // ✅ Find chat between the two users
    $stmt = $conn->prepare("
        SELECT * FROM chats
        WHERE (sender_id = :sender AND receiver_id = :receiver)
           OR (sender_id = :receiver AND receiver_id = :sender)
        LIMIT 1
    ");
    $stmt->execute([
        'sender'   => $sender_id,
        'receiver' => $receiver_id
    ]);

    $chat = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$chat) {
        echo json_encode([
            "status" => false,
            "message" => "Chat not found"
        ]);
        exit;
    }

    // ✅ Decide which archive field to update
    if ((int)$chat['sender_id'] === (int)$sender_id) {
        $field = "is_archived_by_sender";
    } elseif ((int)$chat['receiver_id'] === (int)$sender_id) {
        $field = "is_archived_by_receiver";
    } else {
        echo json_encode([
            "status" => false,
            "message" => "You are not part of this chat"
        ]);
        exit;
    }

    // ✅ Update archive status
    $update = $conn->prepare("UPDATE chats SET $field = :is_archive WHERE id = :chat_id");
    $update->execute([
        "is_archive" => $is_archive,
        "chat_id"    => $chat['id']
    ]);

    echo json_encode([
        "status"  => true,
        "message" => $is_archive ? "User archived successfully" : "User unarchived successfully"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => false,
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
