<?php
require_once "connection.php"; // uses $conn and addNotification
header("Content-Type: application/json");

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Only POST method allowed"
    ]);
    exit;
}

// Get POST input from form-data
$application_id = isset($_POST['application_id']) ? $_POST['application_id'] : null;
$status = isset($_POST['status']) ? strtolower($_POST['status']) : null;

// Validate required parameters
if (!$application_id || !$status) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameters: application_id or status"
    ]);
    exit;
}

try {
    // Fetch application
    $stmt = $conn->prepare("SELECT * FROM job_applications WHERE id = ?");
    $stmt->execute([$application_id]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo json_encode([
            "success" => false,
            "message" => "Application not found"
        ]);
        exit;
    }

    $now = date("Y-m-d H:i:s");
    $updates = [];
    $message = "";
    $notification_target_id = null;
    $notification_message = "";

    switch ($status) {
        case "accept":
            $updates = [
                "accepted_date" => $now,
                "status" => "Accepted",
                "application_step" => 2
            ];
            $message = "Great, you have successfully accepted the job!";
            $notification_target_id = $application['owner_id'];
            $notification_message = $application['applicant_id'] . " accepted the project on " . $now;
            break;

        case "complete":
            $updates = [
                "completed_at" => $now,
                "status" => "Completed",
                "application_step" => 3
            ];
            $message = "You have successfully completed the job!";
            $notification_target_id = $application['owner_id'];
            $notification_message = $application['applicant_id'] . " completed the project on " . $now;
            break;

        case "ownerreview":
            if (!isset($_POST['owner_review'])) {
                echo json_encode([
                    "success" => false,
                    "message" => "Missing parameter: owner_review"
                ]);
                exit;
            }
            $owner_review = trim($_POST['owner_review']);
            $updates = [
                "owner_review" => $owner_review,
                "owner_review_at" => $now,
                "application_step" => 5
            ];
            $message = "Great, you have posted a review!";
            $notification_target_id = $application['applicant_id'];
            $notification_message = "Owner posted a review on " . $now;
            break;

        case "employeereview":
            if (!isset($_POST['employee_review'])) {
                echo json_encode([
                    "success" => false,
                    "message" => "Missing parameter: employee_review"
                ]);
                exit;
            }
            $employee_review = trim($_POST['employee_review']);
            $updates = [
                "employee_review" => $employee_review,
                "employee_review_at" => $now,
                "application_step" => 6
            ];
            $message = "Great, you have posted a review!";
            $notification_target_id = $application['owner_id'];
            $notification_message = "Employee posted a review on " . $now;
            break;

        default:
            echo json_encode([
                "success" => false,
                "message" => "Invalid status value"
            ]);
            exit;
    }

    // Build SQL dynamically
    $setPart = [];
    $values = [];
    foreach ($updates as $col => $val) {
        $setPart[] = "$col = ?";
        $values[] = $val;
    }
    $values[] = $application_id;

    $sql = "UPDATE job_applications SET " . implode(", ", $setPart) . " WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute($values);

    // Send notification
    if ($notification_target_id) {
        addNotification(
            $notification_target_id,
            $notification_message,
            $notification_message,
            'job_application',
            $application['applicant_id'],
            'JobApplication',
            $application_id
        );
    }

    echo json_encode([
        "success" => true,
        "message" => $message,
        "application_id" => $application_id,
        "new_status" => $updates['status'] ?? $status,
        "updated_fields" => $updates
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
