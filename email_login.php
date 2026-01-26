<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';
require 'connection.php'; // $conn is PDO instance

use Firebase\JWT\JWT;

try {
    // 1️⃣ Get input data
    if (!empty($_POST)) {
        $data = $_POST; // form-data
    } else {
        $input = file_get_contents("php://input"); // raw JSON
        $data = json_decode($input, true) ?? [];
    }

    // 2️⃣ Validate
    if (empty($data['email']) || empty($data['password'])) {
        echo json_encode(["status" => false, "error" => "Email and password required"]);
        exit;
    }

    $email = trim($data['email']);
    $password = trim($data['password']);

    // 3️⃣ Fetch user
<<<<<<< HEAD
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
=======
    $stmt = $conn->prepare("SELECT * FROM new_users WHERE email = :email LIMIT 1");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => false, "error" => "This email is not registered in our system"]);
        exit;
    }

    // 4️⃣ Verify hashed password (bcrypt)
    if (!password_verify($password, $user['password'])) {
        echo json_encode(["status" => false, "error" => "Invalid password"]);
        exit;
    }

    // 5️⃣ Create JWT token
    $secretKey = "MyVerySecretKey123!@#";
    $payload = [
        'iss' => 'https://api.mmmt.app',
        'aud' => 'https://api.mmmt.app',
        'iat' => time(),
        'exp' => time() + 3600,
        'data' => [
            'id' => $user['id'],
            'email' => $user['email']
        ]
    ];

    $jwt = JWT::encode($payload, $secretKey, 'HS256');

    // 6️⃣ Send success response
    echo json_encode([
        "status" => true,
        "message" => "Login successful",
        "token" => $jwt,
        "token_key" => $secretKey
    ]);

} catch (PDOException $e) {
    echo json_encode(["status" => false, "error" => $e->getMessage()]);
}
