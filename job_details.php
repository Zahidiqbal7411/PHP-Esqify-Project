<?php
header('Content-Type: application/json');
<<<<<<< HEAD
<<<<<<< HEAD
require_once 'connection.php'; // should create a $conn = new PDO(...)
=======
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
header('Content-Type: application/json');
require_once 'connection.php'; // should create a $conn = new PDO(...)
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

$response = [
    'status' => false,
    'message' => '',
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    'data' => []
];

// Get job ID from POST
<<<<<<< HEAD
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
=======
    'data' => null
];

// Get job ID from GET or POST
$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
$id = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : 0);
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

// Validate ID
if ($id <= 0) {
    $response['message'] = 'Invalid job ID.';
    echo json_encode($response);
    exit;
}

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
try {
    // Prepare SQL query with JOINs
    $sql = "
        SELECT 
            j.*, 
            i.title AS industry_name, 
            s.title AS speciality_name, 
            p.title AS practice_area_name,
            st.name AS job_state_name,
            c.name AS job_city_name
        FROM eq_jobs j
        LEFT JOIN industrys i ON j.industry = i.id
        LEFT JOIN specialties s ON j.speciality = s.id
        LEFT JOIN practice_areas p ON j.practice_area = p.id
        LEFT JOIN states st ON j.job_state = st.id
        LEFT JOIN citys c ON j.job_city = c.id
        WHERE j.id = :id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $job = $stmt->fetch(PDO::FETCH_ASSOC);

    // If job not found
    if (!$job) {
        $response['message'] = 'Job not found.';
        echo json_encode($response);
        exit;
    }

    // Replace IDs with names for better clarity
    $job['industry'] = $job['industry_name'];
    $job['speciality'] = $job['speciality_name'];
    $job['practice_area'] = $job['practice_area_name'];
    $job['job_state'] = $job['job_state_name'];
    $job['job_city'] = $job['job_city_name'];

    // Remove helper fields
    unset(
        $job['industry_name'],
        $job['speciality_name'],
        $job['practice_area_name'],
        $job['job_state_name'],
        $job['job_city_name'],
        $job['firm'] // in case it exists
    );

    // NEW: Add has_applied flag if user_id is provided
    $job['has_applied'] = false;
<<<<<<< HEAD
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
=======
    $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : (isset($_GET['user_id']) ? intval($_GET['user_id']) : 0);
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    if ($user_id > 0) {
        try {
            $appliedStmt = $conn->prepare("SELECT id FROM job_applications WHERE applicant_id = ? AND job_id = ? LIMIT 1");
            $appliedStmt->execute([$user_id, $id]);
            $job['has_applied'] = $appliedStmt->rowCount() > 0;
        } catch (PDOException $e) {
<<<<<<< HEAD
            error_log("Has applied check error: " . $e->getMessage());
=======
            // Table might not exist yet
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        }
    }

    // Final response
    $response['status'] = true;
    $response['message'] = 'Job data loaded.';
    $response['data'] = $job;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response);
<<<<<<< HEAD
=======
// Fetch job with joins to get readable names, including job_state and job_city names
$jobQuery = mysqli_query($conn, "
    SELECT 
        j.*, 
        i.title AS industry_name, 
        s.title AS speciality_name, 
        p.title AS practice_area_name,
        st.name AS job_state_name,
        c.name AS job_city_name
    FROM eq_jobs j
    LEFT JOIN industrys i ON j.industry = i.id
    LEFT JOIN specialties s ON j.speciality = s.id
    LEFT JOIN practice_areas p ON j.practice_area = p.id
    LEFT JOIN states st ON j.job_state = st.id
    LEFT JOIN citys c ON j.job_city = c.id
    WHERE j.id = $id
    LIMIT 1
");

$job = mysqli_fetch_assoc($jobQuery);

// If job not found
if (!$job) {
    $response['message'] = 'Job not found.';
    echo json_encode($response);
    exit;
}

// Replace IDs with names for better clarity
$job['industry'] = $job['industry_name'];
$job['speciality'] = $job['speciality_name'];
$job['practice_area'] = $job['practice_area_name'];
$job['job_state'] = $job['job_state_name'];
$job['job_city'] = $job['job_city_name'];

// Optional: remove the extra helper fields
unset(
    $job['industry_name'],
    $job['speciality_name'],
    $job['practice_area_name'],
    $job['job_state_name'],
    $job['job_city_name'],
    $job['firm'] // just in case
);

// Final response
$response['status'] = true;
$response['message'] = 'Job data loaded.';
$response['data'] = $job;

echo json_encode($response);
?>



>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
