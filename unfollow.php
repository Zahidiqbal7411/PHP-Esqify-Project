<?php
header('Content-Type: application/json');

$response = ['status' => false, 'message' => ''];

// Expected POST params: follower_id, unfollowed_id
<<<<<<< HEAD
$follower_id   = $_POST['follower_id']   ?? null;
=======
$follower_id = $_POST['follower_id'] ?? null;
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
$unfollowed_id = $_POST['unfollowed_id'] ?? null;

if (!$follower_id || !$unfollowed_id) {
    $response['message'] = 'Both follower_id and unfollowed_id are required.';
    echo json_encode($response);
    exit;
}

try {
<<<<<<< HEAD
    require_once 'connection.php'; // provides $conn (PDO)

    // Delete follow relation
    $stmt = $conn->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $unfollowed_id]);

    if ($stmt->rowCount() > 0) {
        $response['status'] = true;
        $response['message'] = 'Unfollowed successfully.';
    } else {
        $response['message'] = 'No follow relation found.';
    }

=======
    require_once 'connection.php'; // Your PDO $pdo

    // Delete the follow relation
    $stmt = $pdo->prepare("DELETE FROM follows WHERE follower_id = ? AND followed_id = ?");
    $stmt->execute([$follower_id, $unfollowed_id]);

    $response['status'] = true;
    $response['message'] = 'Unfollowed successfully.';
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
