<?php
header('Content-Type: application/json');
require_once 'connection.php'; // Your PDO connection file

$response = [
    'status' => false,
    'message' => '',
    'data' => []
];

try {
    // Get industries
    $stmt = $conn->query("SELECT * FROM industrys");
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get specialities
    $stmt = $conn->query("SELECT * FROM specialties");
    $specialities = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get practice areas
    $stmt = $conn->query("SELECT * FROM practice_areas");
    $practiceAreas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get states
    $stmt = $conn->query("SELECT * FROM states");
    $states = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get users (role_id = 3, exclude current user)
    $currentUserId = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT * FROM users WHERE role_id = 3 AND id != ?");
=======
    $check_user = $conn->prepare("SELECT id FROM new_users WHERE id = ?");
    $stmt = $conn->prepare("SELECT * FROM new_users WHERE role_id = 3 AND id != ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$currentUserId]);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get positions - try both table names
    $positions = [];
    try {
        $tableCheck = $conn->query("SHOW TABLES LIKE 'positions'");
        if ($tableCheck->rowCount() > 0) {
            $stmt = $conn->query("SELECT * FROM positions WHERE status = 1 ORDER BY title ASC");
            $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $tableCheck = $conn->query("SHOW TABLES LIKE 'job_positions'");
            if ($tableCheck->rowCount() > 0) {
                $stmt = $conn->query("SELECT * FROM job_positions WHERE status = 1 ORDER BY title ASC");
                $positions = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
        }
    } catch (PDOException $e) {
        // If positions table doesn't exist, just continue with empty array
        error_log("Positions fetch warning: " . $e->getMessage());
    }

<<<<<<< HEAD
=======
    // Build metadata for dropdowns
    $employmentTypes = [
        ['id' => 'full_time', 'title' => 'Full-time'],
        ['id' => 'part_time', 'title' => 'Part-time'],
        ['id' => 'contract', 'title' => 'Contract'],
        ['id' => 'freelance', 'title' => 'Freelance'],
        ['id' => 'internship', 'title' => 'Internship']
    ];

    $seniorityLevels = [
        ['id' => 'intern', 'title' => 'Intern'],
        ['id' => 'entry', 'title' => 'Entry level'],
        ['id' => 'associate', 'title' => 'Associate'],
        ['id' => 'mid_senior', 'title' => 'Mid-Senior level'],
        ['id' => 'director', 'title' => 'Director'],
        ['id' => 'executive', 'title' => 'Executive']
    ];

    $salaryRanges = [
        ['id' => '0-50k', 'title' => '$0 - $50,000'],
        ['id' => '50k-100k', 'title' => '$50,000 - $100,000'],
        ['id' => '100k-150k', 'title' => '$100,000 - $150,000'],
        ['id' => '150k-200k', 'title' => '$150,000 - $200,000'],
        ['id' => '200k+', 'title' => '$200,000+']
    ];

>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    // Build response
    $response['status'] = true;
    $response['message'] = "Data fetched successfully.";
    $response['data'] = [
        'industry' => $industries,
        'speciality' => $specialities,
        'practicearea' => $practiceAreas,
        'states' => $states,
        'users' => $users,
<<<<<<< HEAD
        'positions' => $positions
=======
        'positions' => $positions,
        'employment_types' => $employmentTypes,
        'seniority_levels' => $seniorityLevels,
        'salary_ranges' => $salaryRanges
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    ];

} catch (PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

echo json_encode($response);
