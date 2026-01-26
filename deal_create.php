<?php
header('Content-Type: application/json');
require_once 'connection.php'; // Your PDO connection file

$response = [
    'status' => false,
    'message' => '',
    'data' => []
];

try {
    // Get industries
    $stmt = $conn->query("SELECT * FROM industrys");
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get specialities
    $stmt = $conn->query("SELECT * FROM specialties");
    $specialities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get practice areas
    $stmt = $conn->query("SELECT * FROM practice_areas");
    $practiceAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get states
    $stmt = $conn->query("SELECT * FROM states");
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get users (role_id = 3, exclude current user)
    $currentUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT * FROM users WHERE role_id = 3 AND id != ?");
=======
    $stmt = $conn->prepare("SELECT * FROM new_users WHERE role_id = 3 AND id != ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$currentUserId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get bars
    $bars = [];
    try {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'bars'");
        if ($tableCheck->rowCount() > 0) {
            $stmt = $conn->query("SELECT * FROM bars WHERE status = 1 ORDER BY title ASC");
            $bars = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    } catch (PDOException $e) {
        // If bars table doesn't exist, just continue with empty array
        error_log("Bars fetch warning: " . $e->getMessage());
    }

    // Build response
    $response['status'] = true;
    $response['message'] = "Data fetched successfully.";
    $response['data'] = [
        'industry' => $industries,
        'speciality' => $specialities,
        'practicearea' => $practiceAreas,
        'states' => $states,
        'users' => $users,
        'bars' => $bars
    ];

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
