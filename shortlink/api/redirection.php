<?php

namespace shortlink;

require_once __DIR__ . '/../model/LoginManager.php';
require_once __DIR__ . '/../model/Redirection.php';
require_once __DIR__ . '/../model/RedirectionDAO.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();
$user = $login_manager->get_user();

// Must log-in to use this API
if (is_null($user)) {
    http_response_code(401);
    die();
}

// Get request should be responded with all the redirections that user has created
function handle_get() {
    global $user;

    $redirects = RedirectionDAO::get_instance()->get_user_redirections($user);
    $redirect_properties = array();

    foreach ($redirects as $redirect) {
        $redirect_properties[] = array(
            "slug" => $redirect->get_slug(),
            "destination" => $redirect->get_destination()
        );
    }

    $json = json_encode($redirect_properties);
    
    header('Content-Type: application/json');
    echo $json;
}

// Post request should be responded with a redirection created
function handle_post() {
    global $user;

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_null($data)) throw new Exception("Invalid data");

        $slug = $data['slug'];
        $destination = $data['destination'];
        if (is_null($slug) || is_null($destination)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => $e->getMessage()));
        die();
    }

    if (!Redirection::is_valid_slug($slug)) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid slug"));
        die();
    }

    $redirection = new Redirection($slug, $destination);
    $result = RedirectionDAO::get_instance()->add_redirection($redirection, $user);

    if (!$result) {
        http_response_code(500);
        die();
    }

    http_response_code(201);
}

// Delete request should be responded with a redirection deleted
function handle_delete() {
    global $user;

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_null($data)) throw new Exception("Invalid data");

        $slug = $data['slug'];
        if (is_null($slug)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => $e->getMessage()));
        die();
    }

    $result = RedirectionDAO::get_instance()->delete_redirection($slug);

    if (!$result) {
        http_response_code(500);
        die();
    }

    http_response_code(200);
}

// Put request should be responded with a redirection updated
function handle_put() {
    global $user;

    try {
        $data = json_decode(file_get_contents('php://input'), true);
        if (is_null($data)) throw new Exception("Invalid data");

        $slug = $data['slug'];
        $destination = $data['destination'];
        if (is_null($slug) || is_null($destination)) throw new Exception("Invalid data");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(array("error" => $e->getMessage()));
        die();
    }

    if (!Redirection::is_valid_slug($slug)) {
        http_response_code(400);
        echo json_encode(array("error" => "Invalid slug"));
        die();
    }

    $redirection = new Redirection($slug, $destination);
    $result = RedirectionDAO::get_instance()->update_redirection($redirection);

    if (!$result) {
        http_response_code(500);
        die();
    }

    http_response_code(201);
}

$request_method = $_SERVER['REQUEST_METHOD'];
switch ($request_method) {
    case 'GET':
        handle_get();
        break;
    case 'POST':
        handle_post();
        break;
    case 'DELETE':
        handle_delete();
        break;
    case 'PUT':
        handle_put();
        break;
    default:
        http_response_code(405);
        die();
}