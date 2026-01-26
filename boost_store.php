<?php
require_once 'connection.php'; // $conn
header('Content-Type: application/json');
date_default_timezone_set('UTC');

$result = ['status' => false, 'message' => '', 'data' => null];

// Get POST data
$userId = $_POST['user_id'] ?? null;
$productId = $_POST['product_id'] ?? null; // equivalent to $id in Laravel route
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
if (!$userId || !$productId || !$boostType || !$duration || !$startDate) {
    $result['message'] = 'Missing required fields';
    echo json_encode($result);
    exit;
}

try {
    // Determine model type
    $model = ($isJobBoost === 'isJobBoost') ? 'EQJobs' : 'Deal';

    // Calculate end date
    $endDate = (new DateTime($startDate))->modify("+{$duration} days")->format('Y-m-d');

    // Insert boost record
    $stmt = $conn->prepare("
        INSERT INTO boosts
        (user_id, product_id, boost_type, status, practice_area_id, plan_id, speciality_id, state_id, city_id, budget, reach, model, duration, start_date, end_date, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");

    $success = $stmt->execute([
        $userId,
        $productId,
        $boostType,
        0, // status pending
        $practiceAreaId,
        $planId,
        $specialityId,
        $stateId,
        $cityId,
        $budget,
        $reach,
        $model,
        $duration,
        $startDate,
        $endDate
    ]);

    if ($success) {
        $boostId = $conn->lastInsertId();
        $result['status'] = true;
        $result['message'] = "Great, the Boost has been saved successfully.";
        $result['data'] = [
            'boost_id' => $boostId,
            'user_id' => $userId,
            'product_id' => $productId,
            'boost_type' => $boostType,
            'status' => 0,
            'practice_area_id' => $practiceAreaId,
            'plan_id' => $planId,
            'speciality_id' => $specialityId,
            'state_id' => $stateId,
            'city_id' => $cityId,
            'budget' => $budget,
            'reach' => $reach,
            'model' => $model,
            'duration' => $duration,
            'start_date' => $startDate,
            'end_date' => $endDate
        ];
    } else {
        $result['message'] = "Technical Error! Boost not created.";
    }
} catch (Exception $e) {
    $result['message'] = "An error occurred in file " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage();
}

echo json_encode($result);
