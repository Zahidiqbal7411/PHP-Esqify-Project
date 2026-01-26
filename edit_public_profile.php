<?php
<<<<<<< HEAD
<<<<<<< HEAD
require_once 'connection.php'; // loads $conn and globals
header('Content-Type: application/json');

// Get user_id from POST
$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => false, 'message' => 'user_id is required']);
=======
header('Content-Type: application/json');
require_once 'connection.php'; // Assumes $pdo is configured with ERRMODE_EXCEPTION

$response = ['status' => false, 'message' => '', 'data' => null];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Only POST requests allowed.';
    echo json_encode($response);
    exit;
}

// Required for identifying the user
$username = $_POST['username'] ?? '';
if (empty($username)) {
    $response['message'] = 'Username is required.';
    echo json_encode($response);
>>>>>>> 404cf62c9ad9b017891b70e9d23c1c5da4108b1a
=======
header('Content-Type: application/json');
require_once 'connection.php'; // loads $conn (PDO)

// 1. Get input
$userId = $_POST['user_id'] ?? null;
if (!$userId) {
    echo json_encode(['status' => false, 'message' => 'user_id is required']);
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    exit;
}

try {
<<<<<<< HEAD
<<<<<<< HEAD
    // Fetch existing user
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
=======
    // 2. Fetch existing user from new_users
    $stmt = $conn->prepare("SELECT * FROM new_users WHERE id = ?");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([$userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
<<<<<<< HEAD
        echo json_encode(['status' => false, 'message' => 'User not found']);
        exit;
    }

    $fieldsToUpdate = [];

    // Optional fields to update if passed
    $optionalFields = ['first_name','last_name','email','state','city','practice_area','bar','speciality','industry','intro'];
    foreach ($optionalFields as $field) {
        if (isset($_POST[$field])) {
            if (in_array($field, ['practice_area','bar','speciality'])) {
                $fieldsToUpdate[$field] = json_encode($_POST[$field]);
            } else {
                $fieldsToUpdate[$field] = $_POST[$field];
            }
        }
    }

    // Handle photo upload
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === 0) {
        $filename = basename($_FILES['photo']['name']); // simple file name
        $targetPath = __DIR__ . '/../public/profile/' . $filename; // go one folder up

        // Unlink old image
        if (!empty($user['image'])) {
            $oldPath = __DIR__ . '/../public/profile/' . $user['image'];
            if (file_exists($oldPath)) unlink($oldPath);
        }

        move_uploaded_file($_FILES['photo']['tmp_name'], $targetPath);
        $fieldsToUpdate['image'] = $filename;
    }

    // Handle banner upload
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === 0) {
        $filename = basename($_FILES['banner_image']['name']); // simple file name
        $targetPath = __DIR__ . '/../public/profile/' . $filename; // go one folder up

        // Unlink old banner
        if (!empty($user['banner_image'])) {
            $oldPath = __DIR__ . '/../public/profile/' . $user['banner_image'];
            if (file_exists($oldPath)) unlink($oldPath);
        }

        move_uploaded_file($_FILES['banner_image']['tmp_name'], $targetPath);
        $fieldsToUpdate['banner_image'] = $filename;
    }

    // Build dynamic SQL
    if (!empty($fieldsToUpdate)) {
        $setParts = [];
        $values = [];
        foreach ($fieldsToUpdate as $col => $val) {
            $setParts[] = "$col = ?";
            $values[] = $val;
        }
        $values[] = $userId; // WHERE id = ?

        $sql = "UPDATE users SET " . implode(", ", $setParts) . " WHERE id = ?";
        $updateStmt = $conn->prepare($sql);
        $updateStmt->execute($values);

        // Merge updated fields with old user data for response
        $user = array_merge($user, $fieldsToUpdate);
    }

    // Prepare response with full image URLs
    if (!empty($user['image'])) $user['image'] = $GLOBALS['dealownerimagepath'] . $user['image'];
    if (!empty($user['banner_image'])) $user['banner_image'] = $GLOBALS['dealownerimagepath'] . $user['banner_image'];

    echo json_encode([
        'status' => true,
        'message' => 'Profile updated successfully',
        'data' => $user
    ]);

} catch (Exception $e) {
    echo json_encode([
        'status' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
=======
    // Fetch the user by username
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $response['message'] = 'User not found.';
        echo json_encode($response);
        exit;
    }

    $userId = $user['id'];

    // Fields that can be updated
    $fields = [
        'full_name'       => $_POST['full_name'] ?? null,
        'email'           => $_POST['email'] ?? null,
        'bio'             => $_POST['bio'] ?? null,
        'location'        => $_POST['location'] ?? null,
        'practice_area'   => $_POST['practice_area'] ?? null, // JSON string or array
        'speciality'      => $_POST['speciality'] ?? null,    // JSON string or array
        'profile_picture' => $_POST['profile_picture'] ?? null
    ];

    // Prepare dynamic SQL
    $updateFields = [];
    $values = [];

    foreach ($fields as $key => $value) {
        if ($value !== null) {
            $updateFields[] = "$key = ?";
            $values[] = is_array($value) ? json_encode($value) : $value;
        }
    }

    if (empty($updateFields)) {
        $response['message'] = 'No valid fields to update.';
        echo json_encode($response);
        exit;
    }

    // Add user ID to the values array
    $values[] = $userId;

    // Final SQL
    $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($values);

    // Fetch updated user info
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$userId]);
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
        echo json_encode(['status' => false, 'message' => 'User not found!']);
        exit;
    }

    // 3. Prepare update fields
    $fields = [
        'first_name', 'last_name', 'username', 'email', 'bio', 'intro', 
        'headline', 'address', 'city', 'state_province', 'country', 
        'law_firm', 'website', 'practice_area', 'industry', 'speciality'
    ];
    
    $updatePairs = [];
    $values = [];
    
    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $updatePairs[] = "`$field` = ?";
            // Handle JSON fields if they are arrays from client
            $val = $_POST[$field];
            if (in_array($field, ['practice_area', 'speciality']) && is_array($val)) {
                $val = json_encode($val);
            }
            $values[] = $val;
        }
    }

    // 4. Handle files (Avatar/Image)
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $filename = time() . '_' . uniqid() . '.' . $ext;
        $uploadPath = 'uploads/users/' . $filename;
        
        if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
            $updatePairs[] = "`image` = ?";
            $values[] = $filename;
        }
    }

    if (empty($updatePairs)) {
        echo json_encode(['status' => false, 'message' => 'No fields to update']);
        exit;
    }

    // 5. Execute Update
    $sql = "UPDATE new_users SET " . implode(', ', $updatePairs) . ", updated_at = NOW() WHERE id = ?";
    $values[] = $userId;
    
    $updateStmt = $conn->prepare($sql);
    $updateStmt->execute($values);

    echo json_encode([
        'status' => true, 
        'message' => 'Profile updated successfully',
        'data' => ['user_id' => $userId]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
