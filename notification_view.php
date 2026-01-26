<?php
require_once 'connection.php'; // loads $conn
session_start();
header('Content-Type: application/json');

// Get user_id from POST
$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'user_id is required']);
    exit;
}

// Pagination
$perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $perPage;

try {
    // Count unread notifications
    $unreadStmt = $conn->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $unreadStmt->execute([$userId]);
    $unreadCount = (int)$unreadStmt->fetchColumn();

    // Mark all unread notifications as read BEFORE fetching
    $markReadStmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0");
    $markReadStmt->execute([$userId]);

    // Fetch paginated notifications with "from" user info
    $notifStmt = $conn->prepare("
        SELECT n.*, u.first_name AS from_first_name, u.username AS from_username
        FROM notifications n
<<<<<<< HEAD
        LEFT JOIN users u ON u.id = n.notification_from
=======
        LEFT JOIN new_users u ON n.notification_from = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $notifStmt->bindValue(1, $userId, PDO::PARAM_INT);
    $notifStmt->bindValue(2, $perPage, PDO::PARAM_INT);
    $notifStmt->bindValue(3, $offset, PDO::PARAM_INT);
    $notifStmt->execute();
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'unread_count' => $unreadCount,
        'page' => $page,
        'per_page' => $perPage,
        'notifications' => $notifications
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
