<?php

namespace shortlink;

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405);
    die();
}

// Log out if already logged in
require_once __DIR__ . '/../model/LoginManager.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();

// Get JSON body
$data = json_decode(file_get_contents('php://input'), true);

if (is_null($data)) {
    http_response_code(400);
    die();
}

if (!isset($data['username']) || !isset($data['password'])) {
    http_response_code(400);
    die();
}

// Get username and password
$username = $data['username'];
$password = $data['password'];

// Login, if not already logged in
if ($login_manager->get_user() == NULL) {
    $login_manager->login($username, $password);
}

// Send unauthorized if login failed
if (is_null($login_manager->get_user())) {
    http_response_code(401);
    die();
}

// Respond with user info
$user = $login_manager->get_user();

$user_properties = array(
    "uid" => $user->get_uid(),
    "username" => $user->get_username(),
    "is_admin" => $user->is_admin()
);

$json = json_encode($user_properties);

header('Content-Type: application/json');
echo $json;