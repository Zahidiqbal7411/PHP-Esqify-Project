<?php
require_once 'connection.php'; // loads $conn
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Get notification_id from POST
$notifId = $_POST['notification_id'] ?? null;

if (!$notifId) {
    echo json_encode(['success' => false, 'message' => 'notification_id is required']);
    exit;
}

try {
    // Check if notification exists
    $checkStmt = $conn->prepare("SELECT id FROM notifications WHERE id = ?");
    $checkStmt->execute([$notifId]);
    $notification = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if (!$notification) {
        echo json_encode(['success' => false, 'message' => 'Notification not found']);
        exit;
    }

    // Delete the notification
    $deleteStmt = $conn->prepare("DELETE FROM notifications WHERE id = ?");
    $deleteStmt->execute([$notifId]);

    echo json_encode(['success' => true, 'message' => 'Notification deleted successfully']);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
