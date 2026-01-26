<?php
<<<<<<< HEAD
<<<<<<< HEAD
header('Content-Type: application/json');
require_once 'connection.php'; 
=======
// store_job.php
header('Content-Type: application/json');
require_once 'connection.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
header('Content-Type: application/json');
require_once 'connection.php'; 
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

$result = [
    'status' => false,
    'message' => '',
    'data' => null
];

<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
// ===== Get raw input (JSON support) =====
$rawInput = file_get_contents("php://input");
$decodedJson = json_decode($rawInput, true);
if (!is_array($decodedJson)) $decodedJson = [];

// ===== Helper: Get param safely from JSON or POST =====
function getParam($key, $default = null) {
    global $decodedJson;
    if (isset($decodedJson[$key])) return $decodedJson[$key];
    if (isset($_POST[$key])) return $_POST[$key];
    return $default;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = getParam('process_type', '');

    if ($type === 'job_details') {
        $title = getParam('title', '');
        $industry = getParam('industry', '');
        $speciality = getParam('speciality', '');
        $practice_area = getParam('practice_area', '');
        $job_city = getParam('job_city', '');
        $job_state = getParam('job_state', '');
        $position = getParam('position', '');
        $owner = getParam('owner', '');
        $description = getParam('description', '');
        $firm = getParam('firm', '');
        $salary = getParam('salary', '');
<<<<<<< HEAD
        $posted_date = date('Y-m-d H:i:s');

        try {
            $sql = "INSERT INTO eq_jobs 
                (title, job_type, industry, speciality, practice_area, job_city, job_state, position, owner, descriptions, firm, salary, posted_date) 
                VALUES 
                (:title, 'regular', :industry, :speciality, :practice_area, :job_city, :job_state, :position, :owner, :descriptions, :firm, :salary, :posted_date)";
=======
        
        // Additional fields for Sprint 2
        $employment_type = getParam('employment_type', '');
        $seniority_level = getParam('seniority_level', '');
        $salary_range = getParam('salary_range', '');
        $additional_compensation = getParam('additional_compensation', '');
        $benefits = getParam('benefits', '');
        $duration = getParam('duration', '');
        
        $posted_date = date('Y-m-d H:i:s');

        try {
            // Updated SQL to include all potential fields safely
            $sql = "INSERT INTO eq_jobs 
                (title, job_type, industry, speciality, practice_area, job_city, job_state, position, owner, descriptions, firm, salary, posted_date, 
                 employment_type, seniority_level, salary_range, additional_compensation, benefits, duration) 
                VALUES 
                (:title, 'regular', :industry, :speciality, :practice_area, :job_city, :job_state, :position, :owner, :descriptions, :firm, :salary, :posted_date,
                 :employment_type, :seniority_level, :salary_range, :additional_compensation, :benefits, :duration)";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)

            $stmt = $conn->prepare($sql);

            $stmt->execute([
                ':title' => $title,
                ':industry' => $industry,
                ':speciality' => $speciality,
                ':practice_area' => $practice_area,
                ':job_city' => $job_city,
                ':job_state' => $job_state,
                ':position' => $position,
                ':owner' => $owner,
                ':descriptions' => $description,
                ':firm' => $firm,
                ':salary' => $salary,
<<<<<<< HEAD
                ':posted_date' => $posted_date
=======
                ':posted_date' => $posted_date,
                ':employment_type' => $employment_type,
                ':seniority_level' => $seniority_level,
                ':salary_range' => $salary_range,
                ':additional_compensation' => $additional_compensation,
                ':benefits' => $benefits,
                ':duration' => $duration
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            ]);

            $lastId = $conn->lastInsertId();

            // Fetch the inserted record
            $selectStmt = $conn->prepare("SELECT * FROM eq_jobs WHERE id = :id");
            $selectStmt->execute([':id' => $lastId]);
            $job = $selectStmt->fetch(PDO::FETCH_ASSOC);

            $result['status'] = true;
            $result['message'] = 'Great, the job details have been saved successfully.';
            $result['data'] = $job;

        } catch (PDOException $e) {
            $result['message'] = 'Database error: ' . $e->getMessage();
<<<<<<< HEAD
=======
// Only accept POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['process_type'] ?? '';

    if ($type === 'job_details') {
        $title = $_POST['title'] ?? '';
        $industry = $_POST['industry'] ?? '';
        $speciality = $_POST['speciality'] ?? '';
        $practice_area = $_POST['practice_area'] ?? '';
        $job_city = $_POST['job_city'] ?? '';
        $job_state = $_POST['job_state'] ?? '';
        $position = $_POST['position'] ?? '';
        $owner = $_POST['owner'] ?? '';
        $description = $_POST['description'] ?? '';
        $firm = $_POST['firm'] ?? '';
        $salary = $_POST['salary'] ?? '';
        $posted_date = date('Y-m-d H:i:s');

        // Build raw SQL query
        $query = "
            INSERT INTO eq_jobs (
                title, job_type, industry, speciality, practice_area,
                job_city, job_state, position, owner, descriptions,
                firm, salary, posted_date
            ) VALUES (
                '$title', 'regular', '$industry', '$speciality', '$practice_area',
                '$job_city', '$job_state', '$position', '$owner', '$description',
                '$firm', '$salary', '$posted_date'
            )
        ";

        // Run the query
        if (mysqli_query($conn, $query)) {
            $result['status'] = true;
            $result['message'] = 'Job successfully saved.';
            $result['data'] = ['id' => mysqli_insert_id($conn)];
        } else {
            $result['message'] = 'Error inserting job: ' . mysqli_error($conn);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        }
    } else {
        $result['message'] = 'Invalid process type.';
    }
} else {
    $result['message'] = 'Invalid request method.';
}

echo json_encode($result);
<<<<<<< HEAD
<<<<<<< HEAD
=======


?>




>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
