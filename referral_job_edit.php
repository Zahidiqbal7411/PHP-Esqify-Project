<?php
header('Content-Type: application/json');
require_once 'connection.php'; // This defines $conn (PDO instance)

$response = [
    'status'  => false,
    'message' => '',
    'data'    => null
];

// Allow only POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Only POST requests are allowed.';
    echo json_encode($response);
    exit;
}

// Required parameters
$process_type = $_POST['process_type'] ?? '';
$job_id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;

// Validate job ID
if ($job_id <= 0) {
    $response['message'] = 'Invalid or missing job ID.';
    echo json_encode($response);
    exit;
}

// ==============================
// 1️⃣ FETCH REFERRAL JOB DETAILS
// ==============================
if ($process_type === 'edit') {
    try {
        $stmt = $conn->prepare("
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
            WHERE j.id = :id AND j.job_type = 'referral'
            LIMIT 1
        ");
        $stmt->execute([':id' => $job_id]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$job) {
            $response['message'] = 'Referral job not found.';
        } else {
            // Replace IDs with human-readable titles
            $job['industry'] = $job['industry_name'];
            $job['speciality'] = $job['speciality_name'];
            $job['practice_area'] = $job['practice_area_name'];
            $job['job_state'] = $job['job_state_name'];
            $job['job_city'] = $job['job_city_name'];

            unset(
                $job['industry_name'],
                $job['speciality_name'],
                $job['practice_area_name'],
                $job['job_state_name'],
                $job['job_city_name']
            );

            $response['status'] = true;
            $response['message'] = 'Referral job details fetched successfully.';
            $response['data'] = $job;
        }
    } catch (PDOException $e) {
        $response['message'] = 'Database error: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// ==============================
// 2️⃣ UPDATE REFERRAL JOB DETAILS
// ==============================
if ($process_type === 'update') {
    try {
        // 1. Fetch existing job data
        $stmt = $conn->prepare("SELECT * FROM eq_jobs WHERE id = :id AND job_type = 'referral' LIMIT 1");
        $stmt->execute([':id' => $job_id]);
        $existingJob = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$existingJob) {
            $response['message'] = 'Referral job not found.';
            echo json_encode($response);
            exit;
        }

        // 2. Merge new data with old (keep old if not provided)
        $title         = $_POST['title']         ?? $existingJob['title'];
        $industry      = $_POST['industry']      ?? $existingJob['industry'];
        $speciality    = $_POST['speciality']    ?? $existingJob['speciality'];
        $practice_area = $_POST['practice_area'] ?? $existingJob['practice_area'];
        $job_city      = $_POST['job_city']      ?? $existingJob['job_city'];
        $job_state     = $_POST['job_state']     ?? $existingJob['job_state'];
        $position      = $_POST['position']      ?? $existingJob['position'];
        $owner         = $_POST['owner']         ?? $existingJob['owner'];
        $description   = $_POST['description']   ?? $existingJob['descriptions'];
        $firm          = $_POST['firm']          ?? $existingJob['firm'];
        $is_active     = isset($_POST['is_active']) ? (int)$_POST['is_active'] : $existingJob['is_active'];
        $salary        = $_POST['salary']        ?? $existingJob['salary'];

        // 3. Update query
        $sql = "UPDATE eq_jobs SET 
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
                WHERE id = :id AND job_type = 'referral'";

        $updateStmt = $conn->prepare($sql);
        $updateStmt->execute([
            ':title'         => $title,
            ':industry'      => $industry,
            ':speciality'    => $speciality,
            ':practice_area' => $practice_area,
            ':job_city'      => $job_city,
            ':job_state'     => $job_state,
            ':position'      => $position,
            ':owner'         => $owner,
            ':description'   => $description,
            ':firm'          => $firm,
            ':is_active'     => $is_active,
            ':salary'        => $salary,
            ':id'            => $job_id
        ]);

        // 4. Return updated record
        $stmt = $conn->prepare("SELECT * FROM eq_jobs WHERE id = :id AND job_type = 'referral' LIMIT 1");
        $stmt->execute([':id' => $job_id]);
        $updatedJob = $stmt->fetch(PDO::FETCH_ASSOC);

        $response['status'] = true;
        $response['message'] = 'Referral job updated successfully.';
        $response['data'] = $updatedJob;

    } catch (PDOException $e) {
        $response['message'] = 'Update failed: ' . $e->getMessage();
    }

    echo json_encode($response);
    exit;
}

// ==============================
// 🔴 Invalid process type
// ==============================
$response['message'] = 'Invalid process type. Use "edit" or "update" only.';
echo json_encode($response);
exit;
