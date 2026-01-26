<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

include_once "connection.php"; // PDO connection

$response = ["success" => false, "message" => ""];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response["message"] = "Only POST method allowed";
    echo json_encode($response);
    exit();
}

// Get application_id
$application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;
if ($application_id <= 0) {
    echo json_encode(["success" => false, "message" => "Missing required parameter: application_id"]);
    exit();
}

// Check if applicant already rejected
$stmt = $conn->prepare("SELECT is_rijected FROM job_applications WHERE id = :id");
$stmt->execute([':id' => $application_id]);
$app = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$app) {
    echo json_encode(["success" => false, "message" => "Application not found."]);
    exit();
}

if ($app['is_rijected'] == 1) {
    echo json_encode([
        "success" => true,
        "message" => "Applicant already rejected.",
        "application_id" => $application_id,
        "status" => "Rejected"
    ]);
    exit();
}

// Reject applicant
$update = $conn->prepare("UPDATE job_applications SET is_rijected = 1, status = 'Rejected' WHERE id = :id");
if ($update->execute([':id' => $application_id])) {
    echo json_encode([
        "success" => true,
        "message" => "Applicant rejected successfully.",
        "application_id" => $application_id,
        "status" => "Rejected"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Failed to reject applicant."
    ]);
}
