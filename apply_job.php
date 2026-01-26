<?php
<<<<<<< HEAD
header('Content-Type: application/json');

// ===== Only Allow POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Only POST method is allowed"]);
    exit();
}

// ===== Database Connection =====
require_once 'connection.php'; // $conn should be a PDO instance

// ===== Get POST params =====
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 1;
$job_id  = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
=======
// Set content type to JSON
header('Content-Type: application/json');
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';

// Fake user authentication (replace with your actual logic)
$user_id = 123; // Assume logged-in user ID, or get it from session/cookie/token

if (!$user_id) {
    echo json_encode(['status' => false, 'message' => 'You have to login first!']);
    exit;
}

// Get job_id from POST
$job_id = isset($_POST['job_id']) ? (int)$_POST['job_id'] : 0;
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a

if (!$job_id) {
    echo json_encode(['status' => false, 'message' => 'Job ID not found!']);
    exit;
}

<<<<<<< HEAD
// ===== Check if job exists =====
$jobStmt = $conn->prepare("SELECT * FROM eq_jobs WHERE id = :job_id");
$jobStmt->execute([':job_id' => $job_id]);
$job = $jobStmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    echo json_encode(['status' => false, 'message' => 'Job not found!']);
    exit;
}

// ===== Take owner_id from job record =====
$owner_id = (int) $job['owner'];

// ===== Check for duplicate application =====
$checkStmt = $conn->prepare("
    SELECT id FROM job_applications 
    WHERE applicant_id = :applicant_id AND job_id = :job_id
");
$checkStmt->execute([
    ':applicant_id' => $user_id,
    ':job_id' => $job_id
]);
$existing = $checkStmt->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    echo json_encode([
        'status' => false,
        'message' => 'You have already applied to this job!',
        'application_id' => $existing['id']
    ]);
    exit;
}

// ===== Insert application =====
try {
    $apply_date = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("
        INSERT INTO job_applications (owner_id, applicant_id, job_id, apply_date) 
        VALUES (:owner_id, :applicant_id, :job_id, :apply_date)
    ");

    $stmt->execute([
        ':owner_id'     => $owner_id,
        ':applicant_id' => $user_id,
        ':job_id'       => $job_id,
        ':apply_date'   => $apply_date
    ]);

    $application_id = $conn->lastInsertId();

    // ===== Return the inserted record =====
    $record = [
        'id'           => $application_id,
        'owner_id'     => $owner_id,
        'applicant_id' => $user_id,
        'job_id'       => $job_id,
        'apply_date'   => $apply_date
    ];

    echo json_encode([
        'status' => true,
        'message' => 'Application submitted successfully!',
        'data' => $record
    ]);

} catch (PDOException $e) {
=======
// Check if job exists
$jobQuery = $conn->query("SELECT * FROM eq_jobs WHERE id = $job_id");
if (!$jobQuery || $jobQuery->num_rows == 0) {
    echo json_encode(['status' => false, 'message' => 'Job not found!']);
    exit;
}
$job = $jobQuery->fetch_assoc();

try {
    $owner_id = (int) $job['owner']; // Assuming `owner` is the column name
    $apply_date = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO job_applications (owner_id, applicant_id, job_id, apply_date) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiis", $owner_id, $user_id, $job_id, $apply_date);
    $stmt->execute();

    echo json_encode(['status' => true, 'message' => 'Application submitted successfully!']);
} catch (Exception $e) {
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
    echo json_encode([
        'status' => false,
        'message' => 'Something went wrong while applying. Please try again later. ' . $e->getMessage()
    ]);
}
<<<<<<< HEAD
=======
?>
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
