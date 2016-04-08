<?php
/**
 * Buznik.net start
 */
$lang = 'rus';
if (isset($_GET['lang'])) {
	$lang = $_GET['lang'];
}
if (isset($argv) && count($argv) > 1) {
	$lang = $argv[1];
}
include('index_' . $lang . '.html');
?>