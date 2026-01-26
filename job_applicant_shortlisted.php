<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once "connection.php"; // <-- your PDO connection

try {
    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode([
            "success" => false,
            "message" => "Only POST method allowed"
        ]);
        exit;
    }

    // Read POST params
    $application_id = isset($_POST['application_id']) ? intval($_POST['application_id']) : 0;

    if ($application_id <= 0) {
        echo json_encode([
            "success" => false,
            "message" => "Missing or invalid application_id"
        ]);
        exit;
    }

    // Check current status first
    $check = $conn->prepare("SELECT is_shortlisted FROM job_applications WHERE id = :id");
    $check->execute([':id' => $application_id]);
    $app = $check->fetch(PDO::FETCH_ASSOC);

    if (!$app) {
        echo json_encode([
            "success" => false,
            "message" => "Application not found."
        ]);
        exit;
    }

    if ($app['is_shortlisted'] == 1) {
        echo json_encode([
            "success" => false,
            "message" => "Application already shortlisted.",
            "application_id" => $application_id,
            "is_shortlisted" => 1
        ]);
        exit;
    }

    // Otherwise update
    $sql = "UPDATE job_applications SET is_shortlisted = 1 WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $application_id]);

    echo json_encode([
        "success" => true,
        "message" => "Application shortlisted successfully.",
        "application_id" => $application_id,
        "is_shortlisted" => 1
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Error: " . $e->getMessage()
    ]);
}
