<?php

namespace shortlink;

require_once __DIR__ . '/model/RedirectionDAO.php';
require_once __DIR__ . '/model/Redirection.php';

// Removes leading and trailing forward slashes if any
function remove_slashes($str) {
	if (str_starts_with($str, '/')) {
		$str = substr($str, 1, strlen($str) - 1);	
	}
	
	if (str_ends_with($str, '/')) {
		$str = substr($str, 0, strlen($str) - 1);	
	}
	
	return $str;
}

function run_redirect($slug) {
	$slug = remove_slashes($slug);
	
	if (!Redirection::is_valid_slug($slug)) return;

	if ($slug === "rd") {
		require_once __DIR__ . '/dashboard.php';
		die(); // Prevent further execution if dashboard is requested
	}

	$dest = RedirectionDAO::get_instance()->get_redirection($slug);
	if (is_null($dest)) return; // Exit and hand over execution to the next script

	header("Location: " . $dest, 301);
	die(); // Terminate script if redirection is successful
}

run_redirect($_SERVER['REQUEST_URI']);