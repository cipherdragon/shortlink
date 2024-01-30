<?php

namespace shortlink;

require_once __DIR__ . '/../model/LoginManager.php';
require_once __DIR__ . '/../model/UserDAO.php';
require_once __DIR__ . '/../model/User.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();
$user = $login_manager->get_user();

// Must log-in to use this API
if (is_null($user)) {
    http_response_code(401);
    die();
}

function get_json_body() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_null($data)) throw new Exception("Invalid data");
    return $data;
}

// Get request should be responded with the info of the user
function handle_get() {
    global $user;

    $user_properties = array(
        "uid" => $user->get_uid(),
        "username" => $user->get_username(),
        "is_admin" => $user->is_admin()
    );

    $json = json_encode($user_properties);
    
    header('Content-Type: application/json');
    echo $json;
}

// Put request should be responded with updating user info
function handle_put() {
    global $user;

    try {
        $data = get_json_body();

        $username = $data['username'];
        $password = $data['password'];

        if (is_null($username) || is_null($password)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        die();
    }

    $role = $user->is_admin() ? "ADMIN" : "USER";

    $user_dao = UserDAO::get_instance();
    $user_dao->update_user($user, $username, $password, $role);

    http_response_code(200);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handle_get();
        break;
    case 'PUT':
        handle_put();
        break;
    default:
        http_response_code(405);
        die();
}