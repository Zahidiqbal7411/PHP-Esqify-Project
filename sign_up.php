<?php
header('Content-Type: application/json');

// ===== Only allow POST =====
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Only POST method is allowed.'
    ]);
    exit();
}

// ===== Include DB connection =====
require_once 'connection.php'; // $conn is PDO

// ===== Get POST data =====
$firstname = isset($_POST['firstname']) ? trim($_POST['firstname']) : '';
$lastname = isset($_POST['lastname']) ? trim($_POST['lastname']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

// ===== Validation =====
if (!$firstname) {
    echo json_encode(['success' => false, 'message' => 'First name is required.']);
    exit;
}

if (!$lastname) {
    echo json_encode(['success' => false, 'message' => 'Last name is required.']);
    exit;
}

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Valid email is required.']);
    exit;
}

if (!$password || strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters.']);
    exit;
}

if ($password !== $confirm_password) {
    echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
    exit;
}

// ===== Check if email exists =====
<<<<<<< HEAD
$stmt = $conn->prepare("SELECT id FROM users WHERE email = :email");
=======
$stmt = $conn->prepare("SELECT id FROM new_users WHERE email = :email");
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
$stmt->execute([':email' => $email]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'This email is already taken.']);
    exit;
}

// ===== Insert user =====
try {
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $verification_token = bin2hex(random_bytes(32));

    $insertStmt = $conn->prepare("
<<<<<<< HEAD
        INSERT INTO users (first_name, last_name, email, password, verification_token, created_at)
=======
        INSERT INTO new_users (first_name, last_name, email, password, verification_token, created_at)
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
        VALUES (:first_name, :last_name, :email, :password, :token, NOW())
    ");

    $insertStmt->execute([
        ':first_name' => $firstname,
        ':last_name' => $lastname,
        ':email' => $email,
        ':password' => $hashedPassword,
        ':token' => $verification_token
    ]);

    $user_id = $conn->lastInsertId();

    // ===== Return inserted user info =====
    echo json_encode([
        'success' => true,
        'message' => 'User registered successfully!',
        'data' => [
            'id' => $user_id,
            'first_name' => $firstname,
            'last_name' => $lastname,
            'email' => $email,
            'verification_token' => $verification_token
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Something went wrong: ' . $e->getMessage()
    ]);
}
