<?php
// fornt.user.applicants.hired.project.php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once "connection.php"; // your PDO connection

try {
    // Allow only POST requests
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(["success" => false, "message" => "Only POST method allowed"]);
        exit();
    }

    // Read POST data
    $input = $_POST;

    // CASE 1: application_id provided -> return single application with joins
    if (!empty($input['application_id'])) {
        $applicationId = intval($input['application_id']);

        $sql = "
            SELECT ja.*, 
                   u.first_name AS owner_first_name, 
                   j.title AS job_title
            FROM job_applications ja
<<<<<<< HEAD
            LEFT JOIN users u ON u.id = ja.owner_id
=======
            LEFT JOIN new_users u ON u.id = ja.owner_id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
            LEFT JOIN eq_jobs j ON j.id = ja.job_id
            WHERE ja.id = :id
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(":id", $applicationId, PDO::PARAM_INT);
        $stmt->execute();
        $application = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$application) {
            echo json_encode(["success" => false, "message" => "Application not found"]);
            exit();
        }

        echo json_encode([
            "success" => true,
            "message" => "Application details",
            "application" => $application
        ]);
        exit();
    }

    // CASE 2: No application_id -> return paginated list with joins
    $perPage = isset($input['per_page']) ? max(1, intval($input['per_page'])) : 10;
    $page = isset($input['page']) ? max(1, intval($input['page'])) : 1;
    $offset = ($page - 1) * $perPage;

    $listSql = "
        SELECT ja.*, 
               u.first_name AS owner_first_name, 
               j.title AS job_title
        FROM job_applications ja
<<<<<<< HEAD
        LEFT JOIN users u ON u.id = ja.owner_id
=======
    JOIN new_users u ON ja.applicant_id = u.id
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        LEFT JOIN eq_jobs j ON j.id = ja.job_id
        ORDER BY ja.id DESC
        LIMIT :limit OFFSET :offset
    ";
    $listStmt = $conn->prepare($listSql);
    $listStmt->bindValue(":limit", $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(":offset", $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $apps = $listStmt->fetchAll(PDO::FETCH_ASSOC);

    $total = (int)$conn->query("SELECT COUNT(*) FROM job_applications")->fetchColumn();

    echo json_encode([
        "success" => true,
        "message" => "Applicants list",
        "total" => $total,
        "page" => $page,
        "per_page" => $perPage,
        "data" => $apps
    ]);
    exit();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
    exit();
}
