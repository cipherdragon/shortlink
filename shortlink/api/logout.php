<?php

namespace shortlink;

require_once __DIR__ . '/../model/LoginManager.php';

$login_manager = LoginManager::get_instance();
$login_manager->session_login();
$login_manager->logout();

http_response_code(200);
die();