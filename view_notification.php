<?php
require_once 'connection.php'; // $pdo PDO instance
session_start();
header('Content-Type: application/json');

// Simulated authentication
$userId = $_SESSION['user_id'] ?? 1;

$perPage = isset($_POST['per_page']) ? (int)$_POST['per_page'] : 10;
$page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
$offset = ($page - 1) * $perPage;

try {
    // Fetch user's first name
<<<<<<< HEAD
    $userStmt = $pdo->prepare("SELECT first_name FROM users WHERE id = :id");
=======
    $userStmt = $pdo->prepare("SELECT first_name FROM new_users WHERE id = :id");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $userStmt->execute([':id' => $userId]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    $firstLetter = strtoupper(substr($user['first_name'] ?? 'U', 0, 1));

    // Count unread notifications
    $unreadStmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :user_id AND is_read = 0");
    $unreadStmt->execute([':user_id' => $userId]);
    $unreadCount = (int)$unreadStmt->fetchColumn();

    // Fetch paginated notifications
    $notifStmt = $pdo->prepare("
        SELECT *
<<<<<<< HEAD
        FROM notifications
        WHERE user_id = :user_id
=======
        FROM notifications n
    LEFT JOIN new_users u ON n.notification_from = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        ORDER BY created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $notifStmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
    $notifStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $notifStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $notifStmt->execute();
    $notifications = $notifStmt->fetchAll(PDO::FETCH_ASSOC);

    // Add first_letter to each notification (if you want it included in each)
    foreach ($notifications as &$n) {
        $n['first_letter'] = $firstLetter;
    }

    // Mark all unread notifications as read
    $markRead = $pdo->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :user_id AND is_read = 0");
    $markRead->execute([':user_id' => $userId]);

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
