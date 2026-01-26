<?php
// config.php - global settings

// ------------------------------------
// Protocol & Host
// ------------------------------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$hostName = $_SERVER['HTTP_HOST'];

// ------------------------------------
// Base URLs (FIXED as requested)
// ------------------------------------
$serverpath = $protocol . "dev.esqify.com/";
$basePath   = $serverpath . "mobile_api/";

$GLOBALS['serverpath'] = $serverpath;
$GLOBALS['basePath']   = $basePath;

// ------------------------------------
// Public Asset Paths
// ------------------------------------
$GLOBALS['jobimagepath']        = $protocol . "dev.esqify.com/public/profile/";
$GLOBALS['dealownerimagepath']  = $protocol . "dev.esqify.com/public/profile/";
$GLOBALS['dealimage']           = $protocol . "dev.esqify.com/public/uploads/deal-images/";
$GLOBALS['chat_file']           = $protocol . "dev.esqify.com/public/uploads/message-file/";
$GLOBALS['comment_images']      = $protocol . "dev.esqify.com/public/uploads/comment-images/";
$GLOBALS['userdocument']        = $protocol . "dev.esqify.com/public/uploads/user-documents/";

// ------------------------------------
// Constants
// ------------------------------------
define('BASE_URL', $basePath);
define('PROJECT_ROOT', __DIR__);

// ------------------------------------
// Database Connection (XAMPP LOCAL)
// ------------------------------------
$db_host = "localhost"; // XAMPP works better with localhost
$db_name = "equiheal_EsqifyNewDB";
$db_user = "root";      // XAMPP default username
$db_pass = '';          // XAMPP default password (empty)

<<<<<<< HEAD
try {
=======
try { 
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $conn = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "status"  => false,
        "message" => "Database connection failed"
    ]);
    exit;
}

<<<<<<< HEAD
=======

>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// ------------------------------------
// Notifications Helper
// ------------------------------------
function addNotification(
    $userId,
    $title,
    $message = null,
    $type = 'info',
    $notification_from = null,
    $related_class = null,
    $class_id = null
) {
    global $conn;

    $stmt = $conn->prepare("
        INSERT INTO notifications
        (user_id, title, message, type, notification_from, related_class, class_id, is_read, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW(), NOW())
    ");

    $stmt->execute([
        $userId,
        $title,
        $message,
        $type,
        $notification_from,
        $related_class,
        $class_id
    ]);

    return $conn->lastInsertId();
}

// ------------------------------------
// Security
// ------------------------------------
<<<<<<< HEAD
require_once __DIR__ . '/security.php';
=======
// (security.php removed as it was empty)
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
