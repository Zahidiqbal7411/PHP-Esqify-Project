<?php
ob_start();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';
// LinkedIn App Credentials
$clientId = '77lyu7rwvt9of0'; // Your LinkedIn Client ID
$redirectUri = 'http://localhost/PHP-Esqify-Project/linkedin-callback.php'; // Must match LinkedIn app config
$scope = 'openid profile email'; // Required scopes for OpenID Connect login

// Generate a secure random state parameter for CSRF protection
$state = bin2hex(random_bytes(16));
$_SESSION['linkedin_state'] = $state;

// Build LinkedIn Authorization URL
$params = [
    'response_type' => 'code',
    'client_id' => $clientId,
    'redirect_uri' => $redirectUri,
    'scope' => $scope,
    'state' => $state
];

$linkedinAuthUrl = 'https://www.linkedin.com/oauth/v2/authorization?' . http_build_query($params);

// Redirect user to LinkedIn for authentication
header('Location: ' . $linkedinAuthUrl);
exit();
