<?php
require_once 'vendor/autoload.php';
require_once 'connection.php';
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/PHP-Esqify-Project/config.php';

$client = new Google_Client();
$client->setClientId('68793283767-52i81i5gtguotn99s68ilhvfoptmv87i.apps.googleusercontent.com'); // Replace with your actual ID
$client->setClientSecret('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'); // Replace with your actual secret
$client->setRedirectUri('http://localhost/PHP-Esqify-Project/google-callback.php');
$client->addScope("email");
$client->addScope("profile");

// Redirect to Google login
$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
