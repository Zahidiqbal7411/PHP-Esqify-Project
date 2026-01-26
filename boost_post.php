<?php
require_once 'connection.php'; // $pdo PDO instance
session_start();
header('Content-Type: application/json');
date_default_timezone_set('UTC');

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['status' => false, 'message' => 'Unauthorized']);
    exit;
}

$productId = $_POST['product_id'] ?? null;
$boostType = $_POST['boost_type'] ?? null;
$practiceAreaId = $_POST['practice_area_boost'] ?? null;
$planId = $_POST['plan'] ?? null;
$specialityId = $_POST['speciality'] ?? null;
$stateId = $_POST['state'] ?? null;
$cityId = $_POST['city'] ?? null;
$budget = $_POST['budget'] ?? null;
$reach = $_POST['reach'] ?? null;
$duration = $_POST['duration'] ?? null;
$startDate = $_POST['start_date'] ?? null;
$isJobBoost = $_POST['isJobBoost'] ?? null;

// Validate required fields
if (!$productId || !$boostType || !$startDate || !$duration) {
    echo json_encode(['status' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    // Determine model class
    $model = ($isJobBoost === 'isJobBoost') ? 'EQJobs' : 'Deal';

    // Calculate end date
    $endDate = (new DateTime($startDate))->modify("+{$duration} days")->format('Y-m-d');

    // Insert boost record
    $stmt = $pdo->prepare("
        INSERT INTO boosts 
        (user_id, product_id, boost_type, status, practice_area_id, plan_id, speciality_id, state_id, city_id, budget, reach, model, duration, start_date, end_date, created_at)
        VALUES 
        (:user_id, :product_id, :boost_type, :status, :practice_area_id, :plan_id, :speciality_id, :state_id, :city_id, :budget, :reach, :model, :duration, :start_date, :end_date, NOW())
    ");

    $success = $stmt->execute([
        ':user_id' => $userId,
        ':product_id' => $productId,
        ':boost_type' => $boostType,
        ':status' => 0,
        ':practice_area_id' => $practiceAreaId,
        ':plan_id' => $planId,
        ':speciality_id' => $specialityId,
        ':state_id' => $stateId,
        ':city_id' => $cityId,
        ':budget' => $budget,
        ':reach' => $reach,
        ':model' => $model,
        ':duration' => $duration,
        ':start_date' => $startDate,
        ':end_date' => $endDate
    ]);

    if ($success) {
        $lastId = $pdo->lastInsertId();
        echo json_encode([
            'status' => true,
            'message' => 'Great, the Boost has been saved successfully.',
            'data' => ['boost_id' => $lastId]
        ]);
    } else {
        echo json_encode([
            'status' => false,
            'message' => 'Technical Error! Boost not created.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'An error occurred in file ' . $e->getFile() . ' on line ' . $e->getLine() . ': ' . $e->getMessage()
    ]);
}
