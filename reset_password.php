<?php
header('Content-Type: application/json');
require 'connection.php'; // $conn is PDO instance

try {
    // 1️⃣ Get POST data
    $verification_token = isset($_POST['verification_token']) ? trim($_POST['verification_token']) : '';
    $new_password       = isset($_POST['password']) ? trim($_POST['password']) : '';

    // 2️⃣ Validate input
    if (empty($verification_token) || empty($new_password)) {
        echo json_encode(["status" => false, "error" => "Verification token and new password are required"]);
        exit;
    }

    // 3️⃣ Find user by verification_token
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_token = :token LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT id FROM new_users WHERE verification_token = :token LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([':token' => $verification_token]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "error" => "Invalid verification token"]);
        exit;
    }

    // 4️⃣ Hash the new password
    $hashedPassword = password_hash($new_password, PASSWORD_BCRYPT);

    // 5️⃣ Update the user's password and clear verification_token (optional but recommended)
<<<<<<< HEAD
    $updateStmt = $conn->prepare("UPDATE users SET password = :password, verification_token = NULL WHERE id = :id");
=======
    $updateStmt = $conn->prepare("UPDATE new_users SET password = :password, verification_token = NULL WHERE id = :id");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $updateStmt->execute([
        ':password' => $hashedPassword,
        ':id'       => $user['id']
    ]);

    // 6️⃣ Success response
    echo json_encode(["status" => true, "message" => "Password reset successful"]);

} catch (PDOException $e) {
    echo json_encode(["status" => false, "error" => $e->getMessage()]);
}
