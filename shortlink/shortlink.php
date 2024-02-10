<?php

namespace shortlink;

require_once __DIR__ . '/model/RedirectionDAO.php';
require_once __DIR__ . '/model/Redirection.php';

function run_redirection($slug) {
	$slug = ltrim($slug, '/');
	$slug = rtrim($slug, '/');
	
	if (!Redirection::is_valid_slug($slug)) return;

	$dest = RedirectionDAO::get_instance()->get_redirection($slug);
	if (is_null($dest)) return;

	header("Location: " . $dest, 301);
	die();
}

function route() {
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$path = rtrim($path, '/');

	switch ($path) {
		case '/rd':
			header("Location: /rd/dashboard");
			die();
		case '/rd/dashboard':
			require_once __DIR__ . '/view/dashboard.php';
			die();
		case '/rd/login':
    		require_once __DIR__ . '/view/login.php';
			die();
		default:
			// Not die()ing here. If redirection does not exist, control needs to
			// be passed to the other application
			run_redirection($path);
			break;
	}
}

route();