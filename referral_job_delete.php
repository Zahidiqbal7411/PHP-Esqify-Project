<?php
<<<<<<< HEAD
header('Content-Type: application/json');
require_once 'connection.php'; // This sets $conn as PDO instance

$response = [
    'status' => false,
    'message' => ''
];

// Allow only POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Invalid request method. POST required.';
    echo json_encode($response);
    exit;
}

// Get job ID from POST
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    $response['message'] = 'Invalid job ID.';
    echo json_encode($response);
    exit;
}

try {
    $sql = "DELETE FROM eq_jobs WHERE id = :id AND job_type = 'referral'";
    $stmt = $conn->prepare($sql);
=======
require_once 'connection.php'; // Make sure $pdo is your PDO instance

// Get job ID from URL
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    $sql = "DELETE FROM eq_jobs WHERE id = :id AND job_type = 'referral'";
    $stmt = $pdo->prepare($sql);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
<<<<<<< HEAD
        $response['status'] = true;
        $response['message'] = 'Referral job deleted successfully.';
    } else {
        $response['message'] = 'No referral job found with that ID.';
    }
} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
=======
        echo json_encode(['status' => true, 'message' => 'Referral job deleted successfully.']);
    } else {
        echo json_encode(['status' => false, 'message' => 'No referral job found with that ID.']);
    }
} else {
    echo json_encode(['status' => false, 'message' => 'Invalid job ID.']);
}
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
