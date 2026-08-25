<?php 
	// Ensure UTF-8 and suppress displayed PHP warnings/notices in output to avoid breaking JSON/AJAX responses.
	ini_set('default_charset', 'UTF-8');
	mb_internal_encoding('UTF-8');
	// Temporarily suppress error output in HTTP responses (log them instead). Reversible.
	ini_set('display_errors', '0');
	ini_set('display_startup_errors', '0');
	error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING & ~E_DEPRECATED);
	if (session_status() === PHP_SESSION_NONE) {
		session_start();
	}
	require_once(dirname(__FILE__)."/shared/class/controller/ViewExtCtrl.class.php");
	ViewExtCtrl::includeMainPage();
?>
