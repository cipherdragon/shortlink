<?php

namespace shortlink;

require_once __DIR__ . '/../model/LoginManager.php';
require_once __DIR__ . '/../model/User.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();
$user = $login_manager->get_user();

if (!is_null($user)) {
    // Already logged in
    header('Location: /rd/dashboard');
    die();
}

require_once __DIR__ . '/dist/login.html';