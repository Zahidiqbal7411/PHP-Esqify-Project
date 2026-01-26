<?php
header("Content-Type: application/json");

// ===== Only Allow POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => false, "message" => "Only POST method is allowed"]);
    exit();
}

// ===== Database Connection =====
require_once 'connection.php';

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

// ===== Determine Owner =====
// From POST if exists, else default to 1
$owner_id = intval(getParam('owner', 1));

// ===== Validate Required Fields =====
$required_fields = ["title", "state", "industry", "practice_area", "speciality"];
$missing_fields = [];
foreach ($required_fields as $field) {
    $val = getParam($field);
    if (is_null($val) || $val === '') {
        $missing_fields[] = $field;
    }
}
if (!empty($missing_fields)) {
    echo json_encode([
        "status" => false,
        "message" => "Missing required fields: " . implode(", ", $missing_fields)
    ]);
    exit();
}

// ===== Handle file uploads (photos) =====
$image_names = [];
if (!empty($_FILES['photos']) && is_array($_FILES['photos']['tmp_name'])) {
    $upload_dir = __DIR__ . '/../public/uploads/deal-images/';
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

    foreach ($_FILES['photos']['tmp_name'] as $key => $tmp_name) {
        if (is_uploaded_file($tmp_name)) {
            $filename = preg_replace("/[^a-zA-Z0-9\.\-\s]/", "", basename($_FILES['photos']['name'][$key]));
            $target = $upload_dir . $filename;
            if (file_exists($target)) {
                $filename = time() . "_" . $filename;
                $target = $upload_dir . $filename;
            }
            if (move_uploaded_file($tmp_name, $target)) {
                $image_names[] = $filename;
            } else {
                echo json_encode([
                    "status" => false,
                    "message" => "Failed to upload file: " . $_FILES['photos']['name'][$key]
                ]);
                exit();
            }
        }
    }
}

// ===== Handle tags JSON string =====
$tagValues = [];
$tags_raw = getParam('tags', '[]');
$tags_decoded = json_decode($tags_raw, true);
if (is_array($tags_decoded)) {
    foreach ($tags_decoded as $tag) {
        if (isset($tag['value'])) $tagValues[] = $tag['value'];
    }
}

// ===== Handle other_attorneys array =====
$other_attorneys = getParam('other_attorneys', []);
if (is_string($other_attorneys)) $other_attorneys = json_decode($other_attorneys, true);
if (!is_array($other_attorneys)) $other_attorneys = [];

// ===== Determine city value (prefer city_id over city text) =====
$city_value = '';
if (!empty(getParam("city_id")) && is_numeric(getParam("city_id"))) {
    $city_value = intval(getParam("city_id"));
} elseif (!empty(getParam("city"))) {
    $city_value = trim(getParam("city"));
}

// ===== Prepare Insert Query =====
$sql = "INSERT INTO deals 
(title, owner, state, industry, practice_area, speciality, descriptions, notes, press_release_link, tags, photos, amount, firm, client, company_name, city, other_attorneys, created_at) 
VALUES 
(:title, :owner, :state, :industry, :practice_area, :speciality, :descriptions, :notes, :press_release_link, :tags, :photos, :amount, :firm, :client, :company_name, :city, :other_attorneys, NOW())";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindValue(":title", trim(getParam("title")), PDO::PARAM_STR);
    $stmt->bindValue(":owner", $owner_id, PDO::PARAM_INT);
    $stmt->bindValue(":state", intval(getParam("state")), PDO::PARAM_INT);
    $stmt->bindValue(":industry", intval(getParam("industry")), PDO::PARAM_INT);
    $stmt->bindValue(":practice_area", intval(getParam("practice_area")), PDO::PARAM_INT);
    $stmt->bindValue(":speciality", intval(getParam("speciality")), PDO::PARAM_INT);
    $stmt->bindValue(":descriptions", trim(getParam("descriptions", '')), PDO::PARAM_STR);
    $stmt->bindValue(":notes", trim(getParam("notes", '')), PDO::PARAM_STR);
    $stmt->bindValue(":press_release_link", trim(getParam("press_release_link", '')), PDO::PARAM_STR);
    $stmt->bindValue(":tags", json_encode($tagValues), PDO::PARAM_STR);
    $stmt->bindValue(":photos", json_encode($image_names), PDO::PARAM_STR);
    $stmt->bindValue(":amount", getParam("amount", "0.00"), PDO::PARAM_STR);
    $stmt->bindValue(":firm", trim(getParam("firm", '')), PDO::PARAM_STR);
    $stmt->bindValue(":client", trim(getParam("client", '')), PDO::PARAM_STR);
    $stmt->bindValue(":company_name", trim(getParam("company_name", '')), PDO::PARAM_STR);
    $stmt->bindValue(":city", $city_value, is_int($city_value) ? PDO::PARAM_INT : PDO::PARAM_STR);
    $stmt->bindValue(":other_attorneys", json_encode($other_attorneys), PDO::PARAM_STR);

    $stmt->execute();

    $full_image_urls = [];
    foreach ($image_names as $img) {
        $full_image_urls[] = rtrim($GLOBALS['dealimage'], '/') . '/' . ltrim($img, '/');
    }

    echo json_encode([
        "status" => true,
        "message" => "Deal posted successfully",
        "deal_id" => $conn->lastInsertId(),
        "photos" => $full_image_urls
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Error posting deal: " . $e->getMessage()
    ]);
}
