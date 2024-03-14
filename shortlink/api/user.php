<?php

namespace shortlink;

use Exception;

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

// Only admin can use this API
if (!$user->is_admin()) {
    error_log("User is not an admin");
    http_response_code(403);
    die();
}

function get_json_body() {
    $data = json_decode(file_get_contents('php://input'), true);
    if (is_null($data)) throw new Exception("Invalid data");
    return $data;
}

// Get request should be responded with all the users
function handle_get() {
    $users = UserDAO::get_instance()->get_all_users();
    $user_properties = array();

    foreach ($users as $user) {
        $user_properties[] = array(
            "uid" => $user->get_uid(),
            "username" => $user->get_username(),
            "is_admin" => $user->is_admin()
        );
    }

    $json = json_encode($user_properties);
    
    header('Content-Type: application/json');
    echo $json;
}

// Post request should be responded with a user created
function handle_post() {
    try {
        $data = get_json_body();

        $username = $data['username'];
        $password = $data['password'];
        $role = $data['role'];

        if (is_null($username) || is_null($password) || is_null($role)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => $e->getMessage()));
        die();
    }

    if (!User::is_valid_username($username)) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid username"));
        die();
    }

    if (!User::is_valid_role($role)) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid role"));
        die();
    }

    $user = UserDAO::get_instance()->create_user($username, $password, $role);

    if (is_null($user)) {
        http_response_code(500);
        return;
    }

    $user_properties = array(
        "uid" => $user->get_uid(),
        "username" => $user->get_username(),
        "is_admin" => $user->is_admin()
    );

    $json = json_encode($user_properties);
    
    header('Content-Type: application/json');
    echo $json;
}

// Delete request should be responded with a user deleted
function handle_delete() {
    try {
        $data = get_json_body();
        $uid = $data['uid'];

        if (is_null($uid)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => $e->getMessage()));
        die();
    }

    $user = UserDAO::get_instance()->get_user_by_uid($uid);
    $is_deleted = UserDAO::get_instance()->delete_user($user);
    http_response_code(($is_deleted ? 200 : 500));
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        handle_get();
        break;
    case 'POST':
        handle_post();
        break;
    case 'DELETE':
        handle_delete();
        break;
    default:
        http_response_code(405);
        die();
}