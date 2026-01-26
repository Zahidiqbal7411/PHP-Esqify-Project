<?php
header("Content-Type: application/json");

// Database connection
require_once "connection.php"; // <-- update with your PDO connection

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        "success" => false,
        "message" => "Only POST method is allowed"
    ]);
    exit;
}

// Ensure request is form-data
if (empty($_POST)) {
    echo json_encode([
        "success" => false,
        "message" => "Request must be sent as form-data (multipart/form-data or application/x-www-form-urlencoded)"
    ]);
    exit;
}

// Validate application_id
if (!isset($_POST['application_id'])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing required parameter: application_id"
    ]);
    exit;
}

$applicationId = (int) $_POST['application_id'];

try {
    // Fetch job application with applicant + job + owner
    $stmt = $conn->prepare("
        SELECT ja.*, 
               u.first_name AS owner_name,
               j.title AS job_title
        FROM job_applications ja
<<<<<<< HEAD
        LEFT JOIN users u ON u.id = ja.owner_id
=======
        LEFT JOIN new_users u ON u.id = ja.owner_id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        LEFT JOIN eq_jobs j ON j.id = ja.job_id
        WHERE ja.id = :id LIMIT 1
    ");
    $stmt->execute(['id' => $applicationId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$application) {
        echo json_encode([
            "success" => false,
            "message" => "Application not found"
        ]);
        exit;
    }

    // If not hired yet
    if (empty($application['hired_at'])) {
        $hiredAt = (new DateTime())->format('Y-m-d H:i:s');

        // Update application
        $update = $conn->prepare("
            UPDATE job_applications 
            SET hired_at = :hired_at, status = 'Hired'
            WHERE id = :id
        ");
        $update->execute([
            'hired_at' => $hiredAt,
            'id'       => $applicationId
        ]);

        // Insert notification (like addNotification in Laravel)
        $notif = $conn->prepare("
            INSERT INTO notifications 
                (user_id, title, message, type, notification_from, related_class, class_id, is_read, created_at) 
            VALUES 
                (:user_id, :title, :message, :type, :notification_from, :related_class, :class_id, 0, NOW())
        ");
        $notif->execute([
            'user_id'         => $application['applicant_id'], // applicant gets notification
            'title'           => "You are hired for a job",
            'message'         => $application['owner_name'] . " hired you for " . $application['job_title'],
            'type'            => "job_application",
            'notification_from' => $application['owner_id'], // employer who hired
            'related_class'   => "JobApplication",
            'class_id'        => $applicationId
        ]);

        echo json_encode([
            "success" => true,
            "message" => "Applicant hired successfully.",
            "application_id" => $applicationId,
            "status" => "Hired",
            "hired_at" => $hiredAt
        ]);
    } else {
        echo json_encode([
            "success" => true,
            "message" => "Applicant already hired.",
            "application_id" => $applicationId,
            "status" => $application['status'],
            "hired_at" => $application['hired_at']
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
