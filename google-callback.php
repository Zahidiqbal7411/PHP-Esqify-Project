<?php
require_once 'vendor/autoload.php';
require_once 'connection.php'; 
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Suppress warnings for cleaner output (optional, remove during development)
error_reporting(E_ALL & ~E_WARNING);

// Set header for JSON response
header('Content-Type: application/json');

// Initialize Google Client
$client = new Google_Client();
$client->setClientId('68793283767-52i81i5gtguotn99s68ilhvfoptmv87i.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/PHP-Esqify-Project/google-callback.php');
$client->addScope("email");
$client->addScope("profile");

// Handle missing authorization code
if (!isset($_GET['code'])) {
    http_response_code(400);
    echo json_encode(["error" => "Missing authorization code."]);
    exit();
}

// Fetch access token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    http_response_code(401);
    echo json_encode(["error" => "Token error", "details" => $token['error']]);
    exit();
}

// Set token
$client->setAccessToken($token['access_token']);

// Get user info
$oauth = new Google_Service_Oauth2($client);
$googleUser = $oauth->userinfo->get();

$email = $conn->real_escape_string($googleUser->email);
$google_id = $conn->real_escape_string($googleUser->id);
$username = $conn->real_escape_string($googleUser->name); // Use Google name as username
$email_logo = $conn->real_escape_string($googleUser->picture); 
$proivder='Google';
// Google profile picture

// Check if user exists
<<<<<<< HEAD
$sql = "SELECT * FROM users WHERE google_id = '$google_id' OR email = '$email' LIMIT 1";
=======
$sql = "SELECT * FROM new_users WHERE google_id = '$google_id' OR email = '$email' LIMIT 1";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $user_id = $user['id'];

    // Update user info
<<<<<<< HEAD
    $update = "UPDATE users SET username='$username', email_logo='$email_logo' WHERE id=$user_id";
    $conn->query($update);
} else {
    // Insert new user
    $insert = "INSERT INTO users (google_id, username, email, email_logo,provider) VALUES ('$google_id', '$username', '$email', '$email_logo','$provider')";
=======
    $update = "UPDATE new_users SET username='$username', email_logo='$email_logo' WHERE id=$user_id";
    $conn->query($update);
} else {
    // Insert new user
    $insert = "INSERT INTO new_users (google_id, username, email, email_logo,provider) VALUES ('$google_id', '$username', '$email', '$email_logo','$provider')";
>>>>>>> 9c6d471 (Created some new API's & fixed issues in some API's)
    $conn->query($insert);
    $user_id = $conn->insert_id;
    $user = [
        'id' => $user_id,
        'username' => $username,
        'email' => $email,
        'google_id' => $google_id,
        'email_logo' => $email_logo
    ];
}

// ✅ Start PHP Session
$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['email'] = $user['email'];
$_SESSION['google_id'] = $google_id;
$_SESSION['email_logo'] = $email_logo;

// ✅ Generate JWT token
$secretKey = "YourSuperSecretKey123!"; // Use a strong key in production
$payload = [
    "iss" => "http://localhost",
    "aud" => "http://localhost",
    "iat" => time(),
    "exp" => time() + 3600, // 1 hour expiry
    "data" => [
        "id" => $user['id'],
        "email" => $user['email'],
        "username" => $user['username']
    ]
];

$jwt = JWT::encode($payload, $secretKey, 'HS256');

// ✅ Return JSON response
echo json_encode([
    "message" => "Google login successful",
    "token" => $jwt,
    "user" => [
        "id" => $user['id'],
        "username" => $user['username'],
        "email" => $user['email'],
        "google_id" => $google_id,
        "email_logo" => $email_logo
    ],
    "session" => $_SESSION
]);
