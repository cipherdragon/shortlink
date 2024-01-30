<?php

namespace shortlink;

require_once __DIR__ . '/model/LoginManager.php';
require_once __DIR__ . '/model/RedirectionDAO.php';
require_once __DIR__ . '/model/Redirection.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();
$user = $login_manager->get_user();

if (is_null($user)) {
    require_once __DIR__ . '/login.php';
    die();
}

require_once __DIR__ . '/view/dashboard.html';
die();