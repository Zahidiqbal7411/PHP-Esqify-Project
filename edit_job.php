<?php
header('Content-Type: application/json');
<<<<<<< HEAD
<<<<<<< HEAD
require_once 'connection.php'; // $conn is PDO instance
=======
require_once 'connection.php'; // This should define $pdo (PDO instance)
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
require_once 'connection.php'; // $conn is PDO instance
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

$response = [
    'status' => false,
    'message' => '',
    'data' => null
];

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// Helper function to get param from POST or GET, with default fallback
function getParam($key, $default = null) {
    if (isset($_POST[$key])) {
        return $_POST[$key];
    } elseif (isset($_GET[$key])) {
        return $_GET[$key];
    }
    return $default;
}

<<<<<<< HEAD
// Get job ID from POST or GET
$id = intval(getParam('id', 0));
if ($id <= 0) {
    $response['message'] = 'Invalid job ID.';
=======
// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Only POST requests are allowed.';
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
    echo json_encode($response);
    exit;
}

<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// Get process_type from POST or GET
$type = getParam('process_type', '');

try {
    if ($type === 'job_details') {
<<<<<<< HEAD
        // UPDATE flow
=======
        // Get job ID
        $id = intval(getParam('id', 0));
        if ($id <= 0) {
            $response['message'] = 'Invalid job ID.';
            echo json_encode($response);
            exit;
        }
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

        // Fetch existing job data to fallback on missing fields
        $selectStmt = $conn->prepare("SELECT * FROM eq_jobs WHERE id = :id LIMIT 1");
        $selectStmt->execute([':id' => $id]);
        $currentJob = $selectStmt->fetch(PDO::FETCH_ASSOC);

        if (!$currentJob) {
            $response['message'] = 'Technical Error! Job not found.';
            echo json_encode($response);
            exit;
        }

        // Use POST/GET data if present, otherwise fallback to existing data
        $title        = getParam('title', $currentJob['title']);
        $industry     = getParam('industry', $currentJob['industry']);
        $speciality   = getParam('speciality', $currentJob['speciality']);
        $practice_area= getParam('practice_area', $currentJob['practice_area']);
        $job_city     = getParam('job_city', $currentJob['job_city']);
        $job_state    = getParam('job_state', $currentJob['job_state']);
        $position     = getParam('position', $currentJob['position']);
        $owner        = getParam('owner', $currentJob['owner']);
        $description  = getParam('description', $currentJob['descriptions']);
        $firm         = getParam('firm', $currentJob['firm']);
        $is_active    = intval(getParam('is_active', $currentJob['is_active']));
        $salary       = getParam('salary', $currentJob['salary']);

<<<<<<< HEAD
        // Prepare update query with named placeholders
=======
        // Prepare update query
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        $updateQuery = "
            UPDATE eq_jobs SET 
                title = :title,
                industry = :industry,
                speciality = :speciality,
                practice_area = :practice_area,
                job_city = :job_city,
                job_state = :job_state,
                position = :position,
                owner = :owner,
                descriptions = :description,
                firm = :firm,
                is_active = :is_active,
                salary = :salary
            WHERE id = :id
        ";

        $stmt = $conn->prepare($updateQuery);
        $stmt->execute([
            ':title'        => $title,
            ':industry'     => $industry,
            ':speciality'   => $speciality,
            ':practice_area'=> $practice_area,
            ':job_city'     => $job_city,
            ':job_state'    => $job_state,
            ':position'     => $position,
            ':owner'        => $owner,
            ':description'  => $description,
            ':firm'         => $firm,
            ':is_active'    => $is_active,
            ':salary'       => $salary,
            ':id'           => $id
        ]);

<<<<<<< HEAD
        // Fetch updated job with readable names
        $selectJob = $conn->prepare("
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
        ");
        $selectJob->execute([':id' => $id]);
        $updatedJob = $selectJob->fetch(PDO::FETCH_ASSOC);

        if ($updatedJob) {
            // Replace IDs with readable names
            $updatedJob['industry'] = $updatedJob['industry_name'];
            $updatedJob['speciality'] = $updatedJob['speciality_name'];
            $updatedJob['practice_area'] = $updatedJob['practice_area_name'];
            $updatedJob['job_state'] = $updatedJob['job_state_name'];
            $updatedJob['job_city'] = $updatedJob['job_city_name'];

            // Remove helper fields
            unset(
                $updatedJob['industry_name'],
                $updatedJob['speciality_name'],
                $updatedJob['practice_area_name'],
                $updatedJob['job_state_name'],
                $updatedJob['job_city_name'],
                $updatedJob['firm'] // optional, remove if you want to keep firm in response
            );
        }

        $response['status'] = true;
        $response['message'] = 'Great, the job details have been updated successfully!';
        $response['data'] = $updatedJob;

    } else {
        // FETCH flow (default)

        $jobQuery = $conn->prepare("
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
        ");
        $jobQuery->execute([':id' => $id]);
        $job = $jobQuery->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            $response['message'] = 'Job not found.';
        } else {
            // Replace IDs with readable names
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
                $job['firm'] // optional
            );

            $response['status'] = true;
            $response['message'] = 'Job data loaded.';
            $response['data'] = $job;
        }
=======
        $response['status'] = true;
        $response['message'] = 'Great, the job details have been updated successfully!';
    } else {
        // Other types or default FETCH logic could go here
        $response['message'] = 'Invalid process_type.';
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    }
} catch (PDOException $e) {
    $response['message'] = 'An error occurred: ' . $e->getMessage();
}

<<<<<<< HEAD
echo json_encode($response);
=======
// Get and validate user ID
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$type = $_POST['process_type'] ?? '';

if ($id <= 0) {
    $response['message'] = 'Invalid user ID.';
    echo json_encode($response);
    exit;
}

if ($type !== 'edit_profile') {
    $response['message'] = 'Invalid or missing process_type.';
    echo json_encode($response);
    exit;
}

try {
    // Check if user exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    if ($stmt->rowCount() === 0) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        exit;
    }

    // Sanitize inputs
    $first_name      = trim($_POST['first_name'] ?? '');
    $last_name       = trim($_POST['last_name'] ?? '');
    $email           = trim($_POST['email'] ?? '');
    $username        = trim($_POST['username'] ?? '');
    $bio             = trim($_POST['bio'] ?? '');
    $location        = trim($_POST['location'] ?? '');
    $practice_area   = trim($_POST['practice_area'] ?? '[]'); // JSON string
    $speciality      = trim($_POST['speciality'] ?? '[]');    // JSON string
    $profile_picture = trim($_POST['profile_picture'] ?? '');

    // Check if username is taken by someone else
    if (!empty($username)) {
        $checkUsername = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkUsername->execute([$username, $id]);

        if ($checkUsername->rowCount() > 0) {
            $response['message'] = 'Username already taken. Please choose another.';
            echo json_encode($response);
            exit;
        }
    }

    // Update user record
    $updateQuery = "
        UPDATE users SET 
            first_name = :first_name,
            last_name = :last_name,
            email = :email,
            username = :username,
            bio = :bio,
            location = :location,
            practice_area = :practice_area,
            speciality = :speciality,
            profile_picture = :profile_picture
        WHERE id = :id
    ";

    $stmt = $pdo->prepare($updateQuery);
    $stmt->execute([
        ':first_name'      => $first_name,
        ':last_name'       => $last_name,
        ':email'           => $email,
        ':username'        => $username,
        ':bio'             => $bio,
        ':location'        => $location,
        ':practice_area'   => $practice_area,
        ':speciality'      => $speciality,
        ':profile_picture' => $profile_picture,
        ':id'              => $id
    ]);

    // Fetch updated user data
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$id]);
    $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);

    $response['status'] = true;
    $response['message'] = 'Profile updated successfully.';
    $response['data'] = $updatedUser;

} catch (PDOException $e) {
    $response['message'] = 'Database error: ' . $e->getMessage();
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
